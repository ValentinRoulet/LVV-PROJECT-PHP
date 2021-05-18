//Fonction qui ferme la notification.
//V_boite_archive,V_boite_envoie,V_boite_reception
function temps() {
	window.setTimeout(function() {
		$(".alert").fadeTo(1000).slideDown(500, function() {
			$(this).remove();
		});
	}, 5000);
}