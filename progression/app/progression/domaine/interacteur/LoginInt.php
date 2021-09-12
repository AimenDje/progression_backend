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

use progression\domaine\entité\Clé;
use progression\dao\{DAOFactory, UserDAO};

class AuthException extends \Exception
{
}

class LoginInt extends Interacteur
{
	function effectuer_login_par_clé($username, $nom_clé, $secret)
	{
		$dao = DAOFactory::getInstance()->get_clé_dao();

		$clé = $dao->get_clé($username, $nom_clé);
		if (
			$clé &&
			$clé->est_valide() &&
			$clé->portée == Clé::PORTEE_AUTH &&
			$dao->vérifier($username, $nom_clé, $secret)
		) {
			$dao = DAOFactory::getInstance()->get_user_dao();
			return $dao->get_user($username);
		} else {
			syslog(LOG_NOTICE, "Clé invalide pour $username");
			return null;
		}
	}

	function effectuer_login_par_identifiant($username, $password = null)
	{
		syslog(LOG_INFO, "Tentative de connexion : $username");

		if (!$this->vérifier_champ_valide($username)) {
			return null;
		}

		$user = null;

		if ($_ENV["AUTH_TYPE"] == "no") {
			$user = $this->login_sans_authentification($username);
		} elseif ($_ENV["AUTH_TYPE"] == "local") {
			$user = $this->login_local($username, $password);
		} elseif ($_ENV["AUTH_TYPE"] == "ldap") {
			$user = $this->login_ldap($username, $password);
		}

		if ($user != null) {
			syslog(LOG_INFO, "Connexion réussie : $username");
		}

		return $user;
	}

	function login_local($username, $password)
	{
		$user = (new ObtenirUserInt())->get_user($username);

		if ($user && $this->source_dao->get_user_dao()->vérifier_password($user, $password)) {
			return $user;
		} else {
			return null;
		}
	}

	function login_ldap($username, $password)
	{
		$user_ldap = $this->get_username_ldap($username, $password);

		if ($user_ldap != null) {
			$user = (new ObtenirUserInt())->get_user($username);
			if (!$user) {
				$user = (new CréerUserInt())->créer_user($username);
			}
		} else {
			$user = null;
		}

		return $user;
	}

	function vérifier_champ_valide($champ)
	{
		return !empty(trim($champ));
	}

	function get_username_ldap($username, $password)
	{
		#Tentative de connexion à AD
		define(LDAP_OPT_DIAGNOSTIC_MESSAGE, 0x0032);

		($ldap = ldap_connect("ldap://" . $_ENV["HOTE_AD"], $_ENV["PORT_AD"])) or
			die("Configuration de serveur LDAP invalide.");
		ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
		ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);
		$bind = @ldap_bind($ldap, $_ENV["DN_BIND"], $_ENV["PW_BIND"]);

		if (!$bind) {
			ldap_get_option($ldap, LDAP_OPT_DIAGNOSTIC_MESSAGE, $extended_error);
			throw new AuthException(
				"Impossible de se connecter au serveur d'authentification. Veuillez communiquer avec l'administrateur du site. Erreur : $extended_error",
			);
		}
		$result = ldap_search($ldap, $_ENV["LDAP_BASE"], "(sAMAccountName=$username)", ["dn", "cn", 1]);
		$user = ldap_get_entries($ldap, $result);
		if ($user["count"] != 1 || !@ldap_bind($ldap, $user[0]["dn"], $password)) {
			return null;
		}
		return $user[0];
	}

	function login_sans_authentification($username)
	{
		$user = (new ObtenirUserInt())->get_user($username);
		if (!$user) {
			$user = (new CréerUserInt())->créer_user($username);
		}

		return $user;
	}
}