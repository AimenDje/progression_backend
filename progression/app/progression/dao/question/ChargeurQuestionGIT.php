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

use Gitonomy\Git\Repository;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class ChargeurQuestionGIT extends Chargeur
{
	public static function récupérer_question($url_du_depot)
	{
		// Créer une instance du chargeur de depot git
		$chargeur_depot = new ChargeurGIT();

		// Cloner le dépôt Git temporairement
		$dossier_temporaire = $chargeur_depot->cloner_depot($url_du_depot);

		// Récupérer le chemin complet du fichier info.yml dans le dépôt cloné
		$liste_info_yml = null;
		$code_de_retour = null;
		exec("find $dossier_temporaire -name 'info.yml'", $liste_info_yml, $code_de_retour);
		Log::debug($code_de_retour);
		if (!$liste_info_yml) {
			throw new RunTimeException("Fichier info.yml inexistant");
			
		}
		$chemin_fichier_dans_depot = $liste_info_yml[0];
		Log::debug("chemin du depot" . $chemin_fichier_dans_depot);
        
		// Créer une instance du char geur de fichiers
		$chargeur_fichier = new ChargeurQuestionFichier();

		// Lire le contenu du fichier info.yml depuis le dépôt cloné en utilisant le chargeur de fichiers
		$contenu_question = $chargeur_fichier->récupérer_question($chemin_fichier_dans_depot);

		// Supprimer le répertoire temporaire du dépôt cloné
		exec("rm -rf $dossier_temporaire");

		return $contenu_question;
	}
}
