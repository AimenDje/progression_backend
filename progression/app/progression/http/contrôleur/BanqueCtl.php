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
use Illuminate\Support\Facades\Log;
//use progression\domaine\interacteur\ObtenirBanquesInt;
use progression\http\transformer\dto\BanqueDTO;
use progression\util\Encodage;
use progression\domaine\entité\Banque;

class BanquesCtl extends Contrôleur
{
	public function get(Request $request, $username)
	{
		Log::debug("BanquesCtl.get. Params : ", [$request->all(), $username]);

		$réponse = null;
		$banques = $this->obtenir_banques($username);
		$réponse = $this->valider_et_préparer_réponse($banques, $username);

		Log::debug("BanquesCtl.get. Retour : ", [$réponse]);
		return $réponse;
	}

	private function valider_et_préparer_réponse($banques, $username)
	{
		Log::debug("BanquesCtl.valider_et_préparer_réponse. Params : ", [$banques]);

		if ($banques === null) {
			$réponse = null;
		} else {
			$dtos = [];
			foreach ($banques as $question_uri => $banque) {
				$uri_encodé = Encodage::base64_encode_url($question_uri);
				array_push(
					$dtos,
					new BanqueDTO(
						id: "{$username}/{$uri_encodé}",
						objet: $banque,
						liens: BanqueCtl::get_liens($username, $uri_encodé),
					),
				);
			}
			$réponse = $this->collection($dtos, new BanqueTransformer());
		}

		$réponse = $this->préparer_réponse($réponse);

		Log::debug("BanquesCtl.valider_et_préparer_réponse. Retour : ", [$réponse]);
		return $réponse;
	}

	private function obtenir_banques($username)
	{
		Log::debug("BanquesCtl.obtenir_banques. Params : ", [$username]);

		$banquesInt = new ObtenirBanquesInt();

		$banques = $banquesInt->get_banques($username, $this->get_includes());

		Log::debug("BanquesCtl.obtenir_banques. Retour : ", [$banques]);
		return $banques;
	}
}
