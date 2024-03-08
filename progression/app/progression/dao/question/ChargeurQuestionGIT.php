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
use progression\domaine\entité\question\QuestionProg;

class ChargeurQuestionGIT extends Chargeur
{
	public function récupérer_question(string $url_du_depot): QuestionProg
	{
		// Obtenir l'instance de ChargeurFactory

		// Obtenir le chargeur GIT de ChargeurFactory
		$chargeurGIT = $this->source->get_chargeur_git();

		// Cloner le dépôt Git temporairement
		$dossier_temporaire = $chargeurGIT->cloner_depot($url_du_depot);

		// Récupérer le chemin complet du fichier info.yml dans le dépôt cloné
		$chemin_fichier_dans_depot = $chargeurGIT->chercher_info($dossier_temporaire);

		// Utiliser ChargeurFactory pour obtenir le chargeur de fichiers
		$chargeurFichier = $this->source->get_chargeur_question_fichier();

		// Lire le contenu du fichier info.yml depuis le dépôt cloné en utilisant le chargeur de fichiers
		$contenu_question = $chargeurFichier->récupérer_question($chemin_fichier_dans_depot);

		// Supprimer le répertoire temporaire du dépôt cloné
		$chargeurGIT->supprimer_dossier_temporaire($dossier_temporaire);

		return $contenu_question;
	}
}
