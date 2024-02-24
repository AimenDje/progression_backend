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
use progression\domaine\entité\user\{État, Rôle};
use progression\UserAuthentifiable;
use Illuminate\Support\Facades\Config;

final class ConfigCtlTests extends ContrôleurTestCase
{
	public function setUp(): void
	{
		parent::setUp();

		Config::set("version.numéro", "3.0.0");
	}

	// GET
	// Utilisateur sans authentification
	public function test_config_simple_sans_authentification()
	{
		putenv("AUTH_LOCAL=false");
		putenv("AUTH_LDAP=false");

		$résultat_observé = $this->call("GET", "/");

		$this->assertEquals(200, $résultat_observé->status());
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/config_sans_auth_anonyme.json",
			$résultat_observé->getContent(),
		);
	}

	// Utilisateur avec authentification locale
	public function test_config_simple_avec_authentification_locale_utilisateur_anonyme()
	{
		putenv("AUTH_LOCAL=true");
		putenv("AUTH_LDAP=false");

		$résultat_observé = $this->call("GET", "/");

		$this->assertEquals(200, $résultat_observé->status());
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/config_locale_anonyme.json",
			$résultat_observé->getContent(),
		);
	}

	public function test_config_simple_avec_authentification_locale_utilisateur_authentifié()
	{
		putenv("AUTH_LOCAL=true");
		putenv("AUTH_LDAP=false");

		$user = new UserAuthentifiable(username: "jdoe", date_inscription: 0, rôle: Rôle::NORMAL, état: État::ACTIF);

		$résultat_observé = $this->actingAs($user)->call("GET", "/");

		$this->assertEquals(200, $résultat_observé->status());
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/config_locale_authentifié.json",
			$résultat_observé->getContent(),
		);
	}

	public function test_config_simple_avec_authentification_locale_utilisateur_inactif()
	{
		putenv("AUTH_LOCAL=true");
		putenv("AUTH_LDAP=false");

		$user = new UserAuthentifiable(username: "jdoe", date_inscription: 0, rôle: Rôle::NORMAL, état: État::INACTIF);

		$résultat_observé = $this->actingAs($user)->call("GET", "/");

		$this->assertEquals(401, $résultat_observé->status());
	}

	# Utilisateur LDAP
	public function test_config_simple_avec_LDAP_utilisateur_anonyme()
	{
		putenv("AUTH_LOCAL=false");
		putenv("AUTH_LDAP=true");
		putenv("LDAP_DOMAINE=exemple.com");
		putenv("LDAP_URL_MDP_REINIT=http://portail.exemple.com");

		$résultat_observé = $this->call("GET", "/");

		$this->assertEquals(200, $résultat_observé->status());
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/config_ldap_anonyme.json",
			$résultat_observé->getContent(),
		);
	}

	public function test_config_simple_avec_authentification_locale_et_LDAP_utilisateur_anonyme()
	{
		putenv("AUTH_LOCAL=true");
		putenv("AUTH_LDAP=true");
		putenv("LDAP_DOMAINE=exemple.com");
		putenv("LDAP_URL_MDP_REINIT=http://portail.exemple.com");

		$résultat_observé = $this->call("GET", "/");

		$this->assertEquals(200, $résultat_observé->status());
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/config_locale_et_ldap_anonyme.json",
			$résultat_observé->getContent(),
		);
	}

	public function test_config_simple_avec_LDAP_utilisateur_authentifié()
	{
		putenv("AUTH_LOCAL=false");
		putenv("AUTH_LDAP=true");
		putenv("LDAP_DOMAINE=exemple.com");
		putenv("LDAP_URL_MDP_REINIT=http://portail.exemple.com");

		$user = new UserAuthentifiable(username: "jdoe", date_inscription: 0, rôle: Rôle::NORMAL, état: État::ACTIF);

		$résultat_observé = $this->actingAs($user)->call("GET", "/");

		$this->assertEquals(200, $résultat_observé->status());
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/config_ldap_authentifié.json",
			$résultat_observé->getContent(),
		);
	}
}
