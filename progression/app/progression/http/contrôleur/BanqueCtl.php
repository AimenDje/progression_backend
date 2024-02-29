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

use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\Log;
use progression\domaine\interacteur\ObtenirBanquesInt;
use progression\http\transformer\dto\BanqueDTO;
use progression\http\transformer\BanqueTransformer;
use progression\util\Encodage;
use progression\domaine\entité\banque\Banque;

class BanqueCtl extends Contrôleur
{
    /**
     * @param string $username
     */
    public function get(Request $request, string $username) : JsonResponse
	{
		Log::debug("BanquesCtl.get. Params : ", [$request->all(), $username]);

		$réponse = null;
		$banques = $this->obtenir_banques($username);
		$réponse = $this->valider_et_préparer_réponse($banques, $username);

		Log::debug("BanquesCtl.get. Retour : ", [$réponse]);
		return $réponse;
	}

    /**
     * @return array<Banque>
     */
	private function obtenir_banques(string $username) : array
	{
		Log::debug("BanquesCtl.obtenir_banques. Params : ", [$username]);

		$banquesInt = new ObtenirBanquesInt();

		$banques = $banquesInt->get_banques($username, $this->get_includes());

		Log::debug("BanquesCtl.obtenir_banques. Retour : ", [$banques]);
		return $banques;
	}

    /**
     * @param array<Banque> $banques
     */    
	private function valider_et_préparer_réponse(array $banques, string  $username) : JsonResponse
	{
		Log::debug("BanquesCtl.valider_et_préparer_réponse. Params : ", [$banques]);

		if ($banques == null) {
			$réponse = null;
		} else {
			$dtos = [];
			foreach ($banques as $id => $banque) {
				array_push(
					$dtos,
					new BanqueDTO(
						id: $id,
						objet: $banque,
						liens: BanqueCtl::get_liens($username),
					),
				);
			}
			$réponse = $this->collection($dtos, new BanqueTransformer());
		}

		$réponse = $this->préparer_réponse($réponse);

		Log::debug("BanquesCtl.valider_et_préparer_réponse. Retour : ", [$réponse]);
		return $réponse;
	}

    /**
	 * @return array<string>
	 */
	public static function get_liens(string $username): array
	{
		$urlBase = Contrôleur::$urlBase;
		return [
			"self" => "{$urlBase}/user/{$username}"
		];
	}

}
