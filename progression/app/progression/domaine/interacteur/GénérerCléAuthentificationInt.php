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

use progression\domaine\entité\clé\{Clé, Portée};
use Carbon\Carbon;

class GénérerCléAuthentificationInt extends Interacteur
{
	/**
	 * @return array<Clé>
	 */
	public function générer_clé($username, $nom, $expiration = 0): array
	{
		if (empty($nom)) {
			throw new RessourceInvalideException("Le nom ne peut être vide");
		}
		if (empty($username)) {
			throw new RessourceInvalideException("Le nom d'utilisateur ne peut être vide");
		}

		$dao = $this->source_dao->get_clé_dao();

		if ($dao->get_clé($username, $nom)) {
			throw new DuplicatException("Une clé avec le même nom existe déjà");
		}

		$secret = bin2hex(random_bytes(20));
		$clé = new Clé($secret, Carbon::now()->getTimestamp(), $expiration, Portée::AUTH);

		return $dao->save($username, $nom, $clé);
	}
}
