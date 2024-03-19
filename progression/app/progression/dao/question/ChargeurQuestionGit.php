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

class ChargeurQuestionGit extends Chargeur
{
	/**
	 * @param string $uri
	 * @return array<mixed>
	 */

	public function récupérer_question(string $uri): array
	{
		$répertoire_temporaire = $this->cloner_dépôt($uri);

		$chemin_fichier_dans_dépôt = $this->chercher_info($répertoire_temporaire);

		$chargeurFichier = $this->source->get_chargeur_question_fichier();

		$contenu_question = $chargeurFichier->récupérer_question($chemin_fichier_dans_dépôt);

		$this->supprimer_répertoire_temporaire($répertoire_temporaire);

		return $contenu_question;
	}

	private function cloner_dépôt(string $url_du_dépôt): string
	{
		$dossier_memoir = config('params.repertoire_temporaire');
		$dossier_temporaire = $dossier_memoir . "/git_repo_" . uniqid();

		if (!is_dir($dossier_memoir)) {
			mkdir($dossier_memoir, 0777, true);
			Log::debug("Création du dossier mémoire : $dossier_memoir");
		}

		Log::debug("Chemin du dépôt temporaire: " . $dossier_temporaire);
		Log::debug("URL du dépôt git: " . $url_du_dépôt);

		try {
			Admin::cloneTo($dossier_temporaire, $url_du_dépôt, false);
			Log::debug("Dépôt cloné avec succès à : $dossier_temporaire");
		} catch (ChargeurException $e) {
			Log::error("Erreur lors du clonage du dépôt : " . $e->getMessage());
			throw new ChargeurException(
				"Le clonage du dépôt git a échoué! Ce dépôt est peut-être privé ou n'existe pas.",
			);
		}

		return $dossier_temporaire;
	}

	public function chercher_info(string $répertoire_temporaire): string
	{
		$cheminDirect = $répertoire_temporaire . "/info.yml";

		if (file_exists($cheminDirect)) {
			$chemin_fichier_dans_dépôt = $cheminDirect;
		} else {
			$cheminRecherche = $répertoire_temporaire . "/**/info.yml";
			$fichiers = glob($cheminRecherche, GLOB_BRACE);

			if (is_array($fichiers) && $fichiers) {
				$chemin_fichier_dans_dépôt = $fichiers[0];
			}
		}

		if (empty($chemin_fichier_dans_dépôt)) {
			throw new RuntimeException("Fichier info.yml inexistant dans le dépôt.");
		}

		Log::debug("Fichier info.yml trouvé : " . $chemin_fichier_dans_dépôt);

		return $chemin_fichier_dans_dépôt;
	}

	private function supprimer_répertoire_temporaire(string $dossier_temporaire): void
	{
		if (is_dir($dossier_temporaire)) {
			system("rm -rf " . escapeshellarg($dossier_temporaire));
			Log::debug("Dossier temporaire supprimé : $dossier_temporaire");
		}
	}
}
