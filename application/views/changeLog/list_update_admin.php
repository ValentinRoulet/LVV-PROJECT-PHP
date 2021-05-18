<section class="content">
    <div class="row col-md-offset-3">
        <div class="col-md-8">
            <?php
                // foreach affichage des postes
                foreach($posts as $post){
            ?>

            <div class="box box-primary">
                <!-- titre et date -->
                <div class="box-header with-border">
                    <h1 style="float:left;"><?php echo $post->titre_post; ?></h1>
                    <p style="float:right;"><?php echo date('d/m/Y - H:i', strtotime($post->date_post)); ?></p>
                </div>

                <!-- Catégorie -->
                <div class="box-body">
                    <div class="categorie" id="<?php echo $post->id_post ?>">
                        <p style="color:white;margin:0;padding:0;"><?php echo $categories[$post->id_categorie_post-1]->lib_categorie; ?></p>
                    </div>
                </div>
                <!-- Message -->
                <div class="box-footer">
                    <p><?php echo $post->message_post; ?></p>
                </div>
                
                <div class="box-footer">
                    <!-- Affichage des deux boutons + id_post qui est caché -->
                    <form role="form" action="<?php echo base_url(); ?>index.php/changeLog/c_changelog_posts" method="post">
                        <input type="hidden" name="id_post" id="id_post" value='<?php echo $post->id_post; ?>' >
                        <button type='submit' class='btn btn-primary pull-right no-print' value="Edit" name="edit"  >Éditer </button>
                        <button type='submit' class='btn btn-primary pull-right no-print' value="Supp" name="supp" onclick="return confirm('Voulez vous supprimer?')" style="margin-right:5px;" >Supprimer</button>
                    </form>
                </div>

            </div>
            <br>

            <script> 
                // ajout css a la div de categorie
                document.getElementById("<?php echo $post->id_post ?>").style.borderRadius = "25px"; 
                document.getElementById("<?php echo $post->id_post ?>").style.backgroundColor = "<?php echo $categories[$post->id_categorie_post-1]->hexColorCode; ?>";
                document.getElementById("<?php echo $post->id_post ?>").style.padding = "15px"; 
                document.getElementById("<?php echo $post->id_post ?>").style.width = "200px"; 
                document.getElementById("<?php echo $post->id_post ?>").style.height = "10px"; 
                document.getElementById("<?php echo $post->id_post ?>").style.display = "flex"; 
                document.getElementById("<?php echo $post->id_post ?>").style.alignItems = "center"; 
                document.getElementById("<?php echo $post->id_post ?>").style.justifyContent = "center"; 
            
            </script>
            
            <?php
                }
            ?>
        </div>
    </div>
</section>