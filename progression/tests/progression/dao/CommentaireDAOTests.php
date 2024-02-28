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

use progression\domaine\entité\Commentaire;
use progression\domaine\entité\user\{User, Rôle};
use progression\TestCase;
use progression\domaine\interacteur\IntégritéException;

final class CommentaireDAOTests extends TestCase
{
	public $jdoe;
	public $admin;
	public $stefany;

	public function setUp(): void
	{
		parent::setUp();

		$this->jdoe = new User(username: "jdoe", date_inscription: 1600828609);
		$this->admin = new User(username: "admin", date_inscription: 1580828611, rôle: Rôle::ADMIN);
		$this->stefany = new User(username: "Stefany", date_inscription: 1610828610);

		app("db")->connection()->beginTransaction();
	}

	public function tearDown(): void
	{
		app("db")->connection()->rollBack();
		parent::tearDown();
	}

	public function test_étant_donné_un_commentaire_lorsquon_le_cherche_par_son_numero_sans_inclusion_on_obtient_le_commentaire()
	{
		$réponse_attendue = new Commentaire("le 1er message", $this->jdoe, 1615696277, 14);
		$réponse_observée = (new CommentaireDAO())->get_commentaire(id: 1);

		$this->assertEquals($réponse_attendue, $réponse_observée);
	}

	public function test_étant_donné_un_commentaire_lorsquon_le_cherche_par_son_numero_en_incluant_le_créateur_on_obtient_le_commentaire_et_son_créateur()
	{
		$réponse_attendue = new Commentaire("le 1er message", $this->jdoe, 1615696277, 14);
		$réponse_observée = (new CommentaireDAO())->get_commentaire(id: 1, includes: ["créateur"]);

		$this->assertEquals($réponse_attendue, $réponse_observée);
	}

	public function test_étant_donné_un_commentaire_inexistant_lorsquon_le_cherche_par_son_numero_on_obtient_null()
	{
		$réponse_observée = (new CommentaireDAO())->get_commentaire(-1);
		$this->assertNull($réponse_observée);
	}

	public function test_étant_donné_tous_les_commentaire_dune_tentative_lorsquon_les_cherchent_par_tentative_existante_en_incluant_le_créateur_on_obitent_tous_les_commentaires_de_la_tentative_et_leur_créateur()
	{
		$commentaires = [];
		$commentaires[1] = new Commentaire("le 1er message", $this->jdoe, 1615696277, 14);
		$commentaires[2] = new Commentaire("le 2er message", $this->admin, 1615696278, 12);
		$commentaires[3] = new Commentaire("le 3er message", $this->stefany, 1615696279, 14);
		$réponse_attendue = $commentaires;

		$réponse_observée = (new CommentaireDAO())->get_tous_par_tentative(
			username: "bob",
			question_uri: "https://depot.com/roger/questions_prog/fonctions01/appeler_une_fonction",
			date_soumission: 1615696276,
			includes: ["créateur"],
		);
		$this->assertEquals($réponse_attendue, $réponse_observée);
	}

	public function test_étant_donné_tous_les_commentaire_dune_tentative_lorsquon_les_cherchent_par_tentative_non_existante_on_obtient_tableau_vide()
	{
		$réponse_attendue = [];
		$réponse_observée = (new CommentaireDAO())->get_tous_par_tentative(
			"bobby",
			"https://depot.com/roger/questions_prog/fonctions05/appeler_une_fonction",
			1615696276,
		);
		$this->assertEquals($réponse_attendue, $réponse_observée);
	}

	public function test_étant_donné_un_commentaire_inexistant_lorsquon_le_sauvegarde_il_est_créé_dans_la_bd_et_on_obtient_le_commentaire()
	{
		$réponse_attendue = new Commentaire("le 4ième message", $this->jdoe, 1615696276, 11);

		$dao = new CommentaireDAO();
		$réponse_observée = $dao->save(
			"bob",
			"https://depot.com/roger/questions_prog/fonctions01/appeler_une_fonction",
			4,
			new Commentaire("le 4ième message", $this->jdoe, 1615696276, 11),
		);

		//Vérifie le Commentaire retourné
		$this->assertEquals([4 => $réponse_attendue], $réponse_observée);

		//Vérifie le Commentaire stoqué dans la BD
		$réponse_observée = (new CommentaireDAO())->get_commentaire(4);
		$this->assertEquals($réponse_attendue, $réponse_observée);
	}

	public function test_étant_donné_un_commentaire_existant_lorsquon_le_sauvegarde_on_modifie_le_commentaire_dans_la_bd()
	{
		$réponse_attendue = new Commentaire("le 1er message modifie", $this->jdoe, 1615696255, 17);

		$dao = new CommentaireDAO();
		$réponse_observée = $dao->save(
			"bob",
			"https://depot.com/roger/questions_prog/fonctions01/appeler_une_fonction",
			1,
			new Commentaire("le 1er message modifie", $this->jdoe, 1615696255, 17),
		);

		//Vérifie le Commentaire retourné
		$this->assertEquals([1 => $réponse_attendue], $réponse_observée);

		//Vérifie le Commentaire stoqué dans la BD
		$réponse_observée = (new CommentaireDAO())->get_commentaire(1);
		$this->assertEquals($réponse_attendue, $réponse_observée);
	}

	public function test_étant_donné_un_commentaire_inexistant_par_un_créateur_inexistant_lorsquon_le_sauvegarde_on_obtient_une_exception()
	{
		$this->personne = new User(username: "personne", date_inscription: 0);

		$dao = new CommentaireDAO();

		$this->expectException(IntégritéException::class);
		$dao->save(
			"bob",
			"https://depot.com/roger/questions_prog/fonctions01/appeler_une_fonction",
			1,
			new Commentaire("le 1er message modifie", $this->personne, 1615696255, 17),
		);
	}
}
