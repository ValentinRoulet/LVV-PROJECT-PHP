<?php

class m_messagerie extends CI_Model
{

    public function get_id_envoi($data)
    {
        $this->db->select('DISTINCT(IDUserEnvoie)');
        $this->db->from('message_user');
        $this->db->where(array('id_post' => $data['idData']));
        $query = $this->db->get();

        return $query->result();
    }

    public function get_message_profil($data)
    {

        $sql = "SELECT message_id, message_text, message_date FROM message_user, message WHERE MessageUser = message_id AND (IDUserRecu = 1 AND IDUserEnvoie = 2) or (IDUserRecu = 2 AND IDUserEnvoie = 1)";
        $query = $this->db->query($sql, array(
            'id' => $data['userInfo'],
            'id' => $data['userInfo'],
            'id' => $data['userInfo'],
            'id' => $data['userInfo']
        ));
        return $query->result();
    } //SELECT  message_id, message_text, message_date  FROM MU.message_user, M.message WHERE MessageUser = message_id and (IDUserRecu = 2 AND IDUserEnvoie = 1) or (IDUserRecu = 1 AND IDUserEnvoie = 2)


    public function get_all_message()
    {
        $this->db->select('message_id','message_text','message_date');
        $this->db->from('message_user','message');
        $this->db->where(array('user_id' => $_SESSION['dataUser']));
        $query = $this->db->get();
        return $query->result();
    }

    public function get_roles()
    {
        $this->db->select('statut_id');
        $this->db->from('statut');
        $query = $this->db->get();
        return $query->result();
    }




    public function get_chat_messages($chat_id, $last_chat_message_id = 0){


        $query_str = "SELECT cm.chat_message_id, cm.user_id, cm.chat_message_content, DATE_FORMAT(cm.date_created, '%D of %M %Y at %H:%i:%s') AS chat_message_timestamp, u.username FROM chat_messages cm JOIN users u ON cm.user_id = u.user_id WHERE cm.chat_id = ? and cm.chat_message_id > ? ORDER BY cm.chat_message_id ASC";
        
        $result = $this->db->query($query_str, array($chat_id, $last_chat_message_id));
        
        return $result;
    }
    


}

?>