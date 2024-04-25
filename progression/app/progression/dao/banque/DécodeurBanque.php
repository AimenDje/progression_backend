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

use progression\domaine\entité\banque\Banque;

class DécodeurBanque
{
	/**
	 * @param array<mixed> $infos_banque
	 */
	public static function load(Banque $banque, array $infos_banque): Banque
	{
		$banque->nom = $infos_banque["nom"] ?? null;
		$banque->url = $infos_banque["url"] ?? null;

		return $banque;
	}
}
