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
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use progression\domaine\entité\Commentaire;
use progression\domaine\interacteur\{SauvegarderCommentaireInt, IntéracteurException, ObtenirUserInt};
use progression\http\transformer\CommentaireTransformer;
use progression\http\transformer\dto\GénériqueDTO;
use progression\util\Encodage;

class CommentaireCtl extends Contrôleur
{
	public function post(Request $request, $username, $question_uri, $timestamp): JsonResponse
	{
		Log::debug("CommentaireCtl.post. Params : ", [$request->all(), $username]);
		$commentaire = null;

		$question_uriDécodé = Encodage::base64_decode_url($question_uri);

		$réponse = null;
		$validateur = $this->valider_paramètres($request);
		if ($validateur->fails()) {
			$réponse = $this->réponse_json(["erreur" => $validateur->errors()], 400);
		} else {
			if (isset($request->créateur) && !Gate::allows("créer-commentaire", $request->créateur)) {
				$réponse = $this->réponse_json(["erreur" => $validateur->errors()], 403);
			} else {
				$user = (new ObtenirUserInt())->get_user($request->créateur ?? $username);
				if (!$user) {
					$réponse = $this->réponse_json(["erreur" => $validateur->errors()], 400);
				} else {
					$commentaireInt = new SauvegarderCommentaireInt();
					$commentaire = $commentaireInt->sauvegarder_commentaire(
						$username,
						$question_uriDécodé,
						null,
						new Commentaire(
							$request->message,
							$user,
							(new \DateTime())->getTimestamp(),
							$request->numéro_ligne,
						),
					);

					$id = array_key_first($commentaire);
					$réponse = $this->valider_et_préparer_réponse(
						$commentaire[$id],
						$username,
						$question_uri,
						$timestamp,
						$id,
					);
				}
			}
		}

		Log::debug("CommentaireCtl.post. Retour : ", [$réponse]);
		return $réponse;
	}

	/**
	 * @return array<string>
	 */
	public static function get_liens(string $id, int $numéro, Commentaire $commentaire): array
	{
		$urlBase = Contrôleur::$urlBase;

		return [
			"self" => "{$urlBase}/commentaire/{$id}/{$numéro}",
			"auteur" => "{$urlBase}/user/{$commentaire->créateur->username}",
			"tentative" => "{$urlBase}/tentative/{$id}",
		];
	}

	private function valider_paramètres($request)
	{
		return Validator::make(
			$request->all(),
			[
				"message" => "required",
				"créateur" => "string",
				"numéro_ligne" => ["required", "integer"],
			],
			[
				"required" => "Le champ :attribute est obligatoire.",
				"integer" => "Le champ :attribute doit être un entier.",
			],
		);
	}

	private function valider_et_préparer_réponse(
		Commentaire $commentaire,
		string $username,
		string $question_uri,
		int $timestamp,
		int $numéro,
	): JsonResponse {
		Log::debug("CommentaireCtl.valider_et_préparer_réponse. Params : ", [
			$commentaire,
			$username,
			$question_uri,
			$timestamp,
		]);

		$dto = new GénériqueDTO(
			id: "{$username}/{$question_uri}/{$timestamp}/{$numéro}",
			objet: $commentaire,
			liens: CommentaireCtl::get_liens("{$username}/{$question_uri}/{$timestamp}", $numéro, $commentaire),
		);

		$réponse = $this->item($dto, new CommentaireTransformer());

		$réponse = $this->préparer_réponse($réponse);

		Log::debug("CommentaireCtl.valider_et_préparer_réponse. Retour : ", [$réponse]);

		return $réponse;
	}
}
