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

use progression\dao\DAOFactory;
use progression\http\contrôleur\GénérateurDeToken;
use progression\domaine\entité\user\{User, État, Rôle};
use progression\domaine\entité\clé\{Clé, Portée};
use progression\UserAuthentifiable;
use Firebase\JWT\JWT;

final class AuthServiceProviderTests extends TestCase
{
	public $utilisateurActifNormal;
	public $utilisateurInactifNormal;
	public $utilisateurEnAttenteNormal;
	public $tokenUtilisateurActifNormal;
	public $tokenUtilisateurInactifNormal;
	public $tokenUtilisateurEnAttenteNormal;
	public $tokenAvecFingerprint;

	public function setUp(): void
	{
		parent::setUp();

		putenv("AUTH_LOCAL=true");
		putenv("AUTH_LDAP=false");

		$this->utilisateurActifNormal = new UserAuthentifiable(
			username: "utilisateur_actif_normal",
			date_inscription: 0,
			rôle: Rôle::NORMAL,
			état: État::ACTIF,
		);
		$this->utilisateurInactifNormal = new UserAuthentifiable(
			username: "utilisateur_inactif_normal",
			date_inscription: 0,
			rôle: Rôle::NORMAL,
			état: État::INACTIF,
		);
		$this->utilisateurEnAttenteNormal = new UserAuthentifiable(
			username: "utilisateur_en_attente_normal",
			date_inscription: 0,
			rôle: Rôle::NORMAL,
			état: État::EN_ATTENTE_DE_VALIDATION,
		);

		$this->tokenUtilisateurActifNormal = GénérateurDeToken::get_instance()->générer_token(
			"utilisateur_actif_normal",
		);
		$this->tokenUtilisateurInactifNormal = GénérateurDeToken::get_instance()->générer_token(
			"utilisateur_inactif_normal",
		);
		$this->tokenUtilisateurEnAttenteNormal = GénérateurDeToken::get_instance()->générer_token(
			"utilisateur_en_attente_normal",
			0,
			[
				"user_en_attente" => [
					"url" => "/user/utilisateur_en_attente_normal/",
					"method" => "^POST$",
				],
			],
		);

		$this->tokenAvecFingerprint = GénérateurDeToken::get_instance()->générer_token(
			username: "utilisateur_actif_normal",
			fingerprint: "7ce100971f64e7001e8fe5a51973ecdfe1ced42befe7ee8d5fd6219506b5393c",
		);

		$mockUserDAO = Mockery::mock("progression\\dao\\UserDAO");
		$mockUserDAO
			->shouldReceive("get_user")
			->with("utilisateur_actif_normal")
			->andReturn(
				new User(
					username: "utilisateur_actif_normal",
					date_inscription: 0,
					état: État::ACTIF,
					rôle: Rôle::NORMAL,
				),
			);
		$mockUserDAO
			->shouldReceive("get_user")
			->with("utilisateur_actif_normal", [])
			->andReturn(
				new User(
					username: "utilisateur_actif_normal",
					date_inscription: 0,
					état: État::ACTIF,
					rôle: Rôle::NORMAL,
				),
			);
		$mockUserDAO
			->shouldReceive("get_user")
			->with("utilisateur_inactif_normal")
			->andReturn(
				new User(
					username: "utilisateur_inactif_normal",
					date_inscription: 0,
					état: État::INACTIF,
					rôle: Rôle::NORMAL,
				),
			);
		$mockUserDAO
			->shouldReceive("get_user")
			->with("utilisateur_inactif_normal", [])
			->andReturn(
				new User(
					username: "utilisateur_inactif_normal",
					date_inscription: 0,
					état: État::INACTIF,
					rôle: Rôle::NORMAL,
				),
			);
		$mockUserDAO
			->shouldReceive("get_user")
			->with("utilisateur_en_attente_normal")
			->andReturn(
				new User(
					username: "utilisateur_en_attente_normal",
					date_inscription: 0,
					état: État::EN_ATTENTE_DE_VALIDATION,
					rôle: Rôle::NORMAL,
				),
			);
		$mockUserDAO
			->shouldReceive("get_user")
			->with("utilisateur_en_attente_normal", [])
			->andReturn(
				new User(
					username: "utilisateur_en_attente_normal",
					date_inscription: 0,
					état: État::EN_ATTENTE_DE_VALIDATION,
					rôle: Rôle::NORMAL,
				),
			);
		$mockUserDAO->shouldReceive("vérifier_password")->with(Mockery::any(), "password")->andReturn(true);
		$mockUserDAO->shouldReceive("vérifier_password")->with(Mockery::any(), Mockery::any())->andReturn(false);
		$mockUserDAO
			->shouldReceive("get_user")
			->with("UTILISATEUR_ACTIF_NORMAL", [])
			->andReturn(
				new User(
					username: "utilisateur_actif_normal",
					date_inscription: 0,
					état: État::ACTIF,
					rôle: Rôle::NORMAL,
				),
			);
		$mockUserDAO
			->shouldReceive("get_user")
			->with("autre_utilisateur", [])
			->andReturn(
				new User(username: "autre_utilisateur", date_inscription: 0, état: État::ACTIF, rôle: Rôle::NORMAL),
			);
		$mockUserDAO
			->shouldReceive("get_user")
			->with("utilisateur_innocent", [])
			->andReturn(
				new User(username: "utilisateur_innocent", date_inscription: 0, état: État::ACTIF, rôle: Rôle::NORMAL),
			);
		$mockUserDAO
			->shouldReceive("get_user")
			->with("utilisateur_malveillant")
			->andReturn(
				new User(
					username: "utilisateur_malveillant",
					date_inscription: 0,
					état: État::ACTIF,
					rôle: Rôle::NORMAL,
				),
			);
		$mockUserDAO
			->shouldReceive("get_user")
			->with("utilisateur_malveillant", [])
			->andReturn(
				new User(
					username: "utilisateur_malveillant",
					date_inscription: 0,
					état: État::ACTIF,
					rôle: Rôle::NORMAL,
				),
			);

		$mockUserDAO->shouldReceive("get_user")->with("utilisateur_inexistant", [])->andReturn(null);

		$mockUserDAO->shouldReceive("save")->andReturnUsing(function ($username, $user) {
			return [$username => $user];
		});

		// CléDAO
		$mockCléDAO = Mockery::mock("progression\\dao\\CléDAO");
		$mockCléDAO
			->shouldReceive("get_clé")
			->with("utilisateur_actif_normal", "cleValide01")
			->andReturn(new Clé(null, (new \DateTime())->getTimestamp(), 0, Portée::AUTH));
		$mockCléDAO
			->shouldReceive("vérifier")
			->with("utilisateur_actif_normal", "cleValide01", "secret")
			->andReturn(true);
		$mockCléDAO->shouldReceive("get_clé")->andReturn(null);
		$mockCléDAO->shouldReceive("vérifier")->andReturn(false);

		$mockDAOFactory = Mockery::mock("progression\\dao\\DAOFactory");
		$mockDAOFactory->shouldReceive("get_user_dao")->andReturn($mockUserDAO);
		$mockDAOFactory->shouldReceive("get_clé_dao")->andReturn($mockCléDAO);
		DAOFactory::setInstance($mockDAOFactory);
	}

