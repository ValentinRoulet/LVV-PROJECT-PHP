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
                                            <span class="info-box-number"><?php echo($profils_envoyeur[$x]->message_text) ?></span>
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
            <div name="ListeMessage">
                <div class="box-body chat" name="box message">
                    <!-- Conversations are loaded here -->
                    <div class="direct-chat-messages AreaMessage" name="message area" id='message_area'>
                        <?php $y = 0; ?>
                        <!-- boucle foreach pour récupérer et affecter des trucs pour chaque message de conversation --->
                        <?php if($ActiveConv == true) { ?>
                            <?php foreach ( $conv as $value) { 
                                if($conv[$y]->message_id_receveur == $userId ){ ?>
                                    <!-- Message par la droite par défaut -->
                                    <div class="direct-chat-msg right">
                                        <div class="direct-chat-info clearfix">
                                            <span class="direct-chat-name pull-right"><?php if($conv[$y]->message_id_envoyeur == $userId) {echo $conv_name;} else {echo $conv_name1;} ?></span>
                                            <span class="direct-chat-timestamp pull-left"><?php echo $conv[$y]->message_date ?></span>
                                        </div>
                                        <img class="direct-chat-img" src="dist/img/user3-128x128.jpg" alt="message user image">
                                        <div class="direct-chat-text">
                                            <?php echo($conv[$y]->message_text) ?>
                                        </div>
                                    </div>
                                    <!-- FIN Message par la droite par défaut -->
                                <?php } else { ?>

                                    <!-- Message par la gauche par défaut -->
                                    <div class="direct-chat-msg">
                                        <div class="direct-chat-info clearfix">
                                            <span class="direct-chat-name pull-left"><?php if($conv[$y]->message_id_envoyeur == $userId) {echo $conv_name;} else {echo $conv_name1;} ?></span>
                                            <span class="direct-chat-timestamp pull-right"><?php echo $conv[$y]->message_date ?></span>
                                        </div>
                                        <!-- /.direct-chat-info -->
                                        <img class="direct-chat-img" src="dist/img/user1-128x128.jpg" alt="user image">
                                        <!-- /.direct-chat-img -->
                                        <div class="direct-chat-text">
                                            <?php echo($conv[$y]->message_text) ?>
                                        </div>
                                    </div>
                                    <!-- FIN Message par la gauche par défaut -->
                            <?php } $y = $y + 1;} ?>
                        
                    </div> <!-- Mesage area -->
                </div><!-- Box message -->
            </div><!-- Liste Message -->

            <div name="editeur" class="editeur">
                <form class="form-horizontal" action="<?php echo base_url() . 'index.php/messagerie/c_messagerie/envoyer' ?>" method="POST">
                    <input type="text" name="inputMessage" id="inputMessage" class="editor" autocomplete="off" value=''>
                    
                    <button type="submit" name="submit" id='submit'> Envoyer </button>
                </form>
                <?php } ?>
            </div>
        </div><!--Conteneur droite -->
    </div> <!-- page -->

<!-----------------------------------------FIN MESSAGES---------------------------------->

    
<script type="text/javascript">
    window.onload = function() 
    {
        $("div").scrollTop(1000)
    }
</script>




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

        <!-- Script du datePicker et de l'heure -->
        <script>
            $(function() {
                $('input[name="datetimes"]').daterangepicker({
                    singleDatePicker: true,
                    timePicker: true,
                    showDropdowns: true,
                    locale: true,
                    timePicker24Hour: true,
                    minYear: 2000,
                    
                    maxYear: parseInt(moment().format('YYYY'),10),
                    locale: {
                    format: 'DD-MM-YYYY HH:mm'
                    }
                });
            });
        </script>
    </body>
</html>