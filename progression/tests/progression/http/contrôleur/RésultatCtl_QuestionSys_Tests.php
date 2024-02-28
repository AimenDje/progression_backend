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
use progression\dao\exécuteur\ExécutionException;
use progression\domaine\entité\{TestSys, Résultat, TentativeSys};
use progression\domaine\entité\question\{Question, QuestionSys};
use progression\domaine\entité\user\{User, Rôle, État};
use progression\dao\question\ChargeurException;
use progression\UserAuthentifiable;

final class RésultatCtl_QuestionSys_Tests extends ContrôleurTestCase
{
	public function setUp(): void
	{
		parent::setUp();

		$this->user = new UserAuthentifiable(
			username: "jdoe",
			date_inscription: 0,
			rôle: Rôle::NORMAL,
			état: État::ACTIF,
		);

		// QuestionProg
		//aHR0cHM6Ly9kZXBvdC5jb20vcXVlc3Rpb25fZGVfc2FsdXRhdGlvbnM
		$question_de_salutations = new QuestionSys(
			niveau: "Débutant",
			titre: "Question de salutation",
			feedback_pos: "Bon travail!",
			feedback_neg: "Encore un effort!",
			// TestsProg
			tests: [
				new TestSys(
					nom: "1 salutation",
					sortie_attendue: "Bonjour\n",
					validation: "true && echo Bonjour",
					utilisateur: "joe",
					feedback_pos: "C'est ça!",
					feedback_neg: "C'est pas ça :(",
					caché: false,
				),
				new TestSys(
					nom: "2 salutations",
					sortie_attendue: "Bonjour\nBonjour\n",
					validation: "false && echo Bonjour\nBonjour",
					utilisateur: "joe",
					feedback_pos: "C'est ça!",
					feedback_neg: "C'est pas ça :(",
					caché: false,
				),
				new TestSys(
					nom: "pas de salutation",
					sortie_attendue: "\n",
					validation: "true && echo Bonjour\nBonjour",
					utilisateur: "joe",
					feedback_pos: "C'est ça!",
					feedback_neg: "C'est pas ça :(",
					caché: false,
				),
				new TestSys(
					nom: "salutations cachées",
					sortie_attendue: "Bonjour\n",
					validation: "true && echo Bonjour",
					utilisateur: "joe",
					feedback_pos: "C'est ça!",
					feedback_neg: "C'est pas ça :(",
					caché: true,
				),
			],
		);

		$mockTentativeDAO = Mockery::mock("progression\\dao\\tentative\\TentativeSysDAO");
		$mockTentativeDAO
			->shouldReceive("get_dernière")
			->andReturn(new TentativeSys("dernier conteneur", "http://ttyshare.com/abcde"));
		$mockQuestionDAO = Mockery::mock("progression\\dao\\question\\QuestionDAO");
		$mockQuestionDAO
			->shouldReceive("get_question")
			->with("https://depot.com/question_de_salutations")
			->andReturn($question_de_salutations);
		$mockQuestionDAO->shouldReceive("get_question")->andReturn(null);

		// Exécuteur
		$mockExécuteur = Mockery::mock("progression\\dao\\exécuteur\\Exécuteur");
		$mockExécuteur
			->shouldReceive("exécuter_sys")
			->with(Mockery::Any(), Mockery::Any(), Mockery::Any(), Mockery::Any(), Mockery::Any(), 0, Mockery::Any())
			->andReturn([
				"temps_exécution" => 0.551,
				"résultats" => [
					"abcdef0123456789" => [
						"output" => "Bonjour\n",
						"errors" => "",
						"time" => 0.03,
						"code" => 0,
					],
					"sans importance 0" => [
						"output" => "Bonjour\n",
						"errors" => "",
						"time" => 0.03,
						"code" => 0,
					],
					"sans importance 1" => [
						"output" => "Bonjour\n",
						"errors" => "",
						"time" => 0.03,
						"code" => 0,
					],
					"sans importance 2" => [
						"output" => "Bonjour\n",
						"errors" => "",
						"time" => 0.03,
						"code" => 0,
					],
				],
				"conteneur_id" => "cafebaba01234",
				"url_terminal" => "http://ttyshare.com/abcde",
			]);
		$mockExécuteur
			->shouldReceive("exécuter_sys")
			->with(Mockery::Any(), Mockery::Any(), Mockery::Any(), Mockery::Any(), Mockery::Any(), 1, Mockery::Any())
			->andReturn([
				"temps_exécution" => 0.551,
				"résultats" => [
					"sans importance 0" => [
						"output" => "Bonjour\n",
						"errors" => "",
						"time" => 0.03,
						"code" => 0,
					],
					"abcdef0123456789" => [
						"output" => "Bonjour\nBonjour\n",
						"errors" => "",
						"time" => 0.03,
						"code" => 1,
					],
					"sans importance 1" => [
						"output" => "Bonjour\n",
						"errors" => "",
						"time" => 0.03,
						"code" => 0,
					],
					"sans importance 2" => [
						"output" => "Bonjour\n",
						"errors" => "",
						"time" => 0.03,
						"code" => 0,
					],
				],
				"conteneur_id" => "cafebaba01234",
				"url_terminal" => "http://ttyshare.com/abcde",
			]);
		$mockExécuteur
			->shouldReceive("exécuter_sys")
			->with(Mockery::Any(), Mockery::Any(), Mockery::Any(), Mockery::Any(), Mockery::Any(), 2, Mockery::Any())
			->andReturn([
				"temps_exécution" => 0.551,
				"résultats" => [
					"sans importance 0" => [
						"output" => "Bonjour\n",
						"errors" => "",
						"time" => 0.03,
						"code" => 0,
					],
					"sans importance 1" => [
						"output" => "Bonjour\n",
						"errors" => "",
						"time" => 0.03,
						"code" => 0,
					],
					"abcdef0123456789" => [
						"output" => "Mauvaise sortie\n",
						"errors" => "",
						"time" => 0.03,
						"code" => 0,
					],
					"sans importance 2" => [
						"output" => "Bonjour\n",
						"errors" => "",
						"time" => 0.03,
						"code" => 0,
					],
				],
				"conteneur_id" => "cafebaba01234",
				"url_terminal" => "http://ttyshare.com/abcde",
			]);
		$mockExécuteur
			->shouldReceive("exécuter_sys")
			->with(Mockery::Any(), Mockery::Any(), Mockery::Any(), Mockery::Any(), Mockery::Any(), 3, Mockery::Any())
			->andReturn([
				"temps_exécution" => 0.551,
				"résultats" => [
					"sans importance 0" => [
						"output" => "Bonjour\n",
						"errors" => "",
						"time" => 0.03,
						"code" => 0,
					],
					"sans importance 1" => [
						"output" => "Bonjour\n",
						"errors" => "",
						"time" => 0.03,
						"code" => 0,
					],
					"sans importance 2" => [
						"output" => "Bonjour\n",
						"errors" => "",
						"time" => 0.03,
						"code" => 0,
					],
					"abcdef0123456789" => [
						"output" => "Bonjour\n",
						"errors" => "",
						"time" => 0.03,
						"code" => 0,
					],
				],
				"conteneur_id" => "cafebaba01234",
				"url_terminal" => "http://ttyshare.com/abcde",
			]);

		// User
		$mockUserDAO = Mockery::mock("progression\\dao\\UserDAO");
		$mockUserDAO->allows("get_user")->with("jdoe")->andReturn(new User(username: "jdoe", date_inscription: 0));

		// DAOFactory
		$mockDAOFactory = Mockery::mock("progression\\dao\\DAOFactory");
		$mockDAOFactory->shouldReceive("get_question_dao")->andReturn($mockQuestionDAO);
		$mockDAOFactory->shouldReceive("get_tentative_dao")->andReturn($mockTentativeDAO);
		$mockDAOFactory->shouldReceive("get_exécuteur")->andReturn($mockExécuteur);
		$mockDAOFactory->shouldReceive("get_user_dao")->andReturn($mockUserDAO);

		DAOFactory::setInstance($mockDAOFactory);
	}