	// Authentification par token
	public function test_étant_donné_un_token_pour_un_utilisateur_existant_lorsquon_utilise_un_token_avec_un_nom_dutilisateur_avec_une_casse_différente_on_obtient_un_code_200()
	{
		$this->call(
			"GET",
			"/user/UTILISATEUR_ACTIF_NORMAL",
			[],
			[],
			[],
			[
				"HTTP_Authorization" => "Bearer " . $this->tokenUtilisateurActifNormal,
			],
		);

		$this->assertResponseStatus(200);
	}

	public function test_étant_donné_un_token_pour_un_utilisateur_existant_lorsque_le_token_expire_dans_1_seconde_on_obtient_un_code_200()
	{
		$expiration = time() + 1;
		$tokenUtilisateurActifNormal = GénérateurDeToken::get_instance()->générer_token(
			"utilisateur_actif_normal",
			$expiration,
		);

		$this->call(
			"GET",
			"/user/utilisateur_actif_normal",
			[],
			[],
			[],
			["HTTP_Authorization" => "Bearer " . $tokenUtilisateurActifNormal],
		);

		$this->assertResponseStatus(200);
	}

	public function test_étant_donné_un_token_pour_un_utilisateur_existant_lorsque_la_date_dexpiration_du_token_est_0_on_obtient_un_code_200()
	{
		$expiration = 0;
		$tokenUtilisateurActifNormal = GénérateurDeToken::get_instance()->générer_token(
			"utilisateur_actif_normal",
			$expiration,
		);
		$this->call(
			"GET",
			"/user/utilisateur_actif_normal",
			[],
			[],
			[],
			["HTTP_Authorization" => "Bearer " . $tokenUtilisateurActifNormal],
		);

		$this->assertResponseStatus(200);
	}

