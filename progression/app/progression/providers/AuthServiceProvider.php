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

namespace progression\providers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Validator as ValidatorImpl;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use progression\domaine\interacteur\{ObtenirUserInt, LoginInt, AccèsInterditException, ParamètreInvalideException};
use progression\domaine\entité\user\{User, État, Rôle};
use Firebase\JWT\JWT;
use Firebase\JWT\SignatureInvalidException;
use UnexpectedValueException;
use DomainException;

class AuthServiceProvider extends ServiceProvider
{
	public function register()
	{
	}

	public function boot()
	{
		Gate::guessPolicyNamesUsing(function ($modelClass) {
			if ($modelClass == "progression\domaine\entité\user\User") {
				return "acces-utilisateur";
			}
		});

		Gate::before(function ($user, $ability) {
			if ($user && $user->rôle == Rôle::ADMIN) {
				return true;
			}
		});

		// À corriger?
		// Cause : "Cannot access offset 'auth' on Illuminate\Contracts\Foundation\Application."
		// auth est définit dans bootstrap/app.php
		// @phpstan-ignore-next-line
		$this->app["auth"]->viaRequest("api", function (Request $request): User|null|false {
			// Fournit le User passé aux Gates à fins d'authentification et d'autorisation
			// Retourne un User authentifié ou null.
			// Retourne false si aucun indentifiant n'est reçu.
			$creds = $this->extraireCreds($request);
			if ($creds === false) {
				return false;
			}

			$validateur = $this->valider_paramètres($creds);
			if ($validateur->fails()) {
				throw new ParamètreInvalideException($validateur->errors());
			}

			if (array_key_exists("token", $creds)) {
				$contexte = $this->extraireCookie($request, "contexte_token");
				$fingerprint = $contexte ? hash("sha256", $contexte) : null;
				return $this->authentifier_par_token($creds, $fingerprint);
			} else {
				return $this->authentifier_par_mdp($creds);
			}
		});

		Gate::define("acces-utilisateur", function ($user, $request) {
			$creds = $this->extraireCreds($request);
			if ($creds === false || empty($creds["identifiant"])) {
				return false;
			}

			if (array_key_exists("token", $creds)) {
				$token = $creds["token"];
				if (empty($token)) {
					return false;
				}

				$tokenDécodé = $this->décoderToken($token);
				if (
					is_array($tokenDécodé) &&
					array_key_exists("ressources", $tokenDécodé) &&
					is_array($tokenDécodé["ressources"]) &&
					$this->vérifierRessourceAutorisée($tokenDécodé["ressources"] ?: null, $request) &&
					mb_strtolower($user->username) == mb_strtolower($request->username)
				) {
					return true;
				}
			} elseif (array_key_exists("key_name", $creds)) {
				return false; // TODO https://git.dti.crosemont.quebec/progression/progression_backend/-/issues/217
			} elseif (array_key_exists("password", $creds)) {
				return mb_strtolower($creds["identifiant"]) == mb_strtolower($request->username) &&
					$this->vérifier_état_user_actif($user);
			}

			return false;
		});

		Gate::define("acces-ressource", function ($user, $request) {
			$tokenRessource = $request->input("tkres");
			$tokenRessourceDécodé = $this->décoderToken($tokenRessource);
			if (
				is_array($tokenRessourceDécodé) &&
				array_key_exists("ressources", $tokenRessourceDécodé) &&
				is_array($tokenRessourceDécodé["ressources"]) &&
				mb_strtolower($request->username) == mb_strtolower($tokenRessourceDécodé["username"]) &&
				$this->vérifierExpirationToken($tokenRessourceDécodé) &&
				$this->vérifierRessourceAutorisée($tokenRessourceDécodé["ressources"] ?: null, $request)
			) {
				return true;
			}

			return false;
		});

		Gate::define("utilisateur-non-inactif", function ($user, $request) {
			return $user && $this->vérifier_état_user_non_inactif($user);
		});

		Gate::define("utilisateur-validé", function ($user, $request) {
			return $user && $user->état != État::EN_ATTENTE_DE_VALIDATION;
		});

		Gate::define("soumettre-tentative", function ($user, $username) {
			return $user && mb_strtolower($user->username) == mb_strtolower($username);
		});

		Gate::define("modifier-rôle-user-admin", function ($user) {
			return $user && $user->rôle == Rôle::ADMIN;
		});

		Gate::define("modifier-état-user-inactif", function ($user) {
			return $user && $user->rôle == Rôle::ADMIN;
		});

		Gate::define("créer-commentaire", function ($user, $créateur) {
			return ($user && $user->rôle == Rôle::ADMIN) || $user->username == $créateur;
		});

		Gate::define("valider-le-courriel", function ($user, $rôle_user_cible) {
			return !($rôle_user_cible == Rôle::ADMIN || getenv("MAIL_MAILER") == "no");
		});
	}

