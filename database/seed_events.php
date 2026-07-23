<?php
// database/seed_events.php - Veritabanı şema ve tohumlama (seed) kurulumu
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: text/html; charset=utf-8');

echo "<h2>🐾 Sıcak Patizi - Etkinlik Veritabanı Kurulumu</h2>";

// 1. hayvan_etkinlikleri tablosuna eksik sütunları ekle
$alter_queries = [
    "ALTER TABLE hayvan_etkinlikleri ADD COLUMN IF NOT EXISTS detay_aciklama LONGTEXT AFTER aciklama",
    "ALTER TABLE hayvan_etkinlikleri ADD COLUMN IF NOT EXISTS etkinlik_turu VARCHAR(50) DEFAULT 'sahiplendirme' AFTER kategori",
    "ALTER TABLE hayvan_etkinlikleri ADD COLUMN IF NOT EXISTS durum VARCHAR(20) DEFAULT 'yayin' AFTER aktif",
    "ALTER TABLE hayvan_etkinlikleri ADD COLUMN IF NOT EXISTS organizator VARCHAR(255) AFTER ilce_id",
    "ALTER TABLE hayvan_etkinlikleri ADD COLUMN IF NOT EXISTS iletisim_telefon VARCHAR(50) AFTER organizator",
    "ALTER TABLE hayvan_etkinlikleri ADD COLUMN IF NOT EXISTS iletisim_email VARCHAR(100) AFTER iletisim_telefon"
];

foreach ($alter_queries as $alter_sql) {
    @$conn->query($alter_sql);
}

// 2. event_subscriptions tablosunu oluştur
$create_subscriptions_sql = "
CREATE TABLE IF NOT EXISTS event_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    email VARCHAR(255) NOT NULL,
    subscribed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    reminded_at DATETIME NULL,
    is_active TINYINT(1) DEFAULT 1,
    UNIQUE KEY unique_event_user (event_id, email)
);
";
if ($conn->query($create_subscriptions_sql)) {
    echo "<p style='color:green;'>✓ event_subscriptions tablosu hazır.</p>";
} else {
    echo "<p style='color:red;'>Hata (subscriptions): " . htmlspecialchars($conn->error) . "</p>";
}

