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
                "estSimple" => $this->m_totp_login->get_data_user($_SESSION['dataUser'][0]->user_id)[0]->totp_key == null,
                "id" => $_SESSION['dataUser'][0]->user_id
            );
            $data['boutons'] = array(
                array("Rafraichir", "fas fa-sync", $this->dir_controlleur, null),
                array("Déconnexion", "fas fa-sync", $this->dir_login, null),
                array("Retour", "fas fa-sync", $this->dir_retour, null),
            );
        }

        //données envoyé à la vue
        $data['message'] = array(
            "id_user" => $this->m_totp_login->get_data_user($_SESSION['dataUser'][0]->user_id)[0]->user_id,
            "id_expedi" => $this->m_totp_login->get_data_user($_SESSION['dataUser'][0]->user_id)[0]->user_nom,
            //"messages" => $this->m_messagerie->get_all_message($_SESSION['dataUser'][0]->user_id)[0]->user_id,
            "vu" => $this->m_totp_login->get_data_user($_SESSION['dataUser'][0]->user_id)[0]->totp_key == null,
            "role" => $this->m_messagerie->get_roles()
        );

        //var_dump($_SESSION['dataUser']);
        var_dump($data['userInfo']['id']);

        //var_dump($this->m_messagerie->get_message_profil($data));

        //var_dump($_SESSION['dataUser']);

        
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


    public function ajax_get_chat_messages(){
        $chat_id = $this->input->post('chat_id');
        $recipient = $this->input->post('recipient');
        
        if (!$recipient){
        echo $this->_get_chat_messages($chat_id);
        }
        }
        
        function _get_chat_messages($chat_id){
        
        $last_chat_message_id = (int)$this->session->userdata('last_chat_message_id_' . $chat_id);
        
        $chat_messages = $this->Chat_model->get_chat_messages($chat_id, $last_chat_message_id);
        
        if ($chat_messages->num_rows() > 0)
        {
        
        //store last chat message id
        $last_chat_message_id = $chat_messages->row($chat_messages->num_rows() - 1)->chat_message_id;
        $this->session->set_userdata('last_chat_message_id_' . $chat_id, $last_chat_message_id);
        $chat_messages_html = '<ul>';
        foreach($chat_messages->result() as $chat_message)
        {
        
        $li_class = ($this->session->userdata('user_id') == $chat_message->user_id) ? 'class="by_current_user"' : '';
        
        $chat_messages_html .='<li ' . $li_class. '>' . '<span class="chat_message_header">' . $chat_message->chat_message_timestamp . ' by ' . $chat_message->username . '</span><p class="message_content">' .$chat_message->chat_message_content . '</p></li>';
        }
        
        $chat_messages_html .='</ul>';
        
        
        
        $result = array('status' =>'ok', 'content'=>$chat_messages_html);
        
        
        return json_encode($result);
        
        }
        else
        {
        $result = array('status' =>'ok', 'content'=>'');
        //print_r($result);
        
        return json_encode($result);
        
        exit();
        
        }
        
        
        }









}

    

