<?php
include('includes/db.php');

echo "<h2>Kategoriler:</h2>";
$res = $conn->query('SELECT * FROM kategoriler ORDER BY id');
while($row = $res->fetch_assoc()) {
    echo "ID: " . $row['id'] . " - Ad: " . $row['ad'] . "<br>";
}

echo "<h2>Cinsler:</h2>";
$res = $conn->query('SELECT c.id, c.ad as cins_ad, c.kategori_id, k.ad as kategori_ad FROM cinsler c JOIN kategoriler k ON c.kategori_id = k.id ORDER BY c.kategori_id, c.ad');
while($row = $res->fetch_assoc()) {
    echo "Cins ID: " . $row['id'] . " - Cins: " . $row['cins_ad'] . " - Kategori ID: " . $row['kategori_id'] . " - Kategori: " . $row['kategori_ad'] . "<br>";
}

echo "<h2>Cinsler Array Structure:</h2>";
$cinsler = [];
$res = $conn->query("SELECT id, kategori_id, ad FROM cinsler ORDER BY kategori_id, ad ASC");
while($row = $res->fetch_assoc()) {
    $cinsler[$row['kategori_id']][] = $row;
}
echo "<pre>";
print_r($cinsler);
echo "</pre>";
?>
