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

use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\Log;
use Composer\Semver\Comparator;

class ContrôleurFrontal extends Contrôleur
{
	private function valider_content_type(Request $request): bool
	{
		$résultat = $request->headers->get("content-type") == "application/vnd.api+json";

		// Désuétude V 4.0.0
		// Le corps des requêtes DOIT être passé en format JSON:API.
		assert(
			$résultat || Comparator::lessThan(strval(config("app.version")), "4.0.0"),
			"Fonctionnalité désuète. Doit être retirée",
		);
		// Fin désuétude

		return $résultat;
	}

	public function put_avancement(Request $request, string $username, string $question_uri): JsonResponse
	{
		if ($this->valider_content_type($request)) {
			$avancement = self::décoder_objet($request);

			if (isset($request["data"]["id"]) && $request["data"]["id"] != "${username}/${question_uri}") {
				return $this->préparer_réponse(["erreur" => ["id" => ["Le champ id ne correspond pas à l'URI."]]], 409);
			}

			/**
			 * @var AvancementCtl|null $contrôleur;
			 */
			$contrôleur = $this->get_contrôleur($request);

			if (!$contrôleur) {
				return $this->préparer_réponse(
					["erreur" => ["type" => ["Le champ type est manquant ou ne correspond pas à un type valide"]]],
					409,
				);
			}
		} else {
			$avancement = $request->all();

			$contrôleur = new AvancementCtl();
		}

		return $contrôleur->post($username, $question_uri, $avancement);
	}

	public function post_avancement(Request $request, string $username): JsonResponse
	{
		if ($this->valider_content_type($request)) {
			$avancement = self::décoder_objet($request);

			if (!isset($request["data"]["id"])) {
				return $this->préparer_réponse(["erreur" => ["id" => ["Le champ id est obligatoire."]]], 400);
			}

			if (!preg_match("/${username}\/[a-zA-Z0-9_-]+/", $request["data"]["id"])) {
				return $this->préparer_réponse(
					["erreur" => ["id" => ["Le champ id doit avoir le format {username}/{question_uri}."]]],
					400,
				);
			}

			$question_uri = explode("/", $request["data"]["id"])[1];

			/**
			 * @var AvancementCtl|null $contrôleur;
			 */
			$contrôleur = $this->get_contrôleur($request);
			if (!$contrôleur) {
				return $this->préparer_réponse(
					["erreur" => ["type" => ["Le champ type est manquant ou ne correspond pas à un type valide"]]],
					409,
				);
			}
		} else {
			if (!$request->question_uri) {
				return $this->préparer_réponse(
					["erreur" => ["question_uri" => ["Le champ question uri est obligatoire."]]],
					400,
				);
			}
			$question_uri = $request->question_uri;
			$avancement = $request->avancement ?? [];
			$avancement["id"] = $question_uri;

			$contrôleur = new AvancementCtl();
		}

		return $contrôleur->post($username, $question_uri, $avancement);
	}

	public function post_clé(Request $request, string $username): JsonResponse
	{
		if ($this->valider_content_type($request)) {
			$clé = self::décoder_objet($request);

			/**
			 * @var CléCtl|null $contrôleur;
			 */
			$contrôleur = $this->get_contrôleur($request);

			if (!$contrôleur) {
				return $this->préparer_réponse(
					["erreur" => ["type" => ["Le champ type est manquant ou ne correspond pas à un type valide"]]],
					409,
				);
			}
		} else {
			$clé = $request->all();

			$contrôleur = new CléCtl();
		}

		return $contrôleur->post($username, $clé);
	}

	public function post_user(Request $request): JsonResponse
	{
		Log::info("{$request->ip()} - Tentative d'inscription : {$request->input("username")}");

		if ($this->valider_content_type($request)) {
			$user = self::décoder_objet($request);

			/**
			 * @var UserCtl|null $contrôleur;
			 */
			$contrôleur = $this->get_contrôleur($request);

			if (!$contrôleur) {
				return $this->préparer_réponse(
					["erreur" => ["type" => ["Le champ type est manquant ou ne correspond pas à un type valide"]]],
					409,
				);
			}
		} else {
			$user = $request->all();

			$contrôleur = new UserCtl();
		}

		return $contrôleur->post($user);
	}

	public function put_user(Request $request, string $username): JsonResponse
	{
		if ($this->valider_content_type($request)) {
			$user = self::décoder_objet($request);

			if (isset($request["data"]["id"]) && $request["data"]["id"] != $username) {
				return $this->préparer_réponse(["erreur" => ["id" => ["Le champ id ne correspond pas à l'URI."]]], 409);
			}

			/**
			 * @var UserCtl|null $contrôleur;
			 */
			$contrôleur = $this->get_contrôleur($request);
			if (!$contrôleur) {
				return $this->préparer_réponse(
					["erreur" => ["type" => ["Le champ type est manquant ou ne correspond pas à un type valide"]]],
					409,
				);
			}
		} else {
			$user = $request->all();

			$contrôleur = new UserCtl();
		}

		return $contrôleur->put($username, $user);
	}

