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

use progression\domaine\entité\{QuestionProg, TentativeProg, Test, RésultatProg};
use PHPUnit\Framework\TestCase;

final class TraiterTentativeProgIntTests extends TestCase
{
	public function test_étant_donné_une_TentativeProg_correcte_lorsquon_la_traite_on_obtient_une_TentativeProg_traitée_et_réussie()
	{
		$question = new QuestionProg();
		$question->tests = [
			new Test("premier test", "ok\n", "1", null, "Test 0 passé", "Test 0 échoué"),
			new Test("deuxième test", "ok\nok\nok\nok\nok\n", "5", null, "Test 1 passé", "Test 1 échoué"),
		];
		$question->feedback_pos = "Bravo!";
		$question->feedback_neg = "Non!";

		$tentative = new TentativeProg("python", "testCode");
		$tentative->résultats = [new RésultatProg("ok\n", ""), new RésultatProg("ok\nok\nok\nok\nok\n", "")];
		$résultat_attendu = new TentativeProg("python", "testCode", null, true, 2, "Bravo!", [
			new RésultatProg("ok\n", "", true, "Test 0 passé"),
			new RésultatProg("ok\nok\nok\nok\nok\n", "", true, "Test 1 passé"),
		]);

		$résultat_observé = (new TraiterTentativeProgInt(null))->traiter_résultats($question, $tentative);

		$this->assertEquals($résultat_attendu, $résultat_observé);
	}

	public function test_étant_donné_une_TentativeProg_incorrecte_lorsquon_la_traite_on_obtient_une_TentativeProg_traitée_et_nonréussie_avec_feedback_positif()
	{
		$question = new QuestionProg();
		$question->tests = [
			new Test("premier test", "ok\n", "1", null, "Test 0 passé", "Test 0 échoué"),
			new Test("deuxième test", "ok\nok\nok\nok\nok\n", "5", null, "Test 1 passé", "Test 1 échoué"),
			new Test(
				"troisième test",
				"ok\nok\nok\nok\nok\nok\nok\nok\nok\nok\n",
				"10",
				null,
				"Test 2 passé",
				"Test 2 échoué",
			),
		];
		$question->feedback_pos = "Bravo!";
		$question->feedback_neg = "As-tu essayé de ne pas faire ça?";

		$tentative = new TentativeProg("python", "testCode");
		$tentative->résultats = [
			new RésultatProg("ok\n", ""),
			new RésultatProg("ok\nok\nok\n", ""),
			new RésultatProg("ok\nok\nok\nok\nok\n", ""),
		];

		$résultat_attendu = new TentativeProg(
			"python",
			"testCode",
			null,
			false,
			1,
			"As-tu essayé de ne pas faire ça?",
			[
				new RésultatProg("ok\n", "", true, "Test 0 passé"),
				new RésultatProg("ok\nok\nok\n", "", false, "Test 1 échoué"),
				new RésultatProg("ok\nok\nok\nok\nok\n", "", false, "Test 2 échoué"),
			],
		);

		$résultat_observé = (new TraiterTentativeProgInt(null))->traiter_résultats($question, $tentative);

		$this->assertEquals($résultat_attendu, $résultat_observé);
	}

	public function test_étant_donné_une_TentativeProg_avec_une_erreur_et_un_feedback_d_erreur_prévu_lorsquon_la_traite_on_obtient_une_TentativeProg_traitée_et_nonréussie_avec_feedback_d_erreur()
	{
		$question = new QuestionProg();
		$question->tests = [
			new Test("premier test", "ok\n", "1", null, "Test 0 passé", "Test 0 échoué", "Erreur!"),
			new Test("deuxième test", "ok\nok\nok\nok\nok\n", "5", null, "Test 1 passé", "Test 1 échoué", "Erreur!"),
		];
		$question->feedback_pos = "Bravo!";
		$question->feedback_neg = "As-tu essayé de ne pas faire ça?";
		$question->feedback_err = "Revise la syntaxe de ton code";

		$tentative = new TentativeProg("python", "testCode");
		$tentative->résultats = [new RésultatProg("ok\n", ""), new RésultatProg("", "testErreur")];

		$résultat_attendu = new TentativeProg("python", "testCode", null, false, 1, "Revise la syntaxe de ton code", [
			new RésultatProg("ok\n", "", true, "Test 0 passé"),
			new RésultatProg("", "testErreur", false, "Erreur!"),
		]);

		$résultat_observé = (new TraiterTentativeProgInt(null))->traiter_résultats($question, $tentative);

		$this->assertEquals($résultat_attendu, $résultat_observé);
	}

	public function test_étant_donné_une_TentativeProg_avec_une_erreur_sans_feedback_d_erreur_prévu_lorsquon_la_traite_on_obtient_une_TentativeProg_traitée_et_nonréussie_avec_feedback_aléatoire()
	{
		$question = new QuestionProg();
		$question->tests = [
			new Test("premier test", "ok\n", "1", null, "Test 0 passé", "Test 0 échoué"),
			new Test("deuxième test", "ok\nok\nok\nok\nok\n", "5", null, "Test 1 passé", "Test 1 échoué"),
		];
		$question->feedback_pos = "Bravo!";
		$question->feedback_neg = "As-tu essayé de ne pas faire ça?";

		$tentative = new TentativeProg("python", "testCode");
		$tentative->résultats = [new RésultatProg("ok\n", ""), new RésultatProg("", "testErreur")];

		$résultat_observé = (new TraiterTentativeProgInt(null))->traiter_résultats($question, $tentative);

		$this->assertNotEmpty($résultat_observé->feedback);
	}
}
