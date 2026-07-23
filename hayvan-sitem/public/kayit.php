<?php
// filepath: c:\xampp\htdocs\hayvan-sitem\public\kayit.php

session_start();
include("../includes/db.php");

// Kullanıcı kayıt işlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $email = $_POST['email'] ?? '';

    // Basit form doğrulama
    if (!empty($username) && !empty($password) && !empty($email)) {
        // Şifreyi hashle
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Kullanıcıyı veritabanına ekle
        $stmt = $conn->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $hashed_password, $email);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Kayıt başarılı! Giriş yapabilirsiniz.";
            header("Location: giris.php");
            exit();
        } else {
            $_SESSION['error'] = "Kayıt sırasında bir hata oluştu.";
        }

        $stmt->close();
    } else {
        $_SESSION['error'] = "Lütfen tüm alanları doldurun.";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıt Ol - Hayvan Dostları Platformu</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Kayıt Ol</h2>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Kullanıcı Adı:</label>
                <input type="text" name="username" id="username" required>
            </div>
            <div class="form-group">
                <label for="email">E-posta:</label>
                <input type="email" name="email" id="email" required>
            </div>
            <div class="form-group">
                <label for="password">Şifre:</label>
                <input type="password" name="password" id="password" required>
            </div>
            <button type="submit">Kayıt Ol</button>
        </form>
        <p>Zaten bir hesabınız var mı? <a href="giris.php">Giriş yapın</a></p>
    </div>
</body>
</html>

<?php
$conn->close();
?>