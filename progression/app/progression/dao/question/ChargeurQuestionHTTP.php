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

use RuntimeException;

class ChargeurQuestionHTTP extends Chargeur
{
	public function récupérer_question($uri)
	{
		$entêtes = array_change_key_case($this->source->get_chargeur_http()->get_entêtes($uri));

		$code = self::get_entête($entêtes, "0");
		if (!self::vérifier_code_http($code)) {
			return null;
		}

		$taille = self::get_entête($entêtes, "content-length");
		self::vérifier_taille($taille);

		$content_type = self::get_entête($entêtes, "content-type");
		self::vérifier_type($content_type);

		if (str_starts_with($content_type, "application")) {
			$type_archive = self::déterminer_type_archive(
				self::get_entête($entêtes, "content-type"),
				self::get_entête($entêtes, "content-disposition"),
			);
			return self::extraire_archive($uri, $type_archive);
		} elseif (str_starts_with($content_type, "text")) {
			return $this->source->get_chargeur_question_fichier()->récupérer_question($uri);
		}
	}

	private function get_entête($entêtes, $clé)
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

	private function vérifier_taille($taille)
	{
		$taille_max = config("limites.taille_question");

		if (!$taille) {
			throw new ChargeurException("Fichier de taille inconnue. On ne le chargera pas.");
		}

		if ($taille > $taille_max) {
			throw new ChargeurException("Fichier trop volumineux ($taille > $taille_max). On ne le chargera pas.");
		}
	}

	private function vérifier_type($type)
	{
		if (!preg_match("/(application|text)\/.*/", $type)) {
			throw new ChargeurException("Impossible de charger le fichier de type $type");
		}
	}

	private function extraire_archive($uri, $type_archive)
	{
		$chemin_fichier = self::télécharger_fichier($uri);
		try {
			$question = $this->source
				->get_chargeur_question_archive()
				->récupérer_question($chemin_fichier, $type_archive);
		} catch (ChargeurException $e) {
			throw $e;
		} finally {
			unlink($chemin_fichier);
		}

		return $question;
	}

	private function déterminer_type_archive($content_type, $content_disposition)
	{
		return self::déterminer_type_par_mime($content_type) ||
			self::déterminer_type_par_extension($content_disposition);
	}

	private function déterminer_type_par_mime($content_type)
	{
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

	private function déterminer_type_par_extension($content_disposition)
	{
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

	private function télécharger_fichier($uri)
	{
		$nomUnique = uniqid("archive_", true);
		$chemin = sys_get_temp_dir() . "/$nomUnique.arc";

		$contenu = $this->source->get_chargeur_http()->get_url($uri);

		if ($contenu === false) {
			throw new ChargeurException("Impossible de charger le fichier archive $uri");
		}

		if (file_put_contents($chemin, $contenu)) {
			return $chemin;
		}

		return false;
	}
}
