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

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use progression\domaine\interacteur\InscriptionInt;
use progression\domaine\entité\user\{User, État, Rôle};
use progression\http\transformer\UserTransformer;

class UserCréationCtl extends UserCtl
{
	public function post(Request $request): JsonResponse
	{
		Log::debug("UserCréationCtl.post. Params : ", $request->all());
		Log::info("{$request->ip()} - Tentative d'inscription : {$request->input("username")}");

		$username = $request->input("username");
		if ($username) {
			$réponse = $this->créer_user($request, $username);
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

	public function put(Request $request, string $username): JsonResponse
	{
		Log::debug("UserCréationCtl.put. Params : ", $request->all());
		Log::info("{$request->ip()} - Tentative d'inscription : {$request->input("username")}");

		$réponse = $this->créer_user($request, $username);

		Log::debug("UserCréationCtl.put. Retour : ", [$réponse]);
		return $réponse;
	}

	private function créer_user(Request $request, string $username): JsonResponse
	{
		$auth_local = config("authentification.local") !== false;

		if ($auth_local) {
			return $this->effectuer_inscription_locale($request, $username);
		} else {
			return $this->effectuer_inscription_non_locale($request, $username);
		}
	}

	private function effectuer_inscription_non_locale(Request $request, string $username): JsonResponse
	{
		$auth_ldap = config("authentification.ldap") === true;

		if ($auth_ldap) {
			$réponse = $this->réponse_json(["erreur" => "Inscription locale non supportée."], 403);
		} else {
			$erreurs = $this->valider_paramètres_sans_authentification($request, $username);
			if (count($erreurs) > 0) {
				$réponse = $this->réponse_json(["erreur" => $erreurs], 400);
			} else {
				$user_retourné = $this->effectuer_inscription_sans_mdp($request);
				$id = array_key_first($user_retourné);
				$réponse = $this->valider_et_préparer_réponse($user_retourné[$id], $id);
			}
		}

		return $réponse;
	}

	private function effectuer_inscription_locale(Request $request, string $username): JsonResponse
	{
		$erreurs = $this->valider_paramètres_inscription_locale($request, $username);

		if (count($erreurs) > 0) {
			$réponse = $this->réponse_json(["erreur" => $erreurs], 400);
		} else {
			$user_retourné = $this->effectuer_inscription($request);

			$id = array_key_first($user_retourné);
			$réponse = $this->valider_et_préparer_réponse($user_retourné[$id], $id);
		}

		return $réponse;
	}

	/**
	 * @return array<User>
	 */
	private function effectuer_inscription(Request $request): array
	{
		Log::debug("UserCréationCtl.effectuer_inscription. Params : ", [$request]);

		$username = $request->input("username");
		$courriel = $request->input("courriel");
		$password = $request->input("password");

		$inscriptionInt = new InscriptionInt();
		$user = $inscriptionInt->effectuer_inscription_locale($username, $courriel, $password);

		Log::debug("UserCréationCtl.effectuer_inscription. Retour : ", [$user]);

		return $user;
	}

	/**
	 * @return array<User>
	 */
	private function effectuer_inscription_sans_mdp(Request $request): array
	{
		Log::debug("UserCréationCtl.effectuer_inscription_sans_mdp. Params : ", [$request]);

		$username = $request->input("username");

		$inscriptionInt = new InscriptionInt();
		$user = $inscriptionInt->effectuer_inscription_sans_mdp($username);

		Log::debug("UserCréationCtl.effectuer_inscription_sans_mdp. Retour : ", [$user]);

		return $user;
	}

	private function valider_paramètres_inscription_locale(Request $request, string $username): MessageBag
	{
		Log::debug("UserCréationCtl.valider_paramètres : ", $request->all());

		//Vérifie si les paramètres permettent un renvoi de courriel
		$réponse = $this->valider_paramètres_renvoi_courriel($request);

		if (!$réponse->isEmpty()) {
			//Si le renvoi de courriel n'est pas possible, vérifie si les paramètres permettent une nouvelle inscription
			$réponse = $this->valider_paramètres_nouvelle_inscritption($request, $username);
		}

		Log::debug("UserCréationCtl.valider_paramètres. Retour : ", [$réponse]);
		return $réponse;
	}

	private function valider_paramètres_renvoi_courriel(Request $request): MessageBag
	{
		Log::debug("UserCréationCtl.valider_paramètres_renvoi_courriel : ", $request->all());

		// Demande de retour de courriel de validation
		$validateur = Validator::make($request->all(), [
			"username" => "required|regex:/^\w{1,64}$/u|exists:progression\dao\models\UserMdl,username",
			"courriel" => "required|email|exists:progression\dao\models\UserMdl,courriel",
			"password" => "prohibited",
		]);

		$réponse = $validateur->errors();

		Log::debug("UserCréationCtl.valider_paramètres_renvoi_courriel. Retour : ", [$réponse]);

		return $réponse;
	}

	private function valider_paramètres_sans_authentification(Request $request, string $username): MessageBag
	{
		Log::debug("UserCréationCtl.valider_paramètres_sans_authentification : ", $request->all());

		$validateur = Validator::make(
			array_merge($request->all(), ["username_p" => $username]),
			[
				"username" => "required|same:username_p|regex:/^\w{1,64}$/u",
				"courriel" => "prohibited",
				"password" => "prohibited",
			],
			[
				"username.same" => "Le nom d'utilisateur diffère de :attribute.",
				"username.regex" => "Le nom d'utilisateur doit être composé de 2 à 64 caractères alphanumériques.",
			],
		);

		$réponse = $validateur->errors();

		Log::debug("UserCréationCtl.valider_paramètres_sans_authentification. Retour : ", [$réponse]);
		return $réponse;
	}

	private function valider_paramètres_nouvelle_inscritption(Request $request, string $username): MessageBag
	{
		Log::debug("UserCréationCtl.valider_paramètres_nouvelle_inscritption : ", $request->all());

		$validateur = Validator::make(
			array_merge($request->all(), ["username_p" => $username]),
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
}
