<?php
//Commentaire

use Otp\Otp;
use Otp\GoogleAuthenticator;
use ParagonIE\ConstantTime\Encoding;

require_once('./assets/plugins/otp/Otp.php');
require_once('./assets/plugins/otp/GoogleAuthenticator.php');
require_once('./assets/plugins/otp/OtpInterface.php');
require_once('./assets/plugins/Encoding/Encoding.php');
require_once('./assets/plugins/Encoding/Base32.php');
require_once('./assets/plugins/phpqrcode/qrlib.php');

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class c_totp_login extends C_utilitaire {

    private $data;

    public function __construct() {
		parent::__construct();
		date_default_timezone_set( 'Europe/Paris' );
		setlocale( LC_TIME, 'fr', 'fr_FR', 'fr_FR.ISO8859-1' );
        $this->load->library('form_validation');
        $this->load->helper('form');
        $this->load->model('totp/m_totp_login');
        $this->load->helper('fonctions_helper');
        $this->dir_controlleur = 'totp/c_totp_login';
        $this->dir_changelog = 'changeLog/c_changelog_posts';

	}

    function index(){

        $_SESSION['dataUser'] = null;

        $_SESSION['MM_Username'] = 1;
        $_SESSION['SU'] = 'X';

        $this->form_validation->set_rules("login","login","required");
        $this->form_validation->set_rules("password","password","required");
        if($this->form_validation->run()){
            // met dans une array les inputs du formulaire
            $data['output'] = array(
                'login'=>$this->input->post('login'),
                'password'=>f_crypt($this->input->post('password'))
            );

            //envoie les données du formulaire à la fonction login_user du modèle totp_login
            if($this->m_totp_login->login_user($data)){
                // récupère l'id de l'utilisateur qui viens de se connecter et le place dans une variable de session
                $_SESSION['dataUser'] = $this->m_totp_login->get_id_user($data);

                //regarde si il possède ou pas la double auth et le redirige en fonction de ça
                if($this->m_totp_login->get_data_user($_SESSION['dataUser'][0]->user_id)[0]->totp_key == null){
                    redirect(base_url() . 'index.php/'. $this->dir_controlleur .'/mon_compte');
                }
                else{
                    redirect(base_url() . 'index.php/'. $this->dir_controlleur .'/valider_totp');
                }
            }else{
                redirect(base_url() . 'index.php/'. $this->dir_controlleur);
            }
        }

        // On peuple la variable data pour charger les bons script/css
        $data['scripts'] = array('jquery2', 'bootstrap', 'lte', 'datatables', 'datepicker', 'sweetalert');
        // Creation du bandeau
        $data['titre'] = array("Login", "");
        $data['boutons'] = array();
        $data['custom_script'] = '';

        // On charge les differents modules neccessaires a l'affichage d'une page
        $this->load->view('template/header_html_base', $data);
        $this->load->view('template/header_scripts', $data);
        $this->load->view('template/bandeau', $data);
		$this->load->view("totp/login_page.php", $data);
        $this->load->view('template/footer_scripts', $data);
        $this->load->view('template/footer_html_base');
    }


    function mon_compte(){
        // si la session est null c'est que l'utilisateur n'est pas connecté donc retour à la page de login
        if($_SESSION['dataUser'] == null){
            redirect(base_url() . 'index.php/'. $this->dir_controlleur);
        }

        //données envoyé à la vue
        $data['userInfo'] = array(
            "prenom" => $this->m_totp_login->get_data_user($_SESSION['dataUser'][0]->user_id)[0]->user_prenom,
            "nom" => $this->m_totp_login->get_data_user($_SESSION['dataUser'][0]->user_id)[0]->user_nom,
            "estSimple" => $this->m_totp_login->get_data_user($_SESSION['dataUser'][0]->user_id)[0]->totp_key == null
        );
        
        $_SESSION['dataUser']['role'] = $this->m_totp_login->get_role($_SESSION['dataUser'][0]->user_id)[0]->id_role_user;

        // On peuple la variable data pour charger les bons script/css
        $data['scripts'] = array('jquery2', 'bootstrap', 'lte', 'datatables', 'datepicker', 'sweetalert');
        // Creation du bandeau
        $data['titre'] = array("Mon Compte", "");
        $data['boutons'] = array(array("Deconnexion", "fa fa-arrow-left", $this->dir_controlleur, null),
                                array("ChangeLog", "fas fa-bacon", $this->dir_changelog, null));
        $data['custom_script'] = '';

        // On charge les differents modules neccessaires a l'affichage d'une page
        $this->load->view('template/header_html_base', $data);
        $this->load->view('template/header_scripts', $data);
        $this->load->view('template/bandeau', $data);
		$this->load->view("totp/mon_compte.php", $data);
        $this->load->view('template/footer_scripts', $data);
        $this->load->view('template/footer_html_base');
    }

    function valider_totp(){
        // si la session est null c'est que l'utilisateur n'est pas connecté donc retour à la page de login
        if($_SESSION['dataUser'] == null){
            redirect(base_url() . 'index.php/'. $this->dir_controlleur);
        }

        // validation form de la double authentification
        $this->form_validation->set_rules("key","key","required");
        if($this->form_validation->run()){
            $inputKey = $this->input->post('key');

            $otp = new Otp();

            // décode la clé secrete en fonction de la clé totp décrypté en BDD
            $currentTotp = $otp->totp(Encoding::base32DecodeUpper(f_decrypt($this->m_totp_login->get_data_user($_SESSION['dataUser'][0]->user_id)[0]->totp_key)));

            // compare l'input et la clé décodé en BDD
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

        // On charge les differents modules neccessaires a l'affichage d'une page
        $this->load->view('template/header_html_base', $data);
        $this->load->view('template/header_scripts', $data);
        $this->load->view('template/bandeau', $data);
		$this->load->view("totp/valider_totp.php", $data);
        $this->load->view('template/footer_scripts', $data);
        $this->load->view('template/footer_html_base');
    }

    function activate_totp(){
        // si la session est null c'est que l'utilisateur n'est pas connecté donc retour à la page de login
        if($_SESSION['dataUser'] == null){
            redirect(base_url() . 'index.php/'. $this->dir_controlleur);
        }

        // appel fonction qui crée la clé TOTP
        $secret = GoogleAuthenticator::generateRandom();

        // appel fonction qui retourne le string le l'URI du type otpauth
        $qrtext = GoogleAuthenticator::getKeyUri('totp', 'totpGPA', $secret, null, array("algoritm" => "SHA256", "digits" => '6', "period" => '30'));

        // Création du de l'image du qr code
        $SERVERFILEPATH = $_SERVER['DOCUMENT_ROOT'].'/assets/images/qrcodes/';
        $text = $qrtext;
        $text1= $_SESSION['dataUser'][0]->user_id;	
        $folder = $SERVERFILEPATH;
        $file_name1 = $text1."-Qrcode" . rand(2,200) . ".png";
        $file_name = $folder.$file_name1;
        QRcode::png($text,$file_name);
        $qrCode = base_url('/assets/images/qrcodes/'.$file_name1);

        $data['totp'] = array("qrCode" => $qrCode);

        // ajoute la clé TOTP crypté dans la BDD
        $this->m_totp_login->set_totp_key(f_crypt($secret), $_SESSION['dataUser'][0]->user_id);

        // On peuple la variable data pour charger les bons script/css
        $data['scripts'] = array('jquery2', 'bootstrap', 'lte', 'datatables', 'datepicker', 'sweetalert');
        // Creation du bandeau
        $data['titre'] = array("Activer le totp", "");
        $data['boutons'] = array(
            array("Retour", "fa fa-arrow-left", $this->dir_controlleur . "/mon_compte", null)
        );
        $data['custom_script'] = '';

        // On charge les differents modules neccessaires a l'affichage d'une page
        $this->load->view('template/header_html_base', $data);
        $this->load->view('template/header_scripts', $data);
        $this->load->view('template/bandeau', $data);
		$this->load->view("totp/activate_totp.php", $data);
        $this->load->view('template/footer_scripts', $data);
        $this->load->view('template/footer_html_base');
    }

    function desactiver_totp(){
        // si la session est null c'est que l'utilisateur n'est pas connecté donc retour à la page de login
        if($_SESSION['dataUser'] == null){
            redirect(base_url() . 'index.php/'. $this->dir_controlleur);
        }

        $this->m_totp_login->set_totp_key(null, $_SESSION['dataUser'][0]->user_id);
        $this->mon_compte();
    }

    function infos_utilisateur(){
        // si la session est null c'est que l'utilisateur n'est pas connecté donc retour à la page de login
        if($_SESSION['dataUser'] == null){
            redirect(base_url() . 'index.php/'. $this->dir_controlleur);
        }

        //données envoyé à la vue
        $data['userInfo'] = array(
            "prenom" => $this->m_totp_login->get_data_user($_SESSION['dataUser'][0]->user_id)[0]->user_prenom,
            "nom" => $this->m_totp_login->get_data_user($_SESSION['dataUser'][0]->user_id)[0]->user_nom,
            "mail" => $this->m_totp_login->get_data_user($_SESSION['dataUser'][0]->user_id)[0]->user_mail,
            "estSimple" => $this->m_totp_login->get_data_user($_SESSION['dataUser'][0]->user_id)[0]->totp_key == null
        );

        $data['scripts'] = array('jquery2', 'bootstrap', 'lte', 'datatables', 'datepicker', 'sweetalert');
        // Creation du bandeau
        $data['titre'] = array("Informations de l'utilisateur", "");
        $data['boutons'] = array(
            array("Retour", "fa fa-arrow-left", $this->dir_controlleur . "/mon_compte", null)
        );
        $data['custom_script'] = '';

        // On charge les differents modules neccessaires a l'affichage d'une page
        $this->load->view('template/header_html_base', $data);
        $this->load->view('template/header_scripts', $data);
        $this->load->view('template/bandeau', $data);
		$this->load->view("totp/infos_utilisateur.php", $data);
        $this->load->view('template/footer_scripts', $data);
        $this->load->view('template/footer_html_base');
    }

    function profil($idProfil)
    {
        // si la session est null c'est que l'utilisateur n'est pas connecté donc retour à la page de login
        if($_SESSION['dataUser'] == null){
            redirect(base_url() . 'index.php/'. $this->dir_controlleur);
        }

        //données envoyé à la vue
        $data['userInfo'] = array(
            "prenom" => $this->m_totp_login->get_data_user($idProfil)->user_prenom,
            "nom" => $this->m_totp_login->get_data_user($idProfil)->user_nom,
            "mail" => $this->m_totp_login->get_data_user($idProfil)->user_mail,
            "tel" =>$this->m_totp_login->get_data_user($idProfil)->user_tel
        );

        $data['scripts'] = array('jquery2', 'bootstrap', 'lte', 'datatables', 'datepicker', 'sweetalert');
        // Creation du bandeau
        $data['titre'] = array("Informations de l'utilisateur", "");
        $data['boutons'] = array(
            array("Retour", "fa fa-arrow-left", $this->dir_controlleur . "/mon_compte", null)
        );
        $data['custom_script'] = '';

        // On charge les differents modules neccessaires a l'affichage d'une page
        $this->load->view('template/header_html_base', $data);
        $this->load->view('template/header_scripts', $data);
        $this->load->view('template/bandeau', $data);
		$this->load->view("totp/infos_utilisateur.php", $data);
        $this->load->view('template/footer_scripts', $data);
        $this->load->view('template/footer_html_base');
    }
}