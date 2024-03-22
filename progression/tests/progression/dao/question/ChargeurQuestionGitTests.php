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

final class ChargeurQuestionGitTests extends TestCase
{
	private $contenu_tmp;

	public function setUp(): void
	{
		parent::setUp();

		$this->contenu_tmp = scandir("/tmp");
	}

	public function test_étant_donné_un_dépôt_git_avec_un_fichier_info_yml_inexistant_losrquon_cherche_info_yml_on_obtient_une_runtime_exception()
	{
		$mockFacadeFile = Mockery::mock("alias:Illuminate\Support\Facades\File");
		$mockFacadeFile->shouldReceive("exists")->with("/tmp/gitExemple/info.yml")->andReturn(false);
		try {
			(new ChargeurQuestionGit())->chercher_info("/tmp/gitExemple");
			$this->fail();
		} catch (RuntimeException $e) {
			$this->assertEquals("Fichier info.yml inexistant dans le dépôt.", $e->getMessage());
		}
	}

	public function test_étant_donné_un_dépôt_git_avec_un_fichier_info_yml_existant_losrquon_cherche_info_yml_on_obtient_le_chemin_du_fichier()
	{
		$cheminAttendue = "/tmp/gitExemple/info.yml";
		$mockFacadeFile = Mockery::mock("alias:Illuminate\Support\Facades\File");
		$mockFacadeFile->shouldReceive("exists")->with("/tmp/gitExemple/info.yml")->andReturn(true);
		$this->assertEquals($cheminAttendue, (new ChargeurQuestionGit())->chercher_info("/tmp/gitExemple"));
	}

	public function test_étant_donné_un_lien_public_dun_dépôt_git_lorsquon_récupère_le_lien_on_obtient_le_contenu_de_la_question()
	{
		$résultatAttendu = [];

		$mockChargeurQuestionGit = Mockery::mock("progression\\dao\\question\\ChargeurQuestionGit");

		$mockFacadeFile = Mockery::mock("alias:Illuminate\Support\Facades\File");
		$mockFacadeFile->shouldReceive("deleteDirectory")->andReturn(true);
		$mockFacadeFile->shouldReceive("isDirectory")->andReturn(true);
		$mockFacadeFile->shouldReceive("exists")->andReturn(true);

		$mockAdmin = Mockery::mock("alias:Gitonomy\Git\Admin");
		$mockAdmin->shouldReceive("cloneTo")->andReturn();

		$mockChargeurQuestionGit->shouldReceive("chercher_info")->andReturn("");

		$mockChargeurFichier = Mockery::mock("progression\\dao\\question\\ChargeurQuestionFichier");
		$mockChargeurFichier->shouldReceive("récupérer_question")->andReturn($résultatAttendu);
		$mockChargeurFactory = Mockery::mock("progression\\dao\\question\\ChargeurFactory");
		$mockChargeurFactory->shouldReceive("get_chargeur_question_fichier")->andReturn($mockChargeurFichier);

		$ChargeurQuestionGit = new ChargeurQuestionGit($mockChargeurFactory);
		$résultatObtenue = $ChargeurQuestionGit->récupérer_question("");
		$this->assertEquals($résultatAttendu, $résultatObtenue);
	}

	public function test_étant_donné_un_dépôt_git_inexistant_losrquon_essaie_de_cloner_on_obtient_une_chargeur_exception()
	{
		$mockFacadeFile = Mockery::mock("alias:Illuminate\Support\Facades\File");
		$mockFacadeFile->shouldReceive("isDirectory")->with("/tmp")->andReturn(true);
		$repTemporaire = "/tmp/git_repo_" . uniqid();
		mkdir($repTemporaire);
		$uriDépôt = "https://legitexistepas.git";

		$mockAdmin = Mockery::mock("alias:Gitonomy\Git\Admin");
		$mockAdmin->shouldReceive("cloneTo")->with($repTemporaire, $uriDépôt, false);

		$chargeurQuestionGit = new ChargeurQuestionGit();
		$reflection = new \ReflectionClass(get_class($chargeurQuestionGit));
		$methode = $reflection->getMethod("cloner_dépôt");
		$methode->setAccessible(true);

		try {
			$methode->invokeArgs($chargeurQuestionGit, [$uriDépôt]);
			$this->fail();
		} catch (ChargeurException $e) {
			rmdir($repTemporaire);
			$this->assertEquals(
				"Le clonage du dépôt git a échoué! Ce dépôt est peut-être privé ou n'existe pas.",
				$e->getMessage(),
			);
		}
	}

	public function test_étant_donné_un_répertoire_temporaire_inexistant_losrquon_vérifie_si_il_existe_on_obtient_une_chargeur_exception()
	{
		$mockFacadeFile = Mockery::mock("alias:Illuminate\Support\Facades\File");
		$mockFacadeFile->shouldReceive("isDirectory")->with("/tmp")->andReturn(false);

		$uriDépôt = "https://legitexistepas.git";

		$chargeurQuestionGit = new ChargeurQuestionGit();
		$reflection = new \ReflectionClass(get_class($chargeurQuestionGit));
		$methode = $reflection->getMethod("cloner_dépôt");
		$methode->setAccessible(true);

		try {
			$methode->invokeArgs($chargeurQuestionGit, [$uriDépôt]);
			$this->fail();
		} catch (ChargeurException $e) {
			$this->assertEquals("Le répertoire cible où le clone est sensé se faire n'existe pas.", $e->getMessage());
		}
	}

	public function test_étant_donné_un_dépôt_git_losrquon_essaie_de_le_cloner_on_obtient_son_chemin_de_son_répertoire()
	{
		$résultat_attendu = "/tmp/git_repo_";

		$mockFacadeFile = Mockery::mock("alias:Illuminate\Support\Facades\File");
		$mockFacadeFile->shouldReceive("isDirectory")->with("/tmp")->andReturn(true);

		$uriDépôt = "https://legitexistepas.git";

		$mockAdmin = Mockery::mock("alias:Gitonomy\Git\Admin");
		$mockAdmin->shouldReceive("cloneTo")->andReturn();

		$chargeurQuestionGit = new ChargeurQuestionGit();
		$reflection = new \ReflectionClass(get_class($chargeurQuestionGit));
		$methode = $reflection->getMethod("cloner_dépôt");
		$methode->setAccessible(true);

		$résultat_obtenue = $methode->invokeArgs($chargeurQuestionGit, [$uriDépôt]);

		$résultat_obtenue = substr($résultat_obtenue, 0, 14);

		$this->assertEquals($résultat_attendu, $résultat_obtenue);
	}

	public function test_étant_donné_un_répertoire_temporaire_existant_dans_le_dossier_tmp_losrquon_le_supprime_on_obtient_un_dossier_tmp_vide()
	{
		$mockFacadeFile = Mockery::mock("alias:Illuminate\Support\Facades\File");
		$mockFacadeFile->shouldReceive("isDirectory")->with("/tmp/repertoireTemporaire")->andReturn(true);
		$mockFacadeFile->shouldReceive("deleteDirectory")->with("/tmp/repertoireTemporaire")->andReturn(true);
		$chargeurQuestionGit = new ChargeurQuestionGit();
		$reflection = new \ReflectionClass(get_class($chargeurQuestionGit));
		$supprimer = $reflection->getMethod("supprimer_répertoire_temporaire");
		$supprimer->setAccessible(true);
		$résultat = $supprimer->invoke($chargeurQuestionGit, "/tmp/repertoireTemporaire");
		$this->assertNull($résultat);
	}
}
