<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $verification_code = $_POST['verification_code'];

    $sql = "SELECT id FROM users WHERE email=? AND verification_code=? AND verified_acc=0";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $verification_code);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $sql_update = "UPDATE users SET verified_acc=1 WHERE email=?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("s", $email);
        $stmt_update->execute();

        echo '<p>Your account has been successfully verified. Redirecting to login...</p>';
        echo '<script>window.setTimeout(function() { window.location.href = "login.php"; }, 5000);</script>';
    } else {
        echo '<p>Invalid verification code or the account is already verified.</p>';
    }

    $stmt->close();
    $stmt_update->close();
} else {
    echo '<p>Invalid request.</p>';
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Code</title>
</head>
<body>
    <h2>Verify Your Account</h2>
    <form action="verify_code.php" method="post">
        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" required><br>
        <label for="verification_code">Verification Code:</label><br>
        <input type="text" id="verification_code" name="verification_code" required><br>
        <button type="submit">Verify</button>
    </form>
    <a href="login.php">Back to Login</a>
</body>
</html>
