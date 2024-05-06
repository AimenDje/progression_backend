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
use RuntimeException;
use progression\dao\chargeur\ChargeurException;

final class ChargeurQuestionGitTests extends TestCase
{
	private $contenu_tmp;
	private $répertoire_temporaire = null;

	public function setUp(): void
	{
		parent::setUp();
		$this->contenu_tmp = scandir(getenv("TEMPDIR"));

		$mockAdmin = Mockery::mock("alias:Gitonomy\Git\Admin");

		// Simule un dépôt invalide
		$mockAdmin
			->shouldReceive("cloneTo")
			->with(Mockery::Any(), "https://mondépôt_inexistant.git", false)
			->andThrow(new RuntimeException());

		// Simule le clonage d'un dépôt sans question
		$mockAdmin
			->shouldReceive("cloneTo")
			->withArgs(function ($dir, $url, $bare) {
				return $url == "https://git.com/mondépôt_sans_question.git" && !$bare;
			})
			->andReturn(true);

		// Simule le clonage d'une question valide
		$mockAdmin
			->shouldReceive("cloneTo")
			->withArgs(function ($dir, $url, $bare) {
				if ($url == "https://git.com/mondépôt_valide.git" && !$bare) {
					file_put_contents($dir . "/info.yml", ["type: sys\n", "image: ubuntu\n", "réponse: test\n"]);
					return true;
				} else {
					return false;
				}
			})
			->andReturn(true);
	}

	public function tearDown(): void
	{
		// Le contenu du répertoire /tmp n'a pas changé
		$this->assertEquals($this->contenu_tmp, scandir(getenv("TEMPDIR")));

		parent::tearDown();
	}

	public function test_étant_donné_un_dépôt_git_avec_un_fichier_info_yml_inexistant_losrquon_cherche_info_yml_on_obtient_une_chargeur_exception()
	{
		$this->expectException(ChargeurException::class);
		$this->expectExceptionMessage("Fichier info.yml inexistant dans le dépôt.");

		(new ChargeurQuestionGit())->récupérer_fichier("https://git.com/mondépôt_sans_question.git");
	}

	public function test_étant_donné_un_lien_public_dun_dépôt_git_lorsquon_récupère_le_lien_on_obtient_le_contenu_de_la_question()
	{
		$résultat_attendu = [
			"type" => "sys",
			"image" => "ubuntu",
			"réponse" => "test",
		];

		$ChargeurQuestionGit = new ChargeurQuestionGit();
		$résultat_obtenu = $ChargeurQuestionGit->récupérer_fichier("https://git.com/mondépôt_valide.git");
		$this->assertEquals($résultat_attendu, $résultat_obtenu);
	}

	public function test_étant_donné_un_dépôt_git_inexistant_losrquon_essaie_de_cloner_on_obtient_une_chargeur_exception()
	{
		$this->expectException(ChargeurException::class);
		$this->expectExceptionMessage(
			"Le clonage du dépôt git a échoué! Ce dépôt est peut-être privé ou n'existe pas.",
		);

		(new ChargeurQuestionGit())->récupérer_fichier("https://git.com/mondépôt_inexistant.git");
	}
}
