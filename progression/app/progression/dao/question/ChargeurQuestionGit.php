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
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

class ChargeurQuestionGit extends ChargeurQuestion
{
	/**
	 * @param string $uri
	 * @return array<mixed>
	 */
	public function récupérer_question(string $uri): array
	{
		$répertoire_temporaire = $this->cloner_dépôt($uri);

		$dernierCommit = $this->getIdDernierCommit($répertoire_temporaire);

		$chemin_fichier_dans_dépôt = $this->chercher_info($répertoire_temporaire);

		$chargeurFichier = $this->source->get_chargeur_question_fichier();

		$contenu_question = $chargeurFichier->récupérer_question($chemin_fichier_dans_dépôt);

		$donnéesÀMettreEnCache = [
			"contenu" => $contenu_question,
			"cléModification" => $dernierCommit,
		];

		$cléCache = md5($uri);

		Cache::put($cléCache, $donnéesÀMettreEnCache);

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
		$remote_hash = $this - ­ > obtenir_hash_dernier_commit($uri);
		return $hash_cache === $remote_hash;
	}

	/**
	 * @param string $uri
	 * @return string
	 */
	public function obtenir_hash_dernier_commit(string $uri): string
	{
		try {
			$répertoire_temporaire = Repository::createTemporary();

			$répertoire_temporaire->addRemote("origin", $uri);

			$répertoire_temporaire->run("fetch", ["origin"]);

			$brancheParDefaut = $répertoire_temporaire->getDefaultBranch();

			$latestCommitHash = $répertoire_temporaire->run("rev-parse", ["origin/$brancheParDefaut"]);

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
		$répertoire_temporaire = $répertoire_cible . "/git_repo_" . uniqid();

		if (!File::isDirectory($répertoire_cible)) {
			File::makeDirectory($répertoire_cible, 0777, true);
			Log::debug("Dossier créé : $répertoire_cible");
		}

		Log::debug("Chemin du dépôt temporaire: " . $répertoire_temporaire);
		Log::debug("URL du dépôt git: " . $url_du_dépôt);

		try {
			Admin::cloneTo($répertoire_temporaire, $url_du_dépôt, false);
			Log::debug("Dépôt cloné avec succès à : $répertoire_temporaire");
		} catch (ChargeurException $e) {
			Log::error("Erreur lors du clonage du dépôt : " . $e->getMessage());
			throw new ChargeurException(
				"Le clonage du dépôt git a échoué! Ce dépôt est peut-être privé ou n'existe pas.",
			);
		}
		return $répertoire_temporaire;
	}

	private function getIdDernierCommit(string $répertoire): string
	{
		$repository = new Repository($répertoire);
		$commit = $repository->getHeadCommit();

		if ($commit !== null) {
			Log::debug("Voici le dernier commit: " . $commit->getHash());
			return $commit->getHash();
		} else {
			Log::error("Aucun commit trouvé dans le dépôt.");
			throw new RuntimeException("Aucun commit trouvé dans le dépôt cloné.");
		}
	}

	/**
	 * @param string $répertoire_temporaire
	 * @return string
	 */
	private function chercher_info(string $répertoire_temporaire): string
	{
		$cheminDirect = $répertoire_temporaire . "/info.yml";

		if (File::exists($cheminDirect)) {
			Log::debug("Fichier trouvé : " . $cheminDirect);
			return $cheminDirect;
		}

		throw new RuntimeException("Fichier info.yml inexistant dans le dépôt.");
	}

	/**
	 * @param string $dossier_temporaire
	 */
	private function supprimer_répertoire_temporaire(string $dossier_temporaire): void
	{
		if (File::isDirectory($dossier_temporaire)) {
			File::deleteDirectory($dossier_temporaire);
			Log::debug("Dossier temporaire supprimé : $dossier_temporaire");
		}
	}
}
