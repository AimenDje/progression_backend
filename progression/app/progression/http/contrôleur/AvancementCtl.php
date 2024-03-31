<?php
/*
   This file is part of Progression.

   Progression is free software: you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation, either version 3 of the License, or
   (at your option) any later version.

   Progression is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with Progression.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace progression\http\contrôleur;

use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Gate;
use progression\domaine\interacteur\{ObtenirAvancementInt, SauvegarderAvancementInt, IntéracteurException};
use progression\http\transformer\AvancementTransformer;
use progression\http\transformer\dto\AvancementDTO;
use progression\util\Encodage;
use progression\domaine\entité\Avancement;
use progression\domaine\entité\question\État;

class AvancementCtl extends Contrôleur
{
	public function get(Request $request, $username, $question_uri)
	{
		Log::debug("AvancementCtl.get. Params : ", [$request->all(), $username, $question_uri]);

		$avancement = $this->obtenir_avancement($username, $question_uri);
		$réponse = $this->valider_et_préparer_réponse($avancement, $username, $question_uri);

		Log::debug("AvancementCtl.get. Retour : ", [$réponse]);
		return $réponse;
	}

	public function put(Request $request, string $username, string $question_uri): JsonResponse
	{
		Log::debug("AvancementCtl.put. Params : ", [$request->all(), $username]);

		$request->merge([
			"question_uri" => $question_uri,
			"avancement" => ["extra" => $request->extra ?? ""],
		]);

		$réponse = $this->post($request, $username);

		Log::debug("AvancementCtl.put. Retour : ", [$réponse]);
		return $réponse;
	}

	public function patch(Request $request, string $username, string $question_uri): JsonResponse
	{
		Log::debug("AvancementCtl.patch. Params : ", [$request->all(), $username]);

		$avancement_existant = $this->obtenir_avancement($username, $question_uri);

		if (!$avancement_existant) {
			$réponse = $this->préparer_réponse(null);
		} elseif ($request->extra === null || $avancement_existant->extra === $request->extra) {
			$réponse = $this->valider_et_préparer_réponse($avancement_existant, $username, $question_uri);
		} else {
			$request->merge([
				"question_uri" => $question_uri,
				"avancement" => ["extra" => $request->extra ?? ""],
			]);

			$réponse = $this->post($request, $username);
		}

		Log::debug("AvancementCtl.patch. Retour : ", [$réponse]);
		return $réponse;
	}

	public function post(Request $request, $username)
	{
		Log::debug("AvancementCtl.post. Params : ", [$request->all(), $username]);

		$validateur = $this->valider_paramètres($request);

		$réponse = null;
		if ($validateur->fails()) {
			$réponse = $this->réponse_json(["erreur" => $validateur->errors()], 400);
		} else {
			$avancement = $this->construire_avancement($username, $request->question_uri, $request->avancement ?? []);

			if ($request->avancement != null || $avancement->état === État::DEBUT) {
				$avancement_retourné = $this->sauvegarder_avancement($username, $request->question_uri, $avancement);

				$id = array_key_first($avancement_retourné);
				$réponse = $this->valider_et_préparer_réponse(
					$avancement_retourné[$id],
					$username,
					Encodage::base64_encode_url($id),
				);
			} else {
				$avancement_retourné = $avancement;
				$réponse = $this->valider_et_préparer_réponse($avancement_retourné, $username, $request->question_uri);
			}
		}

		Log::debug("AvancementCtl.post. Retour : ", [$réponse]);

		return $réponse;
	}

	/**
	 * @return array<string>
	 */
	public static function get_liens(string $username, string $question_uri): array
	{
		$urlBase = Contrôleur::$urlBase;

		$liens = [
			"self" => "{$urlBase}/avancement/{$username}/{$question_uri}",
			"user" => "{$urlBase}/user/{$username}",
			"question" => "{$urlBase}/question/{$question_uri}",
		];

		if (Gate::allows("soumettre-tentative", $username)) {
			$liens += [
				"soumettre" => "{$urlBase}/avancement/{$username}/{$question_uri}/tentatives",
			];
		}

		return $liens;
	}

	private function valider_et_préparer_réponse($avancement, $username, $question_uri)
	{
		Log::debug("AvancementCtl.valider_et_préparer_réponse. Params : ", [$avancement, $username, $question_uri]);

		if ($avancement) {
			$dto = new AvancementDTO(
				id: "{$username}/{$question_uri}",
				objet: $avancement,
				liens: AvancementCtl::get_liens($username, $question_uri),
			);

			$réponse = $this->item($dto, new AvancementTransformer());
		} else {
			$réponse = null;
		}

		$réponse = $this->préparer_réponse($réponse);

		Log::debug("AvancementCtl.valider_et_préparer_réponse. Retour : ", [$réponse]);
		return $réponse;
	}

	private function valider_paramètres($request)
	{
		$validateur = Validator::make(
			$request->all(),
			[
				"question_uri" => [
					"required",
					function ($attribute, $value, $fail) {
						$url = Encodage::base64_decode_url($value);
						if (!$url || Validator::make(["question_uri" => $url], ["question_uri" => "url"])->fails()) {
							$fail("Le champ question_uri doit être un URL encodé en base64.");
						}
					},
				],
				"avancement.extra" => "string",
			],
			[
				"required" => "Le champ :attribute est obligatoire.",
			],
		);

		return $validateur;
	}

	private function obtenir_avancement($username, $question_uri)
	{
		Log::debug("AvancementCtl.obtenir_avancement. Params : ", [$username, $question_uri]);

		$avancementInt = new ObtenirAvancementInt();

		$chemin = Encodage::base64_decode_url($question_uri);
		$avancement = $avancementInt->get_avancement($username, $chemin, $this->get_includes());

		Log::debug("AvancementCtl.obtenir_avancement. Retour : ", [$avancement]);
		return $avancement;
	}

	private function sauvegarder_avancement($username, $question_uri, $avancement)
	{
		Log::debug("AvancementCtl.sauvegarder_avancement. Params : ", [$username, $question_uri, $avancement]);

		$avancementInt = new SauvegarderAvancementInt();
		$chemin = Encodage::base64_decode_url($question_uri);

		$nouvel_avancement = $avancementInt->sauvegarder($username, $chemin, $avancement);

		Log::debug("AvancementCtl.sauvegarder_avancement. Retour : ", [$nouvel_avancement]);
		return $nouvel_avancement;
	}

	/**
	 * @param array<string> $modifications
	 */
	private function construire_avancement(string $username, string $question_uri, array $modifications): Avancement
	{
		$avancementInt = new ObtenirAvancementInt();
		$chemin = Encodage::base64_decode_url($question_uri);
		$avancement = $avancementInt->get_avancement($username, $chemin, $this->get_includes()) ?? new Avancement();

		$avancement->extra = $modifications["extra"] ?? $avancement->extra;

		return $avancement;
	}
}
