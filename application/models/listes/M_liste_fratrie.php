<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class m_liste_fratrie extends CI_Model {
    
     public function __construct() {
        parent::__construct();
        date_default_timezone_set('Europe/Paris');
        setlocale(LC_TIME, 'fr', 'fr_FR', 'fr_FR.ISO8859-1');
		 
    }
    
    public function getDataFratrie($id) {

        $this->db->select('*');
        $this->db->from('enfant_fratrie');
        $this->db->where('enfant_fratrie_id', +$id);
        
        $query = $this->db->get();
        return $query->result();
    }
	
    public function addDataFratrie($array_data){
        $this->db->insert('enfant_fratrie',$array_data);
        $insert_id = $this->db->insert_id();
        return $insert_id;
    }
   
}   
?>