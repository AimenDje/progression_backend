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
use progression\domaine\entité\{TestProg, Exécutable, Résultat};
use progression\domaine\entité\question\{Question, QuestionProg};
use progression\domaine\entité\user\{User, Rôle, État};
use progression\UserAuthentifiable;

final class RésultatCtl_QuestionProg_Tests extends ContrôleurTestCase
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

		// QuestionProg
		//aHR0cHM6Ly9kZXBvdC5jb20vcXVlc3Rpb25fcsOpdXNzaWU
		$question_réussie = new QuestionProg(
			titre: "Question réussie",
			niveau: "Débutant",
			feedback_pos: "Bon travail!",
			feedback_neg: "Encore un effort!",
			feedback_err: "oups!",
			exécutables: [
				// Ébauches
				"réussi" => new Exécutable("#+TODO\nprint(\"Hello world!\")", "réussi"),
				"non_réussi" => new Exécutable("//+TODO\nSystem.out.println(\"Hello world!\")", "non_réussi"),
				"erreur" => new Exécutable("//+TODO\nSystem.out.println(\"Hello world!\")", "erreur"),
				"pas d'exécuteur" => new Exécutable("//+TODO\nSystem.out.println(\"Hello world!\")", "erreur"),
			],
			// TestsProg
			tests: [
				new TestProg(
					nom: "1 salutations",
					sortie_attendue: "Bonjour\n",
					entrée: "1",
					params: "",
					feedback_pos: "C'est ça!",
					feedback_neg: "C'est pas ça :(",
					feedback_err: "arrrg!",
					caché: false,
				),
				new TestProg(
					nom: "2 salutations",
					sortie_attendue: "Bonjour\nBonjour\n",
					entrée: "2",
					params: "",
					feedback_pos: "C'est ça!",
					feedback_neg: "C'est pas ça :(",
					feedback_err: "arrrg!",
					caché: true,
				),
				new TestProg(
					nom: "3 salutations",
					sortie_attendue: "Bonjour\nBonjour\nBonjour\n",
					entrée: "3",
					params: "",
					feedback_pos: "C'est ça!",
					feedback_neg: "C'est pas ça :(",
					feedback_err: "arrrg!",
					caché: false,
				),
			],
		);

		$mockQuestionDAO = Mockery::mock("progression\\dao\\question\\QuestionDAO");
		$mockQuestionDAO
			->shouldReceive("get_question")
			->with("https://depot.com/question_réussie")
			->andReturn($question_réussie);
		$mockQuestionDAO->shouldReceive("get_question")->andReturn(null);

		// Exécuteur
		$mockExécuteur = Mockery::mock("progression\\dao\\exécuteur\\Exécuteur");
		$mockExécuteur
			->shouldReceive("exécuter_prog")
			->withArgs(function ($exec, $test) {
				return $exec->lang == "réussi";
			})
			->andReturn([
				"temps_exécution" => 0.551,
				"résultats" => [
					"abcdef0123456789" => [
						"output" => "Bonjour\nBonjour\nBonjour\n",
						"errors" => "",
						"time" => 0.03,
					],
				],
			]);
		$mockExécuteur
			->shouldReceive("exécuter_prog")
			->withArgs(function ($exec, $test) {
				return $exec->lang == "non_réussi";
			})
			->andReturn([
				"temps_exécution" => 0.552,
				"résultats" => [
					"abcdef0123456789" => [
						"output" => "Mauvaise sortie\n",
						"errors" => "",
						"time" => 0.03,
					],
				],
			]);
		$mockExécuteur
			->shouldReceive("exécuter_prog")
			->withArgs(function ($exec, $test) {
				return $exec->lang == "erreur";
			})
			->andReturn([
				"temps_exécution" => 0.552,
				"résultats" => [
					"abcdef0123456789" => [
						"output" => "",
						"errors" => "Erreur!",
						"time" => 0.03,
					],
				],
			]);
		$mockExécuteur
			->shouldReceive("exécuter_prog")
			->withArgs(function ($exec, $test) {
				return $exec->lang == "pas d'exécuteur";
			})
			->andThrow(new ExécutionException("Exécuteur non disponible.", 503));

		// User
		$mockUserDAO = Mockery::mock("progression\\dao\\UserDAO");
		$mockUserDAO->allows("get_user")->with("jdoe")->andReturn(new User(username: "jdoe", date_inscription: 0));

		// DAOFactory
		$mockDAOFactory = Mockery::mock("progression\\dao\\DAOFactory");
		$mockDAOFactory->shouldReceive("get_question_dao")->andReturn($mockQuestionDAO);
		$mockDAOFactory->shouldReceive("get_exécuteur")->andReturn($mockExécuteur);
		$mockDAOFactory->shouldReceive("get_user_dao")->andReturn($mockUserDAO);

		DAOFactory::setInstance($mockDAOFactory);
	}

	public function test_étant_donné_un_test_unique_lorsquil_est_soumis_on_obtient_le_résultat_réussi_pour_le_test_fourni()
	{
		$résultat_obtenu = $this->actingAs($this->user)->call(
			"POST",
			"/question/aHR0cHM6Ly9kZXBvdC5jb20vcXVlc3Rpb25fcsOpdXNzaWU/resultats",
			[
				"langage" => "réussi",
				"code" => "#+TODO\nprint(\"Hello world!\")",
				"test" => [
					"nom" => "Bonjour",
					"entrée" => "bonjour",
					"sortie_attendue" => "Bonjour\nBonjour\nBonjour\n",
				],
			],
		);

		$this->assertEquals(200, $résultat_obtenu->status());

		$this->assertJsonStringEqualsJsonString(
			file_get_contents(__DIR__ . "/résultats_attendus/résultat_prog_unique_soumis_avec_test.json"),
			$résultat_obtenu->getContent(),
		);
	}

	public function test_étant_donné_un_test_unique_raté_lorsquil_est_soumis_on_obtient_le_résultat_non_réussi_pour_le_test_fourni()
	{
		$résultat_obtenu = $this->actingAs($this->user)->call(
			"POST",
			"/question/aHR0cHM6Ly9kZXBvdC5jb20vcXVlc3Rpb25fcsOpdXNzaWU/resultats",
			[
				"langage" => "non_réussi",
				"code" => "#+TODO\nprint(\"Hello world!\")",
				"test" => [
					"nom" => "Bonjour",
					"entrée" => "bonjour",
					"sortie_attendue" => "Bonjour\nBonjour\nBonjour\n",
				],
			],
		);

		$this->assertEquals(200, $résultat_obtenu->status());

		$this->assertJsonStringEqualsJsonString(
			file_get_contents(__DIR__ . "/résultats_attendus/résultat_prog_unique_soumis_avec_test_non_réussi.json"),
			$résultat_obtenu->getContent(),
		);
	}

	public function test_étant_donné_un_test_unique_erroné_lorsquil_est_soumis_on_obtient_le_résultat_avec_erreur_pour_le_test_fourni()
	{
		$résultat_obtenu = $this->actingAs($this->user)->call(
			"POST",
			"/question/aHR0cHM6Ly9kZXBvdC5jb20vcXVlc3Rpb25fcsOpdXNzaWU/resultats",
			[
				"langage" => "erreur",
				"code" => "#+TODO\nprint(\"Hello world!\")",
				"test" => [
					"nom" => "Bonjour",
					"entrée" => "bonjour",
					"sortie_attendue" => "Bonjour\nBonjour\nBonjour\n",
				],
			],
		);

		$this->assertEquals(200, $résultat_obtenu->status());

		$this->assertJsonStringEqualsJsonString(
			file_get_contents(__DIR__ . "/résultats_attendus/résultat_prog_unique_soumis_avec_test_erreur.json"),
			$résultat_obtenu->getContent(),
		);
	}

	public function test_étant_donné_un_test_unique_comportant_un_numéro_de_test_lorsquil_est_soumis_on_obtient_le_résultat_réussi_pour_le_test_de_la_question()
	{
		$résultat_obtenu = $this->actingAs($this->user)->call(
			"POST",
			"/question/aHR0cHM6Ly9kZXBvdC5jb20vcXVlc3Rpb25fcsOpdXNzaWU/resultats",
			[
				"langage" => "réussi",
				"code" => "#+TODO\nprint(\"Hello world!\")",
				"index" => 2,
			],
		);

		$this->assertEquals(200, $résultat_obtenu->status());

		$this->assertJsonStringEqualsJsonString(
			file_get_contents(__DIR__ . "/résultats_attendus/résultat_prog_unique_soumis_avec_indice.json"),
			$résultat_obtenu->getContent(),
		);
	}

	public function test_étant_donné_un_test_unique_caché_lorsquil_est_soumis_on_obtient_le_résultat_caviardé()
	{
		$résultat_obtenu = $this->actingAs($this->user)->call(
			"POST",
			"/question/aHR0cHM6Ly9kZXBvdC5jb20vcXVlc3Rpb25fcsOpdXNzaWU/resultats",
			[
				"langage" => "réussi",
				"code" => "#+TODO\nprint(\"Hello world!\")",
				"index" => 1,
			],
		);

		$this->assertEquals(200, $résultat_obtenu->status());

		$this->assertJsonStringEqualsJsonString(
			file_get_contents(__DIR__ . "/résultats_attendus/résultat_prog_unique_soumis_avec_test_caché.json"),
			$résultat_obtenu->getContent(),
		);
	}

	public function test_étant_donné_un_test_unique_comportant_un_numéro_de_test_et_un_test_lorsquil_est_soumis_on_obtient_le_résultat_réussi_pour_le_test_reçu()
	{
		$résultat_obtenu = $this->actingAs($this->user)->call(
			"POST",
			"/question/aHR0cHM6Ly9kZXBvdC5jb20vcXVlc3Rpb25fcsOpdXNzaWU/resultats",
			[
				"langage" => "réussi",
				"code" => "#+TODO\nprint(\"Hello world!\")",
				"index" => 0,
				"test" => [
					"nom" => "Bonjour",
					"entrée" => "bonjour",
					"sortie_attendue" => "Bonjour\nBonjour\nBonjour\n",
				],
			],
		);

		$this->assertEquals(200, $résultat_obtenu->status());

		$this->assertJsonStringEqualsJsonString(
			file_get_contents(__DIR__ . "/résultats_attendus/résultat_prog_unique_soumis_avec_indice_et_test.json"),
			$résultat_obtenu->getContent(),
		);
	}

	public function test_étant_donné_un_test_unique_sans_test_lorsquil_est_soumis_on_obtient_une_erreur_400()
	{
		$résultat_obtenu = $this->actingAs($this->user)->call(
			"POST",
			"/question/aHR0cHM6Ly9kZXBvdC5jb20vcXVlc3Rpb25fcsOpdXNzaWU/resultats",
			[
				"langage" => "réussi",
				"code" => "#+TODO\nprint(\"Hello world!\")",
			],
		);

		$this->assertEquals(400, $résultat_obtenu->status());
		$this->assertEquals(
			'{"erreur":{"test":["Le champ test est obligatoire lorsque index n\'est pas présent."]}}',
			$résultat_obtenu->getContent(),
		);
	}

	public function test_étant_donné_un_test_unique_sans_langage_lorsquil_est_soumis_on_obtient_une_erreur_400()
	{
		$résultat_obtenu = $this->actingAs($this->user)->call(
			"POST",
			"/question/aHR0cHM6Ly9kZXBvdC5jb20vcXVlc3Rpb25fcsOpdXNzaWU/resultats",
			[
				"code" => "#+TODO\nprint(\"Hello world!\")",
				"test" => 1,
			],
		);

		$this->assertEquals(400, $résultat_obtenu->status());
		$this->assertEquals(
			'{"erreur":{"langage":["Le champ langage est obligatoire."]}}',
			$résultat_obtenu->getContent(),
		);
	}

	public function test_étant_donné_un_test_unique_sans_code_lorsquil_est_soumis_on_obtient_une_erreur_400()
	{
		$résultat_obtenu = $this->actingAs($this->user)->call(
			"POST",
			"/question/aHR0cHM6Ly9kZXBvdC5jb20vcXVlc3Rpb25fcsOpdXNzaWU/resultats",
			[
				"langage" => "python",
				"test" => 1,
			],
		);

		$this->assertEquals(400, $résultat_obtenu->status());
		$this->assertEquals('{"erreur":{"code":["Le champ code est obligatoire."]}}', $résultat_obtenu->getContent());
	}

	public function test_étant_donné_un_test_unique_pour_une_question_inexistante_lorsquil_est_soumis_on_obtient_une_erreur_404()
	{
		$résultat_obtenu = $this->actingAs($this->user)->call(
			"POST",
			"/question/aHR0cHM6Ly9leGVtcGxlLmNvbS9xdWVzdGlvbl9pbnRyb3V2YWJsZS55bWw/resultats",
			[
				"langage" => "réussi",
				"code" => "#+TODO\nprint(\"Hello world!\")",
				"test" => 1,
			],
		);

		$this->assertEquals(404, $résultat_obtenu->status());
		$this->assertEquals(
			'{"erreur":"La question https:\/\/exemple.com\/question_introuvable.yml n\'existe pas."}',
			$résultat_obtenu->getContent(),
		);
	}

	public function test_étant_donné_un_test_unique_avec_indice_de_test_inexistant_lorsquil_est_soumis_on_obtient_une_erreur_400()
	{
		$résultat_obtenu = $this->actingAs($this->user)->call(
			"POST",
			"/question/aHR0cHM6Ly9kZXBvdC5jb20vcXVlc3Rpb25fcsOpdXNzaWU/resultats",
			[
				"langage" => "réussi",
				"code" => "#+TODO\nprint(\"Hello world!\")",
				"index" => 42,
			],
		);

		$this->assertEquals(400, $résultat_obtenu->status());
		$this->assertEquals('{"erreur":"L\'indice de test n\'existe pas."}', $résultat_obtenu->getContent());
	}

	public function test_étant_donné_un_test_unique_ayant_du_code_dépassant_la_taille_maximale_de_caractères_on_obtient_une_erreur_400()
	{
		putenv("TAILLE_CODE_MAX=23");
		$testCode = "#+TODO\n日本語でのテストです\n#-TODO"; //24 caractères UTF8

		$résultat_obtenu = $this->actingAs($this->user)->call(
			"POST",
			"/question/aHR0cHM6Ly9kZXBvdC5jb20vcXVlc3Rpb25fcsOpdXNzaWU/resultats/",
			[
				"index" => 0,
				"langage" => "réussi",
				"code" => "$testCode",
			],
		);
		putenv("TAILLE_CODE_MAX=1000");

		$this->assertEquals(400, $résultat_obtenu->status());
		$this->assertEquals(
			'{"erreur":{"code":["Le code soumis 24 > 23 caractères."]}}',
			$résultat_obtenu->getContent(),
		);
	}

	public function test_étant_donné_un_test_unique_ayant_exactement_la_taille_maximale_de_caractères_on_obtient_un_code_200()
	{
		putenv("TAILLE_CODE_MAX=24");
		$testCode = "#+TODO\n日本語でのテストです\n#-TODO"; //24 caractères UTF8

		$résultat_obtenu = $this->actingAs($this->user)->call(
			"POST",
			"/question/aHR0cHM6Ly9kZXBvdC5jb20vcXVlc3Rpb25fcsOpdXNzaWU/resultats",
			[
				"index" => 0,
				"langage" => "réussi",
				"code" => "$testCode",
			],
		);
		putenv("TAILLE_CODE_MAX=1000");

		$this->assertEquals(200, $résultat_obtenu->status());
	}

	public function test_étant_donné_un_test_unique_compilebox_inaccessible_lorsquon_appelle_post_on_obtient_Service_non_disponible()
	{
		$résultat_obtenu = $this->actingAs($this->user)->call(
			"POST",
			"/question/aHR0cHM6Ly9kZXBvdC5jb20vcXVlc3Rpb25fcsOpdXNzaWU/resultats/",
			[
				"langage" => "pas d'exécuteur",
				"code" => "#+TODO\nprint(\"on ne se rendra pas à exécuter ceci\")",
				"index" => 0,
			],
		);

		$this->assertEquals(503, $résultat_obtenu->status());
		$this->assertEquals('{"erreur":"Exécuteur non disponible."}', $résultat_obtenu->getContent());
	}
}
