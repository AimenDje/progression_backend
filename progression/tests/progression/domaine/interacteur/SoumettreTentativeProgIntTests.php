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

use progression\domaine\entité\question\{Question, QuestionProg};
use progression\domaine\entité\{Exécutable, Avancement, Résultat, TentativeProg, TestProg};
use progression\domaine\entité\user\User;
use progression\dao\DAOFactory;
use progression\dao\tentative\TentativeProgDAO;
use progression\TestCase;
use Mockery;
use progression\dao\question\QuestionDAO;

final class SoumettreTentativeProgIntTests extends TestCase
{
	protected static $question;
	protected static $tentativeSoumiseIncorrecte;

	public function setUp(): void
	{
		parent::setUp();

		//Mock User
		$mockUserDao = Mockery::mock("progression\\dao\\UserDAO");
		$mockUserDao->allows()->get_user("jdoe")->andReturn(new User(username: "jdoe", date_inscription: 0));

		// Mock TentativeDAO
		$mockTentativeDAO = Mockery::mock("progression\\dao\\tentative\\TentativeProgDAO");
		$mockTentativeDAO
			->shouldReceive("save")
			->withArgs(function ($username, $uri, $tentative) {
				return $username == "jdoe" && $uri == "https://example.com/question" && $tentative->langage == "python";
			})
			->andReturn(
				new TentativeProg(
					langage: "python",
					code: "#Commentaire invisible\n#+VISIBLE\n#+TODO\nprint(\"je fais mon possible!\")\n#-TODO\n# Rien à faire ici\n#+TODO\n# À faire\n\n",
					date_soumission: 1615696286,
					réussi: true,
					tests_réussis: 1,
					temps_exécution: 122,
				),
			);
		$mockTentativeDAO
			->shouldReceive("save")
			->withArgs(function ($username, $uri, $tentative) {
				return $username == "jdoe" && $uri == "https://example.com/question" && $tentative->langage == "java";
			})
			->andReturn(
				new TentativeProg(
					langage: "java",
					code: "#Commentaire invisible\n#+VISIBLE\n#+TODO\nprint(\"je fais mon possible!\")\n#-TODO\n# Rien à faire ici\n#+TODO\n# À faire\n\n",
					date_soumission: 1615696286,
					réussi: false,
					tests_réussis: 0,
					temps_exécution: 122,
				),
			);

		// Mock exécuteur
		$mockExécuteur = Mockery::mock("progression\\dao\\exécuteur\\Exécuteur");
		$mockExécuteur
			->shouldReceive("exécuter_prog")
			->withArgs(function ($exécutable) {
				return $exécutable->lang == "python";
			})
			->andReturn([
				"temps_exécution" => 0.122,
				"résultats" => [["output" => "sortieTest", "errors" => "", "time" => 0.1]],
			]);
		$mockExécuteur
			->shouldReceive("exécuter_prog")
			->withArgs(function ($exécutable) {
				return $exécutable->lang == "java";
			})
			->andReturn([
				"temps_exécution" => 0.122,
				"résultats" => [["output" => "Incorrecte", "errors" => "", "time" => 0.1]],
			]);

		// Mock DAOFactory
		$mockDAOFactory = Mockery::mock("progression\\dao\\DAOFactory");
		$mockDAOFactory->shouldReceive("get_exécuteur")->andReturn($mockExécuteur);
		$mockDAOFactory->shouldReceive("get_tentative_prog_dao")->andReturn($mockTentativeDAO);
		$mockDAOFactory->shouldReceive("get_user_dao")->andReturn($mockUserDao);
		DAOFactory::setInstance($mockDAOFactory);

		//Mock Question
		self::$question = new QuestionProg();
		self::$question->titre = "Bonsoir";
		self::$question->niveau = "facile";
		self::$question->tests = [
			new TestProg(
				nom: "nomTest",
				sortie_attendue: "sortieTest",
				entrée: "entréeTest",
				params: "params",
				feedback_pos: "feedbackPositif",
				feedback_neg: "feedbackNégatif",
				feedback_err: "feedbackErreur",
			),
		];

		self::$question->exécutables["python"] = new Exécutable(
			"#Commentaire invisible\n#+VISIBLE\n#+TODO\nprint()\n#-TODO\n# Rien à faire ici\n#+TODO\n# À faire\n\n",
			"python",
		);
		self::$question->exécutables["java"] = new Exécutable(
			"#Commentaire invisible\n#+VISIBLE\n#+TODO\nprint()\n#-TODO\n# Rien à faire ici\n#+TODO\n# À faire\n\n",
			"java",
		);
		self::$question->feedback_neg = "feedbackGénéralNégatif";

		self::$question->feedback_pos = "feedbackGénéralPositif";

		self::$tentativeSoumiseIncorrecte = new TentativeProg(
			"java",
			"#Commentaire invisible\n#+VISIBLE\n#+TODO\nprint(\"je fais mon possible!\")\n#-TODO\n# Rien à faire ici\n#+TODO\n# À faire\n\n",
			1615696286,
		);
	}

	public function test_étant_donné_une_questionprog_et_une_tentativeprog_lorsqu_on_appelle_soumettre_tentative_on_obtient_un_objet_tentative_comportant_les_tests_réussis_et_les_résultats()
	{
		$mockAvancementDAO = Mockery::mock("progression\\dao\\AvancementDAO");
		$mockAvancementDAO
			->shouldReceive("get_avancement")
			->with("jdoe", "https://example.com/question")
			->andReturn(null);
		$mockAvancementDAO->shouldReceive("save")->andReturn(null);

		$mockDAOFactory = DAOFactory::getInstance();
		$mockDAOFactory->shouldReceive("get_avancement_dao")->andReturn($mockAvancementDAO);

		$tentative_attendue = new TentativeProg(
			langage: "java",
			code: "#Commentaire invisible\n#+VISIBLE\n#+TODO\nprint(\"je fais mon possible!\")\n#-TODO\n# Rien à faire ici\n#+TODO\n# À faire\n\n",
			date_soumission: 1615696286,
			réussi: false,
			tests_réussis: 0,
			temps_exécution: 122,
			feedback: "feedbackGénéralNégatif",
			résultats: [new Résultat("Incorrecte", "", false, "feedbackNégatif", 100)],
		);

		$interacteur = new SoumettreTentativeProgInt();
		$tentative_obtenue = $interacteur->soumettre_tentative(
			self::$question,
			self::$tentativeSoumiseIncorrecte,
			self::$question->tests,
		);

		$this->assertEquals($tentative_attendue, $tentative_obtenue);
	}
}
