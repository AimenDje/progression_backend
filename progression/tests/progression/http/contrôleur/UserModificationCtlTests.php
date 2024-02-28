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

use Illuminate\Support\Facades\Config;
use progression\dao\DAOFactory;
use progression\domaine\entité\{Avancement, TentativeProg};
use progression\domaine\entité\user\{User, État, Rôle};
use progression\UserAuthentifiable;

final class UserModificationCtlTests extends ContrôleurTestCase
{
	public $user;
	public function setUp(): void
	{
		parent::setUp();

		$this->user = new UserAuthentifiable(
			username: "jdoe",
			date_inscription: 0,
			courriel: "jdoe@progressionmail.com",
			rôle: Rôle::NORMAL,
			état: État::ACTIF,
		);

		$this->admin = new UserAuthentifiable(
			username: "admin",
			date_inscription: 0,
			courriel: "admin@progressionmail.com",
			rôle: Rôle::ADMIN,
			état: État::ACTIF,
		);

		$this->jdoe = new User(
			username: "jdoe",
			courriel: "jdoe@progressionmail.com",
			date_inscription: 1600828609,
			préférences: '{"app": {"pref1": 1, "pref2": 2}}',
			état: État::INACTIF,
		);

		// UserDAO
		$mockUserDAO = Mockery::mock("progression\\dao\\UserDAO");
		$mockUserDAO
			->shouldReceive("get_user")
			->with("jdoe", [])
			->andReturn($this->jdoe);
		$mockUserDAO->shouldReceive("get_user")->with("roger", [])->andReturn(null);
		$mockUserDAO
			->shouldReceive("get_user")
			->with("nouveau", [])
			->andReturn(
				new User(
					username: "nouveau",
					courriel: "nouveau@progressionmail.com",
					date_inscription: 1600828609,
					état: ÉTAT::EN_ATTENTE_DE_VALIDATION,
				),
			);

		$mockExpéditeurDao = Mockery::mock("progression\\dao\\mail\\Expéditeur");

		// DAOFactory
		$mockDAOFactory = Mockery::mock("progression\\dao\\DAOFactory");
		$mockDAOFactory->shouldReceive("get_user_dao")->andReturn($mockUserDAO);
		$mockDAOFactory->shouldReceive("get_expéditeur")->andReturn($mockExpéditeurDao);
		DAOFactory::setInstance($mockDAOFactory);
	}

