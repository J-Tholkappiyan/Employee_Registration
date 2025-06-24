<?php
session_start();

// Generate new CAPTCHA
$_SESSION['captcha'] = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 6);

// Output the new CAPTCHA
echo $_SESSION['captcha'];
?>