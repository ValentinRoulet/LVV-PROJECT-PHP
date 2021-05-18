<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class m_liste_appareillage extends CI_Model {
    
     public function __construct() {
        parent::__construct();
        date_default_timezone_set('Europe/Paris');
        setlocale(LC_TIME, 'fr', 'fr_FR', 'fr_FR.ISO8859-1');
		 
    }
    
    public function listeAppareillage() {

            $this->db->select('DATE_FORMAT(appareillage_date_fin,"%d/%m/%Y") AS date_fin, enfant_nom, enfant_prenom, enfant_id, appareillage_id, liste_appareillage_libelle');
            $this->db->from('appareillage');
            $this->db->join('enfant', 'appareillage_enfant_id = enfant_id','left');
            $this->db->join('liste_appareillage', 'liste_appareillage_id = appareillage_liste_appareillage_id','left');
            $this->db->where('enfant_archive = ', '');
            $this->db->where('appareillage_date_fin <> ', '0000-00-00');
            //$this->db->where_in('enfant_service_id', explode(',',$_SESSION['services']));
            $this->db->order_by('appareillage_date_fin', 'desc');
        
        $query = $this->db->get();
        //$this->db->last_query();
        return $query->result();
    }
	
   
}   
?>