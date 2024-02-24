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

use progression\domaine\entité\question\QuestionSys;
use progression\domaine\entité\{TentativeSys, TestSys, Résultat};
use progression\TestCase;

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

		$tentative = new TentativeSys("conteneurTest", "https://tty.com/abcde", "réponseTest", 1692201035);
		$tentative->résultats = [
			new Résultat(sortie_observée: "reponse test", code_retour: 0),
			new Résultat(sortie_observée: "Test fonctionnel", code_retour: 0),
		];
		$résultat_attendu = new TentativeSys(
			"conteneurTest",
			"https://tty.com/abcde",
			"réponseTest",
			1692201035,
			true,
			[
				new Résultat(
					sortie_observée: "reponse test",
					sortie_erreur: "",
					résultat: true,
					feedback: "Test 0 passé",
					code_retour: 0,
				),
				new Résultat(
					sortie_observée: "Test fonctionnel",
					sortie_erreur: "",
					résultat: true,
					feedback: "Test 1 passé",
					code_retour: 0,
				),
			],
			2,
			null,
			"Bon travail!",
		);

		$résultat_observé = (new TraiterTentativeSysInt())->traiter_résultats($tentative, $rétroactions, $tests);

		$this->assertEquals($résultat_attendu, $résultat_observé);
	}

	public function test_étant_donné_une_TentativeSys_incorrecte_lorsquon_la_traite_on_obtient_une_TentativeSys_traitée_et_nonréussie_avec_feedback_négatif()
	{
		$question = new QuestionSys();
		$question->tests = [
			new TestSys("premier test", "reponse test", null, null, "Test 0 passé", "Test 0 échoué"),
			new TestSys("deuxième test", "Test fonctionnel", null, null, "Test 1 passé", "Test 1 échoué"),
			new TestSys("troisième test", "Test de validation", null, null, "Test 2 passé", "Test 2 échoué"),
		];
		$tests = [
			new TestSys("premier test", "reponse test", null, null, "Test 0 passé", "Test 0 échoué"),
			new TestSys("deuxième test", "Test fonctionnel", null, null, "Test 1 passé", "Test 1 échoué"),
			new TestSys("troisième test", "Test de validation", null, null, "Test 2 passé", "Test 2 échoué"),
		];

		$rétroactions["feedback_pos"] = "Bon travail!";
		$rétroactions["feedback_neg"] = "Essaye encore";

		$tentative = new TentativeSys("conteneurTest", "https://tty.com/abcde", "réponseTest", 1692201035);
		$tentative->résultats = [
			new Résultat(sortie_observée: "reponse test", code_retour: 0),
			new Résultat(sortie_observée: "Test non fonctionnel", code_retour: 1),
			new Résultat(sortie_observée: "Test validation", code_retour: 0),
		];

		$résultat_attendu = new TentativeSys(
			"conteneurTest",
			"https://tty.com/abcde",
			"réponseTest",
			1692201035,
			false,
			[
				new Résultat(
					sortie_observée: "reponse test",
					sortie_erreur: "",
					résultat: true,
					feedback: "Test 0 passé",
					code_retour: 0,
				),
				new Résultat(
					sortie_observée: "Test non fonctionnel",
					sortie_erreur: "",
					résultat: false,
					feedback: "Test 1 échoué",
					code_retour: 1,
				),
				new Résultat(
					sortie_observée: "Test validation",
					sortie_erreur: "",
					résultat: false,
					feedback: "Test 2 échoué",
					code_retour: 0,
				),
			],
			1,
			null,
			"Essaye encore",
		);

		$résultat_observé = (new TraiterTentativeSysInt())->traiter_résultats($tentative, $rétroactions, $tests);

		$this->assertEquals($résultat_attendu, $résultat_observé);
	}
}
