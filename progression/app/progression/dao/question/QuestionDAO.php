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
use progression\domaine\entité\question\{QuestionProg, QuestionSys};
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class QuestionDAO extends EntitéDAO
{
	public function get_question($uri)
	{
		$scheme = parse_url($uri, PHP_URL_SCHEME);
		$extension = pathinfo($uri, PATHINFO_EXTENSION);

		if ($scheme == "file") {
			$chargeur = ChargeurFactory::get_instance()->get_chargeur_question_fichier();
		} elseif ($extension == "git") {
			$chargeur = ChargeurFactory::get_instance()->get_chargeur_question_git();
		} elseif ($scheme == "https") {
			$chargeur = ChargeurFactory::get_instance()->get_chargeur_question_http();
		} else {
			throw new BadMethodCallException("Schéma d'URI invalide");
		}

		$donnéesRécupérées = Cache::get(md5($uri));

		if ($donnéesRécupérées && isset($donnéesRécupérées["contenu"])) {
			$is_changed = $chargeur->est_modifié($uri, $donnéesRécupérées["cléModification"]);
			Log::debug("Valeur de is_changed :" . ($is_changed ? "true" : "false"));
			if ($is_changed) {
				// Cache::forget($donnéesRécupérées);
				$infos_question = $chargeur->récupérer_question($uri);
			} else {
				$infos_question = $donnéesRécupérées["contenu"];
			}
		} else {
			$infos_question = $chargeur->récupérer_question($uri);
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
