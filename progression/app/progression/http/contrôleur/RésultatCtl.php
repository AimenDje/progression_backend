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

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use progression\http\transformer\RésultatTransformer;
use progression\http\transformer\dto\GénériqueDTO;
use progression\domaine\entité\question\{Question, QuestionProg, QuestionSys};
use progression\domaine\entité\{Résultat, Test, TestProg, TentativeProg, TentativeSys};
use progression\domaine\interacteur\{
	ObtenirQuestionInt,
	ObtenirTentativeInt,
	SoumettreTentativeProgInt,
	SoumettreTentativeSysInt,
	SoumettreTentativeIntéracteurException,
	IntéracteurException,
};
use progression\util\Encodage;
use RuntimeException;
use DomainException;
use progression\dao\question\ChargeurException;
use progression\dao\exécuteur\ExécutionException;

class RésultatCtl extends Contrôleur
{
	public function post(Request $request, string $uri): JsonResponse
	{
		Log::debug("RésultatCtl.post. Params : ", [$request->all()]);

		$chemin = Encodage::base64_decode_url($uri);
		$question = $this->récupérer_question($chemin);

		$validation = null;
		if ($question instanceof QuestionProg) {
			$validation = $this->valider_paramètres_prog($request, $uri);
		} elseif ($question instanceof QuestionSys) {
			$validation = $this->valider_paramètres_sys($request);
		}

		if ($validation && $validation->fails()) {
			Log::notice(
				"({$request->ip()}) - {$request->method()} {$request->path()} (" . __CLASS__ . ") Paramètres invalides",
			);
			return $this->réponse_json(["erreur" => $validation->errors()], 400);
		}

		if (!$question) {
			$réponse = $this->réponse_json(["erreur" => "La question " . $chemin . " n'existe pas."], 404);
		} elseif (isset($request->index) && !array_key_exists($request->index, $question->tests)) {
			$réponse = $this->réponse_json(["erreur" => "L'indice de test n'existe pas."], 400);
		} else {
			$résultat = $this->traiter_post_Question($request, $chemin, $question);

			if (!$résultat) {
				$réponse = $this->réponse_json(["erreur" => "La tentative n'est pas traitable."], 400);
			} else {
				$id = array_key_first($résultat);
				$réponse = $this->valider_et_préparer_réponse($résultat[$id], $id);
			}
		}

		Log::debug("RésultatCtl.post. Retour : ", [$réponse]);
		return $réponse;
	}

	/**
	 * @return array<string>
	 */
	public static function get_liens(string $hash): array
	{
		$urlBase = Contrôleur::$urlBase;
		return [
			"self" => "{$urlBase}/resultat/{$hash}",
		];
	}

	private function valider_paramètres_prog(Request $request, string $uri)
	{
		$TAILLE_CODE_MAX = (int) config("limites.taille_code");

		$validateur = Validator::make(
			[...$request->all(), "uri" => $uri],
			[
				"uri" => [
					"required",
					function ($attribute, $value, $fail) {
						$url = Encodage::base64_decode_url($value);
						if (!$url || Validator::make(["uri" => $url], ["uri" => "url"])->fails()) {
							$fail("Le champ uri doit être un URL encodé en base64.");
						}
					},
				],
				"code" => "required|string|between:0,$TAILLE_CODE_MAX",
				"langage" => "required|string",
				"test" => "required_without:index",
				"index" => "integer",
			],
			[
				"required" => "Le champ :attribute est obligatoire.",
				"code.between" => "Le code soumis " . mb_strlen($request->code) . " > $TAILLE_CODE_MAX caractères.",
				"question_uri.required" => "Le champ question_uri est obligatoire.",
				"test.required_without" => "Le champ test est obligatoire lorsque index n'est pas présent.",
			],
		);

		return $validateur;
	}

	private function valider_paramètres_sys(Request $request)
	{
		$validateur = Validator::make(
			$request->all(),
			[
				"index" => "required|integer",
			],
			[
				"required" => "Le champ :attribute est obligatoire.",
				"index.integer" => "Le champ index doit être un entier.",
			],
		);

		return $validateur;
	}

	private function récupérer_question(string $chemin): Question|null
	{
		return (new ObtenirQuestionInt())->get_question($chemin);
	}

