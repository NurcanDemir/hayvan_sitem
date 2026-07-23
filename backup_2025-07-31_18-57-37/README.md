# 🐾 Hayvan Sahiplendirme Sitesi

Türkiye'deki sokak hayvanlarının sahiplendirilmesi ve kayıp hayvanların bulunması için geliştirilmiş modern bir web platformu.

## 📋 İçindekiler

- [Özellikler](#-özellikler)
- [Teknolojiler](#-teknolojiler)
- [Kurulum](#-kurulum)
- [Veritabanı Yapısı](#-veritabanı-yapısı)
- [Kullanım](#-kullanım)
- [Proje Yapısı](#-proje-yapısı)
- [Ekran Görüntüleri](#-ekran-görüntüleri)
- [Katkıda Bulunma](#-katkıda-bulunma)
- [Lisans](#-lisans)

## ✨ Özellikler

### 👥 Kullanıcı Özellikleri
- **Üye Sistemi**: Güvenli kayıt ve giriş sistemi
- **İlan Oluşturma**: Detaylı hayvan sahiplendirme ilanları ekleme
- **Gelişmiş Filtreleme**: Hayvan türü, cins, şehir/ilçe bazında arama
- **Favoriler**: Beğenilen ilanları favorilere ekleme
- **Sahiplenme Talepleri**: Hayvanlar için sahiplenme talebi oluşturma
- **Profil Yönetimi**: Kendi ilanlarını görüntüleme ve düzenleme

### 🎨 Tasarım Özellikleri
- **Responsive Design**: Mobil ve masaüstü uyumlu tasarım
- **Modern UI**: Tailwind CSS ile şık ve kullanıcı dostu arayüz
- **Karanlık/Açık Tema**: Kullanıcı tercihine göre tema seçimi
- **Sticky Footer**: Her durumda sayfanın altında konumlanan footer

### 📊 Yönetici Paneli
- **İlan Yönetimi**: Tüm ilanları görüntüleme, düzenleme ve silme
- **Kullanıcı Yönetimi**: Üye bilgilerini görüntüleme
- **Talep Yönetimi**: Sahiplenme taleplerini takip etme
- **Raporlama**: İstatistiksel veriler ve raporlar

## 🛠 Teknolojiler

### Backend
- **PHP 8+**: Ana programlama dili
- **MySQL**: Veritabanı yönetim sistemi
- **Apache**: Web sunucusu

### Frontend
- **HTML5**: Semantik yapı
- **CSS3**: Stil ve animasyonlar
- **JavaScript (ES6+)**: İnteraktif özellikler
- **Tailwind CSS**: Utility-first CSS framework
- **Font Awesome**: İkon kütüphanesi
- **SweetAlert2**: Modern alert/popup sistemi

### Geliştirme Araçları
- **XAMPP**: Yerel geliştirme ortamı
- **npm**: Paket yöneticisi
- **PostCSS**: CSS işleme
- **Autoprefixer**: CSS vendor prefix ekleme

## 🚀 Kurulum

### Ön Gereksinimler
- XAMPP (Apache + MySQL + PHP 8+)
- Node.js ve npm
- Modern web tarayıcısı

### 1. Projeyi Klonlama
```bash
git clone https://github.com/NurcanDemir/hayvan_sitem.git
cd hayvan_sitem
```

### 2. XAMPP Kurulumu
1. XAMPP'ı indirin ve kurun
2. Apache ve MySQL servislerini başlatın
3. Projeyi `C:\xampp\htdocs\hayvan_sitem` klasörüne yerleştirin

### 3. Veritabanı Kurulumu
1. phpMyAdmin'e gidin (http://localhost/phpmyadmin)
2. `hayvan_sitem` adında yeni veritabanı oluşturun
3. Aşağıdaki tabloları oluşturun:

```sql
-- Kategoriler tablosu (Hayvan türleri)
CREATE TABLE kategoriler (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ad VARCHAR(50) NOT NULL
);

-- Cinsleri tablosu
CREATE TABLE cinsler (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kategori_id INT,
    ad VARCHAR(100) NOT NULL,
    FOREIGN KEY (kategori_id) REFERENCES kategoriler(id)
);

-- Kullanıcılar tablosu
CREATE TABLE kullanicilar (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kullanici_adi VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    sifre VARCHAR(255) NOT NULL,
    olusturma_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- İlanlar tablosu
CREATE TABLE ilanlar (
    id INT PRIMARY KEY AUTO_INCREMENT,
    baslik VARCHAR(200) NOT NULL,
    aciklama TEXT,
    foto VARCHAR(255),
    kullanici_id INT,
    kategori_id INT,
    cins_id INT,
    hastalik_id INT,
    il_id INT,
    ilce_id INT,
    iletisim VARCHAR(100),
    yas INT,
    cinsiyet ENUM('erkek', 'dişi'),
    asi_durumu VARCHAR(100),
    kisirlastirma BOOLEAN DEFAULT FALSE,
    adres TEXT,
    olusturma_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kullanici_id) REFERENCES kullanicilar(id),
    FOREIGN KEY (kategori_id) REFERENCES kategoriler(id),
    FOREIGN KEY (cins_id) REFERENCES cinsler(id)
);

-- Favoriler tablosu
CREATE TABLE favoriler (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kullanici_id INT,
    ilan_id INT,
    olusturma_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kullanici_id) REFERENCES kullanicilar(id),
    FOREIGN KEY (ilan_id) REFERENCES ilanlar(id),
    UNIQUE KEY unique_favorite (kullanici_id, ilan_id)
);

-- Sahiplenme talepleri tablosu
CREATE TABLE sahiplenme_talepleri (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ilan_id INT,
    talep_eden_id INT,
    mesaj TEXT,
    durum ENUM('beklemede', 'onaylandi', 'reddedildi') DEFAULT 'beklemede',
    olusturma_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ilan_id) REFERENCES ilanlar(id),
    FOREIGN KEY (talep_eden_id) REFERENCES kullanicilar(id)
);
```

### 4. CSS Build İşlemi
```bash
# Bağımlılıkları yükleyin
npm install

# CSS dosyalarını build edin
npm run dev
```

### 5. Dosya İzinleri
Uploads klasörüne yazma izni verin:
```bash
chmod 755 uploads/
```

### 6. Yapılandırma
`includes/db.php` dosyasındaki veritabanı ayarlarını kontrol edin:
```php
$host = "localhost";
$kullanici = "root";
$sifre = "";
$veritabani = "hayvan_sitem";
```

## 📁 Proje Yapısı

```
hayvan_sitem/
├── admin/                  # Yönetici paneli
│   ├── admin_giris.php
│   ├── admin_panel.php
│   ├── ilan_yonetim.php
│   └── includes/
├── css/                    # Custom CSS dosyaları
├── dist/                   # Build edilmiş CSS dosyaları
├── images/                 # Statik resimler
├── includes/               # Ortak PHP dosyaları
│   ├── db.php             # Veritabanı bağlantısı
│   ├── header.php         # Ortak header
│   ├── footer.php         # Ortak footer
│   └── auth.php           # Kimlik doğrulama
├── js/                     # JavaScript dosyaları
├── src/                    # Kaynak CSS dosyaları
├── uploads/                # Kullanıcı yüklediği dosyalar
├── index.php              # Ana sayfa
├── giris.php              # Giriş sayfası
├── kayit.php              # Kayıt sayfası
├── ilan_ekle.php          # İlan ekleme
├── ilan_detay.php         # İlan detayı
├── ilanlar.php            # İlan listesi
├── favorilerim.php        # Favori ilanlar
└── package.json           # npm yapılandırması
```

## 🎯 Kullanım

### Kullanıcı İşlemleri

1. **Kayıt Olma**
   - Ana sayfada "Kayıt Ol" butonuna tıklayın
   - Gerekli bilgileri doldurun
   - E-posta doğrulaması yapın

2. **İlan Ekleme**
   - Giriş yaptıktan sonra "İlan Ekle" menüsüne gidin
   - Hayvan bilgilerini detaylı şekilde doldurun
   - Fotoğraf yükleyin
   - İlanınızı yayınlayın

3. **İlan Arama**
   - Ana sayfada filtreleri kullanın
   - Hayvan türü, cins, şehir seçin
   - Arama sonuçlarını inceleyin

4. **Sahiplenme Talebi**
   - Beğendiğiniz ilanın detayına gidin
   - "Sahiplenmek İstiyorum" butonuna tıklayın
   - Mesajınızı yazın ve gönderin

### Yönetici İşlemleri

1. **Admin Girişi**
   - `/admin/admin_giris.php` adresine gidin
   - Admin bilgileriniz ile giriş yapın

2. **İlan Yönetimi**
   - Tüm ilanları görüntüleyin
   - Uygunsuz ilanları silin veya düzenleyin
   - İlan istatistiklerini görüntüleyin

## 🔧 Geliştirme

### CSS Geliştirme
```bash
# Watch mode ile CSS'i canlı geliştirin
npm run dev

# Production için build edin
npm run build
```

### Yeni Özellik Ekleme
1. Feature branch oluşturun
2. Kodunuzu yazın
3. Test edin
4. Pull request oluşturun

### Veritabanı Şeması Güncelleme
- Migration dosyaları oluşturun
- Test ortamında deneyin
- Production'a dikkatli şekilde deploy edin

## 🐛 Bilinen Sorunlar

- [ ] Büyük dosya yükleme sınırlaması
- [ ] Safari'de bazı CSS uyumluluk sorunları
- [ ] IE desteği bulunmuyor

## 🤝 Katkıda Bulunma

1. Bu repo'yu fork edin
2. Feature branch oluşturun (`git checkout -b feature/amazing-feature`)
3. Değişikliklerinizi commit edin (`git commit -m 'Add some amazing feature'`)
4. Branch'inizi push edin (`git push origin feature/amazing-feature`)
5. Pull Request oluşturun

### Kod Standartları
- PSR-4 autoloading standardı
- Camel case değişken isimleri
- Türkçe yorum satırları
- Responsive design prensipleri

## 📝 Lisans

Bu proje MIT lisansı altında lisanslanmıştır. Detaylar için [LICENSE](LICENSE) dosyasına bakın.

## 👨‍💻 Geliştirici

**Nurcan Demir**
- GitHub: [@NurcanDemir](https://github.com/NurcanDemir)
- Email: [email@example.com](mailto:email@example.com)

## 🙏 Teşekkürler

- Tailwind CSS ekibine harika framework için
- Font Awesome ekibine ikon kütüphanesi için
- Açık kaynak topluluğuna katkıları için

---

**Not**: Bu proje eğitim amaçlı geliştirilmiştir ve sürekli olarak geliştirilmektedir. Herhangi bir sorun yaşarsanız lütfen issue oluşturun.

## 📞 İletişim

Proje hakkında sorularınız varsa:
- GitHub Issues kullanın
- E-posta ile iletişime geçin
- Geliştirici toplulukları aracılığıyla destek alın

Bu README dosyası projenin gelişimi ile birlikte güncellenecektir.

