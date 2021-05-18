<h1>Informations de l'utilisateur</h1>
<br><br>

<h3>Prénom : <?php echo $userInfo['prenom'] ?></h3>
<h3>Nom : <?php echo $userInfo['nom'] ?></h3>
<h3>Mail : <?php echo $userInfo['mail'] ?></h3>
<h3>
    Double authentification : 

    <?php
        if($userInfo['estSimple']){
    ?>
    Désactivé
    <?php
        }else{
    ?>
    Activé
    <?php
        }
    ?>
</h3>
<br><br><br>

<div class="col-md-6 col-md-offset-3">
    <div class="box box-info">

        <div class="box-header with-border">
            <h3 class="box-title">Partie A</h3>
        </div>

        <div class="box-body">
            Box A
        </div>
    </div>
</div>


<?php
    if(!$userInfo['estSimple']){
?>

    <div class="col-md-6 col-md-offset-3">
        <div class="box box-info">

            <div class="box-header with-border">
                <h3 class="box-title">Partie B</h3>
            </div>

            <div class="box-body">
                Box B (Visible que si la double auth est activé)
            </div>
        </div>
    </div>

<?php
    }
?>