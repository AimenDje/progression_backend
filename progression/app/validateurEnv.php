<?php
require_once __DIR__ . "/../vendor/autoload.php";

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
if (file_exists(__DIR__ . ".env")) {
	$dotenv->load();
}
$dotenv->required("APP_URL")->allowedRegexValues('(.*$)');
$dotenv->required("APP_NAME")->allowedRegexValues("(.*)");
$dotenv->required("APP_TIMEZONE")->allowedValues(["UTC"]);

$dotenv->required("JWT_SECRET")->allowedRegexValues("(.*)");
$dotenv->required("DB_HOST")->allowedRegexValues("(.*)");
$dotenv->required("DB_DATABASE")->allowedRegexValues("(.*)");
$dotenv->required("DB_USERNAME")->allowedRegexValues("([a-zA-Z0-9_]+)");
$dotenv->required("DB_PASSWORD")->allowedRegexValues("(.*)");

$dotenv->ifpresent("auth.local")->isBoolean();
$dotenv->ifpresent("auth.ldap")->isBoolean();

$dotenv->required("HTTP_ORIGIN")->allowedRegexValues("(.*)");

$dotenv->required("QUESTION_TAILLE_MAX")->isInteger();
$dotenv->required("TAILLE_CODE_MAX")->isInteger();
$dotenv->required("JWT_TTL")->isInteger();

$dotenv->required("COMPILEBOX_URL")->allowedRegexValues("(.*)");
$dotenv->required("COMPILEBOX_IMAGE_EXECUTEUR")->allowedRegexValues("(.*)");

$dotenv->ifpresent("MAIL_MAILER")->allowedValues(["smtp", "log", "no"]);
$dotenv->ifpresent("MAIL_PORT")->isInteger();
