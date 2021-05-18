<?php
class C_utilitaire extends CI_Controller {
    
	protected $nom_table = null;
	protected $dir_controleur = null;
	
    /**
     * Constructeur de la classe
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url_helper');
		//$this->load->helper('flashData');
        $this->load->library('grocery_CRUD');
        //$this->load->model('sql_utilitaire');
		date_default_timezone_set("Europe/Paris");
		setlocale (LC_TIME, 'fr_FR.utf8','fra');
    }
    
    /**
     * Fonction qui change le format d'un numero de telephone
     * 
     * @param type $tel
     * @return type $tel
     */
    protected function format_tel($tel)	
    {
	$tel = chunk_split($tel,2,' ') ;
	return $tel ;
    }
    
    /**
     * Fonction qui change le format d'une date
     * 
     * @param type $date
     * @return type $newDate
     */
    protected function format_date($date)
    {
        $split = explode(' ', $date);
        $heure_dispo = isset($split[1]);
        
        if (strpos($split[0], '-') !== false) { // Date anglaise
            $date_parser = date_parse_from_format('Y-m-d',$split[0]);
			if ($split[0] != '0000-00-00'){
				$new_date = date('d/m/Y', mktime(0,0,0,$date_parser['month'], $date_parser['day'], $date_parser['year']));
				if ($heure_dispo and ($split[1][0] != '0' or $split[1][1] != '0')){
					$new_date .= " " . substr($split[1],0,-3);
				}
				return $new_date;
			} else {
				return null;
			}
            
        } else if (strpos($split[0], '/') !== false) { // Date francaise
            $date_parser = date_parse_from_format('d/n/Y',$split[0]);
            $new_date = date('Y-m-d', mktime(0,0,0,$date_parser['month'], $date_parser['day'], $date_parser['year']));
            if ($heure_dispo){
                $new_date .= " " . $split[1];
            }
            return $new_date;
        } else {
            return null;
        }
    }
	
	/**
	 * Fonction qui est appelee lors de l'insertion d'un telephone dans le crud
	 */
	public function callback_insert_telephone($post_array,$primary_key)
	{
		$tel_existe = false;
		$insert = 'INSERT INTO `telephone`(`telephone_type`, `telephone_numero`, `telephone_table`, `telephone_table_id`) 
		VALUES ';
		$table = $post_array['table_name'];
		foreach ($post_array as $nom => $valeur) {
			if (strpos($nom, 'telephone_type') !== false) {
				$tel_existe = true;
				$index = substr($nom, -1);
				if (substr($insert, -1) == ')'){
					$insert .= ",('".$post_array[$nom]."', '".$post_array['telephone_num'.$index]."','".$table."','".$primary_key."')";
				} else {
					$insert .= "('".$post_array[$nom]."', '".$post_array['telephone_num'.$index]."','".$table."','".$primary_key."')";
				}
			}
		}
		if ($tel_existe){
			return $this->sql_utilitaire->insert_perso($insert);
		} else {
			return true;
		}
	}
	
	/**
	 * Fonction qui est appelee lors de l'update d'un telephone dans le crud
	 */
	public function callback_update_telephone($post_array,$primary_key)
	{
		$tel_existe = false;
		$table = $post_array['table_name'];
		$insert = 'INSERT INTO `telephone`(`telephone_type`, `telephone_numero`, `telephone_table`, `telephone_table_id`) VALUES ';
		$delete_tel = "DELETE FROM telephone WHERE telephone_table='".$table."' AND telephone_table_id=".$primary_key;
		$this->sql_utilitaire->insert_perso($delete_tel);
		foreach ($post_array as $nom => $valeur) {
			if (strpos($nom, 'telephone_type') !== false) {
				$tel_existe = true;
				$index = preg_replace('/\D/', '', $nom);
				if (substr($insert, -1) == ')'){
					$insert .= ",('".$post_array[$nom]."', '".$post_array['telephone_num'.$index]."','".$table."','".$primary_key."')";
				} else {
					$insert .= "('".$post_array[$nom]."', '".$post_array['telephone_num'.$index]."','".$table."','".$primary_key."')";
				}
			}
		}
		if ($tel_existe){
			return $this->sql_utilitaire->insert_perso($insert);
		} else {
			return true;
		}
		
	}
	