	public function test_étant_donné_un_test_unique_valide_lorsquil_est_soumis_on_obtient_le_résultat_réussi_pour_le_test_fourni()
	{
		$résultat_obtenu = $this->actingAs($this->user)->call(
			"POST",
			"/question/aHR0cHM6Ly9kZXBvdC5jb20vcXVlc3Rpb25fZGVfc2FsdXRhdGlvbnM/resultats",
			[
				"index" => 0,
			],
		);

		$this->assertEquals(200, $résultat_obtenu->status());

		$this->assertJsonStringEqualsJsonString(
			file_get_contents(__DIR__ . "/résultats_attendus/résultat_sys_unique_réussi.json"),
			$résultat_obtenu->getContent(),
		);
	}

	public function test_étant_donné_un_test_unique_avec_code_de_retour_non_nul_lorsquil_est_soumis_on_obtient_le_résultat_non_réussi_pour_le_test_fourni()
	{
		$résultat_obtenu = $this->actingAs($this->user)->call(
			"POST",
			"/question/aHR0cHM6Ly9kZXBvdC5jb20vcXVlc3Rpb25fZGVfc2FsdXRhdGlvbnM/resultats",
			[
				"index" => 1,
			],
		);

		$this->assertEquals(200, $résultat_obtenu->status());

		$this->assertJsonStringEqualsJsonString(
			file_get_contents(__DIR__ . "/résultats_attendus/résultat_sys_unique_code_retour_non_nul.json"),
			$résultat_obtenu->getContent(),
		);
	}

