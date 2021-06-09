<?php
// on vérifie toujours qu'il s'agit d'un membre qui est connecté
if ($_SESSION['dataUser'] == null) {
	// si ce n'est pas le cas, on le redirige vers l'accueil
	header ('Location: index.php/index/c_index');
	exit();
}
?>

<!DOCTYPE html>

<html lang="fr">
    <head>
        <meta charset=UTF-8>
    </head>
    <body>
    

    <div name="page" class="page">
        <div name="conteneurG" class="conteneurG">
            <div name="boutonCréer">
                <button type="button" id='btnNew' class="btn btn-block btn-primary btn-lg">Nouveau Message..</button>
            </div> <!-- Bouton Créer -->

<!----------------------------------------PROFILS------------------------------->
            <div name="ListeProfil">
                    <!-- boucle foreach permet de mettre nom prénom pour chaque profils à qui on a parlé ------->
                    <?php $x = 0; ?>
                    <?php foreach ( $profils_envoyeur as $value) { ?>
                        <form class="form-horizontal" action="<?php echo base_url() . 'index.php/messagerie/c_messagerie/conversation' ?>" method="POST">
                            <input type="hidden" name="id_personne" id="id_personne" value='<?php echo $profils_envoyeur[$x]->message_id_envoyeur ?>' >
                            <button class="button" type="submit">
                                <div class="Profils">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-aqua"><i class="ion ion-ios-gear-outline"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text"><?php echo($profils_envoyeur_name[$x]->user_nom . " " . $profils_envoyeur_name[$x]->user_prenom)  ?></span>
                                            <span class="info-box-number"><?php echo($last_message[$x]->message_text) ?></span>
                                            <?php $x = $x + 1 ?>
                                        </div>
                                    </div>
                                </div>
                            </button>
                        </form>
                    <?php } ?>
            </div> <!-- Liste profil -->
<!----------------------------------------FIN PROFILS------------------------------->
        </div><!-- Conteneur gauche -->
<!-----------------------------------------MESSAGES---------------------------------->
        <div name="conteneurD">
            <form class="form-horizontal" action="<?php echo base_url()?>index.php/messagerie/c_messagerie/nouveau" method="POST">
                <select name="id_profils" id="id_profils" class="col-sm-3 col-md-offset-5">
                    <option value=''>Selectionner un utilisateur</option>
                    <!-- Boucle pour récupérer et afficher toutes les catégories de la base de donnée -->
                    <?php $z = 0; ?>
                    <?php foreach ( $profils as $value) { ?>
                        <option value='<?php echo($profils[$z]->user_id) ?>'><?php echo($profils[$z]->user_nom . " " . $profils[$z]->user_prenom) ?> </option>
                        
                        <?php $z = $z + 1; } ?>
                </select>

                    <div name="editeur" class="editeur">
                        <input type="text" name="inputMessage" id="inputMessage" class="editor" autocomplete="off" value=''>
                        <button type="submit" class="btn btn-info">Envoyer</button>
                    </div>
            </form>
        </div><!--Conteneur droite -->
    </div> <!-- page -->

<!-----------------------------------------FIN MESSAGES---------------------------------->





        <!-- Script de l'éditeur de texte -->
        <script>
            tinymce.init({ 
            selector:'.editor',
            theme: 'silver',
            skin: 'gpa',
            height: 600,
            branding: false,
            paste_data_images: true,
            plugins:'image emoticons media table autolink lists autolink lists advlist help link paste',  
            image_list: [
                {title: 'My image 1', value: 'https://d1fmx1rbmqrxrr.cloudfront.net/cnet/i/edit/2019/04/trou-noir-eth-770.jpg'}
            ]
            });
        </script>
        
    </body>
</html>