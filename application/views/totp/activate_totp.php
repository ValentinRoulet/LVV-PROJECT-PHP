<!DOCTYPE html>
<h1>TOTP</h1>

<?php 
    $qrCode = $totp['qrCode'];
?>
<br>
<br>

<?php echo "<img src=" .$qrCode." />";?>