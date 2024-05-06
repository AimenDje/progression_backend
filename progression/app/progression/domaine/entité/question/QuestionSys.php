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

namespace progression\domaine\entité\question;

class QuestionSys extends Question
{
	public $utilisateur;
	public $solution;
	public string|null $init;
	public string|null $commande;

	public function __construct(
		$niveau = null,
		$titre = null,
		string $objectif = null,
		$enonce = null,
		$auteur = null,
		$licence = null,
		string $feedback_pos = null,
		string $feedback_neg = null,
		string $feedback_err = null,
		string|null $image = null,
		$utilisateur = null,
		string $init = null,
		string $solution = null,
		$tests = [],
		string $description = null,
		string|null $commande = null,
	) {
		parent::__construct(
			$niveau,
			$titre,
			$objectif,
			$enonce,
			$auteur,
			$licence,
			$feedback_pos,
			$feedback_neg,
			$feedback_err,
			$tests,
			$description,
			$image,
		);

		$this->utilisateur = $utilisateur;
		$this->solution = $solution;
		$this->init = $init;
		$this->commande = $commande;
	}
}
