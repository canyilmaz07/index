<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_name = $_POST['user_name'];
    $password = $_POST['password'];

    $sql = "SELECT id, password, verified_acc FROM users WHERE user_name=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user_name);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $hashed_password, $verified_acc);
    $stmt->fetch();

    if ($stmt->num_rows > 0 && password_verify($password, $hashed_password)) {
        if ($verified_acc) {
            $_SESSION['user_id'] = $id;
            header('Location: dashboard.php');
            exit;
        } else {
            echo '<p>Please verify your email before logging in.</p>';
            echo '<form action="resend_verification.php" method="post">';
            echo '<input type="hidden" name="user_id" value="' . $id . '">';
            echo '<button type="submit">Resend Verification Email</button>';
            echo '</form>';
        }
    } else {
        echo '<p>Invalid username or password.</p>';
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <h2>Login</h2>
    <form action="login.php" method="post">
        <label for="user_name">Username:</label><br>
        <input type="text" id="user_name" name="user_name" required><br>
        <label for="password">Password:</label><br>
        <input type="password" id="password" name="password" required><br>
        <button type="submit">Login</button>
    </form>
    <p>Don't have an account? <a href="register.php">Register here</a></p>
    <p>Forgot password? <a href="reset_request.php">Reset Password</a></p>
</body>
</html>
