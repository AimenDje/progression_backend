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

namespace progression\domaine\entité;

use progression\TestCase;
use progression\domaine\entité\question\État;

final class AvancementTests extends TestCase
{
	public function test_étant_donné_un_Avancement_instancié_avec_tous_ses_paramètres_lorsquon_récupère_ses_attributs_on_obtient_des_valeurs_identiques()
	{
		$résultat_obtenu = new Avancement([new TentativeProg("python", "test", 654321)], "Titre", "niveau", [
			"python" => new Sauvegarde("python", "test"),
		]);

		$this->assertEquals(État::NONREUSSI, $résultat_obtenu->état);
		$this->assertEquals("Titre", $résultat_obtenu->titre);
		$this->assertEquals("niveau", $résultat_obtenu->niveau);
		$this->assertEquals(654321, $résultat_obtenu->date_modification);
		$this->assertNull($résultat_obtenu->date_réussite);
		$this->assertEquals([654321 => new TentativeProg("python", "test", 654321)], $résultat_obtenu->tentatives);
		$this->assertEquals(["python" => new Sauvegarde("python", "test")], $résultat_obtenu->sauvegardes);
	}

	public function test_étant_donné_une_avancement_sans_tentatives_lorsquon_récupère_lavancement_on_obtient_les_valeurs_par_défaut()
	{
		$résultat_obtenu = new Avancement([], "Titre", "niveau", []);

		$this->assertEquals(État::DEBUT, $résultat_obtenu->état);
		$this->assertNull($résultat_obtenu->date_modification);
		$this->assertNull($résultat_obtenu->date_réussite);
	}

	public function test_étant_donné_une_avancement_avec_plusieurs_tentatives_lorsquon_récupère_la_date_de_modification_on_obtient_la_date_de_la_tentative_la_plus_récente()
	{
		$résultat_obtenu = new Avancement(
			[
				new TentativeProg("python", "test", 654323, true),
				new TentativeProg("python", "test", 654312, false),
				new TentativeProg("python", "test", 654321, true),
			],
			"Titre",
			"niveau",
			[],
		);

		$this->assertEquals(État::REUSSI, $résultat_obtenu->état);
		$this->assertEquals(654323, $résultat_obtenu->date_modification);
	}

	public function test_étant_donné_une_avancement_réussi_avec_plusieurs_tentatives_lorsquon_récupère_la_date_de_modification_on_obtient_la_date_de_la_première_tentative_réussie()
	{
		$résultat_obtenu = new Avancement(
			[
				new TentativeProg("python", "test", 654323, true),
				new TentativeProg("python", "test", 654312, false),
				new TentativeProg("python", "test", 654321, true),
			],
			"Titre",
			"niveau",
			[],
		);

		$this->assertEquals(État::REUSSI, $résultat_obtenu->état);
		$this->assertEquals(654321, $résultat_obtenu->date_réussite);
	}

	public function test_étant_donné_un_AvancementProg_instancié_sans_tentatives_lorsquon_récupère_ses_tentatives_on_obtient_un_tableau_vide()
	{
		$résultat_attendu = [];
		$résultat_obtenu = (new Avancement())->tentatives;

		$this->assertEquals($résultat_attendu, $résultat_obtenu);
	}

	public function test_étant_donné_un_AvancementProg_instancié_sans_sauvegardes_lorsquon_récupère_ses_sauvegardes_on_obtient_un_tableau_vide()
	{
		$résultat_attendu = [];
		$résultat_obtenu = (new Avancement())->sauvegardes;

		$this->assertEquals($résultat_attendu, $résultat_obtenu);
	}
}
