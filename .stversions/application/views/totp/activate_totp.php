<h1>TOTP</h1>

<?php 
    use Otp\Otp;
    use Otp\GoogleAuthenticator;

    require_once('./assets/plugins/otp/Otp.php');
    require_once('./assets/plugins/otp/GoogleAuthenticator.php');
    require_once('./assets/plugins/otp/OtpInterface.php');

    $secret = GoogleAuthenticator::generateRandom();

    $qrCode = GoogleAuthenticator::getQrCodeUrl('totp', 'TOTP Test GPA', $secret);
    
    echo $secret;
?>
<br>
<br>

<img src=<?php echo $qrCode; ?>/>