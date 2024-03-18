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

final class ChargeurQuestionGitTests extends TestCase
{
	private $contenu_tmp;

	public function setUp(): void
	{
		parent::setUp();

		$this->contenu_tmp = scandir("/tmp");
	}

	public function tearDown(): void
	{
		$this->assertEquals($this->contenu_tmp, scandir("/tmp"));

		parent::tearDown();
	}

	public function test_étant_donné_un_url_dépôt_git_lorsquon_charge_la_question_on_obtient_un_objet_Question_correspondant()
	{
		$questionAttendue = [];

		$mockChargeurGit = Mockery::mock("progression\\dao\\question\\ChargeurGit");
		$mockChargeurGit
			->shouldReceive("cloner_dépôt")
			->with("url_du_dépôt_git")
			->andReturn("/chemin/dépôt_temporaire")
			->shouldReceive("chercher_info")
			->with("/chemin/dépôt_temporaire")
			->andReturn("/chemin/dépôt_temporaire/info.yml")
			->shouldReceive("supprimer_répertoire_temporaire")
			->with("/chemin/dépôt_temporaire");

		$mockChargeurFichier = Mockery::mock("progression\\dao\\question\\ChargeurQuestionFichier");
		$mockChargeurFichier
			->shouldReceive("récupérer_question")
			->with("/chemin/dépôt_temporaire/info.yml")
			->andReturn($questionAttendue);

		$mockChargeurFactory = Mockery::mock("progression\\dao\\question\\ChargeurFactory");
		$mockChargeurFactory->shouldReceive("get_chargeur_git")->andReturn($mockChargeurGit);
		$mockChargeurFactory->shouldReceive("get_chargeur_question_fichier")->andReturn($mockChargeurFichier);

		print_r(
			"retour chemin fichier test " .
				json_encode(
					$mockChargeurFichier->récupérer_question(
						$mockChargeurGit->chercher_info("/chemin/dépôt_temporaire"),
					),
				),
		);

		$this->assertEquals(
			$questionAttendue,
			(new ChargeurQuestionGit($mockChargeurFactory))->récupérer_question("url_du_dépôt_git"),
		);
	}

	public function test_étant_donné_un_url_dépôt_git_privé_lorsquon_charge_la_question_on_obtient_une_exception_avec_un_message()
	{
		$mockChargeurGit = Mockery::mock("progression\\dao\\question\\ChargeurGit");
		$mockChargeurGit
			->shouldReceive("cloner_dépôt")
			->with("url_du_dépôt_git_privé")
			->andThrow(
				new \RuntimeException(
					"Le clonage du dépôt git a échoué! Ce dépôt est peut-être privé ou n'existe pas.",
				),
			);

		$mockChargeurFactory = Mockery::mock("progression\\dao\\question\\ChargeurFactory");
		$mockChargeurFactory->shouldReceive("get_chargeur_git")->andReturn($mockChargeurGit);

		$chargeurQuestionGit = new ChargeurQuestionGIT($mockChargeurFactory);

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage(
			"Le clonage du dépôt git a échoué! Ce dépôt est peut-être privé ou n'existe pas.",
		);

		$chargeurQuestionGit->récupérer_question("url_du_dépôt_git_privé");
	}

	public function test_étant_donné_un_url_dépôt_git_dans_lequel_le_fichier_infoYml_est_inexistant_lorsquon_charge_la_question_on_obtient_une_exception_avec_un_message()
	{
		$mockChargeurGit = Mockery::mock("progression\\dao\\question\\ChargeurGit");
		$mockChargeurGit
			->shouldReceive("cloner_dépôt")
			->with("url_du_dépôt_git_sans_info.yml")
			->andReturn("/chemin/dépôt_temporaire")
			->shouldReceive("chercher_info")
			->with("/chemin/dépôt_temporaire")
			->andThrow(new ChargeurException("Fichier info.yml inexistant."));

		$mockChargeurFactory = Mockery::mock("progression\\dao\\question\\ChargeurFactory");
		$mockChargeurFactory->shouldReceive("get_chargeur_git")->andReturn($mockChargeurGit);

		$chargeurQuestionGit = new ChargeurQuestionGit($mockChargeurFactory);

		$this->expectException(ChargeurException::class);
		$this->expectExceptionMessage("Fichier info.yml inexistant.");

		$chargeurQuestionGit->récupérer_question("url_du_dépôt_git_sans_info.yml");
	}
}
