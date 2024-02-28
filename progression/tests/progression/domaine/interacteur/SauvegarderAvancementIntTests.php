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

use progression\domaine\entité\question\{Question, QuestionProg};
use progression\domaine\entité\{TentativeProg, Avancement, TentativeSys};
use progression\domaine\entité\user\User;
use progression\domaine\interacteur\SauvegarderAvancementInt;
use progression\dao\DAOFactory;
use progression\TestCase;
use progression\dao\question\QuestionDAO;
use Mockery;

final class SauvegarderAvancementIntTests extends TestCase
{
	public function setUp(): void
	{
		parent::setUp();

		$mockUserDAO = Mockery::mock("progression\\dao\\UserDAO");
		$mockUserDAO->allows()->get_user("jdoe")->andReturn(new User(username: "jdoe", date_inscription: 0));

		$mockAvancementDAO = Mockery::mock("progression\\dao\\AvancementDAO");
		$mockAvancementDAO
			->shouldReceive("get_avancement")
			->with("jdoe", "file:///prog1/les_fonctions/appeler_une_fonction/info.yml", [])
			->andReturn(
				new Avancement(tentatives: [], titre: "Appeler une fonction", niveau: "facile", extra: "Infos extras"),
			);
		$mockAvancementDAO
			->shouldReceive("get_avancement")
			->with("jdoe", "file:///une_question_modifiée/info.yml", [])
			->andReturn(
				new Avancement(tentatives: [], titre: "Ancien titre", niveau: "ancien niveau", extra: "Infos extras"),
			);
		$mockAvancementDAO
			->shouldReceive("get_avancement")
			->with("jdoe", Mockery::Any(), Mockery::Any())
			->andReturn(null);

		$question_existante = new QuestionProg(titre: "Appeler une fonction", niveau: "facile");
		$nouvelle_question = new QuestionProg(titre: "Nouvelle question", niveau: "facile");
		$question_modifiée = new QuestionProg(titre: "Nouveau titre", niveau: "Nouveau niveau");

		$mockQuestionDao = Mockery::mock("progression\\dao\\question\\QuestionDAO");
		$mockQuestionDao
			->shouldReceive("get_question")
			->with("file:///prog1/les_fonctions/appeler_une_fonction/info.yml")
			->andReturn($question_existante);
		$mockQuestionDao
			->shouldReceive("get_question")
			->with("file:///une_question_modifiée/info.yml")
			->andReturn($question_modifiée);
		$mockQuestionDao->shouldReceive("get_question")->with(Mockery::Any())->andReturn(null);

		$mockDAOFactory = Mockery::mock("progression\\dao\\DAOFactory");
		$mockDAOFactory->allows()->get_question_dao()->andReturn($mockQuestionDao);
		$mockDAOFactory->allows()->get_user_dao()->andReturn($mockUserDAO);
		$mockDAOFactory->allows()->get_avancement_dao()->andReturn($mockAvancementDAO);
		DAOFactory::setInstance($mockDAOFactory);
	}

	public function test_étant_donné_un_avancement_existant_lorsquon_sauvegarde_l_avancement_non_modifié_il_est_sauvegardé_et_retourné_tel_quel()
	{
		$avancement_sauvegardé = new Avancement(titre: "Appeler une fonction", niveau: "facile", extra: "Infos extras");

		DAOFactory::getInstance()
			->get_avancement_dao()
			->shouldReceive("save")
			->once()
			->withArgs(function ($username, $question_uri, $type, $avancement) use ($avancement_sauvegardé) {
				return $username == "jdoe" &&
					$question_uri == "file:///prog1/les_fonctions/appeler_une_fonction/info.yml" &&
					$type == "prog" &&
					$avancement == $avancement_sauvegardé;
			})
			->andReturn([
				"file:///prog1/les_fonctions/appeler_une_fonction/info.yml" => $avancement_sauvegardé,
			]);

		$interacteur = new SauvegarderAvancementInt();
		$résultat_observé = $interacteur->sauvegarder(
			"jdoe",
			"file:///prog1/les_fonctions/appeler_une_fonction/info.yml",
			new Avancement(titre: "Appeler une fonction", niveau: "facile", extra: "Infos extras"),
		);

		$résultat_attendu = [
			"file:///prog1/les_fonctions/appeler_une_fonction/info.yml" => $avancement_sauvegardé,
		];

		$this->assertEquals($résultat_attendu, $résultat_observé);
	}

