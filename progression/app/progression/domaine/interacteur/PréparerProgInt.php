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

use progression\domaine\entité\Exécutable;

// Matche le contenu de toutes les zones TODO

define("REGEX_MATCH_TODOS", "/(?s:(?<=\+TODO)(.*?))(?=-TODO|\Z)/");
//                            ^              ^     ^        ^
//                            |              |     |        └  ou la fin du document
//                            |              |     └ jusqu'à une balise -TODO
//                            |              └ matche le contenu
//                            └sans égard aux sauts de ligne, matche tout ce qui suit un +TODO
class PréparerProgInt
{
	public function préparer_exécutable($question, $tentative)
	{
		if (array_key_exists($tentative->langage, $question->exécutables)) {
			$code = $this->composer_code_à_exécuter(
				$question->exécutables[$tentative->langage]->code,
				$tentative->code,
			);
			if ($code != null) {
				return new Exécutable($code, $tentative->langage);
			}
		}
		return null;
	}

	private function composer_code_à_exécuter($ébauche, $code_utilisateur)
	{
		if (!$this->vérifierNombreTodos($ébauche, $code_utilisateur)) {
			return null;
		}

		//S'il n'y a pas de +TODO, ou que le premier est placé après le premiers -TODO,
		//on considère que l'ébauche commence avec une zone éditable
		$premier_plus_todo = strpos($ébauche, "+TODO");
		$premier_moins_todo = strpos($ébauche, "-TODO");
		if (!$premier_plus_todo || ($premier_moins_todo && $premier_plus_todo > $premier_moins_todo)) {
			$ébauche = "#+TODO\n" . $ébauche;
			$code_utilisateur = "#+TODO\n" . $code_utilisateur;
		} else {
			$ébauche = "#\n" . $ébauche;
			$code_utilisateur = "#\n" . $code_utilisateur;
		}

		$codeÉbauche = explode("\n", $ébauche);
		$codeExécutable = [];

		$todoIndex = 0;
		$todoStatut = false;

		preg_match_all(REGEX_MATCH_TODOS, $code_utilisateur, $todos_utilisateur);
		foreach ($codeÉbauche as $ligne) {
			$posMoinsTodo = strpos($ligne, "-TODO");
			$posPlusTodo = strpos($ligne, "+TODO");

			if ($todoStatut && $posMoinsTodo) {
				$todoStatut = false;
			}

			if (!$todoStatut && !$posPlusTodo && !$posMoinsTodo) {
				$codeExécutable[] = $ligne;
			}

			if (!$todoStatut && $posPlusTodo && !$posMoinsTodo) {
				$codeExécutable[] = substr($ligne, 0, $posPlusTodo) . $todos_utilisateur[1][$todoIndex++];
				$todoStatut = true;
			}

			if (!$todoStatut && $posPlusTodo && $posMoinsTodo) {
				$codeExécutable[] =
					substr($ligne, 0, $posPlusTodo) .
					$todos_utilisateur[1][$todoIndex++] .
					substr($ligne, $posMoinsTodo + 5);
			}
		}

		//On recompose le code
		$codeExécutable = implode("\n", $codeExécutable);
		//et on enlève la première ligne
		$codeExécutable = substr($codeExécutable, strpos($codeExécutable, "\n") + 1);

		return $codeExécutable;
	}

	private function vérifierNombreTodos($ébauche, $code_utilisateur)
	{
		preg_match_all(REGEX_MATCH_TODOS, $code_utilisateur, $todos_utilisateur);
		preg_match_all(REGEX_MATCH_TODOS, $ébauche, $todos_ébauche);

		$nb_todos_utilisateur = count($todos_utilisateur[1]);
		$nb_todos_ébauche = count($todos_ébauche[1]);

		return $nb_todos_ébauche == $nb_todos_utilisateur;
	}
}
