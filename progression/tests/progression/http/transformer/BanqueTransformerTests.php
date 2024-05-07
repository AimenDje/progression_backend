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

use progression\domaine\entité\banque\Banque;
use progression\http\transformer\dto\BanqueDTO;
use progression\http\transformer\dto\GénériqueDTO;
use progression\TestCase;

final class BanqueTransformerTests extends TestCase
{
	/*  public function test_étant_donné_une_banque_instanciée_lorsquon_récupère_son_transformer_on_obtient_un_array_identique()
    {
      $banque = new Banque();

      $banqueTransformer = new $banqueTransformer("jdoe");
      $résultats_obtenus = $banqueTransformer->transform(
          new BanqueDTO(
                id: "jdoe/aHR0cHM6Ly9kZXBvdC5jb20vcm9nZXIvcXVlc3Rpb25zX3Byb2cvZm9uY3Rpb25zMDEvYXBwZWxlcl91bmVfZm9uY3Rpb24",
                objet: $banque,
                liens: [],
          ),
      );

      $this->assertJsonStringEqualsJsonFile(
          __DIR__ . "/résultats_attendus/banqueTransformerTest.json",
          json_encode($résultats_obtenus),
      );
      }*/
}