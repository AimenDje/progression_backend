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

use progression\TestCase;

use Illuminate\Support\Facades\Config;
use progression\http\contrôleur\GénérateurDeToken;
use progression\domaine\entité\question\QuestionProg;
use progression\domaine\entité\clé\{Clé, Portée};
use progression\domaine\entité\user\{User, Rôle, État};
use progression\dao\DAOFactory;
use progression\UserAuthentifiable;
use Carbon\Carbon;

final class AuthenticateTests extends TestCase
{
	public $user;

	public function setUp(): void
	{
		parent::setUp();

		Config::set("version.numéro", "3.0.0");

		$this->user = new UserAuthentifiable(
			username: "bob",
			date_inscription: 1590828610,
			rôle: Rôle::NORMAL,
			état: État::ACTIF,
		);
		$this->user_inactif = new UserAuthentifiable(
			username: "roger",
			date_inscription: 0,
			rôle: Rôle::NORMAL,
			état: État::INACTIF,
		);
		$this->user_en_attente = new UserAuthentifiable(
			username: "marcel",
			date_inscription: 0,
			rôle: Rôle::NORMAL,
			état: État::EN_ATTENTE_DE_VALIDATION,
		);

		Carbon::setTestNowAndTimezone(Carbon::create(2001, 5, 21, 12));

		// UserDAO
		$mockUserDAO = Mockery::mock("progression\\dao\\UserDAO");
		$mockUserDAO
			->shouldReceive("get_user")
			->with("bob")
			->andReturn(new User(username: "bob", date_inscription: 1590828610, état: État::ACTIF));
		$mockUserDAO
			->shouldReceive("get_user")
			->with("bob", [])
			->andReturn(new User(username: "bob", date_inscription: 1590828610, état: État::ACTIF));
		$mockUserDAO
			->shouldReceive("trouver")
			->with(null, "bob@progressionmail.com")
			->andReturn(new User(username: "bob", date_inscription: 0, état: État::ACTIF));
		$mockUserDAO
			->shouldReceive("trouver")
			->with(null, "bob@progressionmail.com", [])
			->andReturn(new User(username: "bob", date_inscription: 0, état: État::ACTIF));
		$mockUserDAO->shouldReceive("trouver")->with(null, Mockery::Any(), [])->andReturn(null);
		$mockUserDAO
			->shouldReceive("get_user")
			->with("marcel")
			->andReturn(new User(username: "marcel", date_inscription: 0, état: État::EN_ATTENTE_DE_VALIDATION));
		$mockUserDAO
			->shouldReceive("get_user")
			->with("marcel", [])
			->andReturn(new User(username: "marcel", date_inscription: 0, état: État::EN_ATTENTE_DE_VALIDATION));
		$mockUserDAO
			->shouldReceive("get_user")
			->with("roger")
			->andReturn(new User(username: "roger", date_inscription: 0, état: État::INACTIF));
		$mockUserDAO
			->shouldReceive("get_user")
			->with("roger", [])
			->andReturn(new User(username: "roger", date_inscription: 0, état: État::INACTIF));
		$mockUserDAO->shouldReceive("get_user")->with("zozo")->andReturn(null);

		$mockUserDAO->shouldReceive("vérifier_password")->with(Mockery::Any(), "m0tD3P4ZZE")->andReturn(true);
		$mockUserDAO->shouldReceive("vérifier_password")->with(Mockery::Any(), Mockery::Any())->andReturn(false);

		// CléDAO
		$mockCléDAO = Mockery::mock("progression\\dao\\CléDAO");
		$mockCléDAO
			->shouldReceive("get_clé")
			->with("bob", "cleValide01")
			->andReturn(new Clé(null, (new \DateTime())->getTimestamp(), 0, Portée::AUTH));
		$mockCléDAO->shouldReceive("vérifier")->with("bob", "cleValide01", "secret")->andReturn(true);
		$mockCléDAO->shouldReceive("get_clé")->andReturn(null);
		$mockCléDAO->shouldReceive("vérifier")->andReturn(false);

		$mockQuestionDAO = Mockery::mock("progression\\dao\\QuestionDAO");
		$mockQuestionDAO->shouldReceive("get_question")->with("question_de_test")->andReturn(new QuestionProg());

		// DAOFactory
		$mockDAOFactory = Mockery::mock("progression\\dao\\DAOFactory");
		$mockDAOFactory->shouldReceive("get_user_dao")->andReturn($mockUserDAO);
		$mockDAOFactory->shouldReceive("get_clé_dao")->andReturn($mockCléDAO);
		$mockDAOFactory->shouldReceive("get_question_dao")->andReturn($mockQuestionDAO);
		DAOFactory::setInstance($mockDAOFactory);
	}

