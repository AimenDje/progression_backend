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

	public function tearDown(): void
	{
		$this->assertEquals($this->contenu_tmp, scandir("/tmp"));

		parent::tearDown();
	}

	public function test_étant_donné_un_url_dépôt_git_lorsquon_charge_la_question_on_obtient_un_objet_Question_correspondant()
	{
	}

	public function test_étant_donné_un_url_dépôt_git_privé_lorsquon_charge_la_question_on_obtient_une_exception_avec_un_message()
	{
	}

	public function test_étant_donné_un_url_dépôt_git_dans_lequel_le_fichier_infoYml_est_inexistant_lorsquon_charge_la_question_on_obtient_une_exception_avec_un_message()
	{
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

	public function test_étant_donné_un_lien_public_dun_dépôt_git_lorsquon_récupère_le_lien_on_obtient_le_contenu_de_la_question()
	{
		$résultatAttendu = [
			"niveau" => "intro",
			"rétroactions" => [
				"erreur" => "La page [wikipédia sur Kotlin](https://fr.wikipedia.org/wiki/Kotlin_(langage)) peut vous aider.",
				"négative" => "Assurez-vous que le texte affiché soit exactement celui demandé",
				"positive" => "Bravo! Passez maintenant à l'exercice suivant."
			],
			"tests" => [
				[
					"sortie" => "Bonjour le monde de Kotlin!\n"
				]
			],
			"titre" => "Bonjour Kotlin! (DIR:question)",
			"type" => "prog",
			"uuid" => "1a879ae8-c0af-49b6-889b-3370f07f5418",
			"ébauches" => [
				"kotlin" => "//-VISIBLE\nfun main(){\n//+VISIBLE\n// Écrivez votre code ici 👇\n// et cliquez sur le triangle vert pour le tester.\n// INFO.YML dans DIR QuESTION\n//+TODO\n\n\n//-TODO\n//-VISIBLE\n}\n"
			],
			"énoncé" => "Bienvenue au merveilleux monde de [Kotlin](https://fr.wikipedia.org/wiki/Kotlin_(langage)).\n\nComme premier programme, telle que le veut la coutume, il s'agira de faire afficher la phrase suivante :\n\n    Bonjour le monde de Kotlin!\n"
		];

		$ChargeurQuestionGit = new ChargeurQuestionGit();
		$résultatObtenue = $ChargeurQuestionGit->récupérer_question("https://git.dti.crosemont.quebec/session-intensive/equipe-recuperation/test-depot-git-progression-avec-un-seul-infoyml.git");
		$this->assertEquals($résultatAttendu, $résultatObtenue);
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
