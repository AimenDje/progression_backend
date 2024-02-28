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
use progression\domaine\entité\{Sauvegarde};
use progression\domaine\entité\user\{User, Rôle, État};
use progression\UserAuthentifiable;

final class SauvegardeCtlTests extends ContrôleurTestCase
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

		// Sauvegarde
		$sauvegarde = new Sauvegarde(1620150294, "print(\"Hello world!\")");
		$sauvegardes = [];
		$sauvegardes["python"] = new Sauvegarde(1620150294, "print(\"Hello world!\")");

		$mockSauvegardeDAO = Mockery::mock("progression\\dao\\SauvegardeDAO");
		$mockSauvegardeDAO
			->shouldReceive("get_sauvegarde")
			->with("jdoe", "https://depot.com/roger/questions_prog/fonctions01/appeler_une_fonction", "python", [])
			->andReturn($sauvegarde);
		$mockSauvegardeDAO
			->shouldReceive("get_sauvegarde")
			->with("jdoe", "https://depot.com/roger/questions_prog/fonctions01/appeler_une_fonction", "java", [])
			->andReturn(null);
		$mockSauvegardeDAO->shouldReceive("save")->andReturn(["python" => $sauvegarde]);

		// User
		$mockUserDAO = Mockery::mock("progression\\dao\\UserDAO");
		$mockUserDAO->allows("get_user")->with("jdoe", [])->andReturn(new User(username: "jdoe", date_inscription: 0));

		// DAOFactory
		$mockDAOFactory = Mockery::mock("progression\\dao\\DAOFactory");
		$mockDAOFactory->shouldReceive("get_sauvegarde_dao")->andReturn($mockSauvegardeDAO);
		$mockDAOFactory->shouldReceive("get_user_dao")->andReturn($mockUserDAO);

		DAOFactory::setInstance($mockDAOFactory);
	}

	public function test_étant_donné_une_sauvegarde_existante_lorsquon_fait_une_requête_get_on_obtient_une_sauvegarde()
	{
		$résultat_observé = $this->actingAs($this->user)->call(
			"GET",
			"/sauvegarde/jdoe/aHR0cHM6Ly9kZXBvdC5jb20vcm9nZXIvcXVlc3Rpb25zX3Byb2cvZm9uY3Rpb25zMDEvYXBwZWxlcl91bmVfZm9uY3Rpb24/python",
		);

		$this->assertEquals(200, $résultat_observé->status());
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/sauvegardeCtlTests_sauvegarde.json",
			$résultat_observé->getContent(),
		);
	}

	public function test_étant_donné_une_sauvegarde_inexistante_lorsquon_fait_une_requête_get_on_obtient_un_message_une_erreur_404()
	{
		$résultat_observé = $this->actingAs($this->user)->call(
			"GET",
			"/sauvegarde/jdoe/aHR0cHM6Ly9kZXBvdC5jb20vcm9nZXIvcXVlc3Rpb25zX3Byb2cvZm9uY3Rpb25zMDEvYXBwZWxlcl91bmVfZm9uY3Rpb24/java",
		);

		$this->assertEquals(404, $résultat_observé->status());
		$this->assertEquals('{"erreur":"Ressource non trouvée."}', $résultat_observé->getContent());
	}

	public function test_étant_donné_une_sauvegarde_sans_langage_lorquon_fait_une_requête_post_on_obtient_une_erreur_400()
	{
		$résultat_observé = $this->actingAs($this->user)->call(
			"POST",
			"/avancement/jdoe/aHR0cHM6Ly9kZXBvdC5jb20vcm9nZXIvcXVlc3Rpb25zX3Byb2cvZm9uY3Rpb25zMDEvYXBwZWxlcl91bmVfZm9uY3Rpb24/sauvegardes",
			[
				"code" => "print(\"Hello world!\")",
			],
		);

		$this->assertEquals(400, $résultat_observé->status());
		$this->assertEquals(
			'{"erreur":{"langage":["Le champ langage est obligatoire."]}}',
			$résultat_observé->getContent(),
		);
	}

	public function test_étant_donné_une_sauvegarde_sans_code_lorquon_fait_une_requête_post_on_obtient_une_erreur_400()
	{
		$résultat_observé = $this->actingAs($this->user)->call(
			"POST",
			"/avancement/jdoe/aHR0cHM6Ly9kZXBvdC5jb20vcm9nZXIvcXVlc3Rpb25zX3Byb2cvZm9uY3Rpb25zMDEvYXBwZWxlcl91bmVfZm9uY3Rpb24/sauvegardes",
			[
				"langage" => "python",
			],
		);

		$this->assertEquals(400, $résultat_observé->status());
		$this->assertEquals('{"erreur":{"code":["Le champ code est obligatoire."]}}', $résultat_observé->getContent());
	}

	public function test_étant_donné_un_username_luri_dune_question_un_code_et_un_langage_lorsquon_appelle_post_on_obtient_une_sauvegarde()
	{
		$résultat_observé = $this->actingAs($this->user)->call(
			"POST",
			"/avancement/jdoe/aHR0cHM6Ly9kZXBvdC5jb20vcm9nZXIvcXVlc3Rpb25zX3Byb2cvZm9uY3Rpb25zMDEvYXBwZWxlcl91bmVfZm9uY3Rpb24/sauvegardes",
			[
				"langage" => "python",
				"code" => "print(\"Hello world!\")",
			],
		);

		$this->assertEquals(200, $résultat_observé->status());
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/sauvegardeCtlTests_sauvegarde.json",
			$résultat_observé->getContent(),
		);
	}
}
