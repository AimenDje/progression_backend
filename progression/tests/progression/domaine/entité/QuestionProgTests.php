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

use PHPUnit\Framework\TestCase;

final class QuestionProgTests extends TestCase
{
	public function test_étant_donné_une_QuestionProg_instanciée_avec_tous_ses_paramètres_lorsquon_récupère_ses_attributs_on_obtient_des_valeurs_identiques()
	{
		$exécutables_attendu = ["execTest0", "execTest1"];
		$tests_attendu = ["testTest0", "testTest1"];

		$résultat_obtenu = new QuestionProg();
		$résultat_obtenu->exécutables = ["execTest0", "execTest1"];
		$résultat_obtenu->tests = ["testTest0", "testTest1"];
		$résultat_obtenu->avancement = "avancementTest0";

		$this->assertEquals($exécutables_attendu, $résultat_obtenu->exécutables);
		$this->assertEquals($tests_attendu, $résultat_obtenu->tests);
	}
}
