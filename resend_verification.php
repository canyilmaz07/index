<?php
include 'db.php';
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];

    $sql_get_email = "SELECT email, verification_code FROM users WHERE id=?";
    $stmt_get_email = $conn->prepare($sql_get_email);
    $stmt_get_email->bind_param("i", $user_id);
    $stmt_get_email->execute();
    $stmt_get_email->bind_result($email, $verification_code);
    $stmt_get_email->fetch();
    $stmt_get_email->close();

    $verification_link = "http://localhost/index/verify.php?email=$email&code=" . urlencode($verification_code);
    
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
        $mail->Body = "Please click the link to verify your email: <a href='$verification_link'>$verification_link</a>";

        $mail->send();
        echo 'Verification email has been resent. Please check your email.';
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
} else {
    echo 'Invalid request.';
}

$conn->close();
?>
