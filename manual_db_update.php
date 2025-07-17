<?php
// manual_db_update.php - Manuel veritabanı güncelleme
echo "<h2>Manuel Veritabanı Güncelleme</h2>";

try {
    // Veritabanı bağlantısı
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "hayvan_sitem";

    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        die("Bağlantı hatası: " . $conn->connect_error);
    }

    echo "✅ Veritabanı bağlantısı başarılı<br>";

    // Önce tablo yapısını kontrol et
    echo "<h3>Mevcut Tablo Yapısı:</h3>";
    $result = $conn->query("DESCRIBE sahiplenme_istekleri");
    $columns = [];
    while($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")<br>";
    }

    // sahiplenen_yorumu kolonu var mı kontrol et
    if (!in_array('sahiplenen_yorumu', $columns)) {
        echo "<h3>sahiplenen_yorumu kolonu ekleniyor...</h3>";
        $sql1 = "ALTER TABLE sahiplenme_istekleri ADD COLUMN sahiplenen_yorumu TEXT NULL";
        if ($conn->query($sql1) === TRUE) {
            echo "✅ sahiplenen_yorumu kolonu eklendi<br>";
        } else {
            echo "❌ Hata: " . $conn->error . "<br>";
        }
    } else {
        echo "✅ sahiplenen_yorumu kolonu zaten mevcut<br>";
    }

    // yorum_tarihi kolonu var mı kontrol et
    if (!in_array('yorum_tarihi', $columns)) {
        echo "<h3>yorum_tarihi kolonu ekleniyor...</h3>";
        $sql2 = "ALTER TABLE sahiplenme_istekleri ADD COLUMN yorum_tarihi TIMESTAMP NULL";
        if ($conn->query($sql2) === TRUE) {
            echo "✅ yorum_tarihi kolonu eklendi<br>";
        } else {
            echo "❌ Hata: " . $conn->error . "<br>";
        }
    } else {
        echo "✅ yorum_tarihi kolonu zaten mevcut<br>";
    }

    // Güncellenmiş tablo yapısını göster
    echo "<h3>Güncellenmiş Tablo Yapısı:</h3>";
    $result = $conn->query("DESCRIBE sahiplenme_istekleri");
    while($row = $result->fetch_assoc()) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")<br>";
    }

    // Test verisi ekle
    echo "<h3>Test Verisi Ekleniyor...</h3>";
    $sql_test = "INSERT INTO sahiplenme_istekleri (ilan_id, talep_eden_kullanici_id, ilan_sahibi_kullanici_id, talep_eden_ad_soyad, talep_eden_telefon, talep_eden_email, adres, mesaj, durum, sahiplenen_yorumu, yorum_tarihi) 
    VALUES (1, 1, 1, 'Test Kullanıcı', '5551234567', 'test@email.com', 'Test Adres', 'Test mesaj', 'tamamlandı', 'Çok mutluyum! Minnoş harika bir arkadaş oldu.', NOW())
    ON DUPLICATE KEY UPDATE sahiplenen_yorumu = 'Çok mutluyum! Minnoş harika bir arkadaş oldu.', yorum_tarihi = NOW()";
    
    if ($conn->query($sql_test) === TRUE) {
        echo "✅ Test verisi eklendi<br>";
    } else {
        echo "❌ Test verisi eklenirken hata: " . $conn->error . "<br>";
    }

    $conn->close();
    echo "<h3>✅ Güncelleme Tamamlandı!</h3>";
    echo "<p><a href='index.php'>Ana sayfaya git</a></p>";
    echo "<p><a href='sahiplenen_yorum_ekle.php?talep_id=1'>Yorum ekleme sayfasını test et</a></p>";

} catch (Exception $e) {
    echo "❌ Hata: " . $e->getMessage();
}
?>
