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

use progression\domaine\entité\question\{Question, QuestionSys};
use progression\domaine\entité\{Résultat, TentativeSys, TestSys};
use progression\domaine\entité\user\User;
use progression\dao\DAOFactory;
use progression\TestCase;
use Mockery;
use progression\dao\question\QuestionDAO;

final class SoumettreTentativeSysIntTests extends TestCase
{
	protected static $question_de_test;
	protected static $question_réponse_courte;
	protected static $question_réponse_courte_avec_regex;

	public function setUp(): void
	{
		parent::setUp();

		//Mock User
		$mockUserDao = Mockery::mock("progression\\dao\\UserDAO");
		$mockUserDao->allows()->get_user("jdoe")->andReturn(new User(username: "jdoe", date_inscription: 0));

		// Mock exécuteur
		$mockExécuteur = Mockery::mock("progression\\dao\\exécuteur\\Exécuteur");
		$mockExécuteur
			->shouldReceive("exécuter_sys")
			->with(
				Mockery::Any(),
				Mockery::Any(),
				"Conteneur de test correct",
				Mockery::Any(),
				Mockery::Any(),
				Mockery::Any(),
				Mockery::Any(),
			)
			->andReturn([
				"temps_exécution" => 0.5,
				"résultats" => [["output" => "Correcte", "time" => 0.1, "code" => 0]],
				"conteneur_id" => "Conteneur de test correct",
				"url_terminal" => "https://tty.com/abcde",
			]);
		$mockExécuteur
			->shouldReceive("exécuter_sys")
			->with(
				Mockery::Any(),
				Mockery::Any(),
				"Conteneur de test incorrect",
				Mockery::Any(),
				Mockery::Any(),
				Mockery::Any(),
				Mockery::Any(),
			)
			->andReturn([
				"temps_exécution" => 0.5,
				"résultats" => [["output" => "Incorrecte", "time" => 0.1, "code" => 1]],
				"conteneur_id" => "Conteneur de test incorrect",
				"url_terminal" => "https://tty.com/abcde",
			]);

		$mockExécuteur
			->shouldReceive("exécuter_sys")
			->with(
				Mockery::Any(),
				Mockery::Any(),
				"Conteneur de test incorrect réponse courte",
				Mockery::Any(),
				Mockery::Any(),
				Mockery::Any(),
				Mockery::Any(),
			)
			->andReturn([
				"temps_exécution" => 0.5,
				"résultats" => [["output" => "Incorrecte", "time" => 0.1, "code" => 1]],
				"conteneur_id" => "Conteneur de test incorrect réponse courte",
				"url_terminal" => "https://tty.com/abcde",
			]);

		$mockExécuteur
			->shouldReceive("exécuter_sys")
			->with(Mockery::Any(), Mockery::Any(), null, Mockery::Any(), Mockery::Any(), Mockery::Any(), Mockery::Any())
			->andReturn([
				"temps_exécution" => 0.5,
				"résultats" => [["output" => "Incorrecte", "time" => 0.1, "code" => 1]],
				"conteneur_id" => "Nouveau Conteneur",
				"url_terminal" => "https://tty.com/abcde",
			]);

		// Mock DAOFactory
		$mockDAOFactory = Mockery::mock("progression\\dao\\DAOFactory");
		$mockDAOFactory->shouldReceive("get_exécuteur")->andReturn($mockExécuteur);
		$mockDAOFactory->shouldReceive("get_user_dao")->andReturn($mockUserDao);
		DAOFactory::setInstance($mockDAOFactory);

		//Mock Question
		self::$question_de_test = new QuestionSys(
			titre: "Bonsoir",
			niveau: "facile",
			tests: [
				new TestSys(
					nom: "nomTest",
					sortie_attendue: "Correcte",
					validation: "validationTest",
					utilisateur: "utilisateurTest",
					feedback_pos: "feedbackPositif",
					feedback_neg: "feedbackNégatif",
				),
			],
			feedback_neg: "feedbackGénéralNégatif",
			feedback_pos: "feedbackGénéralPositif",
		);

		self::$question_réponse_courte = new QuestionSys(
			titre: "Bonsoir",
			niveau: "facile",
			solution: "Bonne réponse",
			feedback_neg: "feedbackGénéralNégatif",
			feedback_pos: "feedbackGénéralPositif",
		);

		self::$question_réponse_courte_avec_regex = new QuestionSys(
			titre: "Bonsoir",
			niveau: "facile",
			solution: "~bonne.réponse~i",
			feedback_neg: "feedbackGénéralNégatif",
			feedback_pos: "feedbackGénéralPositif",
		);
	}

	public function test_étant_donné_une_questionsys_avec_des_tests_lorsquon_soumet_une_tentative_correcte_on_obtient_une_tentative_réussie_avec_temps_dexécution_et_ses_résultats()
	{
		$tentative_attendue = new TentativeSys(
			conteneur_id: "Conteneur de test correct",
			url_terminal: "https://tty.com/abcde",
			date_soumission: 1615696286,
			réussi: true,
			tests_réussis: 1,
			temps_exécution: 500,
			feedback: "feedbackGénéralPositif",
			résultats: [
				new Résultat(
					sortie_observée: "Correcte",
					sortie_erreur: "",
					résultat: true,
					feedback: "feedbackPositif",
					temps_exécution: 100,
					code_retour: 0,
				),
			],
		);

		$interacteur = new SoumettreTentativeSysInt();
		$tentative_obtenue = $interacteur->soumettre_tentative(
			self::$question_de_test,
			new TentativeSys(conteneur_id: "Conteneur de test correct", réponse: null, date_soumission: 1615696286),
			self::$question_de_test->tests,
			null,
		);

		$this->assertEquals($tentative_attendue, $tentative_obtenue);
	}

