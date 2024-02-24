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

use progression\domaine\entité\{Avancement, TentativeProg, Question, Sauvegarde, Résultat};
use progression\http\transformer\dto\AvancementDTO;
use progression\TestCase;

final class AvancementTransformerTests extends TestCase
{
	public function test_étant_donné_un_avancement_instancié_avec_des_valeurs_minimales_lorsquon_récupère_son_transformer_on_obtient_un_array_d_objets_identique()
	{
		$avancement = new Avancement();

		$avancementTransformer = new AvancementTransformer("jdoe");
		$résultats_obtenus = $avancementTransformer->transform(
			new AvancementDTO(
				objet: $avancement,
				id: "jdoe/aHR0cHM6Ly9kZXBvdC5jb20vcm9nZXIvcXVlc3Rpb25zX3Byb2cvZm9uY3Rpb25zMDEvYXBwZWxlcl91bmVfZm9uY3Rpb24",
				liens: [],
			),
		);
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/avancementTransformerTest_minimal.json",
			json_encode($résultats_obtenus),
		);
	}

	public function test_étant_donné_un_avancement_instancié_avec_des_valeurs_lorsquon_récupère_son_transformer_on_obtient_un_array_d_objets_identique()
	{
		$avancement = new Avancement([], "Ginette Reno", "Un peu plus haut, un peu plus loin");

		$avancementTransformer = new AvancementTransformer("jdoe");
		$résultats_obtenus = $avancementTransformer->transform(
			new AvancementDTO(
				id: "jdoe/aHR0cHM6Ly9kZXBvdC5jb20vcm9nZXIvcXVlc3Rpb25zX3Byb2cvZm9uY3Rpb25zMDEvYXBwZWxlcl91bmVfZm9uY3Rpb24",
				objet: $avancement,
				liens: [],
			),
		);
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/avancementTransformerTest_base.json",
			json_encode($résultats_obtenus),
		);
	}

	public function test_étant_donné_un_avancement_réussi_avec_des_valeurs_lorsquon_récupère_son_transformer_on_obtient_un_array_d_objets_identique()
	{
		$avancement = new Avancement(
			[
				new TentativeProg("python", "codeTestPython", 1614711760, false, [], 2, 324775, "feedbackTest Python"),
				new TentativeProg("java", "codeTestJava", 1614711761, true, [], 2, 3245, "feedbackTest Java"),
			],
			"Ginette Reno",
			"Un peu plus haut, un peu plus loin",
		);

		$avancementTransformer = new AvancementTransformer("jdoe");
		$avancement_obtenu = $avancementTransformer->transform(
			new AvancementDTO(
				id: "jdoe/aHR0cHM6Ly9kZXBvdC5jb20vcm9nZXIvcXVlc3Rpb25zX3Byb2cvZm9uY3Rpb25zMDEvYXBwZWxlcl91bmVfZm9uY3Rpb24",
				objet: $avancement,
				liens: [],
			),
		);

		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/avancementTransformerTest_avancement_réussi.json",
			json_encode($avancement_obtenu),
		);
	}

	public function test_étant_donné_un_avancement_non_réussi_avec_des_valeurs_lorsquon_récupère_son_transformer_on_obtient_un_array_d_objets_identique()
	{
		$avancement = new Avancement(
			[
				new TentativeProg("python", "codeTestPython", 1614711760, false, [], 2, 324775, "feedbackTest Python"),
				new TentativeProg("java", "codeTestJava", 1614711761, false, [], 2, 3245, "feedbackTest Java"),
			],
			"Ginette Reno",
			"Un peu plus haut, un peu plus loin",
		);

		$avancementTransformer = new AvancementTransformer("jdoe");

		$avancement_obtenu = $avancementTransformer->transform(
			new AvancementDTO(
				id: "jdoe/aHR0cHM6Ly9kZXBvdC5jb20vcm9nZXIvcXVlc3Rpb25zX3Byb2cvZm9uY3Rpb25zMDEvYXBwZWxlcl91bmVfZm9uY3Rpb24",
				objet: $avancement,
				liens: [],
			),
		);

		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/avancementTransformerTest_avancement_non_réussi.json",
			json_encode($avancement_obtenu),
		);
	}

	public function test_étant_donné_un_avancement_avec_ses_tentatives_lorsquon_inclut_les_tentatives_on_reçoit_un_tableau_de_tentatives()
	{
		$avancement = new Avancement(
			[
				"1614711760" => new TentativeProg(
					"python",
					"codeTestPython",
					1614711760,
					false,
					[],
					2,
					324775,
					"feedbackTest Python",
				),
				"1614711761" => new TentativeProg(
					"java",
					"codeTestJava",
					1614711761,
					true,
					[],
					2,
					3245,
					"feedbackTest Java",
				),
			],
			"Ginette Reno",
			"Un peu plus haut, un peu plus loin",
		);
		$avancementTransformer = new AvancementTransformer("jdoe");

		$résultats_obtenus = $avancementTransformer->includeTentatives(
			new AvancementDTO(id: "jdoe/dW5fYXZhbmNlbWVudA", objet: $avancement, liens: []),
		);

		$tentatives = [];
		foreach ($résultats_obtenus->getData() as $résultat) {
			$tentatives[] = $résultat;
		}
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/avancementTransformerTest_inclusion_tentatives.json",
			json_encode($tentatives),
		);
	}

	public function test_étant_donné_un_avancement_avec_ses_tentatives_lorsquon_inclut_les_tentatives_avec_résultats_on_reçoit_un_tableau_de_tentatives_avec_résultats()
	{
		$avancement = new Avancement(
			[
				"1614711760" => new TentativeProg(
					"python",
					"codeTestPython",
					1614711760,
					false,
					[new Résultat("test 1"), new Résultat("test 2", code_retour: 1)],
					2,
					324775,
					"feedbackTest Python",
				),
				"1614711761" => new TentativeProg(
					"java",
					"codeTestJava",
					1614711761,
					true,
					[new Résultat("test 3"), new Résultat("test 4")],
					2,
					3245,
					"feedbackTest Java",
				),
			],
			"Ginette Reno",
			"Un peu plus haut, un peu plus loin",
		);
		$avancementTransformer = new AvancementTransformer("jdoe");

		$résultats_obtenus = $avancementTransformer->includeTentatives(
			new AvancementDTO(id: "jdoe/dW5fYXZhbmNlbWVudA", objet: $avancement, liens: []),
		);

		$tentatives = [];
		foreach ($résultats_obtenus->getData() as $résultat) {
			$tentatives[] = $résultat;
		}
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/avancementTransformerTest_inclusion_tentatives_et_résultats.json",
			json_encode($tentatives),
		);
	}

	public function test_étant_donné_un_avancement_avec_ses_sauvegardes_lorsquon_inclut_les_sauvegardes_on_reçoit_un_tableau_de_sauvegardes()
	{
		$sauvegardes = [];
		$sauvegardes["python"] = new Sauvegarde(1620150294, "print(\"Hello world!\")");
		$sauvegardes["java"] = new Sauvegarde(1620150375, "System.out.println(\"Hello world!\");");
		$avancement = new Avancement([], "Un titre", "un niveau", $sauvegardes);

		$avancementTransformer = new AvancementTransformer("jdoe");
		$résultats_obtenus = $avancementTransformer->includeSauvegardes(
			new AvancementDTO(id: "jdoe/dW5fYXZhbmNlbWVudA", objet: $avancement, liens: []),
		);

		$listeSauvegardes = [];

		foreach ($résultats_obtenus->getData() as $résultat) {
			$listeSauvegardes[] = $résultat;
		}
		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . "/résultats_attendus/avancementTransformerTest_inclusion_sauvegardes.json",
			json_encode($listeSauvegardes),
		);
	}

	public function test_étant_donné_un_avancement_sans_tentative_lorsquon_inclut_les_tentatives_on_reçoit_un_tableau_vide()
	{
		$avancement = new Avancement([], "Un titre", "un niveau");

		$avancementTransformer = new AvancementTransformer("jdoe");
		$résultats_obtenus = $avancementTransformer->includeTentatives(
			new AvancementDTO(id: "jdoe/dW5fYXZhbmNlbWVudA", objet: $avancement, liens: []),
		);

		$this->assertEquals([], $résultats_obtenus->getData());
	}

	public function test_étant_donné_un_avancement_sans_sauvegarde_lorsquon_inclut_les_sauvegardes_on_reçoit_un_tableau_vide()
	{
		$avancement = new Avancement([], "Un titre", "un niveau");

		$avancementTransformer = new AvancementTransformer("jdoe");
		$résultats_obtenus = $avancementTransformer->includeSauvegardes(
			new AvancementDTO(id: "jdoe/dW5fYXZhbmNlbWVudA", objet: $avancement, liens: []),
		);

		$this->assertEquals([], $résultats_obtenus->getData());
	}
}
