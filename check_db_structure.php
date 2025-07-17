<?php
// check_db_structure.php - Veritabanı yapısını kontrol et

include('includes/db.php');

echo "<h2>Veritabanı Yapısı Kontrolü</h2>";

// Kullanicilar tablosunu kontrol et
echo "<h3>Kullanicilar Tablosu</h3>";
$query = "DESCRIBE kullanicilar";
$result = $conn->query($query);
if ($result) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Alan</th><th>Tip</th><th>Null</th><th>Varsayılan</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>" . $row['Field'] . "</td><td>" . $row['Type'] . "</td><td>" . $row['Null'] . "</td><td>" . $row['Default'] . "</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>Kullanicilar tablosu bulunamadı!</p>";
}

// Sahiplenme istekleri tablosunu kontrol et
echo "<h3>Sahiplenme İstekleri Tablosu</h3>";
$query = "DESCRIBE sahiplenme_istekleri";
$result = $conn->query($query);
if ($result) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Alan</th><th>Tip</th><th>Null</th><th>Varsayılan</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>" . $row['Field'] . "</td><td>" . $row['Type'] . "</td><td>" . $row['Null'] . "</td><td>" . $row['Default'] . "</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>Sahiplenme istekleri tablosu bulunamadı!</p>";
}

$conn->close();
?>