	public function test_étant_donné_une_questionsys_avec_des_tests_lorsquon_soumet_une_tentative_on_obtient_une_tentative_non_réussie_avec_temps_dexécution_et_ses_résultats()
	{
		$tentative_attendue = new TentativeSys(
			conteneur_id: "Conteneur de test incorrect",
			url_terminal: "https://tty.com/abcde",
			date_soumission: 1615696286,
			réussi: false,
			tests_réussis: 0,
			temps_exécution: 500,
			feedback: "feedbackGénéralNégatif",
			résultats: [
				new Résultat(
					sortie_observée: "Incorrecte",
					sortie_erreur: "",
					résultat: false,
					feedback: "feedbackNégatif",
					temps_exécution: 100,
					code_retour: 1,
				),
			],
		);

		$interacteur = new SoumettreTentativeSysInt();
		$tentative_obtenue = $interacteur->soumettre_tentative(
			self::$question_de_test,
			new TentativeSys(conteneur_id: "Conteneur de test incorrect", réponse: null, date_soumission: 1615696286),
			self::$question_de_test->tests,
			null,
		);

		$this->assertEquals($tentative_attendue, $tentative_obtenue);
	}

	public function test_étant_donné_une_questionsys_avec_solution_courte_lorsquon_soumet_une_réponse_correcte_on_obtient_une_tentative_réussie()
	{
		$tentative_attendue = new TentativeSys(
			conteneur_id: "Conteneur de test incorrect réponse courte",
			url_terminal: "https://tty.com/abcde",
			réponse: "Bonne réponse",
			date_soumission: 1615696286,
			réussi: true,
			tests_réussis: 1,
			temps_exécution: 0,
			feedback: "feedbackGénéralPositif",
			résultats: [],
		);

		$interacteur = new SoumettreTentativeSysInt();
		$tentative_obtenue = $interacteur->soumettre_tentative(
			self::$question_réponse_courte,
			new TentativeSys(
				conteneur_id: "Conteneur de test incorrect réponse courte",
				réponse: "Bonne réponse",
				date_soumission: 1615696286,
			),
			[],
			null,
		);

		$this->assertEquals($tentative_attendue, $tentative_obtenue);
	}

	public function test_étant_donné_une_questionsys_avec_solution_courte_lorsquon_soumet_une_réponse_incorrecte_on_obtient_une_tentative_non_réussie()
	{
		$tentative_attendue = new TentativeSys(
			conteneur_id: "Conteneur de test incorrect",
			url_terminal: "https://tty.com/abcde",

			réponse: "Mauvaise réponse",
			date_soumission: 1615696286,
			réussi: false,
			tests_réussis: 0,
			temps_exécution: 0,
			feedback: "feedbackGénéralNégatif",
			résultats: [],
		);

		$interacteur = new SoumettreTentativeSysInt();
		$tentative_obtenue = $interacteur->soumettre_tentative(
			self::$question_réponse_courte,
			new TentativeSys(
				conteneur_id: "Conteneur de test incorrect",
				réponse: "Mauvaise réponse",
				date_soumission: 1615696286,
			),
			[],
			null,
		);

		$this->assertEquals($tentative_attendue, $tentative_obtenue);
	}

	public function test_étant_donné_une_questionsys_avec_une_regex_comme_solution_courte_lorsquon_soumet_une_réponse_correcte_on_obtient_une_tentative_réussie()
	{
		$tentative_attendue = new TentativeSys(
			conteneur_id: "Conteneur de test incorrect",
			url_terminal: "https://tty.com/abcde",
			réponse: "Bonne réponse",
			date_soumission: 1615696286,
			réussi: true,
			tests_réussis: 1,
			temps_exécution: 0,
			feedback: "feedbackGénéralPositif",
			résultats: [],
		);

		$interacteur = new SoumettreTentativeSysInt();
		$tentative_obtenue = $interacteur->soumettre_tentative(
			self::$question_réponse_courte_avec_regex,
			new TentativeSys(
				conteneur_id: "Conteneur de test incorrect",
				réponse: "Bonne réponse",
				date_soumission: 1615696286,
			),
			[],
			null,
		);

		$this->assertEquals($tentative_attendue, $tentative_obtenue);
	}

	public function test_étant_donné_une_questionsys_et_une_tentativesys_sans_conteneur_lorsqu_on_appelle_soumettre_tentative_avec_des_tests_on_obtient_un_objet_tentative_comportant_les_tests_réussis_et_les_résultats_et_lid_du_conteneur()
	{
		//Tentative sans conteneur
		$tentative_soumise_sans_conteneur = new TentativeSys(
			conteneur_id: "",
			url_terminal: "https://tty.com/abcde",
			réponse: "",
			date_soumission: 1615696287,
		);

		$tentative_attendue = new TentativeSys(
			conteneur_id: "Nouveau Conteneur",
			url_terminal: "https://tty.com/abcde",
			réponse: "",
			date_soumission: 1615696287,
			réussi: false,
			tests_réussis: 0,
			temps_exécution: 500,
			feedback: "feedbackGénéralNégatif",
			résultats: [
				new Résultat(
					sortie_observée: "Incorrecte",
					sortie_erreur: "",
					résultat: false,
					feedback: "feedbackNégatif",
					temps_exécution: 100,
					code_retour: 1,
				),
			],
		);

		$interacteur = new SoumettreTentativeSysInt();
		$tentative_obtenue = $interacteur->soumettre_tentative(
			self::$question_de_test,
			$tentative_soumise_sans_conteneur,
			self::$question_de_test->tests,
		);

		$this->assertEquals($tentative_attendue, $tentative_obtenue);
	}
}
