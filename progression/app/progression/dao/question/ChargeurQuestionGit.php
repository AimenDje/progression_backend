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
use Gitonomy\Git\Repository;
use Illuminate\Support\Facades\Log;

class ChargeurQuestionGit extends ChargeurQuestion
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

	/**
	 * @param string $uri
	 * @param int|string $cle
	 * @return bool
	 */
	public function est_modifié(string $uri, $hash_cache): bool
	{
		$remote_hash = $this-­>obtenir_hash_dernier_commit($uri);
		if($hash_cache == $remote_hash)
		{
			return true;
		}
		else
		{
			return false;
		}
		
	}

	/**
	 * @param string $uri
	 * @return string
	 */
	public function obtenir_hash_dernier_commit(string $uri):string
	{
		 try {
        	$répertoire_temporaire = Repository::createTemporary();
        
        	$répertoire_temporaire->addRemote('origin', $uri);

        	$répertoire_temporaire->run('fetch', ['origin']);

			$brancheParDefaut = $répertoire_temporaire->getDefaultBranch();

        	$latestCommitHash = $répertoire_temporaire->run('rev-parse', ["origin/$brancheParDefaut"]);

        	return trim($latestCommitHash);
    	} catch (Exception $e) {

			Log::error("Erreur lors de l'obtention du hash du dernier commit : " . $e->getMessage());
			throw new ChargeurException(
				"L'obtention du hash du dernier commit a échoué! Ce dépôt est peut-être privé ou n'existe pas.",
			);
    	}
	}

	/**
	 * @param string $url_du_dépôt
	 * @return string
	 */
	private function cloner_dépôt(string $url_du_dépôt): string
	{
		$répertoire_cible = sys_get_temp_dir();
		$dossier_temporaire = $répertoire_cible . "/git_repo_" . uniqid();

		$this->gestionFichiers->creerDossier($répertoire_cible);

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

	/**
	 * @param string $répertoire_temporaire
	 * @return string
	 */
	public function chercher_info(string $répertoire_temporaire): string
	{
		$cheminDirect = $répertoire_temporaire . "/info.yml";

		return $this->gestionFichiers->verifierExistenceFichier($cheminDirect);
	}

	/**
	 * @param string $dossier_temporaire
	 */
	private function supprimer_répertoire_temporaire(string $dossier_temporaire): void
	{
		$this->gestionFichiers->supprimerDossier($dossier_temporaire);
		Log::debug("Dossier temporaire supprimé : $dossier_temporaire");
	}
}