	public function test_étant_donné_un_token_pour_un_utilisateur_existant_lorsque_la_date_dexpiration_est_échue_depuis_1_seconde_on_obtient_une_erreur_401()
	{
		$expiration = time() - 1;
		$tokenUtilisateurActifNormal = GénérateurDeToken::get_instance()->générer_token(
			"utilisateur_actif_normal",
			$expiration,
		);
		$this->call(
			"GET",
			"/user/utilisateur_actif_normal",
			[],
			[],
			[],
			["HTTP_Authorization" => "Bearer " . $tokenUtilisateurActifNormal],
		);

		$this->assertResponseStatus(401);
	}

	public function test_étant_donné_un_token_pour_un_utilisateur_inexistant_lorsque_lorsquon_tente_dacceder_à_une_ressource_on_obtient_une_erreur_401()
	{
		$tokenUtilisateurActifNormal = GénérateurDeToken::get_instance()->générer_token("utilisateur_inexistant");
		$this->call(
			"GET",
			"/user/utilisateur_actif_normal",
			[],
			[],
			[],
			["HTTP_Authorization" => "Bearer " . $tokenUtilisateurActifNormal],
		);

		$this->assertResponseStatus(401);
	}

	public function test_étant_donné_un_token_avec_un_url_lorsquon_effectue_une_requête_a_un_url_valide_on_obtient_un_code_200()
	{
		$ressources = [
			"test" => [
				"url" => "^user/utilisateur_actif_normal$",
				"method" => "get",
			],
		];
		$tokenUtilisateurActifNormal = GénérateurDeToken::get_instance()->générer_token(
			"utilisateur_actif_normal",
			0,
			$ressources,
		);
		$this->call(
			"GET",
			"/user/utilisateur_actif_normal",
			[],
			[],
			[],
			["HTTP_Authorization" => "Bearer " . $tokenUtilisateurActifNormal],
		);

		$this->assertResponseStatus(200);
	}

	public function test_étant_donné_un_token_avec_un_url_lorsquon_effectue_une_requête_a_un_url_valide_mais_non_autorisée_on_obtient_un_code_403()
	{
		$ressources = [
			"test" => [
				"url" => "^user/utilisateur_actif_normal$",
				"method" => "post",
			],
		];
		$tokenUtilisateurActifNormal = GénérateurDeToken::get_instance()->générer_token(
			"utilisateur_actif_normal",
			0,
			$ressources,
		);
		$this->call(
			"POST",
			"/user/utilisateur_actif_normal/cles",
			[],
			[],
			[],
			["HTTP_Authorization" => "Bearer " . $tokenUtilisateurActifNormal],
		);

		$this->assertResponseStatus(403);
	}

	public function test_étant_donné_un_token_pour_un_utilisateur_actif_normal_lorsquon_effectue_une_requête_pour_accéder_aux_ressources_dun_autre_utilisateur_on_obtient_une_erreur_403()
	{
		$this->call(
			"GET",
			"/user/autre_utilisateur",
			[],
			[],
			[],
			[
				"HTTP_Authorization" => "Bearer " . $this->tokenUtilisateurActifNormal,
			],
		);

		$this->assertResponseStatus(403);
	}

