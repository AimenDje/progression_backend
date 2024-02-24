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

use LDAP\Result;
use Illuminate\Support\Facades\Log;
use progression\domaine\entité\clé\Portée;
use progression\domaine\entité\user\{User, Rôle};
use progression\dao\{UserDAO, DAOException};

class LoginInt extends Interacteur
{
	public function effectuer_login_par_clé($username, $nom_clé, $secret): User|null
	{
		$dao = $this->source_dao->get_clé_dao();

		try {
			$clé = $dao->get_clé($username, $nom_clé);
			if (
				$clé &&
				$clé->est_valide() &&
				$clé->portée == Portée::AUTH &&
				$dao->vérifier($username, $nom_clé, $secret)
			) {
				$dao = $this->source_dao->get_user_dao();
				return $dao->get_user($username);
			} else {
				return null;
			}
		} catch (DAOException $e) {
			throw new IntéracteurException($e);
		}
	}

	public function effectuer_login_par_identifiant(
		string $identifiant,
		string $password = null,
		string $domaine = null,
	): User|null {
		if (!$this->vérifier_champ_valide($identifiant)) {
			return null;
		}

		$user = null;
		$auth_local = config("authentification.local") === true;
		$auth_ldap = config("authentification.ldap") === true;

		if ($auth_ldap && $domaine) {
			// LDAP
			$user = $this->login_ldap($identifiant, $password, $domaine);
		} elseif ($auth_local) {
			// Local
			$user = $this->login_local($identifiant, $password);
		} elseif (!$auth_ldap) {
			// Sans authentification
			$user = $this->login_sans_authentification($identifiant, null);
		}

		return $user;
	}

	private function login_local(string $identifiant, string $password = null): User|null
	{
		$user = null;

		$user_dao = $this->source_dao->get_user_dao();

		$user =
			strpos($identifiant, "@") === false
				? $user_dao->get_user($identifiant)
				: $user_dao->trouver(courriel: $identifiant);
		try {
			if (!$user || !$user_dao->vérifier_password($user, $password)) {
				return null;
			}
		} catch (DAOException $e) {
			throw new IntéracteurException($e);
		}

		return $user;
	}

	private function login_ldap(string $identifiant, string|null $password, string $domaine): User|null
	{
		$user = null;
		if ($password) {
			$user_ldap = $this->get_user_ldap($identifiant, $password, $domaine);
			if (!$user_ldap) {
				return null;
			}
			$courriel = $this->get_courriel_ldap($user_ldap);
			if (!$courriel) {
				return null;
			}

			$user = $this->login_sans_authentification($identifiant, $courriel);
		}
		return $user;
	}

	private function vérifier_champ_valide($champ)
	{
		return $champ && !empty(trim($champ));
	}

	/**
	 * @return array<mixed>|null
	 */
	private function get_user_ldap(string $identifiant, string $password, string $domaine): array|null
	{
		if ($domaine != config("ldap.domaine")) {
			throw new IntéracteurException("Domaine multiple non implémenté", 500);
		}

		// Connexion au serveur LDAP
		$ldap = @ldap_connect("ldap://" . config("ldap.hôte"), (int) config("ldap.port"));
		if (!$ldap) {
			syslog(LOG_ERR, "Erreur de configuration LDAP");
			throw new IntéracteurException("Erreur de configuration LDAP", 500);
		}
		ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
		ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);
		ldap_set_option($ldap, LDAP_OPT_NETWORK_TIMEOUT, (int) config("ldap.timeout"));

		// bind l'utilisateur LDAP
		if (config("ldap.bind.dn") && config("ldap.bind.pw")) {
			$bind = @ldap_bind($ldap, config("ldap.bind.dn"), config("ldap.bind.pw"));
		} else {
			$bind = @ldap_bind($ldap, $identifiant, $password);
		}

		if (!$bind) {
			ldap_get_option($ldap, LDAP_OPT_DIAGNOSTIC_MESSAGE, $extended_error);
			Log::error("Erreur de connexion à LDAP : $extended_error");
			throw new IntéracteurException("Impossible de se connecter au serveur d'authentification", 503);
		}

		//Recherche de l'utilisateur à authentifier
		$result = @ldap_search(
			$ldap,
			base: config("ldap.base") ?: "",
			filter: "(" . config("ldap.uid") . "=$identifiant)",
			attributes: ["dn", "cn", "mail"],
		);

		if ($result instanceof Result) {
			$user = ldap_get_entries($ldap, $result);

			if (
				$user &&
				isset($user["count"]) &&
				$user["count"] == 1 &&
				isset($user[0]) &&
				is_array($user[0]) &&
				isset($user[0]["dn"]) &&
				@ldap_bind($ldap, $user[0]["dn"], $password)
			) {
				return $user;
			}
		}

		return null;
	}

	/**
	 * @param array<mixed> $user
	 */
	private function get_courriel_ldap(array $user): string|null
	{
		return $user[0]["mail"][0] ?? null;
	}

	private function login_sans_authentification(string $username, string $courriel = null): User
	{
		$user = (new InscriptionInt())->effectuer_inscription_sans_mdp($username, $courriel, Rôle::NORMAL);
		return self::premier_élément($user);
	}
}
