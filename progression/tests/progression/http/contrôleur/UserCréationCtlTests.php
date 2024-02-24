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

use progression\http\contrôleur\GénérateurDeToken;
use progression\domaine\entité\user\{User, État, Rôle};
use progression\dao\DAOFactory;
use progression\UserAuthentifiable;
use Carbon\Carbon;

final class UserCréationCtlTests extends ContrôleurTestCase
{
	public $user;

	public function setUp(): void
	{
		parent::setUp();

		$this->user = new UserAuthentifiable(
			username: "bob",
			date_inscription: 0,
			rôle: Rôle::NORMAL,
			état: État::ACTIF,
		);

		// UserDAO
		$mockUserDAO = Mockery::mock("progression\\dao\\UserDAO");
		$mockUserDAO
			->shouldReceive("get_user")
			->with("bob")
			->andReturn(
				new User(
					username: "bob",
					date_inscription: 1590828610,
					courriel: "bob@progressionmail.com",
					état: État::ACTIF,
				),
			);
		$mockUserDAO
			->shouldReceive("get_user")
			->with("nouveau")
			->andReturn(
				new User(
					username: "nouveau",
					date_inscription: 1610828612,
					courriel: "nouveau@mail.com",
					état: État::EN_ATTENTE_DE_VALIDATION,
				),
			);
		$mockUserDAO->shouldReceive("get_user")->with("Marcel")->andReturn(null);
		$mockUserDAO->shouldReceive("get_user")->with("Marcel2")->andReturn(null);
		$mockUserDAO->shouldReceive("get_user")->with("johnny")->andReturn(null);

		// DAOFactory
		$mockDAOFactory = Mockery::mock("progression\\dao\\DAOFactory");
		$mockDAOFactory->shouldReceive("get_user_dao")->andReturn($mockUserDAO);
		DAOFactory::setInstance($mockDAOFactory);

		$mockExpéditeurDao = Mockery::mock("progression\\dao\\mail\Expéditeur");
		$mockDAOFactory->allows()->get_expéditeur()->andReturn($mockExpéditeurDao);
	}

