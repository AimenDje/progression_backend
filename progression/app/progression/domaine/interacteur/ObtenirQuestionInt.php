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

namespace progression\domaine\interacteur;

use progression\dao\DAOException;
use progression\dao\question\ChargeurException;
use DomainException, LengthException, BadMethodCallException;

class ObtenirQuestionInt extends Interacteur
{
	function get_question($question_id)
	{
		try {
			return $this->source_dao->get_question_dao()->get_question($question_id);
		} catch (BadMethodCallException $e) {
			throw new ParamètreInvalideException($e);
		} catch (DAOException $e) {
			throw new IntéracteurException($e, 502);
		} catch (LengthException | DomainException | ChargeurException $e) {
			throw new RessourceInvalideException($e);
		}
	}
}
