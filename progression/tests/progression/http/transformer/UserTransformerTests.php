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
use progression\domaine\entité\User;
use PHPUnit\Framework\TestCase;
use League\Fractal\Manager;
use League\Fractal\Serializer\JsonApiSerializer;
use League\Fractal\Resource\Item;

final class UserTransformerTests extends TestCase{
    public function test_étant_donné_un_user_instancié_avec_id_2_et_nom_bob_lorsquon_récupère_son_transformer_on_obtient_un_objet_json_correspondant(){
        $userTransformer = new UserTransformer();
        
        $user = new User(2);
        $user->username = "bob";
        $json = '{"data":{"type":"User","id":"2","attributes":{"username":"bob","rôle":0},"links":{"self":"https:\/\/progression.dti.crosemont.quebec\/User\/2","0":{"rel":"self","uri":"\/user\/bob"}}}}';
        
        $manager = new Manager();
        $manager->setSerializer(new JsonApiSerializer("https://progression.dti.crosemont.quebec"));
        
        $item = new Item($user, $userTransformer, $userTransformer->type);
        
        $this->assertEquals($json, json_encode($manager->createData($item)->toArray(),JSON_UNESCAPED_UNICODE));
    }
}

    $json = '[null]';
    $this->assertEquals($json, json_encode($userTransformer->transform($user)));
  }
}
