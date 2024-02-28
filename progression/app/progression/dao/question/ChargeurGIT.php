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

use RuntimeException, ErrorException;

class ChargeurGIT extends Chargeur
{
	public static function cloner_depot($url_du_depot)
	{
		// Cloner le dépôt Git temporairement
		$dossier_temporaire = sys_get_temp_dir() . "/" . uniqid("git_repo_");

		// Cloner le dépôt dans le dossier temporaire
		exec("git clone --depth 1 $url_du_depot $dossier_temporaire");

		// Vérifier si le clonage a réussi
		if (!is_dir($dossier_temporaire)) {
			throw new RuntimeException("Le clonage du dépôt a échoué");
		}

		return $dossier_temporaire;
	}
}
