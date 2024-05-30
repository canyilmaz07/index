<?php
include 'db.php';
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    $sql = "SELECT id FROM users WHERE email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $reset_token = md5($email . time());
        $sql_update = "UPDATE users SET reset_token=? WHERE email=?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("ss", $reset_token, $email);
        $stmt_update->execute();

        $reset_link = "http://localhost/index/reset_password.php?email=$email&token=$reset_token";
        
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
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            $mail->Body = "Please click the link to reset your password: <a href='$reset_link'>$reset_link</a>";

            $mail->send();
            echo 'Password reset link has been sent to your email.';
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        echo "Email not found.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Password Reset</title>
</head>
<body>
    <h2>Reset Password</h2>
    <form action="request_password_reset.php" method="post">
        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" required><br>
        <button type="submit">Send Reset Link</button>
    </form>
    <a href="login.php">Go back</a>
</body>
</html>
