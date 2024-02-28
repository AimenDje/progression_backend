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

namespace progression\domaine\entité;

use progression\domaine\entité\user\User;

class Commentaire
{
	public string $message;
	public User $créateur;
	public int $date;
	public int $numéro_ligne;

	public function __construct(string $message, User $créateur, int $date, int $numéro_ligne)
	{
		$this->message = $message;
		$this->créateur = $créateur;
		$this->date = $date;
		$this->numéro_ligne = $numéro_ligne;
	}
}
