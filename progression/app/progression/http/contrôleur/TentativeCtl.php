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

use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use progression\http\transformer\{TentativeProgTransformer, TentativeSysTransformer, TentativeBDTransformer};
use progression\domaine\interacteur\{
	ObtenirAvancementInt,
	ObtenirTentativeInt,
	ObtenirQuestionInt,
	SauvegarderAvancementInt,
	SauvegarderTentativeInt,
	SoumettreTentativeProgInt,
	SoumettreTentativeSysInt,
	TerminerConteneurSysInt,
};
use progression\http\transformer\dto\TentativeDTO;
use progression\domaine\entité\{Avancement, Tentative, TentativeProg, TentativeSys, TentativeBD, Résultat};
use progression\domaine\entité\question\{Question, QuestionProg, QuestionSys, QuestionBD};
use progression\domaine\entité\TestProg;
use progression\util\Encodage;
use Carbon\Carbon;

class TentativeCtl extends Contrôleur
{
	public function get(Request $request, string $username, string $question_uri, int $timestamp): JsonResponse
	{
		$tentative = $this->obtenir_tentative($username, $question_uri, $timestamp);

		$réponse = null;
		if ($tentative) {
			$dto = new TentativeDTO(
				id: "{$username}/{$question_uri}/{$timestamp}",
				objet: $tentative,
				liens: TentativeCtl::get_liens("{$username}/{$question_uri}", $timestamp),
			);

			if ($tentative instanceof TentativeProg) {
				$réponse = $this->item($dto, new TentativeProgTransformer());
			} elseif ($tentative instanceof TentativeSys) {
				$réponse = $this->item($dto, new TentativeSysTransformer());
			} elseif ($tentative instanceof TentativeBD) {
				$réponse = $this->item($dto, new TentativeBDTransformer());
			}
		}

		return $this->préparer_réponse($réponse);
	}

	public function post(Request $request, $username, $question_uri)
	{
		Log::debug("TentativeCtl.post. Params : ", [$request->all(), $username]);

		$chemin = Encodage::base64_decode_url($question_uri);

		$question = $this->récupérer_question($chemin);

		if ($question instanceof Question) {
			if ($question instanceof QuestionProg) {
				$validation = $this->valider_paramètres_prog($request);
				if ($validation->fails()) {
					$réponse = $this->réponse_json(["erreur" => $validation->errors()], 400);
				} else {
					$réponse = $this->traiter_post_QuestionProg($request, $username, $chemin, $question);
				}
			} elseif ($question instanceof QuestionSys) {
				$réponse = $this->traiter_post_QuestionSys($request, $username, $chemin, $question);
			} else {
				Log::notice("({$request->ip()}) - {$request->method()} {$request->path()} (" . __CLASS__ . ")");
				$réponse = $this->réponse_json(["erreur" => "Question de type non implémentée."], 501);
			}
		} else {
			$réponse = $this->réponse_json(["erreur" => "Question inexistante."], 400);
		}

		Log::debug("TentativeCtl.post. Retour : ", [$réponse]);
		return $réponse;
	}

	/**
	 * @return array<string>
	 */
	public static function get_liens(string $id, int $date_soumission): array
	{
		$urlBase = Contrôleur::$urlBase;

		return [
			"self" => "{$urlBase}/tentative/{$id}/{$date_soumission}",
			"avancement" => "{$urlBase}/avancement/{$id}",
		];
	}

	private function obtenir_tentative(string $username, string $question_uri, int $timestamp): Tentative|null
	{
		Log::debug("TentativeCtl.obtenir_tentative. Params : ", [$username, $question_uri, $timestamp]);

		$chemin = Encodage::base64_decode_url($question_uri);

		$tentativeInt = new ObtenirTentativeInt();
		$tentative = $tentativeInt->get_tentative($username, $chemin, $timestamp, $this->get_includes());

		Log::debug("TentativeCtl.obtenir_tentative. Retour : ", [$tentative]);
		return $tentative;
	}

	private function traiter_post_QuestionProg(Request $request, $username, $chemin, $question)
	{
		Log::debug("TentativeCtl.traiter_post_QuestionProg. Params : ", [$request->all(), $username]);

		$tests = $question->tests;

		$timestamp = Carbon::now()->getTimestamp();
		$tentative = new TentativeProg($request->langage, $request->code, $timestamp);

		$tentative_résultante = $this->soumettre_tentative_prog($question, $tentative, $tests);
		if (!$tentative_résultante) {
			return $this->réponse_json(["erreur" => "Tentative intraitable."], 400);
		}

		$this->sauvegarder_tentative_et_avancement($username, $chemin, $question, $tentative_résultante);

		$tentative_résultante = $this->caviarder_résultats_des_tests_cachés($tentative_résultante, $tests);

		$id = $tentative_résultante->date_soumission;
		$question_uri = Encodage::base64_encode_url($chemin);

		$dto = new TentativeDTO(
			id: "{$username}/{$question_uri}/{$id}",
			objet: $tentative_résultante,
			liens: TentativeCtl::get_liens("{$username}/{$question_uri}", $id),
		);

		$réponse = $this->item($dto, new TentativeProgTransformer());

		Log::debug("TentativeCtl.traiter_post_QuestionProg. Retour : ", [$réponse]);

		return $this->préparer_réponse($réponse);
	}

