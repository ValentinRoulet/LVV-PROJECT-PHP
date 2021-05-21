<?php


if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class c_messagerie extends C_utilitaire {

    public function __construct() {
		parent::__construct();
		date_default_timezone_set( 'Europe/Paris' );
		setlocale( LC_TIME, 'fr', 'fr_FR', 'fr_FR.ISO8859-1' );
        $this->dir_controlleur = 'messagerie/c_messagerie';
        $this->dir_index = 'index/c_index';
        $this->dir_login = 'totp/c_totp_login';
        $this->dir_retour = 'totp/c_totp_login/mon_compte';
        $this->load->model('totp/m_totp_login');

	}

    function index(){

        // On peuple la variable data pour charger les bons script/css
        $data['scripts'] = array('jquery', 'bootstrap', 'lte', 'datatables', 'datepicker', 'sweetalert','cssIndex' );

        // Creation du bandeau
        $data['titre'] = array("Menu", "fa fa-shower");


        // si la session est null c'est que l'utilisateur n'est pas connecté donc retour à la page de login
        if($_SESSION['dataUser'] == null){
            redirect($this->dir_login);
        }
        else
        {
            //données envoyé à la vue
            $data['userInfo'] = array(
                "prenom" => $this->m_totp_login->get_data_user($_SESSION['dataUser'][0]->user_id)[0]->user_prenom,
                "nom" => $this->m_totp_login->get_data_user($_SESSION['dataUser'][0]->user_id)[0]->user_nom,
                "mail" => $this->m_totp_login->get_data_user($_SESSION['dataUser'][0]->user_id)[0]->user_mail,
                "estSimple" => $this->m_totp_login->get_data_user($_SESSION['dataUser'][0]->user_id)[0]->totp_key == null
            );
            $data['boutons'] = array(
                array("Rafraichir", "fas fa-sync", $this->dir_controlleur, null),
                array("Déconnexion", "fas fa-sync", $this->dir_login, null),
                array("Retour", "fas fa-sync", $this->dir_retour, null),
            );
        }
        

        
        // On charge les differents modules neccessaires a l'affichage d'une page
        //$this->load->view('template/header_html_base', $data);
        $this->load->view('template/header_scripts', $data); 
        $this->load->view('template/bandeau', $data);
        $this->load->view('template/footer_scripts', $data);
        $this->load->view('template/footer_html_base');
        $this->load->view('messagerie/editeur',$data);
    }

    

    function editeur()
    {
        
    }









}

    

