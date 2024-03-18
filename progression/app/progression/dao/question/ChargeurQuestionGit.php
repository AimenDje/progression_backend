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

class ChargeurQuestionGit extends Chargeur
{
	/**
	 * @param string $url_du_dépôt
	 * @return array<mixed>
	 */

	public function récupérer_question(string $url_du_dépôt): array
	{
		$chargeurGit = $this->source->get_chargeur_git();

		$répertoire_temporaire = $chargeurGit->cloner_dépôt($url_du_dépôt);

		$chemin_fichier_dans_dépôt = $chargeurGit->chercher_info($répertoire_temporaire);

		$chargeurFichier = $this->source->get_chargeur_question_fichier();

		$contenu_question = $chargeurFichier->récupérer_question($chemin_fichier_dans_dépôt);

		$chargeurGit->supprimer_répertoire_temporaire($répertoire_temporaire);

		return $contenu_question;
	}
}
