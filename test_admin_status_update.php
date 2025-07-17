<?php
// test_admin_status_update.php - Admin durumu güncelleme test

session_start();
include('includes/db.php');

// Admin session set et (test için)
$_SESSION['admin_logged_in'] = true;
$_SESSION['admin_id'] = 1;
$_SESSION['admin_kullanici_adi'] = 'test_admin';

echo "<h2>Admin Durum Güncelleme Test</h2>";

// Test talep oluştur
$sql_test = "INSERT INTO sahiplenme_istekleri (ilan_id, talep_eden_kullanici_id, ilan_sahibi_kullanici_id, talep_eden_ad_soyad, talep_eden_telefon, talep_eden_email, adres, mesaj, durum) 
VALUES (1, 1, 1, 'Test User', '5551234567', 'test@email.com', 'Test Address', 'Test message', 'beklemede')
ON DUPLICATE KEY UPDATE durum = 'beklemede'";
$conn->query($sql_test);

// Mevcut talepleri göster
echo "<h3>Mevcut Talepler:</h3>";
$result = $conn->query("SELECT * FROM sahiplenme_istekleri ORDER BY id DESC LIMIT 5");
while($row = $result->fetch_assoc()) {
    echo "ID: " . $row['id'] . " - Durum: " . $row['durum'] . " - Talep Eden: " . $row['talep_eden_ad_soyad'] . "<br>";
}

// Test talebi onayla
echo "<h3>Test: Talebi Onayla</h3>";
$test_id = 1;
$_POST['talep_id'] = $test_id;
$_POST['action'] = 'onayla';

ob_start();
include('admin/talep_durum_guncelle.php');
$response = ob_get_clean();
echo "Response: " . $response . "<br>";

// Test sonucu kontrol et
$result_check = $conn->query("SELECT durum FROM sahiplenme_istekleri WHERE id = $test_id");
if ($result_check->num_rows > 0) {
    $row = $result_check->fetch_assoc();
    echo "Güncellenmiş durum: " . $row['durum'] . "<br>";
}

echo "<h3>Taleplerim sayfasını test et:</h3>";
echo "<a href='taleplerim.php'>Taleplerim sayfasına git</a><br>";
echo "<a href='admin/sahiplenme_talepleri.php'>Admin paneli</a>";

$conn->close();
?>
