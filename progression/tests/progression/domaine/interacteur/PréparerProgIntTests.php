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

namespace progression\domaine\interacteur;

use progression\domaine\entité\question\QuestionProg;
use progression\domaine\entité\{Exécutable, TentativeProg};
use progression\TestCase;
use Mockery;

final class PréparerProgIntTests extends TestCase
{
	public function test_étant_donné_une_questionprog_sans_todo_et_une_tentative_lorsque_préparé_on_obtient_objet_exécutable_comportant_le_exactement_le_code_utilisateur()
	{
		$résultat_attendu = new Exécutable(
			"#Commentaire invisible
             #+VISIBLE

             print(\"Allo le monde\")

             print(\"Test 123\")",

			"python",
		);

		$question = new QuestionProg();
		$question->exécutables["python"] = new Exécutable(
			"#Commentaire invisible
             #+VISIBLE

             print()

             # Rien à faire ici

             # À faire
            ",
			"python",
		);

		$tentative = new TentativeProg(
			"python",
			"#Commentaire invisible
             #+VISIBLE

             print(\"Allo le monde\")

             print(\"Test 123\")",
		);

		$interacteur = new PréparerProgInt();
		$résultat_obtenu = $interacteur->préparer_exécutable($question, $tentative);

		$this->assertEquals($résultat_attendu, $résultat_obtenu);
	}

	public function test_étant_donné_une_questionprog_à_un_todo_sans_balise_de_début_et_une_tentative_lorsque_préparé_on_obtient_objet_exécutable_comportant_le_seulement_code_utilisateur_avant_le_todo()
	{
		$résultat_attendu = new Exécutable(
			"#Commentaire invisible
             #+VISIBLE

             print(\"Allo le monde\")
             #
             # Rien à faire ici

            ",
			"python",
		);

		$question = new QuestionProg();
		$question->exécutables["python"] = new Exécutable(
			"#Commentaire invisible
             #+VISIBLE

             print()
             #-TODO
             # Rien à faire ici

            ",
			"python",
		);

		$tentative = new TentativeProg(
			"python",
			"#Commentaire invisible
             #+VISIBLE

             print(\"Allo le monde\")
             #-TODO
             # Ne devrait pas être ici

            ",
		);

		$interacteur = new PréparerProgInt();
		$résultat_obtenu = $interacteur->préparer_exécutable($question, $tentative);

		$this->assertEquals($résultat_attendu, $résultat_obtenu);
	}

	public function test_étant_donné_une_questionprog_à_un_todo_sans_balise_de_fin_et_une_tentative_lorsque_préparé_on_obtient_objet_exécutable_comportant_le_seulement_code_utilisateur_après_le_todo()
	{
		$résultat_attendu = new Exécutable(
			"#Commentaire invisible
             #+VISIBLE

             # Rien à faire ici

             #
             print(\"Allo le monde\")

            ",
			"python",
		);

		$question = new QuestionProg();
		$question->exécutables["python"] = new Exécutable(
			"#Commentaire invisible
             #+VISIBLE

             # Rien à faire ici

             #+TODO
             print()

            ",
			"python",
		);

		$tentative = new TentativeProg(
			"python",
			"#Commentaire invisible
             #+VISIBLE

             # Ne devrait pas être ici

             #+TODO
             print(\"Allo le monde\")

            ",
		);

		$interacteur = new PréparerProgInt();
		$résultat_obtenu = $interacteur->préparer_exécutable($question, $tentative);

		$this->assertEquals($résultat_attendu, $résultat_obtenu);
	}

	public function test_étant_donné_une_questionprog_à_un_todo_en_ligne_et_une_tentative_lorsque_préparé_on_obtient_objet_exécutable_comportant_le_code_utilisateur_dans_le_todo()
	{
		$résultat_attendu = new Exécutable(
			"#Commentaire invisible
             #+VISIBLE

             # Rien à faire ici

             print(\"Allo le monde\")

            ",
			"python",
		);

		$question = new QuestionProg();
		$question->exécutables["python"] = new Exécutable(
			"#Commentaire invisible
             #+VISIBLE

             # Rien à faire ici

             print(+TODO -TODO)

            ",
			"python",
		);

		$tentative = new TentativeProg(
			"python",
			"#Commentaire invisible
             #+VISIBLE

             # Ne devrait pas être ici

             print(+TODO\"Allo le monde\"-TODO)

            ",
		);

		$interacteur = new PréparerProgInt();
		$résultat_obtenu = $interacteur->préparer_exécutable($question, $tentative);

		$this->assertEquals($résultat_attendu, $résultat_obtenu);
	}

	public function test_étant_donné_une_questionprog_à_un_todo_en_début_de_ligne_et_une_tentative_lorsque_préparé_on_obtient_objet_exécutable_comportant_le_code_utilisateur_dans_le_todo()
	{
		$résultat_attendu = new Exécutable(
			"\nallo = \"Ceci est un test de TODO en début de ligne \"\nprint( allo )\n",
			"python",
		);

		$question = new QuestionProg();
		$question->exécutables["python"] = new Exécutable(
			"\n+TODOnom_de_variable-TODO = \"Ceci est un test de TODO en début de ligne \"\nprint( allo )\n",
			"python",
		);

		$tentative = new TentativeProg(
			"python",
			"\n+TODOallo-TODO = \"Ceci est un test de TODO en début de ligne \"\nprint( allo )\n",
		);

		$interacteur = new PréparerProgInt();
		$résultat_obtenu = $interacteur->préparer_exécutable($question, $tentative);

		$this->assertEquals($résultat_attendu, $résultat_obtenu);
	}

	public function test_étant_donné_une_questionprog_à_un_todo_en_début_de_code_et_une_tentative_lorsque_préparé_on_obtient_objet_exécutable_comportant_le_code_utilisateur_dans_le_todo()
	{
		$résultat_attendu = new Exécutable(
			"allo = \"Ceci est un test de TODO en début de code\"\nprint( allo )\n",
			"python",
		);

		$question = new QuestionProg();
		$question->exécutables["python"] = new Exécutable(
			"+TODOnom_de_variable-TODO = \"Ceci est un test de TODO en début de code\"\nprint( allo )\n",
			"python",
		);

		$tentative = new TentativeProg(
			"python",
			"+TODOallo-TODO = \"Ceci est un test de TODO en début de code\"\nprint( allo )\n",
		);

		$interacteur = new PréparerProgInt();
		$résultat_obtenu = $interacteur->préparer_exécutable($question, $tentative);

		$this->assertEquals($résultat_attendu, $résultat_obtenu);
	}

	public function test_étant_donné_une_questionprog_à_deux_todos_et_une_tentative_lorsque_préparé_on_obtient_objet_exécutable_comportant_le_seulement_code_utilisateur_entre_todos()
	{
		$résultat_attendu = new Exécutable(
			"#Commentaire invisible
             #+VISIBLE
             #
             print(\"Allo le monde\")
             # mais cela devrait apparaître
             # Rien à faire ici
             #
             
             print(\"Test 123\")",

			"python",
		);

		$question = new QuestionProg();
		$question->exécutables["python"] = new Exécutable(
			"#Commentaire invisible
             #+VISIBLE
             #+TODO
             print()
             # pas ceci -TODO mais cela devrait apparaître
             # Rien à faire ici
             #+TODO
             # À faire
            ",
			"python",
		);

		$tentative = new TentativeProg(
			"python",
			"#Commentaire invisible
             #+VISIBLE
             # Ne devrait pas être ici
             #+TODO
             print(\"Allo le monde\")
             #-TODO
             # Ne devrait pas être ici
             #+TODO
             
             print(\"Test 123\")",
		);

		$interacteur = new PréparerProgInt();
		$résultat_obtenu = $interacteur->préparer_exécutable($question, $tentative);

		$this->assertEquals($résultat_attendu, $résultat_obtenu);
	}

	public function test_étant_donné_une_questionprog_à_deux_todos_au_début_et_à_la_fin_et_une_tentative_lorsque_préparé_on_obtient_objet_exécutable_comportant_le_seulement_code_utilisateur_entre_todos()
	{
		$résultat_attendu = new Exécutable(
			"#Commentaire invisible
             #+VISIBLE
             print(\"Allo le monde\")
             #

             # Rien à faire ici

             #
             print(\"Test 123\")",

			"python",
		);

		$question = new QuestionProg();
		$question->exécutables["python"] = new Exécutable(
			"#Commentaire invisible
             #+VISIBLE
             print()
             #-TODO

             # Rien à faire ici

             #+TODO
             # À faire
            ",
			"python",
		);

		$tentative = new TentativeProg(
			"python",
			"#Commentaire invisible
             #+VISIBLE
             print(\"Allo le monde\")
             #-TODO
             # Ne devrait pas être ici
             #+TODO
             print(\"Test 123\")",
		);

		$interacteur = new PréparerProgInt();
		$résultat_obtenu = $interacteur->préparer_exécutable($question, $tentative);

		$this->assertEquals($résultat_attendu, $résultat_obtenu);
	}
	public function test_étant_donné_une_questionprog_avec_un_todo_à_la_toute_fin_lorsque_préparé_on_obtient_objet_exécutable_comportant_le_seulement_code_utilisateur_après_le_todo()
	{
		$résultat_attendu = new Exécutable(
			"
            # Sortie. À faire
            # 
            sortie effectuée",

			"python",
		);

		$question = new QuestionProg();
		$question->exécutables["python"] = new Exécutable(
			"
            # Sortie. À faire
            # +TODO\n",
			"python",
		);

		$tentative = new TentativeProg(
			"python",
			" Sortie. À faire
            # +TODO
            sortie effectuée",
		);

		$interacteur = new PréparerProgInt();
		$résultat_obtenu = $interacteur->préparer_exécutable($question, $tentative);

		$this->assertEquals($résultat_attendu, $résultat_obtenu);
	}

	public function test_étant_donné_une_questionprog_complexe_et_une_tentative_lorsque_préparé_on_obtient_objet_exécutable_comportant_le_seulement_code_utilisateur_entre_todos()
	{
		$résultat_attendu = new Exécutable(
			"Code utilisateur 1
             #

             # Rien à faire ici

             #
             Code utilisateur 2
             #

             test : Code utilisateur 3
             test : Code utilisateur 4

             #
             Code utilisateur 5
            ",

			"python",
		);

		$question = new QuestionProg();
		$question->exécutables["python"] = new Exécutable(
			"À remplir ici
             #-TODO

             # Rien à faire ici

             #+TODO
             À remplir ici
             #-TODO

             test : +TODOÀ remplir ici-TODO
             test : +TODOÀ remplir ici-TODO

             #+TODO
             À remplir ici
            ",
			"python",
		);

		$tentative = new TentativeProg(
			"python",
			"Code utilisateur 1
             #-TODO

             # Rien à faire ici

             #+TODO
             Code utilisateur 2
             #-TODO

             test : +TODOCode utilisateur 3-TODO
             test : +TODOCode utilisateur 4-TODO

             #+TODO
             Code utilisateur 5
            ",
		);

		$interacteur = new PréparerProgInt();
		$résultat_obtenu = $interacteur->préparer_exécutable($question, $tentative);

		$this->assertEquals($résultat_attendu, $résultat_obtenu);
	}

	public function test_étant_donné_une_questionprog_avec_un_todo_et_une_tentative_très_longue_lorsque_prépare_on_obtient_objet_exécutable_comportant_le_code_utilisateur_entre_todos()
	{
		$longue_réponse = str_repeat("#", 20000);
		$long_résultat = "#\n" . $longue_réponse . "\n#\n";

		$résultat_attendu = new Exécutable($long_résultat, "python");

		$question = new QuestionProg();
		$question->exécutables["python"] = new Exécutable("#+TODO\n#-TODO\n", "python");

		$tentative = new TentativeProg(
			"python",
			"#+TODO\n" .
				$longue_réponse .
				"\n#-TODO
             # Ne devrait pas être ici",
		);

		$interacteur = new PréparerProgInt();
		$résultat_obtenu = $interacteur->préparer_exécutable($question, $tentative);

		$this->assertEquals($résultat_attendu, $résultat_obtenu);
	}

	public function test_étant_donné_une_questionprog_très_longue_avec_un_todo_et_une_tentative_lorsque_prépare_on_obtient_objet_exécutable_comportant_le_code_utilisateur_entre_todos()
	{
		$long_préambule = str_repeat("#", 20000);
		$longue_question = $long_préambule . "#+TODO\n#-TODO\n";
		$long_résultat = $long_préambule . "#\n             print(\"Allo le monde\")\n             #\n";

		$résultat_attendu = new Exécutable($long_résultat, "python");

		$question = new QuestionProg();
		$question->exécutables["python"] = new Exécutable($longue_question, "python");

		$tentative = new TentativeProg(
			"python",
			"#Long préambule ici
             #+TODO
             print(\"Allo le monde\")
             #-TODO
             # Ne devrait pas être ici",
		);

		$interacteur = new PréparerProgInt();
		$résultat_obtenu = $interacteur->préparer_exécutable($question, $tentative);

		$this->assertEquals($résultat_attendu, $résultat_obtenu);
	}

	public function test_étant_donné_une_question_et_une_tentative_pour_un_langage_sans_ébauche_lorsquon_prépare_lexécutable_on_obtient_null()
	{
		$question = new QuestionProg();
		$question->exécutables["python"] = new Exécutable(
			"#Commentaire invisible
             #+VISIBLE
             #+TODO
             print()
             #-TODO
             # Rien à faire ici
             #+TODO
             # À faire
            ",
			"python",
		);

		$tentative = new TentativeProg("java", "sans importance");

		$interacteur = new PréparerProgInt();
		$résultat_obtenu = $interacteur->préparer_exécutable($question, $tentative);

		$this->assertNull($résultat_obtenu);
	}

	public function test_étant_donné_une_ébauche_avec_2_todo_et_tentative_avec_1_todo_lorsquon_prépare_lexécutable_on_obtient_null()
	{
		$question = new QuestionProg();
		$question->exécutables["python"] = new Exécutable(
			"#Commentaire invisible
             #+VISIBLE
             #+TODO
             print()
             #-TODO
             # Rien à faire ici
             #+TODO
             # À faire
            ",
			"python",
		);

		$tentative = new TentativeProg(
			"python",
			"#+TODO
             print(1)
             #-TODO
            ",
		);

		$interacteur = new PréparerProgInt();
		$résultat_obtenu = $interacteur->préparer_exécutable($question, $tentative);

		$this->assertNull($résultat_obtenu);
	}

	public function test_étant_donné_une_ébauche_avec_1_todo_et_tentative_avec_2_todo_lorsquon_prépare_lexécutable_on_obtient_null()
	{
		$question = new QuestionProg();
		$question->exécutables["python"] = new Exécutable(
			"#Commentaire invisible
             #+VISIBLE
             #+TODO
             print()
             #-TODO
            ",
			"python",
		);

		$tentative = new TentativeProg(
			"python",
			"#+TODO
             print(1)
             #-TODO
             #+TODO
             print(1)
             #-TODO
            ",
		);

		$interacteur = new PréparerProgInt();
		$résultat_obtenu = $interacteur->préparer_exécutable($question, $tentative);

		$this->assertNull($résultat_obtenu);
	}
}
