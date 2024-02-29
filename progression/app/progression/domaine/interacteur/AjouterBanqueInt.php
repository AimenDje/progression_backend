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

use progression\domaine\entité\banque;

class AjouterBanqueInt extends interacteur
{
	public function ajouter_banque($nom, $url, $username): array
    {
        if (empty($nom)) {
            throw new RessourceInvalideException("Le nom ne peut être vide");
        }
        if (empty($url)) {
            throw new RessourceInvalideException("L'url ne peut être invalide");
        }
    
        $user_dao = $this->source_dao->get_user_dao();
        $user = $user_dao->get_user($username);
        if (empty($user)) {
            throw new RessourceInvalideException("L'utilisateur ne peut être invalide");
        }
        $dao = $this->source_dao->get_banque_dao();
    
        return $dao->ajouter($nom, $url, $user->id);
    }
}
