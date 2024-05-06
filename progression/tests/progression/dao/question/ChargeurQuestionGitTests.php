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
use progression\facades\Git;
use progression\domaine\entité\question\QuestionSys;

use RuntimeException;

final class ChargeurQuestionGitTests extends TestCase
{
	private $contenu_tmp;

	public function setUp(): void
	{
		parent::setUp();

		$this->contenu_tmp = scandir(getenv("TEMPDIR"));
	}

	public function tearDown(): void
	{
		$this->assertEquals($this->contenu_tmp, scandir(getenv("TEMPDIR")));

		parent::tearDown();
	}

	public function test_étant_donné_un_dépôt_distant_existant_lorsquon_récupère_l_id_de_modification_sans_fournir_de_branche_on_btient_le_numéro_du_dernier_commit_sur_main()
	{
		Git::shouldReceive("ls_remote")
			->with("https://test.com/depot.git", ["main", "master"], ["--heads", "--refs"])
			->andReturn(["47b416917044fd7dd3591c2aa74c1239a33639f6	refs/heads/main"]);

		$résultat_obtenu = (new ChargeurQuestionGit())->id_modif("https://test.com/depot.git");

		$résultat_attendu = "47b416917044fd7dd3591c2aa74c1239a33639f6";

		$this->assertEquals($résultat_attendu, $résultat_obtenu);
	}

	public function test_étant_donné_un_dépôt_distant_existant_lorsquon_récupère_l_id_de_modification_en_spécifiant_une_branche_on_btient_le_numéro_du_dernier_commit_sur_cette_branche()
	{
		Git::shouldReceive("ls_remote")
			->with("https://test.com/depot.git", ["test_1"], ["--heads", "--refs"])
			->andReturn(["a22973df618592429debce37cf24c6c7084006fd	refs/heads/test_1"]);

		$résultat_obtenu = (new ChargeurQuestionGit())->id_modif("https://test.com/depot.git#test_1");

		$résultat_attendu = "a22973df618592429debce37cf24c6c7084006fd";

		$this->assertEquals($résultat_attendu, $résultat_obtenu);
	}

	public function test_étant_donné_un_dépôt_distant_existant_lorsquon_récupère_l_id_de_modification_en_spécifiant_une_branche_inexistante_on_btient_une_ChargeurException()
	{
		Git::shouldReceive("ls_remote")
			->with("https://test.com/depot.git", ["inexistante"], ["--heads", "--refs"])
			->andReturn([]);

		$this->expectException(ChargeurException::class);
		$this->expectExceptionMessage(
			"Impossible de récupérer le dernier commit sur l'une des branches [inexistante].",
		);

		$résultat_obtenu = (new ChargeurQuestionGit())->id_modif("https://test.com/depot.git#inexistante");
	}

	public function test_étant_donné_un_dépôt_distant_inexistant_lorsquon_récupère_l_id_de_modification_on_btient_une_ChargeurException()
	{
		Git::shouldReceive("ls_remote")
			->with("https://test.com/depot_inexistant.git#master", Mockery::Any(), ["--heads", "--refs"])
			->andThrow(new RuntimeException());

		$this->expectException(ChargeurException::class);
		$this->expectExceptionMessage(
			"Le dépôt «https://test.com/depot_inexistant.git» n'existe pas ou est inaccessible.",
		);

		$résultat_obtenu = (new ChargeurQuestionGit())->id_modif("https://test.com/depot_inexistant.git");
	}

	public function test_étant_donné_un_dépôt_avec_une_question_valide_lorsquon_le_récupère_on_obtient_la_question_valide()
	{
		Git::shouldReceive("clone")
			->once()
			->withArgs(function ($dir, $url, $options) {
				if ($url == "https://test.com/depot.git" && $options == ["--depth=1", "--single-branch"]) {
					file_put_contents($dir . "/info.yml", ["type: sys\n", "image: ubuntu\n", "réponse: test\n"]);
					return true;
				}
				return false;
			});

		$résultat_obtenu = (new ChargeurQuestionGit())->récupérer_question("https://test.com/depot.git");

		$résultat_attendu = [
			"type" => "sys",
			"image" => "ubuntu",
			"réponse" => "test",
		];
		$this->assertEquals($résultat_attendu, $résultat_obtenu);
	}

