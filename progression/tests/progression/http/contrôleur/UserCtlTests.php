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
use progression\domaine\entité\{Avancement, TentativeProg};
use progression\domaine\entité\user\{User, État, Rôle};
use progression\UserAuthentifiable;

final class UserCtlTests extends ContrôleurTestCase
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

		$user = new User(
			username: "jdoe",
			date_inscription: 1600828609,
			préférences: '{"app": {"pref1": 1, "pref2": 2}}',
			état: État::INACTIF,
		);
		$user_et_avancements = new User(
			username: "jdoe",
			date_inscription: 1600828609,
			préférences: '{"app": {"pref1": 1, "pref2": 2}}',
		);
		$user_et_avancements->avancements = [
			"https://depot.com/roger/questions_prog/fonctions01/appeler_une_fonction" => new Avancement(),
			"https://depot.com/roger/questions_prog/fonctions01/appeler_une_autre_fonction" => new Avancement(),
		];
		$user_et_avancements_et_tentatives = new User(username: "jdoe", date_inscription: 1600828609);
		$user_et_avancements_et_tentatives->avancements = [
			"https://depot.com/roger/questions_prog/fonctions01/appeler_une_fonction" => new Avancement(
				tentatives: [
					new TentativeProg("python", "print('42')", 1600828610),
					new TentativeProg("java", "System.out.print(\"42\")", 1600828612),
				],
			),
			"https://depot.com/roger/questions_prog/fonctions01/appeler_une_autre_fonction" => new Avancement(
				tentatives: [
					new TentativeProg("python", "print('43')", 1600828614),
					new TentativeProg("java", "System.out.print(\"43\")", 1600828616),
				],
			),
		];

		// UserDAO
		$mockUserDAO = Mockery::mock("progression\\dao\\UserDAO");
		$mockUserDAO
			->shouldReceive("get_user")
			->with("jdoe", ["avancements"])
			->andReturn($user_et_avancements);
		$mockUserDAO
			->shouldReceive("get_user")
			->with("jdoe", ["avancements", "avancements.tentatives"])
			->andReturn($user_et_avancements_et_tentatives);
		$mockUserDAO->shouldReceive("get_user")->with("jdoe", [])->andReturn($user);
		$mockUserDAO->shouldReceive("get_user")->with("roger", [])->andReturn(null);

		// DAOFactory
		$mockDAOFactory = Mockery::mock("progression\\dao\\DAOFactory");
		$mockDAOFactory->shouldReceive("get_user_dao")->andReturn($mockUserDAO);
		DAOFactory::setInstance($mockDAOFactory);
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

	public function test_étant_donné_le_nom_dun_utilisateur_inexistant_lorsquon_appelle_get_on_obtient_une_erreur_404()
	{
		$résultatObtenu = $this->actingAs($this->user)->call("GET", "/user/roger");

		$this->assertResponseStatus(404);
	}

	public function test_étant_donné_le_nom_dun_utilisateur_sans_préférences_lorsquon_appelle_get_on_obtient_lutilisateur_avec_préférences_vides()
	{
		DAOFactory::getInstance()
			->get_user_dao()
			->shouldReceive("get_user")
			->with("monique", [])
			->andReturn(new User(username: "monique", date_inscription: 1600828609));

		$résultatObtenu = $this->actingAs($this->user)->call("GET", "/user/monique");

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

	public function test_étant_donné_le_nom_dun_utilisateur_lorsquon_appelle_get_en_incluant_les_avancements_et_tentatives_on_obtient_lutilisateur_et_ses_avancements_et_tentatives_sous_forme_json()
	{
		$résultatObtenu = $this->actingAs($this->user)->call("GET", "/user/jdoe?include=avancements.tentatives");

		$this->assertResponseStatus(200);
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/userCtlTest_user_avec_avancements_et_tentatives.json",
			$résultatObtenu->getContent(),
		);
	}
}