	public function test_étant_donné_un_test_unique_avec_sortie_inattendue_lorsquil_est_soumis_on_obtient_le_résultat_non_réussi_pour_le_test_fourni()
	{
		$résultat_obtenu = $this->actingAs($this->user)->call(
			"POST",
			"/question/aHR0cHM6Ly9kZXBvdC5jb20vcXVlc3Rpb25fZGVfc2FsdXRhdGlvbnM/resultats",
			[
				"index" => 2,
			],
		);

		$this->assertEquals(200, $résultat_obtenu->status());

		$this->assertJsonStringEqualsJsonString(
			file_get_contents(__DIR__ . "/résultats_attendus/résultat_sys_unique_sortie_inattendue.json"),
			$résultat_obtenu->getContent(),
		);
	}

	public function test_étant_donné_un_test_unique_caché_lorsquil_est_soumis_on_obtient_le_résultat_caviardé()
	{
		$résultat_obtenu = $this->actingAs($this->user)->call(
			"POST",
			"/question/aHR0cHM6Ly9kZXBvdC5jb20vcXVlc3Rpb25fZGVfc2FsdXRhdGlvbnM/resultats",
			[
				"index" => 3,
			],
		);

		$this->assertEquals(200, $résultat_obtenu->status());

		$this->assertJsonStringEqualsJsonString(
			file_get_contents(__DIR__ . "/résultats_attendus/résultat_sys_unique_sortie_cachée.json"),
			$résultat_obtenu->getContent(),
		);
	}

	public function test_étant_donné_un_test_unique_sans_numéro_de_test_lorsquil_est_soumis_on_obtient_une_erreur_400()
	{
		$résultat_obtenu = $this->actingAs($this->user)->call(
			"POST",
			"/question/aHR0cHM6Ly9kZXBvdC5jb20vcXVlc3Rpb25fZGVfc2FsdXRhdGlvbnM/resultats",
			[],
		);

		$this->assertEquals(400, $résultat_obtenu->status());
	}

	public function test_étant_donné_un_test_unique_avec_un_numéro_de_test_inexistant_lorsquil_est_soumis_on_obtient_une_erreur_400()
	{
		$résultat_obtenu = $this->actingAs($this->user)->call(
			"POST",
			"/question/aHR0cHM6Ly9kZXBvdC5jb20vcXVlc3Rpb25fZGVfc2FsdXRhdGlvbnM/resultats",
			[
				"index" => 66,
			],
		);

		$this->assertEquals(400, $résultat_obtenu->status());
	}

	public function test_étant_donné_un_test_unique_avec_un_numéro_de_test_non_numérique_lorsquil_est_soumis_on_obtient_une_erreur_400()
	{
		$résultat_obtenu = $this->actingAs($this->user)->call(
			"POST",
			"/question/aHR0cHM6Ly9kZXBvdC5jb20vcXVlc3Rpb25fZGVfc2FsdXRhdGlvbnM/resultats",
			[
				"index" => "zéro",
			],
		);

		$this->assertEquals(400, $résultat_obtenu->status());
	}
}
