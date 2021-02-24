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

namespace progression\http\transformer;

use progression\domaine\entité\{AvancementProg, QuestionProg};
use PHPUnit\Framework\TestCase;

final class AvancementProgTransformerTests extends TestCase
{
    public function test_étant_donné_un_avancement_instancié_avec_des_valeurs_lorsquon_récupère_son_transformer_on_obtient_un_array_d_objets_identique()
    {
        $user_id = 1;
        $question_id = 1;
        $_ENV['APP_URL'] = "https://example.com/";

        $avancementProgTransformer = new AvancementProgTransformer();
        $avancement = new AvancementProg($question_id, $user_id);
        $question = new QuestionProg($question_id);

        $résultat = [
            "id" => $user_id . "/" . $question_id,
            "user_id" => $user_id,
            "état" => 0,
            "links" => [
                "self" => "https://example.com/avancement/" . $avancement->user_id . "/" . $question_id
            ]
        ];

        $this->assertEquals($résultat, $avancementProgTransformer->transform(["avancement" => $avancement, "question" => $question]));
    }

    public function test_étant_donné_un_avancement_null_lorsquon_récupère_son_transformer_on_obtient_un_array_null()
    {
        $avancementProgTransformer = new AvancementProgTransformer();
        $avancement = null;

        $résultat_attendu = [null];
        $résultat_obtenu = $avancementProgTransformer->transform($avancement);

        $this->assertEquals($résultat_attendu, $résultat_obtenu);
    }
}
