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

namespace progression\domaine\entité\banque;

use progression\TestCase;
use \InvalidArgumentException;

final class BanqueTests extends TestCase
{
	public function test_étant_donné_une_Banque_instanciée_avec_tous_ses_paramètres_lorsquon_récupère_ses_attributs_on_obtient_des_valeurs_identiques() {
		$id_attendu = 1;
		$nom_attendu = "Test 1";
		$url_attendu = "https://progression.pages.dti.crosemont.quebec/contenu/prog_1/liste_questions.html";
		$user_id_attendu = 1;
		
		$résultat_obtenu = new Banque(
			id: 1,
			nom: "Test 1",
			url: "https://progression.pages.dti.crosemont.quebec/contenu/prog_1/liste_questions.html",
			user_id: 1,
		);
		
		$this->assertEquals($id_attendu, $résultat_obtenu->id);
		$this->assertEquals($nom_attendu, $résultat_obtenu->nom);
		$this->assertEquals($url_attendu, $résultat_obtenu->url);
		$this->assertEquals($user_id_attendu, $résultat_obtenu->user_id);
	}
}
