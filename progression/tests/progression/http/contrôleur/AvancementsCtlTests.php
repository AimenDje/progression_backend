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
use progression\domaine\entité\{Avancement, TentativeProg, Sauvegarde, Commentaire};
use progression\domaine\entité\user\{User, Rôle, État};
use progression\UserAuthentifiable;

final class AvancementsCtlTests extends ContrôleurTestCase
{
	public function setUp(): void
	{
		parent::setUp();

		// UserDAO
		$mockUserDAO = Mockery::mock("progression\\dao\\UserDAO");
		$mockUserDAO
			->shouldReceive("get_user")
			->with("jdoe")
			->andReturn(new User(username: "jdoe", date_inscription: 0));
		$mockUserDAO->shouldReceive("get_user")->with("bob")->andReturn(new User(username: "bob", date_inscription: 0));

		// Avancement
		$avancements = [
			"uri_a" => new Avancement(),
			"uri_b" => new Avancement(),
		];

		$avancements_et_tentatives = [
			"uri_a" => new Avancement(
				tentatives: [
					1614965817 => new TentativeProg(
						langage: "python",
						code: "codeTest 1",
						date_soumission: 1614965817,
						réussi: false,
						résultats: [],
						tests_réussis: 0,
						feedback: "feedbackTest",
					),
					1614965818 => new TentativeProg(
						langage: "python",
						code: "codeTest 2",
						date_soumission: 1614965818,
						réussi: true,
						résultats: [],
						tests_réussis: 2,
						feedback: "feedbackTest",
					),
				],
			),
			"uri_b" => new Avancement(tentatives: []),
		];

		$avancements_tentatives_et_sauvegardes = [
			"uri_a" => new Avancement(
				tentatives: [
					1614965817 => new TentativeProg(
						langage: "python",
						code: "codeTest 1",
						date_soumission: 1614965817,
						réussi: false,
						résultats: [],
						tests_réussis: 0,
						feedback: "feedbackTest",
					),
					1614965818 => new TentativeProg(
						langage: "python",
						code: "codeTest 2",
						date_soumission: 1614965818,
						réussi: true,
						résultats: [],
						tests_réussis: 2,
						feedback: "feedbackTest",
					),
				],
				sauvegardes: [new Sauvegarde(1614965814, "code sauvegardé")],
			),
			"uri_b" => new Avancement(tentatives: []),
		];

		$avancements_tentatives_commentaires_et_sauvegardes = [
			"uri_a" => new Avancement(
				tentatives: [
					1614965817 => new TentativeProg(
						langage: "python",
						code: "codeTest 1",
						date_soumission: 1614965817,
						réussi: false,
						résultats: [],
						tests_réussis: 0,
						feedback: "feedbackTest",
						commentaires: [
							new Commentaire(
								"Ceci est un commentaire",
								new User(username: "oteur", date_inscription: 0),
								1614974921,
								42,
							),
							new Commentaire(
								"Ceci est un autre commentaire",
								new User(username: "oteur", date_inscription: 0),
								1614974922,
								43,
							),
						],
					),
					1614965818 => new TentativeProg(
						langage: "python",
						code: "codeTest 2",
						date_soumission: 1614965818,
						réussi: true,
						résultats: [],
						tests_réussis: 2,
						feedback: "feedbackTest",
						commentaires: [
							new Commentaire(
								"Ceci est encore un autre commentaire",
								new User(username: "oteur", date_inscription: 0),
								1614984921,
								24,
							),
						],
					),
				],
				sauvegardes: [new Sauvegarde(1614965814, "code sauvegardé")],
			),
			"uri_b" => new Avancement(tentatives: []),
		];

		$mockAvancementDAO = Mockery::mock("progression\\dao\\AvancementDAO");
		$mockAvancementDAO->shouldReceive("get_tous")->with("jdoe", [])->andReturn($avancements);
		$mockAvancementDAO
			->shouldReceive("get_tous")
			->with("jdoe", ["tentatives"])
			->andReturn($avancements_et_tentatives);
		$mockAvancementDAO
			->shouldReceive("get_tous")
			->with("jdoe", ["tentatives", "sauvegardes"])
			->andReturn($avancements_tentatives_et_sauvegardes);
		$mockAvancementDAO
			->shouldReceive("get_tous")
			->with("jdoe", ["tentatives", "tentatives.commentaires", "sauvegardes"])
			->andReturn($avancements_tentatives_commentaires_et_sauvegardes);
		$mockAvancementDAO->shouldReceive("get_tous")->with("bob", [])->andReturn([]);

		// DAOFactory
		$mockDAOFactory = Mockery::mock("progression\\dao\\DAOFactory");
		$mockDAOFactory->shouldReceive("get_user_dao")->andReturn($mockUserDAO);
		$mockDAOFactory->shouldReceive("get_avancement_dao")->andReturn($mockAvancementDAO);

		DAOFactory::setInstance($mockDAOFactory);
	}

