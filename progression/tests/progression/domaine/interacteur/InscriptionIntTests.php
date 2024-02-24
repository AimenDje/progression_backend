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

namespace progression\domaine\interacteur;

use progression\dao\DAOFactory;
use progression\domaine\entité\user\{User, État, Rôle};
use progression\TestCase;
use Carbon\Carbon;
use Mockery;

final class InscriptionIntTests extends TestCase
{
	public function setUp(): void
	{
		parent::setUp();

		$mockUserDao = Mockery::mock("progression\\dao\\UserDAO");
		$mockUserDao
			->shouldReceive("get_user")
			->with("bob")
			->andReturn(new User(username: "bob", date_inscription: 0, courriel: "bob@progressionmail.com"));
		$mockUserDao
			->shouldReceive("trouver")
			->with(null, "bob@progressionmail.com")
			->andReturn(new User(username: "bob", date_inscription: 0, courriel: "bob@progressionmail.com"));
		$mockUserDao->shouldReceive("get_user")->andReturn(null);

		$mockDAOFactory = Mockery::mock("progression\\dao\\DAOFactory");
		$mockDAOFactory->shouldReceive("get_user_dao")->andReturn($mockUserDao);

		$mockExpéditeurDao = Mockery::mock("progression\\dao\\mail\Expéditeur");
		$mockDAOFactory->shouldReceive("get_expéditeur")->andReturn($mockExpéditeurDao);
		DAOFactory::setInstance($mockDAOFactory);
	}

	public function test_étant_donné_un_utilisateur_non_existant_et_un_type_dauthentification_local_lorsquon_effectue_linscription_il_est_sauvegardé_et_on_reçoit_le_nouveau_User_en_attente()
	{
		putenv("AUTH_LOCAL=true");
		putenv("AUTH_LDAP=true");

		Carbon::setTestNow(Carbon::create(2001, 5, 21, 12));

		$roger = new User(
			username: "roger",
			date_inscription: 990446400,
			courriel: "roger@gmail.com",
			état: État::EN_ATTENTE_DE_VALIDATION,
		);

		$mockUserDao = DAOFactory::getInstance()->get_user_dao();
		$mockUserDao->shouldReceive("get_user")->with("roger")->andReturn(null, $roger);
		$mockUserDao->shouldReceive("trouver")->with(Mockery::any(), "roger@gmail.com")->andReturn(null);
		$mockUserDao
			->shouldReceive("save")
			->withArgs(function ($username, $user) {
				return $user->username == "roger" &&
					$username == "roger" &&
					$user->rôle == Rôle::NORMAL &&
					$user->état == État::EN_ATTENTE_DE_VALIDATION;
			})
			->once()
			->andReturn(["roger" => $roger]);

		$mockUserDao
			->shouldReceive("set_password")
			->once()
			->withArgs(function ($user, $password) {
				return $user->username == "roger" && $password == "password";
			});

		$mockExpéditeurDao = DAOFactory::getInstance()->get_expéditeur();
		$mockExpéditeurDao
			->shouldReceive("envoyer_courriel_de_validation")
			->withArgs(function ($user) {
				return $user->courriel == "roger@gmail.com";
			})
			->andReturn();

		$user = (new InscriptionInt())->effectuer_inscription_locale("roger", "roger@gmail.com", "password");

		$this->assertEquals(
			[
				"roger" => new User(
					username: "roger",
					date_inscription: 990446400,
					courriel: "roger@gmail.com",
					état: État::EN_ATTENTE_DE_VALIDATION,
				),
			],
			$user,
		);
	}

	public function test_étant_donné_un_utilisateur_non_existant_et_un_type_dauthentification_no_lorsquon_effectue_linscription_il_est_sauvegardé_et_on_reçoit_le_nouveau_User_actif()
	{
		putenv("AUTH_LOCAL=false");
		putenv("AUTH_LDAP=false");

		Carbon::setTestNow(Carbon::create(2001, 5, 21, 12));

		$mockUserDao = DAOFactory::getInstance()->get_user_dao();
		$mockUserDao
			->shouldReceive("get_user")
			->with("roger")
			->andReturn(null, new User(username: "roger", date_inscription: 0));
		$mockUserDao
			->shouldReceive("save")
			->withArgs(function ($username, $user) {
				return $username == "roger" && $user->username == "roger" && $user->état == État::ACTIF;
			})
			->once()
			->andReturnUsing(function ($username, $user) {
				return [$username => $user];
			});
		$mockUserDao->shouldNotReceive("set_password");

		$mockExpéditeurDao = DAOFactory::getInstance()->get_expéditeur();
		$mockExpéditeurDao->shouldNotReceive("envoyer_courriel_de_validation");

		$user = (new InscriptionInt())->effectuer_inscription_sans_mdp("roger");

		$this->assertEquals(
			[
				"roger" => new User(
					"roger",
					courriel: null,
					date_inscription: 990446400,
					préférences: "",
					état: État::ACTIF,
				),
			],
			$user,
		);
	}

