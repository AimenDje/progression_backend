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

use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Validator as ValidatorImpl;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\MessageBag;
use progression\http\transformer\TokenTransformer;
use progression\http\transformer\dto\GénériqueDTO;
use Carbon\Carbon;

class TokenCtl extends Contrôleur
{
	/**
	 * @param array<mixed> $attributs
	 */
	public function post(string $username, array $attributs): JsonResponse
	{
		Log::debug("TokenCtl.post. Params : ", [$username, $attributs]);

		$validateur = $this->valider_paramètres($attributs);

		if ($validateur->stopOnFirstFailure()->fails()) {
			$réponse = $this->réponse_json(["erreur" => $validateur->errors()], 400);
		} else {
			$ressources = $attributs["ressources"];
			$expiration =
				gettype($attributs["expiration"]) == "string"
					? $this->calculer_expiration($attributs["expiration"])
					: $attributs["expiration"];
			$data = $attributs["data"] ?? [];

			$contexte = null;
			$fingerprint = null;
			if (isset($attributs["fingerprint"]) && $attributs["fingerprint"]) {
				$contexte = $this->getContexteAléatoire();
				$fingerprint = hash("sha256", $contexte);
			}

			$token_envoyé = GénérateurDeToken::get_instance()->générer_token(
				username: $username,
				expiration: $expiration,
				ressources: $ressources ?? [
					"tout" => ["url" => ".*", "method" => ".*"],
				],
				data: $data,
				fingerprint: $fingerprint,
			);

			$signature = explode(".", $token_envoyé)[2];
			$réponse = $this->préparer_réponse(
				$this->item(
					new GénériqueDTO(
						id: $signature,
						objet: (object) [
							"username" => $username,
							"data" => $data,
							"ressources" => $ressources,
							"expiration" => $expiration,
							"fingerprint" => $fingerprint,
							"jwt" => $token_envoyé,
						],
						liens: TokenCtl::get_liens($username, $signature),
					),
					new TokenTransformer(),
				),
			);
			if ($contexte) {
				$réponse->cookie(
					$this->créerCookieSécure(nom: "contexte_token", valeur: $contexte, expiration: $expiration),
				);
			}
		}

		Log::debug("TokenCtl.post. Réponse : ", [$réponse]);
		return $réponse;
	}

	/**
	 * @return array<string>
	 */
	public static function get_liens(string $username, string $id): array
	{
		$urlBase = Contrôleur::$urlBase;

		$liens = [
			"self" => "{$urlBase}/token/{$id}",
			"user" => "{$urlBase}/user/{$username}",
		];

		return $liens;
	}

	/**
	 * @param array<mixed> $data_in
	 */
	private function valider_paramètres(array $data_in): ValidatorImpl
	{
		$validateur = Validator::make(
			$data_in,
			[
				"ressources" => "required",
				"ressources.*.url" => "required|string",
				"ressources.*.method" => "required|string",
				"expiration" => ["required", "regex:/^\+*[0-9]+$/"],
				"fingerprint" => "boolean",
			],
			[
				"required" => "Le champ :attribute est obligatoire.",
				"expiration.regex" => "Le champ expiration doit représenter une date relative ou absolue.",
				"fingerprint" => "Le champ fingerprint doit être un booléen.",
			],
		);

		return $validateur;
	}

	private function calculer_expiration(string $expiration): int
	{
		if ("$expiration"[0] == "+") {
			return intval(Carbon::now()->getTimestamp()) + intval(substr($expiration, 1));
		} else {
			return intval($expiration);
		}
	}

	private function getContexteAléatoire(): string
	{
		return GénérateurAléatoire::get_instance()->générer_chaîne_aléatoire(64);
	}
}
