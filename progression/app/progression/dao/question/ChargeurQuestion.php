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

use BadMethodCallException;

class ChargeurQuestion extends Chargeur
{
	public function récupérer_question($uri)
	{
		$scheme = parse_url(strtolower($uri), PHP_URL_SCHEME);
		$extension = pathinfo($uri, PATHINFO_EXTENSION);

		if ($scheme == "file") {
			$sortie = $this->source->get_chargeur_question_fichier()->récupérer_question($uri);
		} elseif ($extension == "git") {
			$sortie = $this->source->get_chargeur_question_git()->récupérer_question($uri);
		} elseif ($scheme == "https") {
			$sortie = $this->source->get_chargeur_question_http()->récupérer_question($uri);
		} else {
			throw new BadMethodCallException("Schéma d'URI invalide");
		}

		$sortie["uri"] = $uri;

		return $sortie;
	}
}
