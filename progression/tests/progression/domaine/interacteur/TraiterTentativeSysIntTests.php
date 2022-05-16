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

use progression\domaine\entité\{QuestionSys, TentativeSys, TestSys, RésultatSys};
use PHPUnit\Framework\TestCase;

final class TraiterTentativeSysIntTests extends TestCase
{
	public function test_étant_donné_une_TentativeSys_correcte_lorsquon_la_traite_on_obtient_une_TentativeSys_traitée_et_réussie_avec_un_feedback_positif()
	{
		$question = new QuestionSys();
		$question->tests = [
			new TestSys("premier test", "reponse test", null, null, "Test 0 passé", "Test 0 échoué"),
			new TestSys("deuxième test", "Test fonctionnel", null, null, "Test 1 passé", "Test 1 échoué"),
		];
		$tests = [
			new TestSys("premier test", "reponse test", null, null, "Test 0 passé", "Test 0 échoué"),
			new TestSys("deuxième test", "Test fonctionnel", null, null, "Test 1 passé", "Test 1 échoué"),
		];

		$rétroactions["feedback_pos"] = "Bon travail!";
		$rétroactions["feedback_neg"] = "Essaye encore";

		$tentative = new TentativeSys("conteneurTest", "réponseTest");
		$tentative->résultats = [new RésultatSys("reponse test"), new RésultatSys("Test fonctionnel")];
		$résultat_attendu = new TentativeSys("conteneurTest", "réponseTest", null, true, 2, null, "Bon travail!", [
			new RésultatSys("reponse test", true, "Test 0 passé"),
			new RésultatSys("Test fonctionnel", true, "Test 1 passé"),
		]);

		$résultat_observé = (new TraiterTentativeSysInt(null))->traiter_résultats($tentative, $rétroactions, $tests);

		$this->assertEquals($résultat_attendu, $résultat_observé);
	}
}
