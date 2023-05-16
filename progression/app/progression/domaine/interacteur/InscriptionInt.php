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

namespace progression\domaine\interacteur;

use progression\dao\DAOFactory;
use progression\domaine\entité\user\{User, État, Rôle};
use progression\http\transformer\UserTransformer;
use progression\http\contrôleur\GénérateurDeToken;
use Carbon\Carbon;

class InscriptionInt extends Interacteur
{
	function effectuer_inscription($username, string|null $courriel = null, $password = null, Rôle $rôle = Rôle::NORMAL)
	{
		if (!$username) {
			return null;
		}

		$auth_local = getenv("AUTH_LOCAL") === "true";
		$auth_ldap = getenv("AUTH_LDAP") === "true";

		if ($auth_local) {
			if (!$password || !$courriel) {
				return null;
			} else {
				return $this->effectuer_inscription_avec_mdp($username, $courriel, $password, $rôle);
			}
		} elseif ($auth_ldap) {
			return null;
		} else {
			return $this->effectuer_inscription_sans_mdp($username, $rôle);
		}
	}

	private function effectuer_inscription_avec_mdp(string $username, string $courriel, string $password, Rôle $rôle)
	{
		$dao = $this->source_dao->get_user_dao();

		$user = $dao->get_user($username);

		if ($user) {
			return null;
		}

		$user = $dao->save(
			new User(
				$username,
				$courriel,
				rôle: $rôle,
				état: $rôle == Rôle::ADMIN ? État::ACTIF : État::ATTENTE_DE_VALIDATION,
			),
		);
		$dao->set_password($user, $password);

		if ($rôle != Rôle::ADMIN) {
			$ressources = [
				"data" => [
					"url_user" => getenv("APP_URL") . "user/" . $username,
					"user" => [
						"username" => $user->username,
						"courriel" => $user->courriel,
						"rôle" => $user->rôle,
					],
				],
				"permissions" => [
					"user" => [
						"url" => "^user/" . $username . "$",
						"method" => "^POST$",
					],
				],
			];

			$expirationToken = Carbon::now()->addMinutes((int) getenv("JWT_EXPIRATION"))->timestamp;
			$token = GénérateurDeToken::get_instance()->générer_token($username, $expirationToken, $ressources);
			$this->source_dao->get_expéditeur()->envoyer_validation_courriel($user, $token);
		}

		return $user;
	}

	private function effectuer_inscription_sans_mdp($username, Rôle $rôle)
	{
		$dao = $this->source_dao->get_user_dao();
		return $dao->get_user($username) ??
			$dao->save(new User($username, courriel: null, rôle: $rôle, état: État::ACTIF));
	}
}
