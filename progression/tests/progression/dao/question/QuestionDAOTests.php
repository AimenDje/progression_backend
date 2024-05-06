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

use progression\domaine\entité\question\{Question, QuestionProg, QuestionSys};
use progression\domaine\entité\{Exécutable, TestProg, TestSys};
use progression\dao\chargeur\{ChargeurException, ChargeurFactory};
use progression\TestCase;
use Mockery;
use Illuminate\Support\Facades\Cache;

final class QuestionDAOTests extends TestCase
{
	public function setUp(): void
	{
		parent::setUp();
		Cache::flush();
	}
	public function tearDown(): void
	{
		ChargeurFactory::set_instance(null);
		parent::tearDown();
	}

	public function test_étant_donné_un_uri_de_question_inexistant_lorqsuon_la_charge_on_obtient_une_exception()
	{
		$this->expectException(ChargeurException::class);
		(new QuestionDAO())->get_question("file:///tmp/inexistant.yml");
	}

	public function test_étant_donné_un_fichier_de_question_minimal_lorsquon_charge_la_question_on_obtient_les_valeurs_par_défaut()
	{
		$résultat_attendu = new QuestionProg(
			tests: [new TestProg()],
			exécutables: ["python" => new Exécutable("", "python")],
		);

		$résultat_obtenu = (new QuestionDAO())->get_question("file://" . __DIR__ . "/démo/défauts/info.yml");

		$this->assertEquals($résultat_attendu, $résultat_obtenu);
	}

	public function test_étant_donné_une_question_déjà_en_cache_lorsquon_la_récupère_de_nouveau_on_obtient_la_question_tirée_de_la_cache()
	{
		$résultat_attendu = new QuestionProg(
			tests: [new TestProg()],
			exécutables: ["python" => new Exécutable("", "python")],
		);

		$mockChargeurFichier = Mockery::mock("progression\\dao\\question\\ChargeurQuestionFichier");
		$mockChargeurFichier
			->shouldReceive("id_modif")
			->once()
			->with("file:///tmp/test/info.yml")
			->andReturn("1710000000");
		$mockChargeurFichier->shouldNotReceive("récupérer_fichier");

		$mockFactory = Mockery::mock("progression\\dao\\chargeur\\ChargeurFactory");
		$mockFactory->shouldReceive("get_chargeur_question_fichier")->andReturn($mockChargeurFichier);
		ChargeurFactory::set_instance($mockFactory);

		Cache::shouldReceive("get")
			->once()
			->with("1710000000")
			->andReturn([
				"type" => "prog",
				"ébauches" => [
					"python" => "",
				],
				"tests" => [[]],
			]);
		Cache::shouldNotReceive("put");

		$résultat_obtenu = (new QuestionDAO())->get_question("file:///tmp/test/info.yml");

		$this->assertEquals($résultat_attendu, $résultat_obtenu);
	}

	public function test_étant_donné_une_question_pas_déjà_en_cache_lorsquon_la_récupère_on_obtient_la_question_et_elle_est_placée_en_cache()
	{
		$résultat_attendu = new QuestionProg(
			tests: [new TestProg()],
			exécutables: ["python" => new Exécutable("", "python")],
		);

		$question = [
			"type" => "prog",
			"ébauches" => [
				"python" => "",
			],
			"tests" => [[]],
		];

		$mockChargeurFichier = Mockery::mock("progression\\dao\\question\\ChargeurQuestionFichier");
		$mockChargeurFichier
			->shouldReceive("id_modif")
			->once()
			->with("file:///tmp/test/info.yml")
			->andReturn("1710000000");
		$mockChargeurFichier
			->shouldReceive("récupérer_fichier")
			->with("file:///tmp/test/info.yml")
			->andReturn($question);

		$mockFactory = Mockery::mock("progression\\dao\\chargeur\\ChargeurFactory");
		$mockFactory->shouldReceive("get_chargeur_question_fichier")->andReturn($mockChargeurFichier);
		ChargeurFactory::set_instance($mockFactory);

		Cache::shouldReceive("get")->once()->with("1710000000")->andReturn(null);
		Cache::shouldReceive("put")->once()->with("1710000000", $question);
		$résultat_obtenu = (new QuestionDAO())->get_question("file:///tmp/test/info.yml");

		$this->assertEquals($résultat_attendu, $résultat_obtenu);
	}

