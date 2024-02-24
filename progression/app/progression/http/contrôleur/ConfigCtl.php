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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;
use progression\http\transformer\ConfigTransformer;
use progression\http\transformer\dto\GénériqueDTO;
use progression\domaine\entité\user\User;

class ConfigCtl extends Contrôleur
{
	public function get(Request $request)
	{
		Log::debug("ConfigCtl.get");

		$config = [
			"version" => config("app.name") . " " . config("app.version") . "(" . config("version.commit_sha") . ")",
			"config" => [
				"AUTH" => [
					"LDAP" => config("authentification.ldap") === true,
					"LOCAL" => config("authentification.local") === true,
				],
			],
		];

		if (config("authentification.ldap") === true) {
			$config_ldap = [
				"DOMAINE" => config("ldap.domaine"),
				"URL_MDP_REINIT" => config("ldap.url_mdp_reinit"),
			];

			$config["config"]["LDAP"] = $config_ldap;
		}

		$user = $request->user("api");

		$réponse = $this->valider_et_préparer_réponse([
			"config" => $config,
			"user" => $user,
		]);

		Log::debug("ConfigCtl.get. Retour : ", [$réponse]);
		return $réponse;
	}

	/**
	 * @return array<string>
	 */
	public function get_liens(User|false $user): array
	{
		$urlBase = Contrôleur::$urlBase;

		$ldap = config("authentification.ldap") === true;
		$local = config("authentification.local") === true;

		$liens = [
			"self" => "$urlBase/",
		];

		if (!$user && ($local || !$ldap)) {
			$liens["inscrire"] = "$urlBase/users";
		}

		if ($user) {
			$liens["user"] = "$urlBase/user/{$user->username}";
			$liens["tokens"] = "$urlBase/user/{$user->username}/tokens";
		}

		return $liens;
	}

	/**
	 * @param array<mixed> $config
	 */
	private function valider_et_préparer_réponse(array $config): JsonResponse
	{
		Log::debug("ConfigCtl.valider_et_préparer_réponse. Params : ", [$config]);

		if ($config) {
			$dto = new GénériqueDTO(id: "serveur", objet: $config, liens: ConfigCtl::get_liens($config["user"]));
			$réponse = $this->item($dto, new ConfigTransformer());
		} else {
			$réponse = null;
		}

		$réponse = $this->préparer_réponse($réponse);

		Log::debug("ConfigCtl.valider_et_préparer_réponse. Retour : ", [$réponse]);
		return $réponse;
	}
}
