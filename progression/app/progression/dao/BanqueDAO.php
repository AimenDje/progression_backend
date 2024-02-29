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

use progression\domaine\entité\banque\Banque;
use progression\dao\models\BanqueMdl;

use DB;
use Illuminate\Database\QueryException;

class BanqueDAO extends EntitéDAO
{
	/**
	 * @param array<string> $includes
     * @return array<Banque>
	 */
	public function get_banques(string $username, array $includes = []): array
	{
		try {
			return $this->construire(BanqueMdl::query()
                                     ->join("user", "banque.user_id", "=", "user.id")
                                     ->where("user.username", $username)
                                     ->get(),
                                     $includes,
            );
        } catch (QueryException $e) {
            throw new DAOException($e);
        }
    }

	/**
     * @param array<string> $includes
     * @return array<Banque>
	 */    
    public static function construire(mixed $data, $includes = []): array
	{
		$banques = [];
		foreach ($data as $item) {
			if ($item == null) {
				continue;
			}
            
            $banque = new Banque(
				id: $item["id"],
				nom: $item["nom"],
				url: $item["url"],
				user_id: $item["user_id"],
            );
			$banquess[$item["id"]] = $banque;
		}
		return $banques;
	}
}