	#  AUTH LDAP
	# Ces tests nécessitent que LDAP soit découplé de l'interacteur: https://git.dti.crosemont.quebec/progression/progression_backend/-/issues/79
	//public function test_étant_donné_un_utilisateur_existant_et_une_authentification_LDAP_effectue_une_requête_avec_identifiant_et_mdp_corrects_on_obtient_la_ressource()
	//{
	//	putenv("AUTH_LOCAL=false");
	//	putenv("AUTH_LDAP=true");
	//
	//	$résultat_observé = $this->call("GET", "/user/bob", [
	//		"identifiant" => "bob",
	//		"password" => "m0tD3P4ZZE",
	//	]);
	//
	//    $this->assertResponseStatus(200);
	//	$this->assertJsonStringEqualsJsonString('{"Token":"token valide"}', $token = $résultat_observé->getContent());
	//}
	//
	//public function test_étant_donné_un_utilisateur_existant_et_une_authentification_LDAP_effectue_une_requête_avec_courriel_et_mdp_corrects_on_obtient_la_ressource()
	//{
	//	putenv("AUTH_LOCAL=false");
	//	putenv("AUTH_LDAP=true");
	//
	//	$résultat_observé = $this->call("GET", "/user/bob", [
	//		"identifiant" => "bob@progressionmail.com",
	//		"password" => "m0tD3P4ZZE",
	//	]);
	//
	//	$this->assertResponseStatus(200);
	//	$this->assertJsonStringEqualsJsonString('{"Token":"token valide"}', $token = $résultat_observé->getContent());
	//}

	public function test_étant_donné_un_utilisateur_inexistant_avec_authentification_LDAP_lorsquon_effectue_une_requête_sans_domaine_on_obtient_une_erreur_401()
	{
		putenv("AUTH_LOCAL=false");
		putenv("AUTH_LDAP=true");

		$résultat_observé = $this->call(
			"GET",
			"/user/bob",
			[],
			[],
			[],
			[
				"HTTP_Authorization" => "basic " . base64_encode("zozo:m0tD3P4ZZE"),
			],
		);

		$this->assertResponseStatus(401);
		$this->assertJsonStringEqualsJsonString('{"erreur":"Accès interdit."}', $résultat_observé->content());
	}

	public function test_étant_donné_une_authentification_LDAP_lorsquon_effectue_une_requête_avec_un_nom_dutilisateur_invalide_on_obtient_une_erreur_400()
	{
		putenv("AUTH_LOCAL=false");
		putenv("AUTH_LDAP=true");

		$résultat_observé = $this->call(
			"GET",
			"/user/bob",
			[],
			[],
			[],
			[
				"HTTP_Authorization" => "basic " . base64_encode("Marcel@test@ici.com:password"),
			],
		);

		$this->assertResponseStatus(400);
		$this->assertJsonStringEqualsJsonString(
			'{"erreur":{"identifiant":["L\'identifiant doit être un nom d\'utilisateur ou un courriel valide."]}}',
			$résultat_observé->getContent(),
		);
	}

