-- Hayvan Barınakları Tablosu
CREATE TABLE IF NOT EXISTS hayvan_barinaklari (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad VARCHAR(255) NOT NULL,
    adres TEXT NOT NULL,
    telefon VARCHAR(20),
    email VARCHAR(100),
    website VARCHAR(255),
    lat DECIMAL(10,8) NOT NULL,
    lng DECIMAL(11,8) NOT NULL,
    aciklama TEXT,
    kapasite INT DEFAULT 0,
    aktif_hayvan_sayisi INT DEFAULT 0,
    calisma_saatleri TEXT,
    ozellikler JSON, -- ["kedi", "köpek", "kuş", "tedavi", "aşı"] gibi
    resim VARCHAR(255),
    durum ENUM('aktif', 'pasif') DEFAULT 'aktif',
    olusturma_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    guncelleme_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Hayvan Etkinlikleri Tablosu
CREATE TABLE IF NOT EXISTS hayvan_etkinlikleri (
    id INT AUTO_INCREMENT PRIMARY KEY,
    baslik VARCHAR(255) NOT NULL,
    aciklama TEXT NOT NULL,
    detay_aciklama LONGTEXT,
    etkinlik_tarihi DATETIME NOT NULL,
    bitis_tarihi DATETIME,
    adres TEXT,
    konum_lat DECIMAL(10,8),
    konum_lng DECIMAL(11,8),
    organizator VARCHAR(255),
    iletisim_telefon VARCHAR(20),
    iletisim_email VARCHAR(100),
    website VARCHAR(255),
    resim VARCHAR(255),
    etkinlik_turu ENUM('sahiplendirme', 'egitim', 'bagis', 'tedavi', 'sosyal', 'yarışma', 'sergi', 'diğer') DEFAULT 'diğer',
    durum ENUM('yayin', 'taslak', 'iptal') DEFAULT 'yayin',
    olusturan_admin_id INT,
    olusturma_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    guncelleme_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (olusturan_admin_id) REFERENCES kullanicilar(id) ON DELETE SET NULL
);

-- Örnek Barınak Verileri
INSERT INTO hayvan_barinaklari (ad, adres, telefon, email, lat, lng, aciklama, kapasite, aktif_hayvan_sayisi, calisma_saatleri, ozellikler) VALUES
('İstanbul Hayvan Barınağı', 'Sarıyer, İstanbul', '+90 212 555 0101', 'info@istanbulbarinak.com', 41.1579, 29.0106, 'İstanbul\'un en büyük hayvan barınağı', 500, 320, '08:00-18:00', '["kedi", "köpek", "tedavi", "aşı", "kısırlaştırma"]'),
('Ankara Büyükşehir Barınağı', 'Çankaya, Ankara', '+90 312 555 0202', 'info@ankarabarinak.com', 39.9208, 32.8541, 'Modern tesislerle hizmet veren barınak', 300, 180, '09:00-17:00', '["kedi", "köpek", "kuş", "tedavi"]'),
('İzmir Hayvan Hastanesi ve Barınağı', 'Bornova, İzmir', '+90 232 555 0303', 'info@izmirbarinak.com', 38.4237, 27.1428, 'Tam teşekküllü veteriner hastanesi ile', 200, 95, '08:30-17:30', '["kedi", "köpek", "tedavi", "ameliyat", "acil"]'),
('Bursa Doğa ve Hayvan Koruma', 'Nilüfer, Bursa', '+90 224 555 0404', 'info@bursabarinak.com', 40.1826, 29.0610, 'Gönüllü destekli barınak', 150, 78, '08:00-16:00', '["kedi", "köpek", "rehabilitasyon"]');

-- Örnek Etkinlik Verileri
INSERT INTO hayvan_etkinlikleri (baslik, aciklama, detay_aciklama, etkinlik_tarihi, bitis_tarihi, adres, konum_lat, konum_lng, organizator, iletisim_telefon, etkinlik_turu, resim) VALUES
('Büyük Sahiplendirme Fuarı 2025', 'İstanbul\'da gerçekleşecek büyük sahiplendirme etkinliği', 'Onlarca barınak ve gönüllü organizasyonun katıldığı bu etkinlikte yüzlerce hayvan sahiplendirilmeyi bekliyor. Ücretsiz aşı ve veteriner kontrolleri de yapılacak.', '2025-08-15 10:00:00', '2025-08-15 18:00:00', 'Forum İstanbul AVM, Bayrampaşa', 41.0420, 28.9815, 'İstanbul Büyükşehir Belediyesi', '+90 212 555 0001', 'sahiplendirme', 'sahiplendirme_fuari.jpg'),
('Hayvan Bakımı Eğitim Semineri', 'Yeni sahiplenenler için kapsamlı eğitim', 'Veteriner hekimler eşliğinde hayvan bakımı, beslenme, sağlık konularında detaylı bilgiler verilecek.', '2025-07-30 14:00:00', '2025-07-30 17:00:00', 'Ankara Veteriner Fakültesi', 39.9208, 32.8541, 'Ankara Üniversitesi Vet. Fak.', '+90 312 555 0002', 'egitim', 'egitim_semineri.jpg'),
('Sokak Hayvanları İçin Bağış Kampanyası', 'Kış hazırlıkları için yardım toplama', 'Soğuk kış ayları yaklaşırken sokak hayvanları için mama, battaniye, kulübe bağış kampanyası başlatıldı.', '2025-08-01 09:00:00', '2025-08-31 18:00:00', 'Tüm İzmir', 38.4237, 27.1428, 'İzmir Hayvan Sevenler Derneği', '+90 232 555 0003', 'bagis', 'bagis_kampanyasi.jpg'),
('Ücretsiz Kısırlaştırma Günü', 'Sokak hayvanları için ücretsiz kısırlaştırma', 'Kayıtlı veteriner hekimler eşliğinde sokak kedileri ve köpekleri için ücretsiz kısırlaştırma hizmeti.', '2025-08-20 08:00:00', '2025-08-20 16:00:00', 'Bursa Veteriner Kliniği', 40.1826, 29.0610, 'Bursa Veteriner Hekimler Odası', '+90 224 555 0004', 'tedavi', 'kisirlaştirma.jpg');
