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

final class TentativeProgTests extends TestCase
{
	public function test_étant_donné_une_TentativeProg_instanciée_avec_un_code_lorsquon_récupère_son_code_on_obtient_le_code_identique()
	{
		$tentativeProgTest = new TentativeProg(langage: "python", code: "codeTest", date_soumission: 1234567890);

		$langage = $tentativeProgTest->langage;
		$code = $tentativeProgTest->code;
		$date_soumission = $tentativeProgTest->date_soumission;

		$this->assertEquals("python", $langage);
		$this->assertEquals("codeTest", $code);
		$this->assertEquals("1234567890", $date_soumission);
	}
}
