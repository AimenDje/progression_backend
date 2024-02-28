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

final class TestTests extends TestCase
{
	public function test_étant_donné_un_Test_instancié_avec_tous_ses_paramètres_lorsquon_récupère_ses_attributs_on_obtient_des_valeurs_identiques()
	{
		$nom_attendu = "testNom";
		$sortie_attendu = "testSortie";
		$feedback_pos_attendu = "testFbp";
		$feedback_neg_attendu = "testFbn";

		$résultat_obtenu = new Test("testNom", "testSortie", "testFbp", "testFbn");

		$this->assertEquals($nom_attendu, $résultat_obtenu->nom);
		$this->assertEquals($sortie_attendu, $résultat_obtenu->sortie_attendue);
		$this->assertEquals($feedback_pos_attendu, $résultat_obtenu->feedback_pos);
		$this->assertEquals($feedback_neg_attendu, $résultat_obtenu->feedback_neg);
	}

	public function test_étant_donné_un_Test_instancié_sans_paramètres_lorsquon_récupère_ses_attributs_on_obtient_des_valeurs_par_défaut()
	{
		$résultat_obtenu = new Test();

		$this->assertEquals("", $résultat_obtenu->nom);
		$this->assertEquals("", $résultat_obtenu->sortie_attendue);

		$this->assertNull($résultat_obtenu->feedback_pos);
		$this->assertNull($résultat_obtenu->feedback_neg);
	}
}
