-- Hayvan Etkinlikleri Tablosu
CREATE TABLE IF NOT EXISTS hayvan_etkinlikleri (
    id INT AUTO_INCREMENT PRIMARY KEY,
    baslik VARCHAR(255) NOT NULL,
    aciklama TEXT NOT NULL,
    detay_aciklama LONGTEXT,
    etkinlik_tarihi DATETIME NOT NULL,
    etkinlik_saati TIME,
    bitis_tarihi DATETIME,
    adres TEXT,
    il_id INT DEFAULT 34,
    ilce_id INT DEFAULT 0,
    konum_lat DECIMAL(10,8),
    konum_lng DECIMAL(11,8),
    organizator VARCHAR(255),
    iletisim_telefon VARCHAR(20),
    iletisim_email VARCHAR(100),
    website VARCHAR(255),
    resim VARCHAR(255),
    kategori ENUM('sahiplendirme', 'saglik', 'egitim', 'bagis', 'diger') DEFAULT 'sahiplendirme',
    etkinlik_turu ENUM('sahiplendirme', 'egitim', 'bagis', 'tedavi', 'sosyal', 'yarışma', 'sergi', 'diğer') DEFAULT 'sahiplendirme',
    durum ENUM('yayin', 'taslak', 'iptal') DEFAULT 'yayin',
    aktif TINYINT(1) DEFAULT 1,
    olusturma_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    guncelleme_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Event Subscriptions (Abone / Hatırlat) Tablosu
CREATE TABLE IF NOT EXISTS event_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    email VARCHAR(255) NOT NULL,
    subscribed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    reminded_at DATETIME NULL,
    is_active TINYINT(1) DEFAULT 1,
    UNIQUE KEY unique_event_user (event_id, email),
    FOREIGN KEY (event_id) REFERENCES hayvan_etkinlikleri(id) ON DELETE CASCADE
);

-- Temizlik
DELETE FROM event_subscriptions WHERE event_id IN (1,2,3,4,5);
DELETE FROM hayvan_etkinlikleri WHERE id IN (1,2,3,4,5);

-- 5 Adet Gerçekçi Örnek Etkinlik Tohumlama (Seed)
INSERT INTO hayvan_etkinlikleri 
(id, baslik, aciklama, detay_aciklama, etkinlik_tarihi, etkinlik_saati, adres, il_id, organizator, iletisim_telefon, iletisim_email, kategori, durum, aktif) 
VALUES
(1, 'İstanbul Büyük Sahiplendirme Şenliği', 'Yüzlerce can dostumuz yeni sıcak yuvalarını arıyor! Barınaklardaki kedi ve köpeklerle tanışın.', 'İstanbul Büyükşehir Belediyesi ve Pati Dostları Derneği iş birliğiyle düzenlenen sahiplendirme şenliğinde veteriner kontrolü, ücretsiz mikroçip ve sahiplenme hediyeleri sunulmaktadır.', DATE_ADD(NOW(), INTERVAL 3 DAY), '11:00:00', 'Kadıköy Moda Sahil Parkı', 34, 'İBB & Pati Dostları', '0212 555 0101', 'info@patidostlari.org', 'sahiplendirme', 'yayin', 1),

(2, 'Ankara Ücretsiz Aşı ve Sağlık Taraması', 'Sokak hayvanları ve yeni sahiplenilen dostlarımız için ücretsiz genel sağlık kontrolü ve aşı günü.', 'Ankara Veteriner Hekimler Odası desteğiyle patili dostlarımızın genel sağlık taramaları, parazit aşıları ve bakım rehberliği ücretsiz verilecektir.', DATE_ADD(NOW(), INTERVAL 7 DAY), '10:00:00', 'Kuğulu Park, Çankaya', 6, 'Ankara Vet. Hekimler Odası', '0312 444 0202', 'iletisim@ankaravet.org', 'saglik', 'yayin', 1),

(3, 'İzmir Sokak Canları İçin Mama Bağış Koşusu', 'Sokakta yaşayan dostlarımız için adımlarımızı birleştiriyoruz. Toplanan mamalar barınaklara ulaştırılacaktır.', 'İzmir Kordon sahilinde düzenlenecek 5K bağış koşusuna katılarak veya mama desteği sağlayarak yüzlerce can dostumuza destek olabilirsiniz.', DATE_ADD(NOW(), INTERVAL 12 DAY), '09:30:00', 'Alsancak Kordon Boyu', 35, 'İzmir Hayvan Koruma Derneği', '0232 333 0303', 'destek@izmirhayvan.org', 'bagis', 'yayin', 1),

(4, 'İlk Defa Hayvan Sahiplenenler İçin Eğitim Semineri', 'Evcil hayvan bakımı, beslenme, pozitif eğitim ve psikoloji üzerine uzman veteriner sunumu.', 'Kedi ve köpeklerin eve uyumu, temel tuvalet eğitimi, doğru beslenme ve ilk yardım konularının anlatılacağı ücretsiz seminerimize tüm hayvanseverler davetlidir.', DATE_ADD(NOW(), INTERVAL 15 DAY), '14:00:00', 'Beşiktaş Kültür Merkezi', 34, 'Sıcak Patizi Eğitim Akademisi', '0212 222 0404', 'egitim@sicakpatizi.org', 'egitim', 'yayin', 1),

(5, 'Bursa Yuva Arayan Patiler Tanışma Günü', 'Bursa Nilüfer barınağındaki can dostlarımızla tanışın, onlara geçici veya kalıcı yuva olun.', 'Nilüfer Hayvan Bakımevi bahçesinde gerçekleşecek açık hava buluşmasında patili dostlarımızla vakit geçirebilir, sahiplenme başvurusu yapabilirsiniz.', DATE_ADD(NOW(), INTERVAL 20 DAY), '13:00:00', 'Nilüfer Hayvan Bakımevi, Nilüfer', 16, 'Nilüfer Belediyesi & Barınak Gönüllüleri', '0224 111 0505', 'bursa@barinak.org', 'sahiplendirme', 'yayin', 1);

-- Örnek Abonelikler (Subscribers)
INSERT INTO event_subscriptions (event_id, email, subscribed_at) VALUES
(1, 'ahmet@example.com', NOW()),
(1, 'elif@example.com', NOW()),
(1, 'mehmet@example.com', NOW()),
(1, 'ayse@example.com', NOW()),
(1, 'can@example.com', NOW()),
(2, 'zeynep@example.com', NOW()),
(2, 'burak@example.com', NOW()),
(2, 'selin@example.com', NOW()),
(3, 'deniz@example.com', NOW()),
(3, 'onur@example.com', NOW()),
(3, 'gamze@example.com', NOW()),
(3, 'serkan@example.com', NOW()),
(4, 'merve@example.com', NOW()),
(5, 'emre@example.com', NOW()),
(5, 'kubra@example.com', NOW());
