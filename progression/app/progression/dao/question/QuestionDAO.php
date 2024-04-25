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

namespace progression\dao\question;

use DomainException;
use BadMethodCallException;
use progression\dao\EntitéDAO;
use progression\dao\chargeur\ChargeurFactory;
use progression\domaine\entité\question\{QuestionProg, QuestionSys};

class QuestionDAO extends EntitéDAO
{
	public function get_question($uri)
	{
		$infos_question = ChargeurFactory::get_instance()->get_chargeur()->récupérer_fichier($uri);

		$type = $infos_question["type"] ?? ($type = "prog");
		if ($type == "prog") {
			return DécodeurQuestionProg::load(new QuestionProg(), $infos_question);
		} elseif ($type == "sys") {
			return DécodeurQuestionSys::load(new QuestionSys(), $infos_question);
		} else {
			throw new DomainException("Le fichier ne peut pas être décodé. Type inconnu");
		}
	}
}
