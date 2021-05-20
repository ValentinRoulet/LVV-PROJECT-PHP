<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class c_index extends C_utilitaire {

    public function __construct() {
		parent::__construct();
		date_default_timezone_set( 'Europe/Paris' );
		setlocale( LC_TIME, 'fr', 'fr_FR', 'fr_FR.ISO8859-1' );
        $this->dir_controlleur = 'index/c_index';
        $this->dir_controlleur2 = 'index/c_indedfx';
        $this->load->model('fullcalendar/m_fullcalendar');

	}

    function index(){
        // On peuple la variable data pour charger les bons script/css
        $data['scripts'] = array('jquery', 'bootstrap', 'lte', 'datatables', 'datepicker', 'sweetalert','animScroll','cssIndex', 'calendar');

        // Creation du bandeau
        $data['titre'] = array("Menu", "fa fa-shower");

        $data['boutons'] = array(
            array("Rafraichir", "fas fa-sync", $this->dir_controlleur, null),
        );

        
        // On charge les differents modules neccessaires a l'affichage d'une page
        //$this->load->view('template/header_html_base', $data);
        $this->load->view('template/header_scripts', $data); 
        $this->load->view('template/bandeau', $data);
        $this->load->view('template/footer_scripts', $data);
        $this->load->view('template/footer_html_base');
        $this->load->view('index/index',$data);
    }

    function load()
    {
        $event_data = $this->m_fullcalendar->fetch_all_event();
        foreach($event_data->result_array() as $row)
        {
            $data[] = array(
                'id' => $row['id'],
                'title' => $row['title'],
                'start' => $row['start_date'],
                'end' => $row['end_date']
            );
        }
        echo json_encode($data);
    }

    function update()
    {
        if($this->input->post('id'))
        {
            $data = array(
                'title'   => $this->input->post('title'),
                'start_date' => $this->input->post('start'),
                'end_date'  => $this->input->post('end')
            );

            $this->m_fullcalendar->update_event($data, $this->input->post('id'));
            
        }
    }

    function delete()
    {
        if($this->input->post('id'))
        {
            $this->m_fullcalendar->delete_event($this->input->post('id'));
        }
    }

    function insert()
    {
        if($this->input->post('title'))
        {
            $data = array(
                'title'  => $this->input->post('title'),
                'start_date'=> $this->input->post('start'),
                'end_date'  => $this->input->post('start')
            );
            $this->m_fullcalendar->insert_event($data);
            
        }
    }

}



