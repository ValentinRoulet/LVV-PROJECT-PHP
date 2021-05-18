var bool;
bool = 0;
$i = 0;

function imprimer_page() {
	window.print();
}

// Ces 3 fonctions permettentde conserver le texte si on change de service, de fonction etc...
function transfert_message_service(){
	var texte = document.getElementById("message").value;
	document.getElementById("text_service").value = texte;
}

function transfert_message_fonction(){
	var texte = document.getElementById("message").value;
	document.getElementById("text_fonction").value = texte;
}

function transfert_message_all(){
	var texte = document.getElementById("message").value;
	document.getElementById("text_all").value = texte;
}

// Fonction qui bloque le bouton pour archiver ou restorer.
// V_boite_archive,V_boite_envoie,V_boite_reception
function debloquer_boutton() {
	var inputs = document.getElementsByTagName('input');
	for (i = 0; i < inputs.length; i++) {
		if (inputs[i].checked == true) {
			document.getElementById('bloquer').disabled = false;
			break;
		}
		document.getElementById('bloquer').disabled = true;
	}
}


// Fonction qui coche ou décoche tous les boutons checkboxs.
// V_boite_archive,V_boite_envoie,V_boite_reception
function allselect() {
	if (bool == 0) {
		document.getElementById('allbutton').innerHTML = '<i class="fa fa-check-square-o"></i>';
		var inputs = document.getElementsByTagName('input');
		for (i = 0; i < inputs.length; i++) {
			if (inputs[i].type == 'checkbox')
				inputs[i].checked = true;
		}
		bool = 1;
	} else {
		document.getElementById('allbutton').innerHTML = '<i class="fa fa-square-o"></i>';
		var inputs = document.getElementsByTagName('input');
		for (i = 0; i < inputs.length; i++) {
			if (inputs[i].type == 'checkbox')
				inputs[i].checked = false;
		}
		bool = 0;
	}
}
// Affiche ou cache 2 zone de saisie pour saisir le sujet et le destinataire
// dans le cas ou vous voulez envoyer un message par mail.
// V_nouveau_message
function changer_div() {
	if ($i == 0) {
		document.getElementById("mail").style.display = "block";
		document.getElementById("change").value = "Annuler";
		$i = 1;
	} else {
		document.getElementById("mail").style.display = "none";
		document.getElementById("change").value = "Envoyer par mail";
		$i = 0;
	}
}