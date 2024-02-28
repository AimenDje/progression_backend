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

use Illuminate\Database\QueryException;
use progression\domaine\entité\Commentaire;
use progression\dao\models\{TentativeProgMdl, CommentaireMdl, UserMdl};
use progression\domaine\interacteur\IntégritéException;

class CommentaireDAO extends EntitéDAO
{
	public function get_commentaire($id, $includes = []): Commentaire|null
	{
		try {
			$commentaire = CommentaireMdl::select("commentaire.*")
				->with(in_array("créateur", $includes) ? "créateur" : [])
				->where("id", $id)
				->first();

			return self::premier_élément($this->construire([$commentaire], $includes));
		} catch (QueryException $e) {
			throw new DAOException($e);
		}
	}

	/**
	 * @return array<Commentaire>
	 */
	public function get_tous_par_tentative($username, $question_uri, $date_soumission, $includes = []): array
	{
		try {
			return $this->construire(
				CommentaireMdl::select("commentaire.*")
					->with($includes ? ["créateur"] : [])
					->join("reponse_prog", "tentative_id", "=", "reponse_prog.id")
					->join("avancement", "reponse_prog.avancement_id", "=", "avancement.id")
					->join("user", "avancement.user_id", "=", "user.id")
					->where("user.username", $username)
					->where("avancement.question_uri", $question_uri)
					->where("reponse_prog.date_soumission", $date_soumission)
					->get(),
				$includes,
			);
		} catch (QueryException $e) {
			throw new DAOException($e);
		}
	}

	/**
	 * @return array<Commentaire>
	 */
	public function save($username, $question_uri, $numéro, $commentaire): array
	{
		try {
			$tentative = TentativeProgMdl::join("avancement", "reponse_prog.avancement_id", "=", "avancement.id")
				->join("user", "avancement.user_id", "=", "user.id")
				->where("user.username", $username)
				->where("avancement.question_uri", $question_uri)
				->first();

			if (!$tentative) {
				throw new IntégritéException("Impossible de sauvegarder la ressource; le parent n'existe pas.");
			}

			$créateur = UserMdl::where("username", $commentaire->créateur->username)->first();
			if (!$créateur) {
				throw new IntégritéException("Impossible de sauvegarder la ressource; le parent n'existe pas.");
			}

			$objet = [
				"tentative_id" => $tentative->id,
				"créateur_id" => $créateur->id,
				"message" => $commentaire->message,
				"date" => $commentaire->date,
				"numéro_ligne" => $commentaire->numéro_ligne,
			];
			return $this->construire([CommentaireMdl::updateOrCreate(["id" => $numéro], $objet)]);
		} catch (QueryException $e) {
			throw new DAOException($e);
		}
	}

	/**
	 * @return array<Commentaire>
	 */
	public static function construire($data, $includes = []): array
	{
		$commentaires = [];
		foreach ($data as $item) {
			if ($item == null) {
				continue;
			}
			$id = $item["id"];
			$créateur = self::premier_élément(
				UserDAO::construire([$item["créateur"]], self::filtrer_niveaux($includes, "commentaires")),
			);
			$commentaire = new Commentaire(
				message: $item["message"],
				créateur: $créateur,
				date: $item["date"],
				numéro_ligne: $item["numéro_ligne"],
			);

			$commentaires[$id] = $commentaire;
		}

		return $commentaires;
	}
}
