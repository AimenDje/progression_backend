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

use progression\domaine\entité\Commentaire;
use progression\domaine\entité\user\User;
use progression\dao\DAOFactory;
use PHPUnit\Framework\TestCase;
use Mockery;

final class SauvegarderCommentaireTests extends TestCase
{
	public function setUp(): void
	{
		parent::setUp();

		$mockCommentaireDAO = Mockery::mock("progression\\dao\\CommentaireDAO");

		$mockDAOFactory = Mockery::mock("progression\\dao\\DAOFactory");
		$mockDAOFactory->allows()->get_commentaire_dao()->andReturn($mockCommentaireDAO);

		DAOFactory::setInstance($mockDAOFactory);
	}

	public function test_étant_donné_un_commentaire_non_existant_lorsquon_le_sauvegarde_obtient_un_commentaire_a_partir_de_la_BD()
	{
		$commentaireAttendu = new Commentaire(
			"le 99iem message",
			new User(username: "mock", date_inscription: 0),
			1615696276,
			14,
		);
		$commentaireInt = new SauvegarderCommentaireInt();
		DAOFactory::getInstance()
			->get_commentaire_dao()
			->shouldReceive("save")
			->withArgs(function ($user, $uri, $numéro, $commentaire) {
				return $user == "jdoe" &&
					$uri == "prog1/les_fonctions_01/appeler_une_fonction_paramétrée" &&
					$numéro == 99 &&
					$commentaire ==
						new Commentaire(
							"le 99iem message",
							new User(username: "mock", date_inscription: 0),
							1615696276,
							14,
						);
			})
			->andReturn([0 => $commentaireAttendu]);

		$this->assertEquals(
			[0 => $commentaireAttendu],
			$commentaireInt->sauvegarder_commentaire(
				"jdoe",
				"prog1/les_fonctions_01/appeler_une_fonction_paramétrée",
				99,
				$commentaireAttendu,
			),
		);
	}
}
