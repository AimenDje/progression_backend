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

use progression\domaine\entité\clé\{Clé, Portée};
use progression\dao\DAOFactory;
use PHPUnit\Framework\TestCase;
use Mockery;

final class ObtenirCléIntTests extends TestCase
{
	public function setUp(): void
	{
		parent::setUp();

		$clé = new Clé(1234, "2021-06-25 00:00:00", "2021-06-26 00:00:00", Portée::AUTH);

		$mockCléDAO = Mockery::mock("progression\\dao\\CléDAO");
		$mockCléDAO->shouldReceive("get_clé")->with("jdoe", 1234, [])->andReturn($clé);
		$mockCléDAO->shouldReceive("get_clé")->with("jdoe", 9999, [])->andReturn(null);

		$mockDAOFactory = Mockery::mock("progression\\dao\\DAOFactory");
		$mockDAOFactory->allows()->get_clé_dao()->andReturn($mockCléDAO);
		DAOFactory::setInstance($mockDAOFactory);
	}

	public function test_étant_donné_une_clé_existante_lorsquon_la_recherche_par_username_et_numéro_on_obtient_un_objet_Clé_correspondant()
	{
		$int = new ObtenirCléInt();

		$this->assertEquals(
			new Clé(null, "2021-06-25 00:00:00", "2021-06-26 00:00:00", Portée::AUTH),
			$int->get_clé("jdoe", 1234),
		);
	}

	public function test_étant_donné_une_clé_inexistante_lorsquon_la_recherche_par_username_et_numéro_on_obtient_null()
	{
		$int = new ObtenirCléInt();

		$this->assertNull($int->get_clé("jdoe", 9999));
	}
}
