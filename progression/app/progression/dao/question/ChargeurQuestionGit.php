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

use progression\facades\Git;

use RuntimeException;
use Spatie\TemporaryDirectory\TemporaryDirectory;

class ChargeurQuestionGit extends ChargeurQuestion
{
	/**
	 * @return array<mixed>
	 */
	public function récupérer_question(string $uri): array
	{
		$répertoire_temporaire = $this->cloner_dépôt($uri);

		$chemin_fichier_dans_dépôt = $répertoire_temporaire->path() . "/info.yml";
		$chargeurFichier = $this->source->get_chargeur_question_fichier();

		$contenu_question = $chargeurFichier->récupérer_question($chemin_fichier_dans_dépôt);

		return $contenu_question;
	}

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
				$répertoire_temporaire->path(),
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
