<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// -----------------------------------------------------

date_default_timezone_set('Europe/Paris');
setlocale(LC_TIME, 'fr', 'fr_FR', 'fr_FR.ISO8859-1');

// format des n° de tel en 00 00 00 00 00
function format_tel($tel) {
    $tel_formate = chunk_split($tel, 2, ' ');
    return $tel_formate;
}

// format du mail: ajout du mailto
function format_mail($mail) {
    return '<a href="mailto:' . $mail . '">' . $mail . '</a>';
}

/**
 * Fonction Gestion des logs
 * @param string $actions Modification/Création...
 * @param string $table table concernée
 * @param int $id id correspondante
 * @return insertion en base
 *
 */
function logs($actions, $table, $id) {
    if (!isset($_SESSION)) {
        session_start();
    }
    $CI = & get_instance();
    /* global $mysql;
      global $database_mysql;
      mysql_select_db($database_mysql, $mysql);
      $query = "INSERT INTO logs (logs_personnel_id, logs_timestamp, logs_action, logs_table, logs_lien_id) VALUES ('" . $_SESSION['MM_Username'] . "', '" . time() . "', '" . $actions . "', '" . $table . "', '" . $id . "')";
      $query = mysql_query($query, $mysql) or die(mysql_error()); */

    $req = $CI->db->query("INSERT INTO logs (logs_personnel_id, logs_timestamp, logs_action, logs_table, logs_lien_id) VALUES ('" . $_SESSION['MM_Username'] . "', '" . time() . "', '" . $actions . "', '" . $table . "', '" . $id . "')");
}

//	Gestion de l'annee bissextile	
function annee_bis($annee) {
    if (cal_days_in_month(CAL_GREGORIAN, 2, $annee) == 29) {
        return 29;
    } else {
        return 28;
    }
}

// gestion des trimestres
function trimestre($date_timestamp = NULL) {
    if (!isset($date_timestamp)) {
        //mois en cours par defaut
        $date_timestamp = time();
        $tab_tri['mois_en_cours'] = $mois_en_cours = date('n');
    } else {
        $tab_tri['mois_en_cours'] = $mois_en_cours = date('n', $date_timestamp);
    }
    $tab_tri['annee'] = date("Y", $date_timestamp);

    //Determine le trimestre en cours (1,2,3,4)
    $tab_tri['tri_en_cours'] = floor(($mois_en_cours - 1) / 3) + 1;

    //Determine le premier mois du trimestre dernier (1-12)
    $tab_tri['date_mois1_tri'] = 3 * floor(($mois_en_cours - 1) / 3) - 2;
    $tab_tri['date_mois1_tri_date'] = mktime(2, 0, 0, $tab_tri['date_mois1_tri'], 1, date('Y', $date_timestamp));

    //Determine le dernier mois du trimestre dernier (1-12)
    $tab_tri['date_mois3_tri'] = 3 * floor(($mois_en_cours - 1) / 3);
    $tab_tri['date_mois3_tri_date'] = mktime(2, 0, 0, $tab_tri['date_mois3_tri'], 1, date('Y', $date_timestamp));

    $tab_tri['mois_1_debut'] = mktime(2, 0, 0, date('n', $date_timestamp), 1, date('Y', $date_timestamp));
    $tab_tri['mois_1_fin'] = mktime(20, 0, 0, date('n', $date_timestamp), date('t', $date_timestamp), date('Y', $date_timestamp));
    $tab_tri['mois_2_debut'] = mktime(2, 0, 0, date('n', $date_timestamp) + 1, 1, date('Y', $date_timestamp));
    $tab_tri['mois_2_fin'] = mktime(20, 0, 0, date('n', $date_timestamp) + 1, date('t', $tab_tri['mois_2_debut']), date('Y', $date_timestamp));
    $tab_tri['mois_3_debut'] = mktime(2, 0, 0, date('n', $date_timestamp) + 2, 1, date('Y', $date_timestamp));
    $tab_tri['mois_3_fin'] = mktime(20, 0, 0, date('n', $date_timestamp) + 2, date('t', $tab_tri['mois_3_debut']), date('Y', $date_timestamp));

    return $tab_tri;
}

// fonction qui convertie une date en timestamp en vérifiant le format, ex: 11/02/2012 en 132891480 + calcul de l'heure suivant le début ou fin de journée
function conv_timestamp_duree($date, $periode) {
    date_default_timezone_set('Europe/Paris');
    $date_N = substr($date, 3, 2); //recuperation du mois
    $date_J = substr($date, 0, 2); //recuperation du jour
    $date_Y = substr($date, 6, 4); //recuperation de l'anne
    if ($periode == "debut") {
        $date = mktime(0, 0, 0, $date_N, $date_J, $date_Y); //transformation de la date en timestamp
    } else {
        $date = mktime(23, 59, 59, $date_N, $date_J, $date_Y); //transformation de la date en timestamp
    }
    return $date;
}

// fonction qui convertie une date en timestamp en vérifiant le format, ex: 11/02/2012 ou 11/02/12 en 1328914800	
function conv_timestamp($date) {
    if (strlen($date) == 8) {//si il n'y a pas de "/"
        $date_N = substr($date, 2, 2); //recuperation du mois
        $date_J = substr($date, 0, 2); //recuperation du jour
        $date_Y = substr($date, 4, 4); //recuperation de l'anne
        $date = mktime(0, 0, 0, $date_N, $date_J, $date_Y); //transformation de la date en timestamp
        return $date;
    }
    if (strlen($date) == 10) {// si il y a les "/"
        $date_N = substr($date, 3, 2); //recuperation du mois
        $date_J = substr($date, 0, 2); //recuperation du jour
        $date_Y = substr($date, 6, 4); //recuperation de l'anne
        $date = mktime(0, 0, 0, $date_N, $date_J, $date_Y); //transformation de la date en timestamp
        return $date;
    }
}

// fonction qui convertie une date : FR-EN ou EN-FR 	
function Conv_Date($date, $format) {
    if (!empty($date)) {
        if ($format == 'FR-EN') {
            $date_M = substr($date, 3, 2); //recuperation du mois
            $date_J = substr($date, 0, 2); //recuperation du jour
            $date_A = substr($date, 6, 4); //recuperation de l'anne
            $date = $date_A . '-' . $date_M . '-' . $date_J;
            return $date;
        }
        if ($format == 'EN-FR') {
            $date_M = substr($date, 5, 2); //recuperation du mois
            $date_J = substr($date, 8, 2); //recuperation du jour
            $date_A = substr($date, 0, 4); //recuperation de l'anne
            $date = $date_J . '/' . $date_M . '/' . $date_A;
            return $date;
        }
    } else {
        return NULL;
    }
}

// Convertir une période	
function conv_periode($periode, $format) {
    $debut = substr($periode, 0, 10);
    $fin = substr($periode, -10);

    if ($format == 'timestamp') {
        $dates = array('debut' => conv_timestamp_duree($debut, 'debut'), 'fin' => conv_timestamp_duree($fin, 'fin'));
        return $dates;
    } elseif ($format == 'dates_fr') {
        $dates = array('debut' => date('d/m/Y', conv_timestamp_duree($debut, 'debut')), 'fin' => date('d/m/Y', conv_timestamp_duree($fin, 'fin')));
        return $dates;
    } elseif ($format == 'dates_us') {
        $dates = array('debut' => date('Y-m-d', conv_timestamp_duree($debut, 'debut')), 'fin' => date('Y-m-d', conv_timestamp_duree($fin, 'fin')));
        return $dates;
    } else {
        $dates = array('debut' => 'pas de conversion possible');
        return $dates;
    }
}

// changement date	0000-00-00 en 00/00/0000 et retrait si null
function Conv_Date_EN($date) {
    if (empty($date) or $date == '0000-00-00' or $date == '1970-01-01') {
        return NULL;
    } else {
        return date('d/m/Y', strtotime($date));
    }
}

//Affiche une date de timestamp en date 00/00/0000 + heure suivant choix
function Affiche_Date($timestamp, $type) {
    if ($timestamp == '0' or $timestamp == '1970-01-01' or $timestamp == '0000-00-00' or $timestamp == '-3600' or empty($timestamp)) {
        return NULL;
    } elseif ($type == 'date') {
        return date('d/m/Y', $timestamp);
    } elseif ($type == 'date_heure') {
        return date('d/m/Y H:i', $timestamp);
    } elseif ($type == 'heure') {
        return date('H:i', $timestamp);
    }
}

//Change le format de date
function changeDateFormat($strMyDate, $strCurrentFormat, $strNewFormat) {
    $date = date_parse_from_format($strCurrentFormat, $strMyDate);
    return date($strNewFormat, mktime($date['hour'], $date['minute'], $date['second'], $date['month'], $date['day'], $date['year']));
}

//Vérification de varible si NULL
function Verif_NULL($var, $fin = NULL) {
    if ($var == '0' or empty($var)) {
        return '';
    } else {
        return $var . $fin;
    }
}

//Calcul de l'écart entre deux dates
function calcul_date_diff($start_time, $end_time, $type) {
    $a1 = date('Y', $start_time);
    $m1 = date('m', $start_time);
    $a2 = date('Y', $end_time);
    $m2 = date('m', $end_time);
    if ($type == 'jour') {
        $diff = ($end_time - $start_time) / 86400; // 86400 = 24*60*60
    } elseif ($type == 'semaine') {
        $diff = floor(($end_time - $start_time) / 604800); // 604800 = 24*60*60*7
    } elseif ($type == 'mois') {
        $diff = ($m2 - $m1) + 12 * ($a2 - $a1);
    } elseif ($type == 'annee') {
        $diff = $a2 - $a1;
    }
    return $diff;
}

//Calcul d'un age
function calcul_age($var, $date_compare) {
    $naiss = $var;
    $today = $date_compare;
    $secondes = ($today > $naiss) ? $today - $naiss : $naiss - $today;
    $annees = date('Y', $secondes) - 1970;
    return $annees;
}

//Calcul d'un age
function calcul_age2($var, $date_compare) {
    if ($var == '') {
        $result = 'nc';
    } else {
        $age = ($date_compare - $var) / (60 * 60 * 24 * 365);
        if ($age < 1) {
            $age = ($date_compare - $var) / (60 * 60 * 24 * 30);
            $result = round($age) . " mois";
        } else {
            if (floor($age) == 1) {
                $result = floor($age) . " an";
            } else {
                $result = floor($age) . " ans";
            }
        }
    }
    return $result;
}

//Calcul d'un age
function calcul_age3($var, $date_compare) {
    if ($var == '') {
        $result = 'nc';
    } else {

        $hours_in_day = 24;
        $minutes_in_hour = 60;
        $seconds_in_mins = 60;

        $birth_date = new DateTime($date_compare);
        $current_date = new DateTime($var);

        $diff = $birth_date->diff($current_date);
        if ($diff->y < 1) {
            $result = $diff->m . " mois";
        } else {
            if (floor($diff->y) == 1) {
                $result = $diff->y . " an " . $diff->m . " mois ";
            } else {
                $result = $diff->y . " ans " . $diff->m . " mois ";
            }
        }
        /* echo $years     = $diff->y . " years " . $diff->m . " months " . $diff->d . " day(s)"; echo "<br/>";
          echo $months    = ($diff->y * 12) + $diff->m . " months " . $diff->d . " day(s)"; echo "<br/>";
          echo $weeks     = floor($diff->days/7) . " weeks " . $diff->d%7 . " day(s)"; echo "<br/>";
          echo $days      = $diff->days . " days"; echo "<br/>";
          echo $hours     = $diff->h + ($diff->days * $hours_in_day) . " hours"; echo "<br/>";
          echo $mins      = $diff->h + ($diff->days * $hours_in_day * $minutes_in_hour) . " minutest"; echo "<br/>";
          echo $seconds   = $diff->h + ($diff->days * $hours_in_day * $minutes_in_hour * $seconds_in_mins) . " seconds"; echo "<br/>"; */
    }
    return $result;
}

//formate le n° de SS avec des espaces
function format_secu($num) {
    if ($num) {
        $chaine = '';
        for ($i = 0; $i < 13; $i++) {
            if ($i == '1' OR $i == '3' OR $i == '5' OR $i == '7' OR $i == '10' OR $i == '13')
                $chaine .= ' ' . $num[$i];
            else
                $chaine .= $num[$i];
        }
        return $chaine;
    }else {
        return NULL;
    }
}

//calcul la clé du n° de SS 
function format_secu_cle($num) {
    $coef = 97;
    $numero = $num;
    $mod = bcmod($numero, $coef);
    $cle = $coef - $mod;
    $longueur_cle = strlen($cle);
    if ($longueur_cle == 1) {
        $cle = "0" . $cle;
    }

    return $cle;
}

//conversion de secondes en heures / minutes
function secondes_en_heures_minutes($duree) {
    //si chiffre négatif
    $num_neg = NULL;
    if ($duree < 0) {
        $duree = abs($duree);
        $num_neg = 'oui';
    }

    $heures = intval($duree / 3600);
    $minutes = intval(($duree % 3600) / 60);
    if ($heures < 10) {
        $heures = "0" . $heures;
    }
    if ($minutes < 10) {
        $minutes = "0" . $minutes;
    }

    if ($num_neg == 'oui') {
        $string = '-' . $heures . 'h' . $minutes;
    } else {
        $string = $heures . 'h' . $minutes;
    }
    return $string;
}

//recherche d'une personne
function Get_Personnel($id) {
    $CI = & get_instance();
    //global $pdo;
    if ($id != '') {
        //$requete = $pdo->query("SELECT * FROM personnel WHERE personnel_id=".$id);
        //$data = $requete->fetch(PDO::FETCH_A);
        //$requete -> closeCursor();
        $query = $CI->db->query("SELECT * FROM personnel WHERE personnel_id=" . $id);
        $data = $query->row_array();
        return ($data) ? $data : NULL;
    } else {
        return NULL;
    }
}

