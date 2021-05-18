<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class m_totp_login extends CI_Model {
    
     public function __construct() {
        parent::__construct();
        date_default_timezone_set('Europe/Paris');
        setlocale(LC_TIME, 'fr', 'fr_FR', 'fr_FR.ISO8859-1');
		 
    }
    
    public function login_user($data) {

        $this->db->select('*');
        $this->db->from('users');
        $this->db->where('user_mail',$data['output']['login']);
        $this->db->where('user_password',$data['output']['password']);
        $query = $this->db->get();
        //$this->db->last_query();
        return $query->result();
    }

    public function get_data_user($data) {

        $this->db->select('*');
        $this->db->from('users');
        $this->db->where('user_mail',$data['login']);
        $query = $this->db->get();
        //$this->db->last_query();
        return $query->result();
    }

	
   
}   
?>