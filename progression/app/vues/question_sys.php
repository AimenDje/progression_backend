<?php

page_header();
page_contenu();

function page_contenu(){
    $infos=get_infos();
    
    $réponse_serveur=connexion_conteneur($infos);
    $infos=array_merge($infos, décoder_réponse($réponse_serveur));
    
    if(isset($_POST['submit'])){
        $infos['essayé']="true";
        $infos=array_merge($infos, vérifier_réussite($infos));
    }

    sauvegarder_conteneur($infos);
    
    render_page($infos);
}

function get_infos(){
    $question=charger_question_ou_terminer();
    $avancement=charger_avancement();

    $infos=array("réponse"=>get_réponse_utilisateur(),
                 "question"=>$question,
                 "avancement"=>$avancement,
                 "nom_serveur"=>$_SERVER["SERVER_NAME"],
                 "url_retour"=>"index.php?p=serie&ID=".$question->serieID,
                 "titre_retour"=>"la liste de questions");

    return $infos;
}

function charger_question_ou_terminer(){
    $question=new QuestionSysteme($_GET['ID']);

    if(is_null($question->id)){
        header('Location: index.php?p=accueil');
    }

    return $question;
}

function charger_avancement(){
    $avancement=new Avancement($_GET['ID'], $_SESSION['user_id']);

    return $avancement;
}

function get_réponse_utilisateur(){
    return isset($_POST['reponse'])?$_POST['reponse']:"";
}

function connexion_conteneur($infos){
    $url_rc=get_url_compilebox();
    $options_rc=get_options_compilebox($infos["question"], $infos["avancement"]);

    $context=stream_context_create($options_rc);
    $comp_resp=file_get_contents($url_rc, false, $context);

    return $comp_resp;
}

function get_url_compilebox(){
    return "http://".$GLOBALS['config']['compilebox_hote'].":".$GLOBALS['config']['compilebox_port']."/compile";
}

function get_options_compilebox($question, $avancement){
    if(isset($_POST['reset']) && $_POST['reset']=='Réinitialiser'){
        $data_rc=get_data_nouveau_conteneur($question, $avancement);
    }
    else{
        $data_rc=get_data_conteneur($question, $avancement);
    }
    
    $options_rc=array('http'=> array(
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query($data_rc)));

    return $options_rc;
}

function get_data_nouveau_conteneur($question, $avancement){
    return array('language' => 13, 'code' => 'reset', 'vm_name' => $question->image, 'parameters' => $avancement->conteneur, 'stdin' => '', 'user' => $question->user );
}

function get_data_conteneur($question, $avancement){
    return array('language' => 13, 'code' => $question->verification, 'vm_name' => $question->image, 'parameters' => $avancement->conteneur, 'stdin' => '', 'user' => $question->user);
}

function décoder_réponse($réponse){
    $infos_réponse=array();
    
    $infos_réponse["cont_id"]=trim(json_decode($réponse, true)['cont_id']);
    $infos_réponse["cont_ip"]=trim(json_decode($réponse, true)['add_ip']);
    $infos_réponse["cont_port"]=trim(json_decode($réponse, true)['add_port']);
    $infos_réponse["res_validation"]=trim(json_decode($réponse, true)['resultat']);

    return $infos_réponse;
}

function vérifier_réussite($infos){
    $réussite=array();
    
    $réussi=vérifier_réponse($infos);
    if($réussi){
        $réussite["réussi"]="true";
        $infos["avancement"]->set_etat(Question::ETAT_REUSSI);
    }

    //récupère l'état d'avancement
    if($infos["avancement"]->get_etat()==Question::ETAT_REUSSI){
        $réussite["état_réussi"]="true";
    }
    else{
        $réussite["état_réussi"]="";
    }
    
    return $réussite;
}

function sauvegarder_conteneur($infos){
    $infos["avancement"]->set_conteneur($infos["cont_id"]);
}

function vérifier_réponse($infos){
    $réussi=false;
    
    //Vérifie la réponse
    if(!is_null($infos["question"]->reponse) && $infos["question"]->reponse!=""){
        if($infos['réponse']!='')
            if($infos['réponse']==$infos["question"]->reponse){
                $réussi=true;
            }
    }
    elseif($infos['res_validation']!="" && $infos['res_validation']=="valide"){
            $réussi=true;            
    }
    return $réussi;
}

function render_page($infos){
    $template=$GLOBALS['mustache']->loadTemplate("question_sys");
    echo $template->render($infos);
}

?>