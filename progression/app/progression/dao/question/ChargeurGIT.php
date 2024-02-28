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

        // Define RAM disk configuration
        $ramDiskSizeMB = 1;
        $ramDiskMountPoint = '/tmp';

        // Create RAM disk using the appropriate system command
        $createRamDiskCommand = "sudo mount -t tmpfs -o size={$ramDiskSizeMB}M tmpfs {$ramDiskMountPoint}";
        exec($createRamDiskCommand, $output, $returnCode);

        // Check if the RAM disk creation was successful
        if ($returnCode !== 0) {
            throw new RuntimeException("Failed to create RAM disk. Check permissions and try again.");
        }

        // Cloning the repository into the RAM disk
    $dépot_en_mémoire = new Repository($ramDiskMountPoint /*. '/repository'*/);
        $dépot_en_mémoire->run('clone', [$url_du_depot, '--depth=1']);

        // Check if the clone operation was successful
        try {
            $dépot_en_mémoire->getStatus();
        } catch (\Exception $e) {
            throw new RuntimeException("Le clonage du dépôt a échoué");
        }

		return $dépot_en_mémoire;
	}
}
