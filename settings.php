<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Kullanıcı adı değişikliği için son değişiklik tarihini kontrol et
$sql_last_username_change = "SELECT last_username_change FROM users WHERE id=?";
$stmt_last_username_change = $conn->prepare($sql_last_username_change);
$stmt_last_username_change->bind_param("i", $user_id);
$stmt_last_username_change->execute();
$stmt_last_username_change->bind_result($last_username_change);
$stmt_last_username_change->fetch();
$stmt_last_username_change->close();

$current_time = time();
$last_change_timestamp = strtotime($last_username_change);
$seconds_in_60_days = 60 * 24 * 60 * 60; // 60 günün saniye cinsinden karşılığı

$username_change_disabled = ($current_time - $last_change_timestamp < $seconds_in_60_days);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $name = $_POST['name'];
        $surname = $_POST['surname'];
        $gender = $_POST['gender'];
        $country = $_POST['country'];
        $birth_date = $_POST['birth_date'] ?? NULL;
        $phone = $_POST['phone'] ?? NULL;
        $city = $_POST['city'] ?? NULL;

        $sql = "UPDATE users SET name=?, surname=?, gender=?, country=?, birth_date=?, phone=?, city=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssi", $name, $surname, $gender, $country, $birth_date, $phone, $city, $user_id);

        if ($stmt->execute()) {
            echo '<div class="success">Profile updated successfully!</div>';
        } else {
            echo '<div class="error">Error updating profile: ' . $stmt->error . '</div>';
        }

        $stmt->close();
    } elseif (isset($_POST['change_username'])) {
        if ($username_change_disabled) {
            echo '<div class="error">You can change your username again in ' . ceil(($last_change_timestamp + $seconds_in_60_days - $current_time) / (24 * 60 * 60)) . ' days.</div>';
        } else {
            // Handle username change
            // ...
        }
    }
}

// Kullanıcı bilgilerini getir
$sql_select_user = "SELECT user_name, name, surname, email, gender, country, birth_date, phone, city FROM users WHERE id=?";
$stmt_select_user = $conn->prepare($sql_select_user);
$stmt_select_user->bind_param("i", $user_id);
$stmt_select_user->execute();
$stmt_select_user->bind_result($user_name, $name, $surname, $email, $gender, $country, $birth_date, $phone, $city);
$stmt_select_user->fetch();
$stmt_select_user->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
</head>
<body>
    <h2>Settings</h2>

    <h3>User Information</h3>
    <form action="settings.php" method="post">
        <label for="user_name">Username:</label><br>
        <input type="text" id="user_name" name="user_name" value="<?php echo htmlspecialchars($user_name); ?>" disabled><br>
        <?php if (!$username_change_disabled): ?>
            <button type="submit" name="change_username">Change Username</button><br>
        <?php else: ?>
            <small>You can change your username again in <?php echo ceil(($last_change_timestamp + $seconds_in_60_days - $current_time) / (24 * 60 * 60)); ?> days.</small><br>
        <?php endif; ?>
        <label for="name">Name:</label><br>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required><br>
        <label for="surname">Surname:</label><br>
        <input type="text" id="surname" name="surname" value="<?php echo htmlspecialchars($surname); ?>" required><br>
        <label for="gender">Gender:</label><br>
        <select id="gender" name="gender" required>
            <option value="male" <?php if ($gender == 'male') echo 'selected'; ?>>Male</option>
            <option value="female" <?php if ($gender == 'female') echo 'selected'; ?>>Female</option>
            <option value="other" <?php if ($gender == 'other') echo 'selected'; ?>>Other</option>
        </select><br>
        <label for="country">Country:</label><br>
        <input type="text" id="country" name="country" value="<?php echo htmlspecialchars($country); ?>" required><br>
        <label for="birth_date">Birth Date:</label><br>
        <input type="date" id="birth_date" name="birth_date" value="<?php echo htmlspecialchars($birth_date); ?>"><br>
        <label for="phone">Phone:</label><br>
        <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>"><br>
        <label for="city">City:</label><br>
        <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($city); ?>"><br>
        <button type="submit" name="update_profile">Update Profile</button>
    </form>

    <h3>Email Change</h3>
    <form action="change_email.php" method="post">
        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
        <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
        <button type="submit">Change Email</button>
    </form>

    <h3>Password Change</h3>
    <form action="change_password.php" method="post">
        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
        <button type="submit">Change Password</button>
    </form>

    <p><a href="dashboard.php">Back to Dashboard</a></p>
</body>
</html>