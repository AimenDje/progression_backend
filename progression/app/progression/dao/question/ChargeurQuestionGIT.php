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


/*class ChargeurQuestionGIT extends Chargeur
{
    public static function récupérer_question($url_du_depot)
    {
        // Cloner le dépôt Git temporairement
        $dossier_temporaire = sys_get_temp_dir() . '/' . uniqid('git_repo_');
        $repository = new Repository($url_du_depot, $dossier_temporaire, false, array('depth' => 1));
        $repository->run('clone', array('--depth' => 1, $url_du_depot, $dossier_temporaire));

        // Récupérer le chemin complet du fichier info.yml dans le dépôt cloné
        $chemin_fichier_dans_depot = "$dossier_temporaire/info.yml";
        Log::debug("chemin du depot" . $chemin_fichier_dans_depot);

        // Créer une instance du chargeur de fichiers
        $chargeur_fichier = new ChargeurQuestionFichier();

        // Lire le contenu du fichier info.yml depuis le dépôt cloné en utilisant le chargeur de fichiers
        $contenu_question = $chargeur_fichier->récupérer_question($chemin_fichier_dans_depot);

        // Supprimer le répertoire temporaire du dépôt cloné
        exec("rm -rf $dossier_temporaire");

        return $contenu_question; 
    }
}*/
class ChargeurQuestionGIT extends Chargeur
{
    public static function récupérer_question($url_du_depot)
{
    // Cloner le dépôt Git temporairement
    $dossier_temporaire = sys_get_temp_dir() . '/' . uniqid('git_repo_');
    
    // Cloner le dépôt dans le dossier temporaire
    exec("git clone --depth 1 $url_du_depot $dossier_temporaire");
    $résultat_clone = exec("git clone --depth 1 $url_du_depot $dossier_temporaire");
    // Vérifier si le clonage a réussi
    
    if (!is_dir($dossier_temporaire)) {
        throw new \RuntimeException("Clonage échoué : il est possible que votre dépôt est privé");
    }

    $chemin_fichier_dans_depot = "$dossier_temporaire/info.yml";
    if (!file_exists($chemin_fichier_dans_depot)) {
        throw new \RuntimeException("Clonage échoué : fichier info.yml inexistant");
    }
    

    // Récupérer le chemin complet du fichier info.yml dans le dépôt cloné
    $chemin_fichier_dans_depot = "$dossier_temporaire/info.yml";
    Log::debug("chemin du depot" . $chemin_fichier_dans_depot);

    // Créer une instance du chargeur de fichiers
    $chargeur_fichier = new ChargeurQuestionFichier();

    // Lire le contenu du fichier info.yml depuis le dépôt cloné en utilisant le chargeur de fichiers
    $contenu_question = $chargeur_fichier->récupérer_question($chemin_fichier_dans_depot);

    // Supprimer le répertoire temporaire du dépôt cloné
    exec("rm -rf $dossier_temporaire");

    return $contenu_question; 
}

}



