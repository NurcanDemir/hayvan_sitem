<?php
// debug_admin_fix.php - Admin paneli sorunlarını tespit etme ve düzeltme

include('includes/db.php');

echo "<h2>Admin Paneli Sorun Tespiti ve Düzeltme</h2>";

// 1. Bilgilendirmeler tablosunun varlığını kontrol et
echo "<h3>1. Bilgilendirmeler Tablosu Kontrolü</h3>";
$query = "SHOW TABLES LIKE 'bilgilendirmeler'";
$result = $conn->query($query);
if ($result->num_rows > 0) {
    echo "<p style='color: green;'>✓ Bilgilendirmeler tablosu mevcut</p>";
} else {
    echo "<p style='color: red;'>✗ Bilgilendirmeler tablosu MEVCUT DEĞİL</p>";
    echo "<p>Bilgilendirmeler tablosunu oluşturuluyor...</p>";
    
    // Bilgilendirmeler tablosunu oluştur
    $create_table = "CREATE TABLE IF NOT EXISTS `bilgilendirmeler` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `talep_id` int(11) NOT NULL,
        `admin_id` int(11) NOT NULL,
        `bilgi_turu` enum('bilgi','onay','red','tamamlandi','ek_belge','randevu','uyari') NOT NULL DEFAULT 'bilgi',
        `mesaj` text NOT NULL,
        `tarih` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `okundu` tinyint(1) DEFAULT 0,
        PRIMARY KEY (`id`),
        KEY `talep_id` (`talep_id`),
        KEY `admin_id` (`admin_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci";
    
    if ($conn->query($create_table)) {
        echo "<p style='color: green;'>✓ Bilgilendirmeler tablosu oluşturuldu</p>";
    } else {
        echo "<p style='color: red;'>✗ Bilgilendirmeler tablosu oluşturulamadı: " . $conn->error . "</p>";
    }
}

// 2. Admin tablosunu kontrol et
echo "<h3>2. Admin Tablosu Kontrolü</h3>";
$query = "DESCRIBE admin";
$result = $conn->query($query);
if ($result) {
    echo "<p style='color: green;'>✓ Admin tablosu mevcut</p>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Alan</th><th>Tip</th><th>Null</th><th>Varsayılan</th></tr>";
    
    $has_ad = false;
    $has_soyad = false;
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>" . $row['Field'] . "</td><td>" . $row['Type'] . "</td><td>" . $row['Null'] . "</td><td>" . $row['Default'] . "</td></tr>";
        if ($row['Field'] == 'ad') $has_ad = true;
        if ($row['Field'] == 'soyad') $has_soyad = true;
    }
    echo "</table>";
    
    // Ad ve soyad alanlarını kontrol et ve ekle
    if (!$has_ad) {
        echo "<p style='color: orange;'>⚠ Admin tablosunda 'ad' alanı eksik, ekleniyor...</p>";
        $conn->query("ALTER TABLE admin ADD COLUMN ad VARCHAR(50) NOT NULL DEFAULT 'Admin'");
    }
    
    if (!$has_soyad) {
        echo "<p style='color: orange;'>⚠ Admin tablosunda 'soyad' alanı eksik, ekleniyor...</p>";
        $conn->query("ALTER TABLE admin ADD COLUMN soyad VARCHAR(50) NOT NULL DEFAULT 'User'");
    }
} else {
    echo "<p style='color: red;'>✗ Admin tablosu bulunamadı: " . $conn->error . "</p>";
}

// 3. Sahiplenme istekleri tablosunu kontrol et
echo "<h3>3. Sahiplenme İstekleri Tablosu Kontrolü</h3>";
$query = "SELECT COUNT(*) as toplam FROM sahiplenme_istekleri";
$result = $conn->query($query);
if ($result) {
    $row = $result->fetch_assoc();
    echo "<p style='color: green;'>✓ Sahiplenme istekleri tablosu mevcut. Toplam kayıt: " . $row['toplam'] . "</p>";
    
    // Sahiplenme istekleri tablosunun yapısını kontrol et
    $query = "DESCRIBE sahiplenme_istekleri";
    $result = $conn->query($query);
    $has_sahiplenen_yorumu = false;
    $has_yorum_tarihi = false;
    $has_talep_eden_ad_soyad = false;
    $has_talep_eden_email = false;
    $has_talep_eden_telefon = false;
    
    echo "<p><strong>Sahiplenme istekleri tablosu alanları:</strong></p>";
    while ($row = $result->fetch_assoc()) {
        echo "<p>- " . $row['Field'] . " (" . $row['Type'] . ")</p>";
        if ($row['Field'] == 'sahiplenen_yorumu') $has_sahiplenen_yorumu = true;
        if ($row['Field'] == 'yorum_tarihi') $has_yorum_tarihi = true;
        if ($row['Field'] == 'talep_eden_ad_soyad') $has_talep_eden_ad_soyad = true;
        if ($row['Field'] == 'talep_eden_email') $has_talep_eden_email = true;
        if ($row['Field'] == 'talep_eden_telefon') $has_talep_eden_telefon = true;
    }
    
    if (!$has_sahiplenen_yorumu) {
        echo "<p style='color: orange;'>⚠ Sahiplenme istekleri tablosunda 'sahiplenen_yorumu' alanı eksik, ekleniyor...</p>";
        $conn->query("ALTER TABLE sahiplenme_istekleri ADD COLUMN sahiplenen_yorumu TEXT");
    }
    
    if (!$has_yorum_tarihi) {
        echo "<p style='color: orange;'>⚠ Sahiplenme istekleri tablosunda 'yorum_tarihi' alanı eksik, ekleniyor...</p>";
        $conn->query("ALTER TABLE sahiplenme_istekleri ADD COLUMN yorum_tarihi TIMESTAMP NULL");
    }
    
    if (!$has_talep_eden_ad_soyad) {
        echo "<p style='color: orange;'>⚠ Sahiplenme istekleri tablosunda 'talep_eden_ad_soyad' alanı eksik, ekleniyor...</p>";
        $conn->query("ALTER TABLE sahiplenme_istekleri ADD COLUMN talep_eden_ad_soyad VARCHAR(100)");
    }
    
    if (!$has_talep_eden_email) {
        echo "<p style='color: orange;'>⚠ Sahiplenme istekleri tablosunda 'talep_eden_email' alanı eksik, ekleniyor...</p>";
        $conn->query("ALTER TABLE sahiplenme_istekleri ADD COLUMN talep_eden_email VARCHAR(100)");
    }
    
    if (!$has_talep_eden_telefon) {
        echo "<p style='color: orange;'>⚠ Sahiplenme istekleri tablosunda 'talep_eden_telefon' alanı eksik, ekleniyor...</p>";
        $conn->query("ALTER TABLE sahiplenme_istekleri ADD COLUMN talep_eden_telefon VARCHAR(20)");
    }
} else {
    echo "<p style='color: red;'>✗ Sahiplenme istekleri tablosu bulunamadı: " . $conn->error . "</p>";
}

// 4. Raporlar için gerekli tabloları kontrol et
echo "<h3>4. Raporlar İçin Gerekli Tabloları Kontrol</h3>";

$tables = ['ilanlar', 'kullanicilar', 'kategoriler', 'favoriler', 'il'];
foreach ($tables as $table) {
    $query = "SHOW TABLES LIKE '$table'";
    $result = $conn->query($query);
    if ($result->num_rows > 0) {
        echo "<p style='color: green;'>✓ $table tablosu mevcut</p>";
    } else {
        echo "<p style='color: red;'>✗ $table tablosu MEVCUT DEĞİL</p>";
    }
}

// 5. Veritabanı bağlantısını test et
echo "<h3>5. Veritabanı Bağlantısı Test</h3>";
if ($conn->ping()) {
    echo "<p style='color: green;'>✓ Veritabanı bağlantısı aktif</p>";
} else {
    echo "<p style='color: red;'>✗ Veritabanı bağlantısı sorunu: " . $conn->error . "</p>";
}

echo "<h3>Düzeltme İşlemleri Tamamlandı!</h3>";
echo "<p><a href='admin/admin_panel.php'>Admin Paneline Git</a></p>";
echo "<p><a href='admin/raporlar.php'>Raporlar Sayfasına Git</a></p>";
echo "<p><a href='admin/admin_talep_bilgilendir.php'>Bilgilendirme Sayfasına Git</a></p>";

$conn->close();
?>
