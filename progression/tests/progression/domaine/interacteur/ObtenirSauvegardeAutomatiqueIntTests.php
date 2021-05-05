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

use progression\domaine\entité\{Sauvegarde, User, Question, QuestionProg};
use progression\dao\DAOFactory;
use PHPUnit\Framework\TestCase;
use Mockery;

final class ObtenirSauvegardeAutomatiqueIntTests extends TestCase
{
	public function setUp(): void
	{
		parent::setUp();

        // UserDAO
		$mockUserDAO = Mockery::mock("progression\dao\UserDAO");
		$mockUserDAO
			->shouldReceive("get_user")
			->with("jdoe")
			->andReturn(new User("jdoe"));
		$mockUserDAO
			->shouldReceive("get_user")
			->with("Marcel")
			->andReturn(null);

        // Question
		$question = new QuestionProg();
		$question->type = Question::TYPE_PROG;
		$question->nom = "appeler_une_fonction_paramétrée";
		$question->uri = "https://depot.com/roger/questions_prog/fonctions01/appeler_une_fonction";
		$mockQuestionDAO = Mockery::mock("progression\dao\QuestionDAO");
		$mockQuestionDAO
			->shouldReceive("get_question")
			->with("https://depot.com/roger/questions_prog/fonctions01/appeler_une_fonction")
			->andReturn($question);
		$mockQuestionDAO
			->shouldReceive("get_question")
			->with("https://depot.com/roger/questions_prog/question_inexistante")
			->andReturn(null);

		// Sauvegarde
        $sauvegarde = new Sauvegarde
        (
            "jdoe",
            "https://depot.com/roger/questions_prog/fonctions01/appeler_une_fonction",
            1620150294,
            "python",
            "print(\"Hello world!\")"
        );
		$mockSauvegardeDAO = Mockery::mock("progression\dao\SauvegardeDAO");
		$mockSauvegardeDAO
			->shouldReceive("get_sauvegarde")
			->with("jdoe", "https://depot.com/roger/questions_prog/fonctions01/appeler_une_fonction", "python")
			->andReturn($sauvegarde);
		$mockSauvegardeDAO
			->shouldReceive("get_sauvegarde")
			->with("Marcel", "https://depot.com/roger/questions_prog/fonctions01/appeler_une_fonction", "python")
			->andReturn(null);
        $mockSauvegardeDAO
			->shouldReceive("get_sauvegarde")
			->with("jdoe", "https://depot.com/roger/questions_prog/question_inexistante", "python")
			->andReturn(null);

		// DAOFactory
		$mockDAOFactory = Mockery::mock("progression\dao\DAOFactory");
		$mockDAOFactory->shouldReceive("get_user_dao")->andReturn($mockUserDAO);
        $mockDAOFactory->shouldReceive("get_question_dao")->andReturn($mockQuestionDAO);
		$mockDAOFactory->shouldReceive("get_sauvegarde_dao")->andReturn($mockSauvegardeDAO);
		DAOFactory::setInstance($mockDAOFactory);
	}

	public function tearDown(): void
	{
		Mockery::close();
	}

	public function test_étant_donné_le_username_dun_utilisateur_inexistant_lorsquon_appelle_get_sauvegarde_on_obtient_un_objet_null()
	{
		$interacteur = new ObtenirSauvegardeAutomatiqueInt();
		$résultat_obtenu = $interacteur->get_sauvegarde_automatique(
			"Marcel",
			"https://depot.com/roger/questions_prog/fonctions01/appeler_une_fonction",
			"python"
		);

		$this->assertNull($résultat_obtenu);
	}

    public function test_étant_donné_luri_dune_question_inexistante_lorsquon_appelle_get_sauvegarde_on_obtient_un_objet_null()
	{
		$interacteur = new ObtenirSauvegardeAutomatiqueInt();
		$résultat_obtenu = $interacteur->get_sauvegarde_automatique(
			"jdoe",
			"https://depot.com/roger/questions_prog/question_inexistante",
			"python"
		);

		$this->assertNull($résultat_obtenu);
	}

    public function test_étant_donné_luri_dune_question_existante_un_username_existant_et_le_bon_langage_lorsquon_appelle_get_sauvegarde_on_obtient_un_objet_sauvegarde_correspondant()
	{
		$interacteur = new ObtenirSauvegardeAutomatiqueInt();
		$résultat_obtenu = $interacteur->get_sauvegarde_automatique(
			"jdoe",
			"https://depot.com/roger/questions_prog/fonctions01/appeler_une_fonction",
			"python"
		);
        $résultat_attendu = new Sauvegarde
        (
            "jdoe",
            "https://depot.com/roger/questions_prog/fonctions01/appeler_une_fonction",
            1620150294,
            "python",
            "print(\"Hello world!\")"
        );
		$this->assertEquals($résultat_attendu , $résultat_obtenu);
	}
}
