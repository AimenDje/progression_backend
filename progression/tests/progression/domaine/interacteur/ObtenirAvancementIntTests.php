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

use progression\domaine\entité\Avancement;
use progression\domaine\entité\question\{QuestionProg, État};
use progression\dao\DAOFactory;
use PHPUnit\Framework\TestCase;
use Mockery;

final class ObtenirAvancementIntTests extends TestCase
{
	public function setUp(): void
	{
		parent::setUp();

		$question = new QuestionProg(niveau: "difficile", titre: "Une question difficile");

		$mockQuestionDao = Mockery::mock("progression\\dao\\question\\QuestionDAO");
		$mockQuestionDao
			->shouldReceive("get_question")
			->with("prog1/les_fonctions_01/appeler_une_fonction_paramétrée_difficile")
			->andReturn($question);

		$avancement = new Avancement(titre: "Une question facile", niveau: "facile");
		$avancement->état = État::NONREUSSI;

		$mockAvancementDAO = Mockery::mock("progression\\dao\\AvancementDAO");

		$mockAvancementDAO
			->shouldReceive("get_avancement")
			->with("jdoe", "prog1/les_fonctions_01/appeler_une_fonction_paramétrée", [])
			->andReturn($avancement);
		$mockAvancementDAO->shouldReceive("get_avancement")->andReturn(null);

		$mockDAOFactory = Mockery::mock("progression\\dao\\DAOFactory");
		$mockDAOFactory->allows()->get_avancement_dao()->andReturn($mockAvancementDAO);
		$mockDAOFactory->allows()->get_question_dao()->andReturn($mockQuestionDao);
		DAOFactory::setInstance($mockDAOFactory);
	}

	public function test_étant_donné_un_avancement_avec_un_username_et_question_uri_existant_lorsquon_cherche_par_username_et_question_uri_on_obtient_un_objet_avancementprog_correspondant()
	{
		$interacteur = new ObtenirAvancementInt();
		$résultat_obtenu = $interacteur->get_avancement(
			"jdoe",
			"prog1/les_fonctions_01/appeler_une_fonction_paramétrée",
		);

		$résultat_attendu = new Avancement(titre: "Une question facile", niveau: "facile");
		$résultat_attendu->état = État::NONREUSSI;

		$this->assertEquals($résultat_attendu, $résultat_obtenu);
	}

	public function test_étant_donné_un_user_existant_et_une_question_uri_inexistante_lorsquon_cherche_par_username_et_question_uri_on_obtient_null()
	{
		$interacteur = new ObtenirAvancementInt();

		$this->assertNull(
			$interacteur->get_avancement("jdoe", "prog1/les_fonctions_01/appeler_une_fonction_paramétrée_difficile"),
		);
	}
}
