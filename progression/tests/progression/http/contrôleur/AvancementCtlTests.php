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
use progression\dao\question\ChargeurException;
use progression\domaine\entité\question\{Question, QuestionProg, État};
use progression\domaine\entité\{Avancement, TentativeProg, Sauvegarde, Commentaire};
use progression\domaine\entité\user\{User, Rôle};
use progression\domaine\entité\user\État as UserÉtat;
use progression\UserAuthentifiable;

final class AvancementCtlTests extends ContrôleurTestCase
{
	public $user;

	public function setUp(): void
	{
		parent::setUp();

		$this->user = new UserAuthentifiable(
			username: "jdoe",
			date_inscription: 0,
			rôle: Rôle::NORMAL,
			état: UserÉtat::ACTIF,
		);

		// UserDAO
		$mockUserDAO = Mockery::mock("progression\\dao\\UserDAO");
		$mockUserDAO
			->shouldReceive("get_user")
			->with("jdoe")
			->andReturn(new User(username: "jdoe", date_inscription: 0, rôle: Rôle::NORMAL, état: UserÉtat::ACTIF));
		$mockUserDAO
			->shouldReceive("get_user")
			->with("roger")
			->andReturn(new User(username: "roger", date_inscription: 0, rôle: Rôle::NORMAL, état: UserÉtat::ACTIF));
		$mockUserDAO->shouldReceive("get_user")->with("Marcel")->andReturn(null);

		// Question Appeler une fonction
		$question = new QuestionProg();
		$question->titre = "Avancement de test";
		$question->niveau = "facile";

		$mockQuestionDAO = Mockery::mock("progression\\dao\\question\\QuestionDAO");
		$mockQuestionDAO
			->shouldReceive("get_question")
			->with("https://depot.com/roger/questions_prog/fonctions01/appeler_une_fonction", [])
			->andReturn($question);
		$mockQuestionDAO
			->shouldReceive("get_question")
			->with("https://depot.com/roger/questions_prog/fonctions01/appeler_une_fonction")
			->andReturn($question);

		// Question Nouvelle Question
		$question = new QuestionProg();
		$question->titre = "Nouvel Avancement de test";
		$question->niveau = "test";

		$mockQuestionDAO
			->shouldReceive("get_question")
			->with("https://depot.com/roger/questions_prog/nouvelle_question")
			->andReturn($question);
		$mockQuestionDAO
			->shouldReceive("get_question")
			->with("https://depot.com/roger/questions_prog/nouvelle_question_defaut")
			->andReturn($question);

		// Question inexistante
		$mockQuestionDAO
			->shouldReceive("get_question")
			->with("https://depot.com/roger/question_inexistante")
			->andThrow(
				new ChargeurException(
					"Le fichier https://depot.com/roger/question_inexistante ne peut pas être chargé.",
				),
			);

		// Avancement
		$avancement_réussi = new Avancement();
		$avancement_réussi->date_modification = 1614965818;
		$avancement_réussi->date_réussite = 1614965817;
		$avancement_réussi->état = État::REUSSI;
		$avancement_réussi->titre = "Avancement de test";
		$avancement_réussi->niveau = "facile";
		$avancement_réussi->extra = "Infos extra";

		$avancement_réussi_avec_tentatives_et_sauvegardes = new Avancement(
			tentatives: [
				1614965817 => new TentativeProg("python", "codeTest 1", 1614965817, true, [], 2, 120, "feedbackTest"),
				1614965818 => new TentativeProg("python", "codeTest 2", 1614965818, true, [], 2, 150, "feedbackTest"),
			],
			titre: "Titre",
			niveau: "facile",
			sauvegardes: [
				"python" => new Sauvegarde(1614965817, "Test 1"),
				"java" => new Sauvegarde(1614965818, "Test 2"),
			],
		);

		$avancement_réussi_avec_tentatives_commentaires_et_sauvegardes = new Avancement(
			tentatives: [
				1614965817 => new TentativeProg(
					langage: "python",
					code: "codeTest 1",
					date_soumission: 1614965817,
					réussi: true,
					résultats: [],
					tests_réussis: 2,
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
			titre: "Titre",
			niveau: "facile",
			sauvegardes: [
				"python" => new Sauvegarde(1614965817, "Test 1"),
				"java" => new Sauvegarde(1614965818, "Test 2"),
			],
		);

		$mockAvancementDAO = Mockery::mock("progression\\dao\\AvancementDAO");
		$mockAvancementDAO
			->shouldReceive("get_avancement")
			->with("jdoe", "https://depot.com/roger/questions_prog/fonctions01/appeler_une_fonction", [])
			->andReturn($avancement_réussi);
		$mockAvancementDAO
			->shouldReceive("get_avancement")
			->with("jdoe", "https://depot.com/roger/questions_prog/fonctions01/appeler_une_fonction", [
				"tentatives",
				"sauvegardes",
			])
			->andReturn($avancement_réussi_avec_tentatives_et_sauvegardes);
		$mockAvancementDAO
			->shouldReceive("get_avancement")
			->with("jdoe", "https://depot.com/roger/questions_prog/fonctions01/appeler_une_fonction", [
				"tentatives",
				"tentatives.commentaires",
				"sauvegardes",
			])
			->andReturn($avancement_réussi_avec_tentatives_commentaires_et_sauvegardes);
		$mockAvancementDAO
			->shouldReceive("get_avancement")
			->with("jdoe", Mockery::Any(), Mockery::Any())
			->andReturn(null);

		// DAOFactory
		$mockDAOFactory = Mockery::mock("progression\\dao\\DAOFactory");
		$mockDAOFactory->shouldReceive("get_user_dao")->andReturn($mockUserDAO);
		$mockDAOFactory->shouldReceive("get_question_dao")->andReturn($mockQuestionDAO);
		$mockDAOFactory->shouldReceive("get_avancement_dao")->andReturn($mockAvancementDAO);

		DAOFactory::setInstance($mockDAOFactory);
	}

	// GET
	public function test_étant_donné_un_avancement_existant_lorsquon_le_récupère_sans_includes_on_obtient_l_avancement_seulement_sous_forme_json()
	{
		$résultat_observé = $this->actingAs($this->user)->call(
			"GET",
			"/avancement/jdoe/aHR0cHM6Ly9kZXBvdC5jb20vcm9nZXIvcXVlc3Rpb25zX3Byb2cvZm9uY3Rpb25zMDEvYXBwZWxlcl91bmVfZm9uY3Rpb24",
		);

		$this->assertResponseStatus(200);
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/avancementCtlTests_avancement_réussi.json",
			$résultat_observé->getContent(),
		);
	}

	public function test_étant_un_avancement_existant_lorsquon_le_récupère_avec_includes_on_obtient_l_avancement_et_ses_tentatives_et_sauvegardes_sous_forme_json()
	{
		$résultat_observé = $this->actingAs($this->user)->call(
			"GET",
			"/avancement/jdoe/aHR0cHM6Ly9kZXBvdC5jb20vcm9nZXIvcXVlc3Rpb25zX3Byb2cvZm9uY3Rpb25zMDEvYXBwZWxlcl91bmVfZm9uY3Rpb24?include=tentatives,sauvegardes",
		);

		$this->assertResponseStatus(200);
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/avancementCtlTests_avancement_réussi_avec_tentatives_et_sauvegardes.json",
			$résultat_observé->getContent(),
		);
	}

