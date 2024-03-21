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

use progression\TestCase;
use progression\dao\EntitéDAO;
use progression\domaine\entité\banque\Banque;
use progression\domaine\entité\banque\QuestionBanque;

final class BanqueDAOTests extends TestCase
{
	public function setUp(): void
	{
		parent::setUp();
		app("db")->connection()->beginTransaction();
	}

	public function tearDown(): void
	{
		app("db")->connection()->rollBack();
		parent::tearDown();
	}

	public function test_étant_donné_un_utilisateur_qui_nexiste_pas_lorsquon_récupère_toutes_les_banques_on_obtient_une_liste_vide()
	{
		$résultat_observé = (new BanqueDAO())->get_tous("joe");

        $résultats_attendu = [];
        
        $this->assertEquals($résultats_attendu, $résultat_observé);
	}

    public function test_étant_donné_un_utilisateur_existant_qui_na_pas_de_banque_lorsquon_récupère_toutes_les_banques_on_obtient_une_liste_vide()
    {
		$résultat_observé = (new BanqueDAO())->get_tous("jdoe");

        $résultats_attendu = [];
        
        $this->assertEquals($résultats_attendu, $résultat_observé);
	}


    /*
      public function test_étant_donné_un_utilisateur_existant_qui_a_une_banque_avec_une_erreur_dans_le_contenu_yml_lorsquon_récupère_toutes_les_banques_on_obtient_une_liste_contenant_une_banque_avec_les_questions_si_elles_sont_lisibles()
      {
        $résultat_observé = (new BanqueDAO())->get_tous("bob");


        $résultats_attendu = [];
        
        $banque = new Banque("Test banque de questions 1 - fichier yaml invalide","https://git.dti.crosemont.quebec/Julien_Giguere/progression_questions-test/-/raw/main/QuestionsYAML/contenu.yml");    
            
        $questionBanque1 = new QuestionBanque("Question1","https://git.dti.crosemont.quebec/asarkes/progression_questions-test/-/raw/main/QuestionsYAML/exemple-anayees.yml");
        $banque->ajouterQuestionsBanque($questionBanque1);

        $questionBanque2 = new QuestionBanque("erreur de lecture du fichier contenu.yml","");
        $banque->ajouterQuestionsBanque($questionBanque2);

        $questionBanque3 = new QuestionBanque("Question3","https://git.dti.crosemont.quebec/asarkes/progression_questions-test/-/raw/main/QuestionsYAML/exemple-anayees-1.yml");
        $banque->ajouterQuestionsBanque($questionBanque3);

        array_push($résultats_attendu, $banque);
       
        $this->assertEquals($résultats_attendu, $résultat_observé);
        }*/

    // public function test_étant_donné_un_utilisateur_existant_qui_a_une_banque_lorsquon_récupère_toutes_les_banques_on_obtient_une_liste_contenant_sa_banque()
    
    // public function test_étant_donné_un_utilisateur_existant_qui_a_plusieurs_banques_lorsquon_récupère_toutes_les_banques_on_obtient_une_liste_contenant_ses_banques()

    
}
