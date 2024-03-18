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
use RuntimeException;
use Illuminate\Support\Facades\Log;

class ChargeurGit extends Chargeur
{
	public function cloner_dépôt(string $uri): string
	{
		$dossier_memoir = "/tmp/memoire";
		$répertoire_temporaire = $dossier_memoir . "/git_repo_" . uniqid();

		if (!is_dir($dossier_memoir)) {
			mkdir($dossier_memoir, 0777, true);
			Log::debug("Création du dossier mémoire : $dossier_memoir");
		}

		Log::debug("Chemin du dépôt temporaire: " . $répertoire_temporaire);
		Log::debug("URL du dépôt git: " . $uri);

		try {
			Admin::cloneTo($répertoire_temporaire, $uri, false);
			Log::debug("Dépôt cloné avec succès à : $répertoire_temporaire");
		} catch (\Exception $e) {
			Log::error("Erreur lors du clonage du dépôt : " . $e->getMessage());
			throw new RuntimeException(
				"Le clonage du dépôt git a échoué! Ce dépôt est peut-être privé ou n'existe pas.",
			);
		}

		return $répertoire_temporaire;
	}

	public function chercher_info(string $répertoire_temporaire): string
	{
		if (file_exists($répertoire_temporaire . "/info.yml")){
			$cheminRecherche = $répertoire_temporaire . "/info.yml";
		}else {
			$cheminRecherche = $répertoire_temporaire . "/**/info.yml";
		}
		
		$fichiers = glob($cheminRecherche, GLOB_BRACE);

		if (empty($fichiers)) {
			throw new RuntimeException("Fichier info.yml inexistant dans le dépôt.");
		}

		$chemin_fichier_dans_dépôt = $fichiers[0];
		Log::debug("Fichier info.yml trouvé : " . $chemin_fichier_dans_dépôt);

		return $chemin_fichier_dans_dépôt;
	}

	public function supprimer_répertoire_temporaire(string $répertoire_temporaire): void
	{
		if (is_dir($répertoire_temporaire)) {
			system("rm -rf " . escapeshellarg($répertoire_temporaire));
			Log::debug("Répertoire temporaire supprimé : $répertoire_temporaire");
		}
	}
}
