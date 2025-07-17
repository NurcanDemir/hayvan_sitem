<?php
// update_sahiplenen_comments.php - Sahiplenen yorumları için veritabanı güncellemesi

include('includes/db.php');

echo "<h2>Sahiplenen Yorumları İçin Veritabanı Güncellemesi</h2>";

// sahiplenme_istekleri tablosuna sahiplenen_yorumu kolonu ekle
$sql_alter = "ALTER TABLE sahiplenme_istekleri ADD COLUMN IF NOT EXISTS sahiplenen_yorumu TEXT NULL";
$result = $conn->query($sql_alter);

if ($result) {
    echo "✅ sahiplenme_istekleri tablosuna sahiplenen_yorumu kolonu eklendi<br>";
} else {
    echo "❌ Hata: " . $conn->error . "<br>";
}

// sahiplenme_istekleri tablosuna yorum_tarihi kolonu ekle
$sql_alter2 = "ALTER TABLE sahiplenme_istekleri ADD COLUMN IF NOT EXISTS yorum_tarihi TIMESTAMP NULL";
$result2 = $conn->query($sql_alter2);

if ($result2) {
    echo "✅ sahiplenme_istekleri tablosuna yorum_tarihi kolonu eklendi<br>";
} else {
    echo "❌ Hata: " . $conn->error . "<br>";
}

// Test verisi ekle
$test_data = "INSERT INTO sahiplenme_istekleri (ilan_id, talep_eden_kullanici_id, ilan_sahibi_kullanici_id, talep_eden_ad_soyad, talep_eden_telefon, talep_eden_email, adres, mesaj, durum, sahiplenen_yorumu, yorum_tarihi) 
VALUES (1, 1, 1, 'Test Kullanıcı', '5551234567', 'test@email.com', 'Test Adres', 'Test mesaj', 'tamamlandı', 'Çok mutluyum! Minnoş harika bir arkadaş oldu.', NOW())
ON DUPLICATE KEY UPDATE sahiplenen_yorumu = 'Çok mutluyum! Minnoş harika bir arkadaş oldu.', yorum_tarihi = NOW()";

$result3 = $conn->query($test_data);
if ($result3) {
    echo "✅ Test verisi eklendi<br>";
} else {
    echo "❌ Test verisi eklenirken hata: " . $conn->error . "<br>";
}

echo "<h3>Güncel Tablo Yapısı:</h3>";
$result_structure = $conn->query("DESCRIBE sahiplenme_istekleri");
while($row = $result_structure->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . " - " . $row['Null'] . "<br>";
}

$conn->close();
?>
