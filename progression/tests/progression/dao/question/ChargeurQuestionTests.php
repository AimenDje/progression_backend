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

use BadMethodCallException;
use progression\TestCase;
use Mockery;

use progression\dao\chargeur\ChargeurFactory;

final class ChargeurQuestionTests extends TestCase
{
	public function setUp(): void
	{
		parent::setUp();

		$mockChargeurFichier = Mockery::mock("progression\\dao\\question\\ChargeurQuestionFichier");
		$mockChargeurFichier->shouldReceive("récupérer_fichier")->andReturn([]);
		$mockChargeurHTTP = Mockery::mock("progression\\dao\\chargeur\\ChargeurRessourceHTTP");
		$mockChargeurHTTP->shouldReceive("récupérer_fichier")->andReturn([]);

		// ChargeurFactory
		$mockChargeurFactory = Mockery::mock("progression\\dao\\chargeur\\ChargeurFactory");
		$mockChargeurFactory->shouldReceive("get_chargeur_question_fichier")->andReturn($mockChargeurFichier);
		$mockChargeurFactory->shouldReceive("get_chargeur_question_http")->andReturn($mockChargeurHTTP);

		ChargeurFactory::set_instance($mockChargeurFactory);
	}

	public function tearDown(): void
	{
		ChargeurFactory::set_instance(null);
		parent::tearDown();
	}

	public function test_étant_donné_un_uri_de_fichier_lorsquon_charge_la_question_on_obtient_un_tableau_associatif_avec_un_uri_correct()
	{
		$résultat_obtenu = (new ChargeurQuestion())->récupérer_fichier("file://test_de_fichier");

		$this->assertEquals("file://test_de_fichier", $résultat_obtenu["uri"]);
	}

	public function test_étant_donné_un_uri_https_prog_lorsquon_charge_la_question_on_obtient_un_tableau_associatif_avec_un_uri_correct()
	{
		$résultat_obtenu = (new ChargeurQuestion())->récupérer_fichier("https://test_de_http");

		$this->assertEquals("https://test_de_http", $résultat_obtenu["uri"]);
	}

	public function test_étant_donné_un_uri_https_en_majuscules_prog_lorsquon_charge_la_question_on_obtient_un_tableau_associatif_avec_un_uri_correct()
	{
		$résultat_obtenu = (new ChargeurQuestion())->récupérer_fichier("HTTPS://test_de_http");

		$this->assertEquals("HTTPS://test_de_http", $résultat_obtenu["uri"]);
	}

	public function test_étant_donné_un_uri_invalide_charge_la_question_on_obtient_une_exception()
	{
		try {
			$résultat_obtenu = (new ChargeurQuestion())->récupérer_fichier("invalide://test_de_http");

			$this->fail();
		} catch (BadMethodCallException $e) {
			$this->assertEquals("Schéma d'URI invalide", $e->getMessage());
		}
	}
}
