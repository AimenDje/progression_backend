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

use progression\dao\DAOFactory;

class Interacteur
{
	protected DAOFactory $source_dao;

	function __construct($sourceDAO = null)
	{
		if ($sourceDAO == null) {
			$this->source_dao = DAOFactory::getInstance();
		} else {
			$this->source_dao = $sourceDAO;
		}
	}

	/**
	 * @param array<mixed> $array
	 */
	public static function premier_élément(array $array): mixed
	{
		if (count($array) == 0) {
			return null;
		}
		return array_values($array)[0];
	}
}
