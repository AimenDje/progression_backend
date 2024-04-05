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

namespace progression\http\contrôleur;

use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\Rules\Enum;
use progression\domaine\entité\Avancement;
use progression\domaine\entité\user\{User, État, Rôle};
use progression\domaine\interacteur\{ObtenirUserInt, InscriptionInt, ModifierUserInt, SauvegarderUtilisateurInt};
use progression\http\transformer\UserTransformer;
use progression\http\transformer\dto\UserDTO;
use progression\util\Encodage;

class UserCtl extends Contrôleur
{
	public function get(string $username): JsonResponse
	{
		Log::debug("UserCtl.get. Params : ", [$username]);

		$user = $this->obtenir_user($username);
		$réponse = $this->valider_et_préparer_réponse($user, $user->username ?? "");

		Log::debug("UserCtl.get. Retour : ", [$réponse]);
		return $réponse;
	}

	/**
	 * @return array<string>
	 */
	public static function get_liens(string $username): array
	{
		$urlBase = Contrôleur::$urlBase;
		return [
			"self" => "{$urlBase}/user/{$username}",
			"avancements" => "{$urlBase}/user/{$username}/avancements",
			"clés" => "{$urlBase}/user/{$username}/cles",
			"tokens" => "{$urlBase}/user/{$username}/tokens",
		];
	}

	protected function obtenir_user(string $username): User|null
	{
		Log::debug("UserCtl.obtenir_user. Params : ", [$username]);

		$userInt = new ObtenirUserInt();

		$user = $userInt->get_user(username: $username, includes: $this->get_includes());
		if ($user) {
			$user->avancements = $this->réencoder_uris($user->avancements);
		}

		Log::debug("UserCtl.obtenir_user. Retour : ", [$user]);
		return $user;
	}

	protected function valider_et_préparer_réponse(User|null $user, string $username): JsonResponse
	{
		Log::debug("UserCtl.valider_et_préparer_réponse. Params : ", [$user]);

		if ($user) {
			$liens = self::get_liens($user->username);
			$dto = new UserDTO(id: $username, objet: $user, liens: $liens);

			$réponse = $this->item($dto, new UserTransformer());
		} else {
			$réponse = null;
		}

		Log::debug("UserCtl.valider_et_préparer_réponse. Retour : ", [$réponse]);

		return $this->préparer_réponse($réponse);
	}

	/**
	 * @param array<Avancement> $avancements
	 * @return array<Avancement>
	 */
	private function réencoder_uris(array $avancements): array
	{
		$avancements_réencodés = [];

		foreach ($avancements as $uri => $avancement) {
			$avancements_réencodés[Encodage::base64_encode_url($uri)] = $avancement;
		}

		return $avancements_réencodés;
	}

	/**
	 * @param array<mixed> $attributs
	 */
	public function post(array $attributs): JsonResponse
	{
		Log::debug("UserCréationCtl.post. Params : ", [$attributs]);

		if (array_key_exists("username", $attributs)) {
			$réponse = $this->créer_user($attributs, $attributs["username"]);
		} else {
			$réponse = $this->réponse_json(
				[
					"erreur" => [
						"username" => ["Le champ username est obligatoire."],
					],
				],
				400,
			);
		}

		Log::debug("UserCréationCtl.post. Retour : ", [$réponse]);
		return $réponse;
	}

	/**
	 * @param array<mixed> $attributs
	 */
	public function put(string $username, array $attributs): JsonResponse
	{
		Log::debug("UserCréationCtl.put. Params : ", [$username, $attributs]);

		$réponse = $this->créer_user($attributs, $username);

		Log::debug("UserCréationCtl.put. Retour : ", [$réponse]);
		return $réponse;
	}

	/**
	 * @param array<mixed> $attributs
	 */
	private function créer_user(array $attributs, string $username): JsonResponse
	{
		$auth_local = config("authentification.local") !== false;

		if ($auth_local) {
			return $this->effectuer_inscription_locale($attributs, $username);
		} else {
			return $this->effectuer_inscription_non_locale($attributs, $username);
		}
	}

	/**
	 * @param array<mixed> $attributs
	 */
	private function effectuer_inscription_non_locale(array $attributs, string $username): JsonResponse
	{
		$auth_ldap = config("authentification.ldap") === true;

		if ($auth_ldap) {
			$réponse = $this->réponse_json(["erreur" => "Inscription locale non supportée."], 403);
		} else {
			$erreurs = $this->valider_paramètres_sans_authentification($attributs, $username);
			if (count($erreurs) > 0) {
				$réponse = $this->réponse_json(["erreur" => $erreurs], 400);
			} else {
				$user_retourné = $this->effectuer_inscription_sans_mdp($attributs);
				$id = array_key_first($user_retourné);
				$réponse = $this->valider_et_préparer_réponse($user_retourné[$id], $id);
			}
		}

		return $réponse;
	}

