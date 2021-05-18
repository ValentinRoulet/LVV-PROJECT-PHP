<?php


use Otp\Otp;
use Otp\GoogleAuthenticator;
use ParagonIE\ConstantTime\Encoding;

require_once('./assets/plugins/otp/Otp.php');
require_once('./assets/plugins/otp/GoogleAuthenticator.php');
require_once('./assets/plugins/otp/OtpInterface.php');
require_once('./assets/plugins/Encoding/Encoding.php');
require_once('./assets/plugins/Encoding/Base32.php');

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class c_totp_login extends C_utilitaire {

    private $data;

    public function __construct() {
		parent::__construct();
		date_default_timezone_set( 'Europe/Paris' );
		setlocale( LC_TIME, 'fr', 'fr_FR', 'fr_FR.ISO8859-1' );
        $this->load->library('form_validation');
        $this->load->library('encryption');
        $this->load->model('totp/m_totp_login');
        $this->dir_controlleur = 'totp/c_totp_login';
	}

    function index(){
        $this->session->sess_destroy();
        $this->session;
        $this->load->library('form_validation');
        $this->load->helper('form');

        $this->form_validation->set_rules("login","login","required");
        $this->form_validation->set_rules("password","password","required");
        if($this->form_validation->run()){
            $data['output'] = array(
                'login'=>$this->input->post('login'),
                'password'=>$this->input->post('password')
            );
            if(count($this->m_totp_login->login_user($data)) > 0){
                $_SESSION['output'] = $this->m_totp_login->login_user($data);
                if($_SESSION['output'][0]->totp_key == null){
                    redirect(base_url() . 'index.php/'. $this->dir_controlleur .'/mon_compte');
                }
                else{
                    redirect(base_url() . 'index.php/'. $this->dir_controlleur .'/valider_totp');
                }
            }else{
                $_SESSION['output'] = "pas bon";
                redirect(base_url() . 'index.php/'. $this->dir_controlleur);
            }
        }

        // On peuple la variable data pour charger les bons script/css
        $data['scripts'] = array('jquery2', 'bootstrap', 'lte', 'datatables', 'datepicker', 'sweetalert');
        // Creation du bandeau
        $data['titre'] = array("Login", "");
        $data['boutons'] = array();
        $data['custom_script'] = '';

        //$_SESSION['output'] ='oui2';
        // On charge les differents modules neccessaires a l'affichage d'une page
        $this->load->view('template/header_html_base', $data);
        $this->load->view('template/header_scripts', $data);
        $this->load->view('template/bandeau', $data);
		$this->load->view("totp/login_page.php", $data);
        $this->load->view('template/footer_scripts', $data);
        $this->load->view('template/footer_html_base');
    }

    function mon_compte(){
        $_SESSION['dataUser'] = $this->m_totp_login->get_data_user(array("login" => $_SESSION['output'][0]->user_mail));

        // On peuple la variable data pour charger les bons script/css
        $data['scripts'] = array('jquery2', 'bootstrap', 'lte', 'datatables', 'datepicker', 'sweetalert');
        // Creation du bandeau
        $data['titre'] = array("Mon Compte", "");
        $data['boutons'] = array(array("Deconnexion", "fa fa-arrow-left", $this->dir_controlleur, null));
        $data['custom_script'] = '';

        //$_SESSION['output'] ='oui2';
        // On charge les differents modules neccessaires a l'affichage d'une page
        $this->load->view('template/header_html_base', $data);
        $this->load->view('template/header_scripts', $data);
        $this->load->view('template/bandeau', $data);
		$this->load->view("totp/mon_compte.php", $data);
        $this->load->view('template/footer_scripts', $data);
        $this->load->view('template/footer_html_base');
    }

    function valider_totp(){
        $_SESSION['dataUser'] = $this->m_totp_login->get_data_user(array("login" => $_SESSION['output'][0]->user_mail));
        $this->load->library('form_validation');
        $this->load->helper('form');

        $this->form_validation->set_rules("key","key","required");
        if($this->form_validation->run()){
            $inputKey = $this->input->post('key');

            $otp = new Otp();

            echo $_SESSION['dataUser'];

            $currentTotp = $otp->totp(Encoding::base32DecodeUpper($_SESSION['dataUser'][0]->totp_key));

            if($currentTotp == $inputKey){
                redirect(base_url() . 'index.php/'. $this->dir_controlleur .'/mon_compte');
            }else{
                redirect(base_url() . 'index.php/'. $this->dir_controlleur);
            }
        }

        // On peuple la variable data pour charger les bons script/css
        $data['scripts'] = array('jquery2', 'bootstrap', 'lte', 'datatables', 'datepicker', 'sweetalert');
        // Creation du bandeau
        $data['titre'] = array("Validation TOTP", "");
        $data['boutons'] = array();
        $data['custom_script'] = '';

        //$_SESSION['output'] ='oui2';
        // On charge les differents modules neccessaires a l'affichage d'une page
        $this->load->view('template/header_html_base', $data);
        $this->load->view('template/header_scripts', $data);
        $this->load->view('template/bandeau', $data);
		$this->load->view("totp/valider_totp.php", $data);
        $this->load->view('template/footer_scripts', $data);
        $this->load->view('template/footer_html_base');
    }

    function activate_totp(){

        $secret = GoogleAuthenticator::generateRandom();

        $qrCode = GoogleAuthenticator::getQrCodeUrl('totp', 'totpGPA', $secret, null, array("algoritm" => "SHA256", "digits" => '6', "period" => '30'));

        $data['totp'] = array( "secret" => $secret, "qrCode" => $qrCode);

        $this->m_totp_login->set_totp_key($secret, $_SESSION['output'][0]->user_id);
        // On peuple la variable data pour charger les bons script/css
        $data['scripts'] = array('jquery2', 'bootstrap', 'lte', 'datatables', 'datepicker', 'sweetalert');
        // Creation du bandeau
        $data['titre'] = array("Activer le totp", "");
        $data['boutons'] = array(
            array("Retour", "fa fa-arrow-left", $this->dir_controlleur . "/mon_compte", null)
        );
        $data['custom_script'] = '';

        //$_SESSION['output'] ='oui2';
        // On charge les differents modules neccessaires a l'affichage d'une page
        $this->load->view('template/header_html_base', $data);
        $this->load->view('template/header_scripts', $data);
        $this->load->view('template/bandeau', $data);
		$this->load->view("totp/activate_totp.php", $data);
        $this->load->view('template/footer_scripts', $data);
        $this->load->view('template/footer_html_base');
    }

    function desactiver_totp(){
        $this->m_totp_login->set_totp_key(null, $_SESSION['output'][0]->user_id);
        $this->mon_compte();
    }
}