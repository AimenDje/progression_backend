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

namespace progression;

use Laravel\Lumen\Testing\TestCase as BaseTestCase;
use progression\dao\DAOFactory;
use Mockery;

abstract class TestCase extends BaseTestCase
{
	private $env;

	public function setUp(): void
	{
		parent::setUp();

		//Sauvegarde de l'environnement
		$this->env = getenv(null);
	}

	public function tearDown(): void
	{
		DAOFactory::setInstance(null);
		Mockery::close();

		//Réinitialise l'environnement
		foreach ($this->env as $k => $e) {
			putenv("{$k}={$e}");
		}

		parent::tearDown();
	}

	public function createApplication()
	{
		return require __DIR__ . "/../../app/bootstrap/app.php";
	}
}