	/**
	 * @param array<mixed> $attributs
	 */
	private function effectuer_inscription_locale(array $attributs, string $username): JsonResponse
	{
		$erreurs = $this->valider_paramètres_inscription_locale($attributs, $username);

		if (count($erreurs) > 0) {
			$réponse = $this->réponse_json(["erreur" => $erreurs], 400);
		} else {
			$user_retourné = $this->effectuer_inscription($attributs);

			$id = array_key_first($user_retourné);
			$réponse = $this->valider_et_préparer_réponse($user_retourné[$id], $id);
		}

		return $réponse;
	}

	/**
	 * @param array<mixed> $attributs
	 * @return non-empty-array<string,User>
	 */
	private function effectuer_inscription(array $attributs): array
	{
		Log::debug("UserCréationCtl.effectuer_inscription. Params : ", [$attributs]);

		$username = $attributs["username"];
		$courriel = $attributs["courriel"];
		$password = $attributs["password"] ?? null;

		$inscriptionInt = new InscriptionInt();
		$user = $inscriptionInt->effectuer_inscription_locale($username, $courriel, $password);

		Log::debug("UserCréationCtl.effectuer_inscription. Retour : ", [$user]);

		return $user;
	}

	/**
	 * @param array<mixed> $attributs
	 * @return non-empty-array<string,User>
	 */
	private function effectuer_inscription_sans_mdp(array $attributs): array
	{
		Log::debug("UserCréationCtl.effectuer_inscription_sans_mdp. Params : ", [$attributs]);

		$username = $attributs["username"];

		$inscriptionInt = new InscriptionInt();
		$user = $inscriptionInt->effectuer_inscription_sans_mdp($username);

		Log::debug("UserCréationCtl.effectuer_inscription_sans_mdp. Retour : ", [$user]);

		return $user;
	}

	/**
	 * @param array<mixed> $attributs
	 */
	private function valider_paramètres_inscription_locale(array $attributs, string $username): MessageBag
	{
		Log::debug("UserCréationCtl.valider_paramètres : ", $attributs);

		//Vérifie si les paramètres permettent un renvoi de courriel
		$réponse = $this->valider_paramètres_renvoi_courriel($attributs);

		if (!$réponse->isEmpty()) {
			//Si le renvoi de courriel n'est pas possible, vérifie si les paramètres permettent une nouvelle inscription
			$réponse = $this->valider_paramètres_nouvelle_inscritption($attributs, $username);
		}

		Log::debug("UserCréationCtl.valider_paramètres. Retour : ", [$réponse]);
		return $réponse;
	}

	/**
	 * @param array<mixed> $attributs
	 */
	private function valider_paramètres_renvoi_courriel(array $attributs): MessageBag
	{
		Log::debug("UserCréationCtl.valider_paramètres_renvoi_courriel : ", $attributs);

		// Demande de retour de courriel de validation
		$validateur = Validator::make(
			$attributs,
			[
				"username" => "required|regex:/^\w{1,64}$/u|exists:progression\dao\models\UserMdl,username",
				"courriel" => "required|email|exists:progression\dao\models\UserMdl,courriel",
				"password" => "prohibited",
			],
			[
				"required" => "Le champ :attribute est obligatoire.",
			],
		);

		$réponse = $validateur->errors();

		Log::debug("UserCréationCtl.valider_paramètres_renvoi_courriel. Retour : ", [$réponse]);

		return $réponse;
	}

	/**
	 * @param array<mixed> $attributs
	 */
	private function valider_paramètres_sans_authentification(array $attributs, string $username): MessageBag
	{
		Log::debug("UserCréationCtl.valider_paramètres_sans_authentification : ", $attributs);

		$validateur = Validator::make(
			array_merge($attributs, ["username_p" => $username]),
			[
				"username" => "required|same:username_p|regex:/^\w{1,64}$/u",
				"courriel" => "prohibited",
				"password" => "prohibited",
			],
			[
				"required" => "Le champ :attribute est obligatoire.",
				"username.same" => "Le nom d'utilisateur diffère de :attribute.",
				"username.regex" => "Le nom d'utilisateur doit être composé de 2 à 64 caractères alphanumériques.",
			],
		);

		$réponse = $validateur->errors();

		Log::debug("UserCréationCtl.valider_paramètres_sans_authentification. Retour : ", [$réponse]);
		return $réponse;
	}