	private function traiter_post_QuestionSys(Request $request, $username, $chemin, $question)
	{
		Log::debug("TentativeCtl.traiter_post_QuestionSys. Params : ", [$request->all(), $username]);

		if (!$question->solution && !$request->conteneur_id) {
			$this->détruire_conteneur_courant($username, $chemin);
			$conteneur_id = "";
		} else {
			$conteneur_id = $request->conteneur_id;
		}

		$timestamp = Carbon::now()->getTimestamp();
		$tentative = new TentativeSys(
			conteneur_id: $conteneur_id,
			réponse: $request->réponse,
			date_soumission: $timestamp,
		);

		$tentative_résultante = $this->soumettre_tentative_sys($question, $tentative, $question->tests);
		if (!$tentative_résultante) {
			return $this->réponse_json(["erreur" => "Tentative intraitable."], 400);
		}
		$id = $tentative_résultante->date_soumission;

		$this->sauvegarder_tentative_et_avancement($username, $chemin, $question, $tentative_résultante);

		$question_uri = Encodage::base64_encode_url($chemin);

		$dto = new TentativeDTO(
			id: "{$username}/{$question_uri}/{$id}",
			objet: $tentative_résultante,
			liens: TentativeCtl::get_liens("{$username}/{$question_uri}", $id),
		);

		$réponse = $this->item($dto, new TentativeSysTransformer());

		Log::debug("TentativeCtl.traiter_post_QuestionSys. Retour : ", [$réponse]);

		return $this->préparer_réponse($réponse);
	}

	private function valider_paramètres_prog($request)
	{
		$TAILLE_CODE_MAX = (int) config("limites.taille_code");

		return Validator::make(
			$request->all(),
			[
				"langage" => "required|string",
				"code" => "required|string|between:0,$TAILLE_CODE_MAX",
			],
			[
				"required" => "Le champ :attribute est obligatoire.",
				"string" => "Le champ :attribute doit être une chaîne de caractères.",
				"code.between" => "Le code soumis " . mb_strlen($request->code) . " > :max caractères.",
			],
		);
	}

	private function récupérer_question($chemin)
	{
		$questionInt = new ObtenirQuestionInt();

		return $questionInt->get_question($chemin);
	}

	private function construire_test($test, string|null $entrée, string|null $params, string|null $sortie_attendue)
	{
		if ($entrée !== null) {
			$test->entrée = $entrée;
		}
		if ($params !== null) {
			$test->params = $params;
		}
		if ($sortie_attendue !== null) {
			$test->sortie_attendue = $sortie_attendue;
		}

		return $test;
	}

	/**
	 * @return array<mixed>
	 */
	private function détruire_conteneur_courant(string $username, string $chemin): array
	{
		Log::debug("TentativeCtl.détruire_conteneur_courant. Params ${username} ${chemin}");
		$conteneur_id = $this->récupérer_conteneur_id($username, $chemin);

		$réponse = (new TerminerConteneurSysInt())->terminer($conteneur_id);
		Log::debug("TentativeCtl.détruire_conteneur_courant. Retour", [$réponse]);
		return $réponse;
	}

	private function soumettre_tentative_prog($question, $tentative, $tests)
	{
		return (new SoumettreTentativeProgInt())->soumettre_tentative($question, $tentative, $tests);
	}

	private function soumettre_tentative_sys($question, $tentative, $tests)
	{
		return (new SoumettreTentativeSysInt())->soumettre_tentative($question, $tentative, $tests);
	}

	private function sauvegarder_tentative_et_avancement($username, $chemin, $question, $tentative)
	{
		$sauvegardeTentativeInt = new SauvegarderTentativeInt();
		$sauvegardeTentativeInt->sauvegarder($username, $chemin, $tentative);

		$avancementInt = new ObtenirAvancementInt();
		$avancement =
			$avancementInt->get_avancement($username, $chemin) ??
			new Avancement(tentatives: [], titre: $question->titre, niveau: $question->niveau);

		$avancement->ajouter_tentative($tentative);

		$avancementInt = new SauvegarderAvancementInt();
		$avancementInt->sauvegarder($username, $chemin, $avancement, $question);
	}

	/**
	 * @param array<TestProg> $tests
	 */
	private function caviarder_résultats_des_tests_cachés(TentativeProg $tentative, array $tests): TentativeProg
	{
		foreach ($tests as $i => $test) {
			$hash = array_keys($tentative->résultats)[$i];

			if ($tests[$i]->caché) {
				$this->caviarder_résultat($tentative->résultats[$hash]);
			}
		}

		return $tentative;
	}

	private function caviarder_résultat(Résultat $résultat): Résultat
	{
		$résultat->sortie_observée = null;
		$résultat->sortie_erreur = null;

		return $résultat;
	}

	private function récupérer_conteneur_id(string $username, string $chemin): string
	{
		Log::debug("TentativeCtl.récupérer_conteneur_id. Params : ", [$username, $chemin]);

		$obtenirTentativeInt = new ObtenirTentativeInt();
		$tentative_récupérée = $obtenirTentativeInt->get_dernière($username, $chemin);
		if (!$tentative_récupérée instanceof TentativeSys) {
			return "";
		}

		$conteneur_id = $tentative_récupérée?->conteneur_id ?? "";

		Log::debug("TentativeCtl.récupérer_conteneur_id. Retour : ", [$conteneur_id]);
		return $conteneur_id;
	}
}
