<?php


if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class c_messagerie extends C_utilitaire {

    public function __construct() {
		parent::__construct();
		date_default_timezone_set( 'Europe/Paris' );
		setlocale( LC_TIME, 'fr', 'fr_FR', 'fr_FR.ISO8859-1' );
        $this->load->library('form_validation');
        $this->load->helper('form');
        $this->dir_controlleur = 'messagerie/c_messagerie';
        $this->dir_index = 'index/c_index';
        $this->dir_login = 'totp/c_totp_login';
        $this->dir_retour = 'totp/c_totp_login/mon_compte';
        $this->load->model('messagerie/m_messagerie');
        $this->load->model('totp/m_totp_login');
        

	}

    function index()
    {


        // On peuple la variable data pour charger les bons script/css
        $data['scripts'] = array('jquery', 'bootstrap', 'lte', 'datepicker','cssMessagerie' );

        // Creation du bandeau
        $data['titre'] = array("Messagerie", "fas fa-envelope");
        

        // si la session est null c'est que l'utilisateur n'est pas connecté donc retour à la page de login
        if(!isset($_SESSION['dataUser']))
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
                array("Déconnexion", "fas fa-sign-out-alt", $this->dir_login, null),
                array("Retour", "fa fa-arrow-left", $this->dir_retour, null),
            );
        }

        
        $data['ActiveConv'] = false;
        

        //Permet de trier tous les utilisateurs à qui on a parler du plus récent au plus ancien
        $data['profils_envoyeur'] = $this->m_messagerie->get_id_profil_envoyeur($data['userId']);
        //$data['profils_envoyeur2'] = $this->m_messagerie->get_id_profil_envoyeur2($data['userId']);
        //$data['profils_envoyeur'] = array_merge($data['profils_envoyeur1'],$data['profils_envoyeur2']);
        $x = 0;
        foreach ( $data['profils_envoyeur'] as $value) 
        {
            $data['last_message'][$x] = $this->m_messagerie->get_last_message_profil($data['userId'],$data['profils_envoyeur'][$x]->message_id_envoyeur);
            $data['profils_envoyeur_name'][$x] = $this->m_messagerie->get_name_user($data['profils_envoyeur'][$x]->message_id_envoyeur);
            $x = $x + 1;
        }

        //var_dump($data['profils_envoyeur']);
        //var_dump($data['last_message']);
        //var_dump($data['profils_envoyeur_name']);


        
        
        // On charge les differents modules neccessaires a l'affichage d'une page
        //$this->load->view('template/header_html_base', $data);
        $this->load->view('template/header_scripts', $data); 
        $this->load->view('template/bandeau', $data);
        //$this->load->view('template/footer_scripts', $data);
        $this->load->view('template/footer_html_base');
        $this->load->view('messagerie/editeur',$data);
    }


    function conversation()
    {
        // On peuple la variable data pour charger les bons script/css
        $data['scripts'] = array('jquery', 'bootstrap', 'lte', 'datepicker','cssMessagerie','tinyMCE' );

        // Creation du bandeau
        $data['titre'] = array("Messagerie", "fas fa-envelope");
        
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
                array("Déconnexion", "fas fa-sign-out-alt", $this->dir_login, null),
                array("Retour", "fa fa-arrow-left", $this->dir_retour, null),
            );
        }

        //permet de savoir si une conversation est en cours
        $data['ActiveConv'] = true;

        //Permet de trier tous les utilisateurs à qui on a parler du plus récent au plus ancien
        $data['profils_envoyeur'] = $this->m_messagerie->get_id_profil_envoyeur($data['userId']);
        $x = 0;
        foreach ( $data['profils_envoyeur'] as $value) 
        {
            $data['last_message'][$x] = $this->m_messagerie->get_last_message_profil($data['userId'],$data['profils_envoyeur'][$x]->message_id_envoyeur);
            $data['profils_envoyeur_name'][$x] = $this->m_messagerie->get_name_user($data['profils_envoyeur'][$x]->message_id_envoyeur);
            $x = $x + 1;
        }

        //on récupère l'id de la personne qui recoit
        $data['personne'] = $this->input->post('id_personne');

            
            if($data['personne'] == NULL )
            {
                $data['personne'] = $_SESSION['personne'];
            } else{
                $_SESSION['personne'] = $data['personne'];
            }
            
             //permet de récupérer tout les messages entre deux personnes et de les ajouter dans un tableau
            $data['conv'] = $this->m_messagerie->get_message_profil_envoyeur($data['userId'],$data['personne']);

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

            
        
         // On charge les differents modules neccessaires a l'affichage d'une page
         //$this->load->view('template/header_html_base', $data);
        $this->load->view('template/header_scripts', $data); 
        $this->load->view('template/bandeau', $data);
        $this->load->view('template/footer_scripts', $data);
        $this->load->view('template/footer_html_base');
        $this->load->view('messagerie/editeur',$data);
    }


    function envoyer()
    {

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

        //on mets le message, la date dans des variable puis on envoie tout dans la fonction set_message
        $data["message"] = $this->input->post('inputMessage');
        $data['date'] = date("Y-m-d H:i:s");  
        $this->m_messagerie->set_message($data['userId'],$_SESSION['personne'],$data['message'],$data['date']);
        redirect($this->dir_controlleur . "/conversation");

    }

    function nouveau()
    {
        // On peuple la variable data pour charger les bons script/css
        $data['scripts'] = array('jquery', 'bootstrap', 'lte', 'datepicker','cssMessagerie' );

        // Creation du bandeau
        $data['titre'] = array("Messagerie", "fas fa-envelope");

        
        

        // si la session est null c'est que l'utilisateur n'est pas connecté donc retour à la page de login
        if(!isset($_SESSION['dataUser']))
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
        
        $data['ActiveConv'] = false;
        

        //Permet de trier tous les utilisateurs à qui on a parler du plus récent au plus ancien
        $data['profils_envoyeur'] = $this->m_messagerie->get_id_profil_envoyeur($data['userId']);
        $x = 0;
        foreach ( $data['profils_envoyeur'] as $value) 
        {
            $data['last_message'][$x] = $this->m_messagerie->get_last_message_profil($data['userId'],$data['profils_envoyeur'][$x]->message_id_envoyeur);
            $data['profils_envoyeur_name'][$x] = $this->m_messagerie->get_name_user($data['profils_envoyeur'][$x]->message_id_envoyeur);
            $x = $x + 1;
        }

        $data['profils'] = $this->m_messagerie->get_all_profil();

        $this->form_validation->set_rules("inputMessage","inputMessage","required");
        if($this->form_validation->run()){
            $data['id_profils'] = $this->input->post('id_profils');
            $data["message"] = $this->input->post('inputMessage');
            $data['date'] = date("Y-m-d H:i:s");  
            $this->m_messagerie->set_message($data['userId'],$data['id_profils'],$data['message'],$data['date']);
            var_dump($data['id_profils']);
            var_dump($data["message"]);
            var_dump($data['date']);
            var_dump($data['userId']);
            //redirect($this->dir_controlleur);
        }
        

        // On charge les differents modules neccessaires a l'affichage d'une page
        $this->load->view('template/header_scripts', $data); 
        $this->load->view('template/bandeau', $data);
        $this->load->view('template/footer_scripts', $data);
        $this->load->view('template/footer_html_base');
        $this->load->view('messagerie/nouveau',$data);
    }


}

    