//recherche dune liste de personnes
function Get_Personnel_Liste($liste_id) {
    $CI = & get_instance();
    if ($liste_id != '') {
        $query = $CI->db->query("SELECT * FROM personnel WHERE personnel_id IN (" . $liste_id) . ")";
        $data = $query->row_array();
        return ($data) ? $data : NULL;
    } else {
        return NULL;
    }
}

//recherche du dernier planning d'une personne
function Get_Personnel_Dernier_Horaires($id) {
    global $pdo;
    if ($id != '') {
        $requete = $pdo->query("SELECT * FROM personnel_planning WHERE personnel_planning_personnel_id=" . $id . " ORDER BY personnel_planning_date DESC LIMIT 1 ");
        $data = $requete->fetch(PDO::FETCH_ASSOC);
        $requete->closeCursor();
        return $data;
    } else {
        return NULL;
    }
}

//recherche des plannings d'une personne
function Liste_Personnel_Horaires($id) {
    global $pdo;
    if ($id != '') {
        $requete = $pdo->query("SELECT * FROM personnel_planning WHERE personnel_planning_personnel_id=" . $id . " ORDER BY personnel_planning_date DESC  ");
        $data = $requete->fetchall(PDO::FETCH_ASSOC);
        $requete->closeCursor();
        return $data;
    } else {
        return NULL;
    }
}

//recherche d'une personne nom/prénom
function Get_Personnel_Nom($id) {
    $CI = & get_instance();
    //global $pdo;


    if ($id != '') {
        $query = $CI->db->query("SELECT personnel_nom, personnel_prenom, personnel_niveau FROM personnel WHERE personnel_id=" . $id . "");
        //$data = $requete->fetch(PDO::FETCH_ASSOC);
        //$requete -> closeCursor();
        $intervenants = $query->row_array();

        return $intervenants['personnel_nom'] . ' ' . $intervenants['personnel_prenom'];
    } else {
        return NULL;
    }
}

//recherche si personnel est en libéral
function Get_Personnel_Liberal($id) {
    global $pdo;
    if ($id != '') {
        $requete = $pdo->query("SELECT personnel_liberal FROM personnel WHERE personnel_id=" . $id . "");
        $data = $requete->fetch(PDO::FETCH_ASSOC);
        $requete->closeCursor();
        return $data['personnel_liberal'];
    } else {
        return NULL;
    }
}

//recherche d'une personne liée a un enfant
function Get_Responsable($id_enfant, $table) {
    $CI = & get_instance();
    //global $pdo;
    if ($id_enfant and $table) {
        if ($table == 'enfant') {
            $query = $CI->db->query("SELECT * FROM " . $table . " WHERE enfant_id='" . $id_enfant . "'");
        } else {
            $query = $CI->db->query("SELECT * FROM " . $table . " WHERE " . $table . "_enfant_id='" . $id_enfant . "'");
        }
        $data = $query->row_array();
        return $data;
    } else {
        return NULL;
    }
}

//recherche les num de tel d'une personne: sous forme d'array ou liste présentée (presentation => 'array' ou 'liste')
function Liste_Tel($id, $table, $presentation) { // 
    $CI = & get_instance();
    //global $pdo;
    if ($id and $table) {
        $query = $CI->db->query("SELECT * FROM telephone WHERE telephone_table_id = '" . $id . "' AND telephone_table = '" . $table . "' ");
        //$data = $requete->fetchall(PDO::FETCH_BOTH);
        $data = $query->result_array();
        if ($presentation == 'liste') {
            $liste = NULL;
            foreach ($data as $ligne) {
                $liste .= $ligne['telephone_type'] . ': ' . format_tel($ligne['telephone_numero']) . ' ';
            }
            if (empty($liste)) {
                $liste = ' ';
            }
            return $liste;
        } else {
            return $data;
        }
    } else {
        return NULL;
    }
}

//recherche les num de tel portable d'une personne: sous forme d'array ou liste présentée (presentation => 'array' ou 'liste')
function Liste_Tel_Portable($id, $table, $presentation) { // 
    $CI = & get_instance();
    //global $pdo;
    if ($id and $table) {
        $query = $CI->db->query("SELECT * FROM telephone WHERE telephone_table_id = '" . $id . "' AND telephone_table = '" . $table . "' AND LEFT(TRIM(telephone_numero) , 2) IN (06,07)");
        //$data = $requete->fetchall(PDO::FETCH_BOTH);
        $data = $query->result_array();
        if ($presentation == 'liste') {
            $liste = NULL;
            foreach ($data as $ligne) {
                $liste .= format_tel($ligne['telephone_numero']) . ' ';
            }
            if (empty($liste)) {
                $liste = ' ';
            }
            return $liste;
        } else {
            return $data;
        }
    } else {
        return NULL;
    }
}

function Get_Tel_Portable_Intervenant($intervenant_id) { // 
    $CI = & get_instance();
    $query = $CI->db->query("SELECT personnel_tel FROM personnel WHERE LEFT(TRIM(personnel_tel) , 2) IN (06,07)");
    //$data = $requete->fetch(PDO::FETCH_BOTH);
    $data = $query->result_row();
    if (!empty($data)) {
        $tel_intervenant = format_tel($data->personnel_tel);
    } else {
        $tel_intervenant = NULL;
    }

    return $tel_intervenant;
}

