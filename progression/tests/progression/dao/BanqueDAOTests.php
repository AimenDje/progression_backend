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

namespace progression\dao;

use progression\TestCase;
use progression\dao\EntitéDAO;
use progression\dao\chargeur\Chargeur;
use progression\domaine\entité\question\{QuestionProg, QuestionSys};
use progression\domaine\entité\{Exécutable, TestProg, TestSys};
use progression\domaine\entité\banque\Banque;
use progression\domaine\entité\banque\QuestionBanque;

final class BanqueDAOTests extends TestCase
{
	public function setUp(): void
	{
		parent::setUp();
		app("db")->connection()->beginTransaction();
	}

	public function tearDown(): void
	{
		app("db")->connection()->rollBack();
		parent::tearDown();
	}

	public function test_étant_donné_un_utilisateur_qui_nexiste_pas_lorsquon_récupère_toutes_les_banques_on_obtient_une_DAOException()
	{
		$résultat_observé = (new BanqueDAO())->get_tous("joe");

		$résultats_attendu = [];

		$this->assertEquals($résultats_attendu, $résultat_observé);
	}

	public function test_étant_donné_un_utilisateur_existant_qui_na_pas_de_banque_lorsquon_récupère_toutes_les_banques_on_obtient_une_liste_vide()
	{
		$résultat_observé = (new BanqueDAO())->get_tous("jdoe");

		$résultats_attendu = [];

		$this->assertEquals($résultats_attendu, $résultat_observé);
	}

	public function test_étant_donné_un_utilisateur_qui_possède_deux_banques_de_questions_lorsquon_récupère_ses_banque_on_obtient_les_deux_banques()
	{
		$résultats_attendu = [
			1 => new Banque(
				"Test banque de questions 1 - fichier yaml valide",
				"file:///var/www/progression/tests/progression/dao/démo/banque_1/contenu.yml",
			),
			2 => new Banque(
				"Test banque de questions 2 - fichier yaml valide",
				"file:///var/www/progression/tests/progression/dao/démo/banque_2/contenu.yml",
			),
		];

		$résultat_observé = (new BanqueDAO())->get_tous("bob");
		$this->assertEquals($résultats_attendu, $résultat_observé);
	}

	public function test_étant_donné_un_utilisateur_qui_possède_deux_banques_de_questions_lorsquon_récupère_ses_banque_et_leurs_questions_on_obtient_les_deux_banques_et_les_questions()
	{
		$questions_1 = [
			new QuestionProg(
				titre: "Question 1",
				exécutables: ["python" => new Exécutable(code: "print(42)", lang: "python")],
				tests: [new TestProg(sortie_attendue: 42)],
			),
			new QuestionProg(
				titre: "Question 2",
				exécutables: ["python" => new Exécutable(code: "print(42)", lang: "python")],
				tests: [new TestProg(sortie_attendue: 42)],
			),
		];
		$questions_2 = [
			new QuestionSys(titre: "Question 3", image: "ubuntu", tests: [new TestSys(validation: "true")]),
			new QuestionSys(titre: "Question 4", image: "ubuntu", tests: [new TestSys(validation: "true")]),
		];

		$résultats_attendu = [
			1 => new Banque(
				"Test banque de questions 1 - fichier yaml valide",
				"file:///var/www/progression/tests/progression/dao/démo/banque_1/contenu.yml",
				$questions_1,
			),
			2 => new Banque(
				"Test banque de questions 2 - fichier yaml valide",
				"file:///var/www/progression/tests/progression/dao/démo/banque_2/contenu.yml",
				$questions_2,
			),
		];

		$résultat_observé = (new BanqueDAO())->get_tous("bob", ["questions"]);

		$this->assertEquals($résultats_attendu, $résultat_observé);
	}
}
