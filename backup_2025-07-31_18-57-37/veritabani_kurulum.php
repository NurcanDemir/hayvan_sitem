<?php
// MySQL bağlantı testi
$host = "localhost";
$kullanici = "root";
$sifre = "";

echo "<h2>MySQL Bağlantı Testi</h2>";

// Önce MySQL'e bağlanmayı dene
$conn = mysqli_connect($host, $kullanici, $sifre);
if (!$conn) {
    echo "❌ MySQL bağlantısı başarısız: " . mysqli_connect_error() . "<br>";
    echo "<strong>Çözüm önerileri:</strong><br>";
    echo "1. XAMPP Control Panel'den MySQL'in çalıştığından emin olun<br>";
    echo "2. Root şifresini kontrol edin<br>";
    exit;
} else {
    echo "✅ MySQL bağlantısı başarılı<br>";
}

// Veritabanı var mı kontrol et
$veritabani = "hayvan_sitem";
$db_check = mysqli_select_db($conn, $veritabani);

if (!$db_check) {
    echo "❌ '$veritabani' veritabanı bulunamadı<br>";
    echo "<strong>Veritabanı oluşturuluyor...</strong><br>";
    
    // Veritabanını oluştur
    $create_db = "CREATE DATABASE $veritabani CHARACTER SET utf8 COLLATE utf8_turkish_ci";
    if (mysqli_query($conn, $create_db)) {
        echo "✅ '$veritabani' veritabanı oluşturuldu<br>";
        mysqli_select_db($conn, $veritabani);
    } else {
        echo "❌ Veritabanı oluşturulamadı: " . mysqli_error($conn) . "<br>";
        exit;
    }
} else {
    echo "✅ '$veritabani' veritabanı mevcut<br>";
}

// Kullanıcılar tablosu var mı kontrol et
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'kullanicilar'");
if (mysqli_num_rows($table_check) == 0) {
    echo "❌ 'kullanicilar' tablosu bulunamadı<br>";
    echo "<strong>Kullanıcılar tablosu oluşturuluyor...</strong><br>";
    
    $create_table = "CREATE TABLE kullanicilar (
        id INT AUTO_INCREMENT PRIMARY KEY,
        kullanici_adi VARCHAR(50) UNIQUE NOT NULL,
        sifre VARCHAR(255) NOT NULL,
        kullanici_tipi ENUM('normal', 'admin') DEFAULT 'normal',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) CHARACTER SET utf8 COLLATE utf8_turkish_ci";
    
    if (mysqli_query($conn, $create_table)) {
        echo "✅ 'kullanicilar' tablosu oluşturuldu<br>";
    } else {
        echo "❌ Tablo oluşturulamadı: " . mysqli_error($conn) . "<br>";
        exit;
    }
} else {
    echo "✅ 'kullanicilar' tablosu mevcut<br>";
}

// İlanlar tablosu kontrolü
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'ilanlar'");
if (mysqli_num_rows($table_check) == 0) {
    echo "❌ 'ilanlar' tablosu bulunamadı<br>";
    echo "<strong>İlanlar tablosu oluşturuluyor...</strong><br>";
    
    $create_table = "CREATE TABLE ilanlar (
        id INT AUTO_INCREMENT PRIMARY KEY,
        kullanici_id INT,
        baslik VARCHAR(200) NOT NULL,
        aciklama TEXT,
        fotograf VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (kullanici_id) REFERENCES kullanicilar(id)
    ) CHARACTER SET utf8 COLLATE utf8_turkish_ci";
    
    if (mysqli_query($conn, $create_table)) {
        echo "✅ 'ilanlar' tablosu oluşturuldu<br>";
    } else {
        echo "❌ İlanlar tablosu oluşturulamadı: " . mysqli_error($conn) . "<br>";
    }
} else {
    echo "✅ 'ilanlar' tablosu mevcut<br>";
}

// Favoriler tablosu kontrolü
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'favoriler'");
if (mysqli_num_rows($table_check) == 0) {
    echo "❌ 'favoriler' tablosu bulunamadı<br>";
    echo "<strong>Favoriler tablosu oluşturuluyor...</strong><br>";
    
    $create_table = "CREATE TABLE favoriler (
        id INT AUTO_INCREMENT PRIMARY KEY,
        kullanici_id INT,
        ilan_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (kullanici_id) REFERENCES kullanicilar(id),
        FOREIGN KEY (ilan_id) REFERENCES ilanlar(id)
    ) CHARACTER SET utf8 COLLATE utf8_turkish_ci";
    
    if (mysqli_query($conn, $create_table)) {
        echo "✅ 'favoriler' tablosu oluşturuldu<br>";
    } else {
        echo "❌ Favoriler tablosu oluşturulamadı: " . mysqli_error($conn) . "<br>";
    }
} else {
    echo "✅ 'favoriler' tablosu mevcut<br>";
}

echo "<hr>";
echo "<h3>✅ Veritabanı kurulumu tamamlandı!</h3>";
echo "<a href='admin_olustur.php' class='btn btn-primary'>Admin Kullanıcısı Oluştur</a><br><br>";
echo "<a href='index.php' class='btn btn-secondary'>Anasayfaya Git</a>";

mysqli_close($conn);
?>
