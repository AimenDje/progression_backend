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

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class GestionFichiers
{
	/**
	 * @var GestionFichiers
	 */
	private static $instance = null;

	private function __construct()
	{
	}

	/**
	 * @return GestionFichiers
	 */
	static function getInstance(): GestionFichiers
	{
		if (self::$instance === null) {
			self::$instance = new GestionFichiers();
		}

		return self::$instance;
	}

	public function creerDossier(string $chemin): void
	{
		if (!File::isDirectory($chemin)) {
			File::makeDirectory($chemin, 0777, true);
			Log::debug("Dossier créé : $chemin");
		}
	}

	public function supprimerDossier(string $chemin): void
	{
		if (File::isDirectory($chemin)) {
			File::deleteDirectory($chemin);
			Log::debug("Dossier supprimé : $chemin");
		}
	}

	/**
	 * @param string $cheminComplet
	 * @return string
	 * @throws RuntimeException
	 */
	public function verifierExistenceFichier(string $cheminComplet): string
	{
		if (File::exists($cheminComplet)) {
			Log::debug("Fichier trouvé : " . $cheminComplet);
			return $cheminComplet;
		}

		throw new RuntimeException("Fichier info.yml inexistant dans le dépôt.");
	}
}