	public function test_étant_donné_un_dépôt_avec_une_question_valide_lorsquon_le_récupère_une_branche_spécifique_on_obtient_la_question_valide()
	{
		Git::shouldReceive("clone")
			->once()
			->withArgs(function ($dir, $url, $options) {
				if (
					$url == "https://test.com/depot.git" &&
					$options == ["--depth=1", "--single-branch", "--branch=une_branche"]
				) {
					file_put_contents($dir . "/info.yml", [
						"type: sys\n",
						"image: ubuntu\n",
						"réponse: une branche spécifique\n",
					]);
					return true;
				}
				return false;
			});

		$résultat_obtenu = (new ChargeurQuestionGit())->récupérer_question("https://test.com/depot.git#une_branche");

		$résultat_attendu = [
			"type" => "sys",
			"image" => "ubuntu",
			"réponse" => "une branche spécifique",
		];
		$this->assertEquals($résultat_attendu, $résultat_obtenu);
	}

	public function test_étant_donné_un_dépôt_avec_une_question_valide_lorsquon_le_récupère_une_branche_spécifique_inexistante_on_obtient_une_ChargeurException()
	{
		Git::shouldReceive("clone")
			->once()
			->with(Mockery::Any(), "https://test.com/depot.git", [
				"--depth=1",
				"--single-branch",
				"--branch=branche_inexistante",
			])
			->andThrow(new RuntimeException());

		$this->expectException(ChargeurException::class);
		$this->expectExceptionMessage(
			"Le clonage du dépôt «https://test.com/depot.git» a échoué! Le dépôt n'existe pas ou est inaccessible.",
		);

		$résultat_obtenu = (new ChargeurQuestionGit())->récupérer_question(
			"https://test.com/depot.git#branche_inexistante",
		);
	}

	public function test_étant_donné_un_dépôt_avec_une_question_invalide_lorsquon_le_récupère_on_obtient_une_ChargeurException()
	{
		Git::shouldReceive("clone")
			->once()
			->withArgs(function ($dir, $url, $options) {
				if ($url == "https://test.com/depot.git" && $options == ["--depth=1", "--single-branch"]) {
					file_put_contents($dir . "/info.yml", ["type: invalide\n"]);
					return true;
				}
				return false;
			});

		$this->expectException(ChargeurException::class);
		$this->expectExceptionMessage("Le fichier n'existe pas ou est invalide. (err: 1)");

		$résultat_obtenu = (new ChargeurQuestionGit())->récupérer_question("https://test.com/depot.git");
	}

	public function test_étant_donné_un_dépôt_sans_info_yml_lorsquon_tente_de_le_récupérer_on_obtient_une_ChargeurException()
	{
		Git::shouldReceive("clone")
			->once()
			->withArgs(function ($dir, $url, $options) {
				if ($url == "https://test.com/depot.git" && $options == ["--depth=1", "--single-branch"]) {
					return true;
				}
				return false;
			});

		$this->expectException(ChargeurException::class);
		$this->expectExceptionMessage("Le fichier n'existe pas ou est invalide. (err: 255)");
		$résultat_obtenu = (new ChargeurQuestionGit())->récupérer_question("https://test.com/depot.git");
	}

	public function test_étant_donné_un_dépôt_inexistant_lorsquon_tente_de_le_récupérer_on_obtient_une_ChargeurException()
	{
		Git::shouldReceive("clone")
			->once()
			->with(Mockery::Any(), "https://test.com/depot.git", ["--depth=1", "--single-branch"])
			->andThrow(new RuntimeException("Le dépôt n'existe pas"));

		$this->expectException(ChargeurException::class);
		$this->expectExceptionMessage(
			"Le clonage du dépôt «https://test.com/depot.git» a échoué! Le dépôt n'existe pas ou est inaccessible.",
		);
		$résultat_obtenu = (new ChargeurQuestionGit())->récupérer_question("https://test.com/depot.git");
	}
}
