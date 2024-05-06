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
namespace progression\dao\question;

use Gitonomy\Git\Admin;
use Illuminate\Support\Facades\File;
use RuntimeException;
use progression\dao\chargeur\ChargeurException;
use Spatie\TemporaryDirectory\TemporaryDirectory;

class ChargeurQuestionGit extends ChargeurQuestion
{
	/**
	 * @param string $uri
	 * @return array<mixed>
	 */
	public function récupérer_fichier(string $uri): array
	{
		$répertoire_temporaire = (new TemporaryDirectory(getenv("TEMPDIR")))->deleteWhenDestroyed()->create();
		$chemin_fichier_dans_dépôt = $this->cloner_dépôt($répertoire_temporaire->path(), $uri);
		$chargeurFichier = $this->source->get_chargeur_question_fichier();

		$contenu_question = $chargeurFichier->récupérer_fichier($chemin_fichier_dans_dépôt);
		return $contenu_question;
	}

	/**
	 * @param string $uri
	 * @param int|string $cle
	 * @return bool
	 */
	public function est_modifié(string $uri, $cle): bool
	{
		return true;
	}

	/**
	 * @param string $url_du_dépôt
	 * @return string
	 */
	private function cloner_dépôt(string $destination, string $url_du_dépôt): string
	{
		try {
			Admin::cloneTo($destination, $url_du_dépôt, false);
		} catch (RuntimeException $e) {
			throw new ChargeurException(
				"Le clonage du dépôt git a échoué! Ce dépôt est peut-être privé ou n'existe pas.",
			);
		}

		$chemin_fichier_info = $destination . "/info.yml";

		if (File::exists($chemin_fichier_info)) {
			return $chemin_fichier_info;
		} else {
			throw new ChargeurException("Fichier info.yml inexistant dans le dépôt.");
		}
	}
}
