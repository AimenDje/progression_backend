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

namespace progression\domaine\interacteur;

use progression\domaine\entité\Avancement;
use progression\dao\DAOFactory;
use progression\TestCase;
use Mockery;

final class ObtenirAvancementsIntTests extends TestCase
{
	public function setUp(): void
	{
		parent::setUp();

		$avancement1 = new Avancement([], "prog1/les_fonctions_01/appeler_une_fonction_paramétrée_1", "jdoe");
		$avancement2 = new Avancement([], "prog1/les_fonctions_01/appeler_une_fonction_paramétrée_2", "jdoe");

		$mockAvancementDAO = Mockery::mock("progression\\dao\\AvancementDAO");

		$mockAvancementDAO
			->shouldReceive("get_tous")
			->with("jdoe", [])
			->andReturn([$avancement1, $avancement2]);
		$mockAvancementDAO->shouldReceive("get_tous")->with("bob", [])->andReturn([]);

		$mockDAOFactory = Mockery::mock("progression\\dao\\DAOFactory");
		$mockDAOFactory->allows()->get_avancement_dao()->andReturn($mockAvancementDAO);
		DAOFactory::setInstance($mockDAOFactory);
	}

	public function test_étant_donné_une_collection_d_avancements_pour_un_utilsateur_existant_lorsquon_cherche_par_username_on_obtient_une_collection_d_avancementprog_correspondant()
	{
		$interacteur = new ObtenirAvancementsInt();
		$résultat_obtenu = $interacteur->get_avancements("jdoe");

		$résultat_attendu = [
			new Avancement([], "prog1/les_fonctions_01/appeler_une_fonction_paramétrée_1", "jdoe"),
			new Avancement([], "prog1/les_fonctions_01/appeler_une_fonction_paramétrée_2", "jdoe"),
		];

		$this->assertEquals($résultat_attendu, $résultat_obtenu);
	}

	public function test_étant_donné_un_avancement_avec_un_user_sans_avancements_lorsquon_cherche_par_username_on_obtient_un_tableau_vide()
	{
		$interacteur = new ObtenirAvancementsInt();
		$résultat_obtenu = $interacteur->get_avancements("bob");

		$this->assertEquals([], $résultat_obtenu);
	}
}
