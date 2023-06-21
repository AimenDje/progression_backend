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

final class ConfigCtlTests extends ContrôleurTestCase
{
	public function setUp(): void
	{
		parent::setUp();

		putenv("APP_URL=https://example.com");
	}

	// GET
	public function test_config_simple_sans_authentification()
	{
		putenv("AUTH_LOCAL=false");
		putenv("AUTH_LDAP=false");

		$résultat_observé = $this->call("GET", "/");

		$this->assertEquals(200, $résultat_observé->status());
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/config_sans_auth.json",
			$résultat_observé->getContent(),
		);
	}

	public function test_config_simple_sans_LDAP()
	{
		putenv("AUTH_LOCAL=true");
		putenv("AUTH_LDAP=false");

		$résultat_observé = $this->call("GET", "/");

		$this->assertEquals(200, $résultat_observé->status());
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/config_locale.json",
			$résultat_observé->getContent(),
		);
	}

	public function test_config_simple_avec_LDAP_et_domaine()
	{
		putenv("AUTH_LOCAL=true");
		putenv("AUTH_LDAP=true");
		putenv("LDAP_DOMAINE=exemple.com");
		putenv("LDAP_URL_MDP_REINIT=http://portail.exemple.com");

		$résultat_observé = $this->call("GET", "/");

		$this->assertEquals(200, $résultat_observé->status());
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/config_ldap.json",
			$résultat_observé->getContent(),
		);
	}
}