	#  AUTH_LOCAL = false
	public function test_étant_donné_un_utilisateur_existant_sans_authentification_lorsquon_inscrit_de_nouveau_on_obtient_un_user_avec_la_date_dinscription_originale()
	{
		putenv("AUTH_LOCAL=false");

		$résultat_observé = $this->call("PUT", "/user/bob", [
			"username" => "bob",
		]);

		$mockExpéditeurDao = DAOFactory::getInstance()->get_expéditeur();
		$mockExpéditeurDao->shouldNotReceive("envoyer_courriel_de_validation");

		$this->assertEquals(200, $résultat_observé->status());
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/userCréationCtlTest_user_existant_sans_auth.json",
			$résultat_observé->getContent(),
		);
	}

	public function test_étant_donné_un_utilisateur_existant_sans_authentification_lorsquon_inscrit_avec_un_nom_dutilisateur_différent_on_obtient_une_erreur_400()
	{
		putenv("AUTH_LOCAL=false");

		$résultat_observé = $this->call("PUT", "/user/bob", [
			"username" => "autre_nom",
		]);

		$mockExpéditeurDao = DAOFactory::getInstance()->get_expéditeur();
		$mockExpéditeurDao->shouldNotReceive("envoyer_courriel_de_validation");

		$this->assertEquals(400, $résultat_observé->status());
	}

	public function test_étant_donné_un_utilisateur_inexistant_sans_authentification_lorsquon_inscrit_avec_un_nom_dutilisateur_différent_on_obtient_une_erreur_400()
	{
		putenv("AUTH_LOCAL=false");

		$résultat_observé = $this->call("PUT", "/user/roger", [
			"username" => "autre_nom",
		]);

		$mockExpéditeurDao = DAOFactory::getInstance()->get_expéditeur();
		$mockExpéditeurDao->shouldNotReceive("envoyer_courriel_de_validation");

		$this->assertEquals(400, $résultat_observé->status());
	}

	public function test_étant_donné_un_utilisateur_inexistant_sans_authentification_lorsquon_linscrit_il_est_sauvegardé_et_on_obtient_un_user()
	{
		putenv("AUTH_LOCAL=false");
		putenv("PREFERENCES_DEFAUT={préférences par défaut}");

		Carbon::setTestNow(Carbon::create(2021, 01, 16, 15, 23, 32));

		$mockUserDAO = DAOFactory::getInstance()->get_user_dao();
		$mockUserDAO
			->shouldReceive("save")
			->once()
			->withArgs(function ($username, $user) {
				return $username == "Marcel" && $user->username == "Marcel";
			})
			->andReturnUsing(function ($username, $user) {
				return [$username => $user];
			});

		$mockExpéditeurDao = DAOFactory::getInstance()->get_expéditeur();
		$mockExpéditeurDao->shouldNotReceive("envoyer_courriel_de_validation");

		$résultat_observé = $this->call("PUT", "/user/Marcel", [
			"username" => "Marcel",
		]);

		$this->assertEquals(200, $résultat_observé->status());
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/userCréationCtlTest_user_inexistant_sans_auth.json",
			$résultat_observé->getContent(),
		);
	}

	# AUTH_LOCAL = true
	# PUT Utilisateur inexistant
	public function test_étant_donné_un_utilisateur_inexistant_avec_authentification_lorsquon_linscrit_avec_un_courriel_existant_on_obtient_une_erreur_409()
	{
		putenv("AUTH_LOCAL=true");

		$mockExpéditeurDao = DAOFactory::getInstance()->get_expéditeur();
		$mockExpéditeurDao->shouldNotReceive("envoyer_courriel_de_validation");

		$mockUserDAO = DAOFactory::getInstance()->get_user_dao();
		$mockUserDAO
			->shouldReceive("trouver")
			->with(Mockery::any(), "jane@gmail.com")
			->andReturn(new User(username: "jane", date_inscription: 0, courriel: "jane@gmail.com"));

		$résultat_observé = $this->call("PUT", "/user/johnny", [
			"username" => "johnny",
			"courriel" => "jane@gmail.com",
			"password" => "Test1234",
		]);

		$this->assertEquals(409, $résultat_observé->status());
		$this->assertEquals('{"erreur":"Le courriel est déjà utilisé."}', $résultat_observé->getContent());
	}

	public function test_étant_donné_un_utilisateur_inexistant_avec_authentification_lorsquon_linscrit_avec_un_courriel_invalide_on_obtient_une_erreur_400()
	{
		putenv("AUTH_LOCAL=true");

		$mockExpéditeurDao = DAOFactory::getInstance()->get_expéditeur();
		$mockExpéditeurDao->shouldNotReceive("envoyer_courriel_de_validation");

		$résultat_observé = $this->call("PUT", "/user/bobby", [
			"username" => "bobby",
			"courriel" => "bobby.com",
			"password" => "Test1234",
		]);

		$this->assertEquals(400, $résultat_observé->status());
		$this->assertEquals(
			'{"erreur":{"courriel":["Le champ courriel doit être un courriel valide."]}}',
			$résultat_observé->getContent(),
		);
	}

	public function test_étant_donné_un_utilisateur_inexistant_avec_authentification_lorsquon_linscrit_avec_un_password_invalide_on_obtient_une_erreur_400()
	{
		putenv("AUTH_LOCAL=true");

		$mockExpéditeurDao = DAOFactory::getInstance()->get_expéditeur();
		$mockExpéditeurDao->shouldNotReceive("envoyer_courriel_de_validation");

		$résultat_observé = $this->call("PUT", "/user/bobby", [
			"username" => "bobby",
			"courriel" => "bobby@gmail.com",
			"password" => "pasbon",
		]);

		$this->assertEquals(400, $résultat_observé->status());
		$this->assertEquals(
			'{"erreur":{"password":["Le mot de passe doit contenir au moins 8 caractères, une majuscule et un chiffre."]}}',
			$résultat_observé->getContent(),
		);
	}

	public function test_étant_donné_un_utilisateur_inexistant_avec_authentification_lorsquon_linscrit_il_est_sauvegardé_et_on_obtient_un_user()
	{
		putenv("AUTH_LOCAL=true");
		putenv("PREFERENCES_DEFAUT={préférences par défaut}");

		Carbon::setTestNow(Carbon::create(2021, 01, 16, 15, 23, 32));

		$mockUserDAO = DAOFactory::getInstance()->get_user_dao();
		$mockUserDAO->shouldReceive("trouver")->with(Mockery::any(), "marcel2@gmail.com")->andReturn(null);
		$mockUserDAO
			->shouldReceive("save")
			->once()
			->withArgs(function ($username, $user) {
				return $username == "Marcel2" && $user->username == "Marcel2";
			})
			->andReturnUsing(function ($username, $user) {
				return [$username => $user];
			})
			->shouldReceive("set_password")
			->once()
			->withArgs(function ($user, $password) {
				return $user->username == "Marcel2" && $password == "Test1234";
			});

		$mockExpéditeurDao = DAOFactory::getInstance()->get_expéditeur();
		$mockExpéditeurDao->shouldReceive("envoyer_courriel_de_validation")->once();

		$résultat_observé = $this->call("POST", "/users", [
			"username" => "Marcel2",
			"courriel" => "marcel2@gmail.com",
			"password" => "Test1234",
		]);

		$this->assertEquals(200, $résultat_observé->status());
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/userCréationCtlTest_user_inexistant_avec_auth.json",
			$résultat_observé->getContent(),
		);
	}

	public function test_étant_donné_un_utilisateur_inexistant_avec_authentification_lorsquon_linscrit_sans_username_il_n_est_pas_sauvegardé_et_on_obtient_une_erreur_400()
	{
		putenv("AUTH_LOCAL=true");

		Carbon::setTestNow(Carbon::create(2021, 01, 16, 15, 23, 32));

		$mockUserDAO = DAOFactory::getInstance()->get_user_dao();
		$mockUserDAO->shouldNotReceive("save");

		$mockExpéditeurDao = DAOFactory::getInstance()->get_expéditeur();
		$mockExpéditeurDao->shouldNotReceive("envoyer_courriel_de_validation");

		$résultat_observé = $this->call("POST", "/users", [
			"courriel" => "marcel2@gmail.com",
			"password" => "Test1234",
		]);

		$this->assertEquals(400, $résultat_observé->status());
		$this->assertEquals(
			'{"erreur":{"username":["Le champ username est obligatoire."]}}',
			$résultat_observé->getContent(),
		);
	}

	# PUT Utilisateur existant
	public function test_étant_donné_un_utilisateur_en_attente_de_validation_lorsquon_linscrit_de_nouveau_avec_le_même_courriel_et_sans_mdp_il_nest_pas_sauvegardé_et_un_courriel_de_validation_est_renvoyé()
	{
		putenv("AUTH_LOCAL=true");

		$mockUserDAO = DAOFactory::getInstance()->get_user_dao();
		$mockUserDAO->shouldNotReceive("save")->shouldNotReceive("set_password");

		$mockExpéditeurDao = DAOFactory::getInstance()->get_expéditeur();
		$mockExpéditeurDao->shouldReceive("envoyer_courriel_de_validation")->once();

		$résultat_observé = $this->actingAs($this->user)->call("PUT", "/user/nouveau", [
			"username" => "nouveau",
			"courriel" => "nouveau@progressionmail.com",
		]);

		$this->assertEquals(200, $résultat_observé->status());
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/userCréationCtlTest_renvoi_courriel.json",
			$résultat_observé->getContent(),
		);
	}

	public function test_étant_donné_un_utilisateur_existant_avec_authentification_lorsquon_linscrit_de_nouveau_on_obtient_une_erreur_409()
	{
		putenv("AUTH_LOCAL=true");

		$mockExpéditeurDao = DAOFactory::getInstance()->get_expéditeur();
		$mockExpéditeurDao->shouldNotReceive("envoyer_courriel_de_validation");

		$résultat_observé = $this->call("PUT", "/user/bob", [
			"username" => "bob",
			"courriel" => "bob@progressionmail.com",
			"password" => "Test1234",
		]);

		$this->assertEquals(409, $résultat_observé->status());
		$this->assertEquals('{"erreur":"Un utilisateur du même nom existe déjà."}', $résultat_observé->getContent());
	}

	public function test_étant_donné_un_utilisateur_existant_lorsquon_linscrit_de_nouveau_sans_mdp_il_nest_pas_sauvegardé_et_on_obtient_une_erreur_400()
	{
		putenv("AUTH_LOCAL=true");

		$mockUserDAO = DAOFactory::getInstance()->get_user_dao();
		$mockUserDAO
			->shouldReceive("trouver")
			->with("bob", Mockery::any())
			->andReturn(new User(username: "bob", date_inscription: 0, courriel: "bob@gmail.com"));
		$mockUserDAO->shouldNotReceive("save")->shouldNotReceive("set_password");

		$mockExpéditeurDao = DAOFactory::getInstance()->get_expéditeur();
		$mockExpéditeurDao->shouldNotReceive("envoyer_courriel_de_validation");

		$résultat_observé = $this->call("PUT", "/user/bob", [
			"username" => "bob",
			"courriel" => "bob@gmail.com",
		]);

		$this->assertEquals(400, $résultat_observé->status());
		$this->assertEquals(
			'{"erreur":{"password":["Le champ password est obligatoire."]}}',
			$résultat_observé->getContent(),
		);
	}

	public function test_étant_donné_un_utilisateur_existant_linscrit_de_nouveau_sans_courriel_il_nest_pas_sauvegradé_et_on_obtient_une_erreur_400()
	{
		putenv("AUTH_LOCAL=true");

		$mockUserDAO = DAOFactory::getInstance()->get_user_dao();
		$mockUserDAO
			->shouldReceive("trouver")
			->with("bob", Mockery::any())
			->andReturn(new User(username: "bob", date_inscription: 0, courriel: "bob@gmail.com"));
		$mockUserDAO->shouldNotReceive("save")->shouldNotReceive("set_password");

		$mockExpéditeurDao = DAOFactory::getInstance()->get_expéditeur();
		$mockExpéditeurDao->shouldNotReceive("envoyer_courriel_de_validation");

		$résultat_observé = $this->call("PUT", "/user/bob", [
			"username" => "bob",
			"password" => "Test1234",
		]);

		$this->assertEquals(400, $résultat_observé->status());
		$this->assertEquals(
			'{"erreur":{"courriel":["Le champ courriel est obligatoire."]}}',
			$résultat_observé->getContent(),
		);
	}

	# Identifiants invalides
	public function test_étant_donné_un_utilisateur_inexistant_avec_authentification_lorsquon_linscrit_avec_un_username_différent_on_obtient_une_erreur_400()
	{
		putenv("AUTH_LOCAL=true");

		$mockExpéditeurDao = DAOFactory::getInstance()->get_expéditeur();
		$mockExpéditeurDao->shouldNotReceive("envoyer_courriel_de_validation");

		$résultat_observé = $this->call("PUT", "/user/zozo", [
			"username" => "autre_nom",
			"courriel" => "zozo@gmail.com",
			"password" => "Test1234",
		]);

		$this->assertEquals(400, $résultat_observé->status());
		$this->assertEquals(
			'{"erreur":{"username":["Le nom d\'utilisateur diffère de username."]}}',
			$résultat_observé->getContent(),
		);
	}

	public function test_étant_donné_une_authentificaton_locale_lorsquon_inscrit_un_nom_dutilisateur_invalide_on_obtient_une_erreur_400()
	{
		putenv("AUTH_LOCAL=true");

		$mockExpéditeurDao = DAOFactory::getInstance()->get_expéditeur();
		$mockExpéditeurDao->shouldNotReceive("envoyer_courriel_de_validation");

		$résultat_observé = $this->call("PUT", "/user/B@B", [
			"username" => "B@B",
			"courriel" => "test@progressionmail.com",
			"password" => "Test01234",
		]);

		$this->assertEquals(400, $résultat_observé->status());
		$this->assertEquals(
			'{"erreur":{"username":["Le nom d\'utilisateur doit être composé de 2 à 64 caractères alphanumériques."]}}',
			$résultat_observé->getContent(),
		);
	}

	public function test_étant_donné_une_authentificaton_locale_lorsquon_inscrit_sans_nom_dutlisateur_on_obtient_une_erreur_400()
	{
		putenv("AUTH_LOCAL=true");

		$mockExpéditeurDao = DAOFactory::getInstance()->get_expéditeur();
		$mockExpéditeurDao->shouldNotReceive("envoyer_courriel_de_validation");

		$résultat_observé = $this->call("PUT", "/user/Marcel", [
			"courriel" => "test@progressionmail.com",
			"password" => "Test01234",
		]);

		$this->assertEquals(400, $résultat_observé->status());
		$this->assertEquals(
			'{"erreur":{"username":["Le champ username est obligatoire."]}}',
			$résultat_observé->getContent(),
		);
	}

	public function test_étant_donné_une_authentification_locale_lorsquon_inscrit_un_nouvel_utilisateur_sans_mot_de_passe_on_obtient_une_erreur_400()
	{
		putenv("AUTH_LOCAL=true");

		$mockExpéditeurDao = DAOFactory::getInstance()->get_expéditeur();
		$mockExpéditeurDao->shouldNotReceive("envoyer_courriel_de_validation");

		$résultat_observé = $this->call("PUT", "/user/Marcel", [
			"username" => "Marcel",
			"courriel" => "marcel@gmail.com",
		]);

		$this->assertEquals(400, $résultat_observé->status());
		$this->assertEquals(
			'{"erreur":{"password":["Le champ password est obligatoire."]}}',
			$résultat_observé->getContent(),
		);
	}

	public function test_étant_donné_une_authentification_locale_lorsquon_inscrit_un_nouvel_utilisateur_avec_mot_de_passe_vide_on_obtient_une_erreur_400()
	{
		putenv("AUTH_LOCAL=true");

		$mockExpéditeurDao = DAOFactory::getInstance()->get_expéditeur();
		$mockExpéditeurDao->shouldNotReceive("envoyer_courriel_de_validation");

		$résultat_observé = $this->call("PUT", "/user/Marcel", [
			"username" => "Marcel",
			"courriel" => "marcel@gmail.com",
			"password" => "",
		]);

		$this->assertEquals(400, $résultat_observé->status());
		$this->assertEquals(
			'{"erreur":{"password":["Le champ password est obligatoire."]}}',
			$résultat_observé->getContent(),
		);
	}

	public function test_étant_donné_une_authentification_locale_lorsquon_inscrit_sans_userame_courriel_ni_mdp_on_obtient_une_erreur_400()
	{
		putenv("AUTH_LOCAL=true");

		$mockExpéditeurDao = DAOFactory::getInstance()->get_expéditeur();
		$mockExpéditeurDao->shouldNotReceive("envoyer_courriel_de_validation");

		$résultat_observé = $this->call("PUT", "/user/Marcel", []);

		$this->assertEquals(400, $résultat_observé->status());
		$this->assertEquals(
			'{"erreur":{"username":["Le champ username est obligatoire."],"courriel":["Le champ courriel est obligatoire."],"password":["Le champ password est obligatoire."]}}',
			$résultat_observé->getContent(),
		);
	}

	public function test_étant_donné_une_authentification_locale_lorsquon_inscrit_sans_courriel_on_obtient_une_erreur_400()
	{
		putenv("AUTH_LOCAL=true");

		$mockExpéditeurDao = DAOFactory::getInstance()->get_expéditeur();
		$mockExpéditeurDao->shouldNotReceive("envoyer_courriel_de_validation");

		$résultat_observé = $this->call("PUT", "/user/Marcel", [
			"username" => "Marcel",
			"password" => "Test1234",
		]);

		$this->assertEquals(400, $résultat_observé->status());
		$this->assertEquals(
			'{"erreur":{"courriel":["Le champ courriel est obligatoire."]}}',
			$résultat_observé->getContent(),
		);
	}
}
