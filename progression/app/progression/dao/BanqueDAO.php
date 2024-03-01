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
use progression\dao\models\{BanqueMdl, UserMdl};
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

    public function ajouter(string $username, Banque $banque): array
    {
        try {
            
            $user = UserMdl::query()->where("username", $username)->first();

            if (!$user) {
                throw new IntégritéException("Impossible de sauvegarder la ressource; le parent n'existe pas.");
            }
            
            $objet = [
                "nom" => $banque->nom,
                "url" => $banque->url,
                "user_id" => $user->id,
            ];

            return $this->construire([
                BanqueMdl::updateOrCreate([
                    'url' => $banque->url,],
                                          $objet)
            ]);
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

            $user = self::premier_élément(
                UserDAO::construire([$item["user"]], self::filtrer_niveaux($includes, "banque")),
            );
            
            $banque = new Banque(
				$item["nom"],
				$item["url"],
                $user,
            );
			$banques[$item["id"]] = $banque;
            }
		return $banques;
	}
}
