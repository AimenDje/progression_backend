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

use progression\ContrôleurTestCase;

use progression\dao\DAOFactory;
use progression\domaine\entité\clé\{Clé, Portée};
use progression\domaine\entité\user\{User, Rôle, État};
use progression\UserAuthentifiable;

final class CléCtlV3Tests extends ContrôleurTestCase
{
	public $user;

	public function setUp(): void
	{
		parent::setUp();

		$this->user = new UserAuthentifiable(
			username: "jdoe",
			date_inscription: 0,
			rôle: Rôle::NORMAL,
			état: État::ACTIF,
		);
		$this->admin = new UserAuthentifiable(
			username: "admin",
			date_inscription: 0,
			rôle: Rôle::ADMIN,
			état: État::ACTIF,
		);

		// UserDAO
		$mockUserDAO = Mockery::mock("progression\\dao\\UserDAO");
		$mockUserDAO
			->shouldReceive("get_user")
			->with("jdoe")
			->andReturn(new User(username: "jdoe", date_inscription: 0));
		$mockUserDAO->shouldReceive("get_user")->with("bob")->andReturn(new User(username: "bob", date_inscription: 0));

		//CléDAO
		$mockCléDAO = Mockery::mock("progression\\dao\\CléDAO");
		$mockCléDAO
			->shouldReceive("get_clé")
			->with("jdoe", "cle de test", [])
			->andReturn(new Clé(1234, 1625709495, 1625713000, Portée::AUTH));
		$mockCléDAO->shouldReceive("get_clé")->with("jdoe", "cle inexistante", [])->andReturn(null);
		$mockCléDAO->shouldReceive("get_clé")->with("jdoe", "nouvelle_cle")->andReturn(null);
		$mockCléDAO
			->shouldReceive("save")
			->withArgs(["jdoe", "nouvelle_cle", Mockery::Any()])
			->andReturnUsing(function ($u, $n, $o) {
				return ["nouvelle_cle" => $o];
			});

		// DAOFactory
		$mockDAOFactory = Mockery::mock("progression\\dao\\DAOFactory");
		$mockDAOFactory->shouldReceive("get_clé_dao")->andReturn($mockCléDAO);
		$mockDAOFactory->shouldReceive("get_user_dao")->andReturn($mockUserDAO);
		DAOFactory::setInstance($mockDAOFactory);
	}

	// POST
	public function test_étant_donné_un_utilisateur_normal_connecté_lorsquil_requiert_une_clé_dauthentification_on_obtient_une_clé_avec_un_secret_généré_aléatoirement_sans_expiration()
	{
		$résultat_observé = $this->actingAs($this->user)->call("POST", "/user/jdoe/cles", [
			"nom" => "nouvelle_cle",
		]);

		$this->assertEquals(200, $résultat_observé->status());
		$clé_sauvegardée = json_decode($résultat_observé->getContent())->data->attributes;

		$this->assertNotNull($clé_sauvegardée->secret);
		$this->assertEquals(0, $clé_sauvegardée->expiration);
		$this->assertEquals("auth", $clé_sauvegardée->portée);
	}

	public function test_étant_donné_un_utilisateur_normal_connecté_lorsquil_requiert_une_clé_dauthentification_avec_expiration_0_on_obtient_une_clé_avec_un_secret_généré_aléatoirement_sans_expiration()
	{
		$résultat_observé = $this->actingAs($this->user)->call("POST", "/user/jdoe/cles", [
			"nom" => "nouvelle_cle",
			"expiration" => 0,
		]);

		$this->assertEquals(200, $résultat_observé->status());
		$clé_sauvegardée = json_decode($résultat_observé->getContent())->data->attributes;

		$this->assertNotNull($clé_sauvegardée->secret);
		$this->assertEquals(0, $clé_sauvegardée->expiration);
		$this->assertEquals("auth", $clé_sauvegardée->portée);
	}

	public function test_étant_donné_un_utilisateur_normal_connecté_lorsquil_requiert_une_clé_dauthentification_avec_expiration_on_obtient_une_clé_avec_un_secret_généré_aléatoirement_avec_expiration()
	{
		$expiration = time() + 100;
		$résultat_observé = $this->actingAs($this->user)->call("POST", "/user/jdoe/cles", [
			"nom" => "nouvelle_cle",
			"expiration" => $expiration,
		]);

		$this->assertEquals(200, $résultat_observé->status());
		$clé_sauvegardée = json_decode($résultat_observé->getContent())->data->attributes;

		$this->assertNotNull($clé_sauvegardée->secret);
		$this->assertEquals($expiration, $clé_sauvegardée->expiration);
		$this->assertEquals("auth", $clé_sauvegardée->portée);
	}

	public function test_étant_donné_un_utilisateur_normal_connecté_lorsquil_requiert_une_clé_dauthentification_avec_expiration_passée_on_obtient_une_erreur_400()
	{
		$expiration = time() - 100;
		$résultat_observé = $this->actingAs($this->user)->call("POST", "/user/jdoe/cles", [
			"nom" => "nouvelle_cle",
			"expiration" => $expiration,
		]);

		$this->assertEquals(400, $résultat_observé->status());
		$this->assertEquals(
			'{"erreur":{"expiration":["Expiration ne peut être dans le passé."]}}',
			$résultat_observé->getContent(),
		);
	}

	public function test_étant_donné_un_utilisateur_normal_connecté_lorsquil_requiert_une_clé_dauthentification_avec_expiration_non_entière_on_obtient_une_erreur_400()
	{
		$expiration = time() + 100.5;
		$résultat_observé = $this->actingAs($this->user)->call("POST", "/user/jdoe/cles", [
			"nom" => "nouvelle_cle",
			"expiration" => $expiration,
		]);

		$this->assertEquals(400, $résultat_observé->status());
		$this->assertEquals(
			'{"erreur":{"expiration":["Expiration doit être un entier."]}}',
			$résultat_observé->getContent(),
		);
	}

	public function test_étant_donné_un_utilisateur_normal_connecté_lorsquil_requiert_une_clé_dauthentification_avec_expiration_non_numérique_on_obtient_une_erreur_400()
	{
		$expiration = "patate";
		$résultat_observé = $this->actingAs($this->user)->call("POST", "/user/jdoe/cles", [
			"nom" => "nouvelle_cle",
			"expiration" => $expiration,
		]);

		$this->assertEquals(400, $résultat_observé->status());
		$this->assertEquals(
			'{"erreur":{"expiration":["Expiration doit être un nombre."]}}',
			$résultat_observé->getContent(),
		);
	}
}
