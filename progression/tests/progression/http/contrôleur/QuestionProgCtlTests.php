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

use progression\domaine\entité\question\{Question, QuestionProg, Type};
use progression\domaine\entité\{Exécutable, TestProg};
use progression\domaine\entité\user\{User, Rôle, État};
use progression\dao\DAOFactory;
use progression\dao\question\ChargeurException;
use progression\UserAuthentifiable;

final class QuestionProgCtlTests extends ContrôleurTestCase
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
			titre: "Appeler une fonction paramétrée",
			objectif: "Appel d'une fonction existante recevant un paramètre",
			description: "Ceci est une question prog complète",
			enonce: "La fonction `salutations` affiche une salution autant de fois que la valeur reçue en paramètre. Utilisez-la pour faire afficher «Bonjour le monde!» autant de fois que le nombre reçu en entrée.",
			auteur: "Albert Einstein",
			licence: "poétique",
			niveau: "débutant",
			// Ébauches
			exécutables: [
				"python" => new Exécutable("print(\"Hello world\")", "python"),
				"java" => new Exécutable("System.out.println(\"Hello world\")", "java"),
			],

			// Tests
			tests: [
				new TestProg(
					nom: "2 salutations",
					sortie_attendue: "Bonjour\nBonjour\n",
					entrée: "2",
					params: "param_0 param_1",
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
			->with("https://depot.com/roger/questions_invalide")
			->andThrow(new ChargeurException("Question invalide."));
		$mockQuestionDAO->shouldReceive("get_question")->with(Mockery::any())->andReturn(null);

		// DAOFactory
		$mockDAOFactory = Mockery::mock("progression\\dao\\DAOFactory");
		$mockDAOFactory->shouldReceive("get_question_dao")->andReturn($mockQuestionDAO);
		DAOFactory::setInstance($mockDAOFactory);
	}

	public function test_étant_donné_le_chemin_dune_question_lorsquon_appelle_get_on_obtient_la_question_et_ses_relations_sous_forme_json()
	{
		$résultat_obtenu = $this->actingAs($this->user)->call(
			"GET",
			"/question/aHR0cHM6Ly9kZXBvdC5jb20vcm9nZXIvcXVlc3Rpb25zX3Byb2cvZm9uY3Rpb25zMDEvYXBwZWxlcl91bmVfZm9uY3Rpb24",
		);

		$this->assertEquals(200, $résultat_obtenu->status());
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/questionCtlTests_question_prog.json",
			$résultat_obtenu->getContent(),
		);
	}

	public function test_étant_donné_le_chemin_dune_question_lorsquon_appelle_get_en_incluant_les_tests_et_les_ébauches_on_obtient_la_question_et_ses_sous_objets_sous_forme_json()
	{
		$résultat_obtenu = $this->actingAs($this->user)->call(
			"GET",
			"/question/aHR0cHM6Ly9kZXBvdC5jb20vcm9nZXIvcXVlc3Rpb25zX3Byb2cvZm9uY3Rpb25zMDEvYXBwZWxlcl91bmVfZm9uY3Rpb24?include=tests,ebauches",
		);

		$this->assertEquals(200, $résultat_obtenu->status());
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/questionCtlTests_question_prog_tests_et_ébauches.json",
			$résultat_obtenu->getContent(),
		);
	}

	public function test_étant_donné_le_chemin_dune_question_inexistante_lorsquon_appelle_get_on_obtient_une_erreur_404()
	{
		//https://depot.com/roger/questions_prog/fonctions01/appeler_une
		$résultat_obtenu = $this->actingAs($this->user)->call(
			"GET",
			"/question/aHR0cHM6Ly9kZXBvdC5jb20vcm9nZXIvcXVlc3Rpb25zX3Byb2cvZm9uY3Rpb25zMDEvYXBwZWxlcl91bmV",
		);

		$this->assertEquals(404, $résultat_obtenu->status());
		$this->assertEquals('{"erreur":"Ressource non trouvée."}', $résultat_obtenu->getContent());
	}
	public function test_étant_donné_le_chemin_dune_question_invalide_lorsquon_appelle_get_on_obtient_une_erreur_422()
	{
		//https://depot.com/roger/questions_invalide
		$résultat_obtenu = $this->actingAs($this->user)->call(
			"GET",
			"/question/aHR0cHM6Ly9kZXBvdC5jb20vcm9nZXIvcXVlc3Rpb25zX2ludmFsaWRl",
		);

		$this->assertEquals(422, $résultat_obtenu->status());
		$this->assertEquals('{"erreur":"Question invalide."}', $résultat_obtenu->getContent());
	}
}