	public function test_étant_donné_un_utilisateur_non_existant_et_un_type_dauthentification_local_sans_validation_de_courriel_lorsquon_effectue_linscription_il_est_sauvegardé_et_on_reçoit_le_nouveau_User_actif()
	{
		putenv("AUTH_LOCAL=true");
		putenv("AUTH_LDAP=false");
		putenv("MAIL_MAILER=no");

		Carbon::setTestNow(Carbon::create(2001, 5, 21, 12));

		$mockUserDao = DAOFactory::getInstance()->get_user_dao();
		$mockUserDao
			->shouldReceive("get_user")
			->with("roger")
			->andReturn(null, new User(username: "roger", date_inscription: 990446400));
		$mockUserDao->shouldReceive("trouver")->with(null, "roger@progressionmail.com")->andReturn(null);
		$mockUserDao
			->shouldReceive("save")
			->withArgs(function ($username, $user) {
				return $username == "roger" && $user->username == "roger" && $user->état == État::ACTIF;
			})
			->once()
			->andReturnUsing(function ($username, $user) {
				return [$username => $user];
			});
		$mockUserDao
			->shouldReceive("set_password")
			->once()
			->withArgs(function ($user, $password) {
				return $user->username == "roger" && $password == "password";
			});
		$mockExpéditeurDao = DAOFactory::getInstance()->get_expéditeur();
		$mockExpéditeurDao->shouldNotReceive("envoyer_courriel_de_validation");

		$user = (new InscriptionInt())->effectuer_inscription_locale("roger", "roger@progressionmail.com", "password");

		$this->assertEquals(
			[
				"roger" => new User(
					"roger",
					courriel: "roger@progressionmail.com",
					date_inscription: 990446400,
					préférences: "",
					état: État::ACTIF,
				),
			],
			$user,
		);
	}

	public function test_étant_donné_un_utilisateur_non_existant_sans_authentification_et_une_variable_PREFERENCES_DEFAUT_définie_lorsquon_effectue_linscription_il_est_sauvegardé_avec_des_préférences_par_défaut()
	{
		putenv("AUTH_LOCAL=false");
		putenv("AUTH_LDAP=false");
		putenv("PREFERENCES_DEFAUT=préférences par défaut");

		$mockUserDao = DAOFactory::getInstance()->get_user_dao();
		$mockUserDao
			->allows()
			->get_user("roger")
			->andReturn(null, new User("roger", date_inscription: 990446400, préférences: "préférences par défaut"));
		$mockUserDao
			->shouldReceive("save")
			->withArgs(function ($username, $user) {
				return $username == "roger" &&
					$user->username == "roger" &&
					$user->préférences == "préférences par défaut";
			})
			->once()
			->andReturnUsing(function ($username, $user) {
				return [$username => $user];
			});

		$user = (new InscriptionInt())->effectuer_inscription_sans_mdp("roger");
		$this->assertEquals(
			[
				"roger" => new User(
					"roger",
					date_inscription: 990446400,
					état: État::ACTIF,
					préférences: "préférences par défaut",
				),
			],
			$user,
		);
	}

	public function test_étant_donné_un_utilisateur_non_existant_un_type_dauthentification_locale_et_une_variable_PREFERENCES_DEFAUT_définie_lorsquon_effectue_linscription_il_est_sauvegardé_avec_des_préférences_par_défaut()
	{
		putenv("MAIL_MAILER=log");

		putenv("AUTH_LOCAL=true");
		putenv("AUTH_LDAP=false");
		putenv("PREFERENCES_DEFAUT=préférences par défaut");

		$mockUserDao = DAOFactory::getInstance()->get_user_dao();
		$mockUserDao
			->allows()
			->get_user("roger")
			->andReturn(
				null,
				new User(
					"roger",
					courriel: "roger@testmail.com",
					date_inscription: 990446400,
					préférences: "préférences par défaut",
				),
			);
		$mockUserDao->shouldReceive("trouver")->with(Mockery::any(), "roger@testmail.com")->andReturn(null);
		$mockUserDao
			->shouldReceive("save")
			->withArgs(function ($username, $user) {
				return $username == "roger" &&
					$user->username == "roger" &&
					$user->préférences == "préférences par défaut";
			})
			->once()
			->andReturnUsing(function ($username, $user) {
				return [$username => $user];
			});

		$mockUserDao
			->shouldReceive("set_password")
			->once()
			->withArgs(function ($user, $password) {
				return $user->username == "roger" && $password == "password";
			});
		$mockExpéditeurDao = DAOFactory::getInstance()->get_expéditeur();
		$mockExpéditeurDao
			->shouldReceive("envoyer_courriel_de_validation")
			->withArgs(function ($user) {
				return $user->courriel == "roger@testmail.com";
			})
			->andReturn();

		$user = (new InscriptionInt())->effectuer_inscription_locale("roger", "roger@testmail.com", "password");

		$this->assertEquals(
			[
				"roger" => new User(
					"roger",
					courriel: "roger@testmail.com",
					date_inscription: 990446400,
					état: État::EN_ATTENTE_DE_VALIDATION,
					préférences: "préférences par défaut",
				),
			],
			$user,
		);
	}

