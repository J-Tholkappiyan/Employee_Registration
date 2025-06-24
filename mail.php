<?php
session_start();

// Manual PHPMailer loading
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (isset($_SESSION['otp']) && isset($_SESSION['email'])) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'jtkappiyan2003@gmail.com'; // Your Gmail
        $mail->Password   = 'fkuv akrt ohwr kzli'; // Your app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        // Recipients
        $mail->setFrom('jtkappiyan2003@gmail.com', 'Employee Portal');
        $mail->addAddress($_SESSION['email']);
        
        // Content
        $mail->isHTML(false);
        $mail->Subject = 'Your OTP for Employee Portal Login';
        $mail->Body    = 'Your OTP is: ' . $_SESSION['otp'] . "\n\nThis OTP is valid for 5 minutes.";
        
        $mail->send();
    } catch (Exception $e) {
        // Log error to a file for debugging
        file_put_contents('mail_errors.log', date('Y-m-d H:i:s') . ' - ' . $e->getMessage() . "\n", FILE_APPEND);
        die("Failed to send OTP. Please try again later.");
    }
} else {
    header("Location: login.php");
    exit();
}
?>