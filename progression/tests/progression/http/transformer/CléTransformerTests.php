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

namespace progression\http\transformer;

use progression\domaine\entité\Clé;
use PHPUnit\Framework\TestCase;

final class CléTransformerTests extends TestCase
{
	public function setUp(): void
	{
		parent::setUp();

		$_ENV["APP_URL"] = "https://example.com/";
	}

	public function test_étant_donné_une_clé_d_authentification_lorsquon_la_transforme_on_obtient_un_array_identifque()
	{
		$transformer = new CléTransformer();

		$clé = new Clé("1234", "2021-06-25 00:00:00", "2021-06-26 00:00:00", 1);
		$clé->id = "jdoe/clé%20de%20test";

		$résultat_obtenu = $transformer->transform($clé);

		$résultat_attendu = [
			"id" => "jdoe/clé%20de%20test",
			"secret" => "1234",
			"création" => "2021-06-25 00:00:00",
			"expiration" => "2021-06-26 00:00:00",
			"portée" => 1,
			"links" => [
				"self" => "https://example.com/cle/jdoe/clé%20de%20test",
			],
		];

		$this->assertEquals($résultat_attendu, $résultat_obtenu);
	}
}
