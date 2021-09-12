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
use progression\domaine\entité\{User, Clé};
use PHPUnit\Framework\TestCase;
use Mockery;

final class LoginIntTests extends TestCase
{
	public function setUp(): void
	{
		parent::setUp();

		$mockUserDAO = Mockery::mock("progression\dao\UserDAO");
		$mockUserDAO
			->allows()
			->get_user("bob")
			->andReturn(new User("bob"));
		$mockUserDAO
			->allows()
			->get_user("Banane")
			->andReturn(null);
		$mockUserDAO
			->allows()
			->vérifier_password(Mockery::Any(), "password")
			->andReturn(true);
		$mockUserDAO
			->allows()
			->vérifier_password(Mockery::Any(), Mockery::Any())
			->andReturn(false);

		$mockUserDAO->shouldReceive("save")->andReturn(new User("Banane"));
		$mockUserDAO->shouldReceive("set_password")->withArgs(function ($user, $password) {
			return $user->username == "Banane" && $password == "password";
		});

		$mockCléDAO = Mockery::mock("progression\dao\CléDAO");
		$mockCléDAO
			->shouldReceive("get_clé")
			->with("bob", "clé valide")
			->andReturn(new Clé("secret", (new \DateTime())->getTimestamp(), 0, Clé::PORTEE_AUTH));
		$mockCléDAO
			->shouldReceive("vérifier")
			->with("bob", "clé valide", "secret")
			->andReturn(true);
		$mockCléDAO
			->shouldReceive("get_clé")
			->with("bob", "clé expirée")
			->andReturn(
				new Clé(
					"secret",
					(new \DateTime())->getTimestamp() - 2,
					(new \DateTime())->getTimestamp() - 1,
					Clé::PORTEE_AUTH,
				),
			);
		$mockCléDAO
			->shouldReceive("get_clé")
			->with("bob", "clé révoquée")
			->andReturn(new Clé("secret", (new \DateTime())->getTimestamp(), 0, Clé::PORTEE_REVOQUEE));
		$mockCléDAO
			->shouldReceive("get_clé")
			->with("bob", "clé inexistante")
			->andReturn(null);
		$mockCléDAO->shouldReceive("vérifier")->andReturn(false);

		$mockDAOFactory = Mockery::mock("progression\dao\DAOFactory");
		$mockDAOFactory
			->allows()
			->get_user_dao()
			->andReturn($mockUserDAO);
		$mockDAOFactory
			->allows()
			->get_clé_dao()
			->andReturn($mockCléDAO);
		DAOFactory::setInstance($mockDAOFactory);
	}

	public function tearDown(): void
	{
		Mockery::close();
	}

	public function test_étant_donné_lutilisateur_null_lorsquon_login_obtient_null()
	{
		$interacteur = new LoginInt();
		$résultat_obtenu = $interacteur->effectuer_login_par_identifiant(null);

		$this->assertNull($résultat_obtenu);
	}

	public function test_étant_donné_lutilisateur_vide_lorsquon_login_obtient_null()
	{
		$interacteur = new LoginInt();
		$résultat_obtenu = $interacteur->effectuer_login_par_identifiant("");

		$this->assertNull($résultat_obtenu);
	}

	public function test_étant_donné_lutilisateur_bob_et_une_authentification_de_type_no_lorsquon_login_sans_mot_de_passe_on_obtient_un_objet_user_nommé_bob()
	{
		$_ENV["AUTH_TYPE"] = "no";

		$interacteur = new LoginInt();
		$résultat_obtenu = $interacteur->effectuer_login_par_identifiant("bob");

		$this->assertEquals(new User("bob"), $résultat_obtenu);
	}

	public function test_étant_donné_lutilisateur_Banane_inexistant_et_une_authentification_de_type_no_lorsquon_login_sans_mot_de_passe_il_est_créé_et_on_obtient_un_objet_user_nommé_Banane()
	{
		$_ENV["AUTH_TYPE"] = "no";

		$interacteur = new LoginInt();
		$résultat_obtenu = $interacteur->effectuer_login_par_identifiant("Banane");

		$this->assertEquals(new User("Banane"), $résultat_obtenu);
	}

	public function test_étant_donné_lutilisateur_existant_bob_et_une_authentification_de_type_local_lorsquon_login_avec_mdp_correct_on_obtient_un_objet_user_nommé_bob()
	{
		$_ENV["AUTH_TYPE"] = "local";

		$interacteur = new LoginInt();
		$résultat_obtenu = $interacteur->effectuer_login_par_identifiant("bob", "password");

		$this->assertEquals(new User("bob"), $résultat_obtenu);
	}

	public function test_étant_donné_lutilisateur_existant_bob_et_une_authentification_de_type_local_lorsquon_login_avec_mdp_incorrect_on_obtient_null()
	{
		$_ENV["AUTH_TYPE"] = "local";

		$interacteur = new LoginInt();
		$résultat_obtenu = $interacteur->effectuer_login_par_identifiant("bob", "pas mon mot de passe");

		$this->assertNull($résultat_obtenu);
	}

	public function test_étant_donné_lutilisateur_Banane_inexistant_et_une_authentification_de_type_local_lorsquon_login_on_obtient_null()
	{
		$_ENV["AUTH_TYPE"] = "local";

		$interacteur = new LoginInt();
		$résultat_obtenu = $interacteur->effectuer_login_par_identifiant("Banane", "password");

		$this->assertNull($résultat_obtenu);
	}

	// Login par clé
	public function test_étant_donné_lutilisateur_existant_bob_lorsquon_login_avec_une_clé_d_authentification_valide_on_obtient_un_user_bob()
	{
		$interacteur = new LoginInt();
		$résultat_obtenu = $interacteur->effectuer_login_par_clé("bob", "clé valide", "secret");

		$this->assertEquals(new User("bob"), $résultat_obtenu);
	}

	public function test_étant_donné_lutilisateur_existant_bob_lorsquon_login_avec_une_clé_d_authentification_inexistante_on_obtient_null()
	{
		$interacteur = new LoginInt();
		$résultat_obtenu = $interacteur->effectuer_login_par_clé("bob", "clé inexistante", "secret");

		$this->assertNull($résultat_obtenu);
	}

	public function test_étant_donné_lutilisateur_existant_bob_lorsquon_login_avec_une_clé_d_authentification_et_un_secret_invalide_on_obtient_null()
	{
		$interacteur = new LoginInt();
		$résultat_obtenu = $interacteur->effectuer_login_par_clé("bob", "clé valide", "mauvais secret");

		$this->assertNull($résultat_obtenu);
	}

	public function test_étant_donné_lutilisateur_existant_bob_lorsquon_login_avec_une_clé_d_authentification_expirée_on_obtient_null()
	{
		$interacteur = new LoginInt();
		$résultat_obtenu = $interacteur->effectuer_login_par_clé("bob", "clé expirée", "secret");

		$this->assertNull($résultat_obtenu);
	}

	public function test_étant_donné_lutilisateur_existant_bob_lorsquon_login_avec_une_clé_d_authentification_révoquée_on_obtient_null()
	{
		$interacteur = new LoginInt();
		$résultat_obtenu = $interacteur->effectuer_login_par_clé("bob", "clé révoquée", "secret");

		$this->assertNull($résultat_obtenu);
	}
}