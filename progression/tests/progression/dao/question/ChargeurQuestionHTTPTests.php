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

use progression\domaine\entité\question\QuestionProg;
use PHPUnit\Framework\TestCase;
use Mockery;

final class ChargeurQuestionHTTPTests extends TestCase
{
	private $contenu_tmp;

	public function setUp(): void
	{
		parent::setUp();
		$this->contenu_tmp = scandir("/tmp");
	}

	public function tearDown(): void
	{
		// Le contenu du répertoire /tmp n'a pas changé
		$this->assertEquals($this->contenu_tmp, scandir("/tmp"));

		parent::tearDown();
	}

	public function test_étant_donné_un_url_de_type_text_yaml_lorsquon_charge_la_question_on_obtient_un_objet_Question_correspondant()
	{
		$résultat_attendu = new QuestionProg();
		$résultat_attendu->titre = "Question de test";

		// ChargeurHTTP
		$mockChargeurHTTP = Mockery::mock("progression\\dao\\question\\ChargeurHTTP");
		$mockChargeurHTTP
			->shouldReceive("get_entêtes")
			->with("http://exemple.com/question1/info.yml")
			->andReturn([
				0 => "HTTP/1.1 200 OK",
				"Content-Type" => "text/yaml",
				"Content-Length" => "999",
				"Content-Disposition" => 'filename="info.yml"',
			]);
		// ChargeurQuestionFichier
		$mockChargeurFichier = Mockery::mock("progression\\dao\\question\\ChargeurQuestionFichier");
		$mockChargeurFichier->shouldReceive("récupérer_question")->andReturn($résultat_attendu);
		// ChargeurFactory
		$mockChargeurFactory = Mockery::mock("progression\\dao\\question\\ChargeurFactory");
		$mockChargeurFactory->shouldReceive("get_chargeur_http")->andReturn($mockChargeurHTTP);
		$mockChargeurFactory->shouldReceive("get_chargeur_question_fichier")->andReturn($mockChargeurFichier);

		$this->assertEquals(
			$résultat_attendu,
			(new ChargeurQuestionHTTP($mockChargeurFactory))->récupérer_question(
				"http://exemple.com/question1/info.yml",
			),
		);
	}

	public function test_étant_donné_un_url_de_type_application_zip_lorsquon_charge_la_question_on_obtient_un_objet_Question_correspondant()
	{
		$résultat_attendu = new QuestionProg();
		$résultat_attendu->titre = "Question de test";

		// ChargeurHTTP
		$mockChargeurHTTP = Mockery::mock("progression\\dao\\question\\ChargeurHTTP");
		$mockChargeurHTTP
			->shouldReceive("get_entêtes")
			->with("http://exemple.com/question1/question.zip")
			->andReturn([
				0 => "HTTP/1.1 200 OK",
				"Content-Type" => "application/zip",
				"Content-Length" => "999",
				"Content-Disposition" => 'filename="question.zip"',
			])
			->shouldReceive("get_url")
			->with("http://exemple.com/question1/question.zip")
			->andReturn("Contenu du fichier zip");
		// ChargeurQuestionArchive
		$mockChargeurArchive = Mockery::mock("progression\\dao\\question\\ChargeurQuestionArchive");
		$mockChargeurArchive
			->shouldReceive("récupérer_question")
			->with(Mockery::Any(), "zip")
			->andReturn($résultat_attendu);
		// ChargeurFactory
		$mockChargeurFactory = Mockery::mock("progression\\dao\\question\\ChargeurFactory");
		$mockChargeurFactory->shouldReceive("get_chargeur_http")->andReturn($mockChargeurHTTP);
		$mockChargeurFactory->shouldReceive("get_chargeur_question_archive")->andReturn($mockChargeurArchive);

		$this->assertEquals(
			$résultat_attendu,
			(new ChargeurQuestionHTTP($mockChargeurFactory))->récupérer_question(
				"http://exemple.com/question1/question.zip",
			),
		);
	}

	public function test_étant_donné_un_url_de_type_application_octet_stream_et_extension_zip_lorsquon_charge_la_question_on_obtient_un_objet_Question_correspondant()
	{
		$résultat_attendu = new QuestionProg();
		$résultat_attendu->titre = "Question de test";

		// ChargeurHTTP
		$mockChargeurHTTP = Mockery::mock("progression\\dao\\question\\ChargeurHTTP");
		$mockChargeurHTTP
			->shouldReceive("get_entêtes")
			->with("http://exemple.com/question1/question.zip")
			->andReturn([
				0 => "HTTP/1.1 200 OK",
				"Content-Type" => "application/octet-stream",
				"Content-Length" => "999",
				"Content-Disposition" => 'filename="question.zip"',
			])
			->shouldReceive("get_url")
			->with("http://exemple.com/question1/question.zip")
			->andReturn("Contenu du fichier zip");
		// ChargeurQuestionArchive
		$mockChargeurArchive = Mockery::mock("progression\\dao\\question\\ChargeurQuestionArchive");
		$mockChargeurArchive
			->shouldReceive("récupérer_question")
			->with(Mockery::Any(), "zip")
			->andReturn($résultat_attendu);
		// ChargeurFactory
		$mockChargeurFactory = Mockery::mock("progression\\dao\\question\\ChargeurFactory");
		$mockChargeurFactory->shouldReceive("get_chargeur_http")->andReturn($mockChargeurHTTP);
		$mockChargeurFactory->shouldReceive("get_chargeur_question_archive")->andReturn($mockChargeurArchive);

		$this->assertEquals(
			$résultat_attendu,
			(new ChargeurQuestionHTTP($mockChargeurFactory))->récupérer_question(
				"http://exemple.com/question1/question.zip",
			),
		);
	}

