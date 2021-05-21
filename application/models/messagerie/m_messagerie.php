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

    public function get_message($data)
    {
        $this->db->select('message_id','message_text','message_date');
        $this->db->from('message_user','message');
        $this->db->where(array('id_post' => $data['idData']));

    SELECT  message_id, message_text, message_date  FROM MU.message_user, M.message WHERE MessageUser = message_id and (IDUserRecu = 2 AND IDUserEnvoie = 1) or (IDUserRecu = 1 AND IDUserEnvoie = 2)


    // return les posts
    public function get_posts() {
        $this->db->select('*');
        $this->db->from('changeLog');
        $this->db->order_by("date_post", "DESC");
        $query = $this->db->get();

        return $query->result();
    }

    //On ajoute le post dans la base de donnée
    public function add_posts($data){
        $Data = array(
            'id_categorie_post' => $data['postData']['categorie'],
            'titre_post' => $data['postData']['titre'],
            'message_post' => $data['postData']['message'],
            'date_post' => $data['postData']['date']
        );
        $this->db->insert('changeLog',$Data);
        
    }

    //Supprime un post grâce à son id
    public function supp_posts($data){
        
        $this->db->where('id_post', $data['idData']);
        $this->db->delete('changeLog');
        
    }
    
    //Fonction pour avoir la categorie
    public function get_categorie($data){
        $this->db->select('id_categorie_post');
        $this->db->from('changeLog');
        $this->db->where(array('id_post' => $data['idData']));
        $query = $this->db->get();
        return $query->result();
    }

    //Fonction pour avoir le titre
    public function get_titre($data){
        
        $this->db->select('titre_post');
        $this->db->from('changeLog');
        $this->db->where(array('id_post' => $data['idData']));
        $query = $this->db->get();
        return $query->result();
    }

        //Fonction pour avoir le message
        public function get_message($data){
        $this->db->select('message_post');
        $this->db->from('changeLog');
        $this->db->where(array('id_post' => $data['idData']));
        $query = $this->db->get();
        return $query->result();
    }

    //Fonction pour avoir la date
    public function get_date($data){
        $this->db->select('date_post');
        $this->db->from('changeLog');
        $this->db->where(array('id_post' => $data['idData']));
        $query = $this->db->get();
        return $query->result();
    }
        
    //Fonction pour update le post dans la base de donnée
    public function update_post($data){

        $Data = array(
            'id_categorie_post' => $data['postData']['categorie'],
            'titre_post' => $data['postData']['titre'],
            'message_post' => $data['postData']['message'],
            'date_post' => $data['postData']['date']
        );
    
        $this->db->where('id_post', $data['idData']);
        $this->db->update('changeLog', $Data);
    }
}

?>