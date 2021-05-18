<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class c_liste_enfant extends C_utilitaire {

    private $champs_enfant = array(
        'Dossier' => 'enfant_id',
        'Nom' => 'enfant_nom',
        'Prenom' => 'enfant_prenom',
        'Date de naissance' => 'enfant_date_naiss'
    );

    public function __construct() {
		parent::__construct();
		date_default_timezone_set( 'Europe/Paris' );
		setlocale( LC_TIME, 'fr', 'fr_FR', 'fr_FR.ISO8859-1' );
        $this->dir_controlleur = 'listes/c_liste_enfant';
	}

    function index($action){
        $crud = new grocery_CRUD();
        $crud->set_theme('datatables');
        $crud->set_language('french');
        $crud->set_table('enfant');
        $crud->unset_bootstrap();
        $crud->unset_read();
        $crud->unset_jquery();
        $crud->columns($this->champs_enfant);
        //$crud->fields($this->champs_ville_insert);
        
        $crud->add_action('Modifier', '', $this->dir_controlleur . '/index/list/edit', 'fa fa-pencil-square-o');    
        $crud->add_action('Suppr dossier', '', $this->dir_controlleur . '/index/list/delete', 'fa fa-trash suppr');    
        $crud->add_action(' Fratrie', '', 'listes/c_liste_fratrie/index/list', 'fa fa-child');

        // On modifie l'affichage de chaque champ par son surnom
        foreach ($this->champs_enfant as $cle => $item) {
            $crud->display_as($item, $cle);
        }
        // Génération du CRUD
        $data['output'] = $crud->render();
        // On peuple la variable data pour charger les bons script/css
        $data['scripts'] = array('jquery2', 'bootstrap', 'lte', 'datatables', 'datepicker', 'sweetalert');
        // Creation du bandeau
        $data['titre'] = array("Liste enfant", "fa fa-database");
        if ($action == "consult") {
            $data['boutons'] = array();
        } else {
            $data['boutons'] = array(
                array("Rafraichir", "fas fa-sync", $this->dir_controlleur . "/index/list", null),
                //array("Ajouter un dossier", "fa fa-plus", $this->dir_controlleur . "/index/add/", null)
            );
        }
        $data['custom_script'] = '
                <script>
                    $(\'span.ui-button-text:contains("Supprimer")\').remove();
                    $(\'span.ui-button-text:contains("Éditer")\').remove(); 
                    $(\'a:contains("Éditer")\').remove(); 
                    $(\'i.fa-pencil\').remove(); 
                </script>
        ';

        // On charge les differents modules neccessaires a l'affichage d'une page
        $this->load->view('template/header_html_base', $data);
        $this->load->view('template/header_scripts', $data);
        $this->load->view('template/bandeau', $data);
		$this->load->view( "listes/liste_enfant", $data['output']);
        $this->load->view('template/footer_scripts', $data);
        $this->load->view('template/footer_html_base');
    }
}