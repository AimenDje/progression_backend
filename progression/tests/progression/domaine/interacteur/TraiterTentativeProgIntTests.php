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

use progression\domaine\entité\question\QuestionProg;
use progression\domaine\entité\{TentativeProg, TestProg, Résultat};
use progression\TestCase;

final class TraiterTentativeProgIntTests extends TestCase
{
	public function test_étant_donné_une_TentativeProg_correcte_lorsquon_la_traite_on_obtient_une_TentativeProg_traitée_et_réussie_avec_un_feedback_positif()
	{
		$tests = [
			new TestProg("premier test", "ok\n", "1", null, "Test 0 passé", "Test 0 échoué"),
			new TestProg("deuxième test", "ok\nok\nok\nok\nok\n", "5", null, "Test 1 passé", "Test 1 échoué"),
		];
		$rétroactions["feedback_pos"] = "Bravo!";
		$rétroactions["feedback_neg"] = "Non!";

		$tentative = new TentativeProg("python", "testCode", 1692201035, false, [], 2, 100);
		$tentative->résultats = [new Résultat("ok\n", ""), new Résultat("ok\nok\nok\nok\nok\n", "")];
		$résultat_attendu = new TentativeProg(
			"python",
			"testCode",
			1692201035,
			true,
			[
				new Résultat("ok\n", "", true, "Test 0 passé"),
				new Résultat("ok\nok\nok\nok\nok\n", "", true, "Test 1 passé"),
			],
			2,
			100,
			"Bravo!",
		);

		$résultat_observé = (new TraiterTentativeProgInt(null))->traiter_résultats($tentative, $rétroactions, $tests);

		$this->assertEquals($résultat_attendu, $résultat_observé);
	}

	public function test_étant_donné_une_TentativeProg_incorrecte_numériquement_lorsquon_la_traite_on_obtient_une_TentativeProg_traitée_et_non_réussie_avec_un_feedback_positif()
	{
		$tests = [
			new TestProg("premier test", "1.0\n", "1", null, "Test 0 passé", "Test 0 échoué"),
			new TestProg("deuxième test", "5.0\n", "5", null, "Test 1 passé", "Test 1 échoué"),
		];
		$rétroactions["feedback_pos"] = "Bravo!";
		$rétroactions["feedback_neg"] = "Non!";

		$tentative = new TentativeProg("python", "testCode", 1692201035, false, [], 2, 100);
		$tentative->résultats = [new Résultat("1\n", ""), new Résultat("5\n", "")];
		$résultat_attendu = new TentativeProg(
			"python",
			"testCode",
			1692201035,
			false,
			[new Résultat("1\n", "", false, "Test 0 échoué"), new Résultat("5\n", "", false, "Test 1 échoué")],
			0,
			100,
			"Non!",
		);

		$résultat_observé = (new TraiterTentativeProgInt(null))->traiter_résultats($tentative, $rétroactions, $tests);

		$this->assertEquals($résultat_attendu, $résultat_observé);
	}

	public function test_étant_donné_une_TentativeProg_incorrecte_lorsquon_la_traite_on_obtient_une_TentativeProg_traitée_et_nonréussie_avec_feedback_négatif()
	{
		$tests = [
			new TestProg("premier test", "ok\n", "1", null, "Test 0 passé", "Test 0 échoué"),
			new TestProg("deuxième test", "ok\nok\nok\nok\nok\n", "5", null, "Test 1 passé", "Test 1 échoué"),
			new TestProg(
				"troisième test",
				"ok\nok\nok\nok\nok\nok\nok\nok\nok\nok\n",
				"10",
				null,
				"Test 2 passé",
				"Test 2 échoué",
			),
		];
		$rétroactions["feedback_pos"] = "Bravo!";
		$rétroactions["feedback_neg"] = "As-tu essayé de ne pas faire ça?";

		$tentative = new TentativeProg("python", "testCode", 1692201035, false, [], 1, 100);
		$tentative->résultats = [
			new Résultat("ok\n", ""),
			new Résultat("ok\nok\nok\n", ""),
			new Résultat("ok\nok\nok\nok\nok\n", ""),
		];

		$résultat_attendu = new TentativeProg(
			"python",
			"testCode",
			1692201035,
			false,
			[
				new Résultat("ok\n", "", true, "Test 0 passé"),
				new Résultat("ok\nok\nok\n", "", false, "Test 1 échoué"),
				new Résultat("ok\nok\nok\nok\nok\n", "", false, "Test 2 échoué"),
			],
			1,
			100,
			"As-tu essayé de ne pas faire ça?",
		);

		$résultat_observé = (new TraiterTentativeProgInt(null))->traiter_résultats($tentative, $rétroactions, $tests);

		$this->assertEquals($résultat_attendu, $résultat_observé);
	}

