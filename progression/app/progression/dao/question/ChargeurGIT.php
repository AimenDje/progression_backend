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

use RuntimeException, ErrorException, Exception;
use RessourceInvalideException;

use Illuminate\Support\Facades\Log;

class ChargeurGIT extends Chargeur
{
	public function cloner_depot(string $url_du_depot): string
	{
		$dossier_memoir = "/tmp/memoire";
		$dossier_memoir_absolue = realpath($dossier_memoir);
		if (is_dir($dossier_memoir)) {
			Log::debug("Le dossier $dossier_memoir_absolue existe.");
		} else {
			Log::debug("Le $dossier_memoir_absolue n'existe pas.");
		}

		$dossier_temporaire = $dossier_memoir . "/git_repo_" . uniqid();
		Log::debug("chemin du depot temporaire: " . $dossier_temporaire);
		Log::debug("URL du dépot git: " . $url_du_depot);

		exec("git clone --depth 1 $url_du_depot $dossier_temporaire 2>&1", $output, $returnCode);
		Log::debug("Output du clonage depot git: " . implode(PHP_EOL, $output));
		Log::debug("Code retour du clonage depot git: " . $returnCode);

		if ($returnCode !== 0) {
			throw new RuntimeException("Le clonage du dépôt a échoué : votre dépôt est privé ou n'existe pas.");
		}

		return $dossier_temporaire;
	}

	public function chercher_info(string $dossier_temporaire): string
	{
		$liste_info_yml = null;
		$code_de_retour = null;
		try {
			exec("find $dossier_temporaire -name 'info.yml'", $liste_info_yml, $code_de_retour);
		} catch (Exception $e) {
			throw new RunTimeException("Erreur inconnue.");
		}

		if ($code_de_retour !== 0 || !$liste_info_yml) {
			throw new ChargeurException("Fichier info.yml inexistant.");
		}

		if (in_array("./info.yml", $liste_info_yml)) {
			array_unshift($liste_info_yml, "./info.yml");
		}
		$chemin_fichier_dans_depot = $liste_info_yml[count($liste_info_yml) - 1];

		Log::debug("Liste info.yml" . implode(PHP_EOL, $liste_info_yml));
		Log::debug("chemin du depot" . $chemin_fichier_dans_depot);

		return $chemin_fichier_dans_depot;
	}

	public function supprimer_dossier_temporaire(string $dossier_temporaire): void
	{
		exec("rm -rf $dossier_temporaire");
	}
}