	/**
	 * @param array<mixed> $attributs
	 */
	private function valider_paramètres_nouvelle_inscritption(array $attributs, string $username): MessageBag
	{
		Log::debug("UserCréationCtl.valider_paramètres_nouvelle_inscritption : ", $attributs);

		$validateur = Validator::make(
			array_merge($attributs, ["username_p" => $username]),
			[
				"username" => "required|same:username_p|regex:/^\w{1,64}$/u",
				"courriel" => "required|email",
				"password" => "required|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/u",
			],
			[
				"username.same" => "Le nom d'utilisateur diffère de :attribute.",
				"username.regex" => "Le nom d'utilisateur doit être composé de 2 à 64 caractères alphanumériques.",
				"courriel.email" => "Le champ courriel doit être un courriel valide.",
				"password.regex" => "Le mot de passe doit contenir au moins 8 caractères, une majuscule et un chiffre.",
				"required" => "Le champ :attribute est obligatoire.",
			],
		);

		$réponse = $validateur->errors();

		Log::debug("UserCréationCtl.valider_paramètres_nouvelle_inscritption. Retour : ", [$réponse]);
		return $réponse;
	}

	/**
	 * @param array<mixed> $attributs
	 */
	public function patch(string $username, array $attributs): JsonResponse
	{
		Log::debug("UserModificationCtl.patch. Params : ", [$username, $attributs]);

		$réponse = null;
		$erreurs = $this->valider_paramètres_modification($attributs);
		if (!$erreurs->isEmpty()) {
			return $this->réponse_json(["erreur" => $erreurs], 400);
		}

		$user = $this->obtenir_user($username);
		if (!$user) {
			$réponse = $this->préparer_réponse(null);
		} else {
			$user = $this->modifier_user($username, $user, $attributs);
			$réponse = $this->valider_et_préparer_réponse($user, $username);
		}

		Log::debug("UserModificationCtl.patch. Retour : ", [$réponse]);
		return $réponse;
	}

	/**
	 * @param array<mixed> $attributs
	 */
	private function modifier_user(string $username, User $user, $attributs): User
	{
		if (array_intersect(array_keys($attributs), ["courriel", "état", "préférences", "rôle"])) {
			$user_original = clone $user;
			$user_modifié = $this->modifier_entité($username, $user, $attributs);

			if ($user_modifié != $user_original) {
				$userInt = new SauvegarderUtilisateurInt();
				$user = $userInt->sauvegarder_user($username, $user)[$username];
			}
		}

		if (array_key_exists("password", $attributs)) {
			$this->modifier_mot_de_passe($user, $attributs["password"]);
		}

		return $user;
	}

	/**
	 * @param array<mixed> $attributs
	 */
	private function modifier_entité(string $username, User $user, array $attributs): User
	{
		if (array_key_exists("état", $attributs)) {
			$user = (new ModifierUserInt())->modifier_état($user, État::from($attributs["état"]));
		}
		if (array_key_exists("rôle", $attributs)) {
			$user = (new ModifierUserInt())->modifier_rôle($user, Rôle::from($attributs["rôle"]));
		}
		if (array_key_exists("préférences", $attributs)) {
			$user = (new ModifierUserInt())->modifier_préférences($user, $attributs["préférences"]);
		}
		if (array_key_exists("courriel", $attributs)) {
			$user = (new ModifierUserInt())->modifier_courriel($user, $attributs["courriel"]);
		}

		return $user;
	}

	private function modifier_mot_de_passe(User $user, string $password): void
	{
		(new ModifierUserInt())->modifier_password($user, $password);
	}

	/**
	 * @param array<mixed> $attributs
	 */
	private function valider_paramètres_modification(array $attributs): MessageBag
	{
		$validateur = Validator::make(
			$attributs,
			[
				"préférences" => "sometimes|string|json|between:0,65535",
				"état" => ["sometimes", "string", new Enum(État::class)],
				"rôle" => ["sometimes", "string", new Enum(Rôle::class)],
				"courriel" => "sometimes|email",
				"password" => "sometimes|string|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/u",
			],
			[
				"json" => "Le champ :attribute doit être en format json.",
				"préférences.between" =>
					"Le champ :attribute " . mb_strlen($attributs["préférences"] ?? "") . " > :max caractères.",
				"password.regex" => "Le mot de passe doit contenir au moins 8 caractères, une majuscule et un chiffre.",
			],
		);

		return $validateur->errors();
	}
}
