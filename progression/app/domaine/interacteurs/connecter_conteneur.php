<?php 

require_once __DIR__.'/interacteur.php';

class ConnecterConteneurInt extends Interacteur {
	
	function connexion_conteneur( $question, $avancement ){
		$this->question = $question;
		$this->avancement = $avancement;
		
		$url_rc=$this->get_url_compilebox();
		$options_rc=$this->get_options_compilebox();

		$context=stream_context_create( $options_rc );
		$comp_resp=file_get_contents( $url_rc, false, $context );

		return $this->décoder_réponse( $comp_resp );
	}

	private function décoder_réponse( $réponse ){
		$infos_réponse=array();
		
		$infos_réponse[ "cont_id" ]=trim( json_decode( $réponse, true )[ 'cont_id' ] );
		$infos_réponse[ "cont_ip" ]=trim( json_decode( $réponse, true )[ 'add_ip' ] );
		$infos_réponse[ "cont_port" ]=trim( json_decode( $réponse, true )[ 'add_port' ] );
		$infos_réponse[ "res_validation" ]=trim( json_decode( $réponse, true )[ 'resultat' ] );

		return $infos_réponse;
	}

	private function get_url_compilebox(){
		return "http://".$GLOBALS[ 'config' ][ 'compilebox_hote' ].":".$GLOBALS[ 'config' ][ 'compilebox_port' ]."/compile"; //TODO changer?
	}

	private function get_options_compilebox(){
		$data_rc = $this->get_data_rc();
		
		$options_rc=array( 'http'=> array(
			'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
			'method'  => 'POST',
			'content' => http_build_query( $data_rc )) );

		return $options_rc;
	}

	protected function get_data_rc(){
		if( $this->avancement->etat==Question::ETAT_DEBUT ){
			$data_rc=$this->get_data_nouveau_conteneur();
		}
		else{
			$data_rc=$this->get_data_conteneur();
		}

		return $data_rc;
	}
	
	protected function get_data_nouveau_conteneur( ){
		return array( 'language' => 13,
					  'code' => 'reset',
					  'vm_name' => $this->question->image,
					  'parameters' => $this->avancement->conteneur,
					  'params_conteneur' => "-e SIAB_SERVICE=/:" . $this->question->user . ":" . $this->question->user . ":HOME:SHELL" ,
					  'stdin' => '',
					  'user' => $this->question->user );
	}

	private function get_data_conteneur(){
		return array( 'language' => 13,
					  'code' => $this->question->verification,
					  'vm_name' => $this->question->image,
					  'parameters' => $this->avancement->conteneur,
					  'params_conteneur' => "-e SIAB_SERVICE=/:" . $this->question->user . ":" . $this->question->user . ":HOME:SHELL" ,
					  'stdin' => '',
					  'user' => $this->question->user );
	}

}

?>