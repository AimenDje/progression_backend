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

namespace progression\domaine\interacteur;

use progression\domaine\entité\{Avancement, Tentative};
use progression\domaine\entité\question\{Question, QuestionProg, QuestionSys, QuestionBD};
use progression\domaine\entité\user\User;
use progression\dao\DAOException;

class SauvegarderAvancementInt extends Interacteur
{
	public function sauvegarder(
		string $username,
		string $question_uri,
		Avancement $avancement,
		Question $question = null,
	): Avancement|null {
		try {
			$question = $question ?? $this->source_dao->get_question_dao()->get_question($question_uri);
		} catch (DAOException $e) {
			throw new IntéracteurException($e, 503);
		}

		if (!$question) {
			return null;
		}

		$avancement->titre = $question->titre;
		$avancement->niveau = $question->niveau;

		if ($question instanceof QuestionProg) {
			$type = "prog";
		} elseif ($question instanceof QuestionSys) {
			$type = "sys";
		} elseif ($question instanceof QuestionBD) {
			$type = "bd";
		} else {
			$type = "inconnu";
		}

		try {
			$dao_avancement = $this->source_dao->get_avancement_dao();
		} catch (DAOException $e) {
			throw new IntéracteurException($e, 503);
		}
		return $dao_avancement->save($username, $question_uri, $type, $avancement);
	}
}
