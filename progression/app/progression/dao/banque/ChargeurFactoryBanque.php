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
use progression\dao\question\ChargeurHTTP;


class ChargeurFactoryBanque
{
	private static $laFactory = null;

	private function __construct()
	{
	}

	static function get_instance()
	{
		if (ChargeurFactoryBanque::$laFactory == null) {
			ChargeurFactoryBanque::$laFactory = new ChargeurFactoryBanque();
		}
		return ChargeurFactoryBanque::$laFactory;
	}

	static function set_instance($uneFactory)
	{
		ChargeurFactoryBanque::$laFactory = $uneFactory;
	}

	function get_chargeur_http()
	{
		return new ChargeurHTTP($this);
	}

	function get_chargeur_banque_fichier()
	{
		return new ChargeurBanqueFichier($this);
	}

	function get_chargeur_banque_archive()
	{
		return new ChargeurBanqueArchive($this);
	}

	function get_chargeur_banque_http()
	{
		return new ChargeurBanqueHTTP($this);
	}

	function get_chargeur_banque_question()
	{
		return new ChargeurBanqueQuestion($this);
	}
}