	/**
	 * Fonction qui est appelee pour creer le champ telephone dans le crud
	 */
	public function callback_field_telephone($value = '', $primary_key = null, $fieldinfo = null, $fieldvalues = null)
	{
		$input = "";
		if ($primary_key == null){
			$input = "<div id='telephone'></div>";
			$input .= "<button class='btn btn-default' type='button' onclick='ajout_telephone()'><i class='fa fa-plus'></i></button>";
		} else {
			$input .= "<div id='telephone'>";
			$select_tel = "SELECT telephone_type, telephone_numero FROM telephone WHERE telephone_table='".$fieldinfo->extras."' AND telephone_table_id=".$primary_key;
			$result = $this->sql_utilitaire->select_perso($select_tel);
			$compteur = 1;
			foreach ($result as $ligne){
				$input .= "<div class='row' id='telephone".$compteur."'>";
				$input .= "<div class='col-xs-2'><label class='pull-right' style='margin-top:6px;'>Type</label></div>";
				$input .= "<div class='col-xs-3'>";
				$input .= "<input type='text' name='telephone_type".$compteur."' id='telephone_type".$compteur."' class='form-control' value='".$ligne['telephone_type']."'></div>";
				$input .= "<div class='col-xs-2'><label class='pull-right' style='margin-top:6px;'>Num&eacute;ro</label></div>";
				$input .= "<div class='col-xs-3'>";
				$input .= "<input type='text' style='min-width:100px;' name='telephone_num".$compteur."' id='telephone_num".$compteur."' class='form-control' maxlength='10' value='".$ligne['telephone_numero']."'></div>";
				$input .= "<div class='col-xs-2'><button class='btn btn-default' type='button' onclick='suppr_telephone(".$compteur.")'><i class='fa fa-trash'></i></button></div></div>";
				$compteur++;
			}
			$input .= "</div>";
			$input .= "<button class='btn btn-default' type='button' onclick='ajout_telephone()'><i class='fa fa-plus'></i></button>";
		}
		
		return $input;
	}
	
	/**
	 * Fonction qui est appelee pour creer le champ telephone dans le crud en read
	 */
	public function callback_read_telephone($value = '', $primary_key = null, $fieldinfo = null, $fieldvalues = null)
		{
		$input = "<div id='telephone'>";
		$select_tel = "SELECT telephone_type, telephone_numero FROM telephone WHERE telephone_table='".$fieldinfo->extras."' AND telephone_table_id=".$primary_key;
		
		$result = $this->sql_utilitaire->select_perso($select_tel);
		foreach ($result as $ligne){
			$input .= "<div class='row'>";
			$input .= "<div class='col-xs-3'><span class='pull-right'>";
			$input .= $ligne['telephone_type']."</span></div>";
			$input .= "<div class='col-xs-9'>";
			$input .= $this->format_tel($ligne['telephone_numero'])."</div>";
		}
		$input .= "</div>";
		
		return $input;
	}
	
