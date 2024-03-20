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

namespace progression\dao;

use progression\TestCase;
use progression\dao\EntitéDAO;

final class BanqueDAOTests extends TestCase
{
	public function setUp(): void
	{
		parent::setUp();
		app("db")->connection()->beginTransaction();
	}

	public function tearDown(): void
	{
		app("db")->connection()->rollBack();
		parent::tearDown();
	}

	public function test_étant_donné_un_utilisateur_existant_qui_na_pas_de_banque_favoris_lorsquon_récupère_toutes_les_banques_on_obtient_null()
	{
		$résultat_observé = (new BanqueDAO())->get_tous("joe");

        $résultats_attendu = [];
        
        $this->assertEquals($résultats_attendu, $résultat_observé);
	}
}
