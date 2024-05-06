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

namespace progression\dao\chargeur;

use progression\dao\question\{ChargeurQuestionFichier, ChargeurQuestionGit};

class ChargeurFactory
{
	private static ChargeurFactory|null $laFactory = null;

	private Chargeur $chargeur_fichier;
	private ChargeurArchive $chargeur_archive;

	private function __construct()
	{
		$this->chargeur_fichier = new ChargeurQuestionFichier($this);
		$this->chargeur_archive = new ChargeurArchive($this);
	}

	static function get_instance(): ChargeurFactory
	{
		if (ChargeurFactory::$laFactory == null) {
			ChargeurFactory::$laFactory = new ChargeurFactory();
		}
		return ChargeurFactory::$laFactory;
	}

	static function set_instance(ChargeurFactory|null $uneFactory): void
	{
		if ($uneFactory == null) {
			ChargeurFactory::$laFactory = new ChargeurFactory();
		} else {
			ChargeurFactory::$laFactory = $uneFactory;
		}
	}

	function get_chargeur(): Chargeur
	{
		return new Chargeur($this);
	}

	function get_chargeur_ressource_http(): ChargeurRessourceHTTP
	{
		return new ChargeurRessourceHTTP($this);
	}

	function get_chargeur_http(): ChargeurHTTP
	{
		return new ChargeurHTTP($this);
	}

	function get_chargeur_question_fichier(): ChargeurQuestionFichier
	{
		return new ChargeurQuestionFichier($this);
	}

	function get_chargeur_question_archive(): ChargeurArchive
	{
		return new ChargeurArchive($this);
	}

	function get_chargeur_question_http(): ChargeurRessourceHTTP
	{
		return new ChargeurRessourceHTTP($this);
	}

	function get_chargeur_banque_http(): ChargeurRessourceHTTP
	{
		return new ChargeurRessourceHTTP($this);
	}

	function get_chargeur_fichier(): Chargeur
	{
		return $this->chargeur_fichier;
	}

	function get_chargeur_archive(): ChargeurArchive
	{
		return $this->chargeur_archive;
	}

	function get_chargeur_question_git(): ChargeurQuestionGit
	{
		return new ChargeurQuestionGit($this);
	}

	function set_chargeur_fichier(Chargeur $chargeur): void
	{
		$this->chargeur_fichier = $chargeur;
	}

	function set_chargeur_archive(ChargeurArchive $chargeur): void
	{
		$this->chargeur_archive = $chargeur;
	}
}
