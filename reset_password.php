<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $token = $_POST['token'];
    $new_password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $sql = "SELECT id FROM users WHERE email=? AND reset_token=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $token);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $sql_update = "UPDATE users SET password=?, reset_token=NULL WHERE email=?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("ss", $new_password, $email);
        $stmt_update->execute();

        echo "Your password has been reset successfully! Redirecting...";
        echo '<script>window.setTimeout(function() { window.location.href = "index.php"; }, 5000);</script>';
    } else {
        echo "Invalid reset link or email.";
    }

    $stmt->close();
    $conn->close();
} else {
    if (isset($_GET['email']) && isset($_GET['token'])) {
        $email = $_GET['email'];
        $token = $_GET['token'];
        echo '
            <form method="POST">
                <input type="hidden" name="email" value="' . htmlspecialchars($email) . '">
                <input type="hidden" name="token" value="' . htmlspecialchars($token) . '">
                <input type="password" name="password" placeholder="New Password" required>
                <button type="submit">Reset Password</button>
            </form>
        ';
    }
}
?>
