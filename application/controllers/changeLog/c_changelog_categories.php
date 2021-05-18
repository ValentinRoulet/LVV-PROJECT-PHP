<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class c_changelog_categories extends C_utilitaire {

    private $champs_categories = array(
        'Nom' => 'lib_categorie',
        'Couleur' => 'hexColorCode'

    );

    private $champs_categories_insert = array(
        'Nom' => 'lib_categorie',
        'Couleur' => 'hexColorCode'
    );

    public function __construct() {
		parent::__construct();
		date_default_timezone_set( 'Europe/Paris' );
		setlocale( LC_TIME, 'fr', 'fr_FR', 'fr_FR.ISO8859-1' );
        $this->dir_controlleur = 'changeLog/c_changelog_categories';
        $this->load->model('changeLog/m_changelog_categories');
	}

    function index($action){
        
        $crud = new grocery_CRUD();
        $crud->set_theme('datatables');
        $crud->set_language('french');
        $crud->set_table('categorie');
        $crud->unset_bootstrap();
        $crud->unset_read();
        $crud->unset_jquery();
        $crud->columns($this->champs_categories);

        $crud->add_action('Modifier', '', $this->dir_controlleur . '/index/list/edit', 'fa fa-pencil-square-o');

        $crud->callback_add_field('state',function () {
            return '<input type="colorpicker">';
        });

        $crud->field_type('id_categorie','invisible');
        $crud->field_type('hexColorCode','colorpicker');

        $crud->callback_add_field('hexColorCode', function () {
            return '<input type="color" value="" name="hexColorCode">';
        });

        $crud->callback_edit_field('hexColorCode', function ($fieldValue) {
            return '<input type="color" value="'. $fieldValue .'" name="hexColorCode">';
        });

        // On modifie l'affichage de chaque champ par son surnom
        foreach ($this->champs_categories as $cle => $item) {
            $crud->display_as($item, $cle);
        }
        
        // Génération du CRUD
        $data['output'] = $crud->render();
        // On peuple la variable data pour charger les bons script/css
        $data['scripts'] = array('jquery2', 'bootstrap', 'lte', 'datatables', 'datepicker', 'sweetalert', 'colorpicker');

        // Creation du bandeau
        $data['titre'] = array("Liste fratrie", "fa fa-database");
        if ($action == "consult") {
            $data['boutons'] = array();
        } else {
            $data['boutons'] = array(
                array("Retour", "fa fa-arrow-left", "changeLog/c_changelog_posts", null),
                array("Rafraichir", "fas fa-sync", $this->dir_controlleur . "/index/list/", null),  
                array("Ajouter", "fa fa-plus", $this->dir_controlleur . "/index/add/" , null)
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
        $this->load->view("changeLog/liste_categories", $data['output']);
        $this->load->view('template/footer_scripts', $data);
        $this->load->view('template/footer_html_base');
    }
}