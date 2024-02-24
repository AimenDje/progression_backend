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

use Illuminate\Support\Facades\Config;
use progression\dao\DAOFactory;
use progression\domaine\entité\user\{User, État, Rôle};
use progression\TestCase;
use progression\UserAuthentifiable;
use Mockery;

final class ModifierUserIntTests extends TestCase
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

		$mockDAOFactory = Mockery::mock("progression\\dao\\DAOFactory");
		DAOFactory::setInstance($mockDAOFactory);

		$mockUserDAO = Mockery::mock("progression\\dao\\UserDAO");
		$mockDAOFactory->allows()->get_user_dao()->andReturn($mockUserDAO);

		$mockExpéditeurDao = Mockery::mock("progression\\dao\\mail\\Expéditeur");
		$mockDAOFactory->shouldReceive("get_expéditeur")->andReturn($mockExpéditeurDao);
	}

	#Préférences
	public function test_étant_donné_un_utilisateur_sans_préférences_lorsquon_lui_ajoute_des_préférences_on_obtient_le_même_utilisateur_avec_des_préférences()
	{
		$this->actingAs($this->user);

		$user_test = new User(username: "bob", date_inscription: 0);

		$interacteur = new ModifierUserInt();
		$user_modifié = $interacteur->modifier_préférences($user_test, "mes préférences");

		$this->assertEquals(new User(username: "bob", date_inscription: 0, préférences: "mes préférences"), $user_test);
		$this->assertEquals(
			new User(username: "bob", date_inscription: 0, préférences: "mes préférences"),
			$user_modifié,
		);
	}

	public function test_étant_donné_un_utilisateur_avec_préférences_lorsquon_modifie_des_préférences_on_obtient_le_même_utilisateur_avec_des_nouvelles_préférences()
	{
		$this->actingAs($this->user);

		$user_test = new User(username: "bob", date_inscription: 0, préférences: "des préférences originales");

		$interacteur = new ModifierUserInt();
		$user_modifié = $interacteur->modifier_préférences($user_test, "d'autres préférences");

		$this->assertEquals(
			new User(username: "bob", date_inscription: 0, préférences: "d'autres préférences"),
			$user_test,
		);
		$this->assertEquals(
			new User(username: "bob", date_inscription: 0, préférences: "d'autres préférences"),
			$user_modifié,
		);
	}

	#Courriel
	public function test_étant_donné_un_utilisateur_avec_courriel_lorsquon_modifie_son_courriel_on_obtient_le_même_utilisateur_avec_courriel_modifié_et_état_en_attente()
	{
		$user_test = new User(username: "bob", date_inscription: 0, courriel: "bob@test.com");

		$mockUserDAO = DAOFactory::getInstance()->get_user_dao();
		$mockUserDAO->shouldReceive("trouver")->with(null, "autre@test.com")->andReturn(null);
		DAOFactory::getInstance()
			->get_expéditeur()
			->shouldReceive("envoyer_courriel_de_validation")
			->with($user_test)
			->once();

		$this->actingAs($this->user);

		$interacteur = new ModifierUserInt();
		$user_modifié = $interacteur->modifier_courriel($user_test, "autre@test.com");

		$this->assertEquals(
			new User(
				username: "bob",
				date_inscription: 0,
				courriel: "autre@test.com",
				état: État::EN_ATTENTE_DE_VALIDATION,
			),
			$user_modifié,
		);
	}

	public function test_étant_donné_un_utilisateur_sans_validation_de_courriel_lorsquon_modifie_son_courriel_on_obtient_le_même_utilisateur_avec_courriel_modifié_et_état_inactif()
	{
		Config::set("mail.mailer", "no");

		$user_test = new User(username: "bob", date_inscription: 0, courriel: "bob@test.com");

		$mockUserDAO = DAOFactory::getInstance()->get_user_dao();
		$mockUserDAO->shouldReceive("trouver")->with(null, "autre@test.com")->andReturn(null);
		DAOFactory::getInstance()->get_expéditeur()->shouldNotReceive("envoyer_courriel_de_validation");

		$this->actingAs($this->user);

		$interacteur = new ModifierUserInt();
		$user_modifié = $interacteur->modifier_courriel($user_test, "autre@test.com");

		$this->assertEquals(
			new User(username: "bob", date_inscription: 0, courriel: "autre@test.com", état: État::INACTIF),
			$user_modifié,
		);
	}

	public function test_étant_donné_un_utilisateur_avec_courriel_lorsquon_modifie_son_courriel_avec_un_courriel_existant_on_obtient_une_exception()
	{
		$user_test = new User(username: "bob", date_inscription: 0, courriel: "bob@test.com");

		$mockUserDAO = DAOFactory::getInstance()->get_user_dao();
		$mockUserDAO
			->shouldReceive("trouver")
			->with(null, "autre@test.com")
			->andReturn(new User(username: "Autre", date_inscription: 0, courriel: "autre@test.com"));

		DAOFactory::getInstance()->get_expéditeur()->shouldNotReceive("envoyer_courriel_de_validation");

		$this->actingAs($this->user);

		$this->expectException(DuplicatException::class);

		$interacteur = new ModifierUserInt();
		$interacteur->modifier_courriel($user_test, "autre@test.com");
	}

	#État
	public function test_étant_donné_un_utilisateur_inactif_lorsquon_modifie_son_état_on_obtient_une_exception()
	{
		$this->actingAs($this->user);

		$user_test = new User(username: "bob", date_inscription: 0, état: État::INACTIF);

		$interacteur = new ModifierUserInt();

		$this->expectException(PermissionException::class);

		$interacteur->modifier_état($user_test, État::ACTIF);
	}

	public function test_étant_donné_un_utilisateur_en_attente_lorsquon_modifie_son_état_pour_actif_on_obtient_un_utilisateur_actif()
	{
		$this->actingAs($this->user);

		$user_test = new User(username: "bob", date_inscription: 0, état: État::EN_ATTENTE_DE_VALIDATION);

		$interacteur = new ModifierUserInt();
		$user_modifié = $interacteur->modifier_état($user_test, État::ACTIF);

		$this->assertEquals(new User(username: "bob", date_inscription: 0, état: État::ACTIF), $user_test);
		$this->assertEquals(new User(username: "bob", date_inscription: 0, état: État::ACTIF), $user_modifié);
	}

	public function test_étant_donné_un_utilisateur_en_attente_lorsquon_modifie_son_état_pour_inactif_on_obtient_une_exception()
	{
		$this->actingAs($this->user);

		$user_test = new User(username: "bob", date_inscription: 0, état: État::EN_ATTENTE_DE_VALIDATION);

		$interacteur = new ModifierUserInt();
		$this->expectException(PermissionException::class);

		$interacteur->modifier_état($user_test, État::INACTIF);
	}

	public function test_étant_donné_un_utilisateur_inactif_lorsqu_un_admin_modifie_son_état_pour_actif_on_obtient_un_utilisateur_actif()
	{
		$this->actingAs($this->admin);

		$user_test = new User(username: "bob", date_inscription: 0, état: État::INACTIF);

		$interacteur = new ModifierUserInt();
		$user_modifié = $interacteur->modifier_état($user_test, État::ACTIF);

		$this->assertEquals(new User(username: "bob", date_inscription: 0, état: État::ACTIF), $user_test);
		$this->assertEquals(new User(username: "bob", date_inscription: 0, état: État::ACTIF), $user_modifié);
	}

	public function test_étant_donné_un_utilisateur_en_attente_lorsqu_un_admin_modifie_son_état_pour_inactif_on_obtient_un_utilisateur_inactif()
	{
		$this->actingAs($this->admin);

		$user_test = new User(username: "bob", date_inscription: 0, état: État::EN_ATTENTE_DE_VALIDATION);

		$interacteur = new ModifierUserInt();
		$user_modifié = $interacteur->modifier_état($user_test, État::INACTIF);

		$this->assertEquals(new User(username: "bob", date_inscription: 0, état: État::INACTIF), $user_test);
		$this->assertEquals(new User(username: "bob", date_inscription: 0, état: État::INACTIF), $user_modifié);
	}

	public function test_étant_donné_un_utilisateur_inactif_lorsquon_modifie_son_état_pour_en_attente_on_obtient_un_utilisateur_en_attente()
	{
		$this->actingAs($this->admin);

		$user_test = new User(username: "bob", date_inscription: 0, état: État::INACTIF);

		$interacteur = new ModifierUserInt();

		$user_modifié = $interacteur->modifier_état($user_test, État::EN_ATTENTE_DE_VALIDATION);

		$this->assertEquals(
			new User(username: "bob", date_inscription: 0, état: État::EN_ATTENTE_DE_VALIDATION),
			$user_test,
		);
		$this->assertEquals(
			new User(username: "bob", date_inscription: 0, état: État::EN_ATTENTE_DE_VALIDATION),
			$user_modifié,
		);
	}

	#Rôle
	public function test_étant_donné_un_utilisateur_normal_lorsquon_modifie_son_rôle_pour_admin_on_obtient_une_erreur_de_permission()
	{
		$this->actingAs($this->user);

		$user_test = new User(username: "bob", date_inscription: 0, état: État::ACTIF);

		$interacteur = new ModifierUserInt();

		$this->expectException(PermissionException::class);

		$interacteur->modifier_rôle($user_test, Rôle::ADMIN);
	}

	public function test_étant_donné_un_utilisateur_normal_lorsqu_un_admin_modifie_son_rôle_pour_admin_on_obtient_un_utilisateur_admin()
	{
		$this->actingAs($this->admin);

		$user_test = new User(username: "bob", date_inscription: 0, état: État::ACTIF);

		$interacteur = new ModifierUserInt();

		$user_modifié = $interacteur->modifier_rôle($user_test, Rôle::ADMIN);

		$this->assertEquals(
			new User(username: "bob", date_inscription: 0, état: État::ACTIF, rôle: Rôle::ADMIN),
			$user_test,
		);
		$this->assertEquals(
			new User(username: "bob", date_inscription: 0, état: État::ACTIF, rôle: Rôle::ADMIN),
			$user_modifié,
		);
	}

	#Mot de passe
	public function test_étant_donné_un_utilisateur_actif_lorsquon_modifie_son_mot_de_passe_on_obtient_un_utilisateur_avec_un_nouveau_mot_de_passe()
	{
		$user_test = new User(username: "bob", date_inscription: 0, état: État::ACTIF);

		$mockUserDAO = DAOFactory::getInstance()->get_user_dao();
		$mockUserDAO->shouldReceive("set_password")->with($user_test, "qwerty123!")->once();

		$this->actingAs($this->user);

		$interacteur = new ModifierUserInt();

		$user_modifié = $interacteur->modifier_password($user_test, "qwerty123!");

		$this->assertEquals(
			new User(username: "bob", date_inscription: 0, état: État::ACTIF, rôle: Rôle::NORMAL),
			$user_modifié,
		);
	}
}
