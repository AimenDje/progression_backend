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

use progression\domaine\entité\{Exécutable, Test, Résultat};
use progression\dao\exécuteur\Exécuteur;
use progression\dao\DAOFactory;
use PHPUnit\Framework\TestCase;
use Mockery;

final class ExécuterProgIntTests extends TestCase
{
	public function setUp(): void
	{
		parent::setUp();

		$_SERVER["REMOTE_ADDR"] = "";
		$_SERVER["PHP_SELF"] = "";

		$mockExécuteur = Mockery::mock("progression\\dao\\exécuteur\\Exécuteur");
		$mockExécuteur
			->shouldReceive("exécuter_prog")
			->with(
				Mockery::on(function ($param) {
					return $param == new Exécutable("a=int(input())\nfor i in range(a):print('ok')", "python");
				}),
				Mockery::on(function ($param) {
					return $param == [new Test("premier test", "ok\n", "1")];
				}),
				null,
			)
			->andReturn([
				"temps_exécution" => 0.234,
				"résultats" => [["output" => "ok\n", "errors" => "", "time" => 0.6]],
			]);
		$mockExécuteur
			->shouldReceive("exécuter_prog")
			->with(
				Mockery::on(function ($param) {
					return $param == new Exécutable("a=int(input())\nfor i in range(a):print('ok')", "python");
				}),
				Mockery::on(function ($param) {
					return $param == [
						new Test("premier test", "ok\n", "1"),
						new Test("deuxième test", "ok\nok\n", "2"),
					];
				}),
				null,
			)
			->andReturn([
				"temps_exécution" => 0.124,
				"résultats" => [
					["output" => "ok\n", "errors" => "", "time" => 0.08],
					["output" => "ok\nok\n", "errors" => "", "time" => 0.04],
				],
			]);

		$mockExécuteur
			->shouldReceive("exécuter_prog")
			->with(
				Mockery::on(function ($param) {
					return $param == new Exécutable("a=a", "python");
				}),
				Mockery::on(function ($param) {
					return $param == [new Test("premier test", "ok\n", "1")];
				}),
				null,
			)
			->andReturn([
				"temps_exécution" => 0.567,
				"résultats" => [["output" => "", "errors" => "erreur", "time" => 0.04]],
			]);

		$mockDAOFactory = Mockery::mock("progression\\dao\\DAOFactory");
		$mockDAOFactory->allows()->get_exécuteur()->andReturn($mockExécuteur);
		DAOFactory::setInstance($mockDAOFactory);
	}

	public function test_étant_donné_un_exécutable_valide_et_un_test_lorsquon_les_soumet_pour_exécution_on_obtient_un_résultat_de_test_avec_ses_sorties_standards()
	{
		$exécutable_valide = new Exécutable("a=int(input())\nfor i in range(a):print('ok')", "python");
		$test = [new Test("premier test", "ok\n", "1")];

		$résultat_observé = (new ExécuterProgInt())->exécuter($exécutable_valide, $test);

		$résultat_attendu = [
			"temps_exécution" => 234,
			"résultats" => [new Résultat("ok\n", "", false, null, 600)],
		];
		$this->assertEquals($résultat_attendu, $résultat_observé);
	}

	public function test_étant_donné_un_exécutable_valide_et_deux_tests_lorsquon_les_soumet_pour_exécution_on_obtient_deux_résultats_de_test_avec_ses_sorties_standards()
	{
		$exécutable_valide = new Exécutable("a=int(input())\nfor i in range(a):print('ok')", "python");
		$test = [new Test("premier test", "ok\n", "1"), new Test("deuxième test", "ok\nok\n", "2")];

		$résultat_observé = (new ExécuterProgInt())->exécuter($exécutable_valide, $test);

		$résultat_attendu = [
			"temps_exécution" => 124,
			"résultats" => [new Résultat("ok\n", "", false, null, 80), new Résultat("ok\nok\n", "", false, null, 40)],
		];
		$this->assertEquals($résultat_attendu, $résultat_observé);
	}

	public function test_étant_donné_un_exécutable_d_erreur_et_un_test_lorsquon_les_soumet_pour_exécution_on_obtient_un_résultat_de_test_avec_ses_sorties_d_erreur()
	{
		$exécutable_erreur = new Exécutable("a=a", "python");
		$test = [new Test("premier test", "ok\n", "1")];

		$résultat_observé = (new ExécuterProgInt())->exécuter($exécutable_erreur, $test);

		$résultat_attendu = [
			"temps_exécution" => 567,
			"résultats" => [new Résultat("", "erreur", false, null, 40)],
		];
		$this->assertEquals($résultat_attendu, $résultat_observé);
	}
}