	/**
	 * Fonction qui est appelee pour creer le champ relation dans le crud
	 */
	public function callback_field_responsable($value = '', $primary_key = null, $fieldinfo = null, $fieldvalues = null)
	{
		$input = "";
		if ($primary_key == null){
			$input = "<div id='relation'></div>";
			$input .= "<button class='btn btn-default' type='button' onclick='ajout_relation()'><i class='fa fa-plus'></i></button>";
		} else {
			
			$select_liens = "SELECT ur.*, r.* FROM usager_responsable ur LEFT JOIN responsable r ON r.responsable_id=ur.id_responsable WHERE id_usager=".$primary_key;
			$liens = $this->sql_utilitaire->select_perso($select_liens);
			
			$select_responsables = 'SELECT * FROM responsable';
			$responsables = $this->sql_utilitaire->select_perso($select_responsables);
			
			$select_relations = 'SELECT * FROM relation';
			$relations = $this->sql_utilitaire->select_perso($select_relations);
			
			$input .= "<div id='relation'>";
			$compteur = 1;
			foreach ($liens as $ligne){
				$input .= "<div class='row' id='relation".$compteur."'>";
				$input .= "<div class='col-xs-6'>";
				$input .= "<select class='chosen-select form-control' name='relation_personne".$compteur."'>";
				$input .= "<option value='null'></option>";
				foreach ($responsables as $resp) {
					if ($resp['responsable_id'] == $ligne['id_responsable']){
						$input .= '<option value="'.$resp['responsable_id'].'" selected>'.$resp['responsable_civilite'].' '.$resp['responsable_nom'].' '.$resp['responsable_prenom'].'</option>';
					} else {
						$input .= '<option value="'.$resp['responsable_id'].'">'.$resp['responsable_civilite'].' '.$resp['responsable_nom'].' '.$resp['responsable_prenom'].'</option>';
					}
				}
				$input .= "</select>";
				$input .= "</div>";
				$input .= "<div class='col-xs-5'>";
				$input .= "<select class='chosen-select form-control' name='relation_nom".$compteur."'>";
				$input .= "<option value='null'></option>";
				foreach ($relations as $rel) {
					if ($rel['relation_id'] == $ligne['usager_responsable_relation']){
						$input .= '<option value="'.$rel['relation_id'].'" selected>'.$rel['relation_nom'].'</option>';
					} else {
						$input .= '<option value="'.$rel['relation_id'].'">'.$rel['relation_nom'].'</option>';
					}
				}
				$input .= "</select>";
				$input .= "</div>";
				$input .= "<div class='col-xs-1'><button class='btn btn-default' type='button' onclick='suppr_relation(".$compteur.")'><i class='fa fa-trash'></i></button></div></div>";
				$compteur++;
			}
			$input .= "</div>";
			$input .= "<button class='btn btn-default' type='button' onclick='ajout_relation()'><i class='fa fa-plus'></i></button>";
		}
		return $input;
	}
	
	/**
	 * Fonction qui est appelee apres un update pour modifier responsable et telephone
	 */
	public function callback_update_tel_resp($post_array,$primary_key)
	{
		$tel_existe = false;
		$table = $post_array['table_name'];
		$insert = 'INSERT INTO `telephone`(`telephone_type`, `telephone_numero`, `telephone_table`, `telephone_table_id`) VALUES ';
		$delete_tel = "DELETE FROM telephone WHERE telephone_table='".$table."' AND telephone_table_id=".$primary_key;
		$this->sql_utilitaire->insert_perso($delete_tel);
		foreach ($post_array as $nom => $valeur) {
			if (strpos($nom, 'telephone_type') !== false) {
				$tel_existe = true;
				$index = preg_replace('/\D/', '', $nom);
				if (substr($insert, -1) == ')'){
					$insert .= ",('".$post_array[$nom]."', '".$post_array['telephone_num'.$index]."','".$table."','".$primary_key."')";
				} else {
					$insert .= "('".$post_array[$nom]."', '".$post_array['telephone_num'.$index]."','".$table."','".$primary_key."')";
				}
			}
		}
		$insert_tel = false;
		if ($tel_existe){
			$insert_tel = $this->sql_utilitaire->insert_perso($insert);
		}
		
		$rel_existe = false;
		$insert = 'INSERT INTO `usager_responsable`(`id_usager`, `id_responsable`, `usager_responsable_relation`) VALUES ';
		$delete = "DELETE FROM usager_responsable WHERE id_usager=".$primary_key;
		$this->sql_utilitaire->insert_perso($delete);
		foreach ($post_array as $nom => $valeur) {
			if (strpos($nom, 'relation_personne') !== false) {
				$rel_existe = true;
				$index = preg_replace('/\D/', '', $nom);
				if (substr($insert, -1) == ')'){
					$insert .= ",(".$primary_key.", ".$post_array[$nom].",".$post_array['relation_nom'.$index].")";
				} else {
					$insert .= "(".$primary_key.", ".$post_array[$nom].",".$post_array['relation_nom'.$index].")";
				}
			}
		}
		$insert_rel = false;
		if ($rel_existe){
			$insert_rel = $this->sql_utilitaire->insert_perso($insert);
		}
		if ($rel_existe && $tel_existe){
			return ($insert_rel !== false && $insert_tel !== false);
		} else if ($rel_existe){
			return $insert_rel;
		} else if ($tel_existe){
			return $insert_tel;
		}  else {
			return true;
		}
	}
	