	public function test_étant_donné_un_avancement_existant_lorsquon_le_récupère_en_incluant_les_tentatives_avec_commentaires_et_sauvegardes_on_obtient_l_avancement_et_ses_sauvegardes_et_tentatives_avec_ses_commentaires_sous_forme_json()
	{
		$résultat_observé = $this->actingAs($this->user)->call(
			"GET",
			"/avancement/jdoe/aHR0cHM6Ly9kZXBvdC5jb20vcm9nZXIvcXVlc3Rpb25zX3Byb2cvZm9uY3Rpb25zMDEvYXBwZWxlcl91bmVfZm9uY3Rpb24?include=tentatives.commentaires,sauvegardes",
		);

		$this->assertResponseStatus(200);
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ .
				"/résultats_attendus/avancementCtlTests_avancement_réussi_avec_tentatives_commentaires_et_sauvegardes.json",
			$résultat_observé->getContent(),
		);
	}

	public function test_étant_donné_un_avancement_inexistant_lorsquon_le_récupère_on_obtient_une_erreur_404()
	{
		$résultat_observé = $this->actingAs($this->user)->call(
			"GET",
			"/avancement/jdoe/aHR0cHM6Ly9kZXBvdC5jb20vcm9nZXIvcXVlc3Rpb25faW5leGlzdGFudGU",
		);

		$this->assertResponseStatus(404);
		$this->assertEquals('{"erreur":"Ressource non trouvée."}', $résultat_observé->getContent());
	}

	// POST
	public function test_étant_donné_un_avancement_existant_lorsquon_appelle_post_sans_question_uri_on_obtient_une_erreur_400()
	{
		$résultat_observé = $this->actingAs($this->user)->call("POST", "/user/jdoe/avancements", [
			"avancement" => [
				"titre" => "Question test",
				"niveau" => "niveau test",
			],
		]);

		$this->assertResponseStatus(400);
		$this->assertEquals(
			'{"erreur":{"question_uri":["Le champ question uri est obligatoire."]}}',
			$résultat_observé->getContent(),
		);
	}

	public function test_étant_donné_un_avancement_existant_lorsquon_appelle_post_avec_un_question_uri_non_encodé_on_obtient_une_erreur_400()
	{
		$résultat_observé = $this->actingAs($this->user)->call("POST", "/user/jdoe/avancements", [
			"avancement" => [
				"titre" => "Question test",
				"niveau" => "niveau test",
			],
			"question_uri" => "http://test.exemple.com/info.yml",
		]);

		$this->assertResponseStatus(400);
		$this->assertEquals(
			'{"erreur":{"question_uri":["Le champ question_uri doit être un URL encodé en base64."]}}',
			$résultat_observé->getContent(),
		);
	}

	public function test_étant_donné_un_avancement_existant_lorsquon_appelle_post_avec_un_question_uri_non_valide_on_obtient_une_erreur_400()
	{
		$résultat_observé = $this->actingAs($this->user)->call("POST", "/user/jdoe/avancements", [
			"avancement" => [
				"titre" => "Question test",
				"niveau" => "niveau test",
			],
			"question_uri" => "Q2VjaSBuJ2VzdCBwdXMgdW4gVVJJ",
		]);

		$this->assertResponseStatus(400);
		$this->assertEquals(
			'{"erreur":{"question_uri":["Le champ question_uri doit être un URL encodé en base64."]}}',
			$résultat_observé->getContent(),
		);
	}

	public function test_étant_donné_un_avancement_inexistant_lorsquon_appelle_post_sans_avancement_on_obtient_un_avancement_avec_ses_valeurs_par_défaut()
	{
		$nouvel_avancement = new Avancement(titre: "Nouvel Avancement de test", niveau: "test");

		$mockAvancementDAO = DAOFactory::getInstance()->get_avancement_dao();
		$mockAvancementDAO
			->shouldReceive("save")
			->once()
			->withArgs(function ($user, $uri, $type, $avancement) use ($nouvel_avancement) {
				return $user == "jdoe" &&
					$uri == "https://depot.com/roger/questions_prog/nouvelle_question_defaut" &&
					$type == "prog" &&
					$avancement == $nouvel_avancement;
			})
			->andReturn([
				"https://depot.com/roger/questions_prog/nouvelle_question_defaut" => $nouvel_avancement,
			]);

		$résultat_observé = $this->actingAs($this->user)->call("POST", "/user/jdoe/avancements", [
			"question_uri" => "aHR0cHM6Ly9kZXBvdC5jb20vcm9nZXIvcXVlc3Rpb25zX3Byb2cvbm91dmVsbGVfcXVlc3Rpb25fZGVmYXV0",
		]);

		$this->assertResponseStatus(200);
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/avancementCtlTests_nouvelAvancement_défaut.json",
			$résultat_observé->getContent(),
		);
	}

	public function test_étant_donné_un_avancement_inexistant_lorsquon_appelle_post_avec_un_avancement_on_obtient_le_nouvel_avancement_sauvegardé()
	{
		$nouvel_avancement = new Avancement(titre: "Nouvel Avancement de test", niveau: "test", extra: "Infos extra");

		$mockAvancementDAO = DAOFactory::getInstance()->get_avancement_dao();
		$mockAvancementDAO
			->shouldReceive("save")
			->once()
			->withArgs(function ($user, $uri, $type, $avancement) use ($nouvel_avancement) {
				return $user == "jdoe" &&
					$uri == "https://depot.com/roger/questions_prog/nouvelle_question" &&
					$type == "prog" &&
					$avancement == $nouvel_avancement;
			})
			->andReturn([
				"https://depot.com/roger/questions_prog/nouvelle_question" => $nouvel_avancement,
			]);

		$résultat_observé = $this->actingAs($this->user)->call("POST", "/user/jdoe/avancements", [
			"question_uri" => "aHR0cHM6Ly9kZXBvdC5jb20vcm9nZXIvcXVlc3Rpb25zX3Byb2cvbm91dmVsbGVfcXVlc3Rpb24",
			"avancement" => [
				"état" => 2, // Propriétés non modifiables
				"titre" => "Titre modifié",
				"niveau" => "Niveau modifié",
				"date_modification" => 9999999,
				"date_réussite" => 888888,
				"extra" => "Infos extra", // Propriétés modifiables
			],
		]);

		$this->assertResponseStatus(200);
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/avancementCtlTests_nouvelAvancement.json",
			$résultat_observé->getContent(),
		);
	}

	public function test_étant_donné_un_avancement_existant_lorsquon_appelle_post_sans_avancement_il_n_est_pas_sauvegardé_et_est_retourné()
	{
		$mockAvancementDAO = DAOFactory::getInstance()->get_avancement_dao();
		$mockAvancementDAO->shouldNotReceive("save");

		$résultat_observé = $this->actingAs($this->user)->call("POST", "/user/jdoe/avancements", [
			"question_uri" =>
				"aHR0cHM6Ly9kZXBvdC5jb20vcm9nZXIvcXVlc3Rpb25zX3Byb2cvZm9uY3Rpb25zMDEvYXBwZWxlcl91bmVfZm9uY3Rpb24",
		]);

		$this->assertResponseStatus(200);
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/avancementCtlTests_avancement_réussi.json",
			$résultat_observé->getContent(),
		);
	}

	public function test_étant_donné_un_avancement_existant_lorsquon_appelle_post_avec_un_avancement_on_obtient_l_avancement_modifié_et_sauvegardé()
	{
		$avancement_sauvegardé = new Avancement(
			titre: "Avancement de test",
			niveau: "facile",
			extra: "Infos extra modifiées",
		);
		$avancement_sauvegardé->date_modification = 1614965818;
		$avancement_sauvegardé->date_réussite = 1614965817;
		$avancement_sauvegardé->état = État::REUSSI;

		$mockAvancementDAO = DAOFactory::getInstance()->get_avancement_dao();
		$mockAvancementDAO
			->shouldReceive("save")
			->once()
			->withArgs(function ($user, $uri, $type, $avancement) use ($avancement_sauvegardé) {
				return $user == "jdoe" &&
					$uri == "https://depot.com/roger/questions_prog/fonctions01/appeler_une_fonction" &&
					$type == "prog" &&
					$avancement == $avancement_sauvegardé;
			})
			->andReturn([
				"https://depot.com/roger/questions_prog/fonctions01/appeler_une_fonction" => $avancement_sauvegardé,
			]);

		$résultat_observé = $this->actingAs($this->user)->call("POST", "/user/jdoe/avancements", [
			"question_uri" =>
				"aHR0cHM6Ly9kZXBvdC5jb20vcm9nZXIvcXVlc3Rpb25zX3Byb2cvZm9uY3Rpb25zMDEvYXBwZWxlcl91bmVfZm9uY3Rpb24",
			"avancement" => [
				"état" => 1, // Propriétés non modifiables
				"titre" => "Titre modifié",
				"niveau" => "Niveau modifié",
				"date_modification" => 9999999,
				"date_réussite" => 888888,
				"extra" => "Infos extra modifiées", // Propriétés modifiables
			],
		]);

		$this->assertResponseStatus(200);
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/avancementCtlTests_avancement_réussi_modifié.json",
			$résultat_observé->getContent(),
		);
	}

	public function test_étant_un_avancement_pour_une_question_inexistante_lorsquon_appelle_post_sans_avancement_on_obtient_une_erreur_422()
	{
		$mockAvancementDAO = DAOFactory::getInstance()->get_avancement_dao();
		$mockAvancementDAO->shouldNotReceive("save");

		$résultat_observé = $this->actingAs($this->user)->call("POST", "/user/jdoe/avancements", [
			"question_uri" => "aHR0cHM6Ly9kZXBvdC5jb20vcm9nZXIvcXVlc3Rpb25faW5leGlzdGFudGU",
		]);

		$this->assertResponseStatus(422);
		$this->assertJsonStringEqualsJsonString(
			'{"erreur":"Le fichier https://depot.com/roger/question_inexistante ne peut pas être chargé."}',
			$résultat_observé->getContent(),
		);
	}

	public function test_étant_un_avancement_pour_une_question_inexistante_lorsquon_appelle_post_avec_un_avancement_on_obtient_une_erreur_422()
	{
		$mockAvancementDAO = DAOFactory::getInstance()->get_avancement_dao();
		$mockAvancementDAO->shouldNotReceive("save");

		$résultat_observé = $this->actingAs($this->user)->call("POST", "/user/jdoe/avancements", [
			"avancement" => [
				"titre" => "Question test",
				"niveau" => "niveau test",
			],
			"question_uri" => "aHR0cHM6Ly9kZXBvdC5jb20vcm9nZXIvcXVlc3Rpb25faW5leGlzdGFudGU",
		]);

		$this->assertResponseStatus(422);
		$this->assertJsonStringEqualsJsonString(
			'{"erreur":"Le fichier https://depot.com/roger/question_inexistante ne peut pas être chargé."}',
			$résultat_observé->getContent(),
		);
	}
}
