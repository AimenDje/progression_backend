<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
   |--------------------------------------------------------------------------
   | Application Routes
   |--------------------------------------------------------------------------
   |
   | Here is where you can register all of the routes for an application.
   | It is a breeze. Simply tell Lumen the URIs it should respond to
   | and give it the Closure to call when that URI is requested.
   |
 */

$router->options("{all:.*}", [
	"middleware" => "cors",
	function () {
		return response("");
	},
]);

$router->group(["middleware" => ["auth_optionnelle"]], function () use ($router) {
	// Configuration serveur
	$router->get("/", "ConfigCtl@get");

	// Inscription
	$router->put("/user/{username}", "ContrôleurFrontal@put_user");
	$router->post("/users", "ContrôleurFrontal@post_user");
});

$router->group(["middleware" => ["auth"]], function () use ($router) {
	// Ébauche
	$router->get("/ebauche/{question_uri}/{langage}", "ÉbaucheCtl@get");

	// Question
	$router->get("/question/{uri}", "QuestionCtl@get");
	$router->get("/question/{uri}/relationships/ebauches", "NotImplementedCtl@get");
	$router->get("/question/{uri}/relationships/tests", "NotImplementedCtl@get");
	$router->get("/question/{uri}/ebauches", "NotImplementedCtl@get");
	$router->get("/question/{uri}/tests", "NotImplementedCtl@get");

	// Test
	$router->get("/test/{question_uri}/{numero:[[:digit:]]+}", "TestCtl@get");

	// Résultat
	$router->post("/question/{uri}/resultats", "ContrôleurFrontal@post_résultat");
});

$router->group(["middleware" => ["auth", "étatValidé"]], function () use ($router) {
	// Token
	$router->post("/user/{username}/tokens", "ContrôleurFrontal@post_token");
});

$router->group(["middleware" => ["auth", "permissionsRessources"]], function () use ($router) {
	// Avancement
	$router->get("/avancement/{username}/{question_uri}", "AvancementCtl@get");
	$router->get("/avancement/{username}/{chemin}/relationships/tentatives", "NotImplementedCtl@get");
	$router->get("/avancement/{username}/{chemin}/tentatives", "NotImplementedCtl@get");

	$router->put("/avancement/{username}/{question_uri}", "ContrôleurFrontal@put_avancement");
	$router->patch("/avancement/{username}/{question_uri}", "ContrôleurFrontal@patch_avancement");
	$router->post("/user/{username}/avancements", "ContrôleurFrontal@post_avancement");

	// Avancements
	$router->get("/user/{username}/avancements", "AvancementsCtl@get");

	// Clé
	$router->post("/user/{username}/cles", "ContrôleurFrontal@post_clé");
	$router->get("/cle/{username}/{nom}", "CléCtl@get");

	// Commentaire
	$router->post(
		"/tentative/{username}/{question_uri}/{timestamp:[[:digit:]]{10}}/commentaires",
		"CommentaireCtl@post",
	);
	$router->get(
		"/commentaire/{username}/{question_uri}/{timestamp:[[:digit:]]{10}}/{numero}",
		"NotImplementedCtl@get",
	);

	// Résultat
	$router->post("/test/{username}/{question_uri}/{numero:[[:digit:]]+}", "NotImplementedCtl@get");

	// Sauvegarde
	$router->post("/avancement/{username}/{question_uri}/sauvegardes", "ContrôleurFrontal@post_sauvegarde");
	$router->get("/sauvegarde/{username}/{question_uri}/{langage}", "SauvegardeCtl@get");
	$router->get("/avancement/{username}/{question_uri}/sauvegardes", "NotImplementedCtl@get");

	// Tentative
	$router->post("/avancement/{username}/{question_uri}/tentatives", "ContrôleurFrontal@post_tentative");
	$router->get("/tentative/{username}/{question_uri}/{timestamp:[[:digit:]]{10}}", "TentativeCtl@get");
	$router->get(
		"/tentative/{username}/{question_uri}/{timestamp:[[:digit:]]{10}}/relationships/resultats",
		"NotImplementedCtl@get",
	);
	$router->get("/tentative/{username}/{question_uri}/{timestamp:[[:digit:]]{10}}/resultats", "NotImplementedCtl@get");

	// User
	$router->get("/user/{username}", "UserCtl@get");
	$router->patch("/user/{username}", "ContrôleurFrontal@patch_user");
	$router->get("/user/{username}/relationships/avancements", "NotImplementedCtl@get");

	// Banque
	$router->get("/user/{username}/banques", "BanqueCtl@get");
    $router->post("/user/{username}/banque", "BanqueCtl@post");
});
