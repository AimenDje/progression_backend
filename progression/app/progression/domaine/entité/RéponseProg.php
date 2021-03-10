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

namespace progression\domaine\entité;

class RéponseProg
{
	public $langage;
	public $code;
	public $date_soumission;
	public $tests_réussis;
	public $feedback;

	public function __construct($langage, $code, $date_soumission = null, $tests_réussis = null, $feedback = null)
	{
		$this->langage = $langage;
		$this->code = $code;
		$this->date_soumission = $date_soumission;
        $this->feedback = $feedback;
        $this->tests_réussis = $tests_réussis;
	}
}
