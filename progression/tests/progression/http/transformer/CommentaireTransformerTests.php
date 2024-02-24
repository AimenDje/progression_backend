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

use progression\TestCase;
use progression\domaine\entité\Commentaire;
use progression\domaine\entité\user\User;
use progression\http\transformer\dto\GénériqueDTO;

final class CommentaireTransformerTests extends TestCase
{
	public function test_étant_donné_un_commentaire_instancié_avec_des_valeurs_lorsquon_récupère_son_transformer_on_obtient_un_objet_json_correspondant()
	{
		$commentaire = new Commentaire(
			"message envoyer par interacteurMocker",
			new User(username: "createur_test", date_inscription: 0),
			122456747,
			15,
		);
		$commentaireTransformer = new CommentaireTransformer("createur_test/test/122456747");
		$résultat_attendu = [
			"id" => "createur_test/test/122456747/11",
			"message" => "message envoyer par interacteurMocker",
			"créateur" => "createur_test",
			"date" => 122456747,
			"numéro_ligne" => 15,
			"links" => [
				"auteur" => "https://example.com/user/createur_test",
				"self" => "https://example.com/commentaire/createur_test/test/122456747/11",
				"tentative" => "https://example.com/tentative/createur_test/test/122456747",
			],
		];

		$this->assertEquals(
			$résultat_attendu,
			$commentaireTransformer->transform(
				new GénériqueDTO(
					id: "createur_test/test/122456747/11",
					objet: $commentaire,
					liens: [
						"auteur" => "https://example.com/user/createur_test",
						"self" => "https://example.com/commentaire/createur_test/test/122456747/11",
						"tentative" => "https://example.com/tentative/createur_test/test/122456747",
					],
				),
			),
		);
	}
}