	private function construire_test(Request $request, TestProg $test): TestProg
	{
		$test->entrée = $request->test["entrée"] ?? $test->entrée;
		$test->params = $request->test["params"] ?? $test->params;
		$test->sortie_attendue = $request->test["sortie_attendue"] ?? $test->sortie_attendue;

		return $test;
	}

	private function valider_et_préparer_réponse(Résultat $résultat, string $hash): JsonResponse
	{
		Log::debug("RésultatCtl.valider_et_préparer_réponse. Params : ", [$résultat]);

		$dto = new GénériqueDTO(id: "{$hash}", objet: $résultat, liens: RésultatCtl::get_liens($hash));

		$réponse = $this->item($dto, new RésultatTransformer());

		$réponse = $this->préparer_réponse($réponse);

		Log::debug("RésultatCtl.valider_et_préparer_réponse. Retour : ", [$réponse]);
		return $réponse;
	}

	/**
	 * @return array<Résultat>
	 */
	private function traiter_post_Question(Request $request, string $chemin, Question $question): array|null
	{
		if ($question instanceof QuestionProg) {
			/**
			 * @var TestProg $test_question (pseudo-cast pour phpstant)
			 */
			$test_question = isset($request->index) ? $question->tests[$request->index] : new TestProg("", "");
			$test = isset($request->test) ? $this->construire_test($request, $test_question) : $test_question;

			return $this->traiter_post_QuestionProg($request, $question, $test);
		} elseif ($question instanceof QuestionSys) {
			return $this->traiter_post_QuestionSys($request, $chemin, $question);
		} else {
			return null;
		}
	}

	/**
	 * @return array<Résultat>
	 */
	private function traiter_post_QuestionProg(Request $request, QuestionProg $question, Test $test): array|null
	{
		$tentative = new TentativeProg($request->langage, $request->code, (new \DateTime())->getTimestamp());

		$tentative_résultante = $this->soumettre_tentative_prog($question, $tentative, $test);

		if (!$tentative_résultante || count($tentative_résultante->résultats) < 1) {
			return null;
		}

		$résultats = $tentative_résultante->résultats;
		$hash = array_key_first($résultats);
		$résultat = $résultats[$hash];

		if ($test->caché) {
			$résultat = $this->caviarder_résultat($résultat);
		}

		return [$hash => $résultat];
	}

	/**
	 * @return array<Résultat>
	 */
	private function traiter_post_QuestionSys(Request $request, string $chemin, QuestionSys $question): array|null
	{
		/* @phpstan-ignore-next-line */
		$utilisateur_courant = auth()->user()->username;
		$dernière_tentative = (new ObtenirTentativeInt())->get_dernière($utilisateur_courant, $chemin);
		if (!$dernière_tentative instanceof TentativeSys) {
			return null;
		}

		$tentative = new TentativeSys(
			date_soumission: (new \DateTime())->getTimestamp(),
			conteneur_id: $dernière_tentative->conteneur_id,
		);

		$test_index = $request->index;

		$tentative_résultante = $this->soumettre_tentative_sys($question, $tentative, $test_index);

		if (count($tentative_résultante->résultats) <= $test_index) {
			return null;
		}

		if ($question->tests[$test_index]->caché) {
			$résultat = $this->caviarder_résultat($tentative_résultante->résultats[$test_index]);
		}

		return [$test_index => $tentative_résultante->résultats[$test_index]];
	}

	private function soumettre_tentative_prog(
		QuestionProg $question,
		TentativeProg $tentative,
		Test $test,
	): TentativeProg|null {
		$intéracteur = new SoumettreTentativeProgInt();
		return $intéracteur->soumettre_tentative($question, $tentative, [$test]);
	}

	private function soumettre_tentative_sys(
		QuestionSys $question,
		TentativeSys $tentative,
		int|null $test_index,
	): TentativeSys {
		$intéracteur = new SoumettreTentativeSysInt();
		return $intéracteur->soumettre_tentative($question, $tentative, $question->tests, $test_index);
	}

	private function caviarder_résultat(Résultat $résultat): Résultat
	{
		$résultat->sortie_observée = null;
		$résultat->sortie_erreur = null;

		return $résultat;
	}
}