	public function test_étant_donné_un_token_ressource_qui_contient_différents_url_lorsquon_effectue_une_requête_à_une_ressource_autorisée_on_obtient_200()
	{
		$ressources = [
			"test1" => ["url" => "^autre/ressource_test$", "method" => "get"],
			"test2" => ["url" => "^user/autre_utilisateur$", "method" => "get"],
			"test3" => ["url" => "^ressource/autre_test$", "method" => ".*"],
		];
		$tokenRessource = GénérateurDeToken::get_instance()->générer_token("autre_utilisateur", 0, $ressources);

		$this->call(
			"GET",
			"user/autre_utilisateur",
			["tkres" => $tokenRessource],
			[],
			[],
			[
				"HTTP_Authorization" => "Bearer " . $this->tokenUtilisateurActifNormal,
			],
		);

		$this->assertResponseStatus(200);
	}

	public function test_étant_donné_un_token_ressource_qui_contient_différents_url_lorsquon_effectue_une_requête_à_une_ressource_non_autorisée_on_obtient_403()
	{
		$ressources = [
			"test1" => [
				"url" => "^user/autre_utilisateur/avancements$",
				"method" => "get",
			],
			"test2" => ["url" => "^user/autre_utilisateur$", "method" => "get"],
			"test3" => [
				"url" => "^user/autre_utilisateur/relationships/avancement$/",
				"method" => "post",
			],
		];
		$tokenRessource = GénérateurDeToken::get_instance()->générer_token("autre_utilisateur", 0, $ressources);

		$this->call(
			"POST",
			"/user/autre_utilisateur/cles",
			["tkres" => $tokenRessource],
			[],
			[],
			[
				"HTTP_Authorization" => "Bearer " . $this->tokenUtilisateurActifNormal,
			],
		);

		$this->assertResponseStatus(403);
	}

	public function test_étant_donné_un_utilisateur_malveillant_lorsquon_fabrique_un_token_pour_accéder_aux_ressources_dun_utilisateur_innocent_on_obtient_une_erreur_403()
	{
		$ressourcesUtilisateurInnocent = [
			"test" => [
				"url" => "^user\/utilisateur_innocent$",
				"method" => "post",
			],
		];

		$responseTokenCtl = $this->call(
			"POST",
			"/user/utilisateur_malveillant/tokens",
			[
				"data" => [
					"ressources" => $ressourcesUtilisateurInnocent,
					"expiration" => 0,
				],
			],
			[],
			[],
			[
				"HTTP_Authorization" => "Basic " . base64_encode("utilisateur_malveillant:password"),
			],
		);

		$tokenJson = json_decode($responseTokenCtl->getContent(), false);
		$tokenContrefait = $tokenJson->data->attributes->jwt;
		$réponse = $this->call(
			"GET",
			"/user/utilisateur_innocent",
			[
				"tkres" => $tokenContrefait,
			],
			[],
			[],
			[
				"HTTP_Authorization" => "Basic " . base64_encode("utilisateur_malveillant:password"),
			],
		);

		$this->assertResponseStatus(403);
	}

	public function test_étant_donné_un_token_ressource_qui_contient_seulement_une_méthode_POST_lorsquon_effectue_une_requête_avec_GET_on_obtient_un_code_403()
	{
		$ressources = ["test" => ["url" => ".*", "", "method" => "post"]];
		$tokenRessource = GénérateurDeToken::get_instance()->générer_token("autre_utilisateur", 0, $ressources);

		$this->call(
			"GET",
			"/user/autre_utilisateur",
			["tkres" => $tokenRessource],
			[],
			[],
			[
				"HTTP_Authorization" => "Bearer " . $this->tokenUtilisateurActifNormal,
			],
		);

		$this->assertResponseStatus(403);
	}

	public function test_étant_donné_un_token_expiré_et_un_token_ressource_valide_lorsquon_effectue_une_requête_on_obtient_401()
	{
		$tokenUtilisateurActifNormal = GénérateurDeToken::get_instance()->générer_token(
			"utilisateur_actif_normal",
			time() - 1,
		);
		$ressources = [
			"test" => ["url" => "^user/autre_utilisateur$", "method" => "get"],
		];
		$tokenRessource = GénérateurDeToken::get_instance()->générer_token("autre_utilisateur", 0, $ressources);

		$this->call(
			"GET",
			"/user/autre_utilisateur",
			["tkres" => $tokenRessource],
			[],
			[],
			["HTTP_Authorization" => "Bearer " . $tokenUtilisateurActifNormal],
		);

		$this->assertResponseStatus(401);
	}

