<?php
include('includes/db.php');

echo "<h2>Kapsamlı Hayvan Kategorileri ve Cinsleri Kurulumu</h2>";

// Önce mevcut kategorileri görelim
echo "<h3>Mevcut Kategoriler:</h3>";
$res = $conn->query("SELECT * FROM kategoriler ORDER BY id");
while($row = $res->fetch_assoc()) {
    echo "ID: " . $row['id'] . " - Ad: " . $row['ad'] . "<br>";
}

echo "<h3>İşlemler:</h3>";

// 1. Önce tüm tabloları temizle
$conn->query("DELETE FROM hastaliklar_cinsler");
$conn->query("DELETE FROM hastaliklar");
$conn->query("DELETE FROM cinsler");
$conn->query("DELETE FROM kategoriler");

echo "✓ Tüm tablolar temizlendi<br>";

// 2. Kategorileri ekle
$kategoriler = [
    'Kedi',
    'Köpek', 
    'Kuş',
    'Sürüngen'
];

$kategori_ids = [];
foreach($kategoriler as $kategori) {
    $stmt = $conn->prepare("INSERT INTO kategoriler (ad) VALUES (?)");
    $stmt->bind_param("s", $kategori);
    $stmt->execute();
    $kategori_ids[$kategori] = $conn->insert_id;
}

echo "✓ " . count($kategoriler) . " kategori eklendi<br>";

// 3. Cinsleri ekle
$cinsler = [
    'Kedi' => [
        'Tekir',
        'Van Kedisi',
        'Ankara Kedisi',
        'Persian',
        'British Shorthair',
        'Scottish Fold',
        'Maine Coon',
        'Siamese',
        'Ragdoll',
        'Sphynx',
        'Bengal',
        'Diğer'
    ],
    'Köpek' => [
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
        'Pitbull',
        'Cocker Spaniel',
        'Boxer',
        'Diğer'
    ],
    'Kuş' => [
        'Muhabbet Kuşu',
        'Kanarya',
        'Papağan',
        'Cennet Papağanı',
        'Kakadu',
        'Güvercin',
        'Saka',
        'Bülbül',
        'Finch',
        'Sevda Kuşu',
        'Diğer'
    ],
    'Sürüngen' => [
        'Iguana',
        'Kaplumbağa',
        'Yılan',
        'Geko',
        'Bearded Dragon',
        'Chameleon',
        'Leopar Gecko',
        'Diğer'
    ]
];

$cins_ids = [];
foreach($cinsler as $kategori => $kategori_cinsleri) {
    foreach($kategori_cinsleri as $cins) {
        $stmt = $conn->prepare("INSERT INTO cinsler (kategori_id, ad) VALUES (?, ?)");
        $stmt->bind_param("is", $kategori_ids[$kategori], $cins);
        $stmt->execute();
        $cins_ids[$kategori][] = $conn->insert_id;
    }
    echo "✓ " . count($kategori_cinsleri) . " $kategori cinsi eklendi<br>";
}

// 4. Hastalıkları ekle
$hastaliklar = [
    'Kedi' => [
        'Sağlıklı',
        'Parazit',
        'Deri Hastalığı',
        'Göz Enfeksiyonu',
        'Solunum Problemi',
        'Yaralanma',
        'Kediler İçin Grip',
        'Böbrek Hastalığı',
        'Diş Problemleri',
        'Diğer'
    ],
    'Köpek' => [
        'Sağlıklı',
        'Parazit',
        'Deri Hastalığı',
        'Göz Enfeksiyonu',
        'Solunum Problemi',
        'Yaralanma',
        'Köpek Gribi',
        'Eklem Problemleri',
        'Kalp Hastalığı',
        'Diğer'
    ],
    'Kuş' => [
        'Sağlıklı',
        'Parazit',
        'Tüy Problemi',
        'Göz Enfeksiyonu',
        'Solunum Problemi',
        'Yaralanma',
        'Kanat Problemi',
        'Sindirim Problemi',
        'Diğer'
    ],
    'Sürüngen' => [
        'Sağlıklı',
        'Parazit',
        'Deri Problemi',
        'Göz Enfeksiyonu',
        'Solunum Problemi',
        'Yaralanma',
        'Kabuk Problemi',
        'Sindirim Problemi',
        'Diğer'
    ]
];

$hastalik_ids = [];
foreach($hastaliklar as $kategori => $kategori_hastaliklari) {
    foreach($kategori_hastaliklari as $hastalik) {
        $stmt = $conn->prepare("INSERT INTO hastaliklar (ad) VALUES (?)");
        $stmt->bind_param("s", $hastalik);
        $stmt->execute();
        $hastalik_ids[$kategori][] = $conn->insert_id;
    }
    echo "✓ " . count($kategori_hastaliklari) . " $kategori hastalığı eklendi<br>";
}

// 5. Hastalık-Cins ilişkilerini kur
echo "<h4>Hastalık-Cins ilişkilerini kurma:</h4>";
$total_relations = 0;

foreach($cins_ids as $kategori => $kategori_cins_ids) {
    if (isset($hastalik_ids[$kategori])) {
        foreach($kategori_cins_ids as $cins_id) {
            foreach($hastalik_ids[$kategori] as $hastalik_id) {
                $stmt = $conn->prepare("INSERT INTO hastaliklar_cinsler (cins_id, hastalik_id) VALUES (?, ?)");
                $stmt->bind_param("ii", $cins_id, $hastalik_id);
                $stmt->execute();
                $total_relations++;
            }
        }
    }
}

echo "✓ $total_relations hastalık-cins ilişkisi kuruldu<br>";

// 6. Sonuçları göster
echo "<h3>✅ Kurulum Tamamlandı!</h3>";
echo "<h4>Oluşturulan Kategoriler:</h4>";
foreach($kategori_ids as $kategori => $id) {
    echo "- $kategori (ID: $id)<br>";
}

echo "<h4>Özet:</h4>";
echo "- " . count($kategoriler) . " kategori<br>";
echo "- " . array_sum(array_map('count', $cinsler)) . " cins<br>";
echo "- " . array_sum(array_map('count', $hastaliklar)) . " hastalık<br>";
echo "- $total_relations hastalık-cins ilişkisi<br>";

echo "<br><a href='ilanlar.php' style='background: green; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>İlanlar sayfasını test et</a>";
echo " <a href='ilan_ekle.php' style='background: blue; color: white; padding: 10px; text-decoration: none; border-radius: 5px; margin-left: 10px;'>İlan ekle sayfasını test et</a>";
?>
