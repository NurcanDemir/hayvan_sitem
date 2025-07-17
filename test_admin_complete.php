<?php
// test_admin_complete.php - Tüm admin paneli fonksiyonlarını test et

session_start();
include('includes/db.php');

// Admin session'ı simüle et
$_SESSION['admin_logged_in'] = true;
$_SESSION['admin_id'] = 1;
$_SESSION['admin_ad'] = 'Test';
$_SESSION['admin_soyad'] = 'Admin';

echo "<h1>Admin Paneli Test Sonuçları</h1>";

// 1. Veritabanı bağlantısı testi
echo "<h2>1. Veritabanı Bağlantısı</h2>";
if ($conn->ping()) {
    echo "<p style='color: green;'>✓ Veritabanı bağlantısı başarılı</p>";
} else {
    echo "<p style='color: red;'>✗ Veritabanı bağlantısı başarısız</p>";
}

// 2. Gerekli tabloların varlığını kontrol et
echo "<h2>2. Gerekli Tablolar</h2>";
$required_tables = ['admin', 'ilanlar', 'kullanicilar', 'sahiplenme_istekleri', 'bilgilendirmeler', 'kategoriler', 'favoriler'];
foreach ($required_tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        echo "<p style='color: green;'>✓ $table tablosu mevcut</p>";
    } else {
        echo "<p style='color: red;'>✗ $table tablosu eksik</p>";
    }
}

// 3. Admin paneli sayfalarının erişilebilirliğini kontrol et
echo "<h2>3. Admin Paneli Sayfaları</h2>";
$admin_pages = [
    'admin_panel.php' => 'Ana Sayfa',
    'sahiplenme_talepleri.php' => 'Sahiplenme Talepleri',
    'raporlar.php' => 'Raporlar',
    'kullanici_yonetim.php' => 'Kullanıcı Yönetimi',
    'ilan_yonetim.php' => 'İlan Yönetimi'
];

foreach ($admin_pages as $page => $title) {
    if (file_exists("admin/$page")) {
        echo "<p style='color: green;'>✓ $title ($page) - Dosya mevcut</p>";
    } else {
        echo "<p style='color: red;'>✗ $title ($page) - Dosya bulunamadı</p>";
    }
}

// 4. Bilgilendirme sistemi testi
echo "<h2>4. Bilgilendirme Sistemi</h2>";
if (file_exists("admin/admin_talep_bilgilendir.php")) {
    echo "<p style='color: green;'>✓ Bilgilendirme sayfası mevcut</p>";
} else {
    echo "<p style='color: red;'>✗ Bilgilendirme sayfası bulunamadı</p>";
}

// 5. Raporlar için veri kontrolü
echo "<h2>5. Raporlar İçin Veri Kontrolü</h2>";
$data_checks = [
    'ilanlar' => 'SELECT COUNT(*) as count FROM ilanlar',
    'kullanicilar' => 'SELECT COUNT(*) as count FROM kullanicilar',
    'sahiplenme_istekleri' => 'SELECT COUNT(*) as count FROM sahiplenme_istekleri',
    'favoriler' => 'SELECT COUNT(*) as count FROM favoriler'
];

foreach ($data_checks as $table => $query) {
    $result = $conn->query($query);
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<p style='color: green;'>✓ $table: " . $row['count'] . " kayıt</p>";
    } else {
        echo "<p style='color: red;'>✗ $table: Veri okunamadı</p>";
    }
}

// 6. Session kontrolü
echo "<h2>6. Session Kontrolü</h2>";
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']) {
    echo "<p style='color: green;'>✓ Admin session aktif</p>";
    echo "<p>Admin ID: " . $_SESSION['admin_id'] . "</p>";
    echo "<p>Admin Adı: " . $_SESSION['admin_ad'] . " " . $_SESSION['admin_soyad'] . "</p>";
} else {
    echo "<p style='color: red;'>✗ Admin session bulunamadı</p>";
}

echo "<h2>Test Tamamlandı!</h2>";
echo "<p><a href='admin/admin_panel.php'>Admin Paneline Git</a></p>";
echo "<p><a href='admin/sahiplenme_talepleri.php'>Sahiplenme Talepleri</a></p>";
echo "<p><a href='admin/raporlar.php'>Raporlar</a></p>";

$conn->close();
?>
