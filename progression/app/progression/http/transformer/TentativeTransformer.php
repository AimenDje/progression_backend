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

use League\Fractal;
use progression\domaine\entité\RéponseProg;

class TentativeTransformer extends Fractal\TransformerAbstract
{

    public $type = "Tentative";

    public function transform(RéponseProg|null $tentative)
    {
        if ($tentative == null) {
            $data_out = [null];
        } else {
            $data_out = [
                'id' => $tentative->date_soumission,
                'date_soumission' => $tentative->date_soumission,
                'tests_réussis' => $tentative->tests_réussis,
                'feedback' => $tentative->feedback,
                'langage' => $tentative->langid,
                'code' => $tentative->code,
            ];
        }

        return $data_out;
    }
}