	/**
	 * @param array<string, string|null> $creds
	 */
	private function authentifier_par_mdp(array $creds): User|null
	{
		// Valide l'authentification par mot de passe ou clé
		$identifiant = $creds["identifiant"];
		if (empty($identifiant)) {
			return null;
		}

		$loginInt = new LoginInt();

		if (array_key_exists("key_name", $creds)) {
			$key_name = $creds["key_name"];
			$key_secret = $creds["key_secret"];
			$user = $loginInt->effectuer_login_par_clé($identifiant, $key_name, $key_secret);
		} else {
			$password = $creds["password"];
			$domaine = $creds["domaine"];
			$user = $loginInt->effectuer_login_par_identifiant($identifiant, $password, $domaine);
		}

		return $user;
	}

	/**
	 * @param array<string, string|null> $creds
	 */
	private function authentifier_par_token(array $creds, string|null $fingerprint): User|null
	{
		// Authentification par token
		$identifiant = $creds["identifiant"];
		if (empty($identifiant)) {
			return null;
		}

		$tokenEncodé = $creds["token"];
		if (empty($tokenEncodé)) {
			return null;
		}

		$tokenDécodé = $this->décoderToken($tokenEncodé);
		if (
			$tokenDécodé &&
			$this->vérifierExpirationToken($tokenDécodé) &&
			(!isset($tokenDécodé["fingerprint"]) || $tokenDécodé["fingerprint"] === $fingerprint)
		) {
			$obtenirUserInteracteur = new ObtenirUserInt();
			return $obtenirUserInteracteur->get_user($identifiant);
		}
		return null;
	}

	/**
	 * @return array<mixed>|null
	 */
	private function décoderToken(string $tokenEncodé): array|null
	{
		try {
			//JWT::decode fournit une stdClass, le moyen le plus simple de transformer en array
			//est de réencoder/décoder en json.
			// @phpstan-ignore-next-line
			return json_decode(json_encode(JWT::decode($tokenEncodé, getenv("JWT_SECRET"), ["HS256"])), true);
		} catch (UnexpectedValueException | SignatureInvalidException | DomainException $e) {
			Log::notice("Token invalide ${tokenEncodé}");
			return null;
		}
	}

	/**
	 * Extrait les identifiants de connexion à partir de l'entête Authorization.
	 *
	 * Si des secrets sont fournis, ils sont d'abord récupérés de l'entête et, à défaut, d'un cookie.
	 *
	 * @return array<string, string|null>|false Un tableau d'identifiants ou false si aucune entête d'authentification n'existe
	 *
	 * Si Authorization est de type Bearer, retourne : identifiant et token
	 * Si Authorization est de type Basic, retourne : identifiant, password et domaine
	 * Si Authorization est de type Key, retourne : identifiant, key_name et key_secret
	 */
	private function extraireCreds(Request $request): array|false
	{
		$authorization = strval(
			is_array($request->header("Authorization"))
				? $request->header("Authorization")[0]
				: $request->header("Authorization"),
		);

		if (empty($authorization)) {
			return false;
		}

		if (stripos($authorization, "bearer") === 0) {
			$creds = $this->décoderCreds_token($authorization);
		} elseif (stripos($authorization, "basic") === 0) {
			$creds = $this->décoderCreds_basic($authorization);
		} elseif (stripos($authorization, "key") === 0) {
			$creds = $this->décoderCreds_key($authorization);
			$creds["key_secret"] = $creds["key_secret"] ?? $this->extraireCookie($request, "authKey_secret");
		} else {
			throw new ParamètreInvalideException("Type d'authentification invalide.");
		}
		return $creds;
	}

	/**
	 * @return array<string, string>
	 */
	private function décoderCreds_token(string $creds_header): array
	{
		$tokenEncodé = trim(str_ireplace("bearer", "", $creds_header));
		$tokenDécodé = $this->décoderToken($tokenEncodé);
		if ($tokenDécodé && $this->vérifierExpirationToken($tokenDécodé)) {
			return [
				"identifiant" => $tokenDécodé["username"],
				"token" => $tokenEncodé,
			];
		} else {
			throw new AccèsInterditException("Token invalide ou expiré");
		}
	}

