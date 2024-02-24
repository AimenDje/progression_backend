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

use RuntimeException;
use PHPUnit\Framework\TestCase;
use ZipArchive;

final class ChargeurQuestionArchiveTests extends TestCase
{
	private $contenu_tmp;

	public function setUp(): void
	{
		$this->contenu_tmp = scandir("/tmp");
	}

	public function tearDown(): void
	{
		// Le contenu du répertoire /tmp n'a pas changé
		$this->assertEquals($this->contenu_tmp, scandir("/tmp"));

		parent::tearDown();
	}

	public function test_étant_donné_un_uri_de_fichier_zip_lorsquon_charge_la_question_on_obtient_un_tableau_associatif_représentant_la_question()
	{
		$résultat_attendu["type"] = "prog";
		$résultat_attendu["titre"] = "Affichage répété";
		$résultat_attendu["objectif"] = "Exercice simple sur les itérations à nombre d'itérations fixe";
		$résultat_attendu["énoncé"] =
			"Saisissez un nombre sur l'entrée standard puis faites afficher la phrase «Bonjour le monde!» autant de fois.";
		$résultat_attendu["auteur"] = "Albert Einstein";
		$résultat_attendu["licence"] = "poétique";
		$résultat_attendu["niveau"] = "débutant";
		$résultat_attendu["rétroactions"] = [
			"négative" => "Pour tout savoir sur les itérations énumérées : [clique ici](http://unlien.com)",
			"positive" => "Bravo! tu es prêt à passer à un type de boucles plus complexe",
		];

		// Ébauches
		$résultat_attendu["ébauches"] = [
			"python" =>
				"#+VISIBLE\nnb_répétitions = int( input() )\n\n#+TODO\n\nprint( \"Bonjour le monde\" )\n\n#-TODO\n\n#-VISIBLE\n",
			"java" =>
				"import java.util.Scanner;\n\npublic class exec {\n\n//+VISIBLE\n\npublic static void main(String[] args) {\n\n	Scanner input = new Scanner( System.in );\n		\n	nb_répétitions = input.nextInt();\n\n//+TODO\n\n	System.out.println( \"Bonjour le monde\" );\n\n//-TODO\n\n	}\n	\n//-VISIBLE\n\n}\n",
		];

		// Tests
		$résultat_attendu["tests"] = [
			[
				"nom" => "10 fois",
				"sortie" =>
					"Bonjour le monde\nBonjour le monde\nBonjour le monde\nBonjour le monde\nBonjour le monde\nBonjour le monde\nBonjour le monde\nBonjour le monde\nBonjour le monde\nBonjour le monde\n",
				"entrée" => "10",
			],
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
			[
				"nom" => "2 fois",
				"sortie" => "Bonjour\nBonjour\n",
				"entrée" => "2",
				"rétroactions" => [
					"positive" => "Bien joué!",
					"négative" => "Rien à dire",
				],
			],
		];

		$uri = __DIR__ . "/démo/boucle_énumérée.zip";

		$résultat_obtenu = (new ChargeurQuestionArchive())->récupérer_question($uri, "zip");

		$this->assertEquals($résultat_attendu, $résultat_obtenu);
	}

	public function test_étant_donné_un_type_de_fichier_inconnu_lorsquon_charge_la_question_on_obtient_une_ChargeurException()
	{
		$uri = __DIR__ . "/démo/inconnu.inc";

		try {
			$résultat_obtenu = (new ChargeurQuestionArchive())->récupérer_question($uri, "inc");
			$this->fail();
		} catch (ChargeurException $résultat_obtenu) {
			$this->assertEquals("Type d'archive inc non implémenté.", $résultat_obtenu->getMessage());
		}
	}

	public function test_étant_donné_un_uri_de_fichier_inexistant_lorsquon_charge_la_question_on_obtient_une_ChargeurException_ER_NOENT()
	{
		$uri = __DIR__ . "/démo/inexistant.zip";

		try {
			$résultat_obtenu = (new ChargeurQuestionArchive())->récupérer_question($uri, "zip");
			$this->fail();
		} catch (ChargeurException $résultat_obtenu) {
			$this->assertStringMatchesFormat(
				"Impossible de lire l'archive %s/démo/inexistant.zip (err.: " . ZipArchive::ER_NOENT . ")",
				$résultat_obtenu->getMessage(),
			);
		}
	}

	public function test_étant_donné_un_uri_de_fichier_zip_invalide_lorsquon_charge_la_question_on_obtient_une_ChargeurException_ER_NOZIP()
	{
		$uri = __DIR__ . "/démo/invalide.zip";

		try {
			$résultat_obtenu = (new ChargeurQuestionArchive())->récupérer_question($uri, "zip");
			$this->fail();
		} catch (ChargeurException $résultat_obtenu) {
			$this->assertStringMatchesFormat(
				"Impossible de lire l'archive %s/démo/invalide.zip (err.: " . ZipArchive::ER_NOZIP . ")",
				$résultat_obtenu->getMessage(),
			);
		}
	}
}
