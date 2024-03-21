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
			(new ChargeurQuestionGit())->chercher_info(
				"/tmp/gitExemple",
			);
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
		$this->assertEquals(
			$cheminAttendue,
			(new ChargeurQuestionGit())->chercher_info(
				"/tmp/gitExemple",
			)
		);
	}

	public function test_étant_donné_un_lien_public_dun_dépôt_git_lorsquon_récupère_le_lien_on_obtient_le_contenu_de_la_question(){
		$résultatAttendu = [];
		$mockFacadeFile = Mockery::mock("alias:Illuminate\Support\Facades\File");
		$mockFacadeFile->shouldReceive("isDirectory")->with("/tmp")->andReturn(true);
		$repTemporaire = "/tmp/git_repo_" . uniqid();
		mkdir($repTemporaire);
		$uriDépôt = "https://legitexistepas.git";

		$mockAdmin = Mockery::mock("alias:Gitonomy\Git\Admin");
		$mockAdmin->shouldReceive("cloneTo")->with($repTemporaire, $uriDépôt,false);
		$mockFacadeFile->shouldReceive("exists")->with("/tmp/gitExemple/info.yml")->andReturn(true);

		$ChargeurQuestionGit = new ChargeurQuestionGit();
		$résultatObtenue = $ChargeurQuestionGit->récupérer_question("https://git.dti.crosemont.quebec/session-intensive/equipe-recuperation/test-depot-git-progression-avec-un-seul-infoyml.git");
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
		$mockAdmin->shouldReceive("cloneTo")->with($repTemporaire, $uriDépôt,false);
		
		$chargeurQuestionGit = new ChargeurQuestionGit();
		$reflection = new \ReflectionClass(get_class($chargeurQuestionGit));
		$methode = $reflection->getMethod('cloner_dépôt');
		$methode->setAccessible(true);

		try {
			$methode->invokeArgs($chargeurQuestionGit, [$uriDépôt]);
			$this->fail();
		} catch (ChargeurException $e) {
			$this->assertEquals("Le clonage du dépôt git a échoué! Ce dépôt est peut-être privé ou n'existe pas.", $e->getMessage());
		}
	}

	public function test_étant_donné_un_répertoire_temporaire_existant_dans_le_dossier_tmp_losrquon_le_supprime_on_obtient_un_dossier_tmp_vide()
    {
        $mockFacadeFile = Mockery::mock("alias:Illuminate\Support\Facades\File");
        $mockFacadeFile->shouldReceive("isDirectory")->with("/tmp/repertoireTemporaire")->andReturn(true);
        $mockFacadeFile->shouldReceive("deleteDirectory")->with("/tmp/repertoireTemporaire")->andReturn(true);
        $ChargeurQuestionGit = new ChargeurQuestionGit();
        $résultat = $ChargeurQuestionGit->supprimer_répertoire_temporaire("/tmp/repertoireTemporaire");
        $this->assertNull($résultat);
    }

}
