<?php
// create_admin_user.php - Varsayılan admin hesabı oluştur

include('includes/db.php');

echo "<h2>Admin Hesabı Oluştur</h2>";

// Admin tablosunu kontrol et
$query = "SHOW TABLES LIKE 'admin'";
$result = $conn->query($query);

if ($result->num_rows == 0) {
    // Admin tablosunu oluştur
    $create_admin_table = "CREATE TABLE IF NOT EXISTS `admin` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `kullanici_adi` varchar(50) NOT NULL,
        `sifre` varchar(255) NOT NULL,
        `ad` varchar(50) NOT NULL DEFAULT 'Admin',
        `soyad` varchar(50) NOT NULL DEFAULT 'User',
        `email` varchar(100) NULL,
        `tarih` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `kullanici_adi` (`kullanici_adi`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci";
    
    if ($conn->query($create_admin_table)) {
        echo "<p style='color: green;'>✓ Admin tablosu oluşturuldu</p>";
    } else {
        echo "<p style='color: red;'>✗ Admin tablosu oluşturulamadı: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color: green;'>✓ Admin tablosu mevcut</p>";
}

// Varsayılan admin hesabını kontrol et
$check_admin = "SELECT * FROM admin WHERE kullanici_adi = 'admin'";
$result = $conn->query($check_admin);

if ($result->num_rows == 0) {
    // Varsayılan admin hesabı oluştur
    $admin_kullanici = 'admin';
    $admin_sifre = 'admin123'; // Güvenlik için değiştirilmeli
    $admin_sifre_hash = password_hash($admin_sifre, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO admin (kullanici_adi, sifre, ad, soyad, email) VALUES (?, ?, ?, ?, ?)");
    $ad = 'Admin';
    $soyad = 'User';
    $email = 'admin@hayvan_sitem.com';
    $stmt->bind_param("sssss", $admin_kullanici, $admin_sifre_hash, $ad, $soyad, $email);
    
    if ($stmt->execute()) {
        echo "<p style='color: green;'>✓ Varsayılan admin hesabı oluşturuldu</p>";
        echo "<p><strong>Kullanıcı Adı:</strong> admin</p>";
        echo "<p><strong>Şifre:</strong> admin123</p>";
        echo "<p style='color: orange;'>⚠ Güvenlik için şifrenizi değiştirin!</p>";
    } else {
        echo "<p style='color: red;'>✗ Admin hesabı oluşturulamadı: " . $stmt->error . "</p>";
    }
    $stmt->close();
} else {
    echo "<p style='color: blue;'>ℹ Admin hesabı zaten mevcut</p>";
    $admin = $result->fetch_assoc();
    echo "<p><strong>Kullanıcı Adı:</strong> " . $admin['kullanici_adi'] . "</p>";
    echo "<p><strong>Ad Soyad:</strong> " . $admin['ad'] . " " . $admin['soyad'] . "</p>";
}

echo "<h3>Admin Girişi</h3>";
echo "<p><a href='admin/admin_giris.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Admin Giriş Sayfası</a></p>";

$conn->close();
?>
