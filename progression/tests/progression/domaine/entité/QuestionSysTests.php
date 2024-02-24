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

use progression\TestCase;

final class QuestionSysTests extends TestCase
{
	public function test_étant_donné_une_QuestionSys_instanciée_avec_tous_ses_paramètres_lorsquon_récupère_ses_attributs_on_obtient_des_valeurs_identiques()
	{
		$tests_attendu = ["testSys0", "testSys1"];

		$résultat_obtenu = new QuestionSys(
			niveau: "Facile",
			titre: "Question système 1",
			objectif: "Ceci est une question système.",
			enonce: "Un énoncé",
			auteur: "Un auteur",
			licence: "Licence",
			feedback_pos: "Feedback positif",
			feedback_neg: "Feedback négatif",
			feedback_err: "Feedback erreur",
			image: "imageDocker",
			utilisateur: "Bob",
			solution: "solutionTest",
			tests: ["testSys0", "testSys1"],
		);

		$this->assertEquals("imageDocker", $résultat_obtenu->image);
		$this->assertEquals("Bob", $résultat_obtenu->utilisateur);
		$this->assertEquals("solutionTest", $résultat_obtenu->solution);
		$this->assertEquals($tests_attendu, $résultat_obtenu->tests);
	}
}
