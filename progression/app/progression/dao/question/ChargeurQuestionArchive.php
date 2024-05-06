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

use ZipArchive;
use Spatie\TemporaryDirectory\TemporaryDirectory;

class ChargeurQuestionArchive extends Chargeur
{
	public function récupérer_question($chemin_archive, $type_archive = false)
	{
		if (!$type_archive) {
			throw new ChargeurException(
				"Impossible de déterminer automatiquement le type de l'archive $chemin_archive.",
			);
		}

		if ($type_archive != "zip") {
			throw new ChargeurException("Type d'archive $type_archive non implémenté.");
		}

		$archiveExtraite = null;

		$destination = (new TemporaryDirectory(getenv("TEMPDIR") ?: sys_get_temp_dir()))
			->deleteWhenDestroyed()
			->create();

		self::extraire_zip($chemin_archive, $destination->path());
		try {
			$question = $this->source
				->get_chargeur_question_fichier()
				->récupérer_question("file://" . $destination->path() . "/info.yml");
		} catch (ChargeurException $e) {
			throw $e;
		}

		return $question;
	}

	private function extraire_zip($chemin_archive, $destination)
	{
		$zip = new ZipArchive();
		$res = $zip->open($chemin_archive);
		if ($res !== true) {
			throw new ChargeurException("Impossible de lire l'archive $chemin_archive (err.: $res)");
		}
		$res = $zip->extractTo($destination);
		if ($res !== true) {
			throw new ChargeurException("Impossible de décompresser l'archive $chemin_archive (err.: $res)");
		}

		$zip->close();
	}
}
