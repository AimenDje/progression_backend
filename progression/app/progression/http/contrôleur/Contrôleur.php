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

namespace progression\http\contrôleur;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Log, App};
use Laravel\Lumen\Routing\Controller as BaseController;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Serializer\JsonApiSerializer;

use Symfony\Component\HttpFoundation\Cookie;
use Carbon\Carbon;

class Contrôleur extends BaseController
{
	//JsonApiSerializer ajoute un slash à l'URL de base, on s'assure d'enlèver le slash ultime
	public static string $urlBase = "";

	protected Manager $manager;

	protected function réponse_json($réponse, $code)
	{
		return response()->json(
			$réponse,
			$code,
			[
				"Content-Type" => "application/vnd.api+json",
				"Charset" => "utf-8",
			],
			JSON_UNESCAPED_UNICODE,
		);
	}

	protected function getFractalManager()
	{
		return $this->manager;
	}

	public function __construct()
	{
		$request = app(Request::class);
		$this->manager = new Manager();

		// On redéfinit le Serializer pour avoir des liens «relationship» personnalisés
		$this->manager->setSerializer(new JsonApiSerializer(Contrôleur::$urlBase));
		if (!empty($request->query("include"))) {
			$this->manager->parseIncludes($request->query("include"));
		}
	}

	/**
	 * @return array<string>
	 */
	protected function get_includes(): array
	{
		return $this->manager->getRequestedIncludes();
	}

	public function item($data, $transformer)
	{
		if ($data == null) {
			return null;
		}

		$resource = new Item($data, $transformer, $transformer->type);
		$item = $this->manager->createData($resource)->toArray();

		return $item;
	}

	public function collection($data, $transformer)
	{
		if ($data === null) {
			return null;
		}

		$resource = new Collection($data, $transformer, $transformer->type);
		$item = $this->manager->createData($resource)->toArray();

		return $item;
	}

	protected function créerCookieSécure(
		string $nom,
		string $valeur,
		int $expiration = null,
		int $âge_max = 3600,
	): Cookie {
		return Cookie::create(
			name: $nom,
			value: $valeur,
			expire: $expiration ?? Carbon::now()->getTimestamp() + $âge_max,
			secure: App::environment(["prod", "staging"]) !== false,
			httpOnly: true,
			sameSite: "strict",
		);
	}

	protected function préparer_réponse($réponse, $code = 200)
	{
		$request = app(Request::class);
		if ($réponse === null) {
			Log::warning("({$request->ip()}) - {$request->method()} {$request->path()} (" . get_class($this) . ")");
			return $this->réponse_json(["erreur" => "Ressource non trouvée."], 404);
		} else {
			Log::info("({$request->ip()}) - {$request->method()} {$request->path()} (" . get_class($this) . ")");
			return $this->réponse_json($réponse, $code);
		}
	}
}

//Initialise urlBase
Contrôleur::$urlBase = preg_replace("/\/+$/", "", config("app.url") ?: "") ?? "";
