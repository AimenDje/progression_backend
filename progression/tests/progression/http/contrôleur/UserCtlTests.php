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
use progression\domaine\entité\{Avancement, User};
use Illuminate\Auth\GenericUser;

final class UserCtlTests extends ContrôleurTestCase
{
	public $user;
	public function setUp(): void
	{
		parent::setUp();

		$this->user = new GenericUser(["username" => "jdoe", "rôle" => User::ROLE_NORMAL]);

		$_ENV["APP_URL"] = "https://example.com/";

		$user = new User("jdoe", préférences: '{"app": {"pref1": 1, "pref2": 2}}');
		$user_et_avancements = new User("jdoe", préférences: '{"app": {"pref1": 1, "pref2": 2}}');
		$user_et_avancements->avancements = [
			"https://depot.com/roger/questions_prog/fonctions01/appeler_une_fonction" => new Avancement(),
			"https://depot.com/roger/questions_prog/fonctions01/appeler_une_autre_fonction" => new Avancement(),
		];

		// UserDAO
		$mockUserDAO = Mockery::mock("progression\\dao\\UserDAO");
		$mockUserDAO
			->shouldReceive("get_user")
			->with("jdoe", ["avancements"])
			->andReturn($user_et_avancements);
		$mockUserDAO
			->shouldReceive("get_user")
			->with("jdoe", [])
			->andReturn($user);

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

	public function test_étant_donné_le_nom_dun_utilisateur_lorsquon_appelle_get_on_obtient_lutilisateur_et_ses_relations_sous_forme_json()
	{
		$résultatObtenu = $this->actingAs($this->user)->call("GET", "/user/jdoe");

		$this->assertResponseStatus(200);
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/userCtlTest_user.json",
			$résultatObtenu->getContent(),
		);
	}

	public function test_étant_donné_le_nom_dun_utilisateur_sans_préférences_lorsquon_appelle_get_on_obtient_lutilisateur_avec_préférences_vides()
	{
		DAOFactory::getInstance()
			->get_user_dao()
			->shouldReceive("get_user")
			->with("roger", [])
			->andReturn(new User("roger"));

		$résultatObtenu = $this->actingAs($this->user)->call("GET", "/user/roger");

		$this->assertResponseStatus(200);
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/userCtlTest_user_sans_préférences.json",
			$résultatObtenu->getContent(),
		);
	}

	public function test_étant_donné_le_nom_dun_utilisateur_lorsquon_appelle_get_en_incluant_les_avancements_on_obtient_lutilisateur_et_ses_avancements_sous_forme_json()
	{
		$résultatObtenu = $this->actingAs($this->user)->call("GET", "/user/jdoe?include=avancements");

		$this->assertResponseStatus(200);
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/userCtlTest_user_avec_avancements.json",
			$résultatObtenu->getContent(),
		);
	}

	// POST
	public function test_étant_donné_un_utilisateur_existant_lorsquon_post_des_préférences_elles_sont_sauvegardées_et_retournée()
	{
		$préférences = '{"app": {"pref1": 3, "pref2": 4}}';
		$user_modifié = new User("jdoe", 42, préférences: $préférences);
		DAOFactory::getInstance()
			->get_user_dao()
			->shouldReceive("save")
			->once()
			->withArgs(function ($user) use ($user_modifié, $préférences) {
				return $user->username == "jdoe" &&
					$user->rôle == 0 &&
					$user->préférences == '{"app": {"pref1": 3, "pref2": 4}}';
			})
			->andReturn(new User("jdoe", préférences: $préférences));

		$résultatObtenu = $this->actingAs($this->user)->call("POST", "/user/jdoe", [
			"préférences" => '{"app": {"pref1": 3, "pref2": 4}}',
		]);

		$this->assertResponseStatus(200);
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/userCtlTest_user_préférences_modifiées.json",
			$résultatObtenu->getContent(),
		);

		$résultatObtenu = $this->actingAs($this->user)->call("GET", "/user/jdoe");

		$this->assertResponseStatus(200);
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/userCtlTest_user_préférences_modifiées.json",
			$résultatObtenu->getContent(),
		);
	}
}
