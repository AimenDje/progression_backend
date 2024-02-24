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

use progression\domaine\entité\Exécutable;
use progression\http\transformer\dto\GénériqueDTO;
use PHPUnit\Framework\TestCase;

final class ÉbaucheTransformerTests extends TestCase
{
	public function test_étant_donné_une_ébauche_instanciée_avec_des_valeurs_lorsquon_récupère_son_transformer_on_obtient_un_objet_json_correspondant()
	{
		$ébaucheTransformer = new ÉbaucheTransformer();

		$ébauche = new Exécutable("return nb1 + nb2;", "python");
		$ébauche->lang = "python";

		$résultat_obtenu = $ébaucheTransformer->transform(
			new GénériqueDTO(
				id: "aHR0cHM6Ly9kZXBvdC5jb20vcm9nZXIvcXVlc3Rpb25zX3Byb2cvZm9uY3Rpb25zMDEvYXBwZWxlcl91bmVfZm9uY3Rpb24/python",
				objet: $ébauche,
				liens: [],
			),
		);

		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/ébaucheTransformerTest_1.json",
			json_encode($résultat_obtenu),
		);
	}
}
