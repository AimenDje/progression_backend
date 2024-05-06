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
use Illuminate\Support\Facades\File;

class ChargeurQuestionFichier extends ChargeurQuestion
{
	public function récupérer_question($uri): array
	{
		$output = null;
		$err_code = null;

		//Les limites doivent être suffisamment basses pour empêcher les «abus» (inclusion récursive, fichiers volumineux, etc.)
		exec(
			"ulimit -s 256 && ulimit -t 3 && python3 -m progression_qc " . escapeshellarg($uri) . " 2>/dev/null",
			$output,
			$err_code,
		);

		if ($err_code != 0) {
			throw new ChargeurException("Le fichier n'existe pas ou est invalide. (err: {$err_code})");
		}

		$info = yaml_parse(implode("\n", $output));
		if ($info === false) {
			throw new RuntimeException("Le fichier ne peut pas être décodé. Le format produit est invalide.");
		}

		return $info;
	}

	public function id_modif(string $uri): string|false
	{
		if (File::isFile($uri)) {
			return strval(File::lastModified($uri));
		} else {
			return false;
		}
	}
}
