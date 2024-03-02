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

class ChargeurFactory
{
	private static $laFactory = null;

	private function __construct()
	{
	}

	static function get_instance()
	{
		if (ChargeurFactory::$laFactory == null) {
			ChargeurFactory::$laFactory = new ChargeurFactory();
		}
		return ChargeurFactory::$laFactory;
	}

	static function set_instance($uneFactory)
	{
		ChargeurFactory::$laFactory = $uneFactory;
	}

	function get_chargeur_http()
	{
		return new ChargeurHTTP($this);
	}

	function get_chargeur_git(): ChargeurGIT
	{
		return new ChargeurGIT($this);
	}

	function get_chargeur_question_fichier()
	{
		return new ChargeurQuestionFichier($this);
	}

	function get_chargeur_question_archive()
	{
		return new ChargeurQuestionArchive($this);
	}

	function get_chargeur_question_http()
	{
		return new ChargeurQuestionHTTP($this);
	}

	function get_chargeur_question_git(): ChargeurQuestionGIT
	{
		return new ChargeurQuestionGIT($this);
	}
}
