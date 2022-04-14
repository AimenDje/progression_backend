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

namespace progression\dao;

use progression\domaine\entité\{Commentaire};
use PHPUnit\Framework\TestCase;

final class CommentaireDAOTests extends TestCase
{
	public function setUp(): void
	{
		EntitéDAO::get_connexion()->begin_transaction();
	}

	public function tearDown(): void
	{
		EntitéDAO::get_connexion()->rollback();
	}

	public function test_étant_donné_un_commentaire_lorsquon_le_cherche_par_son_numero_on_obtient_le_commentaire()
	{
		$commentaire[1] = new Commentaire("le 1er message", "jdoe", 1615696276, 14);
		$réponse_attendue = $commentaire;
		$réponse_observée = (new CommentaireDAO())->get_commentaire(1);

		$this->assertEquals($réponse_attendue, $réponse_observée);
	}

	public function test_étant_donné_un_commentaire_inexistant_lorsquon_le_cherche_par_son_numero_on_obtient_null()
	{
		$réponse_observée = (new CommentaireDAO())->get_commentaire(-1);
		$this->assertNull($réponse_observée);
	}

	public function test_étant_donné_tous_les_commentaire_dune_tentative_lorsquon_les_cherchent_par_tentative_existante_on_obitent_tous_les_commentaires_de_la_tentative()
	{
		$tableauCommentaire[1] = new Commentaire("le 1er message", "jdoe", 1615696276, 14);
		$tableauCommentaire[2] = new Commentaire("le 2er message", "admin", 1615696276, 12);
		$tableauCommentaire[3] = new Commentaire("le 3er message", "Stefany", 1615696276, 14);
		$réponse_attendue = $tableauCommentaire;

		$réponse_observée = (new CommentaireDAO())->get_commentaires_par_tentative(
			"bob",
			"https://depot.com/roger/questions_prog/fonctions01/appeler_une_fonction",
			1615696276,
		);
		$this->assertEquals($réponse_attendue, $réponse_observée);
	}

	public function test_étant_donné_tous_les_commentaire_dune_tentative_lorsquon_les_cherchent_par_tentative_non_existante_on_obtient_tableau_vide()
	{
		$réponse_attendue = [];
		$réponse_observée = (new CommentaireDAO())->get_commentaires_par_tentative(
			"bobby",
			"https://depot.com/roger/questions_prog/fonctions05/appeler_une_fonction",
			1615696276,
		);
		$this->assertEquals($réponse_attendue, $réponse_observée);
	}

	public function test_étant_donné_un_commentaire_inexistant_lorsquon_le_sauvegarde_il_est_créé_dans_la_bd_et_on_obtient_le_commentaire()
	{
		$commentaire = new Commentaire("le 4ième message", "jdoe", 1615696276, 11);
		$tableauCommentaire[4] = new Commentaire("le 4ième message", "jdoe", 1615696276, 11);
		$réponse_attendue = $tableauCommentaire;

		$dao = new CommentaireDAO();
		$réponse_observée = $dao->save(
			"bob",
			"https://depot.com/roger/questions_prog/fonctions01/appeler_une_fonction",
			1615696276,
			null,
			$commentaire,
		);

		//Vérifie le Commentaire retourné
		$this->assertEquals($réponse_attendue, $réponse_observée);

		//Vérifie le Commentaire stoqué dans la BD
		$réponse_observée = (new CommentaireDAO())->get_commentaire(4);
		$this->assertEquals($réponse_attendue, $réponse_observée);
	}

	public function test_étant_donné_un_commentaire_existant_lorsquon_le_sauvegarde_on_modifie_le_commentaire_dans_la_bd()
	{
		$commentaire = new Commentaire("le 1er message modifie", "jdoe", 1615696255, 17);
		$tableauCommentaire[1] = new Commentaire("le 1er message modifie", "jdoe", 1615696255, 17);
		$réponse_attendue = $tableauCommentaire;

		$dao = new CommentaireDAO();
		$réponse_observée = $dao->save(
			"bob",
			"https://depot.com/roger/questions_prog/fonctions01/appeler_une_fonction",
			1615696276,
			1,
			$commentaire,
		);

		//Vérifie le Commentaire retourné
		$this->assertEquals($réponse_attendue, $réponse_observée);

		//Vérifie le Commentaire stoqué dans la BD
		$réponse_observée = (new CommentaireDAO())->get_commentaire(1);
		$this->assertEquals($réponse_attendue, $réponse_observée);
	}
}