	#  Aucune AUTH
	public function test_étant_donné_un_utilisateur_existant_et_sans_authentification_lorsquon_effectue_une_requête_avec_identifiant_sans_mdp_on_obtient_la_ressource()
	{
		putenv("AUTH_LOCAL=false");
		putenv("AUTH_LDAP=false");

		$résultat_observé = $this->call(
			"GET",
			"/question/cXVlc3Rpb25fZGVfdGVzdA", // question_de_test
			[],
			[],
			[],
			[
				"HTTP_Authorization" => "basic " . base64_encode("bob"),
			],
		);

		$this->assertResponseStatus(200);
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/ressource_question_de_test.json",
			$résultat_observé->getContent(),
		);
	}

	public function test_étant_donné_un_utilisateur_existant_et_sans_authentification_lorsquon_effectue_une_requête_avec_identifiant_et_mdp_vide_on_obtient_la_ressource()
	{
		putenv("AUTH_LOCAL=false");
		putenv("AUTH_LDAP=false");

		$résultat_observé = $this->call(
			"GET",
			"/question/cXVlc3Rpb25fZGVfdGVzdA", // question_de_test
			[],
			[],
			[],
			[
				"HTTP_Authorization" => "basic " . base64_encode("bob:"),
			],
		);

		$this->assertResponseStatus(200);
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/ressource_question_de_test.json",
			$résultat_observé->getContent(),
		);
	}

	public function test_étant_donné_un_utilisateur_inexistant_sans_authentification_lorsquon_effectue_une_requête_avec_un_courriel_on_obtient_une_erreur_400()
	{
		putenv("AUTH_LOCAL=false");
		putenv("AUTH_LDAP=false");

		$résultat_observé = $this->call(
			"GET",
			"/user/bob",
			[],
			[],
			[],
			["HTTP_Authorization" => "basic " . base64_encode("zozo@ici.com:")],
		);
		$this->assertResponseStatus(400);
		$this->assertJsonStringEqualsJsonString(
			'{"erreur":{"identifiant":["L\'identifiant doit être un nom d\'utilisateur ou un courriel valide."]}}',
			$résultat_observé->getContent(),
		);
	}

	# AUTH locale
	public function test_étant_donné_un_utilisateur_existant_et_une_authentification_locale_lorsquon_effectue_une_requête_avec_identifiant_et_mdp_corrects_on_obtient_la_ressource()
	{
		putenv("AUTH_LOCAL=true");
		putenv("AUTH_LDAP=false");

		$résultat_observé = $this->call(
			"GET",
			"/question/cXVlc3Rpb25fZGVfdGVzdA", // question_de_test
			[],
			[],
			[],
			[
				"HTTP_Authorization" => "basic " . base64_encode("bob:m0tD3P4ZZE"),
			],
		);

		$this->assertResponseStatus(200);
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/ressource_question_de_test.json",
			$résultat_observé->getContent(),
		);
	}

	public function test_étant_donné_un_utilisateur_existant_et_une_authentificaton_locale_lorsquon_effectue_une_requête_avec_courriel_et_mdp_corrects_on_obtient_la_ressource()
	{
		putenv("AUTH_LOCAL=true");
		putenv("AUTH_LDAP=false");

		$résultat_observé = $this->call(
			"GET",
			"/question/cXVlc3Rpb25fZGVfdGVzdA", // question_de_test
			[],
			[],
			[],
			[
				"HTTP_Authorization" => "basic " . base64_encode("bob@progressionmail.com:m0tD3P4ZZE"),
			],
		);

		$this->assertResponseStatus(200);

		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/ressource_question_de_test.json",
			$résultat_observé->getContent(),
		);
	}

	public function test_étant_donné_un_utilisateur_actif_et_une_authentification_par_identifiant_et_mdp_lorsquon_requiert_une_ressource_protégée_on_obtient_la_ressource()
	{
		putenv("AUTH_LOCAL=true");
		putenv("AUTH_LDAP=false");

		$résultat_observé = $this->call(
			"GET",
			"/user/bob",
			[],
			[],
			[],
			[
				"HTTP_Authorization" => "basic " . base64_encode("bob:m0tD3P4ZZE"),
			],
		);

		$this->assertResponseStatus(200);
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/profil_bob.json",
			$résultat_observé->getContent(),
		);
	}

	public function test_étant_donné_un_utilisateur_inactif_avec_authentification_lorsquon_effectue_une_requête_mdp_correct_on_obtient_une_erreur_401()
	{
		putenv("AUTH_LOCAL=true");
		putenv("AUTH_LDAP=false");

		$this->call(
			"GET",
			"/user/roger",
			[],
			[],
			[],
			[
				"HTTP_Authorization" => "basic " . base64_encode("roger:m0tD3P4ZZE"),
			],
		);
		$this->assertResponseStatus(401);
	}

	public function test_étant_donné_un_utilisateur_actif_avec_authentification_lorsquon_effectue_une_requête_sur_une_ressource_non_protégée_on_obtient_la_ressource_et_un_code_200()
	{
		putenv("AUTH_LOCAL=true");
		putenv("AUTH_LDAP=false");

		$résultat_observé = $this->call(
			"GET",
			"/",
			[],
			[],
			[],
			[
				"HTTP_Authorization" => "basic " . base64_encode("bob:m0tD3P4ZZE"),
			],
		);
		$this->assertResponseStatus(200);
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/config_locale_authentifié.json",
			$résultat_observé->getContent(),
		);
	}

	public function test_étant_donné_un_utilisateur_inexistant_avec_authentification_lorsquon_effectue_une_requête_sur_une_ressource_non_protégée_on_obtient_une_erreur_401()
	{
		putenv("AUTH_LOCAL=true");
		putenv("AUTH_LDAP=false");

		$this->call(
			"GET",
			"/",
			[],
			[],
			[],
			[
				"HTTP_Authorization" => "basic " . base64_encode("zozo:jesaispas"),
			],
		);
		$this->assertResponseStatus(401);
	}

	public function test_étant_donné_un_utilisateur_en_attente_de_validation_avec_authentification_lorsquon_effectue_une_requête_mdp_correct_on_obtient_une_erreur_403()
	{
		putenv("AUTH_LOCAL=true");
		putenv("AUTH_LDAP=false");

		$résultat_observé = $this->call(
			"GET",
			"/user/marcel",
			[],
			[],
			[],
			[
				"HTTP_Authorization" => "basic " . base64_encode("marcel:m0tD3P4ZZE"),
			],
		);
		$this->assertResponseStatus(403);
	}

	public function test_étant_donné_un_utilisateur_inexistant_avec_authentification_lorsquon_effectue_une_requête_avec_identifiant_on_obtient_une_erreur_401()
	{
		putenv("AUTH_LOCAL=true");
		putenv("AUTH_LDAP=false");

		$résultat_observé = $this->call(
			"GET",
			"/user/bob",
			[],
			[],
			[],
			[
				"HTTP_Authorization" => "basic " . base64_encode("zozo:m0tD3P4ZZE"),
			],
		);

		$this->assertResponseStatus(401);
		$this->assertJsonStringEqualsJsonString('{"erreur":"Accès interdit."}', $résultat_observé->content());
	}

	public function test_étant_donné_un_utilisateur_existant_avec_authentification_effectue_une_requête_avec_identifiant_et_mdp_erroné_on_obtient_une_erreur_401()
	{
		putenv("AUTH_LOCAL=true");
		putenv("AUTH_LDAP=false");

		$résultat_observé = $this->call(
			"GET",
			"/user/bob",
			[],
			[],
			[],
			["HTTP_Authorization" => "basic " . base64_encode("bob:incorrect")],
		);

		$this->assertResponseStatus(401);
		$this->assertJsonStringEqualsJsonString('{"erreur":"Accès interdit."}', $résultat_observé->content());
	}

	# Identifiants invalides

	public function test_étant_donné_une_authentificaton_locale_lorsquon_effectue_une_requête_un_nom_dutilisateur_vide_on_obtient_une_erreur_400()
	{
		putenv("AUTH_LOCAL=true");
		putenv("AUTH_LDAP=false");

		$résultat_observé = $this->call(
			"GET",
			"/user/bob",
			[],
			[],
			[],
			["HTTP_Authorization" => "basic " . base64_encode(":m0tD3P4ZZE")],
		);

		$this->assertResponseStatus(400);
	}

	public function test_étant_donné_une_authentificaton_locale_lorsquon_effectue_une_requête_un_nom_dutilisateur_invalide_on_obtient_une_erreur_400()
	{
		putenv("AUTH_LOCAL=true");
		putenv("AUTH_LDAP=false");

		$résultat_observé = $this->call(
			"GET",
			"/user/bob",
			[],
			[],
			[],
			[
				"HTTP_Authorization" => "basic " . base64_encode("bo b:m0tD3P4ZZE"),
			],
		);

		$this->assertResponseStatus(400);
	}

	public function test_étant_donné_une_authentificaton_locale_lorsquon_effectue_une_requête_sans_nom_dutlisateur_on_obtient_une_erreur_400()
	{
		putenv("AUTH_LOCAL=true");
		putenv("AUTH_LDAP=false");

		$résultat_observé = $this->call(
			"GET",
			"/user/bob",
			[],
			[],
			[],
			["HTTP_Authorization" => "basic " . base64_encode(":m0tD3P4ZZE")],
		);

		$this->assertResponseStatus(400);
	}

	public function test_étant_donné_une_authentification_locale_lorsquon_effectue_une_requête_sans_mot_de_passe_on_obtient_une_erreur_400()
	{
		putenv("AUTH_LOCAL=true");
		putenv("AUTH_LDAP=false");

		$résultat_observé = $this->call(
			"GET",
			"/user/bob",
			[],
			[],
			[],
			["HTTP_Authorization" => "basic " . base64_encode("bob")],
		);

		$this->assertResponseStatus(400);
	}

	public function test_étant_donné_une_authentification_locale_lorsquon_effectue_une_requête_avec_mot_de_passe_vide_on_obtient_une_erreur_400()
	{
		putenv("AUTH_LOCAL=true");
		putenv("AUTH_LDAP=false");

		$résultat_observé = $this->call(
			"GET",
			"/user/bob",
			[],
			[],
			[],
			["HTTP_Authorization" => "basic " . base64_encode("bob:")],
		);

		$this->assertResponseStatus(400);
	}

	# Authentification par clé
	public function test_étant_donné_un_utilisateur_existant_et_une_clé_dauthentification_valide_effectue_une_requête_on_obtient_la_ressource()
	{
		$résultat_observé = $this->call(
			"GET",
			"/question/cXVlc3Rpb25fZGVfdGVzdA", // question_de_test
			[],
			[],
			[],
			[
				"HTTP_Authorization" => "Key " . base64_encode("bob:cleValide01:secret"),
			],
		);

		$this->assertResponseStatus(200);
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/ressource_question_de_test.json",
			$résultat_observé->getContent(),
		);
	}

	public function test_étant_donné_un_utilisateur_existant_et_une_clé_dauthentification_valide_effectue_une_requête_avec_un_identifiant_invalide_on_obtient_une_erreur_400()
	{
		$résultat_observé = $this->call(
			"GET",
			"/user/bob",
			[],
			[],
			[],
			[
				"HTTP_Authorization" => "Key " . base64_encode("bobprogressionmail.com:cleValide01:secret"),
			],
		);

		$this->assertResponseStatus(400);
	}

	public function test_étant_donné_un_utilisateur_existant_et_une_clé_dauthentification_invalide_effectue_une_requête_on_obtient_une_erreur_401()
	{
		$résultat_observé = $this->call(
			"GET",
			"/user/bob",
			[],
			[],
			[],
			[
				"HTTP_Authorization" => "Key " . base64_encode("bob:cleInvalide00:secret"),
			],
		);

		$this->assertResponseStatus(401);
		$this->assertJsonStringEqualsJsonString('{"erreur":"Accès interdit."}', $résultat_observé->content());
	}

	public function test_étant_donné_un_utilisateur_existant_effectue_une_requête_avec_une_clé_au_nom_invalide_on_obtient_une_erreur_400()
	{
		$résultat_observé = $this->call(
			"GET",
			"/user/bob",
			[],
			[],
			[],
			[
				"HTTP_Authorization" => "Key " . base64_encode("bob:tata toto:secret"),
			],
		);

		$this->assertResponseStatus(400);
		$this->assertJsonStringEqualsJsonString(
			'{"erreur":{"key_name":["Le champ key_name doit être alphanumérique \'a-Z0-9-_\'"]}}',
			$résultat_observé->content(),
		);
	}

	public function test_étant_donné_un_utilisateur_existant_effectue_une_requête_sans_non_de_clé_on_obtient_une_erreur_400()
	{
		$résultat_observé = $this->call(
			"GET",
			"/user/bob",
			[],
			[],
			[],
			[
				"HTTP_Authorization" => "Key " . base64_encode("bob::secret"),
			],
		);

		$this->assertResponseStatus(400);
		$this->assertJsonStringEqualsJsonString(
			'{"erreur":{"password":["Le champ password est obligatoire lorsque key_name ou token ne sont pas présents."]}}',
			$résultat_observé->content(),
		);
	}

	public function test_étant_donné_un_utilisateur_existant_effectue_une_requête_avec_un_secret_vide_on_obtient_une_erreur_400()
	{
		$résultat_observé = $this->call(
			"GET",
			"/user/bob",
			[],
			[],
			[],
			[
				"HTTP_Authorization" => "Key " . base64_encode("bob:cleValide01:"),
			],
		);

		$this->assertResponseStatus(400);
		$this->assertJsonStringEqualsJsonString(
			'{"erreur":{"key_secret":["Le champ key_secret est obligatoire lorsque key_name est présent."]}}',
			$résultat_observé->content(),
		);
	}

	public function test_étant_donné_un_utilisateur_existant_effectue_une_requête_sans_secret_on_obtient_une_erreur_400()
	{
		$résultat_observé = $this->call(
			"GET",
			"/user/bob",
			[],
			[],
			[],
			[
				"HTTP_Authorization" => "Key " . base64_encode("bob:cleValide01"),
			],
		);

		$this->assertResponseStatus(400);
		$this->assertJsonStringEqualsJsonString(
			'{"erreur":{"key_secret":["Le champ key_secret est obligatoire lorsque key_name est présent."]}}',
			$résultat_observé->content(),
		);
	}
}
