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

namespace progression\http\transformer;

use progression\domaine\entité\Avancement;
use progression\domaine\entité\user\{User, État, Rôle};
use progression\domaine\entité\clé\{Clé, Portée};
use progression\http\transformer\dto\UserDTO;
use PHPUnit\Framework\TestCase;
use Illuminate\Support\Facades\Gate;

final class UserTransformerTests extends TestCase
{
	public function test_étant_donné_un_user_instancié_lorsquon_récupère_son_transformer_on_obtient_un_objet_json_correspondant()
	{
		$user = new User(
			username: "bob",
			date_inscription: 1590828610,
			courriel: "bob@testmail.com",
			état: État::ACTIF,
			rôle: Rôle::NORMAL,
			préférences: "les rouges",
		);

		$résultat = [
			"id" => "bob",
			"courriel" => "bob@testmail.com",
			"username" => "bob",
			"état" => "actif",
			"rôle" => "normal",
			"date_inscription" => 1590828610,
			"préférences" => "les rouges",
			"links" => [
				"self" => "https://example.com/user/bob",
			],
		];

		$userTransformer = new UserTransformer();
		$this->assertEquals(
			$résultat,
			$userTransformer->transform(
				new UserDTO(id: "bob", objet: $user, liens: ["self" => "https://example.com/user/bob"]),
			),
		);
	}

	public function test_étant_donné_un_user_avec_ses_clés_lorsquon_inclut_les_clés_on_reçoit_un_tableau_de_clés()
	{
		$user = new User(username: "bob", date_inscription: 0);
		$user->clés = [
			new Clé(null, 1624593600, 1624680000, Portée::AUTH),
			new Clé(null, 1624593602, 1624680002, Portée::AUTH),
		];

		$userTransformer = new UserTransformer();
		$résultats_obtenus = $userTransformer->includeCles(new UserDTO(id: "bob", objet: $user, liens: []));

		$clés = [];
		foreach ($résultats_obtenus->getData() as $résultat) {
			$clés[] = $résultat;
		}
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/userTransformerTest_inclusion_clés.json",
			json_encode($clés),
		);
	}

	public function test_étant_donné_un_user_avec_des_avancement_lorsquon_inclut_les_avancement_on_reçoit_un_tableau_davancements()
	{
		$user = new User(username: "bob", date_inscription: 0);
		$user->clés = [
			new Clé(null, 1624593600, 1624680000, Portée::AUTH),
			new Clé(null, 1624593602, 1624680002, Portée::AUTH),
		];
		$user->avancements = [new Avancement(titre: "test 1"), new Avancement(titre: "test 2")];

		Gate::shouldReceive("allows")->with("soumettre-tentative", "bob")->andReturn(true);

		$userTransformer = new UserTransformer();
		$résultats_obtenus = $userTransformer->includeAvancements(new UserDTO(id: "bob", objet: $user, liens: []));

		$clés = [];
		foreach ($résultats_obtenus->getData() as $résultat) {
			$clés[] = $résultat;
		}
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/userTransformerTest_inclusion_avancements.json",
			json_encode($clés),
		);
	}
}