	public function patch_user(Request $request, string $username): JsonResponse
	{
		if ($this->valider_content_type($request)) {
			$user = self::décoder_objet($request);

			/**
			 * @var UserCtl|null $contrôleur;
			 */
			$contrôleur = $this->get_contrôleur($request);
			if (!$contrôleur) {
				return $this->préparer_réponse(
					["erreur" => ["type" => ["Le champ type est manquant ou ne correspond pas à un type valide"]]],
					409,
				);
			}
		} else {
			$user = $request->all();

			$contrôleur = new UserCtl();
		}

		return $contrôleur->patch($username, $user);
	}

	public function patch_avancement(Request $request, string $username, string $question_uri): JsonResponse
	{
		if ($this->valider_content_type($request)) {
			$avancement = self::décoder_objet($request);

			/**
			 * @var AvancementCtl|null $contrôleur;
			 */
			$contrôleur = $this->get_contrôleur($request);
			if (!$contrôleur) {
				return $this->préparer_réponse(
					["erreur" => ["type" => ["Le champ type est manquant ou ne correspond pas à un type valide"]]],
					409,
				);
			}
		} else {
			$avancement = $request->all();

			$contrôleur = new AvancementCtl();
		}

		return $contrôleur->patch($username, $question_uri, $avancement);
	}

	public function post_résultat(Request $request, string $uri): JsonResponse
	{
		if ($this->valider_content_type($request)) {
			$résultat = self::décoder_objet($request);

			/**
			 * @var RésultatCtl|null $contrôleur;
			 */
			$contrôleur = $this->get_contrôleur($request);

			if (!$contrôleur) {
				return $this->préparer_réponse(
					["erreur" => ["type" => ["Le champ type est manquant ou ne correspond pas à un type valide"]]],
					409,
				);
			}
		} else {
			$résultat = $request->all();

			$contrôleur = new RésultatCtl();
		}

		return $contrôleur->post($uri, $résultat);
	}

	public function post_sauvegarde(Request $request, string $username, string $question_uri): JsonResponse
	{
		if ($this->valider_content_type($request)) {
			$sauvegarde = self::décoder_objet($request);

			/**
			 * @var SauvegardeCtl|null $contrôleur;
			 */
			$contrôleur = $this->get_contrôleur($request);

			if (!$contrôleur) {
				return $this->préparer_réponse(
					["erreur" => ["type" => ["Le champ type est manquant ou ne correspond pas à un type valide"]]],
					409,
				);
			}
		} else {
			$sauvegarde = $request->all();

			$contrôleur = new SauvegardeCtl();
		}

		return $contrôleur->post($username, $question_uri, $sauvegarde);
	}

	public function post_tentative(Request $request, string $username, string $question_uri): JsonResponse
	{
		if ($this->valider_content_type($request)) {
			$tentative = self::décoder_objet($request);

			/**
			 * @var TentativeCtl|null $contrôleur;
			 */
			$contrôleur = $this->get_contrôleur($request);

			if (!$contrôleur) {
				return $this->préparer_réponse(
					["erreur" => ["type" => ["Le champ type est manquant ou ne correspond pas à un type valide"]]],
					409,
				);
			}
		} else {
			$tentative = $request->all();

			$contrôleur = new TentativeCtl();
		}

		return $contrôleur->post($username, $question_uri, $tentative);
	}

	public function post_token(Request $request, string $username): JsonResponse
	{
		if ($this->valider_content_type($request)) {
			$token = self::décoder_objet($request);

			/**
			 * @var TokenCtl|null $contrôleur;
			 */
			$contrôleur = $this->get_contrôleur($request);

			if (!$contrôleur) {
				return $this->préparer_réponse(
					["erreur" => ["type" => ["Le champ type est manquant ou ne correspond pas à un type valide"]]],
					409,
				);
			}
		} else {
			$token = $request->input("ressources") ? $request->all() : $request->input("data") ?? [];

			$contrôleur = new TokenCtl();
		}

		return $contrôleur->post($username, $token);
	}

	/**
	 * @return array<mixed>
	 */
	private function décoder_objet(Request $request): array
	{
		if ($this->valider_content_type($request)) {
			$data = $request["data"] ?? [];
			if (
				in_array($data["type"] ?? "", [
					"avancement",
					"cle",
					"user",
					"resultat",
					"sauvegarde",
					"token",
					"tentative",
				])
			) {
				return $data["attributes"] ?? [];
			} else {
				return [];
			}
		} else {
			return $request->all();
		}
	}

	private function get_contrôleur(Request $request): Contrôleur|null
	{
		$type = $request->data["type"] ?? "";
		if ($type == "avancement") {
			return new AvancementCtl();
		} elseif ($type == "avancements") {
			return new AvancementsCtl();
		} elseif ($type == "cle") {
			return new CléCtl();
		} elseif ($type == "user") {
			return new UserCtl();
		} elseif ($type == "resultat") {
			return new RésultatCtl();
		} elseif ($type == "sauvegarde") {
			return new SauvegardeCtl();
		} elseif ($type == "token") {
			return new TokenCtl();
		} elseif ($type == "tentative") {
			return new TentativeCtl();
		} else {
			return null;
		}
	}
}
?>
