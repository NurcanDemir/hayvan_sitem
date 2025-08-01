<?php
// filepath: c:\xampp\htdocs\hayvan_sitem\database\create_messaging_tables.php
include(__DIR__ . "/../includes/db.php");

echo "<h2>Mesajlaşma Sistemi Veritabanı Kurulumu</h2>";
echo "<hr>";

// Create conversations table
$conversations_table = "CREATE TABLE IF NOT EXISTS conversations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ilan_id INT NOT NULL,
    ilan_sahibi_id INT NOT NULL,
    talep_eden_id INT NOT NULL,
    sahiplenme_istek_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (ilan_id) REFERENCES ilanlar(id) ON DELETE CASCADE,
    FOREIGN KEY (ilan_sahibi_id) REFERENCES kullanicilar(id) ON DELETE CASCADE,
    FOREIGN KEY (talep_eden_id) REFERENCES kullanicilar(id) ON DELETE CASCADE,
    FOREIGN KEY (sahiplenme_istek_id) REFERENCES sahiplenme_istekleri(id) ON DELETE CASCADE,
    UNIQUE KEY unique_conversation (ilan_id, talep_eden_id)
) CHARACTER SET utf8 COLLATE utf8_turkish_ci";

if (mysqli_query($conn, $conversations_table)) {
    echo "✅ 'conversations' tablosu oluşturuldu<br>";
} else {
    echo "❌ 'conversations' tablosu oluşturulamadı: " . mysqli_error($conn) . "<br>";
}

// Create messages table
$messages_table = "CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conversation_id INT NOT NULL,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message TEXT NOT NULL,
    message_type ENUM('text', 'image') DEFAULT 'text',
    is_read BOOLEAN DEFAULT FALSE,
    is_delivered BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES kullanicilar(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES kullanicilar(id) ON DELETE CASCADE,
    INDEX idx_conversation_created (conversation_id, created_at),
    INDEX idx_sender_receiver (sender_id, receiver_id)
) CHARACTER SET utf8 COLLATE utf8_turkish_ci";

if (mysqli_query($conn, $messages_table)) {
    echo "✅ 'messages' tablosu oluşturuldu<br>";
} else {
    echo "❌ 'messages' tablosu oluşturulamadı: " . mysqli_error($conn) . "<br>";
}

// Create blocked_users table
$blocked_users_table = "CREATE TABLE IF NOT EXISTS blocked_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    blocker_id INT NOT NULL,
    blocked_id INT NOT NULL,
    conversation_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (blocker_id) REFERENCES kullanicilar(id) ON DELETE CASCADE,
    FOREIGN KEY (blocked_id) REFERENCES kullanicilar(id) ON DELETE CASCADE,
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE SET NULL,
    UNIQUE KEY unique_block (blocker_id, blocked_id)
) CHARACTER SET utf8 COLLATE utf8_turkish_ci";

if (mysqli_query($conn, $blocked_users_table)) {
    echo "✅ 'blocked_users' tablosu oluşturuldu<br>";
} else {
    echo "❌ 'blocked_users' tablosu oluşturulamadı: " . mysqli_error($conn) . "<br>";
}

// Add columns to sahiplenme_istekleri table if they don't exist
$add_columns = [
    "ADD COLUMN IF NOT EXISTS durum_guncellenme_tarihi TIMESTAMP NULL",
    "ADD COLUMN IF NOT EXISTS mesajlasma_aktif BOOLEAN DEFAULT FALSE"
];

foreach ($add_columns as $column) {
    $alter_sql = "ALTER TABLE sahiplenme_istekleri $column";
    if (mysqli_query($conn, $alter_sql)) {
        echo "✅ sahiplenme_istekleri tablosuna kolon eklendi<br>";
    } else {
        // Column might already exist, check if it's a duplicate column error
        if (strpos(mysqli_error($conn), 'Duplicate column') === false) {
            echo "❌ sahiplenme_istekleri tablosu güncellenemedi: " . mysqli_error($conn) . "<br>";
        } else {
            echo "✅ Kolon zaten mevcut<br>";
        }
    }
}

echo "<hr>";
echo "<h3>✅ Mesajlaşma sistemi veritabanı kurulumu tamamlandı!</h3>";
echo "<p><a href='../admin/admin_panel.php'>← Admin paneline git</a></p>";

mysqli_close($conn);
?>
