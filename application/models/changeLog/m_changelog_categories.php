<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class m_changelog_categories extends CI_Model {
    
     public function __construct() {
        parent::__construct();
        date_default_timezone_set('Europe/Paris');
        setlocale(LC_TIME, 'fr', 'fr_FR', 'fr_FR.ISO8859-1');
        $this->load->helper('fonctions_helper');
		 
    }
    
    // return les catégories
    public function get_categories() {
        $this->db->select('*');
        $this->db->from('categorie');
        $query = $this->db->get();

        return $query->result();
    }

    public function get_lib_categories() {
        $this->db->select('lib_categorie');
        $this->db->from('categorie');
        $query = $this->db->get();

        return $query->result_array();
    }

    
}   
?>