//recherche d'un enfant
function Get_Enfant($id, $scolarite = NULL, $champs = NULL) {
    $CI = & get_instance();
    //global $pdo;
    //Détails Enfant
    $select = (empty($champs)) ? '*' : $champs;
    $query = $CI->db->query(" SELECT ".$select." FROM enfant 
	LEFT JOIN ecole_historique ON enfant_id = ecole_historique_usager_id 
	LEFT JOIN enfant_details ON enfant_id = enfant_details_enfant_id 
	WHERE enfant_id='" . $id . "' ");
    $data = $query->row_array();

    //Scolarité
    if(!empty($scolarite)){
    //------------------------------------------------------------------------------------ A FAIRE:  scolarite si demandée
    $query_ecole = $CI->db->query("SELECT ecole_historique_ecole_id, ecole_historique_classe_id FROM ecole_historique 
				WHERE ecole_historique_usager_id = '" . $id . "' 
				AND DATE(ecole_historique_debut) IN (SELECT max(DATE(ecole_historique_debut)) 
														FROM ecole_historique 
														WHERE ecole_historique_usager_id = '" . $id . "')
				");
    $data_ecole = $query_ecole->row_array();
    //$data_ecole = $requete_ecole->fetch(PDO::FETCH_ASSOC);
    //$requete_ecole -> closeCursor();
    $data['ecole_historique_ecole_id'] = $data_ecole['ecole_historique_ecole_id'];
    $data['ecole_historique_classe_id'] = $data_ecole['ecole_historique_classe_id'];
    }

    return $data;
}

//recherche d'un enfant
function Get_Enfant_Champ($id, $champs) {
    $CI = & get_instance();
    $query = $CI->db->query("SELECT " . $champs . " FROM enfant WHERE enfant_id='" . $id . "'");
    $data = $query->row_array();
    return $data;
}

//liste les caisses
function Liste_Caisse() {
    $CI = & get_instance();
    $query = $CI->db->query("SELECT * FROM caisse ORDER BY caisse_nom");
    $data = $query->row_array();
    return $data;
}

//recherche d'une caisse
function Get_Caisse($id) {
    $CI = & get_instance();
    $query = $CI->db->query("SELECT * FROM caisse WHERE caisse_id='" . $id . "'");
    $data = $query->row_array();
    return $data;
}

//liste les Complémentaires Santé
function Liste_Complementaire() {
    global $pdo;
    $requete = $pdo->query("SELECT * FROM complementaire ORDER BY complementaire_nom");
    $data = $requete->fetchall(PDO::FETCH_BOTH);
    $requete->closeCursor();
    return $data;
}

//recherche d'une Complémentaires Santé
function Get_Complementaire($id) {
    global $pdo;
    $requete = $pdo->query("SELECT * FROM complementaire WHERE complementaire_id='" . $id . "'");
    $data = $requete->fetch(PDO::FETCH_BOTH);
    $requete->closeCursor();
    return $data;
}

//liste les Responsabilités Civiles
function Liste_RC() {
    global $pdo;
    $requete = $pdo->query("SELECT * FROM rc ORDER BY rc_nom");
    $data = $requete->fetchall(PDO::FETCH_BOTH);
    $requete->closeCursor();
    return $data;
}

//recherche d'une Responsabilité Civile
function Get_RC($id) {
    global $pdo;
    $requete = $pdo->query("SELECT * FROM rc WHERE rc_id='" . $id . "'");
    $data = $requete->fetch(PDO::FETCH_BOTH);
    $requete->closeCursor();
    return $data;
}

//recherche d'une caisse
function Get_Caisse_Name($id) {
    global $pdo;
    $requete = $pdo->query("SELECT caisse_nom FROM caisse WHERE caisse_id='" . $id . "'");
    $data = $requete->fetch(PDO::FETCH_BOTH);
    $requete->closeCursor();
    return $data['caisse_nom'];
}

//recherche des documents liés a une activité
function Get_Document_Activite($id) {
    global $pdo;
    $requete = $pdo->query("SELECT * FROM activite_document WHERE activite_document_activite_id ='" . $id . "'");
    $data = $requete->fetch(PDO::FETCH_BOTH);
    $requete->closeCursor();
    return $data;
}

//liste les DIPC d'un enfant
function Liste_DIPC($enfant_id) {
    global $pdo;
    $requete = $pdo->query("SELECT * FROM dipc WHERE dipc_enfant_id = '" . $enfant_id . "' ORDER BY dipc_date_fin");
    //if(!empty($requete))
    //{
    $data = $requete->fetchall(PDO::FETCH_BOTH);
    $requete->closeCursor();
    //}else{
    //	$data = NULL;
    //}
    return $data;
}

//recherche d'un DIPC
function Get_DIPC($id) {
    global $pdo;
    $requete = $pdo->query("SELECT * FROM dipc WHERE dipc_id='" . $id . "'");
    $data = $requete->fetch(PDO::FETCH_BOTH);
    $requete->closeCursor();
    return $data;
}

//liste les transports d'un usager
function Liste_Transport_Usager($enfant_id) {
    global $pdo;
    $requete = $pdo->query("SELECT * FROM transport WHERE transport_enfant_id = '" . $enfant_id . "' ORDER BY transport_date_fin");
    //if(!empty($requete))
    //{
    $data = $requete->fetchall(PDO::FETCH_BOTH);
    $requete->closeCursor();
    //}else{
    //	$data = NULL;
    //}
    return $data;
}

//recherche d'un Transport
function Get_Transport_Usager($id) {
    global $pdo;
    $requete = $pdo->query("SELECT * FROM transport WHERE transport_id='" . $id . "'");
    $data = $requete->fetch(PDO::FETCH_BOTH);
    $requete->closeCursor();
    return $data;
}

//recherche détails d'un moyen de Transport
function Get_Transport_Detail($id) {
    global $pdo;
    $requete = $pdo->query("SELECT * FROM liste_transport WHERE liste_transport_id='" . $id . "'");
    $data = $requete->fetch(PDO::FETCH_BOTH);
    $requete->closeCursor();
    return $data;
}

//recherche de la société de taxi par défaut de l'usager
function Get_Taxi_Usager_Defaut($id_usager) {
    global $pdo;
    $requete = $pdo->query("SELECT taxi_id,taxi_nom FROM taxi,enfant WHERE enfant_id='" . $id_usager . "' AND enfant_taxi_id = taxi_id");
    $data = $requete->fetch(PDO::FETCH_BOTH);
    $requete->closeCursor();
    return $data;
}

//recherche de la société de taxi aller pour l'activité 
function Get_Taxi_Activite_Aller($id_activite) {
    global $pdo;
    $requete = $pdo->query("SELECT activite_transport,activite_taxi_aller_id,taxi_nom FROM activite,taxi WHERE activite_id='" . $id_activite . "' AND taxi_id = activite_taxi_aller_id");
    $data = $requete->fetch(PDO::FETCH_BOTH);
    $requete->closeCursor();
    return $data;
}

//recherche de la société de taxi Retour pour l'activité 
function Get_Taxi_Activite_Retour($id_activite) {
    global $pdo;
    $requete = $pdo->query("SELECT activite_transport_retour,activite_taxi_retour_id,taxi_nom FROM activite,taxi WHERE activite_id='" . $id_activite . "' AND taxi_id = activite_taxi_retour_id");
    $data = $requete->fetch(PDO::FETCH_BOTH);
    $requete->closeCursor();
    return $data;
}

//liste des absences ----------------------------------------------------> a verifier pour remplacement par Liste_Table('xxx')
function Liste_Absences() {
    global $pdo;
    $requete = $pdo->query("SELECT * FROM liste_motif_absence");
    $data = $requete->fetchall(PDO::FETCH_ASSOC);
    $requete->closeCursor();
    return $data;
}

//liste des tranports----------------------------------------------------> a verifier pour remplacement par Liste_Table('xxx')
function Liste_Transport() {
    global $pdo;
    $requete = $pdo->query("SELECT * FROM liste_transport");
    $data = $requete->fetchall(PDO::FETCH_ASSOC);
    $requete->closeCursor();
    return $data;
}

//liste les villes de la table Enfant 
function Liste_Villes() {
    global $pdo;
    mysql_set_charset('utf8', $pdo);
    $requete = $pdo->query("SELECT enfant_ville FROM enfant GROUP BY enfant_ville ORDER BY enfant_ville ");
    $data = $requete->fetchall(PDO::FETCH_COLUMN);
    $requete->closeCursor();
    //debug(array_values($data));
    return array_values($data);
}

//liste des lieux d'interventions----------------------------------------------------> a verifier pour remplacement par Liste_Table('xxx')
function Liste_lieu() {
    global $pdo;
    $requete = $pdo->query("SELECT * FROM liste_lieu_activite");
    $data = $requete->fetchall(PDO::FETCH_ASSOC);
    $requete->closeCursor();
    return $data;
}

//liste une table dans une liste de la BDD // $affichage = tout / non_archive / archive
function Liste_Table($table, $affichage) {

    $CI = & get_instance();

    $SQL = $ORDER = NULL;
    if (in_array($table, array('etp_domaine', 'etp_contrat', 'taxi', 'caisse', 'complementaire', 'rc'))) {
        $ORDER = $table . "_nom";
    } else {
        $ORDER = $table . "_libelle";
    }

    if ($affichage == 'non_archive') {
        $SQL = ' WHERE ' . $table . '_archive = ""';
    } elseif ($affichage == 'archive') {
        $SQL = ' WHERE ' . $table . '_archive = "x"';
    } else {
        $SQL = NULL;
    }
    $req = "SELECT * FROM " . $table . " " . $SQL . " ORDER BY " . $ORDER . " ";
    //debug($req);
    $requete = $CI->db->query($req);
    $data = $requete->result_array();
    return $data;
}

//liste la table liste_generale dans une liste de la BDD // $affichage = tout / non_archive / archive
function Liste_Table_Generale($type, $regroupement = NULL, $archive = 'non_archive', $ORDER = "liste_generale_libelle") {
    $CI = & get_instance();

    if ($archive == 'non_archive') {
        $SQL .= ' AND liste_generale_archive IS NULL ';
    } elseif ($affichage == 'archive') {
        $SQL .= ' AND liste_generale_archive = 1 ';
    } else {
        $SQL .= NULL;
    }

    if ($regroupement != NULL) {
        $SQL .= ' AND liste_generale_regroupement = "' . $regroupement . '"  ';
    }

    $req = "SELECT * FROM liste_generale WHERE liste_generale_type = '" . $type . "' " . $SQL . " ORDER BY " . $ORDER . " ";
    //debug($req);
    $requete = $CI->db->query($req);
    if ($requete) {
        $data = $requete->fetchall(PDO::FETCH_ASSOC);
        $requete->closeCursor();
    }
    return $data;
}

//Recupere un item de la table liste_generale 
function Get_Table_Generale($id, $type) {
    $CI = & get_instance();
    $SQL = NULL;

    $req = "SELECT liste_generale_libelle FROM liste_generale WHERE liste_generale_rubrique_id = '" . $id . "' AND liste_generale_type = '" . $type . "' ";
    //debug($req);
    $requete = $CI->db->query($req);
    if ($requete) {
        $data = $requete->row_array();
        return $data['liste_generale_libelle'];
    }
    return NULL;
}

//Récupère les champs d'une table par l'ID
function Get_ID_Champs($table, $id, $champs, $SQL) {
    $CI = & get_instance();

    $req = "SELECT " . $champs . " FROM " . $table . " WHERE " . $id . " " . $SQL . "  ";
    $requete = $CI->db->query($req);
    $data = $requete->row_array();
    return $data;
}

//Retourne le contenu d'une table par l'id
function Get_Table_Contenu($table, $id) {
    $CI = & get_instance();
    $req = "SELECT * FROM " . $table . " WHERE " . $table . "_id = '" . $id . "' ";
    $requete = $CI->db->query($req);
    $data = $requete->row_array();
    return $data;
}

//Retourne le libelle d'une table
function Get_Libelle($table, $id) {
    $CI = & get_instance();
    $req = "SELECT " . $table . "_libelle FROM " . $table . " WHERE " . $table . "_id = '" . $id . "' ";
    $requete = $CI->db->query($req);
    $data = $requete->row_array();
    return $data[$table . "_libelle"];
}

//Affiche le libelle d'une liste 
function Get_Libelle_From_Table($table, $id) {
    global $pdo;
    $requete = $pdo->query("SELECT " . $table . "_libelle FROM " . $table . " WHERE " . $table . "_id='" . $id . "'");
    $data = $requete->fetch(PDO::FETCH_BOTH);
    $requete->closeCursor();
    return $data[$table . "_libelle"];
}

//Liste les absences intervenants pour une activité
function Liste_Absence_Intervenant($activite_id) {
    $CI = & get_instance();
    $req = "SELECT liste_motif_absence_intervenant_libelle, personnel_id, personnel_nom, personnel_prenom
	FROM absence_intervenant, liste_motif_absence_intervenant, personnel 
	WHERE absence_intervenant_intervenant_id = personnel_id
	AND absence_intervenant_liste_motif_absence_intervenant_id = liste_motif_absence_intervenant_id 
    AND absence_intervenant_activite_id = " . $activite_id;
    $requete = $CI->db->query($req);
    if ($requete) {
        $data = $requete->row_array();
        return $data;
    } else {
        return NULL;
    }
}

//recherche d'une absence
function Get_Absence($id) {
    global $pdo;
    $requete = $pdo->query("SELECT * FROM liste_motif_absence WHERE liste_motif_absence_id='" . $id . "'");
    $data = $requete->fetch(PDO::FETCH_BOTH);
    $requete->closeCursor();
    return $data['liste_motif_absence_libelle'];
}

//recherche d'une absence d'intervenant
function GetAbsenceIntervenant($activite_id, $intervenant_id, $affichage_libelle) {

    //global $pdo;
    $CI = & get_instance();
    $query = $CI->db->query("
						SELECT liste_motif_absence_intervenant_libelle
						FROM absence_intervenant,liste_motif_absence_intervenant 
						WHERE liste_motif_absence_intervenant_id = absence_intervenant_liste_motif_absence_intervenant_id
						AND absence_intervenant_activite_id = '" . $activite_id . "'
						AND absence_intervenant_intervenant_id = '" . $intervenant_id . "'
						");
    $data = $query->row_array();

    if ($affichage_libelle == 'oui') {
        return $data['liste_motif_absence_intervenant_libelle'];
    } else {
        return $data;
    }
}

//recherche d'un type de contrat
function Get_Contrat($id) {
    global $pdo;
    $requete = $pdo->query("SELECT * FROM etp_contrat WHERE etp_contrat_id='" . $id . "'");
    $data = $requete->fetch(PDO::FETCH_BOTH);
    $requete->closeCursor();
    return $data['etp_contrat_nom'];
}

//recherche d'un domaine
function Get_Domaine($id) {
    global $pdo;
    $requete = $pdo->query("SELECT * FROM etp_domaine WHERE etp_domaine_id='" . $id . "'");
    $data = $requete->fetch(PDO::FETCH_BOTH);
    $requete->closeCursor();
    return $data;
}

//recherche d'un service
function Get_Service($id) {
    $CI = & get_instance();
    //global $pdo;
    /* global $pdo;
      $requete = $pdo->query("SELECT * FROM service WHERE service_id='".$id."'");
      $data = $requete->fetch(PDO::FETCH_ASSOC);
      $requete -> closeCursor();
      return $data; */
if($id!=NULL and $id!=0 and $id!='')
{
    $query = $CI->db->query("SELECT * FROM service WHERE service_id=" . $id);
    $data = $query->row_array();
    return ($data) ? $data : NULL;
}else{
    return NULL;
}
}

//recherche d'un service sur 3 tables
function Get_Service_Complet($id) {
    global $pdo;
    $requete = $pdo->query(" SELECT * FROM service 
							LEFT JOIN service_details ON service_id = service_details_id 
							LEFT JOIN service_cnsa ON service_id = service_cnsa_id 
							WHERE service_id='" . $id . "' ");
    $data = $requete->fetch(PDO::FETCH_ASSOC);
    $requete->closeCursor();
    return $data;
}

//recherche d'un service
function Get_Service_Details($id) {
    global $pdo;
    $requete = $pdo->query("SELECT * FROM service_details WHERE service_id='" . $id . "'");
    $data = $requete->fetch(PDO::FETCH_ASSOC);
    $requete->closeCursor();
    return $data;
}

//recherche d'un service
function Get_Service_cnsa($id) {
    global $pdo;
    $requete = $pdo->query("SELECT * FROM service_cnsa WHERE service_id='" . $id . "'");
    $data = $requete->fetch(PDO::FETCH_ASSOC);
    $requete->closeCursor();
    return $data;
}

//recupère les infos de facturation d'un service
function Get_Service_Facturation($id) {
    global $pdo;
    $requete = $pdo->query("SELECT service_activite_facturation, service_activite_calcul FROM service WHERE service_id='" . $id . "'");
    $data = $requete->fetch(PDO::FETCH_ASSOC);
    $requete->closeCursor();
    return $data;
}

//Affiche le nom d'un service
function Get_Service_Name($id) {
    global $pdo;
    $liste = Liste_Services();
    foreach ($liste as $service) {
        $service_nom[$service['service_id']] = $service['service_nom'];
    }
    $data = $service_nom[$id];
    return $data;
}

//liste les services
function Liste_Services() {
    $CI = & get_instance();
    $query = $CI->db->query("SELECT * FROM service ORDER BY service_nom");
    $data = $query->result_array();
    return $data;
}

//Affiche la liste les services
//utilise soit $type_ets ou $services en array
//$type_affichage -> checkbox/radio/select
//$var->donnée a comparer, array pour checkbox, int pour 'selected' et 'checked'
function Affiche_Liste_Services($type_ets, $type_affichage, $services, $var = NULL, $archive = NULL) {
    //global $pdo;
    $CI = & get_instance();
    $SQL = NULL;
    $term = ' WHERE ';
    if ($type_ets != '') {
        $SQL .= $term . ' service_type IN (' . $type_ets . ') ';
        $term = ' AND ';
    }
    if ($services != '') {
        $SQL .= $term . ' service_id IN (' . $services . ') ';
        $term = ' AND ';
    }
    if ($archive == 'non_archive') {
        $SQL .= $term . ' service_archive <> "x" ';
        $term = ' AND ';
    }
    $query = "SELECT * FROM service " . $SQL . " ORDER BY service_nom";
    //echo $query;
    $query = $CI->db->query($query);
    $data = $query->result_array();
    foreach ($data as $key => $ligne_services) {
        $service_nom = utf8_encode($ligne_services['service_nom']);
        if ($type_affichage == 'checkbox') {
            if (!empty($var)) {
                ?>
                <input name="services[]" type="checkbox" value="<?php echo $ligne_services['service_id']; ?>" <?php if (in_array($ligne_services['service_id'], $var)) { ?>checked<?php } ?> /><?php echo $service_nom; ?><br />
            <?php } else { ?>
                <input name="services[]" type="checkbox" value="<?php echo $ligne_services['service_id']; ?>" /><?php echo $service_nom; ?><br />
            <?php } ?>
            <?php
        } elseif ($type_affichage == 'radio') {
            ?>
            <input name="services" type="radio" value="<?php echo $ligne_services['service_id']; ?>" /><?php echo $service_nom; ?><br />
            <?php
        } elseif ($type_affichage == 'select') {
            ?>
            <option value="<?php echo $ligne_services['service_id']; ?>" <?php if ($ligne_services['service_id'] == $var) { ?>selected<?php } ?>><?php echo $service_nom; ?></option>
            <?php
        }
    }
}

//liste les services par intervenant //----------------------------------------------------------> ne fonctionne pas si plusieurs services !! a verif si utilisé
function Liste_Services_Intervenant($intervenant) {
    $CI = & get_instance();
    $query = $CI->db->query("SELECT * FROM personnel,service WHERE personnel_id = '" . $intervenant . "' and personnel_service_id = service_id ORDER BY service_nom");
    $data = $query->row_array();
    return $data;
}

//liste les services par intervenant //----------------------------------------------------------> ne fonctionne pas si plusieurs services !! a verif si utilisé
function Liste_Multi_Services_Intervenant($intervenant, $services_intervenant) {
    $CI = & get_instance();
    $query = $CI->db->query("SELECT service_id,service_nom FROM personnel,service WHERE personnel_id = '" . $intervenant . "' and personnel_service_id IN '" . $services_intervenant . "' ORDER BY service_nom");
    $data = $query->row_array();
    return $data;
}

//liste les services par intervenant //----------------------------------------------------------> nouveau pour remplacer Liste_Services_Intervenant
function Tab_Services_Intervenant($intervenant, $services_intervenant) {
    $CI = & get_instance();
    $query = $CI->db->query("SELECT service_id,service_nom FROM personnel,service WHERE personnel_id = '" . $intervenant . "' and service_id IN (" . $services_intervenant . ") ORDER BY service_nom");
    $data = $query->result_array();
    return $data;
}

//Liste les contacts d'un usager
function Liste_Contact($id) { //'tab' : tableau d'array
    $CI = & get_instance();
    //récupère les id des contacts dans la ligne de l'usager
    $query = $CI->db->query("SELECT enfant_contact_id FROM enfant WHERE enfant_id='" . $id . "'");
    $data = $query->row_array();
    foreach (explode(',', $data['enfant_contact_id']) as $contact) {
        $requete = $CI->db->query("SELECT contact_nom, contact_prenom FROM contact WHERE contact_id = '" . $contact . "'");
        $contact_detail = $query->row_array();
        $tab_contact[$contact]['nom'] = $contact_detail['contact_nom'] . ' ' . $contact_detail['contact_prenom'];
    }


    return $tab_contact;
}

//Liste les contacts d'un usager
function Liste_Contact_Champs($id) { //'tab' : tableau d'array
    $tab_contacts = array();
    $CI = & get_instance();
    //récupère les id des contacts dans la ligne de l'usager
    $query = $CI->db->query("SELECT enfant_contact_id FROM enfant WHERE enfant_id='" . $id . "'");
    $data = $query->row_array();
    foreach (explode(',', $data['enfant_contact_id']) as $contact) {
        $query = $CI->db->query("SELECT * FROM contact,contact_type WHERE contact_type = contact_type_id AND contact_id = '" . $contact . "'");
        $data = $query->row_array();
        if (!empty($data)) {
            array_push($tab_contacts, $data);
        }
    }


    return $tab_contacts;
}

//recherche d'un contact
function Get_Contact($id) {
    global $pdo;
    $requete = $pdo->query("SELECT * FROM contact WHERE contact_id='" . $id . "'");
    $data = $requete->fetch(PDO::FETCH_ASSOC);
    $requete->closeCursor();
    return $data;
}

//recherche du type de contact
function Get_Contact_Type($id) {
    global $pdo;
    $requete = $pdo->query("SELECT * FROM contact_type WHERE contact_type_id='" . $id . "'");
    $data = $requete->fetch(PDO::FETCH_ASSOC);
    $requete->closeCursor();
    return $data;
}

//recherche d'une attente
function Get_Attente($id) {
    global $pdo;
    $requete = $pdo->query("SELECT * FROM attente WHERE attente_id = " . $id . "");
    $data = $requete->fetch(PDO::FETCH_BOTH);
    $requete->closeCursor();
    return $data;
}

//recherche d'une discipline
function Get_Discipline($id) {
    global $pdo;
    $requete = $pdo->query("SELECT * FROM discipline WHERE discipline_id=" . $id . "");
    $data = $requete->fetch(PDO::FETCH_BOTH);
    $requete->closeCursor();
    return $data;
}

//liste les disciplines. Suivant une plage d'id (début/fin) ou un array ou un type
function Liste_Discipline($id_discipline_debut, $id_discipline_fin, $tab_disciplines, $type_discipline) {
    global $pdo;
    if ($id_discipline_debut) {
        $SQL = " discipline_id BETWEEN '" . $id_discipline_debut . "' AND '" . $id_discipline_fin . "' ";
    }
    if ($tab_disciplines) {
        $SQL = " discipline_id IN (" . implode(',', $tab_disciplines) . ") ";
    }
    if ($type_discipline) {
        $SQL = " discipline_famille = '" . $type_discipline . "' ";
    }
    $requete = $pdo->query("SELECT * FROM discipline WHERE " . $SQL . " ORDER BY discipline_libelle");
    $data = $requete->fetchall(PDO::FETCH_BOTH);
    $requete->closeCursor();
    return $data;
}

//liste les intervenants. Choix du service, de la profession, du niveau, en libéral (oui ou non), archivé (oui ou non) et requete complémentaire
function Liste_Intervenant($tab_service, $tab_profession, $tab_niveau, $personnel_liberal, $archive, $autre_requete, $sans_service = 'non') {
    $CI = & get_instance();
    $SQL = " ";
    if ($tab_service) {
        foreach ($tab_service as $service_id) {
            if (!isset($where)) {
                $where = $SQL .= " WHERE (";
            } else {
                $SQL .= " OR ";
            }
            $SQL .= " FIND_IN_SET (" . $service_id . ", personnel_service_id)>0 ";
        }

        if ($sans_service == 'oui') {
            if (!isset($where)) {
                $where = $SQL .= " WHERE ";
            } else {
                $SQL .= " OR ";
            }
            $SQL .= " personnel_service_id = '' OR personnel_service_id IS NULL ";


            $SQL .= " ) ";
        }
        //$SQL .= " FIND_IN_SET (".implode(',',$tab_service).", personnel_service_id)>0 ";
    }
    if ($tab_profession) {
        if (!isset($where)) {
            $where = $SQL .= " WHERE ";
        } else {
            $SQL .= " AND ";
        }
        $SQL .= " personnel_profession IN (" . implode(',', $tab_profession) . ") ";
    }
    if ($tab_niveau) {
        if (!isset($where)) {
            $where = $SQL .= " WHERE ";
        } else {
            $SQL .= " AND ";
        }
        $SQL .= " personnel_niveau IN (" . implode(',', $tab_niveau) . ") ";
    }
    if ($personnel_liberal == 'oui') {
        if (!isset($where)) {
            $where = $SQL .= " WHERE ";
        } else {
            $SQL .= " AND ";
        }
        $SQL .= " personnel_liberal = 'oui' ";
    }
    if ($personnel_liberal == 'non') {
        if (!isset($where)) {
            $where = $SQL .= " WHERE ";
        } else {
            $SQL .= " AND ";
        }
        $SQL .= " personnel_liberal = 'non' ";
    }
    if ($archive == 'non') {
        if (!isset($where)) {
            $where = $SQL .= " WHERE ";
        } else {
            $SQL .= " AND ";
        }
        $SQL .= " personnel_archive_date = '0' ";
    }
    if ($archive == 'oui') {
        if (!isset($where)) {
            $where = $SQL .= " WHERE ";
        } else {
            $SQL .= " AND ";
        }
        $SQL .= " personnel_archive_date <> '0' ";
    }

    $SQL .= $autre_requete;

    $req = "SELECT * FROM personnel  " . $SQL . " ORDER BY personnel_nom";

//echo $req;
    $requete = $CI->db->query($req);
    $data = $requete->result_array();
    return $data;
}

//liste les fonctions
function Liste_Fonctions($tab_fonctions = NULL, $archive = NULL) {
    $CI = & get_instance();

    $fonctions = ($tab_fonctions) ? " liste_fonctions_id IN (" . implode(',', $tab_fonctions) . ") " : NULL;
    $archive = ($archive) ? " liste_fonctions_archive = 'x' " : NULL;

    $and = ($tab_fonctions and $archive) ? " AND " : NULL;
    $where = ($tab_fonctions or $archive) ? " WHERE " : NULL;

    $requete = $CI->db->query("SELECT * FROM liste_fonctions $where $fonctions $and $archive ORDER BY liste_fonctions_libelle");
    $data = $requete->result_array(PDO::FETCH_BOTH);
    return $data;
}

//recherche d'un taxi
function Get_Taxi($id) {
    global $pdo;
    $requete = $pdo->query("SELECT * FROM taxi WHERE taxi_id = '" . $id . "' ");
    $data = $requete->fetch(PDO::FETCH_BOTH);
    $requete->closeCursor();
    return $data;
}

//affiche le nom d'un taxi
function Get_Taxi_Nom($id) {
    global $pdo;
    $requete = $pdo->query("SELECT taxi_nom FROM taxi WHERE taxi_id = '" . $id . "' ");
    $data = $requete->fetch(PDO::FETCH_BOTH);
    $requete->closeCursor();
    return $data['taxi_nom'];
}

//recherche d'une école
function Get_Ecole($id) {
    $CI = & get_instance();
    $query = $CI->db->query("SELECT * FROM ecole WHERE ecole_id = '" . $id . "' ");
    $data = $query->row_array();

    return $data;
}

//recherche de l'école de l'usager au jour
function Get_Scolarite_Usager($id, $date = NULL) {
    //global $pdo;
    $CI = & get_instance();
    (!is_null($date)) ? $date = $date : $date = date('Y/m/d');
    //récupère la scolarite en cours
    $query = $CI->db->query("SELECT * FROM ecole_historique 
							WHERE ecole_historique_usager_id = '" . $id . "'
							AND DATE(ecole_historique_debut) <= DATE('" . date('Y/m/d') . "')
							AND DATE(ecole_historique_fin) >= DATE('" . date('Y/m/d') . "')
							");
    //$data = $requete->fetch(PDO::FETCH_ASSOC);
    $data = $query->row_array();
    //si pas de scolarite en cours récupère la dernière
    if (empty($data)) {
        $req = "SELECT * FROM ecole_historique 
				WHERE ecole_historique_usager_id = '" . $id . "' AND DATE(ecole_historique_debut) IN (SELECT max(DATE(ecole_historique_debut)) FROM ecole_historique 
				WHERE ecole_historique_usager_id = '" . $id . "') 
				";
        $query = $CI->db->query($req);
        $data = $query->row_array();
    }
    return $data;
}

//recherche d'une classe
function Get_Classe($id) {
    //global $pdo;
    $CI = & get_instance();
    $query = $CI->db->query("SELECT * FROM liste_classe WHERE liste_classe_id = '" . $id . "' ");
    $data = $query->row_array();

    return $data;
}

//liste les classes
function Liste_Classes() {
    global $pdo;
    $requete = $pdo->query("SELECT * FROM liste_classe ORDER BY liste_classe_libelle");
    $data = $requete->fetchall(PDO::FETCH_BOTH);
    $requete->closeCursor();
    return $data;
}

//liste les classes pour un type
function Liste_Classes_Type($type, $format = NULL) {
    global $pdo;
    $requete = $pdo->query("SELECT * FROM liste_classe WHERE liste_classe_regroupement = '" . $type . "' ORDER BY liste_classe_libelle");
    $data = $requete->fetchall(PDO::FETCH_ASSOC);
    $requete->closeCursor();
    if (is_null($format)) { //retour complet
        return $data;
    } elseif ($format == "id") { //retour tableau d'id 
        foreach ($data as $id) {
            $tab_id[] = $id['liste_classe_id'];
        }
        return $tab_id;
    }
}

//liste des origines
function Liste_Origines() {
    global $pdo;
    $requete = $pdo->query("SELECT * FROM liste_origine ORDER BY liste_origine_libelle");
    $data = $requete->fetchall(PDO::FETCH_BOTH);
    $requete->closeCursor();
    return $data;
}

//liste des origines par type
function Liste_Origines_Type() {
    global $pdo;
    $requete = $pdo->query("SELECT * FROM liste_origine ORDER BY liste_origine_libelle");
    $res = $requete->fetchall(PDO::FETCH_ASSOC);
    $requete->closeCursor();
    foreach ($res as $data) {
        $tab_origines[$data['liste_origine_id']] = $data['liste_origine_cnsa'];
    }
    return $tab_origines;
}

//liste des réorientations
function Liste_Reorientations() {
    global $pdo;
    $requete = $pdo->query("SELECT * FROM liste_reorientation ORDER BY liste_reorientation_libelle");
    $data = $requete->fetchall(PDO::FETCH_BOTH);
    $requete->closeCursor();
    return $data;
}

//liste des réorientations par type
function Liste_Reorientations_Type() {
    global $pdo;
    $requete = $pdo->query("SELECT * FROM liste_reorientation ORDER BY liste_reorientation_libelle");
    $res = $requete->fetchall(PDO::FETCH_ASSOC);
    $requete->closeCursor();
    foreach ($res as $data) {
        $tab_reorientations[$data['liste_reorientation_id']] = $data['liste_reorientation_cnsa'];
    }
    return $tab_reorientations;
}

//liste des motifs de sorties
function Get_Motif_Sortie($id) {
    global $pdo;
    $requete = $pdo->query("SELECT * FROM liste_motif_sortie WHERE liste_motif_sortie_id = '" . $id . "'");
    $data = $requete->fetch(PDO::FETCH_ASSOC);
    $requete->closeCursor();
    return $data['liste_motif_sortie_libelle'];
}

//liste des motifs par type
function Liste_Motifs_Type() {
    global $pdo;
    $requete = $pdo->query("SELECT * FROM liste_motif_sortie ORDER BY liste_motif_sortie_libelle");
    $res = $requete->fetchall(PDO::FETCH_ASSOC);
    $requete->closeCursor();
    foreach ($res as $data) {
        $tab_motifs[$data['liste_motif_sortie_id']] = $data['liste_motif_sortie_cnsa'];
    }
    return $tab_motifs;
}

//liste des déficiences principales
function Liste_Deficiences_Principales($archive) {
    global $pdo;
    if ($archive == 'oui') {
        $SQL = 'WHERE liste_deficience_principale_archive <> "x" ';
    }
    $requete = $pdo->query("SELECT * FROM liste_deficience_principale " . $SQL . "");
    $data = $requete->fetchall(PDO::FETCH_ASSOC);
    $requete->closeCursor();
    return $data;
}

//recherche des déficiences principales
function Get_Deficience_Principale($id) {
    global $pdo;
    $requete = $pdo->query("SELECT * FROM liste_deficience_principale WHERE liste_deficience_principale_id = '" . $id . "'");
    $data = $requete->fetch(PDO::FETCH_ASSOC);
    $requete->closeCursor();
    return $data['liste_deficience_principale_nom'];
}

//recupère la cellule Excel de la deficience principale
function Get_deficience_principale_excel_CNSA($id, $type) {
    global $pdo;
    $req = "SELECT liste_deficience_principale_rapport_cnsa FROM liste_deficience_principale WHERE liste_deficience_principale_id = " . $id . " ";
    $requete = $pdo->query($req);
    $data = $requete->fetch(PDO::FETCH_ASSOC);
    $requete->closeCursor();
    return $data['liste_deficience_principale_rapport_cnsa'];
}

//recupère la cellule Excel de la deficience principale
function Get_deficience_principale_excel_ANAP($id, $type) {
    global $pdo;
    $req = "SELECT liste_deficience_principale_rapport_anap FROM liste_deficience_principale WHERE liste_deficience_principale_id = " . $id . " ";
    $requete = $pdo->query($req);
    $data = $requete->fetch(PDO::FETCH_ASSOC);
    $requete->closeCursor();
    return $data['liste_deficience_principale_rapport_anap'];
}

//recupère les CIM10 CFTMEA
function Liste_CIM10_CFTMEA_CNSA($var = NULL) {
    global $pdo;
    $requete = $pdo->query("SELECT CIM10_id, CIM10_rapport_cnsa FROM `CIM10` WHERE CIM10_rapport_cnsa <> '' ");
    $data = $requete->fetchall(PDO::FETCH_ASSOC);
    $requete->closeCursor();
    foreach ($data as $cim) {
        $tab_cim[$cim['CIM10_id']] = $cim['CIM10_rapport_cnsa'];
    }
    return $tab_cim;
}

//recupère les CFTMEA axe1
function Liste_CFTMEA_Axe1($var = NULL) {
    global $pdo;
    $requete = $pdo->query("SELECT CIM10_id, CIM10_CFTMEA_code FROM `CIM10` WHERE CIM10_CFTMEA_axe = 1 ");
    $data = $requete->fetchall(PDO::FETCH_ASSOC);
    $requete->closeCursor();
    foreach ($data as $cim) {
        $tab_cim[$cim['CIM10_id']] = $cim['CIM10_CFTMEA_code'];
    }
    return $tab_cim;
}

//recupère les CFTMEA axe2
function Liste_CFTMEA_Axe2($var = NULL) {
    global $pdo;
    $requete = $pdo->query("SELECT CIM10_id, CIM10_CFTMEA_code FROM `CIM10` WHERE CIM10_CFTMEA_axe = 2 ");
    $data = $requete->fetchall(PDO::FETCH_ASSOC);
    $requete->closeCursor();
    foreach ($data as $cim) {
        $tab_cim[$cim['CIM10_id']] = $cim['CIM10_CFTMEA_code'];
    }
    return $tab_cim;
}

//liste les dossiers d'un projet
function Liste_Dossiers_Projets($id_projet, $date_debut, $date_fin) {
    global $pdo;
    $req = "SELECT projets_enfants_enfant_id, projets_enfants_date_debut, projets_enfants_date_fin FROM projets_enfants WHERE projets_enfants_projet_id = " . $id_projet . " AND projets_enfants_date_debut <= '" . $date_fin . "' AND projets_enfants_date_fin >= '" . $date_debut . "' ";
    $requete = $pdo->query($req);
    $data = $requete->fetchall(PDO::FETCH_ASSOC);
    $requete->closeCursor();
    return $data;
}

//liste les pathologies principales
function Liste_Pathologies_Principales($archive) {
    global $pdo;
    if ($archive == 'oui') {
        $SQL = 'WHERE liste_pathologie_principale_archive <> "x" ';
    }
    $requete = $pdo->query("SELECT * FROM liste_pathologie_principale " . $SQL . "");
    $data = $requete->fetchall(PDO::FETCH_ASSOC);
    $requete->closeCursor();
    return $data;
}

//recherche d'une pathologie principale
function Get_Pathologie_Principale($id) {
    global $pdo;
    $requete = $pdo->query("SELECT * FROM liste_pathologie_principale WHERE liste_pathologie_principale_id = '" . $id . "'");
    $data = $requete->fetch(PDO::FETCH_ASSOC);
    $requete->closeCursor();
    return $data['liste_pathologie_principale_nom'];
}

//liste des facteurs d'environnement
function Liste_Facteur_Environnement($archive) {
    global $pdo;
    if ($archive == 'oui') {
        $SQL = 'WHERE liste_facteur_environnement_archive <> "x" ';
    }
    $requete = $pdo->query("SELECT * FROM liste_facteur_environnement " . $SQL . "");
    $data = $requete->fetchall(PDO::FETCH_ASSOC);
    $requete->closeCursor();
    return $data;
}

//recherche d'un facteur d'environnement
function Get_Facteur_Environnement($id) {
    global $pdo;
    $requete = $pdo->query("SELECT * FROM liste_facteur_environnement WHERE liste_facteur_environnement_id = '" . $id . "'");
    $data = $requete->fetch(PDO::FETCH_ASSOC);
    $requete->closeCursor();
    return $data['liste_facteur_environnement_nom'];
}

//recherche d'un facteur d'environnement
function Get_Facteur_Environnement_Code($id) {
    global $pdo;
    $requete = $pdo->query("SELECT * FROM liste_facteur_environnement WHERE liste_facteur_environnement_id = '" . $id . "'");
    $data = $requete->fetch(PDO::FETCH_ASSOC);
    $requete->closeCursor();
    return $data['liste_facteur_environnement_code'];
}

//recherche d'une fonction/profession en rapport à une fonction particulière
function Get_Fonction_Personnel($str_fonctions, $id_personnel) {
    global $pdo;
    $requete = $pdo->query("SELECT COUNT(*) as NB FROM liste_fonctions, personnel WHERE personnel_profession = liste_fonctions_id AND personnel_id = '" . $id_personnel . "' AND liste_fonctions_id IN ( " . $str_fonctions . " ) ");
    $data = $requete->fetch(PDO::FETCH_ASSOC);
    $requete->closeCursor();
    return ($data['NB'] > 0 ) ? 'YES' : 'NO';
}

//recherche de personnes suivant une liste de fonctions
function Get_Personnel_Type($str_fonctions) {
    global $pdo;
    $liste_personnel = NULL;
    $requete = $pdo->query("SELECT personnel_id FROM personnel WHERE personnel_profession IN ( " . $str_fonctions . " ) ");
    $data = $requete->fetchall(PDO::FETCH_ASSOC);
    $requete->closeCursor();
    foreach ($data as $tab_personnel) {
        $liste_personnel .= $tab_personnel['personnel_id'] . ",";
    }
    $liste_personnel = trim($liste_personnel, ",");
    return $liste_personnel;
}

//recherche de personnes suivant un regroupement
function Get_Personnel_Regroupement($regroupement, $contrat, $metier) {
    global $pdo;
    $SQL = NULL;
    $SQL .= (!empty($contrat)) ? ' AND personnel_etp_contrat = "' . $contrat . '" ' : NULL;
    $SQL .= (!empty($metier)) ? ' AND personnel_profession = "' . $metier . '" ' : NULL;
    $req = "SELECT personnel_id 
			FROM personnel 
			LEFT JOIN liste_fonctions ON personnel_profession = liste_fonctions_id
			WHERE liste_fonctions_regroupement = " . $regroupement . " 
			" . $SQL . "
			GROUP BY personnel_id ";
    //debug($req);
    $requete = $pdo->query($req);
    $data = $requete->fetchall(PDO::FETCH_COLUMN);
    $requete->closeCursor();

    return $data;
}

//liste les EP
function Liste_EP($id) {
    global $pdo;
    $req = 'SELECT * FROM entente_prealable WHERE pec_enfant_id =' . $id . ' ORDER BY pec_date_demande';
    $requete = $pdo->query($req);
    $data = $requete->fetchall(PDO::FETCH_ASSOC);
    $requete->closeCursor();
    return $data;
}

//recherche de la prochaine EP
function Get_Next_EP($id, $col_date, $date) { //$col_date = pec_date_debut ou pec_date_demande
    global $pdo;
    $requete = $pdo->query("SELECT pec_date_debut, pec_date_demande FROM entente_prealable WHERE pec_enfant_id = " . $id . " AND " . $col_date . " > " . $date . " ORDER BY " . $col_date . " LIMIT 1 ");
    $data = $requete->fetch(PDO::FETCH_ASSOC);
    $requete->closeCursor();
    if ($data) {
        if (!empty($data['pec_date_debut'])) {
            return $data['pec_date_debut'];
        } else {
            return $data['pec_date_demande'];
        }
    } else {
        return NULL;
    }
}

//recherche de la dernière EP par date début
function Get_Date_Derniere_EP($id) {
    $CI = & get_instance();
    //global $pdo;
    if ($id) {
        //$query = $CI->db->query("SELECT * FROM entente_prealable WHERE pec_enfant_id = '" . $id . "' conv pec_date_debut DESC LIMIT 1 "); // ???? conv pec_date_debut ????
        $query = $CI->db->query("SELECT * FROM entente_prealable WHERE pec_enfant_id = '" . $id . "' ORDER BY pec_date_debut DESC LIMIT 1 ");
        $data = $query->result_array();

        if ($data) {
            return date("d/m/Y", $data[0]['pec_date_fin']);
        } else {
            return NULL;
        }
    } else {
        return NULL;
    }
    /* ???
    global $pdo;
    $requete = $pdo->query("SELECT * FROM entente_prealable WHERE pec_enfant_id = '" . $id . "' ORDER BY pec_date_debut DESC LIMIT 1 ");
    $data = $requete->fetch(PDO::FETCH_ASSOC);
    $requete->closeCursor();
    if ($data) {
        return $data;
    } else {
        return NULL;
    }*/
}

//recherche de la dernière EP par date demande
function Get_Derniere_EP_2($id) {
    global $pdo;
    $requete = $pdo->query("SELECT * FROM entente_prealable WHERE pec_enfant_id = '" . $id . "' ORDER BY pec_date_demande DESC LIMIT 1 ");
    $data = $requete->fetch(PDO::FETCH_ASSOC);
    $requete->closeCursor();
    if ($data) {
        return $data;
    } else {
        return NULL;
    }
}

//recherche de la date d'EP donné au medecin
function Get_Date_EP($id) {
    global $pdo;
    $requete = $pdo->query("SELECT enfant_details_date_ep FROM enfant_details WHERE enfant_details_enfant_id = '" . $id . "' ");
    $data = $requete->fetch(PDO::FETCH_ASSOC);
    $requete->closeCursor();
    if ($data) {
        return $data['enfant_details_date_ep'];
    } else {
        return NULL;
    }
}

//recherche les infos de fratrie
function Get_Fratrie($id) {
    global $pdo;
    $req = "SELECT enfant_details_fratrie, enfant_details_fratrie_pos, enfant_details_fratrie_notes	 FROM enfant_details WHERE enfant_details_enfant_id = '" . $id . "' ";
    $requete = $pdo->query($req);
    $data = $requete->fetch(PDO::FETCH_ASSOC);
    $requete->closeCursor();
    if ($data) {
        return $data;
    } else {
        return NULL;
    }
}

//recherche d'une fonction/profession
function Get_Fonction($id) {
    global $pdo;
    $requete = $pdo->query("SELECT * FROM liste_fonctions WHERE liste_fonctions_id = '" . $id . "' ");
    $data = $requete->fetch(PDO::FETCH_BOTH);
    $requete->closeCursor();
    return $data['liste_fonctions_libelle'];
}

//Récupère un paramètre soit par l'id ou par le nom
function Get_Parametre($var) {
    $CI = & get_instance();
    if (is_int($var)) {
        $sql = 'SELECT parametres_contenu FROM 1_parametres WHERE parametres_id = ' . $var . ' ';
    } else {
        $sql = 'SELECT parametres_contenu FROM 1_parametres WHERE parametres_nom = "' . $var . '" ';
    }
    $req = $CI->db->query($sql);
    $data = $req->row();
    if ($data) {
        return $data->parametres_contenu;
    } else {
        return NULL;
    }
}

//recherche d'une activité par enfant et discipline
function Get_Date_Activite_Enfant($id, $discipline_id) {
    $CI = & get_instance();
    //global $pdo;
    if ($id and $discipline_id) {
        $query = $CI->db->query("SELECT activite_id, activite_date_debut FROM activite WHERE activite_enfant_id = " . $id . " AND activite_discipline_id=" . $discipline_id . " ");
        $data = $query->result_array();

        if ($data) {
            return date("d/m/Y", $data[0]['activite_date_debut']);
        } else {
            return NULL;
        }
    } else {
        return NULL;
    }
}

//recherche de CIM10 par ID
function Get_CIM10_by_Id($id) {
    global $pdo;
    //debug($id);
    //echo "SELECT *  FROM  `CIM10` WHERE  `CIM10_id` = ".$id." ";
    //if(empty($id)){
    $requete = $pdo->query("SELECT *  FROM  `CIM10` WHERE  `CIM10_id` = " . $id . " ");
    $data = $requete->fetch(PDO::FETCH_ASSOC);
    $requete->closeCursor();
    return $data;
    //}else{
    //	return NULL;
    //}
}

//recherche du nom de CIM10 par code
function Get_CIM10_by_Code($code) {
    global $pdo;
    $requete = $pdo->query("SELECT *  FROM  `CIM10` WHERE  `CIM10_code` = '" . $code . "' ");
    $data = $requete->fetch(PDO::FETCH_ASSOC);
    $requete->closeCursor();
    return $data;
}

//recupère la cellule Excel de la  CIM10
function Get_CIM10_excel_CNSA($id) {
    global $pdo;
    $requete = $pdo->query("SELECT `CIM10_rapport_cnsa` FROM `CIM10` WHERE  `CIM10_id` = '" . $id . "' ");
    $data = $requete->fetch(PDO::FETCH_ASSOC);
    $requete->closeCursor();
    return $data['CIM10_rapport_cnsa'];
}

//recherche des CFTMEA d'un usager
function Get_CFTMEA_Usager($id) {
    global $pdo;
    $requete = $pdo->query("SELECT enfant_medical_mises_axe1,enfant_medical_mises_axe2 FROM enfant_medical WHERE  enfant_medical_enfant_id = '" . $id . "' ");
    $data = $requete->fetch(PDO::FETCH_ASSOC);
    $requete->closeCursor();
    return $data;
}

//recherche du nom de CFTMEA par code
function Get_CFTMEA_by_Code($code) {
    global $pdo;
    $requete = $pdo->query("SELECT CIM10_id, CIM10_CFTMEA_code, CIM10_CFTMEA_nom  FROM  `CIM10` WHERE  `CIM10_CFTMEA_code` = '" . $code . "' ");
    $data = $requete->fetch(PDO::FETCH_ASSOC);
    $requete->closeCursor();
    return $data;
}

//---------------------------------------------------------------------------- STATS
//récupère la date du 1er traitement
function Enfant_traitement_date($enfant_id, $SQL) {
    global $pdo;
    $query = "	SELECT activite_enfant_id, MIN(activite_date_debut) as activite_date_debut 
					FROM activite 
					WHERE activite_enfant_id = '" . $enfant_id . "' 
					AND activite_discipline_id >= '6000' 
					AND activite_type = 'T'
					" . $SQL . "
					";
    //echo $query;
    $requete = $pdo->query($query);
    $resultat = $requete->fetch(PDO::FETCH_ASSOC);
    $requete->closeCursor();
    if (empty($resultat['activite_date_debut'])) {
        return NULL;
    } else {
        return $resultat['activite_date_debut'];
    }
}

//récupère la date du 1er diag
function Enfant_diag_date($enfant_id, $SQL) {
    global $pdo;
    $query = "	SELECT activite_enfant_id, MIN(activite_date_debut) as activite_date_debut 
					FROM activite 
					WHERE activite_enfant_id = '" . $enfant_id . "' 
					AND activite_type = 'D'
					" . $SQL . "
					";
    //echo $query;
    $requete = $pdo->query($query);
    $resultat = $requete->fetch(PDO::FETCH_ASSOC);
    $requete->closeCursor();
    if (empty($resultat['activite_date_debut'])) {
        return NULL;
    } else {
        return $resultat['activite_date_debut'];
    }
}

//récupère la date du 1er diag
function Calcul_Frequence_Activite($date_debut, $date_fin, $tab_dossiers_suivis) {
    global $pdo;
    $query = "	SELECT activite_enfant_id, COUNT(activite_id) as NB 
					FROM activite
					LEFT JOIN discipline ON activite_discipline_id = discipline_id
					WHERE activite_enfant_id IN (" . implode(",", $tab_dossiers_suivis) . ")
					AND activite_date_debut >= " . $date_debut . "
					AND activite_date_fin <= " . $date_fin . "
					AND discipline_aupres_enfant = 'oui'
					GROUP BY activite_enfant_id
					";
    //echo $query;
    $requete = $pdo->query($query);
    $resultat = $requete->fetchall(PDO::FETCH_ASSOC);
    $requete->closeCursor();

    return $resultat;
}

function Nb_activite_regroupement($tab_services, $discipline_regroupement, $date_debut, $date_fin, $SQL) {
    global $pdo;
    $query = "SELECT COUNT(discipline_id) as nb_activite
					FROM  activite
					LEFT JOIN discipline ON activite_discipline_id = discipline_id
					WHERE activite_service_id IN (" . implode(",", $tab_services) . ") 
					AND discipline_regroupement = '" . $discipline_regroupement . "'
					AND activite_date_debut >= '" . $date_debut . "'
					AND activite_date_fin <= '" . $date_fin . "'
					AND activite_absence = ''
					" . $SQL . "
					";
    //echo $query;
    //echo $discipline_regroupement.': ';
    $requete = $pdo->query($query);
    $resultat = $requete->fetch(PDO::FETCH_ASSOC);
    //debug($resultat).'<br>';
    return $resultat['nb_activite'];
}

function Nb_dossiers_regroupement($tab_services, $discipline_regroupement, $date_debut, $date_fin, $SQL) {
    global $pdo;
    $query = "SELECT COUNT(activite_enfant_id) as nb_dossiers
					FROM  activite
					LEFT JOIN discipline ON activite_discipline_id = discipline_id
					WHERE activite_service_id IN (" . implode(",", $tab_services) . ") 
					AND discipline_regroupement = '" . $discipline_regroupement . "'
					AND activite_date_debut >= '" . $date_debut . "'
					AND activite_date_fin <= '" . $date_fin . "'
					AND activite_absence = ''
					GROUP BY activite_enfant_id
					" . $SQL . "
					";
    //echo $query; exit;
    $requete = $pdo->query($query);
    $resultat = $requete->fetchall(PDO::FETCH_ASSOC);
    return count($resultat);
}

function Nb_activite_lieu($tab_services, $date_debut, $date_fin, $SQL) {
    global $pdo;
    $query = "SELECT COUNT(activite_id) as nb_activite, liste_lieu_activite_libelle, liste_lieu_activite_id
					FROM  activite
					LEFT JOIN liste_lieu_activite ON activite_lieu = liste_lieu_activite_id
					WHERE activite_service_id IN (" . implode(",", $tab_services) . ") 
					AND activite_date_debut >= '" . $date_debut . "'
					AND activite_date_fin <= '" . $date_fin . "'
					AND activite_enfant_id <> '0'
					AND activite_absence = ''
					" . $SQL . "
					GROUP BY activite_lieu
					";
    //echo $query;
    $requete = $pdo->query($query);
    $resultats = $requete->fetchall(PDO::FETCH_ASSOC);
    foreach ($resultats as $key => $resultat) {
        $data[$resultat['liste_lieu_activite_id']] = $resultat['nb_activite'];
        $data[$resultat['liste_lieu_activite_libelle']] = $resultat['nb_activite'];
    }

    return $data;
}

//contruction d'un tableau des groupes
function Tableau_groupes($tab_services, $date_debut, $date_fin, $SQL) {
    global $pdo;
    $requete_verif_groupe = "SELECT activite_enfant_multiple_num, COUNT(*) AS Q FROM activite 
								WHERE activite_date_debut >= " . $date_debut . "
								AND activite_date_fin <= " . $date_fin . "
								AND activite_service_id IN ('" . implode("','", $tab_service) . "') 
								GROUP BY activite_enfant_multiple_num ";
    $req_verif_groupe = $pdo->query($requete_verif_groupe);
    $res_verif_groupe = $req_verif_groupe->fetchall(PDO::FETCH_OBJ);
    foreach ($res_verif_groupe as $ligne_groupe) {
        $tab_groupe[$ligne_groupe->activite_enfant_multiple_num] = $ligne_groupe->Q;
    }
    $req_verif_groupe->closeCursor();
    return $tab_groupe;
}

function Nb_activite_lieu_intervention($tab_services, $date_debut, $date_fin, $SQL) {
//calcul pour le CNSA, pour Ets Scolaire retirer ESS (discipline regroupement : 50)
    global $pdo;
    $query = "SELECT COUNT(activite_id) as nb_activite, activite_enfant_multiple_num, activite_therapeute, activite_lieu, liste_lieu_activite_id
					FROM  activite
					LEFT JOIN liste_lieu_activite ON activite_lieu = liste_lieu_activite_id
					LEFT JOIN discipline ON discipline_id = activite_discipline_id
					WHERE activite_service_id IN (" . implode(",", $tab_services) . ") 
					AND activite_date_debut >= '" . $date_debut . "'
					AND activite_date_fin <= '" . $date_fin . "'
					AND activite_enfant_id <> '0'
					AND activite_absence = ''
					AND discipline_regroupement <> '50'
					" . $SQL . "
					GROUP BY activite_enfant_multiple_num, activite_lieu
					ORDER BY  activite_enfant_multiple_num
					";
    //echo $query;
    $requete = $pdo->query($query);
    $resultats = $requete->fetchall(PDO::FETCH_ASSOC);
    //debug($resultats);

    $tab_groupe = Tableau_groupes($tab_services, $date_debut, $date_fin, $SQL);

    foreach ($resultats as $key => $activite) {
        //si le num de récurrence est 0, indiquer le nb retourné
        if ($activite['activite_enfant_multiple_num'] == '0') {
            $data[$activite['activite_lieu']] = $activite['nb_activite'];
        } else {
            $data[$activite['activite_lieu']] ++;
        }
    }
    return $data;
}

//---------------------------------------------------------------------------- STATS
//Ajout des données ETP
function Insert_ETP($type, $personnel_id, $etp_service_id, $date_debut, $date_fin, $etp, $hc, $hs, $AN_plus, $AN_moins, $jours_feries, $maladie, $observations) {
    global $pdo;

    $requete = $pdo->exec('INSERT INTO etp_base ( 
	etp_base_type, etp_base_personnel_id, etp_base_etp_service_id, etp_base_date_debut, etp_base_date_fin, etp_base_etp, etp_base_HC, etp_base_HS, etp_base_AN_plus, etp_base_AN_moins, etp_base_jours_feries, etp_base_maladie, etp_base_observations ) 
	VALUE (
	"' . $type . '", "' . $personnel_id . '", "' . $etp_service_id . '", "' . $date_debut . '", "' . $date_fin . '", "' . $etp . '", "' . $hc . '", "' . $hs . '", "' . $AN_plus . '", "' . $AN_moins . '", "' . $jours_feries . '", "' . $maladie . '", "' . $observations . '" 
	) ');
    //$requete -> closeCursor();
    return $requete;
}

//pour debugger
/* if (!$requete) {
  echo "\nPDO::errorInfo():\n";
  print_r($pdo->errorInfo());
  } */


//converti les accents
function cleanText($str) {
    $str = str_replace('\'', '', $str);
    $str = str_replace('\\', '', $str);
    $str = str_replace("Ñ", "&#209;", $str);
    $str = str_replace("ñ", "&#241;", $str);
    $str = str_replace("ñ", "&#241;", $str);
    $str = str_replace("Á", "&#193;", $str);
    $str = str_replace("á", "&#225;", $str);
    $str = str_replace("à", "&agrave;", $str);
    $str = str_replace("É", "&#201;", $str);
    $str = str_replace("é", "&eacute;", $str);
    $str = str_replace("ú", "&#250;", $str);
    $str = str_replace("ù", "&#249;", $str);
    $str = str_replace("Í", "&#205;", $str);
    $str = str_replace("ï", "&iuml;", $str);
    $str = str_replace("í", "&#237;", $str);
    $str = str_replace("Ó", "&#211;", $str);
    $str = str_replace("ó", "&#243;", $str);
    $str = str_replace("ö", "&ouml;", $str);
    $str = str_replace("“", "&#8220;", $str);
    $str = str_replace("”", "&#8221;", $str);

    $str = str_replace("‘", "&#8216;", $str);
    $str = str_replace("’", "&#8217;", $str);
    $str = str_replace("—", "&#8212;", $str);

    $str = str_replace("'", "", $str);

    $str = str_replace("–", "&#8211;", $str);
    $str = str_replace("™", "&trade;", $str);
    $str = str_replace("ü", "&#252;", $str);
    $str = str_replace("Ü", "&#220;", $str);
    $str = str_replace("Ê", "&#202;", $str);
    $str = str_replace("ê", "&ecirc;", $str);
    $str = str_replace("Ç", "&#199;", $str);
    $str = str_replace("ç", "&ccedil;", $str);
    $str = str_replace("È", "&#200;", $str);
    $str = str_replace("è", "&egrave;", $str);
    $str = str_replace("ë", "&euml;", $str);
    $str = str_replace("•", "&#149;", $str);

    $str = str_replace("¼", "&#188;", $str);
    $str = str_replace("½", "&#189;", $str);
    $str = str_replace("¾", "&#190;", $str);
    $str = str_replace("½", "&#189;", $str);

    return $str;
}

//ajout d'un espace entre chaque charactère
function AjoutEspace($str, $num_char, $nb_spc) {
    $x = 0;
    $spc = NULL;
    while ($x < $nb_spc) {
        $spc .= '&nbsp;';
        $x++;
    }
    $str = chunk_split($str, $num_char, $spc);
    return $str;
}

//supprime les accents
function suppAccent($str) {
    $str = strtr($str, 'ÁÀÂÄÃÅÇÉÈÊËÍÏÎÌÑÓÒÔÖÕÚÙÛÜÝ', 'AAAAAACEEEEEIIIINOOOOOUUUUY');

    $str = strtr($str, 'áàâäãåçéèêëíìîïñóòôöõúùûüýÿ', 'aaaaaaceeeeiiiinooooouuuuyy');
    return $str;
    echo $str;
}

//mise en forme pour les autocomplete
function formatAutocomplete($str) {
    $str = stripslashes(strtoupper(strtr($str, 'àáâãäåòóôõöøèéêëçìíîïùúûüÿñ', 'aaaaaaooooooeeeeciiiiuuuuyn')));

    return $str;
}

/**
 * Supprimer les accents
 * 
 * @param string $str chaîne de caractères avec caractères accentués
 * @param string $encoding encodage du texte (exemple : utf-8, ISO-8859-1 ...)
 */
function suppr_accents($str, $encoding = 'utf-8') {
    // transformer les caractères accentués en entités HTML
    $str = htmlentities($str, ENT_NOQUOTES, $encoding);

    // remplacer les entités HTML pour avoir juste le premier caractères non accentués
    // Exemple : "&ecute;" => "e", "&Ecute;" => "E", "Ã " => "a" ...
    $str = preg_replace('#&([A-za-z])(?:acute|grave|cedil|circ|orn|ring|slash|th|tilde|uml);#', '\1', $str);

    // Remplacer les ligatures tel que : Œ, Æ ...
    // Exemple "Å“" => "oe"
    $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str);
    // Supprimer tout le reste
    $str = preg_replace('#&[^;]+;#', '', $str);

    return $str;
}

//fonction htmlentities
function htmlent($str) {
    $str = htmlentities($str, ENT_QUOTES | ENT_IGNORE, "ISO-8859-1");

    return $str;
}

//Jours d'ouverture d'un service sur une période
function JoursOuvertureFermeture($service_id, $jour_debut, $jour_fin) {
    global $pdo;
    //nombre de jours de fermeture du service
    $req_jour_ferme = $pdo->query('SELECT COUNT(fermeture_date) FROM fermeture
				WHERE fermeture_service_id = "' . $service_id . '"
				AND fermeture_date >= "' . $jour_debut . '"
				AND fermeture_date <= "' . $jour_fin . '"');
    $res_jour_ferme = $req_jour_ferme->fetch(PDO::FETCH_BOTH);
    $nb_jours['fermeture'] = $res_jour_ferme[0];

    //nombre de jours ouvrés sur la période	
    $nb_jours_periode = 0;
    while (date('Y-m-d', $jour_debut) < date('Y-m-d', $jour_fin)) {
        $nb_jours_periode += date('N', $jour_debut) < 6 ? 1 : 0; //ne compte pas les samedi et dimanche
        $jour_debut = strtotime("+1 day", $jour_debut);
    }
    $nb_jours['ouvres'] = $nb_jours_periode;

    $nb_jours['ouvert'] = $nb_jours['ouvres'] - $nb_jours['fermeture'];

    return $nb_jours;
}

function genererMDP() {
    // initialiser la variable $mdp
    $mdp = "";
    $longueur = 7;
    // DÃ©finir tout les caractÃ¨res possibles dans le mot de passe, 
    // Il est possible de rajouter des voyelles ou bien des caractÃ¨res spÃ©ciaux
    $chiffre = "123456789";
    $possible = "123456789bcdfghjkmnpqrtvwxyzBCDFGHJKLMNPQRTVWXYZ";
    // obtenir le nombre de caractÃ¨res dans la chaÃ®ne prÃ©cÃ©dente
    // cette valeur sera utilisÃ© plus tard
    $longueurMax = strlen($possible);
    if ($longueur > $longueurMax) {
        $longueur = $longueurMax;
    }
    // initialiser le compteur
    $i = 0;
    // ajouter un caractÃ¨re alÃ©atoire Ã  $mdp jusqu'Ã  ce que $longueur soit atteint
    while ($i < $longueur) {
        // prendre un caractÃ¨re alÃ©atoire
        $caractere = substr($possible, mt_rand(0, $longueurMax - 1), 1);
        // vÃ©rifier si le caractÃ¨re est dÃ©jÃ  utilisÃ© dans $mdp
        if (!strstr($mdp, $caractere)) {
            // Si non, ajouter le caractÃ¨re Ã  $mdp et augmenter le compteur
            $mdp .= $caractere;
            $i++;
        }
    }
    $mdp = $mdp . substr($chiffre, mt_rand(0, 8), 1);
    // retourner le rÃ©sultat final
    return $mdp;
}

function f_crypt($str_to_crypt) {
    $private_key = md5("gpa");
    $letter = -1;
    $new_str = '';
    $strlen = strlen($str_to_crypt);
    for ($i = 0; $i < $strlen; $i++) {
        $letter++;
        if ($letter > 31) {
            $letter = 0;
        }
        $neword = ord($str_to_crypt[$i]) + ord($private_key[$letter]);
        if ($neword > 255) {
            $neword -= 256;
        }
        $new_str .= chr($neword);
    }
    return base64_encode($new_str);
}

function f_decrypt($str_to_decrypt) {
    $private_key = md5("gpa");
    $letter = -1;
    $new_str = '';
    $str_to_decrypt = base64_decode($str_to_decrypt);
    $strlen = strlen($str_to_decrypt);
    for ($i = 0; $i < $strlen; $i++) {
        $letter++;
        if ($letter > 31) {
            $letter = 0;
        }
        $neword = ord($str_to_decrypt[$i]) - ord($private_key[$letter]);
        if ($neword < 1) {
            $neword += 256;
        }
        $new_str .= chr($neword);
    }
    return $new_str;
}

/*
 * Transforme une liste de type 1,2,3 en '1','2','3' pour utilsation avec Codeigninter
 * @param       str    $liste    
 * @return      str               
 */

function format_list_requete($liste) {
    $liste_array = explode(",", $liste);
    $liste_tab = "'" . implode("', '", $liste_array) . "'";
    return $liste_tab;
}

function GetParamSMS($service_id) {
    $CI = & get_instance();
    $CI->db->select('*');
    $CI->db->from('sms_mails_param');
    $CI->db->where('sms_mails_param_type_envoi', 'sms');
    $CI->db->where('sms_mails_param_service_id', $service_id);
    $query = $CI->db->get();
    $data = $query->row_array();
    return $data;
}

function EnvoiSMS($tel, $message, $sender, $mode_demo) {
    //-------config-------
    if ($mode_demo) {
        $url = 'https://api.allmysms.com/http/9.0/simulateCampaign/'; //pour tests
    } else {
        $url = 'https://api.allmysms.com/http/9.0/sendSms/'; //production    
    }

    if (CLIENT == "DEV") {
        $login = 'gpamanagement';    // identifant allmysms spécifique
    } elseif (CLIENT == "ADMANCHE") {
        $login = 'admanche';    // identifant allmysms spécifique
    } elseif (CLIENT == "CMPP14") {
        $login = 'cmpp14';    // identifant allmysms spécifique
    } elseif (CLIENT == "PEP76") {
        $login = 'gpademo';    // test temporaire
    } else {
        $login = 'gpa' . strtolower(CLIENT); // identifant allmysms avec gpa devant
    }
    //votre mot de passe allmysms
    $apiKey = Get_Parametre('sms cle api');    //votre mot de passe allmysms

    $message = $message;    //le message SMS, attention pas plus de 160 caractères
    $sender = $sender;  //l'expediteur, attention pas plus de 11 caractères alphanumériques
    $msisdn = $tel;    //numéro de téléphone du destinataire
    $returnformat = 'json';
    $smsData = "<DATA>
   <MESSAGE><![CDATA[" . $message . "]]></MESSAGE>
   <TPOA>$sender</TPOA>
   <SMS>
      <MOBILEPHONE>$msisdn</MOBILEPHONE>
   </SMS>
</DATA>";

    $fields = array(
        'login' => urlencode($login),
        'apiKey' => urlencode($apiKey),
        'smsData' => urlencode($smsData),
        'returnformat' => $returnformat
    );

    $fieldsString = "";
    foreach ($fields as $key => $value) {
        $fieldsString .= $key . '=' . $value . '&';
    }
    rtrim($fieldsString, '&');



    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, count($fields));
    curl_setopt($curl, CURLOPT_POSTFIELDS, $fieldsString);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $result = curl_exec($curl);
    
    curl_close($curl);
    if ($result === FALSE) {
        echo '<br>Une erreure est survenue: ' . curl_error($curl) . PHP_EOL;
        $result['status'] = "0";
    }

    $result_json = json_decode($result);
    return $result_json;
}

/*
 * Fonction récupération champs spécifiques d'usager adulte
 * @param int $usager_id id de l'usager 
 * @param texte $champs avec liste des champs (champ1,champ2) 
 * @return array avec champs et valeurs
 */

function Get_Usager($usager_id, $champs) {
    $CI = & get_instance();
    $query = $CI->db->query("SELECT " . $champs . " FROM usager WHERE usager_id='" . $usager_id . "'");
    $data = $query->row_array();
    return $data;
}

/**
 * Récupère absence usager par id usager et id activite adulte
 */
function Get_Absence_Usager($activite_id, $usager_id) {
    $CI = & get_instance();
    $query = $CI->db->query('SELECT * FROM usager_absence
    LEFT JOIN usager_liste_motif_absence_usager ON usager_liste_motif_absence_usager_id = usager_absence_motif_id
    WHERE usager_absence_activite_id = ' . $activite_id . ' 
    AND usager_absence_personne_id = ' . $usager_id . ' 
    AND usager_absence_type = "usager" ');
    $data = $query->row_array();
    return $data['usager_liste_motif_absence_usager_libelle'];
}

//------FONCTIONS POUR RUBRIQUES PERSONNALISEES ADULTES-----
function get_toutes_rubriques() {

    //Requete
    $CI = & get_instance();
    $CI->db->select('*');
    $CI->db->from('usager_rubrique_contenu,usager_rubrique');
    $CI->db->where('usager_rubrique_contenu_rubrique_id = usager_rubrique_id');
    $query = $CI->db->get();
    $data = $query->result();

    foreach ($data as $rubrique) {
        //$tab_questionnaire = json_decode($rubrique->usager_rubrique_json);
        $tab_resultat = json_decode($rubrique->usager_rubrique_contenu_json);
        $liste_rubriques[$rubrique->usager_rubrique_contenu_usager_id]['services'] = $rubrique->usager_rubrique_services_id;
        $liste_rubriques[$rubrique->usager_rubrique_contenu_usager_id]['rubriques'][$rubrique->usager_rubrique_nom] = $tab_resultat;
    }
    //debug($liste_rubriques);
    if (!empty($liste_rubriques)) {
        return $liste_rubriques;
    } else {
        return NULL;
    }
}

/**
 * Fonction récupétation de toutes les données des rubriques personnalisées pour un usager
 * @param int $usager_id
 * @return array
 */
function get_rubriques_usager($usager_id = NULL) {
    if ($usager_id == NULL) {
        echo "usager_id requis";
        exit();
    }
    //Requete
    $CI = & get_instance();
    $CI->db->select('*');
    $CI->db->from('usager_rubrique_contenu,usager_rubrique');
    $CI->db->where('usager_rubrique_contenu_rubrique_id = usager_rubrique_id');
    $CI->db->where('usager_rubrique_contenu_usager_id', $usager_id);
    $query = $CI->db->get();
    $data = $query->result();

    foreach ($data as $rubrique) {
        //$tab_questionnaire = json_decode($rubrique->usager_rubrique_json);
        $tab_resultat = json_decode($rubrique->usager_rubrique_contenu_json);
        $liste_rubriques[$rubrique->usager_rubrique_contenu_usager_id]['services'] = $rubrique->usager_rubrique_services_id;
        $liste_rubriques[$rubrique->usager_rubrique_contenu_usager_id]['rubriques'][$rubrique->usager_rubrique_nom] = $tab_resultat;
    }
    //debug($liste_rubriques);
    if (!empty($liste_rubriques)) {
        return $liste_rubriques;
    } else {
        return NULL;
    }
}

/**
 * Fonction récupétation de toutes les données des rubriques personnalisées pour un usager et par serviceS
 * @param int $usager_id
 * @param text $tab_services_id ex: "1,45"
 * @return array
 */
function get_rubriques_usager_services($usager_id = NULL, $tab_services_id = NULL) {
    if ($usager_id == NULL) {
        echo "usager_id requis";
        exit();
    }
    if ($tab_services_id == NULL) {
        echo "tab_services_id requis";
        exit();
    }
    //Requete
    $CI = & get_instance();
    $CI->db->select('*');
    $CI->db->from('usager_rubrique_contenu,usager_rubrique');
    $CI->db->where('usager_rubrique_contenu_rubrique_id = usager_rubrique_id');
    $CI->db->where('usager_rubrique_contenu_usager_id', $usager_id);
    $SQL = "";
    foreach (explode(",", $tab_services_id) as $service_id) {
        $SQL .= " FIND_IN_SET(" . $service_id . ",usager_rubrique_services_id)>0 OR ";
    }
    $SQL = rtrim($SQL, "OR ");
    $this->db->where("(" . $SQL . ")");
    $query = $CI->db->get();
    $data = $query->result();
    foreach ($data as $rubrique) {
        //$tab_questionnaire = json_decode($rubrique->usager_rubrique_json);
        $tab_resultat = json_decode($rubrique->usager_rubrique_contenu_json);
        $liste_rubriques[$rubrique->usager_rubrique_contenu_usager_id]['services'] = $rubrique->usager_rubrique_services_id;
        $liste_rubriques[$rubrique->usager_rubrique_contenu_usager_id]['rubriques'][$rubrique->usager_rubrique_nom] = $tab_resultat;
    }
    //debug($liste_rubriques);
    if (!empty($liste_rubriques)) {
        return $liste_rubriques;
    } else {
        return NULL;
    }
}

/**
 * Fonction récupétation de toutes les données des rubriques personnalisées pour un usager
 * @param int $usager_id
 * @param text $rubrique_nom
 * @param text $question
 * @return array avec 1 ou plusieurs réponses 
 */
function get_reponse_rubrique($usager_id = NULL, $rubrique_nom = NULL, $question = NULL) {

    if ($usager_id == NULL) {
        echo "usager_id requis";
        exit();
    }
    if ($rubrique_nom == NULL) {
        echo "rubrique_nom requis";
        exit();
    }
    if ($question == NULL) {
        echo "question requis";
        exit();
    }
    //Requete
    $CI = & get_instance();
    $CI->db->select('usager_rubrique_contenu_json');
    $CI->db->from('usager_rubrique_contenu,usager_rubrique');
    $CI->db->where('usager_rubrique_contenu_rubrique_id = usager_rubrique_id');
    $CI->db->where('usager_rubrique_contenu_usager_id', $usager_id);
    $CI->db->where('usager_rubrique_nom', $rubrique_nom);
    $query = $CI->db->get();
    $data = $query->row();
    $reponses = json_decode($data->usager_rubrique_contenu_json);


    if (!empty($reponses->$question)) {
        if (!is_array($reponses->$question)) {
            $data_reponses = [$reponses->$question];
        } else {
            $data_reponses = $reponses->$question;
        }
        return $data_reponses;
    } else {
        return NULL;
    }
}

//------/FONCTIONS POUR RUBRIQUES PERSONNALISEES ADULTES-----

/*
 * FONCTIONS DE RECUPERATION DES DONNEES DES RUBRIQUES PERSONNALISEES
 * @param string $type_usager enfant (defaut) / usager (pour adulte)
 * @param int $ids_services 1,2,... si omi utilistation des services de l'utilisateur
 * @param string $noms_rubriques Rubrique 1,Rubrique 2,...
 * @param string $noms_onglets INFOS,DETAILS,...
 * @param string $retour type de retour en array (defaut) ou en tableau HTML
 * @return array contenant entete ou lignes du tableau en array ou en tableau HTML
 */
function creationTableauRubriques($type_usager = 'enfant', $ids_services = null, $noms_rubriques = null, $noms_onglets = null, $retour = 'array') {
    //Récupération données entête
    $CI = & get_instance();
    //Tests paramètres envoyés
    //si  $type_usager = "adulte" récupération données adultes
    if ($type_usager === "adulte" or $type_usager === "enfant") {
        $CI->db->select('*');
        $CI->db->from($type_usager . '_rubrique');
        //selection des services
        $SQL_services = "";
        if ($ids_services != null) { //si des id de services sont demandés
            foreach (explode(",", $ids_services) as $service_id) {
                $SQL_services .= " FIND_IN_SET(" . $service_id . "," . $type_usager . "_rubrique_services_id)>0 OR ";
            }
            $SQL_services = rtrim($SQL_services, "OR ");
            $CI->db->where("(" . $SQL_services . ")");
        } else { //sinon selection des services de l'utilisateur
            foreach (explode(",", $_SESSION['services']) as $service_id) {
                $SQL_services .= " FIND_IN_SET(" . $service_id . "," . $type_usager . "_rubrique_services_id)>0 OR ";
            }
            $SQL_services = rtrim($SQL_services, "OR ");
            $CI->db->where("(" . $SQL_services . ")");
        }
        //selection des rubriques
        if ($noms_rubriques != null) { //si des id de rubriques sont demandés
            $SQL_rubriques = "";
            foreach (explode(",", urldecode($noms_rubriques)) as $nom_rubrique) {

                $SQL_rubriques .= " FIND_IN_SET('" . $nom_rubrique . "'," . $type_usager . "_rubrique_nom)>0 OR ";
            }
            $SQL_rubriques = rtrim($SQL_rubriques, "OR ");
            $CI->db->where("(" . $SQL_rubriques . ")");
        }
        //selection des onglets
        if ($noms_onglets != null) { //si des id de rubriques sont demandés
            $SQL_onglets = "";
            foreach (explode(",", urldecode($noms_onglets)) as $nom_onglet) {
                $SQL_onglets .= " FIND_IN_SET('" . $nom_onglet . "'," . $type_usager . "_rubrique_onglet)>0 OR ";
            }
            $SQL_onglets = rtrim($SQL_onglets, "OR ");
            $CI->db->where("(" . $SQL_onglets . ")");
        }
        $query = $CI->db->get();
        //debug($CI->db->last_query());
        $tab_questionnaires = $query->result_array();
    } else {
        return "Error 400 : variable $type_usager manquante ou incorrecte doit être 'enfant' ou 'adulte'";
    }
    //Formatage entête tableau
    $entete = "<tr>";
    $entete_simple = array();
    $titres = "oui";
    $liste_id_rubriques = array();
    foreach ($tab_questionnaires as $questionnaire) {
        $liste_id_rubriques[] = $questionnaire['enfant_rubrique_id'];
        $obj_json_questionnaire = json_decode($questionnaire['enfant_rubrique_json']);
        if ($titres == "oui") {
            foreach ($obj_json_questionnaire->pages as $rubrique) {
                foreach ($rubrique->elements as $libelle => $valeur) {
                    if ($valeur->type === "multipletext") {
                        //$entete .= "<th>" . $questionnaire['enfant_rubrique_nom'] . " " . $valeur->name . "</th>";
                        if (isset($valeur->items)) {
                            foreach ($valeur->items as $item) {
                                $entete .= "<th>" . $questionnaire['enfant_rubrique_nom'] . " " . $valeur->name . " " . $item->name . "</th>";
                                $entete_simple[] .= $questionnaire['enfant_rubrique_nom'] . " " . $valeur->name . " " . $item->name;
                            }
                        }
                    } else {
                        $entete .= "<th>" . $questionnaire['enfant_rubrique_nom'] . " " . $valeur->name . "</th>";
                        $entete_simple[] .= $questionnaire['enfant_rubrique_nom'] . " " . $valeur->name;
                        if (isset($valeur->items)) {
                            foreach ($valeur->items as $item) {
                                $entete .= "<th>" . $questionnaire['enfant_rubrique_nom'] . " " . $item->name . "</th>";
                                $entete_simple[] .= $questionnaire['enfant_rubrique_nom'] . " " . $item->name;
                            }
                        }
                    }
                }
            }
        }
    }
    $entete .= "</tr>";
    $entete_tableau = $entete;
    $entete_tableau_simple = $entete_simple;
    //récupération données lignes
    //BDD
    $CI->db->select($type_usager . '_rubrique_contenu_enfant_id,' . $type_usager . '_rubrique_contenu_rubrique_id,' . $type_usager . '_rubrique_contenu_json');
    $CI->db->from($type_usager . '_rubrique_contenu');
    $CI->db->where_in($type_usager . '_rubrique_contenu_rubrique_id', $liste_id_rubriques);
    $query = $CI->db->get();
    $tab_rubriques_contenus = $query->result_array();
    $lignes = array();
    foreach ($tab_rubriques_contenus as $tab_rubriques_contenus) {
        $lignes[$tab_rubriques_contenus[$type_usager . '_rubrique_contenu_enfant_id']][$tab_rubriques_contenus[$type_usager . '_rubrique_contenu_rubrique_id']] = json_decode($tab_rubriques_contenus[$type_usager . '_rubrique_contenu_json']);
    }
    $lignes_tableau = "";
    $lignes_tableau_simple = array();
    //debug($lignes);
    foreach ($lignes as $enfant_id => $ligne) {
        //debug($ligne);
        $lignes_tableau .= "<tr>";
        foreach ($tab_questionnaires as $questionnaire) {
            $liste_id_rubriques[] = $questionnaire['enfant_rubrique_id'];
            $obj_json_questionnaire = json_decode($questionnaire['enfant_rubrique_json']);
            foreach ($obj_json_questionnaire->pages as $rubrique) {
                foreach ($rubrique->elements as $libelle => $valeur) {
                    $nom_champ = $valeur->name;
                    if ($valeur->type === "text") {
                        if (!empty($ligne[$questionnaire[$type_usager . "_rubrique_id"]]->$nom_champ)) {
                            $contenu_champ = $ligne[$questionnaire[$type_usager . "_rubrique_id"]]->$nom_champ;
                            $lignes_tableau .= "<td>" . $contenu_champ . "</td>";
                            $lignes_tableau_simple[$enfant_id][] = $contenu_champ;
                        } else {
                            $lignes_tableau .= "<td></td>";
                            $lignes_tableau_simple[$enfant_id][] = "";
                        }
                    } elseif ($valeur->type === "checkbox") {
                        if (!empty($ligne[$questionnaire[$type_usager . "_rubrique_id"]]->$nom_champ)) {
                            $contenu_champ = $ligne[$questionnaire[$type_usager . "_rubrique_id"]]->$nom_champ;
                            $lignes_tableau .= "<td>";
                            $liste_item = "";
                            foreach ($contenu_champ as $item) {
                                $lignes_tableau .= $item . " ";
                                //$lignes_tableau_simple[$enfant_id][].= $item . " ";
                                $liste_item .= $item . " ";
                            }
                            $lignes_tableau_simple[$enfant_id][] = $liste_item;
                            $lignes_tableau .= "</td>";
                        } else {
                            $lignes_tableau .= "<td></td>";
                            $lignes_tableau_simple[$enfant_id][] = "";
                        }
                    } elseif ($valeur->type === "comment") {
                        if (!empty($ligne[$questionnaire[$type_usager . "_rubrique_id"]]->$nom_champ)) {
                            $contenu_champ = $ligne[$questionnaire[$type_usager . "_rubrique_id"]]->$nom_champ;
                            $lignes_tableau .= "<td>" . $contenu_champ . "</td>";
                            $lignes_tableau_simple[$enfant_id][] = $contenu_champ;
                        } else {
                            $lignes_tableau .= "<td></td>";
                            $lignes_tableau_simple[$enfant_id][] = "";
                        }
                    } elseif ($valeur->type === "radiogroup") {
                        if (!empty($ligne[$questionnaire[$type_usager . "_rubrique_id"]]->$nom_champ)) {
                            $contenu_champ = $ligne[$questionnaire[$type_usager . "_rubrique_id"]]->$nom_champ;
                            $lignes_tableau .= "<td>" . $contenu_champ . "</td>";
                            $lignes_tableau_simple[$enfant_id][] = $contenu_champ;
                        } else {
                            $lignes_tableau .= "<td></td>";
                            $lignes_tableau_simple[$enfant_id][] = "";
                        }
                    } elseif ($valeur->type === "multipletext") {
                        $nom_champ = $valeur->name;
                        if (isset($valeur->items)) {
                            foreach ($valeur->items as $item) {
                                //debug($item);
                                if (!empty($ligne[$questionnaire[$type_usager . "_rubrique_id"]]->$nom_champ)) {
                                    $contenu_champ = $ligne[$questionnaire[$type_usager . "_rubrique_id"]]->$nom_champ;
                                    if (!empty($contenu_champ)) {
                                        $nom_item = $item->name;
                                        if (!empty($contenu_champ->$nom_item)) {
                                            $lignes_tableau .= "<td>" . $contenu_champ->$nom_item . "</td>";
                                            $lignes_tableau_simple[$enfant_id][] = $contenu_champ->$nom_item;
                                        } else {
                                            $lignes_tableau .= "<td></td>";
                                            $lignes_tableau_simple[$enfant_id][] = "";
                                        }
                                    } else {
                                        $lignes_tableau .= "<td></td>";
                                        $lignes_tableau_simple[$enfant_id][] = "";
                                    }
                                } else {
                                    $lignes_tableau .= "<td></td>";
                                    $lignes_tableau_simple[$enfant_id][] = "";
                                }
                            }
                        }
                    } else {
                        $lignes_tableau .= "<td></td>";
                        $lignes_tableau_simple[$enfant_id][] = "";

                        if (isset($valeur->items)) {
                            foreach ($valeur->items as $item) {
                                $lignes_tableau .= "<td></td>";
                                $lignes_tableau_simple[$enfant_id][] = "";
                            }
                        }
                    }
                }
            }
        }
        $lignes_tableau .= "</tr>";
    }
    $tableau = array();
    if (($retour === 'array')) {
        $tableau['entete_simple'] = $entete_simple;
        $tableau['lignes_simple'] = $lignes_tableau_simple;
    } else {
        $tableau['entete'] = $entete;
        $tableau['lignes'] = $lignes_tableau;
    }


    return($tableau);
}

//------/FONCTIONS DE RECUPERATION DES DONNEES DES RUBRIQUES PERSONNALISEES   



/*
 * Fonction préparation des données à afficher avec décryptage et / ou anonymisation
 * @param int $usager_id id de l'usager // plus besoin
 * @param string $usager_texte
 * @param int $usager_service service de l'usager
 * @param string $lien lien ou vide
 * @param string $style style pour le lien ou vide ex: "color:white"
 * @param string $title titre pour le title ou vide ex: "mon titre"
 * @param bool $remplacer pour remplacer ou pas le texte $usager_texte par XXXXXX  
 * @return texte anonymisé et/ou décrypté
 */

function affiche($usager_texte, $usager_service, $lien = NULL, $style = NULL, $title = NULL, $remplacer = TRUE) {

    //Personnel connecté à anonymiser 0 ou 1 
    if (!isset($_SESSION["personnel_anonymiser"]) or $_SESSION["personnel_anonymiser"] == 1) {
        $personnel_anonymiser = 1;
    } else {
        $personnel_anonymiser = 0;
    }
    //mise en array des services du personnel
    $tab_personnel_services = explode(",", $_SESSION["services"]);
    //decrype texte
    // $usager_texte = f_decrypt($usager_texte);  // a decommenter quand cryptage actif


    $nouv_texte = $usager_texte;
    //preparation du code du style
    if ($style) {
        $style = 'style="' . $style . '"';
    } else {
        $style = '';
    }
    //preparation du code du title
    if ($title) {
        $title = 'title="' . $title . '"';
    } else {
        $title = '';
    }
    //Anonymisation si usager pas dans service personnel et/ou si anonymisation avtivé dans sa fiche
    if (!in_array($usager_service, $tab_personnel_services)) {
        if ($remplacer) {
            $nouv_texte = "XXXXX";
        }
    }
    // si anonymisation activé dans sa fiche
    elseif ($personnel_anonymiser) {
        if ($remplacer) {
            $nouv_texte = "XXXXX";
        }
        if ($lien) {//affiche un lien ou pas
            $nouv_texte = '<a href="' . $lien . '" target="_blank" ' . $style . ' ' . $title . '>' . $nouv_texte . '</a>';
        }
    } else {
        if ($lien) {//affiche un lien ou pas
            $nouv_texte = '<a href="' . $lien . '" target="_blank" ' . $style . ' ' . $title . '>' . $nouv_texte . '</a>';
        }
    }
    if (trim($usager_texte) == "") {
        $nouv_texte = "";
    }
    return $nouv_texte;
}

/*
 * Fonction decodage utf8 si nécessaire
 * @param string $texte texte à éventuellement decoder
 * @return texte decodé iso ou même texte utf8
 */

function testutf8($texte) {

    if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/encode_utf8.txt') or file_exists($_SERVER['DOCUMENT_ROOT'] . '/logiciel/encode_utf8.txt')) {
        $nouveau_texte = $texte;
    } else {
        if (!detectUTF8($texte)) {
            $nouveau_texte = utf8_encode($texte);
        } else {
            $nouveau_texte = $texte;
        }
        if (CLIENT == "GPA") {
            $nouveau_texte = $texte;
        }
    }
    return trim($nouveau_texte);
}

function detectUTF8($string) {
    return preg_match('%(?:
        [\xC2-\xDF][\x80-\xBF]        # non-overlong 2-byte
        |\xE0[\xA0-\xBF][\x80-\xBF]               # excluding overlongs
        |[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}      # straight 3-byte
        |\xED[\x80-\x9F][\x80-\xBF]               # excluding surrogates
        |\xF0[\x90-\xBF][\x80-\xBF]{2}    # planes 1-3
        |[\xF1-\xF3][\x80-\xBF]{3}                  # planes 4-15
        |\xF4[\x80-\x8F][\x80-\xBF]{2}    # plane 16
        )+%xs', $string);
}

/*
 * récupère contenu d'un fchier executé avec curl
 * @param string $url texte chemin absolu du fichier
 * @return texte resutlat script/fichier
 */

function curl_get_contents($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_COOKIESESSION, true);
    curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());
    session_write_close();
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

/*
 * Fonction escape les caractères spéciaux pour insertion mysql
 * @param string $value texte "échapper"
 * @return texte "echappé"
 */

function escape($value) {
    $search = array("\\", "\x00", "\n", "\r", "'", '"', "\x1a");
    $replace = array("\\\\", "\\0", "\\n", "\\r", "\'", '\"', "\\Z");

    return str_replace($search, $replace, $value);
}

function debug() {
    $trace = debug_backtrace();
    $rootPath = dirname(dirname(__FILE__));
    $file = str_replace($rootPath, '', $trace[0]['file']);
    $line = $trace[0]['line'];
    $var = $trace[0]['args'][0];
    $lineInfo = sprintf('<div><strong>%s</strong> (line <strong>%s</strong>)</div>', $file, $line);
    $debugInfo = sprintf('<pre>%s</pre>', print_r($var, true));
    if ($_SESSION['MM_Username'] == '1' or $_SESSION['SU'] == 'x') {
        print_r($lineInfo . $debugInfo);
    }
}
