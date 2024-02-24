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

namespace progression\dao\question;

use DomainException;
use progression\TestCase;

final class ChargeurQuestionFichierTests extends TestCase
{
	public function test_étant_donné_un_uri_de_fichier_lorsquon_charge_la_question_on_obtient_un_tableau_associatif_représentant_la_question()
	{
		$résultat_attendu = [
			"type" => "prog",
			"titre" => "Affichage répété",
			"objectif" => "Exercice simple sur les itérations à nombre d'itérations fixe",
			"énoncé" =>
				"Saisissez un nombre sur l'entrée standard puis faites afficher la phrase «Bonjour le monde!» autant de fois.",
			"auteur" => "Albert Einstein",
			"licence" => "poétique",
			"niveau" => "débutant",
			"rétroactions" => [
				"négative" => "Pour tout savoir sur les itérations énumérées : [clique ici](http://unlien.com)",
				"positive" => "Bravo! tu es prêt à passer à un type de boucles plus complexe",
			],
			// Ébauches
			"ébauches" => [
				"python" => "print(\"Bonjour le monde\")\n",
				"java" => "System.out.println(\"Bonjour le monde\");\n",
			],
			// Tests
			"tests" => [
				[
					"nom" => "1 fois",
					"sortie" => "Bonjour le monde",
					"entrée" => "1",
				],
				[
					"nom" => "0 fois",
					"sortie" => "",
					"entrée" => "0",
					"rétroactions" => [
						"positive" => "Bien joué! 0 est aussi une entrée valable.",
						"négative" => "N'oublie pas les cas limites, 0 est aussi une entrée valable!",
					],
				],
			],
		];

		$uri = "file://" . __DIR__ . "/démo/boucles/boucle_énumérée/info.yml";

		$résultat_obtenu = (new ChargeurQuestionFichier())->récupérer_question($uri);

		$this->assertEquals($résultat_attendu, $résultat_obtenu);
	}

	public function test_étant_donné_un_uri_de_fichier_yaml_invalide_lorsquon_charge_la_question_on_obtient_une_ChargeurException_err_1()
	{
		$uri = "file://" . __DIR__ . "/démo/yaml_invalide/info.yml";

		try {
			$résultat_obtenu = (new ChargeurQuestionFichier())->récupérer_question($uri);
			$this->fail();
		} catch (ChargeurException $résultat_obtenu) {
			$this->assertEquals("Le fichier {$uri} est invalide. (err: 1)", $résultat_obtenu->getMessage());
		}
	}

	public function test_étant_donné_un_uri_de_fichier_question_invalide_lorsquon_charge_la_question_on_obtient_une_ChargeurException_err_1()
	{
		$uri = "file://" . __DIR__ . "/démo/question_invalide/info.yml";

		try {
			$résultat_obtenu = (new ChargeurQuestionFichier())->récupérer_question($uri);
			$this->fail();
		} catch (ChargeurException $résultat_obtenu) {
			$this->assertEquals("Le fichier {$uri} est invalide. (err: 1)", $résultat_obtenu->getMessage());
		}
	}

	public function test_étant_donné_un_uri_de_fichier_non_existant_lorsquon_charge_la_question_on_obtient_null()
	{
		$uri = "file://" . __DIR__ . "/démo/inexistant/info.yml";

		$this->assertNull((new ChargeurQuestionFichier())->récupérer_question($uri));
	}
}
