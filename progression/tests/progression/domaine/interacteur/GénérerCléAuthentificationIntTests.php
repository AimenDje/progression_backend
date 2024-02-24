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

use progression\domaine\entité\clé\{Clé, Portée};
use progression\dao\DAOFactory;
use PHPUnit\Framework\TestCase;
use Mockery;

final class GénérerCléAuthentificationIntTests extends TestCase
{
	public function setUp(): void
	{
		$mockCléDAO = Mockery::mock("progression\\dao\\CléDAO");
		$mockCléDAO
			->allows("get_clé")
			->with("jdoe", "clé existante")
			->andReturn(
				new Clé(null, (new \DateTime())->getTimestamp(), (new \DateTime())->getTimestamp() + 1, Portée::AUTH),
			);
		$mockCléDAO->allows("get_clé")->andReturn(null);

		$mockDAOFactory = Mockery::mock("progression\\dao\\DAOFactory");
		$mockDAOFactory->allows()->get_clé_dao()->andReturn($mockCléDAO);
		DAOFactory::setInstance($mockDAOFactory);
	}

	public function test_étant_donné_un_utilisateur_jdoe_lorsquon_génère_une_clé_d_authentification_une_nouvelle_clé_est_sauvegardée()
	{
		$mockCléDAO = DAOFactory::getInstance()->get_clé_dao();

		$clé_test = new Clé("", new \DateTime(), 0, Portée::AUTH);

		$mockCléDAO
			->shouldReceive("save")
			->once()
			->withArgs(function ($username, $nom, $clé) use ($clé_test) {
				return $username == "jdoe" && $nom == "nouvelle clé" && ($clé = $clé_test);
			})
			->andReturn(["nouvelle clé" => $clé_test]);

		$résultat_obtenu = (new GénérerCléAuthentificationInt())->générer_clé("jdoe", "nouvelle clé");

		$this->assertNotNull($résultat_obtenu);
	}

	public function test_étant_donné_un_utilisateur_jdoe_lorsquon_génère_deux_clés_d_authentification_elles_ont_des_secrets_différents()
	{
		$mockCléDAO = DAOFactory::getInstance()->get_clé_dao();

		$mockCléDAO
			->allows("save")
			->with("jdoe", "clé 1", Mockery::Any())
			->andReturnUsing(function ($username, $nom, $clé) {
				return ["clé 1" => $clé];
			});
		$mockCléDAO
			->allows("save")
			->with("jdoe", "clé 2", Mockery::Any())
			->andReturnUsing(function ($username, $nom, $clé) {
				return ["clé 2" => $clé];
			});

		$clé1 = (new GénérerCléAuthentificationInt())->générer_clé("jdoe", "clé 1")["clé 1"];
		$clé2 = (new GénérerCléAuthentificationInt())->générer_clé("jdoe", "clé 2")["clé 2"];

		$this->assertNotEquals($clé1->secret, $clé2->secret);
	}

	public function test_étant_donné_un_utilisateur_jdoe_lorsquon_génère_une_deuxième_clé_d_authentification_avec_le_même_nom_on_obtient_une_exception()
	{
		$mockCléDAO = DAOFactory::getInstance()->get_clé_dao();

		$this->expectException(DuplicatException::class);

		$clé = (new GénérerCléAuthentificationInt())->générer_clé("jdoe", "clé existante");
	}

	public function test_étant_donné_un_utilisateur_jdoe_lorsquon_génère_une_clé_d_authentification_sans_nom_on_obtient_une_exception()
	{
		$mockCléDAO = DAOFactory::getInstance()->get_clé_dao();

		$this->expectException(RessourceInvalideException::class);

		$clé = (new GénérerCléAuthentificationInt())->générer_clé("jdoe", "");
	}
}
