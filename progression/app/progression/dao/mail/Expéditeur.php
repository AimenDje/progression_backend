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

namespace progression\dao\mail;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use progression\domaine\entité\user\User;
use progression\http\contrôleur\GénérateurDeToken;
use Carbon\Carbon;

class MailUser
{
	public string $name;
	public string|null $email;

	public function __construct(User $user)
	{
		$this->name = $user->username;
		$this->email = $user->courriel;
	}
}

class Expéditeur
{
	function envoyer_courriel_de_validation(User $user): void
	{
		$data = [
			"url_user" => config("app.url") . "/user/" . $user->username,
			"user" => [
				"username" => $user->username,
				"courriel" => $user->courriel,
				"rôle" => $user->rôle,
			],
		];
		$ressources = [
			"user" => [
				"url" => "^user/" . $user->username . "$",
				"method" => "^PATCH$",
			],
		];

		$expirationToken = Carbon::now()->addMinutes((int) config("jwt.expiration"))->timestamp;
		$token = GénérateurDeToken::get_instance()->générer_token(
			$user->username,
			$expirationToken,
			$ressources,
			$data,
		);

		try {
			Mail::to(new MailUser($user))->send(new ValidationCourrielMail($user, $token));
			Log::notice("(" . __CLASS__ . ") Courriel de validation envoyé à {$user->courriel} :");
		} catch (\Swift_TransportException | \Swift_RfcComplianceException $e) {
			throw new EnvoiDeCourrielException($e);
		}
	}
}
