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

use progression\domaine\entité\{Question, AvancementProg, TentativeProg};
use PHPUnit\Framework\TestCase;
use \Mockery;

final class ObtenirAvancementIntTests extends TestCase
{
	public function test_étant_donné_un_avancement_avec_un_user_id_et_question_id_existant_lorsque_cherché_par_user_id_et_question_id_on_obtient_un_objet_avancementprog_correspondant()
	{
		$résultat_attendu = new AvancementProg("prog1/les_fonctions_01/appeler_une_fonction_paramétrée", "jdoe");
		$résultat_attendu->chemin = "prog1/les_fonctions_01/appeler_une_fonction_paramétrée";

		$mockAvancementDAO = Mockery::mock("progression\dao\AvancementDAO");
		$mockAvancementDAO
			->shouldReceive("get_avancement")
			->with("jdoe", "prog1/les_fonctions_01/appeler_une_fonction_paramétrée")
			->andReturn($résultat_attendu);

		$mockDAOFactory = Mockery::mock("progression\dao\DAOFactory");
		$mockDAOFactory
			->allows()
			->get_avancement_dao()
			->andReturn($mockAvancementDAO);

		$interacteur = new ObtenirAvancementInt($mockDAOFactory);
		$résultat_obtenu = $interacteur->get_avancement(
			"jdoe",
			"prog1/les_fonctions_01/appeler_une_fonction_paramétrée"
		);

		$this->assertEquals($résultat_attendu, $résultat_obtenu);
	}

	public function test_étant_donné_une_tentative_avec_des_attributs_lorsque_cherché_par_user_id_question_id_et_date_soumission_on_obtient_un_objet_tentative_correspondant()
	{
		$résultat_attendu = new TentativeProg("java", "System.out.println();", 1614711760);

		$mockAvancementDAO = Mockery::mock("progression\dao\AvancementDAO");
		$mockAvancementDAO
			->shouldReceive("get_tentative")
			->with("jdoe", "prog1/les_fonctions_01/appeler_une_fonction_paramétrée", 1614711760)
			->andReturn($résultat_attendu);

		$mockDAOFactory = Mockery::mock("progression\dao\DAOFactory");
		$mockDAOFactory
			->allows()
			->get_avancement_dao()
			->andReturn($mockTentativeDAO);

		$interacteur = new ObtenirAvancementInt($mockDAOFactory);
		$résultat_obtenu = $interacteur->get_tentative(
			"jdoe",
			"prog1/les_fonctions_01/appeler_une_fonction_paramétrée",
			1614711760
		);

		$this->assertEquals($résultat_attendu, $résultat_obtenu);
	}
}
