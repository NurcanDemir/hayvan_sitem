<?php
include('includes/db.php');

echo "<h2>Hastalık Sistemi Düzeltme</h2>";

// Önce mevcut hastalık-cins ilişkilerini kontrol edelim
echo "<h3>Mevcut Hastalık-Cins İlişkileri:</h3>";
$res = $conn->query("SELECT hc.cins_id, h.ad as hastalik_ad, c.ad as cins_ad, k.ad as kategori_ad 
                     FROM hastaliklar_cinsler hc
                     JOIN hastaliklar h ON hc.hastalik_id = h.id
                     JOIN cinsler c ON hc.cins_id = c.id
                     JOIN kategoriler k ON c.kategori_id = k.id
                     ORDER BY k.ad, c.ad, h.ad");

$current_category = '';
$current_breed = '';
while($row = $res->fetch_assoc()) {
    if ($current_category != $row['kategori_ad']) {
        $current_category = $row['kategori_ad'];
        echo "<h4>$current_category:</h4>";
    }
    if ($current_breed != $row['cins_ad']) {
        $current_breed = $row['cins_ad'];
        echo "<strong>$current_breed:</strong> ";
    }
    echo $row['hastalik_ad'] . ", ";
    if ($current_breed != $row['cins_ad']) {
        echo "<br>";
    }
}

// Şimdi JavaScript için gerekli veri yapısını oluşturalım
echo "<h3>JavaScript için Veri Yapısı Test:</h3>";
$hastaliklar_cins = [];
$res = $conn->query("SELECT hc.cins_id, h.id, h.ad FROM hastaliklar_cinsler hc
                     JOIN hastaliklar h ON hc.hastalik_id = h.id ORDER BY hc.cins_id, h.ad ASC");
while($row = $res->fetch_assoc()) {
    $hastaliklar_cins[$row['cins_id']][] = [
        'id' => $row['id'],
        'ad' => $row['ad']
    ];
}

echo "<pre>";
print_r($hastaliklar_cins);
echo "</pre>";

// Test için bir cins seçelim
echo "<h3>Test - Cins ID 1 için hastalıklar:</h3>";
if (isset($hastaliklar_cins[1])) {
    foreach($hastaliklar_cins[1] as $hastalik) {
        echo "- " . $hastalik['ad'] . " (ID: " . $hastalik['id'] . ")<br>";
    }
} else {
    echo "Cins ID 1 için hastalık bulunamadı.";
}

echo "<br><a href='ilanlar.php'>İlanlar sayfasını test et</a>";
?>