	public function test_étant_donné_un_url_de_type_inconnu_lorsquon_charge_la_question_on_obtient_une_ChargeurException()
	{
		// ChargeurHTTP
		$mockChargeurHTTP = Mockery::mock("progression\\dao\\question\\ChargeurHTTP");
		$mockChargeurHTTP
			->shouldReceive("get_entêtes")
			->with("http://exemple.com/question1/question.inc")
			->andReturn([
				0 => "HTTP/1.1 200 OK",
				"Content-Type" => "mime/inconnu",
				"Content-Length" => "999",
				"Content-Disposition" => 'filename="question.inc"',
			]);
		// ChargeurFactory
		$mockChargeurFactory = Mockery::mock("progression\\dao\\question\\ChargeurFactory");
		$mockChargeurFactory->shouldReceive("get_chargeur_http")->andReturn($mockChargeurHTTP);

		try {
			(new ChargeurQuestionHTTP($mockChargeurFactory))->récupérer_question(
				"http://exemple.com/question1/question.inc",
			);
			$this->fail();
		} catch (ChargeurException $e) {
			$this->assertEquals("Impossible de charger le fichier de type mime/inconnu", $e->getMessage());
		}
	}
	public function test_étant_donné_un_url_de_taille_non_spécifiée_lorsquon_charge_la_question_on_obtient_une_ChargeurException()
	{
		// ChargeurHTTP
		$mockChargeurHTTP = Mockery::mock("progression\\dao\\question\\ChargeurHTTP");
		$mockChargeurHTTP
			->shouldReceive("get_entêtes")
			->with("http://exemple.com/question1/info.yml")
			->andReturn([
				0 => "HTTP/1.1 200 OK",
				"Content-Type" => "text/yaml",
				"Content-Disposition" => 'filename="info.yml"',
			]);
		// ChargeurFactory
		$mockChargeurFactory = Mockery::mock("progression\\dao\\question\\ChargeurFactory");
		$mockChargeurFactory->shouldReceive("get_chargeur_http")->andReturn($mockChargeurHTTP);

		try {
			(new ChargeurQuestionHTTP($mockChargeurFactory))->récupérer_question(
				"http://exemple.com/question1/info.yml",
			);
			$this->fail();
		} catch (ChargeurException $e) {
			$this->assertEquals("Fichier de taille inconnue. On ne le chargera pas.", $e->getMessage());
		}
	}
	public function test_étant_donné_un_url_de_type_text_de_taille_trop_grande_lorsquon_charge_la_question_on_obtient_une_ChargeurException()
	{
		putenv("QUESTION_TAILLE_MAX=1000");

		// ChargeurHTTP
		$mockChargeurHTTP = Mockery::mock("progression\\dao\\question\\ChargeurHTTP");
		$mockChargeurHTTP
			->shouldReceive("get_entêtes")
			->with("http://exemple.com/question1/info.yml")
			->andReturn([
				0 => "HTTP/1.1 200 OK",
				"Content-Type" => "text/yaml",
				"Content-Length" => "9999999",
				"Content-Disposition" => 'filename="info.yml"',
			]);
		// ChargeurFactory
		$mockChargeurFactory = Mockery::mock("progression\\dao\\question\\ChargeurFactory");
		$mockChargeurFactory->shouldReceive("get_chargeur_http")->andReturn($mockChargeurHTTP);

		try {
			(new ChargeurQuestionHTTP($mockChargeurFactory))->récupérer_question(
				"http://exemple.com/question1/info.yml",
			);
			$this->fail();
		} catch (ChargeurException $e) {
			$this->assertEquals("Fichier trop volumineux (9999999 > 1000). On ne le chargera pas.", $e->getMessage());
		}
	}
	public function test_étant_donné_un_url_de_type_application_de_taille_trop_grande_lorsquon_charge_la_question_on_obtient_une_ChargeurException()
	{
		// ChargeurHTTP
		$mockChargeurHTTP = Mockery::mock("progression\\dao\\question\\ChargeurHTTP");
		$mockChargeurHTTP
			->shouldReceive("get_entêtes")
			->with("http://exemple.com/question1/question.zip")
			->andReturn([
				0 => "HTTP/1.1 200 OK",
				"Content-Type" => "application/zip",
				"Content-Length" => "9999999",
				"Content-Disposition" => 'filename="question.zip"',
			]);
		// ChargeurFactory
		$mockChargeurFactory = Mockery::mock("progression\\dao\\question\\ChargeurFactory");
		$mockChargeurFactory->shouldReceive("get_chargeur_http")->andReturn($mockChargeurHTTP);

		try {
			(new ChargeurQuestionHTTP($mockChargeurFactory))->récupérer_question(
				"http://exemple.com/question1/question.zip",
			);
			$this->fail();
		} catch (ChargeurException $e) {
			$this->assertEquals("Fichier trop volumineux (9999999 > 1000). On ne le chargera pas.", $e->getMessage());
		}
	}
}
