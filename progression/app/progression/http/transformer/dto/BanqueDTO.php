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

namespace progression\http\transformer\dto;

use progression\http\contrôleur\BanqueCtl;

class BanqueDTO extends GénériqueDTO
{
	public array $banques;
	
	public function __construct(mixed $id, mixed $object, array $liens)
	{
		parent::__construct($id, $object, $liens);
		
		$this->banques = [];
		foreach ($object->banques as $uri => $banque) {
			array_push(
				$this->banques,
				new BanqueDTO(id: "{$id}/{$uri}", objet: $banque, liens: BanqueCtl::get_liens($id, $uri)),
			);
		}
	}
}

