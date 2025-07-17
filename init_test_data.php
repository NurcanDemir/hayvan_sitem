<?php
// init_test_data.php - Test verisi oluşturma sahiplenme yorumları için

include('includes/db.php');

echo "<h2>Sahiplenme Yorumları Test Verisi</h2>";

// Test için örnek ilan oluştur
$sql_ilan = "INSERT INTO ilanlar (kullanici_id, baslik, aciklama, kategori_id, cins_id, yas, cinsiyet, asi_durumu, kisirlastirma, hastalik_id, il_id, ilce_id, foto, durum, tarih) 
VALUES (1, 'Test Sahiplenilmiş Kedi', 'Bu kedi sahiplenilmiş bir test verisidir', 1, 1, '2 yaşında', 'e', 'Tam', 1, NULL, 1, 1, 'test.jpg', 'sahiplenildi', NOW())
ON DUPLICATE KEY UPDATE durum = 'sahiplenildi'";

$result1 = $conn->query($sql_ilan);
$ilan_id = $conn->insert_id;

if ($result1) {
    echo "✅ Test ilanı oluşturuldu (ID: $ilan_id)<br>";
} else {
    echo "❌ Test ilanı oluşturulamadı: " . $conn->error . "<br>";
}

// Test sahiplenme talebi oluştur
$sql_talep = "INSERT INTO sahiplenme_istekleri (ilan_id, talep_eden_kullanici_id, ilan_sahibi_kullanici_id, talep_eden_ad_soyad, talep_eden_telefon, talep_eden_email, adres, mesaj, durum, sahiplenen_yorumu, yorum_tarihi) 
VALUES ($ilan_id, 2, 1, 'Ahmet Yılmaz', '5551234567', 'ahmet@email.com', 'Test Adres', 'Test sahiplenme mesajı', 'tamamlandı', 'Minnoşum çok mutlu! Harika bir arkadaş oldu. Çok teşekkürler!', NOW())
ON DUPLICATE KEY UPDATE sahiplenen_yorumu = 'Minnoşum çok mutlu! Harika bir arkadaş oldu. Çok teşekkürler!', yorum_tarihi = NOW()";

$result2 = $conn->query($sql_talep);
if ($result2) {
    echo "✅ Test sahiplenme talebi oluşturuldu<br>";
} else {
    echo "❌ Test sahiplenme talebi oluşturulamadı: " . $conn->error . "<br>";
}

echo "<h3>Test Tamamlandı!</h3>";
echo "<p><a href='index.php'>Ana sayfaya git</a> ve sahiplenenler bölümünü kontrol edin.</p>";
echo "<p><a href='ilan_detay.php?id=$ilan_id'>Test ilanının detayını gör</a></p>";

$conn->close();
?>

echo "İlanlar count: $ilanlar_count<br>";

if ($ilanlar_count > 0) {
    // Get first ilan for testing
    $result = $conn->query("SELECT id, kullanici_id FROM ilanlar LIMIT 1");
    $ilan = $result->fetch_assoc();
    
    echo "Using ilan ID: " . $ilan['id'] . "<br>";
    
    // Check if sahiplenme_istekleri table exists, if not create it
    $result = $conn->query("SHOW TABLES LIKE 'sahiplenme_istekleri'");
    if ($result->num_rows == 0) {
        echo "Creating sahiplenme_istekleri table...<br>";
        $create_sql = "CREATE TABLE sahiplenme_istekleri (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ilan_id INT NOT NULL,
            talep_eden_kullanici_id INT,
            ilan_sahibi_kullanici_id INT,
            talep_eden_ad_soyad VARCHAR(255) NOT NULL,
            talep_eden_telefon VARCHAR(20),
            talep_eden_email VARCHAR(255),
            adres TEXT,
            mesaj TEXT,
            durum ENUM('beklemede', 'onaylandı', 'reddedildi', 'tamamlandı') DEFAULT 'beklemede',
            admin_notlari TEXT,
            talep_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        if ($conn->query($create_sql)) {
            echo "✓ sahiplenme_istekleri table created<br>";
        } else {
            echo "✗ Error creating table: " . $conn->error . "<br>";
        }
    } else {
        echo "✓ sahiplenme_istekleri table already exists<br>";
    }
    
    // Insert a test request
    $stmt = $conn->prepare("INSERT INTO sahiplenme_istekleri (ilan_id, talep_eden_kullanici_id, ilan_sahibi_kullanici_id, talep_eden_ad_soyad, talep_eden_telefon, talep_eden_email, adres, mesaj, durum) VALUES (?, 1, ?, 'Test User', '555-1234', 'test@example.com', 'Test Address', 'Test message for adoption', 'beklemede')");
    $stmt->bind_param("ii", $ilan['id'], $ilan['kullanici_id']);
    
    if ($stmt->execute()) {
        echo "✓ Test adoption request created<br>";
        echo "Request ID: " . $conn->insert_id . "<br>";
    } else {
        echo "✗ Error creating test request: " . $stmt->error . "<br>";
    }
    $stmt->close();
} else {
    echo "No ilanlar found. Please create some ads first.<br>";
}

// Show current requests
echo "<h3>Current Adoption Requests:</h3>";
$result = $conn->query("SELECT * FROM sahiplenme_istekleri ORDER BY id DESC LIMIT 5");
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "ID: " . $row['id'] . ", Status: " . $row['durum'] . ", Name: " . $row['talep_eden_ad_soyad'] . ", Date: " . $row['talep_tarihi'] . "<br>";
    }
} else {
    echo "No requests found<br>";
}

$conn->close();
?>
