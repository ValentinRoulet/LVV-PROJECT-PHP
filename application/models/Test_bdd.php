<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Test_bdd extends CI_Model{

    public function __construct(){
        parent::__construct();
    }

    public function get_name(){
        echo $this->db
                ->select('id,Nom,Prenom,pseudo')
                ->from('Users')
                ->get()
                ->first_row();
    }

    function insert_table_data($data){
        $this->db->insert("Users",$data);
    }

    function get_data_from_table(){
        $this->db->select('Users.id,Users.Nom,Users.Prenom,Users.age,Metier.LibMetier');
        $this->db->from('Users');
        $this->db->join('Metier', 'Metier.idMetier = Users.MetierUser');
        return $this->db->get();
    }

    function getMetier(){
        return $this->db->get('Metier');
    }
}

?>