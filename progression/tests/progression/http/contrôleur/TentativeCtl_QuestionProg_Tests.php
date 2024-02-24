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
use progression\domaine\entité\question\{Question, QuestionProg};
use progression\domaine\entité\{Avancement, TestProg, Exécutable, TentativeProg, Commentaire, Résultat};
use progression\domaine\entité\user\{User, Rôle, État};
use progression\dao\question\ChargeurException;

use progression\UserAuthentifiable;
use Carbon\Carbon;

final class TentativeCtl_QuestionProg_Tests extends ContrôleurTestCase
{
	public $user;
	public $avancement_réussi;
	public $avancement_non_réussi;
	public $tentative_réussie;
	public $tentative_non_réussie;

	public function setUp(): void
	{
		parent::setUp();

		Carbon::setTestNow(Carbon::create(2022, 05, 27, 22, 24, 01));

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
			],
			// TestsProg
			tests: [
				new TestProg(
					nom: "1 salutation",
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
			],
		);

		//aHR0cHM6Ly9kZXBvdC5jb20vcXVlc3Rpb25fbm9uX3LDqXVzc2ll
		$question_non_réussie = new QuestionProg(
			titre: "Question non réussie",
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
					nom: "1 salutation",
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
			],
		);

		$mockQuestionDAO = Mockery::mock("progression\\dao\\question\\QuestionDAO");
		$mockQuestionDAO
			->shouldReceive("get_question")
			->with("https://depot.com/question_réussie")
			->andReturn($question_réussie);
		$mockQuestionDAO
			->shouldReceive("get_question")
			->with("https://depot.com/question_non_réussie")
			->andReturn($question_non_réussie);
		$mockQuestionDAO
			->shouldReceive("get_question")
			->with("https://depot.com/nouvelle_question")
			->andReturn($question_réussie);
		$mockQuestionDAO->shouldReceive("get_question")->with(Mockery::Any())->andReturn(null);

		// Tentative
		// Tentative réussie

		$this->tentative_réussie = new TentativeProg(
			langage: "réussi",
			code: "codeTest",
			date_soumission: "1614374490",
			réussi: true,
			résultats: [],
			tests_réussis: 2,
			feedback: "feedbackTest",
			temps_exécution: 5,
			commentaires: [],
		);

		$this->tentative_réussie_avec_commentaires = new TentativeProg(
			langage: "réussi",
			code: "codeTest",
			date_soumission: "1614374490",
			réussi: true,
			résultats: [],
			tests_réussis: 2,
			feedback: "feedbackTest",
			temps_exécution: 5,
			commentaires: [
				new Commentaire("message", new User(username: "créateur", date_inscription: 0), 1614374490, 42),
			],
		);

		// Tentative non réussie
		$this->tentative_non_réussie = new TentativeProg(
			langage: "non_réussi",
			code: "codeTest",
			date_soumission: "1614374490",
			réussi: false,
			résultats: [],
			tests_réussis: 0,
			feedback: "feedbackTest",
			temps_exécution: 5,
		);

		$mockTentativeDAO = Mockery::mock("progression\\dao\\tentative\\TentativeProgDAO");
		$mockTentativeDAO
			->shouldReceive("get_tentative")
			->with("jdoe", "https://depot.com/question_réussie", "1614374490", [])
			->andReturn($this->tentative_réussie);
		$mockTentativeDAO
			->shouldReceive("get_tentative")
			->with("jdoe", "https://depot.com/question_réussie", "1614374490", ["commentaires"])
			->andReturn($this->tentative_réussie_avec_commentaires);
		$mockTentativeDAO
			->shouldReceive("get_tentative")
			->with("jdoe", "https://depot.com/question_non_réussie", "1614374490", [])
			->andReturn($this->tentative_non_réussie);
		$mockTentativeDAO->shouldReceive("get_tentative")->andReturn(null);

		// Commentaire
		$commentaire = new Commentaire(
			"le 99iem message",
			new User(username: "créateur", date_inscription: 0),
			1615696276,
			14,
		);
		$mockCommentaireDAO = Mockery::mock("progression\\dao\\CommentaireDAO");
		$mockCommentaireDAO
			->shouldReceive("get_commentaires_par_tentative")
			->with("jdoe", "https://depot.com/question_réussie", 1614374490)
			->andReturn($commentaire);

		// Exécuteur
		$mockExécuteur = Mockery::mock("progression\\dao\\exécuteur\\Exécuteur");
		$mockExécuteur
			->shouldReceive("exécuter_prog")
			->withArgs(function ($exec, $test) {
				return $exec->lang == "réussi" && count($test) == 2;
			})
			->andReturn([
				"temps_exécution" => 0.551,
				"résultats" => [
					"bebe123" => [
						"output" => "Bonjour\n",
						"errors" => "",
						"time" => 0.03,
					],
					"cafe456" => [
						"output" => "Bonjour\nBonjour\n",
						"errors" => "",
						"time" => 0.03,
					],
				],
			]);
		$mockExécuteur
			->shouldReceive("exécuter_prog")
			->withArgs(function ($exec, $test) {
				return $exec->lang == "réussi" && count($test) == 1;
			})
			->andReturn([
				"temps_exécution" => 0.551,
				"résultats" => [
					"bebe456" => [
						"output" => "Bonjour\nBonjour\n",
						"errors" => "",
						"time" => 0.03,
					],
				],
			]);
		$mockExécuteur
			->shouldReceive("exécuter_prog")
			->withArgs(function ($exec, $test) {
				return $exec->lang == "non_réussi" && count($test) == 2;
			})
			->andReturn([
				"temps_exécution" => 0.44,
				"résultats" => [
					"bebe123" => [
						"output" => "Allo\n",
						"errors" => "",
						"time" => 0.03,
					],
					"cafe456" => [
						"output" => "Allo\nAllo\n",
						"errors" => "",
						"time" => 0.03,
					],
				],
			]);
		$mockExécuteur
			->shouldReceive("exécuter_prog")
			->withArgs(function ($exec, $test) {
				return $exec->lang == "non_réussi" && count($test) == 1;
			})
			->andReturn([
				"temps_exécution" => 0.44,
				"résultats" => [
					"cafe123" => [
						"output" => "Allo\nAllo\n",
						"errors" => "",
						"time" => 0.03,
					],
				],
			]);
		$mockExécuteur
			->shouldReceive("exécuter_prog")
			->withArgs(function ($exec, $test) {
				return $exec->lang == "erreur" && count($test) == 2;
			})
			->andReturn([
				"temps_exécution" => 0.44,
				"résultats" => [
					"bebe123" => [
						"output" => "Allo\n",
						"errors" => "",
						"time" => 0.03,
					],
					"cafe456" => [
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

		//Avancement

		// Avancement réussi
		$this->avancement_réussi = new Avancement([$this->tentative_réussie], "Question réussie", "Débutant");

		// Avancement non réussi
		$this->avancement_non_réussi = new Avancement(
			[$this->tentative_non_réussie],
			"Question non réussie",
			"Débutant",
		);

		$mockAvancementDAO = Mockery::mock("progression\\dao\\AvancementDAO");
		$mockAvancementDAO
			->shouldReceive("get_avancement")
			->withArgs(["jdoe", "https://depot.com/question_réussie", []])
			->andReturnValues([
				new Avancement([$this->tentative_réussie], "Question réussie", "Débutant"),
				new Avancement([$this->tentative_réussie], "Question réussie", "Débutant"),
			]);

		$mockAvancementDAO
			->shouldReceive("get_avancement")
			->with("jdoe", "https://depot.com/question_non_réussie", [])
			->andReturn($this->avancement_non_réussi);
		$mockAvancementDAO
			->shouldReceive("get_avancement")
			->with("jdoe", "https://depot.com/nouvelle_question", [])
			->andReturn(null);

		// User
		$mockUserDAO = Mockery::mock("progression\\dao\\UserDAO");
		$mockUserDAO->allows("get_user")->with("jdoe")->andReturn(new User(username: "jdoe", date_inscription: 0));

		// DAOFactory
		$mockDAOFactory = Mockery::mock("progression\\dao\\DAOFactory");
		$mockDAOFactory->shouldReceive("get_tentative_dao")->andReturn($mockTentativeDAO);
		$mockDAOFactory
			->shouldReceive("get_tentative_prog_dao")
			->andReturn(Mockery::mock("progression\\dao\\tentative\\TentativeProgDAO"));
		$mockDAOFactory->shouldReceive("get_commentaire_dao")->andReturn($mockCommentaireDAO);
		$mockDAOFactory->shouldReceive("get_avancement_dao")->andReturn($mockAvancementDAO);
		$mockDAOFactory->shouldReceive("get_question_dao")->andReturn($mockQuestionDAO);
		$mockDAOFactory->shouldReceive("get_exécuteur")->andReturn($mockExécuteur);
		$mockDAOFactory->shouldReceive("get_user_dao")->andReturn($mockUserDAO);

		DAOFactory::setInstance($mockDAOFactory);
	}

	public function test_étant_donné_une_tentative_existante_lorsquon_appelle_get_on_obtient_la_TentativeProg_et_ses_relations_sous_forme_json()
	{
		$résultat_obtenu = $this->actingAs($this->user)->call(
			"GET",
			"/tentative/jdoe/aHR0cHM6Ly9kZXBvdC5jb20vcXVlc3Rpb25fcsOpdXNzaWU/1614374490",
		);

		$this->assertEquals(200, $résultat_obtenu->status());
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/tentativeCtlTest_prog_réussie.json",
			$résultat_obtenu->getContent(),
		);
	}

	public function test_étant_donné_une_tentative_existante_lorsquon_appelle_get_en_incluant_les_commentaires_on_obtient_la_TentativeProg_et_ses_commentaires_sous_forme_json()
	{
		$résultat_obtenu = $this->actingAs($this->user)->call(
			"GET",
			"/tentative/jdoe/aHR0cHM6Ly9kZXBvdC5jb20vcXVlc3Rpb25fcsOpdXNzaWU/1614374490?include=commentaires",
		);

		$this->assertEquals(200, $résultat_obtenu->status());
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/tentativeCtlTest_prog_réussie_avec_commentaires.json",
			$résultat_obtenu->getContent(),
		);
	}

	public function test_étant_donné_une_tentative_inexistante_lorsquon_appelle_get_on_obtient_une_erreur_404()
	{
		$résultat_obtenu = $this->actingAs($this->user)->call(
			"GET",
			"/tentative/jdoe/aHR0cHM6Ly9kZXBvdC5jb20vcXVlc3Rpb25fcsOpdXNzaWU/9999999999",
		);

		$this->assertEquals(404, $résultat_obtenu->status());
		$this->assertEquals('{"erreur":"Ressource non trouvée."}', $résultat_obtenu->getContent());
	}

	public function test_étant_donné_un_avancement_inexistant_et_une_tentative_réussie_lorsquon_appelle_post_lavancement_et_la_tentative_sont_sauvegardés_et_on_obtient_la_TentativeProg_réussie()
	{
		$nouvelle_tentative = new TentativeProg(
			langage: "réussi",
			code: "#+TODO\nprint(\"Hello world!\")",
			date_soumission: 1653690241,
			réussi: true,
			tests_réussis: 2,
			temps_exécution: 551,
			résultats: [
				"bebe123" => new Résultat("Bonjour\n", "", true, "C'est ça!", 30),
				"cafe456" => new Résultat("Bonjour\nBonjour\n", "", true, "C'est ça!", 30),
			],
			feedback: "Bon travail!",
		);
		$nouvel_avancement = new Avancement(
			tentatives: [$nouvelle_tentative],
			titre: "Question réussie",
			niveau: "Débutant",
		);

		$mockAvancementDAO = DAOFactory::getInstance()->get_avancement_dao();
		$mockAvancementDAO
			->shouldReceive("save")
			->once()
			->withArgs(function ($user, $uri, $type, $av) use ($nouvel_avancement) {
				return $user == "jdoe" &&
					$uri == "https://depot.com/nouvelle_question" &&
					$type == "prog" &&
					$av == $nouvel_avancement;
			})
			->andReturn([
				"https://depot.com/nouvelle_question" => $nouvel_avancement,
			]);

		$mockTentativeDAO = DAOFactory::getInstance()->get_tentative_prog_dao();
		$mockTentativeDAO
			->shouldReceive("save")
			->once()
			->withArgs(function ($user, $uri, $t) use ($nouvelle_tentative) {
				if ($t->date_soumission - time() > 1) {
					throw "Temps d'exécution >1s {$t->date_soumission}";
				}
				$t->date_soumission = $nouvelle_tentative->date_soumission;
				return $user == "jdoe" && $uri == "https://depot.com/nouvelle_question" && $t == $nouvelle_tentative;
			})
			->andReturn([1653690241 => $nouvelle_tentative]);

		$résultat_obtenu = $this->actingAs($this->user)->call(
			"POST",
			"/avancement/jdoe/aHR0cHM6Ly9kZXBvdC5jb20vbm91dmVsbGVfcXVlc3Rpb24/tentatives?include=resultats",
			[
				"langage" => "réussi",
				"code" => "#+TODO\nprint(\"Hello world!\")",
			],
		);

		$this->assertEquals(200, $résultat_obtenu->status());
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/tentativeCtlTest_prog_nouvel_avancement_tentative_réussie.json",
			$résultat_obtenu->getContent(),
		);
	}

	public function test_étant_donné_un_avancement_réussi_et_une_tentative_réussie_lorsquon_appelle_post_lavancement_et_la_tentative_sont_sauvegardés_et_on_obtient_la_TentativeProg_réussie()
	{
		$nouvelle_tentative = new TentativeProg(
			langage: "réussi",
			code: "#+TODO\nprint(\"Hello world!\")",
			date_soumission: 1653690241,
			réussi: true,
			tests_réussis: 2,
			temps_exécution: 551,
			résultats: [
				"bebe123" => new Résultat("Bonjour\n", "", true, "C'est ça!", 30),
				"cafe456" => new Résultat("Bonjour\nBonjour\n", "", true, "C'est ça!", 30),
			],
			feedback: "Bon travail!",
		);
		$nouvel_avancement = new Avancement(
			tentatives: [$this->tentative_réussie, $nouvelle_tentative],
			titre: "Question réussie",
			niveau: "Débutant",
		);

		$mockAvancementDAO = DAOFactory::getInstance()->get_avancement_dao();
		$mockAvancementDAO
			->shouldReceive("save")
			->once()
			->withArgs(function ($user, $uri, $type, $av) use ($nouvel_avancement) {
				return $user == "jdoe" &&
					$uri == "https://depot.com/question_réussie" &&
					$type == "prog" &&
					$av == $nouvel_avancement;
			})
			->andReturn([
				"https://depot.com/question_réussie" => $nouvel_avancement,
			]);

		$mockTentativeDAO = DAOFactory::getInstance()->get_tentative_prog_dao();
		$mockTentativeDAO
			->shouldReceive("save")
			->once()
			->withArgs(function ($user, $uri, $t) use ($nouvelle_tentative) {
				if ($t->date_soumission - time() > 1) {
					throw "Temps d'exécution >1s {$t->date_soumission}";
				}
				$t->date_soumission = $nouvelle_tentative->date_soumission;
				return $user == "jdoe" && $uri == "https://depot.com/question_réussie" && $t == $nouvelle_tentative;
			})
			->andReturn([1653690241 => $nouvelle_tentative]);

		$résultat_obtenu = $this->actingAs($this->user)->call(
			"POST",
			"/avancement/jdoe/aHR0cHM6Ly9kZXBvdC5jb20vcXVlc3Rpb25fcsOpdXNzaWU/tentatives?include=resultats",
			[
				"langage" => "réussi",
				"code" => "#+TODO\nprint(\"Hello world!\")",
			],
		);

		$this->assertEquals(200, $résultat_obtenu->status());
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/tentativeCtlTest_prog_avancement_réussi_tentative_réussie.json",
			$résultat_obtenu->getContent(),
		);
	}

	public function test_étant_donné_un_avancement_non_réussi_et_une_tentative_réussie_lorsquon_appelle_post_lavancement_et_la_tentative_sont_sauvegardés_et_on_obtient_la_TentativeProg_réussie()
	{
		$nouvelle_tentative = new TentativeProg(
			langage: "réussi",
			code: "#+TODO\nprint(\"Hello world!\")",
			date_soumission: 1653690241,
			réussi: true,
			tests_réussis: 2,
			temps_exécution: 551,
			résultats: [
				"bebe123" => new Résultat("Bonjour\n", "", true, "C'est ça!", 30),
				"cafe456" => new Résultat("Bonjour\nBonjour\n", "", true, "C'est ça!", 30),
			],
			feedback: "Bon travail!",
		);

		$nouvel_avancement = new Avancement(
			tentatives: [$this->tentative_non_réussie, $nouvelle_tentative],
			titre: "Question non réussie",
			niveau: "Débutant",
		);

		$mockAvancementDAO = DAOFactory::getInstance()->get_avancement_dao();
		$mockAvancementDAO
			->shouldReceive("save")
			->withArgs(function ($user, $uri, $type, $av) use ($nouvel_avancement) {
				return $user == "jdoe" &&
					$uri == "https://depot.com/question_non_réussie" &&
					$type == "prog" &&
					$av == $nouvel_avancement;
			})
			->andReturn([
				"https://depot.com/question_non_réussie" => $nouvel_avancement,
			]);

		$mockTentativeDAO = DAOFactory::getInstance()->get_tentative_prog_dao();
		$mockTentativeDAO
			->shouldReceive("save")
			->withArgs(function ($user, $uri, $t) use ($nouvelle_tentative) {
				if ($t->date_soumission - time() > 1) {
					throw "Temps d'exécution >1s {$t->date_soumission}";
				}
				$t->date_soumission = $nouvelle_tentative->date_soumission;
				return $user == "jdoe" && $uri == "https://depot.com/question_non_réussie" && $t == $nouvelle_tentative;
			})
			->andReturn([1653690241 => $nouvelle_tentative]);

		$résultat_obtenu = $this->actingAs($this->user)->call(
			"POST",
			"/avancement/jdoe/aHR0cHM6Ly9kZXBvdC5jb20vcXVlc3Rpb25fbm9uX3LDqXVzc2ll/tentatives?include=resultats",
			[
				"langage" => "réussi",
				"code" => "#+TODO\nprint(\"Hello world!\")",
			],
		);

		$this->assertEquals(200, $résultat_obtenu->status());
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/tentativeCtlTest_prog_avancement_non_réussi_tentative_réussie.json",
			$résultat_obtenu->getContent(),
		);
	}

	public function test_étant_un_avancement_non_réussi_et_une_tentative_non_réussie_lorsquon_appelle_post_lavancement_et_la_tentative_sont_sauvegardés_et_on_obtient_la_TentativeProg_non_réussie()
	{
		$nouvelle_tentative = new TentativeProg(
			langage: "non_réussi",
			code: "#+TODO\nprint(\"Hello world!\")",
			date_soumission: 1653690241,
			réussi: false,
			tests_réussis: 0,
			temps_exécution: 440,
			résultats: [
				"bebe123" => new Résultat("Allo\n", "", false, "C'est pas ça :(", 30),
				"cafe456" => new Résultat("Allo\nAllo\n", "", false, "C'est pas ça :(", 30),
			],
			feedback: "Encore un effort!",
		);

		$nouvel_avancement = new Avancement(
			tentatives: [$this->tentative_non_réussie, $nouvelle_tentative],
			titre: "Question non réussie",
			niveau: "Débutant",
		);

		$mockAvancementDAO = DAOFactory::getInstance()->get_avancement_dao();
		$mockAvancementDAO
			->shouldReceive("save")
			->withArgs(function ($user, $uri, $type, $av) use ($nouvel_avancement) {
				return $user == "jdoe" &&
					$uri == "https://depot.com/question_non_réussie" &&
					$type == "prog" &&
					$av == $nouvel_avancement;
			})
			->andReturn([
				"https://depot.com/question_non_réussie" => $nouvel_avancement,
			]);

		$mockTentativeDAO = DAOFactory::getInstance()->get_tentative_prog_dao();
		$mockTentativeDAO
			->shouldReceive("save")
			->withArgs(function ($user, $uri, $t) use ($nouvelle_tentative) {
				if ($t->date_soumission - time() > 1) {
					throw "Temps d'exécution >1s {$t->date_soumission}";
				}
				$t->date_soumission = $nouvelle_tentative->date_soumission;
				return $user == "jdoe" && $uri == "https://depot.com/question_non_réussie" && $t == $nouvelle_tentative;
			})
			->andReturn([1653690241 => $nouvelle_tentative]);

		$résultat_obtenu = $this->actingAs($this->user)->call(
			"POST",
			"/avancement/jdoe/aHR0cHM6Ly9kZXBvdC5jb20vcXVlc3Rpb25fbm9uX3LDqXVzc2ll/tentatives?include=resultats",
			[
				"langage" => "non_réussi",
				"code" => "#+TODO\nprint(\"Hello world!\")",
			],
		);

		$this->assertEquals(200, $résultat_obtenu->status());
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/tentativeCtlTest_prog_avancement_non_réussi_tentative_non_réussie.json",
			$résultat_obtenu->getContent(),
		);
	}

	public function test_étant_un_avancement_non_réussi_et_une_tentative_erronée_lorsquon_appelle_post_lavancement_et_la_tentative_sont_sauvegardés_et_on_obtient_la_TentativeProg_avec_erreurs()
	{
		$nouvelle_tentative = new TentativeProg(
			langage: "erreur",
			code: "#+TODO\nprint(\"Hello world!\")",
			date_soumission: 1653690241,
			réussi: false,
			tests_réussis: 0,
			temps_exécution: 440,
			résultats: [
				"bebe123" => new Résultat("Allo\n", "", false, "C'est pas ça :(", 30),
				"cafe456" => new Résultat("", "Erreur!", false, "arrrg!", 30),
			],
			feedback: "oups!",
		);

		$nouvel_avancement = new Avancement(
			tentatives: [$this->tentative_non_réussie, $nouvelle_tentative],
			titre: "Question non réussie",
			niveau: "Débutant",
		);

		$mockAvancementDAO = DAOFactory::getInstance()->get_avancement_dao();
		$mockAvancementDAO
			->shouldReceive("save")
			->withArgs(function ($user, $uri, $type, $av) use ($nouvel_avancement) {
				return $user == "jdoe" &&
					$uri == "https://depot.com/question_non_réussie" &&
					$type == "prog" &&
					$av == $nouvel_avancement;
			})
			->andReturn([
				"https://depot.com/question_non_réussie" => $nouvel_avancement,
			]);

		$mockTentativeDAO = DAOFactory::getInstance()->get_tentative_prog_dao();
		$mockTentativeDAO
			->shouldReceive("save")
			->withArgs(function ($user, $uri, $t) use ($nouvelle_tentative) {
				if ($t->date_soumission - time() > 1) {
					throw "Temps d'exécution >1s {$t->date_soumission}";
				}
				$t->date_soumission = $nouvelle_tentative->date_soumission;
				return $user == "jdoe" && $uri == "https://depot.com/question_non_réussie" && $t == $nouvelle_tentative;
			})
			->andReturn([1653690241 => $nouvelle_tentative]);

		$résultat_obtenu = $this->actingAs($this->user)->call(
			"POST",
			"/avancement/jdoe/aHR0cHM6Ly9kZXBvdC5jb20vcXVlc3Rpb25fbm9uX3LDqXVzc2ll/tentatives?include=resultats",
			[
				"langage" => "erreur",
				"code" => "#+TODO\nprint(\"Hello world!\")",
			],
		);

		$this->assertEquals(200, $résultat_obtenu->status());
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/tentativeCtlTest_prog_avancement_non_réussi_tentative_erreur.json",
			$résultat_obtenu->getContent(),
		);
	}

	public function test_étant_donné_une_tentative_sans_code_lorsquelle_est_soumise_on_obtient_une_erreur_de_validation()
	{
		$résultat_obtenu = $this->actingAs($this->user)->call(
			"POST",
			"/avancement/jdoe/aHR0cHM6Ly9kZXBvdC5jb20vcXVlc3Rpb25fbm9uX3LDqXVzc2ll/tentatives",
			["langage" => "réussi"],
		);

		$this->assertEquals(400, $résultat_obtenu->status());
		$this->assertEquals('{"erreur":{"code":["Le champ code est obligatoire."]}}', $résultat_obtenu->getContent());
	}

	public function test_étant_donné_un_url_de_compilebox_inaccessible_lorsquon_appelle_post_on_obtient_Service_non_disponible()
	{
		$résultat_obtenu = $this->actingAs($this->user)->call(
			"POST",
			"/avancement/jdoe/aHR0cHM6Ly9kZXBvdC5jb20vcXVlc3Rpb25fbm9uX3LDqXVzc2ll/tentatives",
			[
				"langage" => "pas d'exécuteur",
				"code" => "#+TODO\nprint(\"on ne se rendra pas à exécuter ceci\")",
			],
		);

		$this->assertEquals(503, $résultat_obtenu->status());
		$this->assertEquals('{"erreur":"Exécuteur non disponible."}', $résultat_obtenu->getContent());
	}

	public function test_étant_donné_une_tentative_avec_un_code_sans_TODO_lorsquelle_est_soumise_on_obtient_Tentative_intraitable()
	{
		$résultat_obtenu = $this->actingAs($this->user)->call(
			"POST",
			"/avancement/jdoe/aHR0cHM6Ly9kZXBvdC5jb20vcXVlc3Rpb25fbm9uX3LDqXVzc2ll/tentatives",
			[
				"langage" => "réussi",
				"code" => "print(\"Hello world!\")",
			],
		);

		$this->assertEquals(400, $résultat_obtenu->status());
		$this->assertEquals('{"erreur":"Tentative intraitable."}', $résultat_obtenu->getContent());
	}

	public function test_étant_donné_une_tentative_avec_une_question_inexistante_lorsquelle_est_soumise_on_obtient_Tentative_intraitable()
	{
		$résultat_obtenu = $this->actingAs($this->user)->call("POST", "/avancement/jdoe/aW5leGlzdGFudGU/tentatives", [
			"langage" => "réussi",
			"code" => "print(\"Hello world!\")",
		]);

		$this->assertEquals(400, $résultat_obtenu->status());
		$this->assertEquals('{"erreur":"Question inexistante."}', $résultat_obtenu->getContent());
	}

	public function test_étant_donné_une_tentative_avec_un_langage_inconnu_lorsquelle_est_soumise_on_obtient_Tentative_intraitable()
	{
		$résultat_obtenu = $this->actingAs($this->user)->call(
			"POST",
			"/avancement/jdoe/aHR0cHM6Ly9kZXBvdC5jb20vcXVlc3Rpb25fbm9uX3LDqXVzc2ll/tentatives",
			[
				"langage" => "inconnu",
				"code" => "print(\"Hello world!\")",
			],
		);

		$this->assertEquals(400, $résultat_obtenu->status());
		$this->assertEquals('{"erreur":"Tentative intraitable."}', $résultat_obtenu->getContent());
	}

	public function test_étant_donné_une_tentative_ayant_du_code_dépassant_la_taille_maximale_de_caractères_on_obtient_une_erreur_400()
	{
		putenv("TAILLE_CODE_MAX=23");
		$testCode = "#+TODO\n日本語でのテストです\n#-TODO"; //24 caractères UTF8

		$mockTentativeDAO = DAOFactory::getInstance()->get_tentative_prog_dao();
		$mockTentativeDAO->shouldNotReceive("save")->withAnyArgs();

		$résultat_obtenu = $this->actingAs($this->user)->call(
			"POST",
			"/avancement/jdoe/aHR0cHM6Ly9kZXBvdC5jb20vcXVlc3Rpb25fcsOpdXNzaWU/tentatives",
			[
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

	public function test_étant_donné_une_tentative_ayant_exactement_la_taille_maximale_de_caractères_on_obtient_un_code_200()
	{
		$_ENV["TAILLE_CODE_MAX"] = 24;
		$testCode = "#+TODO\n日本語でのテストです\n#-TODO"; //24 caractères UTF8

		$mockTentativeDAO = DAOFactory::getInstance()->get_tentative_prog_dao();
		$mockTentativeDAO->shouldReceive("save")->andReturnUsing(function ($u, $uri, $t) {
			return [$t->date_soumission => $t];
		});
		$mockAvancementDAO = DAOFactory::getInstance()->get_avancement_dao();
		$mockAvancementDAO->shouldReceive("save")->andReturnUsing(function ($u, $q, $type, $a) {
			return [$q => $a];
		});

		$résultat_obtenu = $this->actingAs($this->user)->call(
			"POST",
			"/avancement/jdoe/aHR0cHM6Ly9kZXBvdC5jb20vcXVlc3Rpb25fcsOpdXNzaWU/tentatives",
			[
				"langage" => "réussi",
				"code" => "$testCode",
			],
		);

		$_ENV["TAILLE_CODE_MAX"] = 1000;

		$this->assertEquals(200, $résultat_obtenu->status());
	}
}
