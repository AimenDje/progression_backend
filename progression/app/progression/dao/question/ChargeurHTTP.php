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

use progression\dao\DAOException;
use ErrorException;

class ChargeurHTTP extends Chargeur
{
	public function get_url($url)
	{
		return @file_get_contents($url);
	}

	public function get_entêtes($url)
	{
		$opts = [
			"http" => [
				"follow_location" => 1,
			],
		];
		$context = stream_context_create($opts);
		try {
			$entêtes = get_headers($url, true, $context);
		} catch (ErrorException $erreur) {
			throw new DAOException("Impossible de récupérer la question");
		}

		if ($entêtes === false) {
			throw new DAOException("Impossible de récupérer les entêtes de l'URL {$url}");
		}

		return $entêtes;
	}
}
