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
namespace progression\dao\chargeur;

use Gitonomy\Git\Admin;
use Illuminate\Support\Facades\File;
use RuntimeException;
use progression\facades\Git;
use progression\dao\chargeur\{Chargeur, ChargeurException};
use Spatie\TemporaryDirectory\TemporaryDirectory;

class ChargeurRessourceGit extends Chargeur
{
	/**
	 * @return array<mixed>
	 */
	public function récupérer_fichier(string $uri): array
	{
		$répertoire_temporaire = $this->cloner_dépôt($uri);

		$chargeurFichier = $this->source->get_chargeur_fichier();
		return $chargeurFichier->récupérer_fichier($répertoire_temporaire->path("/"));
	}

	/**
	 * @param string $url_du_dépôt
	 */
	public function id_modif(string $uri): string|false
	{
		$fragment = parse_url($uri, PHP_URL_FRAGMENT) ?? "";
		$uri_valide = str_replace("#{$fragment}", "", $uri);
		$branches = $fragment ? [$fragment] : ["main", "master"];

		$options = ["--heads", "--refs"];

		try {
			$liste_commits = Git::ls_remote($uri_valide, $branches, $options);
		} catch (RuntimeException $e) {
			throw new ChargeurException("Le dépôt «{$uri}» n'existe pas ou est inaccessible.");
		}

		if ($liste_commits) {
			[$hash_dernier_commit] = explode("\t", $liste_commits[0]);
			return trim($hash_dernier_commit);
		}

		throw new ChargeurException(
			"Impossible de récupérer le dernier commit sur l'une des branches [" . implode(", ", $branches) . "].",
		);
	}

	private function cloner_dépôt(string $url_du_dépôt): TemporaryDirectory
	{
		$fragment = parse_url($url_du_dépôt, PHP_URL_FRAGMENT) ?? "";
		$url_valide = str_replace("#{$fragment}", "", $url_du_dépôt);
		$branche = $fragment;

		$répertoire_temporaire = (new TemporaryDirectory(getenv("TEMPDIR") ?: sys_get_temp_dir()))
			->deleteWhenDestroyed()
			->create();

		try {
			Git::clone(
				$répertoire_temporaire->path("/"),
				$url_valide,
				array_merge(["--depth=1", "--single-branch"], $branche ? ["--branch={$branche}"] : []),
			);
		} catch (RuntimeException $e) {
			throw new ChargeurException(
				"Le clonage du dépôt «{$url_valide}» a échoué! Le dépôt n'existe pas ou est inaccessible.",
			);
		}
		return $répertoire_temporaire;
	}
}
