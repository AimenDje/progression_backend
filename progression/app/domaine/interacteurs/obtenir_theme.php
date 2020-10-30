<?php

require_once 'domaine/entités/theme.php';
require_once __DIR__.'/interacteur.php';
require_once __DIR__.'/obtenir_serie.php';

class ObtenirThèmeInt extends Interacteur {

	function __construct( $source, $user_id ) {
		parent::__construct( $source );
		$this->_user_id=$user_id;
	}
	
	function get_thèmes(){
		$user = $this->_source->get_user_dao()->get_user( $this->_user_id );
		$thèmes = $this->_source->get_thème_dao()->get_thèmes( $user->role == User::ROLE_ADMIN );
		ObtenirThèmeInt::calculer_avancement( $thèmes );

		return $thèmes;
	}

	function get_thème( $thème_id ){
		return $this->_source->get_thème_dao()->get_thème( $thème_id );
	}	
	
	private function calculer_avancement( $thèmes ){
		foreach( $thèmes as $thème ){
			$interacteur = new ObtenirThèmeInt( $this->_source, $this->_user_id );
			$thème->avancement=$interacteur->get_pourcentage_avancement( $thème->id );
		}
	}
	
    function get_pourcentage_avancement( $thème_id ){
		$nb_questions_réussies = $this->_source->get_thème_dao()->get_avancement( $thème_id, $this->_user_id );
		$nb_questions_total = $this->_source->get_thème_dao()->get_nb_questions_actives( $thème_id );
		return floor( $nb_questions_réussies / $nb_questions_total * 100 );
	}

}

?>