	public function test_étant_donné_un_utilisateur_ayant_des_avancements_lorsquon_appelle_get_on_obtient_tous_les_avancements_et_ses_relations_sous_forme_json()
	{
		$user = new UserAuthentifiable(username: "jdoe", date_inscription: 0, rôle: Rôle::NORMAL, état: État::ACTIF);
		$résultat_observé = $this->actingAs($user)->call("GET", "/user/jdoe/avancements");

		$this->assertResponseStatus(200);
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/avancementCtlTests_avancements.json",
			$résultat_observé->getContent(),
		);
	}

	public function test_étant_donné_un_utilisateur_ayant_des_avancements_lorsquon_appelle_get_en_incluant_les_tentatives_on_obtient_tous_les_avancements_et_ses_relations_sous_forme_json()
	{
		$user = new UserAuthentifiable(username: "jdoe", date_inscription: 0, rôle: Rôle::NORMAL, état: État::ACTIF);
		$résultat_observé = $this->actingAs($user)->call("GET", "/user/jdoe/avancements?include=tentatives");

		$this->assertResponseStatus(200);
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/avancementCtlTests_avancements_avec_tentatives.json",
			$résultat_observé->getContent(),
		);
	}

	public function test_étant_donné_un_utilisateur_ayant_des_avancements_lorsquon_appelle_get_en_incluant_les_tentatives_et_les_sauvegardes_on_obtient_tous_les_avancements_et_ses_relations_sous_forme_json()
	{
		$user = new UserAuthentifiable(username: "jdoe", date_inscription: 0, rôle: Rôle::NORMAL, état: État::ACTIF);
		$résultat_observé = $this->actingAs($user)->call(
			"GET",
			"/user/jdoe/avancements?include=tentatives,sauvegardes",
		);

		$this->assertResponseStatus(200);
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/avancementCtlTests_avancements_avec_tentatives_et_sauvegardes.json",
			$résultat_observé->getContent(),
		);
	}

	public function test_étant_donné_un_utilisateur_ayant_des_avancements_lorsquon_appelle_get_en_incluant_les_tentatives_leur_commentaires_et_les_sauvegardes_on_obtient_tous_les_avancements_et_ses_relations_sous_forme_json()
	{
		$user = new UserAuthentifiable(username: "jdoe", date_inscription: 0, rôle: Rôle::NORMAL, état: État::ACTIF);
		$résultat_observé = $this->actingAs($user)->call(
			"GET",
			"/user/jdoe/avancements?include=tentatives.commentaires,sauvegardes",
		);

		$this->assertResponseStatus(200);
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ .
				"/résultats_attendus/avancementCtlTests_avancements_avec_tentatives_commentaires_et_sauvegardes.json",
			$résultat_observé->getContent(),
		);
	}

	public function test_étant_donné_un_utilisateur_sans_avancement_lorsquon_appelle_get_on_obtient_un_tableau_vide()
	{
		$user = new UserAuthentifiable(username: "bob", date_inscription: 0, rôle: Rôle::NORMAL, état: État::ACTIF);
		$résultat_observé = $this->actingAs($user)->call("GET", "/user/bob/avancements");

		$this->assertResponseStatus(200);
		$this->assertJsonStringEqualsJsonString('{"data":[]}', $résultat_observé->getContent());
	}
}
