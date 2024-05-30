<?php
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendVerificationEmail($email, $verification_code) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp-mail.outlook.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'canyilmaz0735php@hotmail.com';
        $mail->Password = 'can07352470';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('canyilmaz0735php@hotmail.com', 'No Reply');
        $mail->addAddress($email);
        $mail->Subject = 'Email Verification';
        $mail->isHTML(true);
        $mail->CharSet = "UTF-8";
        $verification_link = "http://localhost/index/verify.php?email=$email&code=" . urlencode($verification_code);
        $mail->Body = "Please click the link to verify your email: <a href='$verification_link'>$verification_link</a>";

        $mail->send();
        return true;
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        return false;
    }
}
?>
