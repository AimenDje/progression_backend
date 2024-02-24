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
use progression\domaine\entité\{Avancement, TestSys, Exécutable, TentativeSys, Résultat};
use progression\domaine\entité\question\{Question, QuestionSys};
use progression\domaine\entité\user\{User, Rôle, État};

use progression\UserAuthentifiable;
use Carbon\Carbon;

final class TentativeCtl_QuestionSys_Tests extends ContrôleurTestCase
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

		// QuestionSys avec solution courte
		$question_solution_courte_réussie = new QuestionSys(
			niveau: "Débutant",
			titre: "Question à solution courte",
			solution: "Bonne réponse",
			feedback_pos: "Bon travail!",
			feedback_neg: "Encore un effort!",
		);

		$question_solution_courte_non_réussie = $question_solution_courte_réussie;

		//QuestionSys avec validations
		$question_validée_réussie = new QuestionSys(
			niveau: "Débutant",
			titre: "Question validée",
			feedback_pos: "Bon travail!",
			feedback_neg: "Encore un effort!",
			tests: [
				new TestSys(
					nom: "Toutes permissions 3",
					sortie_attendue: "-rwxrwxrwx",
					validation: "laValidation",
					utilisateur: "momo",
					feedback_pos: "yes!",
					feedback_neg: "non!",
				),
			],
		);

		$question_validée_non_réussie = $question_validée_réussie;

		$mockQuestionDAO = Mockery::mock("progression\\dao\\question\\QuestionDAO");
		$mockQuestionDAO
			->shouldReceive("get_question")
			->with("https://depot.com/question_solution_courte_réussie")
			->andReturn($question_solution_courte_réussie);
		$mockQuestionDAO
			->shouldReceive("get_question")
			->with("https://depot.com/question_validée_réussie")
			->andReturn($question_validée_réussie);
		$mockQuestionDAO
			->shouldReceive("get_question")
			->with("https://depot.com/question_solution_courte_non_réussie")
			->andReturn($question_solution_courte_non_réussie);
		$mockQuestionDAO
			->shouldReceive("get_question")
			->with("https://depot.com/question_validée_non_réussie")
			->andReturn($question_validée_non_réussie);

		// Tentatives
		$this->tentative_solution_courte_non_réussie = new TentativeSys(
			conteneur_id: "leConteneurDeLancienneTentative",
			url_terminal: "https://tty.com/abcde",
			réponse: "laRéponseDeLancienneTentative",
			date_soumission: "1614374490",
			réussi: false,
		);
		$this->tentative_solution_courte_réussie = new TentativeSys(
			conteneur_id: "leConteneurDeLancienneTentative2",
			url_terminal: "https://tty.com/abcde",
			réponse: "laRéponseDeLancienneTentative2",
			date_soumission: "1614374491",
			réussi: true,
		);
		$this->tentative_validée_non_réussie = new TentativeSys(
			conteneur_id: "leConteneurDeLancienneTentative",
			url_terminal: "https://tty.com/abcde",
			réponse: null,
			date_soumission: "1614374490",
			réussi: false,
		);
		$this->tentative_validée_réussie = new TentativeSys(
			conteneur_id: "leConteneurDeLancienneTentative2",
			url_terminal: "https://tty.com/abcde",
			réponse: null,
			date_soumission: "1614374491",
			réussi: true,
		);

		$mockTentativeDAO = Mockery::mock("progression\\dao\\tentative\\TentativeDAO");
		$mockTentativeDAO
			->shouldReceive("get_tentative")
			->with("jdoe", "https://depot.com/question_solution_courte_non_réussie", [])
			->andReturn($this->tentative_solution_courte_non_réussie);
		$mockTentativeDAO
			->shouldReceive("get_tentative")
			->with("jdoe", "https://depot.com/question_solution_courte_réussie", [])
			->andReturn($this->tentative_solution_courte_réussie);
		$mockTentativeDAO
			->shouldReceive("get_tentative")
			->with("jdoe", "https://depot.com/question_validée_non_réussie", [])
			->andReturn($this->tentative_validée_non_réussie);
		$mockTentativeDAO
			->shouldReceive("get_tentative")
			->with("jdoe", "https://depot.com/question_validée_réussie", [])
			->andReturn($this->tentative_validée_réussie);

		$mockTentativeDAO
			->shouldReceive("get_toutes")
			->with("jdoe", "https://depot.com/question_validée_réussie", [])
			->andReturn([$this->tentative_validée_non_réussie, $this->tentative_validée_réussie]);

		$mockTentativeDAO
			->shouldReceive("get_dernière")
			->with("jdoe", "https://depot.com/question_solution_courte_non_réussie", [])
			->andReturn($this->tentative_solution_courte_non_réussie);
		$mockTentativeDAO
			->shouldReceive("get_dernière")
			->with("jdoe", "https://depot.com/question_solution_courte_réussie", [])
			->andReturn($this->tentative_solution_courte_réussie);
		$mockTentativeDAO
			->shouldReceive("get_dernière")
			->with("jdoe", "https://depot.com/question_validée_non_réussie", [])
			->andReturn($this->tentative_validée_non_réussie);
		$mockTentativeDAO
			->shouldReceive("get_dernière")
			->with("jdoe", "https://depot.com/question_validée_réussie", [])
			->andReturn($this->tentative_validée_réussie);

		//Avancement
		$this->avancement_solution_courte_non_réussie = new Avancement(
			[$this->tentative_solution_courte_non_réussie],
			titre: "Question non réussie",
			niveau: "Débutant",
		);
		$this->avancement_solution_courte_réussie = new Avancement(
			[$this->tentative_solution_courte_non_réussie, $this->tentative_solution_courte_réussie],
			titre: "Question réussie",
			niveau: "Débutant",
		);
		$this->avancement_validée_non_réussie = new Avancement(
			[$this->tentative_validée_non_réussie],
			titre: "Question non réussie",
			niveau: "Débutant",
		);
		$this->avancement_validée_réussie = new Avancement(
			[$this->tentative_validée_non_réussie, $this->tentative_validée_réussie],
			titre: "Question réussie",
			niveau: "Débutant",
		);

		$mockAvancementDAO = Mockery::mock("progression\\dao\\AvancementDAO");
		$mockAvancementDAO
			->shouldReceive("get_avancement")
			->with("jdoe", "https://depot.com/question_validée_non_réussie", [])
			->andReturn($this->avancement_validée_non_réussie);
		$mockAvancementDAO
			->shouldReceive("get_avancement")
			->with("jdoe", "https://depot.com/question_validée_réussie", [])
			->andReturn($this->avancement_validée_réussie);
		$mockAvancementDAO
			->shouldReceive("get_avancement")
			->with("jdoe", "https://depot.com/question_solution_courte_non_réussie", [])
			->andReturn($this->avancement_solution_courte_non_réussie);
		$mockAvancementDAO
			->shouldReceive("get_avancement")
			->with("jdoe", "https://depot.com/question_solution_courte_réussie", [])
			->andReturn($this->avancement_solution_courte_réussie);

		// User
		$mockUserDAO = Mockery::mock("progression\\dao\\UserDAO");
		$mockUserDAO->allows("get_user")->with("jdoe")->andReturn(new User(username: "jdoe", date_inscription: 0));

		// DAOFactory
		$mockDAOFactory = Mockery::mock("progression\\dao\\DAOFactory");

		$mockDAOFactory->shouldReceive("get_question_dao")->andReturn($mockQuestionDAO);
		$mockDAOFactory->shouldReceive("get_avancement_dao")->andReturn($mockAvancementDAO);
		$mockDAOFactory->shouldReceive("get_tentative_dao")->andReturn($mockTentativeDAO);
		$mockDAOFactory
			->shouldReceive("get_tentative_sys_dao")
			->andReturn(Mockery::mock("progression\\dao\\tentative\TentativeSysDAO"));
		$mockDAOFactory->shouldReceive("get_user_dao")->andReturn($mockUserDAO);

		DAOFactory::setInstance($mockDAOFactory);
	}

	public function test_étant_donné_un_avancement_non_réussi_pour_une_QuestionSys_à_solution_courte_lorsquon_soumet_la_bonne_réponse_lavancement_et_la_tentative_sont_sauvegardés_et_on_obtient_la_TentativeSys_réussie()
	{
		// Exécuteur
		$mockExécuteur = Mockery::mock("progression\\dao\\exécuteur\\Exécuteur");
		$mockExécuteur->shouldReceive("exécuter_sys")->andReturn([
			"temps_exécution" => 0.65,
			"résultats" => [],
			"conteneur_id" => "leConteneurDeLaNouvelleTentative",
			"url_terminal" => "https://tty.com/abcde",
		]);
		DAOFactory::getInstance()->shouldReceive("get_exécuteur")->andReturn($mockExécuteur);

		$nouvelle_tentative = new TentativeSys(
			conteneur_id: "leConteneurDeLaNouvelleTentative",
			url_terminal: "https://tty.com/abcde",
			réponse: "Bonne réponse",
			date_soumission: 1653690241,
			réussi: true,
			tests_réussis: 1,
			feedback: "Bon travail!",
			temps_exécution: 0,
		);

		$mockTentativeDAO = DAOFactory::getInstance()->get_tentative_sys_dao();
		$mockTentativeDAO
			->shouldReceive("save")
			->withArgs(function ($user, $uri, $t) use ($nouvelle_tentative) {
				if ($t->date_soumission - time() > 1) {
					throw "Temps d'exécution >1s {$t->date_soumission}";
				}
				$t->date_soumission = $nouvelle_tentative->date_soumission;
				return $user == "jdoe" &&
					$uri == "https://depot.com/question_solution_courte_non_réussie" &&
					$t == $nouvelle_tentative;
			})
			->andReturn([1653690241 => $nouvelle_tentative]);

		$nouvel_avancement = new Avancement(
			tentatives: [$this->tentative_solution_courte_non_réussie, $nouvelle_tentative],
			titre: "Question à solution courte",
			niveau: "Débutant",
		);

		$mockAvancementDAO = DAOFactory::getInstance()->get_avancement_dao();
		$mockAvancementDAO
			->shouldReceive("save")
			->withArgs(function ($user, $uri, $type, $av) use ($nouvel_avancement) {
				return $user == "jdoe" &&
					$uri == "https://depot.com/question_solution_courte_non_réussie" &&
					$type == "sys" &&
					$av == $nouvel_avancement;
			})
			->andReturn([
				"https://depot.com/question_solution_courte_non_réussie" => $nouvel_avancement,
			]);

		$résultat_obtenu = $this->actingAs($this->user)->call(
			"POST",
			"/avancement/jdoe/aHR0cHM6Ly9kZXBvdC5jb20vcXVlc3Rpb25fc29sdXRpb25fY291cnRlX25vbl9yw6l1c3NpZQ/tentatives?include=resultats",
			["réponse" => "Bonne réponse"],
		);

		$this->assertEquals(200, $résultat_obtenu->status());

		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/tentativeCtlTest_sys_avancement_non_réussi_tentative_réussie.json",
			$résultat_obtenu->getContent(),
		);
	}

	public function test_étant_donné_un_avancement_non_réussi_pour_une_QuestionSys_à_solution_courte_lorsquon_soumet_la_mauvaise_réponse_lavancement_et_la_tentative_sont_sauvegardés_et_on_obtient_la_TentativeSys_échouée()
	{
		// Exécuteur
		$mockExécuteur = Mockery::mock("progression\\dao\\exécuteur\\Exécuteur");
		$mockExécuteur->shouldReceive("exécuter_sys")->andReturn([
			"temps_exécution" => 0.5,
			"résultats" => [],
			"conteneur_id" => "leConteneurDeLaNouvelleTentative",
			"url_terminal" => "https://tty.com/abcde",
		]);
		DAOFactory::getInstance()->shouldReceive("get_exécuteur")->andReturn($mockExécuteur);

		$nouvelle_tentative = new TentativeSys(
			conteneur_id: "leConteneurDeLaNouvelleTentative",
			url_terminal: "https://tty.com/abcde",
			réponse: "Mauvaise réponse",
			date_soumission: 1653690241,
			réussi: false,
			tests_réussis: 0,
			feedback: "Encore un effort!",
			temps_exécution: 0,
		);

		$mockTentativeDAO = DAOFactory::getInstance()->get_tentative_sys_dao();
		$mockTentativeDAO
			->shouldReceive("save")
			->withArgs(function ($user, $uri, $t) use ($nouvelle_tentative) {
				if ($t->date_soumission - time() > 1) {
					throw "Temps d'exécution >1s {$t->date_soumission}";
				}
				$t->date_soumission = $nouvelle_tentative->date_soumission;
				return $user == "jdoe" &&
					$uri == "https://depot.com/question_solution_courte_non_réussie" &&
					$t == $nouvelle_tentative;
			})
			->andReturn([1653690241 => $nouvelle_tentative]);

		$nouvel_avancement = new Avancement(
			tentatives: [$this->tentative_solution_courte_non_réussie, $nouvelle_tentative],
			titre: "Question à solution courte",
			niveau: "Débutant",
		);

		$mockAvancementDAO = DAOFactory::getInstance()->get_avancement_dao();
		$mockAvancementDAO
			->shouldReceive("save")
			->withArgs(function ($user, $uri, $type, $av) use ($nouvel_avancement) {
				return $user == "jdoe" &&
					$uri == "https://depot.com/question_solution_courte_non_réussie" &&
					$type == "sys" &&
					$av == $nouvel_avancement;
			})
			->andReturn([
				"https://depot.com/question_solution_courte_non_réussie" => $nouvel_avancement,
			]);

		$résultat_obtenu = $this->actingAs($this->user)->call(
			"POST",
			"/avancement/jdoe/aHR0cHM6Ly9kZXBvdC5jb20vcXVlc3Rpb25fc29sdXRpb25fY291cnRlX25vbl9yw6l1c3NpZQ/tentatives?include=resultats",
			["réponse" => "Mauvaise réponse"],
		);

		$this->assertEquals(200, $résultat_obtenu->status());

		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/tentativeCtlTest_sys_avancement_non_réussi_tentative_non_réussie.json",
			$résultat_obtenu->getContent(),
		);
	}

	public function test_étant_donné_un_une_question_sys_lorsquon_soumet_une_tentative_sans_id_de_conteneur_le_conteneur_est_détruit_et_réitinialisé_et_on_obtient_son_id()
	{
		// Exécuteur
		$mockExécuteur = Mockery::mock("progression\\dao\\exécuteur\\Exécuteur");
		$mockExécuteur
			->shouldReceive("terminer")
			->once()
			->andReturn([
				"temps_exécution" => 0.5,
				"résultats" => [["output" => "", "errors" => "", "time" => 0, "code" => 1]],
				"conteneur_id" => "",
				"url_terminal" => "",
			]);

		$mockExécuteur->shouldReceive("exécuter_sys")->andReturn([
			"temps_exécution" => 0.5,
			"résultats" => [["output" => "", "errors" => "", "time" => 0, "code" => 1]],
			"conteneur_id" => "leConteneurDeLaNouvelleTentative",
			"url_terminal" => "https://tty.com/abcde",
		]);

		$nouvelle_tentative = new TentativeSys(
			conteneur_id: "leConteneurDeLaNouvelleTentative",
			url_terminal: "https://tty.com/abcde",
			date_soumission: 1653690241,
			réussi: false,
			tests_réussis: 0,
			résultats: [new Résultat()],
			feedback: "Encore un effort!",
			temps_exécution: 0,
		);

		$nouvel_avancement = new Avancement(
			tentatives: [$nouvelle_tentative],
			titre: "Question validée",
			niveau: "Débutant",
		);

		$mockTentativeDAO = DAOFactory::getInstance()->get_tentative_sys_dao();
		$mockTentativeDAO->shouldReceive("save")->andReturn([1653690241 => $nouvelle_tentative]);

		$mockAvancementDAO = DAOFactory::getInstance()->get_avancement_dao();
		$mockAvancementDAO->shouldReceive("save")->andReturn([
			"https://depot.com/question_validée_réussie" => $nouvel_avancement,
		]);

		DAOFactory::getInstance()->shouldReceive("get_exécuteur")->andReturn($mockExécuteur);

		$résultat_obtenu = $this->actingAs($this->user)->call(
			"POST",
			"/avancement/jdoe/aHR0cHM6Ly9kZXBvdC5jb20vcXVlc3Rpb25fdmFsaWTDqWVfcsOpdXNzaWU/tentatives?include=resultats",
			["conteneur_id" => ""],
		);

		$this->assertEquals(200, $résultat_obtenu->status());

		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/tentativeCtlTest_sys_conteneur_réinitialisé.json",
			$résultat_obtenu->getContent(),
		);
	}

	/*

      À faire : https://git.dti.crosemont.quebec/progression/progression_backend/-/issues/119

      public function test_étant_donné_une_question_sys_avec_tests_de_validation_lorsquon_soumet_une_tentative_validée_réussie_lavancement_et_la_tentative_sont_sauvegardée_et_on_obtient_une_TentativeSys_réussie()
      {
      }

      public function test_étant_donné_une_question_sys_avec_tests_de_validation_lorsquon_soumet_une_tentative_échouée_lavancement_et_la_tentative_sont_sauvegardée_et_on_obtient_une_TentativeSys_échouée()
      {
      }
    */
}
