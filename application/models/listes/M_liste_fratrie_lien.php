<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class m_liste_fratrie_lien extends CI_Model {
    
     public function __construct() {
        parent::__construct();
        date_default_timezone_set('Europe/Paris');
        setlocale(LC_TIME, 'fr', 'fr_FR', 'fr_FR.ISO8859-1');
		 
    }
	
    public function addDataFratrieLien($array_data){
        $this->db->insert('enfant_fratrie_lien',$array_data);
    }
   
}   
?>