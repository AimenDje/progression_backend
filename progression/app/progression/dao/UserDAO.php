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

use progression\domaine\entité\user\User;
use progression\dao\models\UserMdl;

use DB;
use Illuminate\Database\QueryException;

class UserDAO extends EntitéDAO
{
	/**
	 * @param array<string> $includes
	 */
	public function get_user(string $username, array $includes = []): User|null
	{
		try {
			$user = UserMdl::query()
				->where("username", $username)
				->with(in_array("avancements", $includes) ? "avancements" : [])
				->with(in_array("clés", $includes) ? "clés" : [])
				->first();
			return self::premier_élément($this->construire([$user], $includes));
		} catch (QueryException $e) {
			throw new DAOException($e);
		}
	}

	/**
	 * @param array<string> $includes
	 */
	public function trouver(string $username = null, string $courriel = null, array $includes = []): User|null
	{
		if (!$username && !$courriel) {
			return null;
		}

		try {
			$user = UserMdl::query()
				->where($username !== null ? "username" : [], $username)
				->where($courriel !== null ? "courriel" : [], $courriel)
				->with(in_array("avancements", $includes) ? "avancements" : [])
				->with(in_array("clés", $includes) ? "clés" : [])
				->first();
			return $user ? $this->construire([$user], $includes)[$user["username"]] : null;
		} catch (QueryException $e) {
			throw new DAOException($e);
		}
	}

	/**
	 * @return non-empty-array<string,User>
	 */
	public function save(string $username, User $user): array
	{
		try {
			$objet = [
				"username" => $user->username,
				"courriel" => $user->courriel,
				"état" => $user->état,
				"rôle" => $user->rôle,
				"preferences" => $user->préférences,
				"date_inscription" => $user->date_inscription,
			];
			return $this->construire([UserMdl::query()->updateOrCreate(["username" => $username], $objet)]);
		} catch (QueryException $e) {
			throw new DAOException($e);
		}
	}

	public function set_password(User $user, string $password)
	{
		try {
			$hash = password_hash($password, PASSWORD_DEFAULT);
			return DB::update("UPDATE user SET password=? WHERE username=?", [$hash, $user->username]);
		} catch (QueryException $e) {
			throw new DAOException($e);
		}
	}

	public function vérifier_password(User $user, string $password)
	{
		try {
			$hash = DB::select("SELECT password FROM user WHERE username=?", [$user->username]);
			return count($hash) == 1 && password_verify($password, $hash[0]->password);
		} catch (QueryException $e) {
			throw new DAOException($e);
		}
	}

	public static function construire($data, $includes = [])
	{
		$users = [];
		foreach ($data as $item) {
			if ($item == null) {
				continue;
			}
			$users[$item->username] = new User(
				username: $item["username"],
				date_inscription: $item["date_inscription"],
				courriel: $item["courriel"],
				état: $item["état"],
				rôle: $item["rôle"],
				avancements: in_array("avancements", $includes)
					? AvancementDAO::construire($item["avancements"], parent::filtrer_niveaux($includes, "avancements"))
					: [],
				clés: in_array("clés", $includes)
					? CléDAO::construire($item["clés"], parent::filtrer_niveaux($includes, "clés"))
					: [],
				préférences: $item["preferences"] ?? "",
			);
		}
		return $users;
	}
}
