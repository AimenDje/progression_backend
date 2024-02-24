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
use progression\TestCase;
use Mockery;

final class ObtenirCommentaireIntTests extends TestCase
{
	public function setUp(): void
	{
		parent::setUp();

		$mockCommentaireDAO = Mockery::mock("progression\\dao\\CommentaireDAO");

		$tableauCommentaires = [
			new Commentaire("le 1er message", new User(username: "jdoe", date_inscription: 0), 1615696276, 14),
			new Commentaire("le 2er message", new User(username: "admin", date_inscription: 0), 1615696276, 12),
			new Commentaire("le 3er message", new User(username: "Stefany", date_inscription: 0), 1615696276, 14),
		];

		$mockCommentaireDAO
			->shouldReceive("get_commentaire")
			->with(3, [])
			->andReturn(
				new Commentaire("le 3er message", new User(username: "oteur", date_inscription: 0), 1615696276, 14),
			);
		$mockCommentaireDAO
			->shouldReceive("get_commentaire")
			->with(3, ["créateur"])
			->andReturn(
				new Commentaire("le 3er message", new User(username: "Stefany", date_inscription: 0), 1615696276, 14),
			);
		$mockCommentaireDAO
			->shouldReceive("get_tous_par_tentative")
			->with("bob", "https://depot.com/roger/questions_prog/fonctions01/appeler_une_fonction", 1615696276, [
				"créateur",
			])
			->andReturn($tableauCommentaires);
		$mockCommentaireDAO->shouldReceive("get_commentaire")->andReturn(null);

		$mockDAOFactory = Mockery::mock("progression\\dao\\DAOFactory");
		$mockDAOFactory->allows()->get_commentaire_dao()->andReturn($mockCommentaireDAO);
		DAOFactory::setInstance($mockDAOFactory);
	}

	public function test_étant_donné_un_commentaire_existant_lorsquon_le_recherche_par_id_sans_inclusion_on_obtient_un_objet_commentaire()
	{
		$résultat_observé = new ObtenirCommentaireInt();

		$résultat_attendu = new Commentaire(
			"le 3er message",
			new User(username: "oteur", date_inscription: 0),
			1615696276,
			14,
		);

		$this->assertEquals($résultat_attendu, $résultat_observé->get_commentaire_par_id(3));
	}

	public function test_étant_donné_un_commentaire_existant_lorsquon_le_recherche_par_id_en_incluant_le_créateur_on_obtient_un_objet_commentaire_et_son_créateur()
	{
		$résultat_observé = new ObtenirCommentaireInt();

		$résultat_attendu = new Commentaire(
			"le 3er message",
			new User(username: "Stefany", date_inscription: 0),
			1615696276,
			14,
		);

		$this->assertEquals($résultat_attendu, $résultat_observé->get_commentaire_par_id(3, ["créateur"]));
	}

	public function test_étant_donné_un_id_inexistant_lorsquon_cherche_son_commentaire_on_obtient_null()
	{
		$commentaireAttendu = new ObtenirCommentaireInt();

		$this->assertNull($commentaireAttendu->get_commentaire_par_id(9999999));
	}

	public function test_étant_donné_des_commentaires_existants_lorsquon_les_recherche_par_tentative_en_incluant_les_créateurs_on_obtient_une_liste_de_commentaires_correspondante()
	{
		$résultat_observé = new ObtenirCommentaireInt();

		$tableauCommentaire = [
			new Commentaire("le 1er message", new User(username: "jdoe", date_inscription: 0), 1615696276, 14),
			new Commentaire("le 2er message", new User(username: "admin", date_inscription: 0), 1615696276, 12),
			new Commentaire("le 3er message", new User(username: "Stefany", date_inscription: 0), 1615696276, 14),
		];
		$réponse_attendue = $tableauCommentaire;
		$this->assertEquals(
			$réponse_attendue,
			$résultat_observé->get_tous_par_tentative(
				"bob",
				"https://depot.com/roger/questions_prog/fonctions01/appeler_une_fonction",
				1615696276,
				["créateur"],
			),
		);
	}
}
