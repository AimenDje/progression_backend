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
?><?php

require_once 'controleur.php';
require_once 'domaine/entités/user.php';

class ControleurAdmin extends Controleur {

	function __construct( $id, $user_id ){
		parent::__construct( $id, $user_id );

		$user=new User( $user_id );
		if ( $user->role != User::ROLE_ADMIN ){
			http_response_code( 403 );
			die( 'Forbidden' );
		}
	}
}
