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

use mysqli_sql_exception;
use progression\domaine\entité\Commentaire;

class CommentaireDAO extends EntitéDAO
{
	public function get_commentaire($id)
	{
		$commentaire = null;

		//$id = null;
		$message = null;
		$créateur = null;
		$date = null;
		$numéro_ligne = null;

		try {
			$query = EntitéDAO::get_connexion()->prepare(
				"SELECT message, créateur, date, numéro_ligne FROM commentaire WHERE id = ?  ",
			);
			$query->bind_param("i", $id);

			$query->execute();
			$query->bind_result($message,$créateur,$date ,$numéro_ligne);

			$résultat = $query->fetch();
			$query->close();
			if ($résultat) {
				$commentaire = new Commentaire($id , $message, $créateur, $date, $numéro_ligne);
			}
		} catch (mysqli_sql_exception $e) {
			throw new DAOException($e);
		}

		return $commentaire;
	}

	public function get_toutes($username, $question_uri, $timestamp)
	{
		try {
			$query = EntitéDAO::get_connexion()->prepare(
				'SELECT id, message, créateur, date, numéro_ligne FROM commentaire WHERE username = ? AND question_uri = ? AND date_soumission = ?',);
			$query->bind_param("ssi", $username, $question_uri, $timestamp);
			$query->execute();

			$commentaires = [];
			'SELECT id, message, créateur, date, numéro_ligne FROM commentaire WHERE username = “bob” AND question_uri = “https://depot.com/roger/questions_prog/fonctions01/appeler_une_fonction” AND date_soumission = 1615696276';
			$id = null;
			$message = null;
			$créateur = null;
			$date = null;
			$numéro_ligne = null;
			$query->bind_result($id, $message, $créateur, $date, $numéro_ligne);

			while ($query->fetch()) {
				$commentaires[$id] = new Commentaire(null , $message, $créateur, $date, $numéro_ligne);
			}
			$query->close();
		} catch (mysqli_sql_exception $e) {
			throw new DAOException($e);
		}

		return $commentaires;
	}

	public function save($objet)
	{
		try {
			$query = EntitéDAO::get_connexion()->prepare(
				"INSERT INTO commentaire ( id, message, créateur ) VALUES ( ?, ?, ? )",
			);


			$query->bind_param("iss", $objet->id, $objet->message, $objet->créateur);
			$query->execute();
			$query->close();
		} catch (mysqli_sql_exception $e) {
			throw new DAOException($e);
		}

		$commentaire = $this->get_commentaire($objet->id);
	
		return $commentaire;
	}

}
