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
use progression\domaine\entité\question\{QuestionProg, QuestionSys, QuestionBD};
use progression\domaine\entité\user\User;
use progression\domaine\interacteur\{ObtenirQuestionInt, IntéracteurException};
use progression\http\transformer\{QuestionProgTransformer, QuestionSysTransformer};
use progression\http\transformer\dto\{QuestionDTO, QuestionProgDTO};
use progression\util\Encodage;
use progression\dao\question\ChargeurException;

class QuestionCtl extends Contrôleur
{
	public function get(Request $request, $uri)
	{
		Log::debug("QuestionCtl.get. Params : ", [$request->all(), $uri]);

		$réponse = null;
		$question = $this->obtenir_question($uri);
		$réponse = $this->valider_et_préparer_réponse($question, $uri, $request->user("api"));

		Log::debug("QuestionCtl.get. Retour : ", [$réponse]);

		return $réponse;
	}

	/**
	 * @return array<string>
	 */
	public static function get_liens(string $question_uri, User|null $user = null): array
	{
		$urlBase = Contrôleur::$urlBase;

		$liens = [
			"self" => "$urlBase/question/$question_uri",
			"résultats" => "$urlBase/question/$question_uri/resultats",
		];

		if ($user) {
			$liens["avancement"] = "$urlBase/avancement/{$user->username}/$question_uri";
		}

		return $liens;
	}

	private function obtenir_question($question_uri)
	{
		Log::debug("QuestionCtl.obtenir_question. Params : ", [$question_uri]);

		$chemin = Encodage::base64_decode_url($question_uri);

		$questionInt = new ObtenirQuestionInt();
		$question = $questionInt->get_question($chemin);

		Log::debug("Question.Ctl.obtenir_question. Retour : ", [$question]);
		return $question;
	}

	private function valider_et_préparer_réponse($question, $uri, User|null $user)
	{
		Log::debug("QuestionCtl.valider_et_préparer_réponse. Params : ", [$question]);

		if ($question === null) {
			$réponse = $this->préparer_réponse(null);
		} elseif ($question instanceof QuestionProg) {
			$dto = new QuestionProgDTO(id: $uri, objet: $question, liens: QuestionCtl::get_liens($uri, $user));
			$réponse_array = $this->item($dto, new QuestionProgTransformer());
			$réponse = $this->préparer_réponse($réponse_array);
		} elseif ($question instanceof QuestionSys) {
			$dto = new QuestionDTO(id: $uri, objet: $question, liens: QuestionCtl::get_liens($uri, $user));
			$réponse_array = $this->item($dto, new QuestionSysTransformer());
			$réponse = $this->préparer_réponse($réponse_array);
		} elseif ($question instanceof QuestionBD) {
			$réponse = $this->réponse_json(["erreur" => "QuestionBD pas encore implémentée"], 501);
		} else {
			$réponse = $this->réponse_json(["erreur" => "Type de question inconnu."], 400);
		}

		Log::debug("QuestionCtl.valider_et_préparer_réponse. Retour : ", [$réponse]);
		return $réponse;
	}
}
