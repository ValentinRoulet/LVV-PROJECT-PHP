<!DOCTYPE html>
<?php 
    use Otp\Otp;
    use Otp\GoogleAuthenticator;
    use ParagonIE\ConstantTime\Encoding;

    require_once('./assets/plugins/otp/Otp.php');
    require_once('./assets/plugins/otp/GoogleAuthenticator.php');
    require_once('./assets/plugins/otp/OtpInterface.php');
    require_once('./assets/plugins/Encoding/Encoding.php');
    require_once('./assets/plugins/Encoding/Base32.php');

?>

<h1>Mon Compte</h1>
<p>bienvenu <?php echo $userInfo['prenom'] . ' ' . $userInfo['nom'] ?></p>

<?php

    //debug($_SESSION);

    if($userInfo['estSimple']){
?>
    <form class="form-horizontal" action="<?php echo base_url(); ?>index.php/totp/c_totp_login/activate_totp">
        <button type="submit" class="btn btn-info">Activer TOTP</button>
    </form>
<?php    
    } else {
?>

    <form class="form-horizontal" action="<?php echo base_url(); ?>index.php/totp/c_totp_login/desactiver_totp">
        <button type="submit" class="btn btn-info">Désactiver TOTP</button>
    </form>

<?php
    }
?>
<br><br><br>

<form class="form-horizontal" action="<?php echo base_url(); ?>index.php/totp/c_totp_login/infos_utilisateur">
    <button type="submit" class="btn btn-info">Voir vos informations</button>
</form>