	public function test_étant_donné_un_token_valide_et_un_token_ressource_expiré_lorsquon_effectue_une_requête_pour_ses_propres_ressources_on_obtient_200()
	{
		$ressources = [
			"test" => ["url" => "^user/autre_utilisateur$", "method" => "get"],
		];
		$tokenRessource = GénérateurDeToken::get_instance()->générer_token(
			"autre_utilisateur",
			time() - 1,
			$ressources,
		);

		$this->call(
			"GET",
			"/user/utilisateur_actif_normal",
			["tkres" => $tokenRessource],
			[],
			[],
			[
				"HTTP_Authorization" => "Bearer " . $this->tokenUtilisateurActifNormal,
			],
		);

		$this->assertResponseStatus(200);
	}

	public function test_étant_donné_un_token_avec_une_mauvaise_signature_et_un_token_ressource_valide_lorsquon_effectue_une_requête_on_obtient_401()
	{
		$ressources = [
			"test" => ["url" => "^user/autre_utilisateur$", "method" => "get"],
		];
		$payload = [
			"username" => "utilisateur_actif_normal",
			"current" => time(),
			"expired" => 0,
			"ressources" => $ressources,
			"version" => 1,
		];

		$tokenUtilisateurActifNormal = JWT::encode($payload, "mauvais_secret", "HS256");
		$tokenRessource = GénérateurDeToken::get_instance()->générer_token("autre_utilisateur", 0, $ressources);

		$this->call(
			"GET",
			"/user/autre_utilisateur",
			["tkres" => $tokenRessource],
			[],
			[],
			["HTTP_Authorization" => "Bearer " . $tokenUtilisateurActifNormal],
		);

		$this->assertResponseStatus(401);
	}

	public function test_étant_donné_un_token_valide_et_un_token_ressource_expiré_lorsquon_requiert_une_ressource_dun_autre_utilisateur_on_obtient_403()
	{
		$ressources = [
			"test" => ["url" => "^user/autre_utilisateur$", "method" => "get"],
		];
		$tokenRessource = GénérateurDeToken::get_instance()->générer_token(
			"autre_utilisateur",
			time() - 1,
			$ressources,
		);

		$this->call(
			"GET",
			"/user/autre_utilisateur",
			["tkres" => $tokenRessource],
			[],
			[],
			[
				"HTTP_Authorization" => "Bearer " . $this->tokenUtilisateurActifNormal,
			],
		);

		$this->assertResponseStatus(403);
	}

	public function test_étant_donné_un_token_valide_et_un_token_ressource_avec_une_mauvaise_signature_lorsquon_requiert_une_ressource_dun_autre_utilisateur_on_obtient_403()
	{
		$ressources = [
			"test" => ["url" => "^user/autre_utilisateur$", "method" => "get"],
		];
		$payload = [
			"username" => "autre_utilisateur",
			"current" => time(),
			"expired" => 0,
			"ressources" => $ressources,
			"version" => 1,
		];
		$tokenRessource = JWT::encode($payload, "mauvais_secret", "HS256");

		$this->call(
			"GET",
			"/user/autre_utilisateur",
			["tkres" => $tokenRessource],
			[],
			[],
			[
				"HTTP_Authorization" => "Bearer " . $this->tokenUtilisateurActifNormal,
			],
		);

		$this->assertResponseStatus(403);
	}

	public function test_étant_donné_un_token_valide_et_un_token_ressource_mal_formaté_sans_ressources_lorsquon_requiert_une_ressource_dun_autre_utilisateur_on_obtient_403()
	{
		$tokenRessource = GénérateurDeToken::get_instance()->générer_token("autre_utilisateur", 0, []);

		$this->call(
			"GET",
			"/user/autre_utilisateur",
			["tkres" => $tokenRessource],
			[],
			[],
			[
				"HTTP_Authorization" => "Bearer " . $this->tokenUtilisateurActifNormal,
			],
		);

		$this->assertResponseStatus(403);
	}

