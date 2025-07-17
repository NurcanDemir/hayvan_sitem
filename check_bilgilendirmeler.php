<?php
include 'includes/db.php';

echo "<h2>Bilgilendirmeler Tablosu Kontrol</h2>";

// Tablo varlığını kontrol et
$result = $conn->query("SHOW TABLES LIKE 'bilgilendirmeler'");
if ($result->num_rows > 0) {
    echo "<p style='color: green;'>✓ Bilgilendirmeler tablosu mevcut</p>";
    
    // Kayıtları göster
    $result = $conn->query("SELECT * FROM bilgilendirmeler ORDER BY tarih DESC LIMIT 5");
    if ($result->num_rows > 0) {
        echo "<h3>Son 5 Bilgilendirme Kaydı:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Talep ID</th><th>Admin ID</th><th>Bilgi Türü</th><th>Mesaj</th><th>Tarih</th></tr>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['talep_id'] . "</td>";
            echo "<td>" . $row['admin_id'] . "</td>";
            echo "<td>" . $row['bilgi_turu'] . "</td>";
            echo "<td>" . substr($row['mesaj'], 0, 50) . "...</td>";
            echo "<td>" . $row['tarih'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Henüz bilgilendirme kaydı bulunmamaktadır.</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Bilgilendirmeler tablosu bulunamadı</p>";
}

// Tablo yapısını göster
$result = $conn->query("DESCRIBE bilgilendirmeler");
if ($result) {
    echo "<h3>Tablo Yapısı:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}
?>
