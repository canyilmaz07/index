<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT user_name, email, profile_image FROM users WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($user_name, $email, $profile_image);
$stmt->fetch();
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
</head>
<body>
    <h2>Welcome, <?php echo htmlspecialchars($user_name); ?>!</h2>
    <p>Email: <?php echo htmlspecialchars($email); ?></p>
    <?php if ($profile_image): ?>
        <img src="<?php echo htmlspecialchars($profile_image); ?>" alt="Profile Image">
    <?php else: ?>
        <p>No profile image set.</p>
    <?php endif; ?>
    <a href="settings.php">Settings</a>
    <a href="logout.php">Logout</a>
</body>
</html>
