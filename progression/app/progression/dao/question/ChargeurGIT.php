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

 use Illuminate\Support\Facades\Log;

 use RuntimeException;
 
 class ChargeurGIT extends Chargeur
 {
     public static function cloner_depot($url_du_depot)
     {
        $dossier_memoir='/tmp/memoire';
        $dossier_memoir_absolue=realpath($dossier_memoir);
        if (is_dir($dossier_memoir)) {
            Log::debug("Le dossier $dossier_memoir_absolue existe.");
        } else {
            Log::debug("Le $dossier_memoir_absolue n'existe pas.");
        }

         // Définir les chemins pour les volumes tmpfs
         $dossier_temporaire = $dossier_memoir . '/git_repo_' . uniqid();
         Log::debug("chemin du depot temporaire: " . $dossier_temporaire);
         Log::debug("URL du dépot git: " . $url_du_depot);
 
         // Cloner le dépôt dans les volumes tmpfs
         exec("git clone --depth 1 $url_du_depot $dossier_temporaire 2>&1", $output, $returnCode);
         Log::debug("Output du clonage depot git: " . implode(PHP_EOL, $output));
         Log::debug("Code retour du clonage depot git: " . $returnCode);
 
         // Vérifier si le clonage a réussi
         if ($returnCode !== 0) {
             throw new RuntimeException("Le clonage du dépôt a échoué");
         }
 
         return $dossier_temporaire;
     }
 }
