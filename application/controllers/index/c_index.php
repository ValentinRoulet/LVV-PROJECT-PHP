<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class c_index extends C_utilitaire {

    public function __construct() {
		parent::__construct();
		date_default_timezone_set( 'Europe/Paris' );
		setlocale( LC_TIME, 'fr', 'fr_FR', 'fr_FR.ISO8859-1' );
        $this->dir_controlleur = 'index/c_index';
        $this->dir_controlleur2 = 'index/c_indedfx';

	}

    function index(){
        // On peuple la variable data pour charger les bons script/css
        $data['scripts'] = array('jquery', 'bootstrap', 'lte', 'datatables', 'datepicker', 'sweetalert','animScroll','cssIndex', 'calendar');

        // Creation du bandeau
        $data['titre'] = array("Menu", "fa fa-shower");

        $data['boutons'] = array(
            array("Rafraichir", "fas fa-sync", $this->dir_controlleur, null),
        );


        // Quand le bouton changelog est appuyé
        
        if($this->input->POST('oui') != ''){
            echo "oui" ;
            redirect($this-> dir_controlleur2);
        }
        
        // Quand le bouton totp est appuyé
        if($this->input->post('totp') != ''){
            redirect($this-> dir_totp);
        }
        // Quand le bouton fratrie est appuyé
        if($this->input->post('fratrie') != ''){
            redirect($this-> dir_fratrie);
        }

        //On prends les events de la base de donnée
        $data['result'] = $this->db->get("EVENTS")->result();

        foreach ($data['result'] as $key => $value) {
            $data['data'][$key]['title'] = $value->title;
            $data['data'][$key]['start'] = $value->start_date;
            $data['data'][$key]['end'] = $value->end_date;
            $data['data'][$key]['backgroundColor'] = "#00a65a";
        }

        
        // On charge les differents modules neccessaires a l'affichage d'une page
        //$this->load->view('template/header_html_base', $data);
        $this->load->view('template/header_scripts', $data); 
        $this->load->view('template/bandeau', $data);
        $this->load->view('template/footer_scripts', $data);
        $this->load->view('template/footer_html_base');
        $this->load->view('index/index',$data);
    }


}