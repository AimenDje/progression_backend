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

use progression\dao\EntitéDAO;
use progression\dao\banque\ChargeurBanqueFichier;
use progression\dao\models\{BanqueMdl, UserMdl};
use progression\dao\chargeur\{ChargeurFactory, ChargeurArchive};
use progression\domaine\entité\banque\Banque;
use progression\domaine\interacteur\IntégritéException;

use Illuminate\Database\QueryException;

class BanqueDAO extends EntitéDAO
{
	public function __construct(DAOFactory $source = null)
	{
		parent::__construct($source);

		ChargeurFactory::get_instance()->set_chargeur_fichier(new ChargeurBanqueFichier());
		ChargeurFactory::get_instance()->set_chargeur_archive(new ChargeurArchive());
	}

	/**
	 * @param array<string> $includes
	 * @return array<Banque>
	 */
	public function get_tous(string $username, array $includes = []): array
	{
		try {
			return $this->construire(
				BanqueMdl::select("banque.*")
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
	 * @return array<Banque>
	 */
	public function save(string $username, Banque $banque): array
	{
		try {
			$user = UserMdl::query()->where("username", $username)->first();

			if (!$user) {
				throw new IntégritéException("Impossible de sauvegarder la ressource; le parent n'existe pas.");
			}

			$objet = [
				"nom" => $banque->nom,
				"url" => $banque->url,
			];

			return $this->construire([
				BanqueMdl::updateOrCreate(
					[
						"user_id" => $user->id,
						"url" => $banque->url,
					],
					$objet,
				),
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

			$banque = new Banque($item["nom"], $item["url"]);

			if (in_array("questions", $includes)) {
				$contenu = ChargeurFactory::get_instance()
					->get_chargeur()
					->récupérer_fichier($banque->url);

				foreach ($contenu as $question) {
					$questionBanque = DAOFactory::getInstance()
						->get_question_dao()
						->get_question($question["url"]);
					$banque->ajouterQuestionsBanque($questionBanque);
				}
			}
			$banques[$item["id"]] = $banque;
		}

		return $banques;
	}
}
