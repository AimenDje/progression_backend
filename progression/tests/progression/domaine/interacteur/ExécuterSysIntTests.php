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
use progression\domaine\entité\{TestSys, Résultat, TentativeSys};
use progression\dao\exécuteur\Exécuteur;
use progression\dao\DAOFactory;
use PHPUnit\Framework\TestCase;
use Mockery;

final class ExécuterSysIntTests extends TestCase
{
	protected static $questionTest;
	public function setUp(): void
	{
		parent::setUp();

		$_SERVER["REMOTE_ADDR"] = "";
		$_SERVER["PHP_SELF"] = "";

		self::$questionTest = new QuestionSys();
		self::$questionTest->titre = "Bonsoir";
		self::$questionTest->niveau = "facile";
		self::$questionTest->feedback_neg = "feedbackGénéralNégatif";
		self::$questionTest->feedback_pos = "feedbackGénéralPositif";
		self::$questionTest->tests = [
			new TestSys(
				nom: "nomTest",
				sortie_attendue: "sortieTest",
				validation: "validationTest",
				utilisateur: "utilisateurTest",
				feedback_pos: "feedbackPositif",
				feedback_neg: "feedbackNégatif",
			),
		];

		$mockExécuteur = Mockery::mock("progression\\dao\\exécuteur\\Exécuteur");
		$mockExécuteur
			->shouldReceive("exécuter_sys")
			->with(
				Mockery::Any(),
				Mockery::Any(),
				"conteneurTestCompileBox",
				Mockery::Any(),
				Mockery::Any(),
				Mockery::Any(),
				Mockery::Any(),
			)
			->andReturn([
				"temps_exécution" => 0.124,
				"résultats" => [
					[
						"output" => "",
						"errors" => "",
						"time" => 0.2,
						"code" => 0,
					],
				],
				"conteneur_id" => "conteneurTestCompileBox",
				"url_terminal" => "https://tty.com/abcde",
			]);

		$mockExécuteur
			->shouldReceive("exécuter_sys")
			->with(
				Mockery::Any(),
				Mockery::Any(),
				"ConteneurEnvoyéParTentative",
				Mockery::Any(),
				Mockery::Any(),
				Mockery::Any(),
				Mockery::Any(),
			)
			->andReturn([
				"temps_exécution" => 0.124,
				"résultats" => [
					[
						"output" => "ok\n",
						"errors" => "",
						"time" => 0.2,
						"code" => 0,
					],
				],
				"conteneur_id" => "ConteneurEnvoyéParTentative",
				"url_terminal" => "https://tty.com/abcde",
			]);

		$mockExécuteur
			->shouldReceive("exécuter_sys")
			->with(Mockery::Any(), Mockery::Any(), null, Mockery::Any(), Mockery::Any(), Mockery::Any(), Mockery::Any())
			->andReturn([
				"temps_exécution" => 0.124,
				"résultats" => [
					[
						"output" => "ok\n",
						"errors" => "",
						"time" => 0.2,
						"code" => 0,
					],
				],
				"conteneur_id" => "NouveauConteneur",
				"url_terminal" => "https://tty.com/abcde",
			]);

		$mockDAOFactory = Mockery::mock("progression\\dao\\DAOFactory");
		$mockDAOFactory->allows()->get_exécuteur()->andReturn($mockExécuteur);
		DAOFactory::setInstance($mockDAOFactory);
	}

	public function test_étant_donné_une_question_avec_une_tentative_sans_conteneur_on_recoit_lid_du_conteneur_de_compile_box()
	{
		$exécuter_sys_int = new ExécuterSysInt();

		$question = new QuestionSys();
		$question->titre = "Bonsoir";
		$question->niveau = "facile";
		$question->feedback_neg = "feedbackGénéralNégatif";
		$question->feedback_pos = "feedbackGénéralPositif";
		$question->tests = [
			new TestSys(
				nom: "nomTest",
				sortie_attendue: "sortieTest",
				validation: "validationTest",
				utilisateur: "utilisateurTest",
				feedback_pos: "feedbackPositif",
				feedback_neg: "feedbackNégatif",
			),
		];

		$tentative = new TentativeSys("", "https://tty.com/abcde", 1615696286);

		$résultat_observé = $exécuter_sys_int->exécuter($question, $tentative, $question->tests, null);

		$this->assertEquals("NouveauConteneur", $résultat_observé["conteneur_id"]);
	}

	public function test_étant_donné_une_question_avec_une_tentative_avec_conteneur_on_recoit_lid_de_la_tentative_le_bon_temps_dexécution_et_le_bon_résultat()
	{
		$conteneur_id_attendu = "ConteneurEnvoyéParTentative";

		$exécuter_sys_int = new ExécuterSysInt();

		$question = new QuestionSys();
		$question->titre = "Bonsoir";
		$question->niveau = "facile";
		$question->feedback_neg = "feedbackGénéralNégatif";
		$question->feedback_pos = "feedbackGénéralPositif";
		$question->tests = [
			new TestSys(
				nom: "nomTest",
				sortie_attendue: "sortieTest",
				validation: "validationTest",
				utilisateur: "utilisateurTest",
				feedback_pos: "feedbackPositif",
				feedback_neg: "feedbackNégatif",
			),
		];

		$résultat_attendu = new Résultat("ok\n", "", false, null, 200);

		$tentative = new TentativeSys("ConteneurEnvoyéParTentative", "", 1615696286);

		$résultat_observé = $exécuter_sys_int->exécuter($question, $tentative, $question->tests, null);

		$this->assertEquals($conteneur_id_attendu, $résultat_observé["conteneur_id"]);
		$this->assertEquals(124, $résultat_observé["temps_exécution"]);
		$this->assertEquals([$résultat_attendu], $résultat_observé["résultats"]);
	}
}
