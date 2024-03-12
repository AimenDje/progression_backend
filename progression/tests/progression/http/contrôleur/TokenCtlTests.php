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

use Illuminate\Support\Facades\{Config, App};
use progression\dao\DAOFactory;
use progression\http\contrôleur\GénérateurAléatoire;
use progression\domaine\entité\user\{User, Rôle, État};
use progression\UserAuthentifiable;
use Carbon\Carbon;

final class TokenCtlTests extends ContrôleurTestCase
{
	public function setUp(): void
	{
		parent::setUp();

		Carbon::setTestNowAndTimezone(Carbon::create(2001, 5, 21, 12));

		$this->user = new UserAuthentifiable(
			username: "utilisateur_lambda",
			date_inscription: 0,
			rôle: Rôle::NORMAL,
			état: État::ACTIF,
		);

		$mockUserDAO = Mockery::mock("progression\\dao\\UserDAO");
		$mockUserDAO
			->shouldReceive("get_user")
			->with("utilisateur_lambda")
			->andReturn(new User(username: "utilisateur_lambda", date_inscription: 0));

		$mockDAOFactory = Mockery::mock("progression\\dao\\DAOFactory");
		$mockDAOFactory->shouldReceive("get_user_dao")->andReturn($mockUserDAO);
		DAOFactory::setInstance($mockDAOFactory);

		$générateur = Mockery::mock("progression\\http\\contrôleur\\GénérateurAléatoire");
		$générateur
			->shouldReceive("générer_chaîne_aléatoire")
			->with(64)
			->andReturn("xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx");
		GénérateurAléatoire::set_instance($générateur);
	}

