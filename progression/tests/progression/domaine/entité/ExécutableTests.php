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

final class ExécutableTests extends TestCase
{
	public function test_étant_donné_un_Exécutable_instancié_avec_tous_ses_paramètres_lorsquon_récupère_ses_attributs_on_obtient_des_valeurs_identiques()
	{
		$code_attendu = "print('Hello, world!')";
		$code_exec_attendu = "print('Hello, world!')";
		$lang_attendu = "python";

		$résultat_obtenu = new Exécutable("print('Hello, world!')", "python");

		$this->assertEquals($code_attendu, $résultat_obtenu->code);
		$this->assertEquals($lang_attendu, $résultat_obtenu->lang);
	}
}
