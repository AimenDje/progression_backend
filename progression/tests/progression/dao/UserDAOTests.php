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

namespace progression\dao;

use progression\domaine\entité\{Avancement, User};

final class UserDAOTests extends \TestCase
{
	public function setUp(): void
	{
		$_ENV["DB_SERVERNAME"] = "172.20.0.2";
		$_ENV["DB_USERNAME"] = "root";
		$_ENV["DB_PASSWORD"] = "password";
		$_ENV["DB_DBNAME"] = "quiz_test";
		EntitéDAO::get_connexion()->begin_transaction();
	}

	public function tearDown(): void
	{
		EntitéDAO::get_connexion()->rollback();
	}

	public function test_étant_donné_un_utilisateur_existant_lorsquon_cherche_par_son_username_on_obtient_son_profil()
	{
		$réponse_attendue = new User("bob");
		$réponse_attendue->avancements = [
			"https://depot.com/roger/questions_prog/fonctions01/appeler_une_autre_fonction" => new Avancement([], 1, 3),
			"https://depot.com/roger/questions_prog/fonctions01/appeler_une_fonction" => new Avancement([], 0, 3)
		];

		$résponse_observée = (new UserDAO())->get_user("bob");
		$this->assertEquals($réponse_attendue, $résponse_observée);
	}

	public function test_étant_donné_un_utilisateur_inexistant_lorsquon_le_cherche_par_son_username_on_obtient_null()
	{
		$réponse_attendue = null;

		$résponse_observée = (new UserDAO())->get_user("alice");
		$this->assertEquals($réponse_attendue, $résponse_observée);
	}

	public function test_étant_donné_un_utilisateur_inexistant_lorsquon_le_sauvegarde_il_est_créé_dans_la_BD_et_on_obtient_son_profil()
	{
		$réponse_attendue = new User("gaston");
		$user_test = new User("gaston");

		$résponse_observée = (new UserDAO())->save($user_test);
		$this->assertEquals($réponse_attendue, $résponse_observée);

		$résponse_observée = (new UserDAO())->get_user("gaston");
		$this->assertEquals($réponse_attendue, $résponse_observée);
	}

	public function test_étant_donné_un_utilisateur_existant_lorsquon_le_sauvegarde_il_est_modifié_dans_la_BD_et_on_obtient_son_profil_modifié()
	{
		$réponse_attendue = new User("bob", User::ROLE_ADMIN);
		$réponse_attendue->avancements = [
			"https://depot.com/roger/questions_prog/fonctions01/appeler_une_autre_fonction" => new Avancement([], 1, 3),
			"https://depot.com/roger/questions_prog/fonctions01/appeler_une_fonction" => new Avancement([], 0, 3)
		];

		$user_test = (new UserDAO())->get_user("bob");
		$user_test->rôle = User::ROLE_ADMIN;

		$résponse_observée = (new UserDAO())->save($user_test);
		$this->assertEquals($réponse_attendue, $résponse_observée);

		$résponse_observée = (new UserDAO())->get_user("bob");
		$this->assertEquals($réponse_attendue, $résponse_observée);
	}
}