	public function test_étant_donné_un_token_valide_et_un_token_ressource_mal_formaté_sans_url_lorsquon_requiert_une_ressource_dun_autre_utilisateur_on_obtient_403()
	{
		$ressources = [
			"ressources" => ["test" => ["url" => "", "method" => ".*"]],
		];
		$tokenRessource = GénérateurDeToken::get_instance()->générer_token("autre_utilisateur", 0, $ressources);

		$this->call(
			"GET",
			"/user/autre_utilisateur",
			["tkres" => $tokenRessource],
			[],
			[],
			[
				"HTTP_Authorization" => "Bearer " . $this->tokenUtilisateurActifNormal,
			],
		);

		$this->assertResponseStatus(403);
	}

	public function test_étant_donné_un_token_valide_et_un_token_ressource_mal_formaté_sans_méthode_lorsquon_requiert_une_ressource_dun_autre_utilisateur_on_obtient_403()
	{
		$ressources = [
			"ressources" => ["test" => ["url" => ".*", "method" => ""]],
		];
		$tokenRessource = GénérateurDeToken::get_instance()->générer_token("autre_utilisateur", 0, $ressources);

		$this->call(
			"GET",
			"/user/autre_utilisateur",
			["tkres" => $tokenRessource],
			[],
			[],
			[
				"HTTP_Authorization" => "Bearer " . $this->tokenUtilisateurActifNormal,
			],
		);

		$this->assertResponseStatus(403);
	}

	public function test_étant_donné_un_utilisateur_inactif_lorsquon_tente_de_créer_un_token_on_obtient_une_erreur_401()
	{
		$token = [
			"data" => ["données" => "une donnée"],
			"ressources" => [
				"ressources" => ["url" => "test", "method" => "POST"],
			],
			"expiration" => 0,
		];

		$this->call(
			"POST",
			"/user/utilisateur_inactif_normal/tokens",
			["data" => $token],
			[],
			[],
			[
				"HTTP_Authorization" => "Bearer " . $this->tokenUtilisateurInactifNormal,
			],
		);

		$this->assertResponseStatus(401);
	}

	public function test_étant_donné_un_utilisateur_inactif_et_un_token_valide_lorsquon_requiert_une_ressource_protégée_on_obtient_une_erreur_401()
	{
		$ressources = ["permissions" => ["url" => ".*", "method" => ""]];
		$tokenRessource = GénérateurDeToken::get_instance()->générer_token(
			"utilisateur_inactif_normal",
			0,
			$ressources,
		);

		$this->call(
			"GET",
			"/user/utilisateur_inactif_normal",
			[],
			[],
			[],
			[
				"HTTP_Authorization" => "Bearer " . $this->tokenUtilisateurInactifNormal,
			],
		);

		$this->assertResponseStatus(401);
	}
	public function test_étant_donné_un_utilisateur_inactif_et_un_mdp_valide_lorsquon_requiert_une_ressource_protégée_on_obtient_une_erreur_401()
	{
		$this->call(
			"GET",
			"/user/utilisateur_inactif_normal",
			[],
			[],
			[],
			[
				"HTTP_Authorization" => "Bearer " . $this->tokenUtilisateurInactifNormal,
			],
		);

		$this->assertResponseStatus(401);
	}

	public function test_étant_donné_un_utilisateur_inactif_et_un_token_valide_lorsquon_requiert_une_ressource_non_protégée_on_obtient_un_code_401()
	{
		$this->call(
			"GET",
			"/",
			[],
			[],
			[],
			[
				"HTTP_Authorization" => "Bearer " . $this->tokenUtilisateurInactifNormal,
			],
		);

		$this->assertResponseStatus(401);
	}

	public function test_étant_donné_un_utilisateur_actif_et_un_mdp_incorrect_lorsquon_requiert_une_ressource_non_protégée_on_obtient_un_code_401()
	{
		$this->call(
			"GET",
			"/",
			[],
			[],
			[],
			[
				"HTTP_Authorization" => "Basic " . base64_encode("utilisateur_actif_normal:tata"),
			],
		);

		$this->assertResponseStatus(401);
	}

	public function test_étant_donné_un_utilisateur_en_attente_de_validation_lorsquon_tente_de_créer_un_token_on_obtient_un_code_403()
	{
		$this->call(
			"POST",
			"/user/utilisateur_en_attente_normal/tokens",
			[
				"token" => "{un_token}",
			],
			[],
			[],
			[
				"HTTP_Authorization" => "Bearer " . $this->tokenUtilisateurEnAttenteNormal,
			],
		);

		$this->assertResponseStatus(403);
	}

