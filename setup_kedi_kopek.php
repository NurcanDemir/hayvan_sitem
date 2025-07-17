<?php
include('includes/db.php');

echo "<h2>Sadece Kedi ve Köpek Kategorileri İçin Düzenleme</h2>";

// Önce mevcut kategorileri görelim
echo "<h3>Mevcut Kategoriler:</h3>";
$res = $conn->query("SELECT * FROM kategoriler ORDER BY id");
while($row = $res->fetch_assoc()) {
    echo "ID: " . $row['id'] . " - Ad: " . $row['ad'] . "<br>";
}

// Sadece kedi ve köpek kategorilerini tutacağız
echo "<h3>İşlemler:</h3>";

// 1. Önce kedi ve köpek kategorilerini kontrol et/ekle
$kedi_id = null;
$kopek_id = null;

// Kedi kategorisi var mı kontrol et
$res = $conn->query("SELECT id FROM kategoriler WHERE ad = 'Kedi'");
if ($res->num_rows > 0) {
    $kedi_id = $res->fetch_assoc()['id'];
    echo "✓ Kedi kategorisi mevcut (ID: $kedi_id)<br>";
} else {
    $conn->query("INSERT INTO kategoriler (ad) VALUES ('Kedi')");
    $kedi_id = $conn->insert_id;
    echo "✓ Kedi kategorisi eklendi (ID: $kedi_id)<br>";
}

// Köpek kategorisi var mı kontrol et
$res = $conn->query("SELECT id FROM kategoriler WHERE ad = 'Köpek'");
if ($res->num_rows > 0) {
    $kopek_id = $res->fetch_assoc()['id'];
    echo "✓ Köpek kategorisi mevcut (ID: $kopek_id)<br>";
} else {
    $conn->query("INSERT INTO kategoriler (ad) VALUES ('Köpek')");
    $kopek_id = $conn->insert_id;
    echo "✓ Köpek kategorisi eklendi (ID: $kopek_id)<br>";
}

// 2. Diğer kategorileri sil (önce foreign key kısıtlamaları nedeniyle ilanları kontrol et)
echo "<h4>Diğer kategorileri temizleme:</h4>";
$other_categories = $conn->query("SELECT id, ad FROM kategoriler WHERE id NOT IN ($kedi_id, $kopek_id)");
while($cat = $other_categories->fetch_assoc()) {
    // Bu kategorideki ilanları kontrol et
    $ilan_check = $conn->query("SELECT COUNT(*) as count FROM ilanlar WHERE kategori_id = " . $cat['id']);
    $ilan_count = $ilan_check->fetch_assoc()['count'];
    
    if ($ilan_count > 0) {
        echo "⚠ '{$cat['ad']}' kategorisinde $ilan_count ilan var. Bu ilanlar silinecek.<br>";
        $conn->query("DELETE FROM ilanlar WHERE kategori_id = " . $cat['id']);
    }
    
    // Cinsleri de sil
    $conn->query("DELETE FROM cinsler WHERE kategori_id = " . $cat['id']);
    
    // Kategoriyi sil
    $conn->query("DELETE FROM kategoriler WHERE id = " . $cat['id']);
    echo "✓ '{$cat['ad']}' kategorisi silindi<br>";
}

// 3. Kedi ve köpek cinslerini ekle
echo "<h4>Kedi ve Köpek cinslerini ekleme:</h4>";

// Önce mevcut cinsleri temizle
$conn->query("DELETE FROM cinsler WHERE kategori_id IN ($kedi_id, $kopek_id)");

// Kedi cinsleri
$kedi_cinsleri = [
    'Tekir',
    'Van Kedisi',
    'Ankara Kedisi',
    'Persian',
    'British Shorthair',
    'Scottish Fold',
    'Maine Coon',
    'Siamese',
    'Ragdoll',
    'Diğer'
];

foreach($kedi_cinsleri as $cins) {
    $stmt = $conn->prepare("INSERT INTO cinsler (kategori_id, ad) VALUES (?, ?)");
    $stmt->bind_param("is", $kedi_id, $cins);
    $stmt->execute();
}
echo "✓ " . count($kedi_cinsleri) . " kedi cinsi eklendi<br>";

// Köpek cinsleri
$kopek_cinsleri = [
    'Kangal',
    'Akbaş',
    'Golden Retriever',
    'Labrador',
    'German Shepherd',
    'Husky',
    'Bulldog',
    'Beagle',
    'Rottweiler',
    'Poodle',
    'Chihuahua',
    'Diğer'
];

foreach($kopek_cinsleri as $cins) {
    $stmt = $conn->prepare("INSERT INTO cinsler (kategori_id, ad) VALUES (?, ?)");
    $stmt->bind_param("is", $kopek_id, $cins);
    $stmt->execute();
}
echo "✓ " . count($kopek_cinsleri) . " köpek cinsi eklendi<br>";

// 4. Hastalıkları da güncelle (sadece kedi ve köpek için ortak hastalıklar)
echo "<h4>Hastalıkları güncelleme:</h4>";

// Önce hastalıkları temizle
$conn->query("DELETE FROM hastaliklar_cinsler");
$conn->query("DELETE FROM hastaliklar");

// Ortak hastalıklar ekle
$hastaliklar = [
    'Sağlıklı',
    'Parazit',
    'Deri Hastalığı',
    'Göz Enfeksiyonu',
    'Solunum Problemi',
    'Yaralanma',
    'Diğer'
];

$hastalik_ids = [];
foreach($hastaliklar as $hastalik) {
    $stmt = $conn->prepare("INSERT INTO hastaliklar (ad) VALUES (?)");
    $stmt->bind_param("s", $hastalik);
    $stmt->execute();
    $hastalik_ids[] = $conn->insert_id;
}

// Tüm hastalıkları tüm cinslere bağla
$all_cins_ids = [];
$res = $conn->query("SELECT id FROM cinsler WHERE kategori_id IN ($kedi_id, $kopek_id)");
while($row = $res->fetch_assoc()) {
    $all_cins_ids[] = $row['id'];
}

foreach($all_cins_ids as $cins_id) {
    foreach($hastalik_ids as $hastalik_id) {
        $stmt = $conn->prepare("INSERT INTO hastaliklar_cinsler (cins_id, hastalik_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $cins_id, $hastalik_id);
        $stmt->execute();
    }
}

echo "✓ " . count($hastaliklar) . " hastalık eklendi ve tüm cinslere bağlandı<br>";

echo "<h3>✅ Tamamlandı!</h3>";
echo "Artık sadece Kedi ve Köpek kategorileri var.<br>";
echo "<a href='ilanlar.php'>İlanlar sayfasını test et</a>";
?>
