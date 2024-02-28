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

use progression\domaine\entité\user\{User, Rôle, État};
use progression\dao\DAOFactory;
use progression\http\contrôleur\GénérateurDeToken;
use progression\UserAuthentifiable;

final class PermissionsRessourcesTests extends TestCase
{
	public $user;
	public $headers;

	public function setup(): void
	{
		parent::setUp();

		$this->user = new UserAuthentifiable(
			username: "bob",
			date_inscription: 0,
			rôle: Rôle::NORMAL,
			état: État::ACTIF,
		);
		$token = GénérateurDeToken::get_instance()->générer_token("bob");
		$this->headers = ["HTTP_Authorization" => "Bearer " . $token];

		// UserDAO
		$mockUserDAO = Mockery::mock("progression\\dao\\UserDAO");
		$mockUserDAO
			->allows()
			->get_user("bob", [])
			->andReturn(new User(username: "bob", date_inscription: 1590828610, état: État::ACTIF));
		$mockUserDAO
			->allows()
			->get_user("Bob", [])
			->andReturn(new User(username: "bob", date_inscription: 1590828610, état: État::ACTIF));
		$mockUserDAO
			->allows()
			->get_user("jdoe", [])
			->andReturn(new User(username: "jdoe", date_inscription: 1590828610, état: État::ACTIF));

		// DAOFactory
		$mockDAOFactory = Mockery::mock("progression\\dao\\DAOFactory");
		$mockDAOFactory->shouldReceive("get_user_dao")->andReturn($mockUserDAO);
		DAOFactory::setInstance($mockDAOFactory);
	}

	public function test_étant_donné_un_utilisateur_normal_bob_connecté_lorsquon_demande_une_ressource_pour_ce_même_utilisateur_on_obtient_OK()
	{
		$résultat_obtenu = $this->actingAs($this->user)->call("GET", "/user/bob", [], [], [], $this->headers);

		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/profil_bob.json",
			$résultat_obtenu->getContent(),
		);
	}

	public function test_étant_donné_un_utilisateur_normal_bob_connecté_lorsquon_demande_une_ressource_pour_ce_même_utilisateur_avec_une_casse_différente_on_obtient_OK()
	{
		$résultat_obtenu = $this->actingAs($this->user)->call("GET", "/user/Bob", [], [], [], $this->headers);

		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/profil_bob.json",
			$résultat_obtenu->getContent(),
		);
	}

	public function test_étant_donné_un_utilisateur_normal_bob_connecté_lorsquon_demande_une_ressource_pour_l_utilisateur_jdoe_on_obtient_erreur_403()
	{
		$résultat_obtenu = $this->actingAs($this->user)->call("GET", "/user/jdoe", [], [], [], $this->headers);

		$this->assertEquals(403, $résultat_obtenu->status());
		$this->assertEquals('{"erreur":"Opération interdite."}', $résultat_obtenu->getContent());
	}

	public function test_étant_donné_un_utilisateur_admin_connecté_lorsquon_demande_une_ressource_pour_l_utilisateur_existant_bob_on_obtient_son_profil()
	{
		$admin = new UserAuthentifiable(username: "admin", date_inscription: 0, rôle: Rôle::ADMIN, état: État::ACTIF);
		$résultat_obtenu = $this->actingAs($admin)->call("GET", "/user/bob");

		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/profil_bob.json",
			$résultat_obtenu->getContent(),
		);
	}
}
