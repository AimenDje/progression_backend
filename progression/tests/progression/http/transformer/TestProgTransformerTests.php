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

use progression\domaine\entité\TestProg;
use progression\http\transformer\dto\GénériqueDTO;
use progression\TestCase;

final class TestProgTransformerTests extends TestCase
{
	public function test_étant_donné_un_test_instanciée_avec_des_valeurs_minimales_lorsquon_récupère_son_transformer_on_obtient_un_objet_json_correspondant()
	{
		$test = new TestProg("Somme de deux nombres", "42");

		$résultat_attendu = [
			"id" => "jdoe/uri/0",
			"nom" => "Somme de deux nombres",
			"entrée" => "",
			"params" => "",
			"sortie_attendue" => "42",
			"caché" => false,
			"links" => [
				"question" => "https://example.com/question/uri",
				"self" => "https://example.com/test/uri/0",
			],
		];

		$testTransformer = new TestProgTransformer("uri");
		$résultat_obtenu = $testTransformer->transform(
			new GénériqueDTO(
				id: "jdoe/uri/0",
				objet: $test,
				liens: [
					"question" => "https://example.com/question/uri",
					"self" => "https://example.com/test/uri/0",
				],
			),
		);

		$this->assertEquals($résultat_attendu, $résultat_obtenu);
	}

	public function test_étant_donné_un_test_instanciée_avec_des_valeurs_lorsquon_récupère_son_transformer_on_obtient_un_objet_json_correspondant()
	{
		$test = new TestProg("Somme de deux nombres", "42", "21\n21\n", "-h");

		$résultat_attendu = [
			"id" =>
				"jdoe/aHR0cHM6Ly9kZXBvdC5jb20vcm9nZXIvcXVlc3Rpb25zX3Byb2cvZm9uY3Rpb25zMDEvYXBwZWxlcl91bmVfZm9uY3Rpb24/0",
			"nom" => "Somme de deux nombres",
			"entrée" => "21\n21\n",
			"params" => "-h",
			"sortie_attendue" => "42",
			"caché" => false,
			"links" => [
				"question" =>
					"https://example.com/question/aHR0cHM6Ly9kZXBvdC5jb20vcm9nZXIvcXVlc3Rpb25zX3Byb2cvZm9uY3Rpb25zMDEvYXBwZWxlcl91bmVfZm9uY3Rpb24",
				"self" =>
					"https://example.com/test/aHR0cHM6Ly9kZXBvdC5jb20vcm9nZXIvcXVlc3Rpb25zX3Byb2cvZm9uY3Rpb25zMDEvYXBwZWxlcl91bmVfZm9uY3Rpb24/0",
			],
		];

		$testTransformer = new TestProgTransformer(
			"aHR0cHM6Ly9kZXBvdC5jb20vcm9nZXIvcXVlc3Rpb25zX3Byb2cvZm9uY3Rpb25zMDEvYXBwZWxlcl91bmVfZm9uY3Rpb24",
		);
		$résultat_obtenu = $testTransformer->transform(
			new GénériqueDTO(
				id: "jdoe/aHR0cHM6Ly9kZXBvdC5jb20vcm9nZXIvcXVlc3Rpb25zX3Byb2cvZm9uY3Rpb25zMDEvYXBwZWxlcl91bmVfZm9uY3Rpb24/0",
				objet: $test,
				liens: [
					"question" =>
						"https://example.com/question/aHR0cHM6Ly9kZXBvdC5jb20vcm9nZXIvcXVlc3Rpb25zX3Byb2cvZm9uY3Rpb25zMDEvYXBwZWxlcl91bmVfZm9uY3Rpb24",
					"self" =>
						"https://example.com/test/aHR0cHM6Ly9kZXBvdC5jb20vcm9nZXIvcXVlc3Rpb25zX3Byb2cvZm9uY3Rpb25zMDEvYXBwZWxlcl91bmVfZm9uY3Rpb24/0",
				],
			),
		);

		$this->assertEquals($résultat_attendu, $résultat_obtenu);
	}

	public function test_étant_donné_un_test_caché_lorsquon_récupère_son_transformer_on_obtient_des_entrées_params_et_sorties_nulls()
	{
		$_ENV["APP_URL"] = "https://example.com/";

		$test = new TestProg(
			nom: "Somme de deux nombres",
			sortie_attendue: "42",
			entrée: "21\n21\n",
			params: "-h",
			caché: true,
		);

		$résultat_attendu = [
			"id" =>
				"jdoe/aHR0cHM6Ly9kZXBvdC5jb20vcm9nZXIvcXVlc3Rpb25zX3Byb2cvZm9uY3Rpb25zMDEvYXBwZWxlcl91bmVfZm9uY3Rpb24/0",
			"nom" => "Somme de deux nombres",
			"entrée" => null,
			"params" => null,
			"sortie_attendue" => null,
			"caché" => true,
			"links" => [
				"question" =>
					"https://example.com/question/aHR0cHM6Ly9kZXBvdC5jb20vcm9nZXIvcXVlc3Rpb25zX3Byb2cvZm9uY3Rpb25zMDEvYXBwZWxlcl91bmVfZm9uY3Rpb24",
				"self" =>
					"https://example.com/test/aHR0cHM6Ly9kZXBvdC5jb20vcm9nZXIvcXVlc3Rpb25zX3Byb2cvZm9uY3Rpb25zMDEvYXBwZWxlcl91bmVfZm9uY3Rpb24/0",
			],
		];

		$testTransformer = new TestProgTransformer(
			"aHR0cHM6Ly9kZXBvdC5jb20vcm9nZXIvcXVlc3Rpb25zX3Byb2cvZm9uY3Rpb25zMDEvYXBwZWxlcl91bmVfZm9uY3Rpb24",
		);
		$résultat_obtenu = $testTransformer->transform(
			new GénériqueDTO(
				id: "jdoe/aHR0cHM6Ly9kZXBvdC5jb20vcm9nZXIvcXVlc3Rpb25zX3Byb2cvZm9uY3Rpb25zMDEvYXBwZWxlcl91bmVfZm9uY3Rpb24/0",
				objet: $test,
				liens: [
					"question" =>
						"https://example.com/question/aHR0cHM6Ly9kZXBvdC5jb20vcm9nZXIvcXVlc3Rpb25zX3Byb2cvZm9uY3Rpb25zMDEvYXBwZWxlcl91bmVfZm9uY3Rpb24",
					"self" =>
						"https://example.com/test/aHR0cHM6Ly9kZXBvdC5jb20vcm9nZXIvcXVlc3Rpb25zX3Byb2cvZm9uY3Rpb25zMDEvYXBwZWxlcl91bmVfZm9uY3Rpb24/0",
				],
			),
		);

		$this->assertEquals($résultat_attendu, $résultat_obtenu);
	}
}