// 3. events VIEW oluştur (eğer başka sorgular 'events' tablosunu hedefliyorsa)
$conn->query("CREATE OR REPLACE VIEW events AS SELECT 
    id, 
    baslik AS title, 
    aciklama AS description, 
    kategori AS category, 
    il_id AS city, 
    adres AS location_detail, 
    etkinlik_tarihi AS event_date, 
    resim AS image_url, 
    durum AS status, 
    created_at, 
    created_at AS updated_at 
    FROM hayvan_etkinlikleri");

// 4. Eski örnek verileri temizle
$conn->query("DELETE FROM event_subscriptions WHERE event_id IN (1, 2, 3, 4, 5)");
$conn->query("DELETE FROM hayvan_etkinlikleri WHERE id IN (1, 2, 3, 4, 5)");

// 5. 5 Adet Gerçekçi Örnek Etkinliği Ekle
$events = [
    [
        'id' => 1,
        'baslik' => 'İstanbul Büyük Sahiplendirme Şenliği',
        'aciklama' => 'Yüzlerce can dostumuz yeni sıcak yuvalarını arıyor! Barınaklardaki kedi ve köpeklerle tanışın.',
        'detay_aciklama' => 'İstanbul Büyükşehir Belediyesi ve Pati Dostları Derneği iş birliğiyle düzenlenen sahiplendirme şenliğinde ücretsiz veteriner kontrolü ve sahiplenme hediyeleri verilecektir.',
        'etkinlik_tarihi' => date('Y-m-d', strtotime('+3 days')),
        'etkinlik_saati' => '11:00:00',
        'adres' => 'Kadıköy Moda Sahil Parkı',
        'il_id' => 34,
        'ilce_id' => 0,
        'organizator' => 'İBB & Pati Dostları',
        'iletisim_telefon' => '0212 555 0101',
        'iletisim_email' => 'info@patidostlari.org',
        'resim' => 'https://images.unsplash.com/photo-1548767797-d8c844163c4c?w=600&auto=format&fit=crop&q=80',
        'kategori' => 'sahiplendirme',
        'etkinlik_turu' => 'sahiplendirme',
        'aktif' => 1,
        'durum' => 'yayin'
    ],
    [
        'id' => 2,
        'baslik' => 'Ankara Ücretsiz Aşı ve Sağlık Taraması',
        'aciklama' => 'Sokak hayvanları ve yeni sahiplenilen dostlarımız için ücretsiz genel sağlık kontrolü ve aşı günü.',
        'detay_aciklama' => 'Ankara Veteriner Hekimler Odası desteğiyle patili dostlarımızın genel sağlık taramaları ve parazit aşıları ücretsiz yapılacaktır.',
        'etkinlik_tarihi' => date('Y-m-d', strtotime('+7 days')),
        'etkinlik_saati' => '10:00:00',
        'adres' => 'Kuğulu Park, Çankaya',
        'il_id' => 6,
        'ilce_id' => 0,
        'organizator' => 'Ankara Vet. Hekimler Odası',
        'iletisim_telefon' => '0312 444 0202',
        'iletisim_email' => 'iletisim@ankaravet.org',
        'resim' => 'https://images.unsplash.com/photo-1628009368231-7bb7cfcb0def?w=600&auto=format&fit=crop&q=80',
        'kategori' => 'saglik',
        'etkinlik_turu' => 'tedavi',
        'aktif' => 1,
        'durum' => 'yayin'
    ],
    [
        'id' => 3,
        'baslik' => 'İzmir Sokak Canları İçin Mama Bağış Koşusu',
        'aciklama' => 'Sokakta yaşayan dostlarımız için adımlarımızı birleştiriyoruz. Toplanan mamalar barınaklara ulaştırılacaktır.',
        'detay_aciklama' => 'İzmir Kordon sahilinde düzenlenecek 5K bağış koşusuna katılarak veya mama desteği sağlayarak yüzlerce can dostumuza umut olabilirsiniz.',
        'etkinlik_tarihi' => date('Y-m-d', strtotime('+12 days')),
        'etkinlik_saati' => '09:30:00',
        'adres' => 'Alsancak Kordon Boyu',
        'il_id' => 35,
        'ilce_id' => 0,
        'organizator' => 'İzmir Hayvan Koruma Derneği',
        'iletisim_telefon' => '0232 333 0303',
        'iletisim_email' => 'destek@izmirhayvan.org',
        'resim' => 'https://images.unsplash.com/photo-1535294435445-d7249524ef2e?w=600&auto=format&fit=crop&q=80',
        'kategori' => 'bagis',
        'etkinlik_turu' => 'bagis',
        'aktif' => 1,
        'durum' => 'yayin'
    ],
    [
        'id' => 4,
        'baslik' => 'İlk Defa Hayvan Sahiplenenler İçin Eğitim Semineri',
        'aciklama' => 'Evcil hayvan bakımı, beslenme, pozitif eğitim ve psikoloji üzerine uzman veteriner sunumu.',
        'detay_aciklama' => 'Kedi ve köpeklerin eve uyumu, temel eğitim, doğru beslenme ve acil bakım konularının anlatılacağı ücretsiz seminerimize davetlisiniz.',
        'etkinlik_tarihi' => date('Y-m-d', strtotime('+15 days')),
        'etkinlik_saati' => '14:00:00',
        'adres' => 'Beşiktaş Kültür Merkezi',
        'il_id' => 34,
        'ilce_id' => 0,
        'organizator' => 'Sıcak Patizi Eğitim Akademisi',
        'iletisim_telefon' => '0212 222 0404',
        'iletisim_email' => 'egitim@sicakpatizi.org',
        'resim' => 'https://images.unsplash.com/photo-1587300003388-59208cc962cb?w=600&auto=format&fit=crop&q=80',
        'kategori' => 'egitim',
        'etkinlik_turu' => 'egitim',
        'aktif' => 1,
        'durum' => 'yayin'
    ],
    [
        'id' => 5,
        'baslik' => 'Bursa Yuva Arayan Patiler Tanışma Günü',
        'aciklama' => 'Bursa Nilüfer barınağındaki can dostlarımızla tanışın, onlara geçici veya kalıcı sıcak bir yuva olun.',
        'detay_aciklama' => 'Açık hava buluşmasında patili dostlarımızla vakit geçirebilir ve rehber eşliğinde sahiplenme başvurunuzu tamamlayabilirsiniz.',
        'etkinlik_tarihi' => date('Y-m-d', strtotime('+20 days')),
        'etkinlik_saati' => '13:00:00',
        'adres' => 'Nilüfer Hayvan Bakımevi, Nilüfer',
        'il_id' => 16,
        'ilce_id' => 0,
        'organizator' => 'Nilüfer Belediyesi & Barınak Gönüllüleri',
        'iletisim_telefon' => '0224 111 0505',
        'iletisim_email' => 'bursa@barinak.org',
        'resim' => 'https://images.unsplash.com/photo-1601758228041-f3b2795255f1?w=600&auto=format&fit=crop&q=80',
        'kategori' => 'sahiplendirme',
        'etkinlik_turu' => 'sahiplendirme',
        'aktif' => 1,
        'durum' => 'yayin'
    ]
];

$insert_stmt = $conn->prepare("INSERT INTO hayvan_etkinlikleri 
(id, baslik, aciklama, detay_aciklama, etkinlik_tarihi, etkinlik_saati, adres, il_id, ilce_id, organizator, iletisim_telefon, iletisim_email, resim, kategori, etkinlik_turu, aktif, durum) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

foreach ($events as $ev) {
    $insert_stmt->bind_param("issssssiissssssis", 
        $ev['id'], $ev['baslik'], $ev['aciklama'], $ev['detay_aciklama'], $ev['etkinlik_tarihi'], $ev['etkinlik_saati'],
        $ev['adres'], $ev['il_id'], $ev['ilce_id'], $ev['organizator'], $ev['iletisim_telefon'], $ev['iletisim_email'],
        $ev['resim'], $ev['kategori'], $ev['etkinlik_turu'], $ev['aktif'], $ev['durum']
    );
    $insert_stmt->execute();
}
$insert_stmt->close();
echo "<p style='color:green;'>✓ 5 adet örnek etkinlik başarıyla veritabanına eklendi.</p>";

// 6. Örnek Abonelikleri Ekle
$subscriptions = [
    [1, 'ahmet@example.com'],
    [1, 'elif@example.com'],
    [1, 'mehmet@example.com'],
    [1, 'ayse@example.com'],
    [1, 'can@example.com'],
    [2, 'zeynep@example.com'],
    [2, 'burak@example.com'],
    [2, 'selin@example.com'],
    [3, 'deniz@example.com'],
    [3, 'onur@example.com'],
    [3, 'gamze@example.com'],
    [3, 'serkan@example.com'],
    [4, 'merve@example.com'],
    [4, 'tolga@example.com'],
    [5, 'emre@example.com'],
    [5, 'kubra@example.com']
];

$sub_stmt = $conn->prepare("INSERT IGNORE INTO event_subscriptions (event_id, email, subscribed_at) VALUES (?, ?, NOW())");
foreach ($subscriptions as $sub) {
    $sub_stmt->bind_param("is", $sub[0], $sub[1]);
    $sub_stmt->execute();
}
$sub_stmt->close();

echo "<p style='color:green; font-weight:bold;'>✅ Veritabanı ve örnek veriler (5 Etkinlik, 16 Abonelik) başarıyla kuruldu!</p>";
echo "<p><a href='../index.php'>Ana Sayfaya Dön</a> | <a href='../etkinlikler.php'>Etkinlikler Sayfasına Git</a></p>";

$conn->close();
?>