	public function test_étant_donné_une_question_inexistante_lorsquon_sauvegarde_un_avancement_on_obtient_une_exception()
	{
		DAOFactory::getInstance()->get_avancement_dao()->shouldNotReceive("save");

		$interacteur = new SauvegarderAvancementInt();

		$this->expectException(IntégritéException::class);

		$interacteur->sauvegarder(
			"jdoe",
			"file:///question_inexistante/info.yml",
			new Avancement(titre: "Appeler une fonction", niveau: "facile", extra: "Infos extras"),
		);
	}
	public function test_étant_donné_un_avancement_existant_lorsquon_sauvegarde_l_avancement_modifié_il_est_sauvegardé_et_retourné_mutatis_mutandis()
	{
		$avancement_modifié = new Avancement(titre: "Titre modifié", niveau: "Niveau modifié", extra: "Extra modifié");
		$avancement_sauvegardé = new Avancement(
			titre: "Appeler une fonction",
			niveau: "facile",
			extra: "Extra modifié",
		);

		DAOFactory::getInstance()
			->get_avancement_dao()
			->shouldReceive("save")
			->once()
			->withArgs(function ($username, $question_uri, $type, $avancement) use ($avancement_sauvegardé) {
				return $username == "jdoe" &&
					$question_uri == "file:///prog1/les_fonctions/appeler_une_fonction/info.yml" &&
					$type == "prog" &&
					$avancement == $avancement_sauvegardé;
			})
			->andReturn([
				"file:///prog1/les_fonctions/appeler_une_fonction/info.yml" => $avancement_sauvegardé,
			]);

		$interacteur = new SauvegarderAvancementInt();
		$résultat_observé = $interacteur->sauvegarder(
			"jdoe",
			"file:///prog1/les_fonctions/appeler_une_fonction/info.yml",
			$avancement_modifié,
		);

		$résultat_attendu = [
			"file:///prog1/les_fonctions/appeler_une_fonction/info.yml" => $avancement_sauvegardé,
		];

		$this->assertEquals($résultat_attendu, $résultat_observé);
	}
	public function test_étant_donné_un_avancement_existant_et_une_question_modifiée_lorsquon_sauvegarde_l_avancement_modifié_il_est_sauvegardé_et_retourné_mutatis_mutandis()
	{
		$avancement_modifié = new Avancement(extra: "Extra modifié");
		$avancement_sauvegardé = new Avancement(
			titre: "Nouveau titre",
			niveau: "Nouveau niveau",
			extra: "Extra modifié",
		);

		DAOFactory::getInstance()
			->get_avancement_dao()
			->shouldReceive("save")
			->once()
			->withArgs(function ($username, $question_uri, $type, $avancement) use ($avancement_sauvegardé) {
				return $username == "jdoe" &&
					$question_uri == "file:///une_question_modifiée/info.yml" &&
					$type == "prog" &&
					$avancement == $avancement_sauvegardé;
			})
			->andReturn([
				"file:///une_question_modifiée/info.yml" => $avancement_sauvegardé,
			]);

		$interacteur = new SauvegarderAvancementInt();
		$résultat_observé = $interacteur->sauvegarder(
			"jdoe",
			"file:///une_question_modifiée/info.yml",
			$avancement_modifié,
		);

		$résultat_attendu = [
			"file:///une_question_modifiée/info.yml" => $avancement_sauvegardé,
		];

		$this->assertEquals($résultat_attendu, $résultat_observé);
	}
}