	public function test_étant_donné_un_utilisateur_non_validé_et_un_token_ressource_valide_pour_des_ressources_d_autrui_lorsquon_tente_dutiliser_le_token_on_obtient_une_erreur_403()
	{
		$ressources = [
			"permissions" => [
				"url" => "/user/utilisateur_en_attente_normal",
				"method" => "^POST$",
			],
		];
		$tokenRessource = GénérateurDeToken::get_instance()->générer_token("autre_utilisateur", 0, $ressources);

		$this->call(
			"GET",
			"/user/autre_utilisateur",
			[
				"état" => "1",
				"tkres" => $tokenRessource,
			],
			[],
			[],
			[
				"HTTP_Authorization" => "Bearer " . $this->tokenUtilisateurEnAttenteNormal,
			],
		);

		$this->assertResponseStatus(403);
	}

	public function test_étant_donné_un_utilisateur_non_validé_et_un_token_ressource_valide_pour_ses_propres_ressources_lorsquon_tente_de_modifier_son_état_on_obtient_un_code_200()
	{
		$ressources = [
			"permissions" => [
				"url" => "user/utilisateur_en_attente_normal",
				"method" => "^PATCH$",
			],
		];
		$tokenRessource = GénérateurDeToken::get_instance()->générer_token(
			"utilisateur_en_attente_normal",
			0,
			$ressources,
		);
		$this->call(
			"PATCH",
			"/user/utilisateur_en_attente_normal",
			[
				"état" => "actif",
			],
			[],
			[],
			["HTTP_Authorization" => "Bearer " . $tokenRessource],
		);

		$this->assertResponseStatus(200);
	}

	//Authentification par token avec fingerprint
	public function test_étant_donné_un_token_avec_fingerprint_lorsquon_effectue_une_requête_avec_le_cookie_correspondant_on_obtient_la_ressource()
	{
		$this->call(
			"GET",
			"/user/utilisateur_actif_normal",
			[],
			[
				"contexte_token" => "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
			],
			[],
			["HTTP_Authorization" => "Bearer " . $this->tokenAvecFingerprint],
		);

		$this->assertResponseStatus(200);
	}

	public function test_étant_donné_un_token_avec_fingerprint_lorsquon_effectue_une_requête_sans_le_cookie_correspondant_on_obtient_une_erreur_401()
	{
		$this->call(
			"GET",
			"/user/utilisateur_actif_normal",
			[],
			[],
			[],
			["HTTP_Authorization" => "Bearer " . $this->tokenAvecFingerprint],
		);

		$this->assertResponseStatus(401);
	}

	public function test_étant_donné_un_token_avec_fingerprint_lorsquon_effectue_une_requête_avec_un_cookie_invalide_on_obtient_une_erreur_401()
	{
		$this->call(
			"GET",
			"/user/utilisateur_actif_normal",
			[],
			["contexte_token" => "contexte invalide"],
			[],
			["HTTP_Authorization" => "Bearer " . $this->tokenAvecFingerprint],
		);

		$this->assertResponseStatus(401);
	}

	//Authentification par clé
	public function test_étant_donné_un_utilisateur_existant_et_une_clé_dauthentification_valide_fournie_par_entête_lorsquon_effectue_une_requête_on_obtient_la_ressource()
	{
		$this->call(
			"GET",
			"/",
			[],
			[],
			[],
			[
				"HTTP_Authorization" => "Key " . base64_encode("utilisateur_actif_normal:cleValide01:secret"),
			],
		);

		$this->assertResponseStatus(200);
	}

	public function test_étant_donné_un_utilisateur_existant_et_une_clé_dauthentification_valide_fournie_par_cookie_lorsquon_effectue_une_requête_on_obtient_la_ressource()
	{
		$this->call(
			"GET",
			"/",
			[],
			["authKey_secret" => "secret"],
			[],
			[
				"HTTP_Authorization" => "Key " . base64_encode("utilisateur_actif_normal:cleValide01"),
			],
		);

		$this->assertResponseStatus(200);
	}
}