	/**
	 * @return array<string, string|null>
	 */
	private function décoderCreds_basic(string $creds_header): array
	{
		$creds_encodés = trim(str_ireplace("basic", "", $creds_header));
		$creds_décodés = base64_decode($creds_encodés);

		$creds_array = preg_split("/:/", $creds_décodés);

		return [
			"identifiant" => $creds_array[0] ?? null,
			"password" => $creds_array[1] ?? null,
			"domaine" => $creds_array[2] ?? null,
		];
	}

	/**
	 * @return array<string, string|null>
	 */
	private function décoderCreds_key(string $creds_header): array
	{
		$creds_encodés = trim(str_ireplace("key", "", $creds_header));
		$creds_décodés = base64_decode($creds_encodés);

		$creds_array = [];

		$creds_array = preg_split("/:/", $creds_décodés);
		return [
			"identifiant" => $creds_array[0] ?? null,
			"key_name" => $creds_array[1] ?? null,
			"key_secret" => $creds_array[2] ?? null,
		];
	}

	private function extraireCookie(Request $request, string $nom): string|null
	{
		$cookie = $request->cookie($nom);
		if (is_array($cookie)) {
			return strval($cookie[0]);
		} elseif (is_string($cookie)) {
			return $cookie;
		}
		return null;
	}

	private function vérifierExpirationToken($token)
	{
		return time() < $token["expired"] || $token["expired"] === 0;
	}

	private function vérifierRessourceAutorisée($ressources, $request)
	{
		if ($ressources) {
			foreach ($ressources as $ressource) {
				if (
					is_array($ressource) &&
					array_key_exists("url", $ressource) &&
					strlen($ressource["url"]) > 0 &&
					array_key_exists("method", $ressource) &&
					strlen($ressource["method"]) > 0 &&
					preg_match("#" . $ressource["url"] . "#", $request->path()) &&
					preg_match("#" . $ressource["method"] . "#i", $request->method())
				) {
					return true;
				}
			}
		}
		return false;
	}

	private function vérifier_état_user_non_inactif(User $user): bool
	{
		return $user->état !== État::INACTIF;
	}

	private function vérifier_état_user_actif(User $user): bool
	{
		return $user->état === État::ACTIF;
	}

	/**
	 * @param array<string, string|null> $creds
	 */
	private function valider_paramètres(array $creds): ValidatorImpl
	{
		$validateur = Validator::make(
			$creds,
			[
				"identifiant" => "required|string|between:2,64",
				"key_secret" => "required_with:key_name",
				"key_name" => "alpha_dash:ascii",
			],
			[
				"required" => "Le champ :attribute est obligatoire.",
				"identifiant.regex" => "L'identifiant doit être un nom d'utilisateur ou un courriel valide.",
				"password.required_without_all" =>
					"Le champ password est obligatoire lorsque key_name ou token ne sont pas présents.",
				"key_secret.required_with" => "Le champ key_secret est obligatoire lorsque key_name est présent.",
				"key_secret.required" => "Le champ key_secret est obligatoire lorsque key_name est présent",
				"key_name.alpha_dash" => "Le champ key_name doit être alphanumérique 'a-Z0-9-_'",
			],
		)
			->sometimes("password", "required_without_all:key_name,token", function ($input) {
				$auth_local = getenv("AUTH_LOCAL") !== "false";
				$auth_ldap = getenv("AUTH_LDAP") === "true";

				return $auth_local || $auth_ldap;
			})
			->sometimes("identifiant", "regex:/^\w{2,64}$/u", function ($input) {
				$auth_local = getenv("AUTH_LOCAL") !== "false";
				$auth_ldap = getenv("AUTH_LDAP") === "true";

				return isset($input->key_name) || (!$auth_local && !$auth_ldap);
			})
			->sometimes("identifiant", ["regex:/^\w{2,64}$|^[^\s@]+@[^\s@]+\.[a-zA-Z]{2,}$/u"], function ($input) {
				$auth_local = getenv("AUTH_LOCAL") !== "false";
				$auth_ldap = getenv("AUTH_LDAP") === "true";

				return !isset($input->key_name) && ($auth_local || $auth_ldap);
			});

		return $validateur;
	}
}
