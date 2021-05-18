<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class c_changelog_posts extends C_utilitaire {

    public function __construct() {
		parent::__construct();
		date_default_timezone_set( 'Europe/Paris' );
		setlocale( LC_TIME, 'fr', 'fr_FR', 'fr_FR.ISO8859-1' );
        $this->dir_controlleur = 'changeLog/c_changelog_posts';
        $this->dir_Editer = 'changeLog/c_changelog_posts/Editer';
        $this->dir_Ajouter = 'changeLog/c_changelog_posts/Ajouter';
        $this->dir_index = 'index/c_index';
        $this->dir_compte = 'totp/c_totp_login/mon_compte';
        $this->load->model('changeLog/m_changelog_posts');
        $this->load->model('changeLog/m_changelog_categories');
	}

    function index(){
        // met les posts et les categories dans la variable data
        $data['posts'] = $this->m_changelog_posts->get_posts();
        $data['categories'] = $this->m_changelog_categories->get_categories();
        $data['role'] = $_SESSION['dataUser']['role'];
        
        
        // On peuple la variable data pour charger les bons script/css
        $data['scripts'] = array('jquery2', 'bootstrap', 'lte', 'datatables', 'datepicker', 'sweetalert');

        // Creation du bandeau
        $data['titre'] = array("ChangeLogs", "fa fa-shower");
        if ($data['role'] == "1"){
            $data['boutons'] = array(
                array("Retour", "fa fa-arrow-left", $this->dir_compte, null),
                array("Rafraichir", "fas fa-sync", $this->dir_controlleur, null),
                array("Ajouter", "fas fa-plus", $this->dir_Ajouter, null),
                array("Ajouter Categorie", "fas fa-plus", 'changeLog/c_changelog_categories/index/list/', null)
            );
        } else {
            $data['boutons'] = array(
                array("Retour", "fa fa-arrow-left", $this->dir_compte, null),
                array("Rafraichir", "fas fa-sync", $this->dir_controlleur, null)
            );
        };
        
        if ($data['role'] == "1"){
                                // Quand le bouton Supprimer est appuyé
            if($this->input->post('supp') != ''){
                $data['idData'] = $this->input->post('id_post');
                $this->m_changelog_posts->supp_posts($data);
                redirect($this-> dir_controlleur);
            }
            // Quand le bouton Éditer est appuyé
            else if($this->input->post('edit') != ''){
                $data['idData'] = $this->input->post('id_post');
                $_SESSION['IdData'] = $data['idData'];
                redirect(base_url() . 'index.php/'. $this->dir_controlleur .'/Editer');
                
                
            };
        }

        // On charge les differents modules neccessaires a l'affichage d'une page
        $this->load->view('template/header_html_base', $data);
        $this->load->view('template/header_scripts', $data);
        $this->load->view('template/bandeau', $data);
        if ($data['role'] == "1"){
            $this->load->view("changeLog/list_update_admin", $data);
        } else {
            $this->load->view("changeLog/list_update_visiteur", $data);
        };
        $this->load->view('template/footer_scripts', $data);
        $this->load->view('template/footer_html_base');
    }






    //Fonction du bouton EDITER
    public function Editer(){
        //Toutes les variables
        $data['Categ'] = $this->m_changelog_categories->get_categories();
        $data['idData'] = $_SESSION['IdData'];
        $data['Titre'] = $this->m_changelog_posts->get_titre($data);
        $data['Message'] = $this->m_changelog_posts->get_message($data);
        $data['Date'] = $this->m_changelog_posts->get_date($data);
        $data['categData'] = $this->m_changelog_posts->get_categorie($data);
        $data['url'] = 'index.php/changeLog/c_changelog_posts/Editer';
        $data['Date'] = '' . date('d-m-Y H:i', strtotime($this->m_changelog_posts->get_date($data)[0]->date_post));

        //Création du formulaire Editer
        $this->form_validation->set_rules("titre","Titre","required");
        $this->form_validation->set_rules("message","Message","required");
        if($this->form_validation->run()){
        //Ajoute les données du formulaire lorsqu'ont valide le formulaire
        $data['postData'] = array(
            'titre'=>$this->input->post('titre'),
            'message'=>$this->input->post('message'),
            'categorie' =>$this->input->post('categorie'),
            'date' =>$this->input->post('datetimes')
        );
        $data['postData']['date'] = date('Y-m-d H:i:s', strtotime($data['postData']['date']));
        //On update le post
        $this->m_changelog_posts->update_post($data);
        redirect($this-> dir_controlleur);
        }

        // On peuple la variable data pour charger les bons script/css
        $data['scripts'] = array('jquery2', 'bootstrap', 'lte', 'datatables', 'datepicker', 'sweetalert');

        // Creation du bandeau
        $data['titre'] = array("ChangeLogs", "fa fa-shower");

        $data['boutons'] = array(
            array("Rafraichir", "fas fa-sync", $this->dir_Editer, null),
            array("Retour", "fa fa-arrow-left", $this->dir_controlleur, null)
        );

        // On charge les differents modules neccessaires a l'affichage d'une page
        $this->load->view('template/header_html_base', $data);
        $this->load->view('template/header_scripts', $data);
        $this->load->view('template/bandeau', $data);
        $this->load->view('template/footer_scripts', $data);
        $this->load->view('template/footer_html_base');
        $this->load->view('changelog/formulaire',$data);
    }



    //Fonction du bouton AJOUTER
    public function Ajouter(){

        // Mets toutes les catégories dans la variable "Categ"
        $data['Categ'] = $this->m_changelog_categories->get_categories();
        $data['url'] = 'index.php/changeLog/c_changelog_posts/Ajouter';

        //Création du formulaire Ajouter
        $this->form_validation->set_rules("titre","Titre","required");
        $this->form_validation->set_rules("message","Message","required");
        if($this->form_validation->run()){
        //Ajoute les données du formulaire lorsqu'ont valide le formulaire
        $data['postData'] = array(
            'titre'=>$this->input->post('titre'),
            'message'=>$this->input->post('message'),
            'categorie' =>$this->input->post('categorie'),
            'date' =>$this->input->post('datetimes')
        );
        $data['postData']['date'] = date('Y-m-d H:i:s', strtotime($data['postData']['date']));
        //On ajoute le post
        $this->m_changelog_posts->add_posts($data);
        redirect($this-> dir_controlleur);
        }

        // On peuple la variable data pour charger les bons script/css
        $data['scripts'] = array('jquery2', 'bootstrap', 'lte', 'datatables', 'datepicker', 'sweetalert');

        // Creation du bandeau
        $data['titre'] = array("ChangeLogs", "fa fa-shower");

        $data['boutons'] = array(
            array("Rafraichir", "fas fa-sync", $this->dir_Ajouter, null),
            array("Retour", "fa fa-arrow-left", $this->dir_controlleur, null)
        );

        // On charge les differents modules neccessaires a l'affichage d'une page
        $this->load->view('template/header_html_base', $data);
        $this->load->view('template/header_scripts', $data);
        $this->load->view('template/bandeau', $data);
        $this->load->view('template/footer_scripts', $data);
        $this->load->view('template/footer_html_base');
        $this->load->view('changelog/formulaire',$data);
    }

}