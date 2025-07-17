-- Bilgilendirmeler tablosunu oluştur (admin_talep_bilgilendir.php için)
CREATE TABLE IF NOT EXISTS `bilgilendirmeler` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `talep_id` int(11) NOT NULL,
    `admin_id` int(11) NOT NULL,
    `bilgi_turu` enum('bilgi','onay','red','tamamlandi','ek_belge','randevu','uyari') NOT NULL DEFAULT 'bilgi',
    `mesaj` text NOT NULL,
    `tarih` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `okundu` tinyint(1) DEFAULT 0,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`talep_id`) REFERENCES `sahiplenme_istekleri`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`admin_id`) REFERENCES `admin`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- Admin tablosuna eksik alanlar ekle (eğer yoksa)
ALTER TABLE `admin` 
ADD COLUMN IF NOT EXISTS `ad` varchar(50) NOT NULL DEFAULT 'Admin',
ADD COLUMN IF NOT EXISTS `soyad` varchar(50) NOT NULL DEFAULT 'User';

-- Eğer admin tablosunda mevcut kayıtlar varsa, varsayılan ad/soyad değerlerini güncelle
UPDATE `admin` SET `ad` = 'Admin', `soyad` = 'User' WHERE `ad` = '' OR `ad` IS NULL;
