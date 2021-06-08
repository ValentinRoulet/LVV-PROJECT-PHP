<?php

class m_messagerie extends CI_Model
{


    //Permet de récupérer toute une conversation
    public function get_message_profil_envoyeur($data,$data1)
    {
        $sql = " 
        SELECT message_id, message_date, message_text, message_id_receveur, message_id_envoyeur 
        FROM message m, users u 
        WHERE m.message_id_envoyeur = u.user_id 
        AND (m.message_id_envoyeur = ? AND m.message_id_receveur = ?) OR (m.message_id_envoyeur = ? AND m.message_id_receveur = ?)
        GROUP BY m.message_date
        ORDER BY m.message_date ASC
        ";
        $query = $this->db->query($sql, array($data,$data1,$data1,$data));
        return $query->result();
    }

    //Permet de récupérer le dernier message d'une conv
    public function get_last_message_profil($data,$data1)
    {
        $sql = " 
        SELECT message_id, message_date, message_text, message_id_receveur, message_id_envoyeur 
        FROM message m, users u 
        WHERE m.message_id_envoyeur = u.user_id 
        AND (m.message_id_envoyeur = ? AND m.message_id_receveur = ?) OR (m.message_id_envoyeur = ? AND m.message_id_receveur = ?)
        GROUP BY m.message_date
        ORDER BY m.message_date desc
        LIMIT 1
        
        ";
        $query = $this->db->query($sql, array($data,$data1,$data1,$data));
        return $query->row();
    }

    //permet de récupérer le dernier message de tout les utilisateur a qui on a parler
    public function get_id_profil_envoyeur($data)
    {
        $sql = " 
        SELECT message_id_envoyeur, message_text
        FROM message m, users u 
        WHERE m.message_id_envoyeur = u.user_id AND  m.message_id_receveur = ?
        GROUP BY message_id_envoyeur
        ORDER BY message_date DESC 
        ";
        $query = $this->db->query($sql, array($data));
        return $query->result();
    } 

    //Je sais pas si c'est fonctionelle
    public function get_roles()
    {
        $this->db->select('statut_id');
        $this->db->from('statut');
        $query = $this->db->get();
        return $query->result();
    }

    //Permet de récupérer dans un tableau le nom et prénom d'un utilisateur en fonction de son id
    public function get_name_user($data)
    {
        $sql = " 
        SELECT user_nom, user_prenom
        FROM users 
        WHERE user_id = ?
        ";
        $query = $this->db->query($sql, array($data));
        return $query->row();
    }

    public function set_message($id_envoyeur,$id_receveur,$message,$date)
    {
        $sql = " 
        INSERT INTO message (message_id_envoyeur, message_id_receveur, message_text, message_date)
        VALUES (?, ?, ?, ?)
        ";
        $query = $this->db->query($sql, array($id_envoyeur,$id_receveur,$message,$date));
        
    }

    public function get_all_profil()
    {
        $sql = " 
        SELECT user_nom, user_prenom, user_id
        FROM users u 
        ORDER BY user_nom ASC
        ";
        $query = $this->db->query($sql);
        return $query->result();
    }
    
    



}

?>