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

namespace progression\dao;

use progression\domaine\entité\{Avancement, Question};

class AvancementDAO extends EntitéDAO
{
	public function get_avancement($question_id, $user_id)
	{
		$type = $this->_source->get_question_dao()->get_type($question_id);

		if ($type == null) {
			return null;
		} else {
			if ($type == Question::TYPE_PROG) {
				return $this->_source->get_avancement_prog_dao()->get_avancement($question_id, $user_id);
			} elseif ($type == Question::TYPE_SYS) {
				return $this->_source->get_avancement_sys_dao()->get_avancement($question_id, $user_id);
			} elseif ($type == Question::TYPE_BD) {
				return $this->_source->get_avancement_BD_dao()->get_avancement($question_id, $user_id);
			}
		}
	}

	protected function load($objet)
	{
		$query = $this->conn->prepare("SELECT userID, etat FROM avancement WHERE questionID = ? AND userID = ?");
		$query->bind_param("ii", $objet->question_id, $objet->user_id);
		$query->execute();
		$query->bind_result($objet->id, $objet->etat);
		$query->fetch();

		$query->close();
	}
}
?>