	public function test_étant_donné_un_utilisateur_non_existant_sans_authentification_et_une_variable_PREFERENCES_DEFAUT_non_définie_lorsquon_effectue_linscription_il_est_sauvegardé_sans_préférences()
	{
		putenv("AUTH_LOCAL=false");
		putenv("AUTH_LDAP=false");
		putenv("PREFERENCES_DEFAUT=");

		$mockUserDao = DAOFactory::getInstance()->get_user_dao();
		$mockUserDao
			->allows()
			->get_user("roger")
			->andReturn(null, new User("roger", date_inscription: 990446400, préférences: ""));
		$mockUserDao
			->shouldReceive("save")
			->withArgs(function ($username, $user) {
				return $username == "roger" && $user->username == "roger" && $user->préférences == "";
			})
			->once()
			->andReturnUsing(function ($username, $user) {
				return [$username => $user];
			});

		$user = (new InscriptionInt())->effectuer_inscription_sans_mdp("roger");

		$this->assertEquals(
			[
				"roger" => new User("roger", date_inscription: 990446400, préférences: "", état: État::ACTIF),
			],
			$user,
		);
	}

	public function test_étant_donné_un_utilisateur_non_existant_et_un_type_dauthentification_local_lorsquon_effectue_linscription_sans_mdp_on_obtient_une_exception()
	{
		putenv("AUTH_LOCAL=true");
		putenv("AUTH_LDAP=true");

		$mockUserDao = DAOFactory::getInstance()->get_user_dao();
		$mockUserDao
			->shouldReceive("get_user")
			->with("roger")
			->andReturn(null, new User(username: "roger", date_inscription: 0));
		$mockUserDao->shouldNotReceive("save");
		$mockUserDao->shouldNotReceive("set_password");

		$this->expectException(RessourceInvalideException::class);
		(new InscriptionInt())->effectuer_inscription_locale("roger", "roger@gmail.com", null);
	}

	public function test_étant_donné_un_nouvel_admin_lorsquon_effectue_linscription_il_est_sauvegardé_et_on_reçoit_le_nouveau_User_actif()
	{
		putenv("AUTH_LOCAL=true");
		putenv("AUTH_LDAP=false");

		Carbon::setTestNow(Carbon::create(2001, 5, 21, 12));

		$mockUserDao = DAOFactory::getInstance()->get_user_dao();
		$mockUserDao
			->shouldReceive("get_user")
			->with("admin")
			->andReturn(
				null,
				new User(
					username: "admin",
					date_inscription: 0,
					courriel: "admin@gmail.com",
					état: État::ACTIF,
					rôle: Rôle::ADMIN,
				),
			);
		$mockUserDao->shouldReceive("trouver")->with(Mockery::any(), "admin@gmail.com")->andReturn(null);
		$mockUserDao
			->shouldReceive("save")
			->withArgs(function ($username, $user) {
				return $username == "admin" &&
					$user->username == "admin" &&
					$user->état == État::ACTIF &&
					$user->rôle == Rôle::ADMIN;
			})
			->once()
			->andReturnUsing(function ($username, $user) {
				return [$username => $user];
			});

		$mockUserDao
			->shouldReceive("set_password")
			->once()
			->withArgs(function ($user, $password) {
				return $user->username == "admin" && $password == "password";
			});

		$user = (new InscriptionInt())->effectuer_inscription_locale(
			"admin",
			"admin@gmail.com",
			"password",
			rôle: Rôle::ADMIN,
		);

		$this->assertEquals(
			[
				"admin" => new User(
					username: "admin",
					date_inscription: 990446400,
					courriel: "admin@gmail.com",
					état: État::ACTIF,
					rôle: Rôle::ADMIN,
					préférences: "préférences par défaut",
				),
			],
			$user,
		);
	}

	public function test_étant_donné_un_utilisateur_existant_lorsquon_linscrit_avec_le_même_username_on_obtient_une_exception()
	{
		$this->expectException(DuplicatException::class);
		(new InscriptionInt())->effectuer_inscription_locale("bob", courriel: "bob@test.com", password: "password");
	}

	public function test_étant_donné_un_utilisateur_existant_lorsquon_linscrit_avec_le_même_courriel_on_obtient_null()
	{
		$this->expectException(DuplicatException::class);
		(new InscriptionInt())->effectuer_inscription_locale(
			"autrenom",
			courriel: "bob@progressionmail.com",
			password: "password",
		);
	}

	public function test_étant_donné_un_utilisateur_existant_lorsquon_linscrit_avec_le_même_courriel_et_username_on_obtient_une_exception()
	{
		$this->expectException(DuplicatException::class);

		(new InscriptionInt())->effectuer_inscription_locale(
			"bob",
			courriel: "bob@progressionmail.com",
			password: "password",
		);
	}
}
