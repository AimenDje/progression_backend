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
use Gitonomy\Git\Repository;
use Gitonomy\Git\Commit;
use RuntimeException;
use Illuminate\Support\Facades\Cache;

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
		parent::tearDown();
	}

	public function test_étant_donné_un_dépôt_git_avec_un_fichier_info_yml_inexistant_losrquon_cherche_info_yml_on_obtient_une_runtime_exception()
	{
		$mockFacadeFile = Mockery::mock("alias:Illuminate\Support\Facades\File");
		$mockFacadeFile->shouldReceive("exists")->with("/tmp/gitExemple/info.yml")->andReturn(false);
		$chargeurQuestionGit = new ChargeurQuestionGit();
		$reflection = new \ReflectionClass(get_class($chargeurQuestionGit));
		$methode = $reflection->getMethod("chercher_info");
		$methode->setAccessible(true);
		try {
			$methode->invokeArgs($chargeurQuestionGit, ["/tmp/gitExemple"]);
			$this->fail();
		} catch (RuntimeException $e) {
			$this->assertEquals("Fichier info.yml inexistant dans le dépôt.", $e->getMessage());
		}
	}

	public function test_étant_donné_un_dépôt_git_avec_un_fichier_info_yml_existant_losrquon_cherche_info_yml_on_obtient_le_chemin_du_fichier()
	{
		$cheminAttendue = "/tmp/gitExemple/info.yml";
		$mockFacadeFile = Mockery::mock("alias:Illuminate\Support\Facades\File");
		$chargeurQuestionGit = new ChargeurQuestionGit();
		$reflection = new \ReflectionClass(get_class($chargeurQuestionGit));
		$methode = $reflection->getMethod("chercher_info");
		$methode->setAccessible(true);
		$mockFacadeFile->shouldReceive("exists")->with("/tmp/gitExemple/info.yml")->andReturn(true);
		$cheminTesté = $methode->invokeArgs($chargeurQuestionGit, ["/tmp/gitExemple"]);
		$this->assertEquals($cheminAttendue, $cheminTesté);
	}

	public function test_étant_donné_un_lien_public_dun_dépôt_git_lorsquon_récupère_le_lien_on_obtient_le_contenu_de_la_question()
	{
		$résultatAttendu = [];
		$hashCommitAttendu = "e1x2e3m4p5l6e7";

		$mockFacadeFile = Mockery::mock("alias:Illuminate\Support\Facades\File");
		$mockFacadeFile->shouldReceive("deleteDirectory")->andReturn(true);
		$mockFacadeFile->shouldReceive("isDirectory")->andReturn(true);
		$mockFacadeFile->shouldReceive("exists")->andReturn(true);

		$mockAdmin = Mockery::mock("alias:Gitonomy\Git\Admin");
		$mockAdmin->shouldReceive("cloneTo")->andReturn(true);

		$mockRepository = Mockery::mock(Repository::class);
		$mockCommit = Mockery::mock(Commit::class);
		$mockCommit->shouldReceive("getHash")->andReturn($hashCommitAttendu);
		$mockRepository->shouldReceive("getHeadCommit")->andReturn($mockCommit);

		$mockChargeurFichier = Mockery::mock("progression\\dao\\question\\ChargeurQuestionFichier");
		$mockChargeurFichier->shouldReceive("récupérer_question")->andReturn($résultatAttendu);

		$mockChargeurFactory = Mockery::mock("progression\\dao\\question\\ChargeurFactory");
		$mockChargeurFactory->shouldReceive("get_chargeur_question_fichier")->andReturn($mockChargeurFichier);
		$mockChargeurFactory->shouldReceive("get_repository")->andReturn($mockRepository);
		$mockChargeurFactory->shouldReceive("get_admin")->andReturn($mockAdmin);

		Cache::shouldReceive("put")->once();

		$ChargeurQuestionGit = new ChargeurQuestionGit($mockChargeurFactory);

		$résultatObtenu = $ChargeurQuestionGit->récupérer_question("https://exemple.git");

		$this->assertEquals($résultatAttendu, $résultatObtenu);
	}

	public function test_étant_donné_un_dépôt_git_inexistant_losrquon_essaie_de_cloner_on_obtient_une_chargeur_exception()
	{
		$mockFacadeFile = Mockery::mock("alias:Illuminate\Support\Facades\File");
		$mockFacadeFile->shouldReceive("isDirectory")->with("/tmp")->andReturn(true);

		$uriDépôt = "https://exemple.git";
		$repTemporaire = "/tmp/git_repo_" . uniqid();

		$mockAdmin = Mockery::mock("alias:Gitonomy\Git\Admin");
		$mockAdmin
			->shouldReceive("cloneTo")
			->with($repTemporaire, $uriDépôt, false)
			->andThrow(new RuntimeException("Clonage échoué."));

		$mockChargeurFactory = Mockery::mock("ChargeurFactory");
		$mockChargeurFactory->shouldReceive("get_admin")->andReturn($mockAdmin);

		$chargeurQuestionGit = new ChargeurQuestionGit($mockChargeurFactory);

		$reflection = new \ReflectionClass(get_class($chargeurQuestionGit));
		$methode = $reflection->getMethod("cloner_dépôt");
		$methode->setAccessible(true);

		try {
			$methode->invokeArgs($chargeurQuestionGit, [$uriDépôt]);
			$this->fail();
		} catch (ChargeurException $e) {
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

		$uriDépôt = "https://exemple.git";
		$repTemporaire = "/tmp/git_repo_" . uniqid();

		$mockAdmin = Mockery::mock("alias:Gitonomy\Git\Admin");
		$mockAdmin->shouldReceive("cloneTo")->with($repTemporaire, $uriDépôt, false);

		$mockChargeurFactory = Mockery::mock("ChargeurFactory");
		$mockChargeurFactory->shouldReceive("get_admin")->andReturn($mockAdmin);

		$chargeurQuestionGit = new ChargeurQuestionGit($mockChargeurFactory);
		$reflection = new \ReflectionClass(get_class($chargeurQuestionGit));
		$methode = $reflection->getMethod("cloner_dépôt");
		$methode->setAccessible(true);

		try {
			$methode->invokeArgs($chargeurQuestionGit, [$uriDépôt]);
			$this->fail();
		} catch (ChargeurException $e) {
			$this->assertEquals(
				"Le clonage du dépôt git a échoué! Ce dépôt est peut-être privé ou n'existe pas.",
				$e->getMessage(),
			);
		}
	}

	public function test_étant_donné_un_dépôt_git_losrquon_essaie_de_le_cloner_on_obtient_son_chemin_de_son_répertoire()
	{
		$résultat_attendu = "/tmp/git_repo_";

		$mockFacadeFile = Mockery::mock("alias:Illuminate\Support\Facades\File");
		$mockFacadeFile->shouldReceive("isDirectory")->with("/tmp")->andReturn(true);

		$uriDépôt = "https://exemple.git";

		$mockAdmin = Mockery::mock("alias:Gitonomy\Git\Admin");
		$mockAdmin->shouldReceive("cloneTo")->andReturn(true);

		$mockChargeurFactory = Mockery::mock("ChargeurFactory");
		$mockChargeurFactory->shouldReceive("get_admin")->andReturn($mockAdmin);

		$chargeurQuestionGit = new ChargeurQuestionGit($mockChargeurFactory);
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

	public function test_étant_donné_un_répertoire_temporaire_lorsquon_essaye_de_récupérer_le_dernier_commit_on_obtient_le_hash_du_commit()
	{
		$hashAttendu = "exemple123";
		$mockCommit = Mockery::mock(Commit::class);
		$mockCommit->shouldReceive("getHash")->andReturn($hashAttendu);

		$mockRepository = Mockery::mock(Repository::class);
		$mockRepository->shouldReceive("getHeadCommit")->andReturn($mockCommit);

		$mockChargeurFactory = Mockery::mock(ChargeurFactory::class);
		$mockChargeurFactory->shouldReceive("get_repository")->andReturn($mockRepository);

		$chargeurQuestionGit = new ChargeurQuestionGit($mockChargeurFactory);

		$reflection = new \ReflectionClass($chargeurQuestionGit);
		$methode = $reflection->getMethod("getIdDernierCommit");
		$methode->setAccessible(true);

		$resultat = $methode->invokeArgs($chargeurQuestionGit, ["/tmp/git_repo_test"]);

		$this->assertEquals($hashAttendu, $resultat);
	}

	public function test_étant_donné_un_répertoire_temporaire_avec_aucun_commit_lorsquon_essaye_de_récupérer_un_commit_on_obtient_une_RuntimeException()
	{
		$mockRepository = Mockery::mock(Repository::class);
		$mockRepository->shouldReceive("getHeadCommit");

		$mockChargeurFactory = Mockery::mock(ChargeurFactory::class);
		$mockChargeurFactory->shouldReceive("get_repository")->andReturn($mockRepository);

		$chargeurQuestionGit = new ChargeurQuestionGit($mockChargeurFactory);

		$reflection = new \ReflectionClass($chargeurQuestionGit);
		$methode = $reflection->getMethod("getIdDernierCommit");
		$methode->setAccessible(true);

		try {
			$methode->invokeArgs($chargeurQuestionGit, ["/tmp/git_repo_test"]);
			$this->fail();
		} catch (RuntimeException $e) {
			$this->assertEquals("Aucun commit trouvé dans le dépôt cloné.", $e->getMessage());
		}
	}
}
