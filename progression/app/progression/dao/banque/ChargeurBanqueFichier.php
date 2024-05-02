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

namespace progression\dao\banque;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;
use DomainException, RuntimeException;
use progression\dao\chargeur\{Chargeur, ChargeurException};

class ChargeurBanqueFichier extends Chargeur
{
	/**
	 * @return array<string>
	 */
	public function récupérer_fichier(string $uri): array
	{
		$output = null;
		$err_code = null;

		$scheme = parse_url($uri, PHP_URL_SCHEME);

		if ($scheme == "file") {
			// Pour fins de tests seulement.
			if (App::environment() != "local") {
				throw new \Error("ChargeurBanqueFichier : Pour fins de tests seulement.");
			}
			$uri = preg_replace("/^file:\/\//", "", $uri);
			if ($uri == null) {
				throw new \Error("Erreur ${uri}");
			}

			$output = file_get_contents($uri);
		} elseif ($scheme == "http" || $scheme == "https") {
			$ressource = fopen($uri, "r");
			if ($ressource === false) {
				throw new ChargeurException("La ressource {$uri} n'est pas lisible.");
			}
			$output = stream_get_contents($ressource);
		}

		if ($output === false || $output === null) {
			throw new ChargeurException("Le fichier {$uri} n'est pas lisible.");
		}

		$info = yaml_parse($output);
		if ($info === false) {
			throw new RuntimeException("Le fichier {$uri} ne peut pas être décodé. Le format produit est invalide.");
		}

		return $info;
	}
}
