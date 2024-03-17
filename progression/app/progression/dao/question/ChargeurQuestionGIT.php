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

use progression\domaine\entité\question\Question;

class ChargeurQuestionGIT extends Chargeur
{
	/**
	 * @param string $url_du_dépôt
	 * @return array<mixed>
	 */

	public function récupérer_question(string $url_du_dépôt): array
	{
		$chargeurGIT = $this->source->get_chargeur_git();

		$dossier_temporaire = $chargeurGIT->cloner_dépôt($url_du_dépôt);

		$chemin_fichier_dans_dépôt = $chargeurGIT->chercher_info($dossier_temporaire);

		$chargeurFichier = $this->source->get_chargeur_question_fichier();

		$contenu_question = $chargeurFichier->récupérer_question($chemin_fichier_dans_dépôt);

		$chargeurGIT->supprimer_dossier_temporaire($dossier_temporaire);

		return $contenu_question;
	}
}
