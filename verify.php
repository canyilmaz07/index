<?php
include 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['email']) && isset($_GET['code'])) {
    $email = $_GET['email'];
    $verification_code = $_GET['code'];

    // Veritabanında doğrulama kodunu ve e-postayı kontrol ediyoruz
    $sql = "SELECT id, user_name FROM users WHERE email=? AND verification_code=? AND verified_acc=0";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $verification_code);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($user_id, $user_name);

    if ($stmt->num_rows > 0) {
        $stmt->fetch();
        // Doğrulama başarılı ise
        $sql_update = "UPDATE users SET verified_acc=1 WHERE email=?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("s", $email);
        $stmt_update->execute();
        $stmt_update->close();

        // Kullanıcı oturumunu başlat
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_name'] = $user_name;

        // Başarılı doğrulama mesajı ve yönlendirme
        echo '<p>Hesabınız başarıyla doğrulandı. Şimdi otomatik olarak yönlendiriliyorsunuz...</p>';
        echo '<script>window.setTimeout(function() { window.location.href = "index.php"; }, 5000);</script>';
        exit;
    } else {
        // Geçersiz doğrulama kodu veya hesap zaten doğrulanmış.
        echo '<p>Geçersiz doğrulama kodu veya hesap zaten doğrulanmış.</p>';
    }

    $stmt->close();
} else {
    // Geçersiz istek hatası
    echo '<p>Geçersiz istek.</p>';
}

$conn->close();
?>
