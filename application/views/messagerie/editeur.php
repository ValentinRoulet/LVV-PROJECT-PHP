<?php
// on vérifie toujours qu'il s'agit d'un membre qui est connecté
if ($_SESSION['dataUser'] == null) {
	// si ce n'est pas le cas, on le redirige vers l'accueil
	header ('Location: index.php/index/c_index');
	exit();
}
?>


<!doctype html>
<html>
    <head>
        <!-- TinyMCE script -->
        <script src='<?= base_url() ?>assets/plugins/tinymce/js/tinymce/tinymce.min.js'></script>
    </head>
    <body>
            
            
        <div class="box box-primary with-border col-md-4">
            <form class="form-horizontal" action="<?php echo base_url() . 'index.php/messagerie/c_messagerie' ?>" method="POST">

                <div class="box-body with-border">
                    <!-- Menu déroulant -->
                    <div class="col-sm-10">
                        <select name="categorie" id="Categorie" class="col-sm-2 col-md-offset-6">
                            <!-- Boucle pour récupérer et afficher toutes les catégories de la base de donnée -->
                            <?php foreach ( $Categ as $value) { ?>
                                <option value="<?php echo $value->id_categorie; ?>"><?php echo $value->lib_categorie; ?></option>
                            <?php } 
                            
                                if(isset($categData)) { ?>

                                    <script>
                                    
                                        let element = document.getElementById("Categorie");
                                        element.value = <?php echo $categData[0]->id_categorie_post ?>
                                    
                                    </script>

                            <?php  } ?>
                        </select>

                        <div class="input-group date">
                            <div class="input-group-addon">
                                <i class="far fa-calendar-alt"></i>
                            </div>
                            <input type="text" class="form-control pull-right" name="datetimes" id="datetimes" value='<?php if(isset($Date)) {echo($Date);} else { echo date('d-m-Y H:i:s');} ?>'>
                        </div>

                    </div>
                </div>

                <div class="box-footer with-border">

                    <div class="form-group">
                        <div class="col-md-8 col-md-offset-2">
                            <input type="text" name="message" id="inputMessage" class="form-control editor" value='<?php if(isset($Message)) {echo($Message[0]->message_post);} ?>'>
                        </div>
                        
                        <span class="text-danger"><?php echo form_error('message'); ?></span>
                        
                    </div>

                </div>

                <div class="box-footer text-center">
                    <button type="submit" class="btn btn-info">Valider</button>
                </div>

            </form>
        </div>

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

        <!-- Script du datePicker et de l(heure) -->
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