<?php
session_start();

// Manual PHPMailer loading
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Redirect if critical session data is missing
if (!isset($_SESSION['email']) || !isset($_SESSION['otp']) || !isset($_SESSION['otp_expiry'])) {
    $_SESSION['error'] = "Session expired. Please login again.";
    header("Location: login.php");
    exit();
}

$mail = new PHPMailer(true);

try {
    // Server settings (Gmail example)
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'jtkappiyan2003@gmail.com'; // Your Gmail
    $mail->Password   = 'fkuv akrt ohwr kzli';     // Your app password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Recipients
    $mail->setFrom('jtkappiyan2003@gmail.com', 'Employee Portal');
    $mail->addAddress($_SESSION['email']);

    // Email content
    $mail->isHTML(false);
    $mail->Subject = 'Your OTP for Employee Portal Login';
    $mail->Body    = 'Your OTP is: ' . $_SESSION['otp'] . "\n\nValid for 5 minutes.";

    $mail->send();
    
    // Redirect to verification page after successful send
    header("Location: otp_verification.php");
    exit();

} catch (Exception $e) {
    // Log error and redirect back
    file_put_contents('mail_errors.log', date('Y-m-d H:i:s') . ' - ' . $e->getMessage() . "\n", FILE_APPEND);
    $_SESSION['error'] = "Failed to send OTP. Please try again.";
    header("Location: login.php");
    exit();
}
?>