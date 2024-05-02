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

class Chargeur
{
	const ERR_CHARGEMENT = 255;

	protected ChargeurFactory $source;

	public function __construct(ChargeurFactory|null $source = null)
	{
		if ($source == null) {
			$this->source = ChargeurFactory::get_instance();
		} else {
			$this->source = $source;
		}
	}

	/**
	 * @return array<mixed>
	 */
	public function récupérer_fichier(string $uri): array
	{
		$scheme = parse_url($uri, PHP_URL_SCHEME);

		if ($scheme == "file") {
			$chargeur = $this->source->get_chargeur_fichier();
			$infos_question = $chargeur->récupérer_fichier($uri);
		} elseif ($scheme == "http" || $scheme == "https") {
			$infos_question = $this->source->get_chargeur_ressource_http()->récupérer_fichier($uri);
		} else {
			throw new \BadMethodCallException("Schéma d'URI invalide");
		}

		return $infos_question;
	}
}
