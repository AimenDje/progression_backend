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

use progression\domaine\entité\user\{User, État, Rôle};
use progression\dao\mail\EnvoiDeCourrielException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;

class InscriptionInt extends Interacteur
{
	/**
	 * @return array<User>
	 */
	public function effectuer_inscription_locale(
		string $username,
		string $courriel,
		string|null $password,
		Rôle $rôle = Rôle::NORMAL,
	): array {
		$dao = $this->source_dao->get_user_dao();
		$user = $dao->get_user($username);

		if (!$user) {
			if (!$password) {
				throw new RessourceInvalideException("Le mot de passe ne peut pas être laissé vide");
			} else {
				$user_inscrit = $this->effectuer_inscription_avec_mdp($username, $courriel, $password, $rôle);
				$user_créé = self::premier_élément($user_inscrit);

				if ($user_créé && Gate::allows("valider-le-courriel", $user_créé->rôle)) {
					$this->envoyer_courriel_de_validation($user_créé);
				}

				return $user_inscrit;
			}
		} else {
			if (!$password) {
				$this->effectuer_renvoi_de_courriel($user);
				return [$user->username => $user];
			} else {
				throw new DuplicatException("Un utilisateur du même nom existe déjà.");
			}
		}
	}

	private function effectuer_renvoi_de_courriel(User $user): void
	{
		if ($user->état != État::EN_ATTENTE_DE_VALIDATION) {
			throw new PermissionException("L'état de l'utilisateur ne permet pas cette opération");
		}

		$this->envoyer_courriel_de_validation($user);
	}

	/**
	 * @return array<User>
	 */
	public function effectuer_inscription_sans_mdp(
		string $username,
		string $courriel = null,
		Rôle $rôle = Rôle::NORMAL,
	): array {
		$dao = $this->source_dao->get_user_dao();
		$user = $dao->get_user($username);
		if ($user) {
			return [$username => $user];
		}

		$user = $dao->save(
			$username,
			new User(
				username: $username,
				date_inscription: Carbon::now()->getTimestamp(),
				courriel: $courriel,
				rôle: $rôle,
				état: État::ACTIF,
				préférences: config("préférences.défaut") ?: "",
			),
		);
		return $user;
	}

	/**
	 * @return array<User>
	 */
	private function effectuer_inscription_avec_mdp(
		string $username,
		string $courriel,
		string $password,
		Rôle $rôle,
	): array {
		$dao = $this->source_dao->get_user_dao();
		if ($dao->trouver(courriel: $courriel)) {
			throw new DuplicatException("Le courriel est déjà utilisé.");
		}

		return $this->créer_et_sauvegarder_user($username, $courriel, $password, $rôle);
	}

	/**
	 * @return array<User>
	 */
	private function créer_et_sauvegarder_user(string $username, string $courriel, string $password, Rôle $rôle): array
	{
		$dao = $this->source_dao->get_user_dao();
		$user = $dao->save(
			$username,
			new User(
				username: $username,
				date_inscription: Carbon::now()->getTimestamp(),
				courriel: $courriel,
				rôle: $rôle,
				état: Gate::allows("valider-le-courriel", $rôle) ? État::EN_ATTENTE_DE_VALIDATION : État::ACTIF,
				préférences: config("préférences.défaut") ?: "{}",
			),
		);

		$dao->set_password(self::premier_élément($user), $password);

		return $user;
	}

	private function envoyer_courriel_de_validation(User $user): void
	{
		try {
			$this->source_dao->get_expéditeur()->envoyer_courriel_de_validation($user);
		} catch (EnvoiDeCourrielException $e) {
			Log::notice("Échec de l'envoi du courriel à " . $user->courriel);
			Log::notice($e->getMessage());
			Log::debug($e);
		}
	}
}