	public function test_étant_donné_un_token_qui_donne_accès_à_une_ressource_lorsquon_effectue_un_post_on_obtient_un_token_avec_les_ressources_voulues_sans_expiration()
	{
		Config::set("app.version", "1.2.3");
		Config::set("jwt.secret", "secret");

		$résultat_obtenu = $this->actingAs($this->user)->call("POST", "/user/utilisateur_lambda/tokens", [
			"data" => ["données" => "une donnée"],
			"ressources" => [
				"ressource" => ["url" => "test", "method" => "POST"],
			],
			"expiration" => 0,
		]);
		$résultat_observé = $résultat_obtenu;

		$this->assertEquals(200, $résultat_obtenu->status());
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/token_une_ressources.json",
			$résultat_observé->getContent(),
		);
	}

	public function test_étant_donné_un_token_qui_donne_accès_à_plus_dune_ressource_lorsquon_effectue_un_post_on_obtient_un_token_avec_les_ressources_voulues_sans_expiration()
	{
		Config::set("app.version", "1.2.3");
		Config::set("jwt.secret", "secret");

		$résultat_obtenu = $this->actingAs($this->user)->call("POST", "/user/utilisateur_lambda/tokens", [
			"data" => ["données" => "une donnée"],
			"ressources" => [
				"ressource" => ["url" => "test", "method" => "POST"],
				"autre_ressource" => ["url" => "autre_test", "method" => "POST"],
			],
			"expiration" => 0,
		]);
		$résultat_observé = $résultat_obtenu;

		$this->assertEquals(200, $résultat_obtenu->status());
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/token_deux_ressources.json",
			$résultat_observé->getContent(),
		);
	}

	public function test_étant_donné_un_token_qui_donne_accès_à_une_date_dexpiration_spécifique_lorsquon_effectue_un_post_on_obtient_un_token_avec_cette_expiration()
	{
		Config::set("app.version", "1.2.3");
		Config::set("jwt.secret", "secret");

		$résultat_obtenu = $this->actingAs($this->user)->call("POST", "/user/utilisateur_lambda/tokens", [
			"data" => ["données" => "une autre donnée"],
			"ressources" => [
				"ressources_test" => [
					"url" => "test",
					"method" => "POST",
				],
			],
			"expiration" => 1685831340,
		]);
		$résultat_observé = $résultat_obtenu;

		$this->assertEquals(200, $résultat_obtenu->status());
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/token_ressources_avec_expiration_spécifique.json",
			$résultat_observé->getContent(),
		);
	}

	public function test_étant_donné_un_token_qui_donne_accès_à_un_date_dexpiration_relative_de_300s_lorsquon_effectue_un_post_on_obtient_un_token_avec_expiration_plus_tard_de_300s()
	{
		Config::set("app.version", "1.2.3");
		Config::set("jwt.secret", "secret");

		$résultat_obtenu = $this->actingAs($this->user)->call("POST", "/user/utilisateur_lambda/tokens", [
			"data" => ["données" => "une autre donnée"],
			"ressources" => [
				"ressources_test" => [
					"url" => "test",
					"method" => "POST",
				],
			],
			"expiration" => "+300",
		]);
		$résultat_observé = $résultat_obtenu;

		$this->assertEquals(200, $résultat_obtenu->status());
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/token_ressources_avec_expiration_relative.json",
			$résultat_observé->getContent(),
		);
	}

	public function test_étant_donné_un_token_qui_donne_accès_à_un_date_dexpiration_invalide_lorsquon_effectue_un_post_on_obtient_une_erreur_400()
	{
		$résultat_obtenu = $this->actingAs($this->user)->call("POST", "/user/utilisateur_lambda/tokens", [
			"data" => ["données" => "une autre donnée"],
			"ressources" => [
				"ressources_test" => [
					"url" => "test",
					"method" => "POST",
				],
			],
			"expiration" => "demain",
		]);
		$résultat_observé = $résultat_obtenu;

		$this->assertEquals(400, $résultat_obtenu->status());
		$this->assertEquals(
			'{"erreur":{"expiration":["Le champ expiration doit représenter une date relative ou absolue."]}}',
			$résultat_observé->getContent(),
		);
	}

	public function test_étant_donné_un_token_sans_ressources_lorsquon_effectue_un_post_on_obtient_une_erreur_400()
	{
		$résultat_obtenu = $this->actingAs($this->user)->call("POST", "/user/utilisateur_lambda/tokens", [
			"expiration" => 0,
		]);
		$résultat_observé = $résultat_obtenu;

		$this->assertEquals(400, $résultat_obtenu->status());
		$this->assertEquals(
			'{"erreur":{"ressources":["Le champ ressources est obligatoire."]}}',
			$résultat_observé->getContent(),
		);
	}

	public function test_étant_donné_un_token_sans_expiration_lorsquon_effectue_un_post_on_obtient_une_erreur_400()
	{
		$résultat_obtenu = $this->actingAs($this->user)->call("POST", "/user/utilisateur_lambda/tokens", [
			"ressources" => [
				"test" => ["url" => "ressources", "method" => "POST"],
			],
		]);
		$résultat_observé = $résultat_obtenu;

		$this->assertEquals(400, $résultat_obtenu->status());
		$this->assertEquals(
			'{"erreur":{"expiration":["Le champ expiration est obligatoire."]}}',
			$résultat_observé->getContent(),
		);
	}

	public function test_étant_donné_un_token_sans_ressource_ni_expiration_lorsquon_effectue_un_post_on_obtient_une_erreur_400()
	{
		$résultat_obtenu = $this->actingAs($this->user)->call("POST", "/user/utilisateur_lambda/tokens", [
			"ressources" => [],
		]);
		$résultat_observé = $résultat_obtenu;

		$this->assertEquals(400, $résultat_obtenu->status());
		$this->assertEquals(
			'{"erreur":{"ressources":["Le champ ressources est obligatoire."]}}',
			$résultat_observé->getContent(),
		);
	}

	public function test_étant_donné_un_token_sans_fingerprint_lorsquon_effectue_un_post_on_obtient_un_token_sans_fingerprint()
	{
		Config::set("app.version", "1.2.3");
		Config::set("jwt.secret", "secret");

		$résultat_obtenu = $this->actingAs($this->user)->call("POST", "/user/utilisateur_lambda/tokens", [
			"data" => ["données" => "une donnée"],
			"ressources" => [
				"ressources" => ["url" => "test", "method" => "POST"],
			],
			"expiration" => 0,
			"fingerprint" => false,
		]);
		$résultat_observé = $résultat_obtenu;

		$this->assertEquals(200, $résultat_obtenu->status());
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/token_ressources.json",
			$résultat_observé->getContent(),
		);
	}

	public function test_étant_donné_un_contexte_lorsquon_génère_un_token_avec_expiration_spécifique_on_reçoit_le_hash_du_contexte_et_un_cookie_sécure_expirant_en_même_temps()
	{
		App::shouldReceive("environment")
			->with(["prod", "staging"])
			->andReturn(true);
		Config::set("app.version", "1.2.3");
		Config::set("jwt.secret", "secret");
		Config::set("app.mode", "prod");

		$résultat_obtenu = $this->actingAs($this->user)->call("POST", "user/utilisateur_lambda/tokens", [
			"data" => ["données" => "une autre donnée"],
			"ressources" => [
				"ressources_test" => [
					"url" => "test",
					"method" => "POST",
				],
			],
			"fingerprint" => true,
			"expiration" => 1685831340,
		]);
		$résultat_observé = $résultat_obtenu;

		$this->assertEquals(200, $résultat_obtenu->status());
		$this->assertEquals(
			"xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
			$résultat_obtenu->headers->getCookies()[0]->getValue(),
		);
		$this->assertEquals("contexte_token", $résultat_obtenu->headers->getCookies()[0]->getName());
		$this->assertEquals(1685831340, $résultat_obtenu->headers->getCookies()[0]->getExpiresTime());
		$this->assertTrue($résultat_obtenu->headers->getCookies()[0]->isSecure());
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/token_avec_contexte_expiration_spécifique.json",
			$résultat_observé->getContent(),
		);
	}

	public function test_étant_donné_un_contexte_lorsquon_génère_un_token_avec_expiration_relatif_on_reçoit_le_hash_du_contexte_et_un_cookie_sécure_expirant_en_même_temps()
	{
		Config::set("app.version", "1.2.3");
		Config::set("jwt.secret", "secret");

		$résultat_obtenu = $this->actingAs($this->user)->call("POST", "user/utilisateur_lambda/tokens", [
			"data" => ["données" => "une autre donnée"],
			"ressources" => [
				"ressources_test" => [
					"url" => "test",
					"method" => "POST",
				],
			],
			"fingerprint" => true,
			"expiration" => "+300",
		]);
		$résultat_observé = $résultat_obtenu;

		$this->assertEquals(200, $résultat_obtenu->status());

		$this->assertEquals(
			"xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
			$résultat_obtenu->headers->getCookies()[0]->getValue(),
		);
		$this->assertEquals(990446700, $résultat_obtenu->headers->getCookies()[0]->getExpiresTime());
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/token_avec_contexte_expiration_relative.json",
			$résultat_observé->getContent(),
		);
	}
}
