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

use progression\http\transformer\TestTransformer;
use progression\util\Encodage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use DomainException, LengthException, RuntimeException;

class TestCtl extends Contrôleur
{
	public function get(Request $request, $question_uri, $numero)
	{
		$chemin = Encodage::base64_decode_url($question_uri);
		$question = null;
		$réponse = null;

		$questionInt = $this->intFactory->getObtenirQuestionInt();
		try {
			$question = $questionInt->get_question($chemin);
		} catch (LengthException | RuntimeException | DomainException $erreur) {
			Log::error("({$request->ip()}) - {$request->method()} {$request->path()} (" . __CLASS__ . ")");
			return $this->réponse_json(["message" => "Mauvaise requête."], 400);
		}

		if ($question != null) {

			if (array_key_exists($numero, $question->tests)) {
				$test = $question->tests[$numero];
				$test->numéro = $numero;
				$test->id = $question_uri . "/{$test->numéro}";
				$test->links = [
					"related" =>
					$_ENV['APP_URL'] .
						"question/" .
						$question_uri,
				];

				$réponse = $this->item(
					$test,
					new TestTransformer(),
				);
			}
		}

		return $this->préparer_réponse($réponse);
	}
}
