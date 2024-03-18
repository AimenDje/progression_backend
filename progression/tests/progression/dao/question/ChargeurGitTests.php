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

final class ChargeurGitTests extends TestCase
{
	public function test_étant_donnée_un_dossier_en_mémoire_lorsque_on_clone_un_dépôt_git_on_obtient_un_dépôt_git_placer_dans_le_dossier_en_mémoire()
	{
		$résultat_attendu = "/tmp/memoire/git";

		$mockChargeurGit = Mockery::mock("progression\\dao\\question\\ChargeurGit");
		$mockChargeurGit
			->shouldReceive("cloner_dépôt")
			->with("https://git.dti.crosemont.quebec/progression/contenu/prog_1.git")
			->andReturn("/tmp/memoire/git_repo_");

		$mockChargeurFactory = Mockery::mock("progression\\dao\\question\\ChargeurFactory");
		$mockChargeurFactory->shouldReceive("get_chargeur_git")->andReturn($mockChargeurGit);

		$chargeurGit = new ChargeurGit($mockChargeurFactory);
		$résultat_obtenue = $chargeurGit->cloner_dépôt(
			"https://git.dti.crosemont.quebec/progression/contenu/prog_1.git",
		);

		$résultat_obtenue = substr($résultat_obtenue, 0, 16);

		$this->assertEquals($résultat_attendu, $résultat_obtenue);
	}
}
