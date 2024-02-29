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

use RuntimeException, ErrorException;
use Gitonomy\Git\Repository;


class ChargeurGIT extends Chargeur
{
	public static function cloner_depot($url_du_depot)
	{
		/*// Créer un dépôt en mémoire
		/*$dépot_en_mémoire = new Repository('/tmp', ['storage' => ['type' => 'memory']]);
        $dépot_en_mémoire = new Repository(null, ['working_dir' => '/tmp']);
        
		// Cloner le dépôt dans la mémoire
		$dépot_en_mémoire->run('clone', [$url_du_depot, '--depth=1']);

		// Vérifier si le clonage a réussi en vérifiant le statut du dépôt
       try {
            $dépot_en_mémoire->getStatus();
        } catch (\Exception $e) {
            throw new RuntimeException("Le clonage du dépôt a échoué");
        }*/

        // Définition des caractéristique du disque
        $taille_ram_disk_MB = 1;
        $mount_point_ram_disk = '/tmp'; # Patrick a dit qu'on avait droit d'écrire la 

        // Création du RAM Disk
        $ram_disk_commande_création = "sudo mount -t tmpfs -o size={$taille_ram_disk_MB}M tmpfs {$mount_point_ram_disk}";
        exec($ram_disk_commande_création, $output, $code_retour);

        // Vérification si la création a fonctionné
        if ($code_retour !== 0) {
            throw new RuntimeException("Échec de la création du disque RAM. Vérifiez les autorisations et réessayez.");
        }

        // Cloner le dépot
        $dépot_en_mémoire = new Repository($mount_point_ram_disk /*. '/repository'*/); #J'ai mis en commentaitre le . '/repository' tu peux le laisser comme ça ou pas
        $dépot_en_mémoire->run('clone', [$url_du_depot, '--depth=1']);

        // vérification si le clonage a fonctionné
        try {
            $dépot_en_mémoire->getStatus();
        } catch (\Exception $e) {
            throw new RuntimeException("Le clonage du dépôt a échoué");
        }

		return $dépot_en_mémoire;
	}
}
