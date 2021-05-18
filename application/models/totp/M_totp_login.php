<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class m_totp_login extends CI_Model {
    
     public function __construct() {
        parent::__construct();
        date_default_timezone_set('Europe/Paris');
        setlocale(LC_TIME, 'fr', 'fr_FR', 'fr_FR.ISO8859-1');
        $this->load->helper('fonctions_helper');
		 
    }
    
    // return un boolean si les données envoyé corresponde à un utilisateur
    public function login_user($data) {
        $this->db->select('*');
        $this->db->from('users');
        $this->db->where('user_mail',$data['output']['login']);
        $this->db->where('user_password',$data['output']['password']);
        $query = $this->db->get();

        return count($query->result())>0;
    }

    //return les données d'un utilisateur
    public function get_data_user($id) {

        $this->db->select('*');
        $this->db->from('users');
        $this->db->where('user_id',$id);
        $query = $this->db->get();
        return $query->result();
    }

    //return l'id d'un utilisateur
    public function get_id_user($data) {

        $this->db->select('user_id');
        $this->db->from('users');
        $this->db->where('user_mail',$data['output']['login']);
        $this->db->where('user_password',$data['output']['password']);
        $query = $this->db->get();
        return $query->result();
    }

    // rempli le champs totp_key d'un utilisateur dans la BDD
    public function set_totp_key($key, $userID){
        $this->db->update('users', array("totp_key" => $key), "user_id = " . $userID);
    }

    public function get_role($id){
        $this->db->select('id_role_user');
        $this->db->from('users');
        $this->db->where('user_id',$id);
        $query = $this->db->get();
        return $query->result(); 
    }
	
   
}   
?>