	/**
	 * Fonction qui est appelee lors de l'insertion pour ajouter telephone et repsonsable
	 */
	public function callback_insert_tel_resp($post_array,$primary_key)
	{
		$tel_existe = false;
		$table = $post_array['table_name'];
		$insert = 'INSERT INTO `telephone`(`telephone_type`, `telephone_numero`, `telephone_table`, `telephone_table_id`) VALUES ';
		foreach ($post_array as $nom => $valeur) {
			if (strpos($nom, 'telephone_type') !== false) {
				$tel_existe = true;
				$index = preg_replace('/\D/', '', $nom);
				if (substr($insert, -1) == ')'){
					$insert .= ",('".$post_array[$nom]."', '".$post_array['telephone_num'.$index]."','".$table."','".$primary_key."')";
				} else {
					$insert .= "('".$post_array[$nom]."', '".$post_array['telephone_num'.$index]."','".$table."','".$primary_key."')";
				}
			}
		}
		$insert_tel = false;
		if ($tel_existe){
			$insert_tel = $this->sql_utilitaire->insert_perso($insert);
		}
		
		$rel_existe = false;
		$insert = 'INSERT INTO `usager_responsable`(`id_usager`, `id_responsable`, `usager_responsable_relation`) VALUES ';
		foreach ($post_array as $nom => $valeur) {
			if (strpos($nom, 'relation_personne') !== false) {
				$rel_existe = true;
				$index = preg_replace('/\D/', '', $nom);
				if (substr($insert, -1) == ')'){
					$insert .= ",(".$primary_key.", ".$post_array[$nom].",".$post_array['relation_nom'.$index].")";
				} else {
					$insert .= "(".$primary_key.", ".$post_array[$nom].",".$post_array['relation_nom'.$index].")";
				}
			}
		}
		$insert_rel = false;
		if ($rel_existe){
			$insert_rel = $this->sql_utilitaire->insert_perso($insert);
		}
		if ($rel_existe && $tel_existe){
			return ($insert_rel !== false && $insert_tel !== false);
		} else if ($rel_existe){
			return $insert_rel;
		} else if ($tel_existe){
			return $insert_tel;
		}  else {
			return true;
		}
	}
	
	/**
	 * Fonction qui sert a ajouter le bouton d'ajout d'une ville dans le crud
	 *
	 */
	protected function callback_field_ville($value = '', $primary_key = null)
	{
		return value;
	}
	
	/**
	 * Fonction qui sert a creer la ged d'un usager si elle n'existe pas
	 */
	protected function ged_usager($cle_usager)
	{
		$dir = "../documents/".CLIENT."/".$cle_usager;
		if (!is_dir($dir)){ 
			$var = mkdir($dir, 0755);
		}
		return true;
	}
	
	/**
	 * Fonction qui sert a creer la ged d'un usager pour un service
	 */
	protected function ged_service($cle_usager,$cle_service)
	{
		$this->ged_usager($cle_usager);
		
		$dir = "../documents/".CLIENT."/".$cle_usager."/".$cle_service.'/';
		if(count(glob($dir."/*")) === 0)
		{
			$select_service = 'SELECT * FROM service WHERE service_id='.$cle_service;
			$details_service = $this->sql_utilitaire->select_perso($select_service)[0];
			$dossiers = explode(',',$details_service['service_dossiers']);
			foreach($dossiers as $key => $dossier)
			{
				$adddir = $dir.$dossier;
				$var = mkdir($adddir, 0755,true);
			}
		}
		return true;
	}
	
	/**
	 * Fonction qui gere l'	archivage et le desarchivage
	 */
	public function archive($mode, $id){
		if ($mode == 'archiver'){
			$update = 'UPDATE '.$this->nom_table.' SET '.$this->nom_table.'_archive="X" WHERE '.$this->nom_table.'_id='.$id;
			$this->sql_utilitaire->query($update);
			logs('Archive', $this->nom_table, $id);
			redirect(site_url($this->dir_controlleur));
		} else {
			$update = 'UPDATE '.$this->nom_table.' SET '.$this->nom_table.'_archive=null WHERE '.$this->nom_table.'_id='.$id;
			$this->sql_utilitaire->query($update);
			logs('Desarchive', $this->nom_table, $id);
			redirect(site_url($this->dir_controlleur.'/index'));
		}
	}
}
?>