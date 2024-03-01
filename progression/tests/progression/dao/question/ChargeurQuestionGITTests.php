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

use progression\TestCase;
use Mockery;
use progression\domaine\entité\question\QuestionProg;
use Illuminate\Support\Facades\Log;

final class ChargeurQuestionGITTests extends TestCase
{
	private $contenu_tmp;

	public function setUp(): void
	{
		parent::setUp();

		$this->contenu_tmp = scandir("/tmp");
	}

	public function tearDown(): void
	{
		// Le contenu du répertoire /tmp n'a pas changé
		$this->assertEquals($this->contenu_tmp, scandir("/tmp"));

		parent::tearDown();
	}

	public function test_étant_donné_un_url_depot_git_lorsquon_charge_la_question_on_obtient_un_objet_Question_correspondant()
	{
		// Créer un objet Question attendu
		$questionAttendue = new QuestionProg();
		$questionAttendue->titre = "Question de test";

		// Mock du ChargeurGIT
		$mockChargeurGIT = Mockery::mock("overload:progression\\dao\\question\\ChargeurGIT");
		$mockChargeurGIT
			->shouldReceive("cloner_depot")
			->with("url_du_depot_git")
			->andReturn("/chemin/depot_temporaire")
			->shouldReceive("chercher_info")
			->with("/chemin/depot_temporaire")
			->andReturn("/chemin/depot_temporaire/info.yml");

		// Mock du ChargeurQuestionFichier
		$mockChargeurFichier = Mockery::mock("progression\\dao\\question\\ChargeurQuestionFichier");
		$mockChargeurFichier
			->shouldReceive("récupérer_question")
			->with("/chemin/depot_temporaire/info.yml")
			->andReturn($questionAttendue); // Retourne l'objet Question attendu

		// Mock du ChargeurFactory
		$mockChargeurFactory = Mockery::mock("progression\\dao\\question\\ChargeurFactory");
		$mockChargeurFactory->shouldReceive("get_chargeur_git")->andReturn($mockChargeurGIT);
		$mockChargeurFactory->shouldReceive("get_chargeur_question_fichier")->andReturn($mockChargeurFichier);

		Log::debug(
			"retour chemin fichier test " .
				json_encode(
					$mockChargeurFichier->récupérer_question(
						$mockChargeurGIT->chercher_info("/chemin/depot_temporaire"),
					),
				),
		);

		// Vérifier que l'objet retourné est bien le même que l'objet attendu
		$this->assertEquals(
			$questionAttendue,
			(new ChargeurQuestionGIT($mockChargeurFactory))->récupérer_question("url_du_depot_git"),
		);
	}
}