	public function test_étant_donné_une_TentativeProg_avec_une_erreur_et_un_feedback_d_erreur_prévu_lorsquon_la_traite_on_obtient_une_TentativeProg_traitée_et_nonréussie_avec_feedback_d_erreur()
	{
		$tests = [
			new TestProg("premier test", "ok\n", "1", null, "Test 0 passé", "Test 0 échoué", "Erreur!"),
			new TestProg(
				"deuxième test",
				"ok\nok\nok\nok\nok\n",
				"5",
				null,
				"Test 1 passé",
				"Test 1 échoué",
				"Erreur!",
			),
		];

		$rétroactions["feedback_pos"] = "Bravo!";
		$rétroactions["feedback_neg"] = "As-tu essayé de ne pas faire ça?";
		$rétroactions["feedback_err"] = "Revise la syntaxe de ton code";

		$rétroactions["feedback_pos"] = "Bravo!";
		$rétroactions["feedback_neg"] = "As-tu essayé de ne pas faire ça?";
		$rétroactions["feedback_err"] = "Revise la syntaxe de ton code";

		$tentative = new TentativeProg("python", "testCode", 1692201035, false, [], 1, 100);
		$tentative->résultats = [new Résultat("ok\n", ""), new Résultat("", "testErreur")];

		$résultat_attendu = new TentativeProg(
			"python",
			"testCode",
			1692201035,
			false,
			[new Résultat("ok\n", "", true, "Test 0 passé"), new Résultat("", "testErreur", false, "Erreur!")],
			1,
			100,
			"Revise la syntaxe de ton code",
		);

		$résultat_observé = (new TraiterTentativeProgInt(null))->traiter_résultats($tentative, $rétroactions, $tests);

		$this->assertEquals($résultat_attendu, $résultat_observé);
	}

	public function test_étant_donné_une_TentativeProg_avec_une_erreur_sans_feedback_d_erreur_prévu_lorsquon_la_traite_on_obtient_une_TentativeProg_traitée_et_nonréussie_sans_feedback_d_erreur()
	{
		$tests = [
			new TestProg("premier test", "ok\n", "1", null, "Test 0 passé", "Test 0 échoué"),
			new TestProg("deuxième test", "ok\nok\nok\nok\nok\n", "5", null, "Test 1 passé", "Test 1 échoué"),
		];
		$rétroactions["feedback_pos"] = "Bravo!";
		$rétroactions["feedback_neg"] = "As-tu essayé de ne pas faire ça?";
		$rétroactions["feedback_err"] = null;

		$résultats = [new Résultat("ok\n", ""), new Résultat("", "testErreur")];
		$tentative = new TentativeProg("python", "testCode", 1692201035, true, $résultats, 0, 120);

		$résultat_attendu = new TentativeProg(
			"python",
			"testCode",
			1692201035,
			false,
			[new Résultat("ok\n", "", true, "Test 0 passé"), new Résultat("", "testErreur", false, null)],
			1,
			120,
			feedback: null,
		);

		$résultat_observé = (new TraiterTentativeProgInt(null))->traiter_résultats($tentative, $rétroactions, $tests);

		$this->assertEquals($résultat_attendu, $résultat_observé);
	}
}
