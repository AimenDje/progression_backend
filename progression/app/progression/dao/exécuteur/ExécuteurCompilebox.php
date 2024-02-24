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

namespace progression\dao\exécuteur;

use progression\domaine\entité\Exécutable;
use Illuminate\Support\Facades\Log;
use progression\domaine\entité\{TestProg, TestSys};

class ExécuteurCompilebox extends Exécuteur
{
	const langages = [
		"python2" => 0,
		"python" => 1,
		"ruby" => 2,
		"clojure" => 3,
		"php" => 4,
		"javascript" => 5,
		"scala" => 6,
		"go" => 7,
		"cpp" => 8,
		"c" => 9,
		"java" => 10,
		"bash" => 11,
		"perl" => 12,
		"sshd" => 13,
		"SQL" => 14,
		"powershell" => 15,
		"typescript" => 16,
		"kotlin" => 17,
		"c#" => 18,
		"rust" => 19,
	];

	/**
	 * @param Exécutable $exécutable
	 * @param array<TestProg> $tests
	 *
	 * @return array<mixed> Un tableau de "résultats"=>array<id, Résultat> et "temps_exécution"=>int
	 */
	public function exécuter_prog(Exécutable $exécutable, array $tests, string $image = null): array
	{
		$tests_out = [];
		foreach ($tests as $test) {
			$tests_out[] = [
				"stdin" => $test->entrée ?? "",
				"params" => $test->params ?? "",
			];
		}

		$langage = array_key_exists($exécutable->lang, self::langages)
			? self::langages[$exécutable->lang]
			: ($langage = $exécutable->lang);

		$data_rc = [
			"language" => $langage,
			"code" => $exécutable->code,
			"parameters" => "",
			"tests" => $tests_out,
			"vm_name" => $image ?? config("compilebox.image"),
		];

		$réponse = $this->envoyer_requête($data_rc);
		return $this->préparer_résultats_prog($réponse);
	}

	/**
	 * @param string $utilisateur
	 * @param string $image
	 * @param string $conteneur_id
	 * @param string $init
	 * @param array<TestSys> $tests
	 *
	 * @return array<mixed> Un tableau de "résultats"=>array<id, Résultat> et "temps_exécution"=>int
	 */
	public function exécuter_sys(
		string|null $utilisateur,
		string $image,
		string|null $conteneur_id,
		string|null $init,
		array $tests,
		int|null $test_index, //Inutilisé pour le moment
		string|null $commande,
	): array {
		$tests_out = [];
		foreach ($tests as $test) {
			$tests_out[] = ["stdin" => $test->validation];
		}

		$data_rc = [
			"language" => self::langages["sshd"],
			"user" => $utilisateur ?? "",
			"parameters" => $conteneur_id ?? "",
			"code" => $init,
			"tests" => $tests_out,
			"vm_name" => $image,
			"user_cmd" => $commande,
		];

		$réponse = $this->envoyer_requête($data_rc);
		return $this->préparer_résultats_sys($réponse);
	}

	/**
	 * @return array<mixed>
	 */
	public function terminer(string $conteneur_id): array
	{
		$data_rc = [
			"language" => self::langages["sshd"],
			"parameters" => $conteneur_id,
			"code" => "reset",
		];

		$réponse = $this->envoyer_requête($data_rc);
		return $réponse;
	}

	private function envoyer_requête($data_rc)
	{
		$options_rc = [
			"http" => [
				"header" => "Content-type: application/x-www-form-urlencoded\r\n",
				"method" => "POST",
				"content" => http_build_query($data_rc),
			],
		];
		$context = stream_context_create($options_rc);

		try {
			$comp_resp = file_get_contents(config("compilebox.url"), false, $context);
			return $comp_resp ? json_decode(str_replace("\r", "", $comp_resp), true) : false;
		} catch (\ErrorException $e) {
			if (isset($http_response_header)) {
				if (
					is_array($http_response_header) &&
					count($http_response_header) > 0 &&
					$http_response_header[0] == "HTTP/1.1 400 Bad Request"
				) {
					throw new ExécutionException("Requête intraitable par Compilebox", 400);
				} else {
					Log::error($e->getMessage());
					throw new ExécutionException("Erreur inconnue.", 500);
				}
			} else {
				Log::error($e->getMessage());
				throw new ExécutionException("Compilebox non disponible", 503);
			}
		}
	}

	/**
	 * @param array<mixed> $résultats
	 * @return array<mixed>
	 */
	private function préparer_résultats_prog(array $résultats): array
	{
		return [
			"résultats" => $résultats["résultats"],
			"temps_exécution" => $résultats["temps_exec"],
		];
	}

	/**
	 * @param array<mixed> $résultats
	 * @return array<mixed>
	 */
	private function préparer_résultats_sys(array $résultats): array
	{
		return [
			"résultats" => $résultats["résultats"],
			"conteneur_id" => $résultats["conteneur"]["id"],
			"url_terminal" => $résultats["conteneur"]["path"],
			"temps_exécution" => 0,
		];
	}
}
