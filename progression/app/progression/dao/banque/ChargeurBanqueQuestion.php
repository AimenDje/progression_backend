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

use BadMethodCallException;

class ChargeurBanqueQuestion extends ChargeurBanque
{
	public function récupérer_banque($uri)
	{
		$scheme = parse_url(strtolower($uri), PHP_URL_SCHEME);

		if ($scheme == "file") {
			$sortie = $this->source->get_chargeur_banque_fichier()->récupérer_banque($uri);
		} elseif ($scheme == "https") {
			$sortie = $this->source->get_chargeur_banque_http()->récupérer_banque($uri);
		} else {
			throw new BadMethodCallException("Schéma d'URI invalide");
		}

		$sortie["uri"] = $uri;

		return $sortie;
	}
}
