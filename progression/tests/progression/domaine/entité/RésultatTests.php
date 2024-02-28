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

final class RésultatTests extends TestCase
{
	public function test_étant_donné_un_résultat_instanciée_avec_tous_ses_paramètres_lorsquon_récupère_ses_attributs_on_obtient_des_valeurs_identiques()
	{
		$résultat_obtenu = new Résultat(
			sortie_observée: "34",
			sortie_erreur: "",
			résultat: true,
			feedback: "Bon travail",
			temps_exécution: 233,
		);

		$this->assertEquals("34", $résultat_obtenu->sortie_observée);
		$this->assertTrue($résultat_obtenu->résultat);
		$this->assertEquals("Bon travail", $résultat_obtenu->feedback);
		$this->assertEquals(233, $résultat_obtenu->temps_exécution);
	}
}
