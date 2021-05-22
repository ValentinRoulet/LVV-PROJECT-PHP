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
        $this->load->model('messagerie/m_messagerie');
        $this->load->model('totp/m_totp_login');
        

	}

    function index(){


        // On peuple la variable data pour charger les bons script/css
        $data['scripts'] = array('jquery', 'bootstrap', 'lte', 'datatables', 'datepicker', 'sweetalert','cssMessagerie' );

        // Creation du bandeau
        $data['titre'] = array("Menu", "fa fa-shower");
        


        // si la session est null c'est que l'utilisateur n'est pas connecté donc retour à la page de login
        if($_SESSION['dataUser'] == null)
        {
            redirect($this->dir_login);
        }
        else
        {
            //Permet de récupérer l'id de l'utilisateur de session
            $data['userId']=$_SESSION['dataUser'][0]->user_id;
            //Permet de créer les boutons dans le menu en header
            $data['boutons'] = array(
                array("Rafraichir", "fas fa-sync", $this->dir_controlleur, null),
                array("Déconnexion", "fas fa-sync", $this->dir_login, null),
                array("Retour", "fas fa-sync", $this->dir_retour, null),
            );
        }

        //permet de récupérer tout les messages entre deux personnes et de les ajouter dans un tableau
        $data['conv1'] = $this->m_messagerie->get_message_profil_envoyeur($data['userId'],3);
        $data['conv2'] = $this->m_messagerie->get_message_profil_envoyeur1($data['userId'],3);
        $data['conv'] = array_merge($data['conv1'], $data['conv2'] );

        //Permet de récupérer les noms prénoms des deux utilisateurs
        $data['conv_name'] = $this->m_messagerie->get_name_user($data['userId']) ;
        $data['conv_name'] = $data['conv_name']->user_nom . " " . $data['conv_name']->user_prenom;
        if($data['conv'][0]->message_id_receveur == $data['userId'])
        {
            $data['conv_name1'] = $this->m_messagerie->get_name_user($data['conv'][0]->message_id_envoyeur);
            $data['conv_name1'] = $data['conv_name1']->user_nom . " " . $data['conv_name1']->user_prenom;
        } else
        {
            $data['conv_name1'] = $this->m_messagerie->get_name_user($data['conv'][0]->message_id_receveur);
            $data['conv_name1'] = $data['conv_name1']->user_nom . " " . $data['conv_name1']->user_prenom;
        }
        

        //Permet de trier tous les utilisateurs à qui on a parler du plus récent au plus ancien
        $data['profils_envoyeur'] = $this->m_messagerie->get_id_profil_envoyeur($data['userId']);
        $x = 0;
        foreach ( $data['profils_envoyeur'] as $value) 
        {
            $data['profils_envoyeur_name'][$x] = $this->m_messagerie->get_name_user($data['profils_envoyeur'][$x]->message_id_envoyeur);
            $x = $x + 1;
        }
        
        
        // On charge les differents modules neccessaires a l'affichage d'une page
        //$this->load->view('template/header_html_base', $data);
        $this->load->view('template/header_scripts', $data); 
        $this->load->view('template/bandeau', $data);
        $this->load->view('template/footer_scripts', $data);
        $this->load->view('template/footer_html_base');
        $this->load->view('messagerie/editeur',$data);
    }









}

    

