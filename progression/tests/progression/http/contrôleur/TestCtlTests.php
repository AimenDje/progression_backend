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

use progression\ContrôleurTestCase;

use progression\dao\DAOFactory;
use progression\domaine\entité\question\{Question, QuestionProg, QuestionSys, Type};
use progression\domaine\entité\{TestSys, TestProg};
use progression\domaine\entité\user\{User, Rôle, État};

use progression\UserAuthentifiable;

final class TestCtlTests extends ContrôleurTestCase
{
	public $user;

	public function setUp(): void
	{
		parent::setUp();
		$this->user = new UserAuthentifiable(
			username: "bob",
			date_inscription: 0,
			rôle: Rôle::NORMAL,
			état: État::ACTIF,
		);

		// Question
		$question = new QuestionProg(
			titre: "appeler_une_fonction_paramétrée",
			tests: [
				new TestProg("2 salutations", "Bonjour\nBonjour\n", "2"),
				new TestProg("Aucune salutation", "", "0"),
			],
		);
		$question_cachée = new QuestionProg(
			titre: "appeler_une_fonction_paramétrée",
			tests: [
				new TestProg(
					nom: "2 salutations",
					sortie_attendue: "Bonjour\nBonjour\n",
					entrée: "2",
					params: "Bonjour",
					caché: false,
				),
				new TestProg(
					nom: "Aucune salutation",
					sortie_attendue: "Cette sortie devrait être cachée",
					entrée: "Cette entrée être caché",
					params: "Ce param devrait être caché",
					caché: true,
				),
			],
		);

		$mockQuestionDAO = Mockery::mock("progression\\dao\\question\\QuestionDAO");
		$mockQuestionDAO
			->shouldReceive("get_question")
			->with("https://depot.com/roger/questions_prog/fonctions01/appeler_une_fonction")
			->andReturn($question);
		$mockQuestionDAO
			->shouldReceive("get_question")
			->with("https://depot.com/roger/questions_prog/fonctions01/question_cachée")
			->andReturn($question_cachée);

		$question = new QuestionSys(
			titre: "Persmissions 2",
			tests: [
				new TestSys("testNom", "testSortie", "testValidation", "utilisateurTest", "testFbp", "testFbn"),
				new TestSys("testNom2", "testSortie2", "testValidation2", "utilisateurTest2", "testFbp2", "testFbn2"),
			],
		);

		$mockQuestionDAO
			->shouldReceive("get_question")
			->with("https://depot.com/roger/questions_sys/permissions01/octroyer_toutes_les_permissions2")
			->andReturn($question);
		// DAOFactory
		$mockDAOFactory = Mockery::mock("progression\\dao\\DAOFactory");
		$mockDAOFactory->shouldReceive("get_question_dao")->andReturn($mockQuestionDAO);

		DAOFactory::setInstance($mockDAOFactory);
	}

	public function test_étant_donné_le_chemin_dune_questionProg_et_son_test_numero_0_lorsquon_appelle_get_on_obtient_le_test_numero_0_et_ses_relations_sous_forme_json()
	{
		$résultat_obtenu = $this->actingAs($this->user)->call(
			"GET",
			"/test/aHR0cHM6Ly9kZXBvdC5jb20vcm9nZXIvcXVlc3Rpb25zX3Byb2cvZm9uY3Rpb25zMDEvYXBwZWxlcl91bmVfZm9uY3Rpb24/0",
		);

		$this->assertEquals(200, $résultat_obtenu->status());
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/testCtlTest_question_prog.json",
			$résultat_obtenu->getContent(),
		);
	}

	public function test_étant_donné_le_chemin_dune_questionProg_et_son_test_non_caché_lorsquon_appelle_get_on_obtient_le_test_sans_entrée_params_ni_sortie_attendue()
	{
		$résultat_obtenu = $this->actingAs($this->user)->call(
			"GET",
			"/test/aHR0cHM6Ly9kZXBvdC5jb20vcm9nZXIvcXVlc3Rpb25zX3Byb2cvZm9uY3Rpb25zMDEvcXVlc3Rpb25fY2FjaMOpZQ/0",
		);

		$this->assertEquals(200, $résultat_obtenu->status());
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/testCtlTest_question_prog_test_non_caché.json",
			$résultat_obtenu->getContent(),
		);
	}

	public function test_étant_donné_le_chemin_dune_questionProg_et_son_test_caché_lorsquon_appelle_get_on_obtient_le_test_sans_entrée_params_ni_sortie_attendue()
	{
		$résultat_obtenu = $this->actingAs($this->user)->call(
			"GET",
			"/test/aHR0cHM6Ly9kZXBvdC5jb20vcm9nZXIvcXVlc3Rpb25zX3Byb2cvZm9uY3Rpb25zMDEvcXVlc3Rpb25fY2FjaMOpZQ/1",
		);

		$this->assertEquals(200, $résultat_obtenu->status());
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/testCtlTest_question_prog_test_caché.json",
			$résultat_obtenu->getContent(),
		);
	}

	public function test_étant_donné_le_chemin_dune_questionSys_et_son_test_numero_0_lorsquon_appelle_get_on_obtient_le_test_numero_0_et_ses_relations_sous_forme_json()
	{
		$résultat_obtenu = $this->actingAs($this->user)->call(
			"GET",
			"/test/aHR0cHM6Ly9kZXBvdC5jb20vcm9nZXIvcXVlc3Rpb25zX3N5cy9wZXJtaXNzaW9uczAxL29jdHJveWVyX3RvdXRlc19sZXNfcGVybWlzc2lvbnMy/0",
		);

		$this->assertEquals(200, $résultat_obtenu->status());
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/testCtlTest_question_sys.json",
			$résultat_obtenu->getContent(),
		);
	}

	public function test_étant_donné_le_chemin_dune_question_et_son_test_numero_abc_lorsquon_appelle_get_on_obtient_ressource_non_trouvée()
	{
		$résultat_obtenu = $this->actingAs($this->user)->call(
			"GET",
			"/test/aHR0cHM6Ly9kZXBvdC5jb20vcm9nZXIvcXVlc3Rpb25zX3Byb2cvZm9uY3Rpb25zMDEvYXBwZWxlcl91bmVfZm9uY3Rpb24/999",
		);

		$this->assertEquals(404, $résultat_obtenu->status());
		$this->assertEquals('{"erreur":"Ressource non trouvée."}', $résultat_obtenu->getContent());
	}
}
