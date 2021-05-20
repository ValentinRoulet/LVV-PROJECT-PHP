<?php

class m_fullcalendar extends CI_Model
{
    function fetch_all_event(){
        $this->db->order_by('id');
        return $this->db->get('EVENTS');
    }

    function insert_event($data)
    {
        $this->db->insert('EVENTS', $data);
    }

    function update_event($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update('EVENTS', $data);
    }

    function delete_event($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('EVENTS');
    }
}

?>