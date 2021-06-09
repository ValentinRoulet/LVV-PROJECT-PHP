<?php


if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class c_index extends C_utilitaire {

    public function __construct() {
		parent::__construct();
		date_default_timezone_set( 'Europe/Paris' );
		setlocale( LC_TIME, 'fr', 'fr_FR', 'fr_FR.ISO8859-1' );
        $this->dir_controlleur = 'index/c_index';
        $this->dir_login = 'totp/c_totp_login';
        $this->dir_compte = 'totp/c_totp_login/mon_compte';
        $this->load->model('fullcalendar/m_fullcalendar');
        $this->load->model('totp/m_totp_login');

	}

    function index(){

        // On peuple la variable data pour charger les bons script/css
        $data['scripts'] = array('jquery', 'bootstrap', 'lte', 'datatables', 'datepicker', 'sweetalert','animScroll','cssIndex', 'calendar');

        // Creation du bandeau
        $data['titre'] = array("", "");


        // si la session est null c'est que l'utilisateur n'est pas connecté donc retour à la page de login
        if(!isset($_SESSION['dataUser'])){
            $data['boutons'] = array(
                array("Rafraichir", "fas fa-sync", $this->dir_controlleur, null),
                array("Connexion", "fas fa-sign-in-alt", $this->dir_login, null),
            );
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
                array("Déconnexion", "fas fa-sign-out-alt", $this->dir_login, null),
                array("Mon compte", "fas fa-user-circle", $this->dir_compte, null),
                
            );
        }
        

        
        // On charge les differents modules neccessaires a l'affichage d'une page
        //$this->load->view('template/header_html_base', $data);
        $this->load->view('template/header_scripts', $data); 
        $this->load->view('template/bandeau', $data);
        $this->load->view('template/footer_scripts', $data);
        $this->load->view('template/footer_html_base');
        $this->load->view('index/index',$data);
    }

    //permet de récupérer les donnée de la base de donnée et de les affichier dans le script FullCalendar.js
    function load()
    {
        $event_data = $this->m_fullcalendar->fetch_all_event();
        foreach($event_data->result_array() as $row)
        {
            $data[] = array(
                'id' => $row['id'],
                'title' => $row['title'],
                'start' => $row['start_date'],
                'end' => $row['end_date']
            );
        }
        echo json_encode($data);
    }

    //modifie une date dans la base de donnée
    function update()
    {
        if($this->input->post('id'))
        {
            $data = array(
                'title'   => $this->input->post('title'),
                'start_date' => $this->input->post('start'),
                'end_date'  => $this->input->post('end')
            );

            $this->m_fullcalendar->update_event($data, $this->input->post('id'));
            
        }
    }

    //supprime une date de la base de donnée
    function delete()
    {
        if($this->input->post('id'))
        {
            $this->m_fullcalendar->delete_event($this->input->post('id'));
        }
    }

    //insertion d'une date dans la base de donnée
    function insert()
    {
        if($this->input->post('title'))
        {
            $data = array(
                'title'  => $this->input->post('title'),
                'start_date'=> $this->input->post('start'),
                'end_date'  => $this->input->post('start')
            );
            $this->m_fullcalendar->insert_event($data);
            
        }
    }

}



