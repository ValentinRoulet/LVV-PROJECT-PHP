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
<p>bienvenu <?php echo $_SESSION['output'][0]->user_prenom . ' ' . $_SESSION['output'][0]->user_nom ?></p>

<?php 
    if($_SESSION['output'][0]->totp_key == null){
?>
    <form class="form-horizontal" action="<?php echo base_url(); ?>index.php/totp/c_totp_login/activate_totp">
        <button type="submit" class="btn btn-info">Activer TOTP</button>
    </form>
<?php    
    } else {
        $otp = new Otp();

        $currentTotp = $otp->totp(Encoding::base32DecodeUpper($_SESSION['dataUser'][0]->totp_key));
?>
    <br>
    <br>
    <p>Votre clé : <?php echo $currentTotp; ?></p>
<?php
    }
?>
