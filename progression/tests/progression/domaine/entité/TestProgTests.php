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

use progression\TestCase;

final class TestProgTests extends TestCase
{
	public function test_étant_donné_un_TestProg_instancié_avec_tous_ses_paramètres_lorsquon_récupère_ses_attributs_on_obtient_des_valeurs_identiques()
	{
		$nom_attendu = "testNom";
		$sortie_attendu = "testSortie";
		$entrée_attendu = "testEntrée";
		$params_attendu = "testParam0 testParam1";
		$feedback_pos_attendu = "testFbp";
		$feedback_neg_attendu = "testFbn";
		$feedback_err_attendu = "testFbe";

		$résultat_obtenu = new TestProg(
			"testNom",
			"testSortie",
			"testEntrée",
			"testParam0 testParam1",
			"testFbp",
			"testFbn",
			"testFbe",
		);

		$this->assertEquals($nom_attendu, $résultat_obtenu->nom);
		$this->assertEquals($entrée_attendu, $résultat_obtenu->entrée);
		$this->assertEquals($sortie_attendu, $résultat_obtenu->sortie_attendue);
		$this->assertEquals($params_attendu, $résultat_obtenu->params);
		$this->assertEquals($feedback_pos_attendu, $résultat_obtenu->feedback_pos);
		$this->assertEquals($feedback_neg_attendu, $résultat_obtenu->feedback_neg);
		$this->assertEquals($feedback_err_attendu, $résultat_obtenu->feedback_err);
	}

	public function test_étant_donné_un_TestProg_instancié_sans_paramètres_lorsquon_récupère_ses_attributs_on_obtient_des_valeurs_par_défaut()
	{
		$résultat_obtenu = new TestProg();

		$this->assertEquals("", $résultat_obtenu->params);
		$this->assertEquals("", $résultat_obtenu->entrée);
	}
}
