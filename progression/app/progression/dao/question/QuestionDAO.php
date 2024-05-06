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
use Illuminate\Support\Facades\Cache;

class QuestionDAO extends EntitéDAO
{
	public function get_question($uri)
	{
		$scheme = parse_url($uri, PHP_URL_SCHEME);
		$path = parse_url($uri, PHP_URL_PATH);
		$extension = pathinfo($path ?: "", PATHINFO_EXTENSION);

		if ($scheme == "file") {
			$chargeur = ChargeurFactory::get_instance()->get_chargeur_question_fichier();
		} elseif ($extension == "git") {
			$chargeur = ChargeurFactory::get_instance()->get_chargeur_question_git();
		} elseif ($scheme == "https") {
			$chargeur = ChargeurFactory::get_instance()->get_chargeur_question_http();
		} else {
			throw new BadMethodCallException("Schéma d'URI invalide");
		}

		$infos_question = null;

		$id_modif = $chargeur->id_modif($uri);
		if ($id_modif !== false) {
			$infos_question = Cache::get($id_modif);
		}

		if (!$infos_question) {
			$infos_question = $chargeur->récupérer_fichier($uri);
			Cache::put($id_modif, $infos_question);
		}

		if ($infos_question === null) {
			return null;
		}

		$type = $infos_question["type"] ?? "prog";
		if ($type == "prog") {
			return DécodeurQuestionProg::load(new QuestionProg(), $infos_question);
		} elseif ($type == "sys") {
			return DécodeurQuestionSys::load(new QuestionSys(), $infos_question);
		} else {
			throw new DomainException("Type de question inconnu ou non pris en charge");
		}
	}
}
