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

namespace progression\http\transformer;

use League\Fractal;
use progression\util\Encodage;

class QuestionTransformer extends Fractal\TransformerAbstract
{
	public $type = "question";

	public function transform($data_in)
	{
		$question = $data_in["question"];

		$chemin_encodé = Encodage::base64_encode_url($question->uri);

		$data_out = [
			"id" => $chemin_encodé,
			"niveau" => $question->niveau,
			"titre" => $question->titre,
			"description" => $question->description,
			"énoncé" => $question->enonce,
			"feedback_pos" => $question->feedback_pos,
			"feedback_neg" => $question->feedback_neg,
			"feedback_err" => $question->feedback_err,
			"auteur" => $question->auteur,
			"licence" => $question->licence,
			"links" => [
				"self" => $_ENV["APP_URL"] . "question/" . $chemin_encodé,
			],
		];

		return $data_out;
	}
}