	// PATCH
	public function test_étant_donné_un_utilisateur_existant_lorsquon_patch_des_préférences_elles_sont_sauvegardées_et_retournée()
	{
		$préférences = '{"app": {"pref1": 3, "pref2": 4}}';
		$user_modifié = new User(username: "jdoe", date_inscription: 1600828609, préférences: $préférences);
		DAOFactory::getInstance()
			->get_user_dao()
			->shouldReceive("save")
			->once()
			->withArgs(function ($username, $user) {
				return $username == "jdoe" &&
					$user->username == "jdoe" &&
					$user->rôle == Rôle::NORMAL &&
					$user->préférences == '{"app": {"pref1": 3, "pref2": 4}}';
			})
			->andReturn([
				"jdoe" => $user_modifié,
			]);

		$résultatObtenu = $this->actingAs($this->user)->call("PATCH", "/user/jdoe", [
			"préférences" => '{"app": {"pref1": 3, "pref2": 4}}',
		]);

		$this->assertResponseStatus(200);
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/userCtlTest_user_préférences_modifiées.json",
			$résultatObtenu->getContent(),
		);
	}

	public function test_étant_donné_un_utilisateur_existant_lorsquon_patch_un_état_valide_il_est_sauvegardé_et_on_obtient_le_user_modifié()
	{
		$mockUserDAO = DAOFactory::getInstance()->get_user_dao();
		$mockUserDAO
			->shouldReceive("get_user")
			->with("jane", [])
			->andReturn(new User(username: "jane", date_inscription: 1600828609, état: État::EN_ATTENTE_DE_VALIDATION));

		$mockUserDAO
			->shouldReceive("save")
			->once()
			->withArgs(function ($username, $user) {
				return $username == "jane" && $user->username == "jane" && $user->état == État::ACTIF;
			})
			->andReturn([
				"jane" => new User(username: "jane", date_inscription: 1600828609, état: État::ACTIF),
			]);

		$résultatObtenu = $this->actingAs($this->user)->call("PATCH", "/user/jane", [
			"état" => "actif",
		]);

		$this->assertResponseStatus(200);
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/userCtlTest_user_état_modifié.json",
			$résultatObtenu->getContent(),
		);
	}

	public function test_étant_donné_un_utilisateur_existant_avec_authentification_lorsquon_le_modifie_avec_un_nouveau_mot_de_passe_il_nest_pas_sauvegardé_son_mdp_est_changé_et_on_obtient_le_même_utilisateur()
	{
		Config::set("authentification.local", true);

		$mockUserDAO = DAOFactory::getInstance()->get_user_dao();
		$mockUserDAO
			->shouldReceive("set_password")
			->with($this->jdoe, "NouveauMdP123")
			->once();

		$mockExpéditeurDao = DAOFactory::getInstance()->get_expéditeur();
		$mockExpéditeurDao->shouldNotReceive("envoyer_courriel_de_validation");

		$résultat_observé = $this->actingAs($this->user)->call("PATCH", "/user/jdoe", [
			"password" => "NouveauMdP123",
		]);

		$this->assertEquals(200, $résultat_observé->status());
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/userCréationCtlTest_user_existant_mdp_modifié.json",
			$résultat_observé->getContent(),
		);
	}

	public function test_étant_donné_un_utilisateur_existant_avec_authentification_lorsquon_le_modifie_avec_un_nouveau_mot_de_passe_insuffisant_il_nest_pas_sauvegardé_et_on_obtient_une_erreur_400()
	{
		Config::set("authentification.local", true);

		$mockUserDAO = DAOFactory::getInstance()->get_user_dao();
		$mockUserDAO->shouldNotReceive("set_password");

		$mockExpéditeurDao = DAOFactory::getInstance()->get_expéditeur();
		$mockExpéditeurDao->shouldNotReceive("envoyer_courriel_de_validation");

		$résultat_observé = $this->actingAs($this->user)->call("PATCH", "/user/jdoe", [
			"password" => "facile",
		]);

		$this->assertEquals(400, $résultat_observé->status());
		$this->assertEquals(
			'{"erreur":{"password":["Le mot de passe doit contenir au moins 8 caractères, une majuscule et un chiffre."]}}',
			$résultat_observé->getContent(),
		);
	}

	public function test_étant_donné_un_utilisateur_actif_lorsquon_le_modifie_avec_un_nouveau_courriel_un_courriel_de_validation_est_envoyé_et_on_obtient_lutilisateur_modifié_avec_état_en_attente()
	{
		Config::set("authentification.local", true);

		$mockExpéditeurDao = DAOFactory::getInstance()->get_expéditeur();
		$mockExpéditeurDao->shouldReceive("envoyer_courriel_de_validation")->once();

		$mockUserDAO = DAOFactory::getInstance()->get_user_dao();
		$mockUserDAO->shouldReceive("trouver")->with(null, "nouveau@gmail.com")->andReturn(null);

		$mockUserDAO
			->shouldReceive("save")
			->once()
			->withArgs(function ($username, $user) {
				return $username == "jdoe" &&
					$user->username == "jdoe" &&
					$user->rôle == Rôle::NORMAL &&
					$user->état == État::EN_ATTENTE_DE_VALIDATION;
			})
			->andReturn([
				"jdoe" => new User(
					username: "jdoe",
					courriel: "nouveau@gmail.com",
					date_inscription: 1600828609,
					état: État::EN_ATTENTE_DE_VALIDATION,
				),
			]);

		$résultat_observé = $this->actingAs($this->user)->call("PATCH", "/user/jdoe", [
			"username" => "jdoe",
			"courriel" => "nouveau@gmail.com",
		]);

		$this->assertEquals(200, $résultat_observé->status());
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/userCréationCtlTest_user_existant_courriel_modifié.json",
			$résultat_observé->getContent(),
		);
	}

	public function test_étant_donné_un_utilisateur_actif_lorsquon_le_modifie_avec_un_courriel_existant_on_obtient_une_erreur_409()
	{
		Config::set("authentification.local", true);

		$mockExpéditeurDao = DAOFactory::getInstance()->get_expéditeur();
		$mockExpéditeurDao->shouldNotReceive("envoyer_courriel_de_validation");

		$mockUserDAO = DAOFactory::getInstance()->get_user_dao();
		$mockUserDAO
			->shouldReceive("trouver")
			->with(null, "nouveau@gmail.com")
			->andReturn(new User(username: "Nouveau", date_inscription: 0, courriel: "nouveau@gmail.com"));

		$mockUserDAO->shouldNotReceive("save");

		$résultat_observé = $this->actingAs($this->user)->call("PATCH", "/user/jdoe", [
			"username" => "jdoe",
			"courriel" => "nouveau@gmail.com",
		]);

		$this->assertEquals(409, $résultat_observé->status());
		$this->assertEquals('{"erreur":"Le courriel est déjà utilisé."}', $résultat_observé->getContent());
	}

	public function test_étant_donné_un_utilisateur_actif_lorsquon_le_modifie_avec_son_propre_courriel_on_il_n_est_pas_sauvegardé_et_lutilisateur_reste_à_l_état_actif()
	{
		Config::set("authentification.local", true);

		$bob = new User("bob", 1600828609, "bob@progressionmail.com", état: État::ACTIF);

		$mockExpéditeurDao = DAOFactory::getInstance()->get_expéditeur();
		$mockExpéditeurDao->shouldNotReceive("envoyer_courriel_de_validation");

		$mockUserDAO = DAOFactory::getInstance()->get_user_dao();
		$mockUserDAO->shouldReceive("get_user")->with("bob", [])->andReturn($bob);
		$mockUserDAO->shouldReceive("trouver")->with(null, "bob@progressionmail.com")->andReturn($bob);

		$mockUserDAO->shouldNotReceive("save");

		$résultat_observé = $this->actingAs($this->user)->call("PATCH", "/user/bob", [
			"username" => "bob",
			"courriel" => "bob@progressionmail.com",
		]);

		$this->assertEquals(200, $résultat_observé->status());
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/userCréationCtlTest_user_existant_courriel_non_modifié.json",
			$résultat_observé->getContent(),
		);
	}

	public function test_étant_donné_un_utilisateur_existant_lorsquon_patch_des_préférences_invalides_elles_ne_sont_pas_sauvegardées_et_on_obtient_une_erreur_400()
	{
		DAOFactory::getInstance()->get_user_dao()->shouldNotReceive("save");

		$résultat_observé = $this->actingAs($this->user)->call("PATCH", "/user/jdoe", [
			"préférences" => "test",
		]);

		$this->assertResponseStatus(400);
		$this->assertEquals(
			'{"erreur":{"préférences":["Le champ préférences doit être en format json."]}}',
			$résultat_observé->getContent(),
		);
	}

	public function test_étant_donné_un_utilisateur_existant_lorsquon_patch_un_état_invalide_il_n_est_pas_sauvegardé_et_on_obtient_une_erreur_400()
	{
		DAOFactory::getInstance()->get_user_dao()->shouldNotReceive("save");

		$résultat_observé = $this->actingAs($this->user)->call("PATCH", "/user/jdoe", [
			"état" => "abc",
		]);

		$this->assertResponseStatus(400);
	}

	public function test_étant_donné_un_utilisateur_inexistant_lorsquon_patch_des_préférences_elles_ne_sont_pas_sauvegardées_et_on_obtient_une_erreur_404()
	{
		DAOFactory::getInstance()->get_user_dao()->shouldNotReceive("save");

		$this->actingAs($this->user)->call("PATCH", "/user/roger", [
			"préférences" => "{\"test\": 42}",
		]);

		$this->assertResponseStatus(404);
	}

	public function test_étant_donné_un_utilisateur_inexistant_lorsquon_patch_un_état_il_n_est_pas_sauvegardé_et_on_obtient_une_erreur_404()
	{
		DAOFactory::getInstance()->get_user_dao()->shouldNotReceive("save");

		$this->actingAs($this->user)->call("PATCH", "/user/roger", [
			"état" => "actif",
		]);

		$this->assertResponseStatus(404);
	}

	public function test_étant_donné_un_utilisateur_inactif_lorsqu_un_admin_patch_un_état_actif_on_obtient_lutilisateur_modifié()
	{
		DAOFactory::getInstance()
			->get_user_dao()
			->shouldReceive("save")
			->once()
			->withArgs(function ($username, $user) {
				return $username == "jdoe" && $user->username == "jdoe" && $user->état == État::ACTIF;
			})
			->andReturn([
				"jdoe" => new User(
					username: "jdoe",
					courriel: "jdoe@progressionmail.com",
					date_inscription: 1600828609,
					état: État::ACTIF,
				),
			]);

		$résultat_observé = $this->actingAs($this->admin)->call("PATCH", "/user/jdoe", [
			"état" => "actif",
		]);

		$this->assertResponseStatus(200);

		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/userCréationCtlTest_user_existant_état_modifié.json",
			$résultat_observé->getContent(),
		);
	}

	public function test_étant_donné_un_utilisateur_inactif_lorsqu_un_admin_patch_un_état_invalide_on_obtient_une_erreur_400()
	{
		DAOFactory::getInstance()->get_user_dao()->shouldNotReceive("save");

		$résultat_observé = $this->actingAs($this->admin)->call("PATCH", "/user/jdoe", [
			"état" => "n'importe quoi",
		]);

		$this->assertResponseStatus(400);
	}

	public function test_étant_donné_un_utilisateur_normal_lorsquon_patch_un_rôle_admin_on_obtient_une_erreur_403()
	{
		DAOFactory::getInstance()->get_user_dao()->shouldNotReceive("save");

		$this->actingAs($this->user)->call("PATCH", "/user/jdoe", [
			"rôle" => "admin",
		]);

		$this->assertResponseStatus(403);
	}

	public function test_étant_donné_un_utilisateur_normal_lorsquon_patch_un_rôle_invalide_on_obtient_une_erreur_400()
	{
		DAOFactory::getInstance()->get_user_dao()->shouldNotReceive("save");

		$this->actingAs($this->user)->call("PATCH", "/user/jdoe", [
			"rôle" => "n'importe quoi",
		]);

		$this->assertResponseStatus(400);
	}

	public function test_étant_donné_un_utilisateur_normal_lorsquon_patch_un_état_inactif_on_obtient_une_erreur_403()
	{
		DAOFactory::getInstance()->get_user_dao()->shouldNotReceive("save");

		$this->actingAs($this->user)->call("PATCH", "/user/jdoe", [
			"état" => "inactif",
		]);

		$this->assertResponseStatus(403);
	}

	public function test_étant_donné_un_utilisateur_normal_lorsquun_admin_patch_un_rôle_admin_on_obtient_un_utilisateur_modifié()
	{
		DAOFactory::getInstance()
			->get_user_dao()
			->shouldReceive("save")
			->once()
			->withArgs(function ($username, $user) {
				return $user->username == "jdoe" && $user->rôle == Rôle::ADMIN;
			})
			->andReturn([
				"jdoe" => new User(
					username: "jdoe",
					courriel: "jdoe@progressionmail.com",
					date_inscription: 1600828609,
					état: État::INACTIF,
					rôle: Rôle::ADMIN,
					préférences: '{"app": {"pref1": 1, "pref2": 2}}',
				),
			]);

		$résultat_observé = $this->actingAs($this->admin)->call("PATCH", "/user/jdoe", [
			"rôle" => "admin",
		]);

		$this->assertResponseStatus(200);
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/userCréationCtlTest_user_existant_rôle_modifié.json",
			$résultat_observé->getContent(),
		);
	}
}
