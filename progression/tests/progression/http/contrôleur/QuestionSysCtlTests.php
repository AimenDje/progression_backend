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
use progression\domaine\entité\question\{Question, QuestionSys, Type};
use progression\domaine\entité\TestSys;
use progression\domaine\entité\user\{User, Rôle, État};
use progression\dao\DAOFactory;
use progression\dao\question\ChargeurException;
use progression\UserAuthentifiable;

final class QuestionSysCtlTests extends ContrôleurTestCase
{
	public $user;

	public function setUp(): void
	{
		parent::setUp();
		$this->user = new UserAuthentifiable(
			username: "jdoe",
			date_inscription: 0,
			rôle: Rôle::NORMAL,
			état: État::ACTIF,
		);

		//QuestionSys avec solution sans pregmatch
		$questionSys = new QuestionSys();
		$questionSys->type = Type::SYS;
		$questionSys->nom = "toutes_les_permissions2";
		$questionSys->solution = "laSolution";
		$questionSys->uri = "https://depot.com/roger/questions_sys/permissions01/octroyer_toutes_les_permissions2";
		$questionSys->feedback_pos = "Bon travail!";
		$questionSys->feedback_neg = "Encore un effort!";

		$questionSys->titre = "Octroyer toutes le permissions";
		$questionSys->objectif = "Octroiement de toutes les permissions.";
		$questionSys->description = "Ceci est une question système complète";
		$questionSys->enonce =
			"Il faut que l'étudiant trouve les commandes justes pour octroyer toutes les permissions à l'utilisateur krusty.";
		$questionSys->auteur = "Albert Einstein";
		$questionSys->licence = "poétique";
		$questionSys->niveau = "débutant";
		$questionSys->image = "l'image";
		$questionSys->utilisateur = "utilisateur";

		$questionSys->tests = [
			new TestSys("test 1", "vrai", "[ -z vrai ]", "bob"),
			new TestSys("test 2", "faux", "[ -z faux ]", "roger"),
		];

		$mockQuestionDAO = Mockery::mock("progression\\dao\\question\\QuestionDAO");
		$mockQuestionDAO
			->shouldReceive("get_question")
			->with("https://depot.com/roger/questions_sys/permissions01/octroyer_toutes_les_permissions2")
			->andReturn($questionSys);

		// DAOFactory
		$mockDAOFactory = Mockery::mock("progression\\dao\\DAOFactory");
		$mockDAOFactory->shouldReceive("get_question_dao")->andReturn($mockQuestionDAO);
		DAOFactory::setInstance($mockDAOFactory);
	}

	public function test_étant_donné_le_chemin_dune_question_lorsquon_appelle_get_on_obtient_la_question_et_ses_relations_sous_forme_json()
	{
		$résultat_obtenu = $this->actingAs($this->user)->call(
			"GET",
			"/question/aHR0cHM6Ly9kZXBvdC5jb20vcm9nZXIvcXVlc3Rpb25zX3N5cy9wZXJtaXNzaW9uczAxL29jdHJveWVyX3RvdXRlc19sZXNfcGVybWlzc2lvbnMy?include=tests",
		);

		//$this->assertEquals(200, $résultat_obtenu->status());
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/questionCtlTests_question_sys.json",
			$résultat_obtenu->getContent(),
		);
	}
}
