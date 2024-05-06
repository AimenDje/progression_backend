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

use progression\dao\DAOException;
use progression\dao\chargeur\ChargeurException;
use RuntimeException;
use Spatie\TemporaryDirectory\TemporaryDirectory;

class ChargeurRessourceHTTP extends Chargeur
{
	/**
	 * @return array<mixed>
	 */
	public function récupérer_fichier(string $uri): array
	{
		$entêtes = array_change_key_case($this->source->get_chargeur_http()->get_entêtes($uri));

		$code = self::get_entête($entêtes, "0");

		if ($code === null || !self::vérifier_code_http($code)) {
			throw new DAOException("Impossible de récupérer les entêtes");
		}

		$taille = self::get_entête($entêtes, "content-length");
		self::vérifier_taille(intval($taille));

		$content_type = self::get_entête($entêtes, "content-type");

		if ($content_type === null) {
			throw new ChargeurException("Impossible de charger le fichier de type inconnu.");
		}

		self::vérifier_type($content_type);

		if (str_starts_with($content_type, "application")) {
			$type_archive = self::déterminer_type_archive(
				self::get_entête($entêtes, "content-type"),
				self::get_entête($entêtes, "content-disposition"),
			);
			if ($type_archive === false) {
				throw new ChargeurException("Impossible de charger le fichier de type inconnu.");
			}
			return self::extraire_archive($uri, $type_archive);
		} elseif (str_starts_with($content_type, "text")) {
			return $this->source->get_chargeur_fichier()->récupérer_fichier($uri);
		} else {
			throw new ChargeurException("Impossible de charger le fichier de type ${content_type}");
		}
	}

	/**
	 * @param array<mixed> $entêtes
	 */
	private function get_entête(array $entêtes, string $clé): string|null
	{
		if ($entêtes == null) {
			return null;
		}

		if (!array_key_exists($clé, $entêtes)) {
			return null;
		}

		$content_type = $entêtes[$clé];

		if (is_string($content_type)) {
			return $content_type;
		}

		if (is_array($content_type)) {
			return $content_type[count($content_type) - 1];
		}

		throw new RuntimeException("L'entête $clé est de type " . gettype($content_type));
	}

	private function vérifier_code_http(string $code): bool
	{
		return explode(" ", $code)[1] == "200";
	}

	private function vérifier_taille(int $taille): void
	{
		$taille_max = config("limites.taille_question");

		if (!$taille) {
			throw new ChargeurException("Fichier de taille inconnue. On ne le chargera pas.");
		}

		if ($taille > $taille_max) {
			throw new ChargeurException("Fichier trop volumineux ($taille > $taille_max). On ne le chargera pas.");
		}
	}

	private function vérifier_type(string $type): void
	{
		if (!preg_match("/(application|text)\/.*/", $type)) {
			throw new ChargeurException("Impossible de charger le fichier de type $type");
		}
	}

	/**
	 * @return array<mixed>
	 */
	private function extraire_archive(string $uri, string $type_archive): array
	{
		$chemin_fichier = self::télécharger_fichier($uri);

		if ($chemin_fichier === false) {
			throw new ChargeurException("Impossible de charger le fichier archive $uri");
		}

		try {
			$ressource = $this->source->get_chargeur_archive()->récupérer_fichier($chemin_fichier, $type_archive);
		} catch (ChargeurException $e) {
			throw $e;
		} finally {
			unlink($chemin_fichier);
		}

		return $ressource;
	}

	private function déterminer_type_archive(string|null $content_type, string|null $content_disposition): string|false
	{
		return self::déterminer_type_par_mime($content_type) ?:
			self::déterminer_type_par_extension($content_disposition);
	}

	private function déterminer_type_par_mime(string|null $content_type): string|false
	{
		if ($content_type === null) {
			return false;
		}
		preg_match("/application\/(x-)*(.*)(-compressed)*/", $content_type, $résultats);
		if (array_key_exists(2, $résultats)) {
			switch ($résultats[2]) {
				case "zip":
				case "7z":
				case "tar":
					return $résultats[2];
				case "gzip":
					return "gz";
				case "vnd.rar":
					return "rar";
			}
		}
		return false;
	}

	private function déterminer_type_par_extension(string|null $content_disposition): string|false
	{
		if ($content_disposition === null) {
			return false;
		}
		preg_match('/filename=\".+\.(.*)\"/i', $content_disposition, $résultats);
		if (!array_key_exists(1, $résultats)) {
			return false;
		}
		$ext = strtolower($résultats[1]);

		if (in_array($ext, ["zip", "rar", "xz", "7z", "tar", "tgz", "gz"])) {
			return $ext;
		} else {
			return false;
		}
	}

	private function télécharger_fichier(string $uri): TemporaryDirectory
	{
		$destination = (new TemporaryDirectory(getenv("TEMPDIR")))->deleteWhenDestroyed()->create();
		$contenu = $this->source->get_chargeur_http()->get_url($uri);

		if (file_put_contents($destination->path("archive.arc"), $contenu)) {
			return $destination;
		}

		return false;
	}
}
