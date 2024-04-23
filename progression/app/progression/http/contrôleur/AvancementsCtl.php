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
use Illuminate\Support\MessageBag;
use progression\domaine\interacteur\{
	ObtenirAvancementsInt,
	SauvegarderAvancementInt,
	ObtenirQuestionInt,
	IntéracteurException,
};
use progression\http\transformer\AvancementTransformer;
use progression\http\transformer\dto\AvancementDTO;
use progression\util\Encodage;
use progression\domaine\entité\Avancement;

class AvancementsCtl extends Contrôleur
{
	public function get(string $username)
	{
		Log::debug("AvancementsCtl.get. Params : ", [$username]);

		$avancements = $this->obtenir_avancements($username);
		$réponse = $this->valider_et_préparer_réponse($avancements, $username);

		Log::debug("AvancementsCtl.get. Retour : ", [$réponse]);
		return $réponse;
	}

	private function valider_et_préparer_réponse($avancements, $username)
	{
		Log::debug("AvancementsCtl.valider_et_préparer_réponse. Params : ", [$avancements]);

		if ($avancements === null) {
			$réponse = null;
		} else {
			$dtos = [];
			foreach ($avancements as $question_uri => $avancement) {
				$uri_encodé = Encodage::base64_encode_url($question_uri);
				array_push(
					$dtos,
					new AvancementDTO(
						id: "{$username}/{$uri_encodé}",
						objet: $avancement,
						liens: AvancementCtl::get_liens($username, $uri_encodé),
					),
				);
			}
			$réponse = $this->collection($dtos, new AvancementTransformer());
		}

		$réponse = $this->préparer_réponse($réponse);

		Log::debug("AvancementsCtl.valider_et_préparer_réponse. Retour : ", [$réponse]);
		return $réponse;
	}

	private function obtenir_avancements($username)
	{
		Log::debug("AvancementsCtl.obtenir_avancements. Params : ", [$username]);

		$avancementsInt = new ObtenirAvancementsInt();

		$avancements = $avancementsInt->get_avancements($username, $this->get_includes());

		Log::debug("AvancementsCtl.obtenir_avancements. Retour : ", [$avancements]);
		return $avancements;
	}
}
