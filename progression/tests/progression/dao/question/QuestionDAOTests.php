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

namespace progression\dao\question;

use progression\domaine\entité\{Question, QuestionProg, Exécutable, Test};
use PHPUnit\Framework\TestCase;
use Mockery;

final class QuestionDAOTests extends TestCase
{
	public function setUp(): void
	{
		parent::setUp();
		$mockChargeurFichier = Mockery::mock("progression\\dao\\question\\ChargeurQuestionFichier");
		$mockChargeurFichier->shouldReceive("récupérer_question")->andReturn([
			"type" => "prog",
			"titre" => "Question de test",
			"ébauches" => [
				"python" => "print(\"Allo le monde\")",
			],
			"tests" => [
				[
					"entrée" => "",
					"sortie" => "Allo le monde",
				],
			],
		]);

		$mockFactory = Mockery::mock("progression\\dao\\question\\ChargeurFactory");
		$mockFactory->shouldReceive("get_chargeur_question_fichier")->andReturn($mockChargeurFichier);

		ChargeurFactory::set_instance($mockFactory);
	}

	public function tearDown(): void
	{
		parent::tearDown();
		ChargeurFactory::set_instance(null);
	}

	public function test_étant_donné_un_fichier_de_question_valide_lorsquon_charge_la_question_on_obtient_un_objet_Question_correspondant()
	{
		$résultat_attendu = new QuestionProg();
		$résultat_attendu->titre = "Question de test";
		$résultat_attendu->exécutables = ["python" => new Exécutable("print(\"Allo le monde\")", "python")];
		$résultat_attendu->tests = [0 => new Test("#1", "Allo le monde", "")];
		$résultat_attendu->uri = "file://" . __DIR__ . "/démo/boucles/boucle_énumérée/info.yml";

		$résultat_obtenu = (new QuestionDAO())->get_question(
			"file://" . __DIR__ . "/démo/boucles/boucle_énumérée/info.yml",
		);

		$this->assertEquals($résultat_attendu, $résultat_obtenu);
	}
}