	public function test_étant_donné_un_fichier_de_question_valide_lorsquon_charge_la_question_on_obtient_un_objet_Question_correspondant()
	{
		$résultat_attendu = new QuestionProg(
			niveau: "débutant",
			titre: "Affichage répété",
			objectif: "Exercice simple sur les itérations à nombre d'itérations fixe",
			enonce: "Saisissez un nombre sur l'entrée standard puis faites afficher la phrase «Bonjour le monde!» autant de fois.",
			auteur: "Albert Einstein",
			licence: "poétique",
			feedback_pos: "Bravo! tu es prêt à passer à un type de boucles plus complexe",
			feedback_neg: "Pour tout savoir sur les itérations énumérées : [clique ici](http://unlien.com)",
			exécutables: [
				"python" => new Exécutable("print(\"Bonjour le monde\")\n", "python"),
				"java" => new Exécutable("System.out.println(\"Bonjour le monde\");\n", "java"),
			],
			tests: [
				new TestProg(nom: "1 fois", entrée: 1, sortie_attendue: "Bonjour le monde"),
				new TestProg(
					nom: "0 fois",
					entrée: 0,
					sortie_attendue: "",
					feedback_pos: "Bien joué! 0 est aussi une entrée valable.",
					feedback_neg: "N'oublie pas les cas limites, 0 est aussi une entrée valable!",
				),
			],
		);

		$résultat_obtenu = (new QuestionDAO())->get_question(
			"file://" . __DIR__ . "/démo/boucles/boucle_énumérée/info.yml",
		);

		$this->assertEquals($résultat_attendu, $résultat_obtenu);
	}

	public function test_étant_donné_un_fichier_de_question_valide_avec_énoncé_multiparties_lorsquon_charge_la_question_on_obtient_un_objet_Question_correspondant()
	{
		$résultat_attendu = new QuestionProg(
			niveau: "débutant",
			titre: "Affichage répété",
			objectif: "Exercice simple sur les itérations à nombre d'itérations fixe",
			enonce: [
				[
					"titre" => "Instructions",
					"texte" => "On veut faire afficher une salutation un certain nombre de fois.",
				],
				[
					"titre" => "À faire",
					"texte" =>
						"Saisissez un nombre sur l'entrée standard puis faites afficher la phrase «Bonjour le monde!» autant de fois.",
				],
			],
			auteur: "Albert Einstein",
			licence: "poétique",
			feedback_pos: "Bravo! tu es prêt à passer à un type de boucles plus complexe",
			feedback_neg: "Pour tout savoir sur les itérations énumérées : [clique ici](http://unlien.com)",
			exécutables: [
				"python" => new Exécutable("print(\"Bonjour le monde\")\n", "python"),
				"java" => new Exécutable("System.out.println(\"Bonjour le monde\");\n", "java"),
			],
			tests: [
				new TestProg(nom: "1 fois", entrée: 1, sortie_attendue: "Bonjour le monde"),
				new TestProg(nom: "0 fois", entrée: 0, sortie_attendue: ""),
			],
		);

		$résultat_obtenu = (new QuestionDAO())->get_question(
			"file://" . __DIR__ . "/démo/boucles/énoncé_multiparties/info.yml",
		);

		$this->assertEquals($résultat_attendu, $résultat_obtenu);
	}

	public function test_étant_donné_un_fichier_de_question_sys_valide_sans_solution_courte_lorsquon_charge_la_question_on_obtient_un_objet_QuestionSys_correspondant()
	{
		$résultat_attendu = new QuestionSys(
			titre: "Toutes les permissions",
			niveau: "débutant",
			enonce: "Appliquez les commandes nécessaires au changement des permissions pour le fichier bonjour.txt. Le fichier doit être public pour tous.",
			objectif: "Exercice simple sur les changements de permissions.",
			image: "http://liendelimage.com:3000",
			utilisateur: "matt",
			tests: [
				new TestSys(
					"toutes permissions",
					"-rwx rwx rwx",
					validation: "ls –l test.txt",
					utilisateur: "matt",
					feedback_pos: "Bien joué!",
					feedback_neg: "Encore un effort! Toutes les permissions ne sont pas octroyées",
				),
			],
			auteur: "Ginette",
			licence: "libre",
			feedback_pos: "Bravo! tu es un pro des permissions",
			feedback_neg: "Pour tout savoir sur les permissions : [clique ici](http://unlien.com)",
		);

		$résultat_obtenu = (new QuestionDAO())->get_question(
			"file://" . __DIR__ . "/démo/permissions_sys/permissions/info.yml",
		);

		$this->assertEquals($résultat_attendu, $résultat_obtenu);
	}

	public function test_étant_donné_un_fichier_de_question_sys_valide_avec_une_solution_courte_lorsquon_charge_la_question_on_obtient_un_objet_QuestionSys_correspondant()
	{
		$résultat_attendu = new QuestionSys(
			titre: "Toutes les permissions",
			niveau: "débutant",
			enonce: "Appliquez les commandes nécessaires au changement des permissions pour le fichier bonjour.txt. Le fichier doit être public pour tous.",
			objectif: "Exercice simple sur les changements de permissions.",
			image: "http://liendelimage.com:3000",
			utilisateur: "matt",
			solution: "34",
			auteur: "Ginette",
			licence: "libre",
			feedback_pos: "Bravo! tu es un pro des permissions",
			feedback_neg: "Pour tout savoir sur les permissions : [clique ici](http://unlien.com)",
		);

		$résultat_obtenu = (new QuestionDAO())->get_question(
			"file://" . __DIR__ . "/démo/permissions_sys/solution_courte/info.yml",
		);

		$this->assertEquals($résultat_attendu, $résultat_obtenu);
	}
}
