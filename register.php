<?php
session_start();
include 'db.php';
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Şehir verilerini veritabanından çekmek için sorgu
$sql_countries = "SELECT DISTINCT country FROM locations ORDER BY country ASC";
$result_countries = $conn->query($sql_countries);

$countries = array();
while ($row = $result_countries->fetch_assoc()) {
    $country = $row['country'];
    $countries[$country] = array();

    // Her ülkenin şehirlerini sorgula
    $sql_cities = "SELECT city FROM locations WHERE country = '$country' ORDER BY city ASC";
    $result_cities = $conn->query($sql_cities);
    while ($city_row = $result_cities->fetch_assoc()) {
        $cities[] = $city_row['city'];
    }
    $countries[$country] = $cities;
    $cities = array();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_name = $_POST['user_name'];
    $name = $_POST['name'];
    $surname = $_POST['surname'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $gender = $_POST['gender'];
    $country = $_POST['country'];
    $birth_date = $_POST['birth_date'] ?? NULL;
    $phone = $_POST['phone'] ?? NULL;
    $city = $_POST['city'] ?? NULL;

    $verification_code = md5(uniqid(rand(), true));

    // Kullanıcı adı doğrulaması: Küçük harfler ve 20 karakter sınırı
    if (!preg_match('/^[a-z0-9_]{1,20}$/', $user_name)) {
        echo '<script>alert("Username must be between 1-20 characters and can only contain lowercase letters, numbers, and underscore.");</script>';
    }
    // E-posta doğrulaması
    else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo '<script>alert("Invalid email format.");</script>';
    }
    // Şifre doğrulaması: En az bir büyük harf, bir küçük harf ve bir rakam içermeli, kullanıcı adı veya isim içermemeli
    else if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$/', $_POST['password'])) {
        echo '<script>alert("Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, and one number.");</script>';
    }
    // İsim ve soyisim doğrulaması
    else if (!preg_match('/^[a-zA-ZüğışöçÜĞİŞÖÇ]{2,20}$/', $name) || !preg_match('/^[a-zA-ZüğışöçÜĞİŞÖÇ]{2,20}$/', $surname)) {
        echo '<script>alert("Name and surname must be between 2-20 characters and can only contain letters.");</script>';
    }
    // Ülke ve şehir doğrulaması
    else if (empty($country) || ($country == 'Turkey' && empty($city)) || ($country == 'Azerbaijan' && empty($city))) {
        echo '<script>alert("Please select a country and city.");</script>';
    }
    // Doğum tarihi doğrulaması: 100 yaşından büyük olmamalı
    else if (!empty($birth_date) && strtotime($birth_date) > strtotime('-100 years')) {
        echo '<script>alert("You must be at least 1 year old and not older than 100 years.");</script>';
    }
    // Tüm doğrulamalar geçerli ise kayıt işlemini gerçekleştir
    else {
        $sql = "INSERT INTO users (user_name, name, surname, email, password, gender, country, birth_date, phone, city, verification_code, verified_acc) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, false)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssssss", $user_name, $name, $surname, $email, $password, $gender, $country, $birth_date, $phone, $city, $verification_code);

        if ($stmt->execute()) {
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
                echo '<script>alert("Registration successful! Please check your email to verify your account.");</script>';
                echo '<script>window.setTimeout(function() { window.location.href = "login.php"; }, 5000);</script>';
            } catch (Exception $e) {
                echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
</head>
<body>
    <h2>Register</h2>
    <form action="register.php" method="post">
        <label for="user_name">Username:</label><br>
        <input type="text" id="user_name" name="user_name" pattern="[a-z0-9_]{1,20}" title="Username must be between 1-20 characters and can only contain lowercase letters, numbers, and underscore." required><br>
        <label for="name">Name:</label><br>
        <input type="text" id="name" name="name" pattern="[a-zA-ZüğışöçÜĞİŞÖÇ]{2,20}" title="Name must be between 2-20 characters and can only contain letters." required><br>
        <label for="surname">Surname:</label><br>
        <input type="text" id="surname" name="surname" pattern="[a-zA-ZüğışöçÜĞİŞÖÇ]{2,20}" title="Surname must be between 2-20 characters and can only contain letters." required><br>
        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" required><br>
        <label for="password">Password:</label><br>
        <input type="password" id="password" name="password" pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$" title="Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, and one number." required><br>
        <label for="gender">Gender:</label><br>
        <select id="gender" name="gender" required>
            <option value="male">Male</option>
            <option value="female">Female</option>
            <option value="other">Other</option>
        </select><br>
        <label for="country">Country:</label><br>
        <select id="country" name="country" onchange="changeCityOptions()" required>
            <option value="">Select Country</option>
            <?php foreach ($countries as $country => $cities): ?>
                <option value="<?= $country ?>"><?= $country ?></option>
            <?php endforeach; ?>
        </select><br>
        <label for="city">City:</label><br>
        <select id="city" name="city" disabled required>
            <option value="">Select City</option>
            <?php foreach ($cities as $city): ?>
                <option value="<?= $city ?>"><?= $city ?></option>
            <?php endforeach; ?>
        </select><br>
        <label for="birth_date">Birth Date:</label><br>
        <input type="date" id="birth_date" name="birth_date"><br>
        <button type="submit">Register</button>
    </form>
    <p>Already have an account? <a href="login.php">Login here</a></p>

    <script>
    // JavaScript ile şehir seçimini dinamik olarak değiştir
    function changeCityOptions() {
        var countrySelect = document.getElementById("country");
        var citySelect = document.getElementById("city");

        // Seçilen ülkeye göre şehir seçeneklerini güncelle
        if (countrySelect.value === "Turkey") {
            citySelect.disabled = false;
            citySelect.innerHTML = "";
            <?php foreach ($countries['Turkey'] as $city): ?>
                var option = document.createElement("option");
                option.text = "<?= $city ?>";
                option.value = "<?= $city ?>";
                citySelect.appendChild(option);
            <?php endforeach; ?>
        } else if (countrySelect.value === "Azerbaijan") {
            citySelect.disabled = false;
            citySelect.innerHTML = "";
            <?php foreach ($countries['Azerbaijan'] as $city): ?>
                var option = document.createElement("option");
                option.text = "<?= $city ?>";
                option.value = "<?= $city ?>";
                citySelect.appendChild(option);
            <?php endforeach; ?>
        } else {
            citySelect.disabled = true;
            citySelect.innerHTML = '<option value="">Select Country First</option>';
        }
    }
</script>
</body>
</html>
