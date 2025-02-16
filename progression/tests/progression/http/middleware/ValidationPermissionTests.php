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

use progression\TestCase;

use progression\http\middleware\ValidationPermissions;
use progression\domaine\entité\User;
use progression\dao\{DAOFactory, UserDAO};
use Illuminate\Auth\GenericUser;

final class ValidationPermissionsTests extends TestCase
{
	public $user;

	public function setup(): void
	{
		parent::setUp();

		$_ENV["AUTH_TYPE"] = "ldap";
		$this->user = new GenericUser(["username" => "bob", "rôle" => User::ROLE_NORMAL]);

		// UserDAO
		$mockUserDAO = Mockery::mock("progression\\dao\\UserDAO");
		$mockUserDAO
			->allows()
			->get_user("bob")
			->andReturn(new User("bob"));
		$mockUserDAO
			->allows()
			->get_user("jdoe")
			->andReturn(new User("jdoe"));

		// DAOFactory
		$mockDAOFactory = Mockery::mock("progression\\dao\\DAOFactory");
		$mockDAOFactory->shouldReceive("get_user_dao")->andReturn($mockUserDAO);
		DAOFactory::setInstance($mockDAOFactory);
	}

	public function tearDown(): void
	{
		Mockery::close();
		DAOFactory::setInstance(null);
	}

	public function test_étant_donné_un_utilisateur_normal_bob_connecté_lorsquon_demande_une_ressource_pour_ce_même_utilisateur_on_obtient_OK()
	{
		$résultat_obtenu = $this->actingAs($this->user)->call("GET", "/user/bob");

		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/profil_bob.json",
			$résultat_obtenu->getContent(),
		);
	}

	public function test_étant_donné_un_utilisateur_normal_bob_connecté_lorsquon_demande_une_ressource_pour_l_utilisateur_jdoe_on_obtient_erreur_403()
	{
		$résultat_obtenu = $this->actingAs($this->user)->call("GET", "/user/jdoe");

		$this->assertEquals(403, $résultat_obtenu->status());
		$this->assertEquals('{"erreur":"Opération interdite."}', $résultat_obtenu->getContent());
	}

	public function test_étant_donné_un_utilisateur_admin_connecté_lorsquon_demande_une_ressource_pour_l_utilisateur_existant_bob_on_obtient_son_profil()
	{
		$admin = new GenericUser(["username" => "admin", "rôle" => User::ROLE_ADMIN]);
		$résultat_obtenu = $this->actingAs($admin)->call("GET", "/user/bob");

		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/profil_bob.json",
			$résultat_obtenu->getContent(),
		);
	}

	public function test_étant_donné_un_utilisateur_normal_connecté_lorsquon_demande_une_ressource_pour_null_on_obtient_son_propre_profil()
	{
		$résultat_obtenu = $this->actingAs($this->user)->call("GET", "/user/");

		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/profil_bob.json",
			$résultat_obtenu->getContent(),
		);
	}
}
