<?php

session_start();
// Kullanıcı oturum açmamışsa giriş sayfasına yönlendir
if (!isset($_SESSION['kullanici_id'])) {
    header("Location: giris.php");
    exit;
}

// Veritabanı bağlantısı ve başlık (header) dosyalarını dahil et
include("includes/db.php");

// Hata raporlamayı aç (geçici olarak, hata ayıklama için)
error_reporting(E_ALL); // Üretim ortamında kapatılmalı
ini_set('display_errors', 1); // Üretim ortamında kapatılmalı

$mesaj = ""; // Kullanıcıya gösterilecek mesaj değişkeni
$mesaj_tur = ""; // 'success' veya 'danger' veya 'warning'

// Kategorileri (Hayvan Türleri) veritabanından çek
$kategoriler = [];
$kategorisor = mysqli_query($conn, "SELECT * FROM kategoriler ORDER BY ad ASC");
while($kat = mysqli_fetch_assoc($kategorisor)) $kategoriler[] = $kat;

// Cinsleri veritabanından çek ve kategoriye göre grupla
$cinsler = [];
$cinssor = mysqli_query($conn, "SELECT id, kategori_id, ad FROM cinsler ORDER BY kategori_id, ad ASC");
while($cins = mysqli_fetch_assoc($cinssor)) {
    $cinsler[$cins['kategori_id']][] = [
        'id' => $cins['id'],
        'ad' => $cins['ad']
    ];
}

// İlleri veritabanından çek
$iller = [];
$ilsor = mysqli_query($conn, "SELECT * FROM il ORDER BY ad ASC");
while($il = mysqli_fetch_assoc($ilsor)) $iller[] = $il;

// İlçeleri veritabanından çek ve ile göre grupla
$ilceler = [];
$ilcesor = mysqli_query($conn, "SELECT id, il_id, ad FROM ilce ORDER BY il_id, ad ASC");
while($ilce = mysqli_fetch_assoc($ilcesor)) {
    $ilceler[$ilce['il_id']][] = [
        'id' => $ilce['id'],
        'ad' => $ilce['ad']
    ];
}

// Cinslere göre hastalık eşleştirmelerini veritabanından çek
$hastaliklar_cins = [];
$hc_sor = mysqli_query($conn, "SELECT hc.cins_id, h.id, h.ad FROM hastaliklar_cinsler hc
                                JOIN hastaliklar h ON hc.hastalik_id = h.id ORDER BY hc.cins_id, h.ad ASC");
while($row = mysqli_fetch_assoc($hc_sor)) {
    $hastaliklar_cins[$row['cins_id']][] = [
        'id' => $row['id'],
        'ad' => $row['ad']
    ];
}

// Form gönderildiyse (POST isteği ve 'ekle' butonu tıklandıysa)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ekle'])) {
    // Form verilerini güvenli hale getir
    $baslik = mysqli_real_escape_string($conn, trim($_POST['baslik']));
    $aciklama = mysqli_real_escape_string($conn, trim($_POST['aciklama']));
    $kategori_id = intval($_POST['kategori_id']);
    $cins_id = intval($_POST['cins_id']);
    $hastalik_id = intval($_POST['hastalik_id'] ?? 0); // Hastalık ID'si alınır, yoksa 0 (Hastalık Yok)
    $il_id = intval($_POST['il_id']);
    $ilce_id = intval($_POST['ilce_id']);
    $iletisim = mysqli_real_escape_string($conn, trim($_POST['iletisim']));
    $kullanici_id = $_SESSION['kullanici_id'];

    // Yeni eklenen alanlar
    $yas = intval($_POST['yas']);
    $cinsiyet = mysqli_real_escape_string($conn, $_POST['cinsiyet']);
    $asi_durumu = mysqli_real_escape_string($conn, trim($_POST['asi_durumu']));
    $kisirlastirma = isset($_POST['kisirlastirma']) ? 1 : 0; // Checkbox işaretliyse 1, değilse 0
    $adres = mysqli_real_escape_string($conn, trim($_POST['adres'])); // Adres alanı

    // "Hastalık Yok" seçeneği (value="0") gelirse, veritabanına NULL olarak kaydet
    $hastalik_id_for_db = ($hastalik_id === 0) ? "NULL" : $hastalik_id;

    $foto_adi = "";
    $hedef_klasor = "uploads/"; // Fotoğrafların kaydedileceği klasör (önceki uyarımıza göre güncellendi)

    // Eğer bir dosya yüklendi ise
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($_FILES['foto']['type'], $allowed_types)) {
            $uzanti = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $foto_adi = uniqid() . "." . $uzanti; // Benzersiz dosya adı oluştur
            $foto_tmp = $_FILES['foto']['tmp_name'];
            $foto_yolu = $hedef_klasor . $foto_adi;

            if (!is_dir($hedef_klasor)) { // Hedef klasör yoksa oluştur
                mkdir($hedef_klasor, 0777, true);
            }

            if (move_uploaded_file($foto_tmp, $foto_yolu)) {
                // Başarılı yükleme
            } else {
                $mesaj = "Fotoğraf yüklenirken hata oluştu.";
                $mesaj_tur = "warning";
            }
        } else {
            $mesaj = "Yalnızca JPG, PNG ve GIF formatlarında fotoğraf yükleyebilirsiniz.";
            $mesaj_tur = "warning";
        }
    } else {
        $mesaj = "Fotoğraf seçilmedi. Lütfen bir fotoğraf yükleyiniz.";
        $mesaj_tur = "warning";
    }

    // Sadece fotoğraf başarıyla yüklendiyse ilanı kaydet
    if (!empty($foto_adi)) {
        // Handle hastalik_id properly - if 0, we need to insert NULL
        if ($hastalik_id === 0) {
            // Insert NULL for hastalik_id when no disease is selected
            $sorgu = "INSERT INTO ilanlar
                        (baslik, aciklama, foto, kullanici_id, kategori_id, cins_id, hastalik_id, il_id, ilce_id, iletisim, yas, cinsiyet, asi_durumu, kisirlastirma, adres)
                        VALUES
                        (?, ?, ?, ?, ?, ?, NULL, ?, ?, ?, ?, ?, ?, ?, ?)"; // NULL for hastalik_id

            $stmt = $conn->prepare($sorgu);
            if ($stmt) {
                $stmt->bind_param("sssiisiisissii",
                    $baslik, $aciklama, $foto_adi, $kullanici_id, $kategori_id, $cins_id,
                    $il_id, $ilce_id, $iletisim, $yas, $cinsiyet, $asi_durumu, $kisirlastirma, $adres);
            }
        } else {
            // Insert actual hastalik_id when a disease is selected
            $sorgu = "INSERT INTO ilanlar
                        (baslik, aciklama, foto, kullanici_id, kategori_id, cins_id, hastalik_id, il_id, ilce_id, iletisim, yas, cinsiyet, asi_durumu, kisirlastirma, adres)
                        VALUES
                        (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"; // Placeholder for hastalik_id

            $stmt = $conn->prepare($sorgu);
            if ($stmt) {
                $stmt->bind_param("sssiisiiisissis",
                    $baslik, $aciklama, $foto_adi, $kullanici_id, $kategori_id, $cins_id, $hastalik_id,
                    $il_id, $ilce_id, $iletisim, $yas, $cinsiyet, $asi_durumu, $kisirlastirma, $adres);
            }
        }

        if ($stmt) {
            if ($stmt->execute()) {
                $mesaj = "İlan başarıyla eklendi.";
                $mesaj_tur = "success";
                // Formu temizle
                $_POST = array(); // Tüm POST verilerini temizleyerek formu sıfırla
            } else {
                $mesaj = "Veritabanı hatası: " . $stmt->error;
                $mesaj_tur = "danger";
            }
            $stmt->close();
        } else {
            $mesaj = "Sorgu hazırlanamadı: " . $conn->error;
            $mesaj_tur = "danger";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yeni İlan Oluştur</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <link href="./dist/output.css" rel="stylesheet">
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-50 font-sans min-h-screen flex flex-col">

<?php include("includes/header.php"); ?>

<div class="container mx-auto p-4 flex-grow pt-20">
    <div class="max-w-4xl mx-auto bg-white p-8 rounded-lg shadow-xl mt-8">
    <h1 class="text-3xl font-bold text-center mb-8">Yeni İlan Oluştur</h1>

    <?php if (!empty($mesaj)): ?>
        <div class="p-4 mb-4 rounded-md text-center <?= $mesaj_tur == 'success' ? 'bg-green-100 text-green-700' : ($mesaj_tur == 'danger' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') ?>">
            <?= htmlspecialchars($mesaj) ?>
        </div>
    <?php endif; ?>

    <form action="ilan_ekle.php" method="POST" enctype="multipart/form-data" class="space-y-6">
        <div>
            <label for="baslik" class="block text-sm font-medium text-gray-700 mb-2">Başlık</label>
            <input type="text" name="baslik" id="baslik" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required placeholder="Hayvanınıza bir isim verin veya ilan başlığı girin" value="<?= htmlspecialchars($_POST['baslik'] ?? '') ?>">
        </div>
        <div>
            <label for="aciklama" class="block text-sm font-medium text-gray-700 mb-2">Açıklama</label>
            <textarea name="aciklama" id="aciklama" rows="6" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required placeholder="Hayvan hakkında detaylı bilgi, özellikleri, alışkanlıkları, sağlık durumu vb."><?= htmlspecialchars($_POST['aciklama'] ?? '') ?></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="kategori" class="block text-sm font-medium text-gray-700 mb-2">Hayvan Türü (Kategori)</label>
                <select name="kategori_id" id="kategori" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <option value="">Seçiniz</option>
                    <?php foreach($kategoriler as $kat): ?>
                        <option value="<?= $kat['id'] ?>" <?= (isset($_POST['kategori_id']) && $_POST['kategori_id'] == $kat['id']) ? 'selected' : '' ?>><?= htmlspecialchars($kat['ad']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="cins" class="block text-sm font-medium text-gray-700 mb-2">Cins</label>
                <select name="cins_id" id="cins" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <option value="">Önce kategori seçiniz</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="yas" class="block text-sm font-medium text-gray-700 mb-2">Yaş</label>
                <input type="number" name="yas" id="yas" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Hayvanın yaşı (ör: 2)" min="0" value="<?= htmlspecialchars($_POST['yas'] ?? '') ?>">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Cinsiyet <span class="text-red-500">*</span></label>
                <div class="space-y-2">
                    <label class="flex items-center">
                        <input type="radio" name="cinsiyet" value="e" <?= (isset($_POST['cinsiyet']) && $_POST['cinsiyet'] == 'e') ? 'checked' : '' ?> required class="mr-2"> Erkek
                    </label>
                    <label class="flex items-center">
                        <input type="radio" name="cinsiyet" value="d" <?= (isset($_POST['cinsiyet']) && $_POST['cinsiyet'] == 'd') ? 'checked' : '' ?> class="mr-2"> Dişi
                    </label>
                </div>
                <div id="cinsiyet-error" class="text-red-500 text-sm mt-1 hidden">Lütfen cinsiyet seçiniz.</div>
            </div>
        </div>

        <div>
            <label for="asi_durumu" class="block text-sm font-medium text-gray-700 mb-2">Aşı Durumu</label>
            <input type="text" name="asi_durumu" id="asi_durumu" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Yapılan aşılar veya 'Tam', 'Eksik', 'Yok'" value="<?= htmlspecialchars($_POST['asi_durumu'] ?? '') ?>">
        </div>

        <div>
            <label class="flex items-center">
                <input type="checkbox" name="kisirlastirma" value="1" <?= (isset($_POST['kisirlastirma']) && $_POST['kisirlastirma'] == '1') ? 'checked' : '' ?> class="mr-2"> Kısırlaştırılmış
            </label>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="il" class="block text-sm font-medium text-gray-700 mb-2">İl</label>
                <select name="il_id" id="il" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <option value="">Seçiniz</option>
                    <?php foreach($iller as $il): ?>
                        <option value="<?= $il['id'] ?>" <?= (isset($_POST['il_id']) && $_POST['il_id'] == $il['id']) ? 'selected' : '' ?>><?= htmlspecialchars($il['ad']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="ilce" class="block text-sm font-medium text-gray-700 mb-2">İlçe</label>
                <select name="ilce_id" id="ilce" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <option value="">Önce il seçiniz</option>
                </select>
            </div>
        </div>
        <div>
            <label for="adres" class="block text-sm font-medium text-gray-700 mb-2">Adres (Mahalle, Cadde vb. detaylar)</label>
            <textarea name="adres" id="adres" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Hayvanın bulunduğu konumun daha detaylı adresi"><?= htmlspecialchars($_POST['adres'] ?? '') ?></textarea>
        </div>

        <div>
            <label for="hastalik_id" class="block text-sm font-medium text-gray-700 mb-2">Hastalığı (Varsa)</label>
            <select name="hastalik_id" id="hastalik_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="0">Hastalık Yok</option>
                <option value="">Önce cins seçiniz</option>
            </select>
        </div>

        <div>
            <label for="iletisim" class="block text-sm font-medium text-gray-700 mb-2">İletişim Bilgisi (Telefon, E-posta vb.)</label>
            <input type="text" name="iletisim" id="iletisim" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required placeholder="İletişim numaranız veya e-posta adresiniz" value="<?= htmlspecialchars($_POST['iletisim'] ?? '') ?>">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Fotoğraf</label>
            <input type="file" name="foto" id="foto" accept="image/*" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            <input type="hidden" name="camera_image_data" id="cameraImageData">
        </div>

        <button type="submit" name="ekle" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-6 rounded-md transition duration-300 w-full">
            <i class="fas fa-plus-circle mr-2"></i> İlanı Oluştur
        </button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // PHP'den gelen JSON verilerini JavaScript değişkenlerine ata
    const cinsler = <?= json_encode($cinsler) ?>;
    const ilceler = <?= json_encode($ilceler) ?>;
    const hastaliklarCins = <?= json_encode($hastaliklar_cins) ?>;

    // HTML elementlerini seç
    const kategoriSelect = document.getElementById('kategori');
    const cinsSelect = document.getElementById('cins');
    const hastalikSelect = document.getElementById('hastalik_id');
    const ilSelect = document.getElementById('il');
    const ilceSelect = document.getElementById('ilce');

    // Kamera ve Fotoğraf Elemanları
    const fileInput = document.getElementById('foto');
    const cameraImageDataInput = document.getElementById('cameraImageData');

    let stream = null; // Kamera akışını tutacak değişken

    // *** Dropdown İşlemleri ***
    function populateCinses(kategoriId, selectedCinsId = null) {
        cinsSelect.innerHTML = '<option value="">Seçiniz</option>';
        if (cinsler[kategoriId]) {
            cinsler[kategoriId].forEach(function(cins) {
                const option = document.createElement('option');
                option.value = cins.id;
                option.textContent = cins.ad;
                if (selectedCinsId && selectedCinsId == cins.id) {
                    option.selected = true;
                }
                cinsSelect.appendChild(option);
            });
        }
        // Kategori değiştiğinde hastalıkları da sıfırla
        populateHastaliklar(selectedCinsId || '', null);
    }

    function populateIlces(ilId, selectedIlceId = null) {
        ilceSelect.innerHTML = '<option value="">Seçiniz</option>';
        if (ilceler[ilId]) {
            ilceler[ilId].forEach(function(ilce) {
                const option = document.createElement('option');
                option.value = ilce.id;
                option.textContent = ilce.ad;
                if (selectedIlceId && selectedIlceId == ilce.id) {
                    option.selected = true;
                }
                ilceSelect.appendChild(option);
            });
        }
    }

    function populateHastaliklar(cinsId, selectedHastalikId = null) {
        hastalikSelect.innerHTML = '<option value="0">Hastalık Yok</option>'; // Her zaman 'Hastalık Yok' ile başla

        if (hastaliklarCins[cinsId]) {
            hastaliklarCins[cinsId].forEach(function(hastalik) {
                const option = document.createElement('option');
                option.value = hastalik.id;
                option.textContent = hastalik.ad;
                if (selectedHastalikId && selectedHastalikId == hastalik.id) {
                    option.selected = true;
                }
                hastalikSelect.appendChild(option);
            });
        } else if (cinsId === "") {
            // Eğer cins seçimi boşsa veya yoksa
            hastalikSelect.innerHTML = '<option value="0">Hastalık Yok</option><option value="">Önce cins seçiniz</option>';
        }
    }

    // Olay Dinleyicileri
    if (kategoriSelect) {
        kategoriSelect.addEventListener('change', function() {
            populateCinses(this.value);
        });
    }

    if (cinsSelect) {
        cinsSelect.addEventListener('change', function() {
            populateHastaliklar(this.value);
        });
    }

    if (ilSelect) {
        ilSelect.addEventListener('change', function() {
            populateIlces(this.value);
        });
    }

    // Sayfa yüklendiğinde mevcut değerleri doldur (eğer formda hata olduysa inputlar kalmış olabilir)
    const initialKategoriId = kategoriSelect ? kategoriSelect.value : '';
    if (initialKategoriId) {
        populateCinses(initialKategoriId, '<?= htmlspecialchars($_POST['cins_id'] ?? '') ?>');
    } else {
        cinsSelect.innerHTML = '<option value="">Önce kategori seçiniz</option>';
    }

    const initialCinsIdForHastalik = cinsSelect ? cinsSelect.value : '';
    if (initialCinsIdForHastalik) {
        populateHastaliklar(initialCinsIdForHastalik, '<?= htmlspecialchars($_POST['hastalik_id'] ?? '0') ?>');
    } else {
        hastalikSelect.innerHTML = '<option value="0">Hastalık Yok</option><option value="">Önce cins seçiniz</option>';
    }

    const initialIlId = ilSelect ? ilSelect.value : '';
    if (initialIlId) {
        populateIlces(initialIlId, '<?= htmlspecialchars($_POST['ilce_id'] ?? '') ?>');
    } else {
        ilceSelect.innerHTML = '<option value="">Önce il seçiniz</option>';
    }

    // *** Kamera İşlemleri ***
    // Dosya inputu değiştiğinde önizleme yap
    if (fileInput) {
        fileInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                // Dosya seçildi, kamera verisini temizle
                cameraImageDataInput.value = '';
            }
        });
    }

    // *** Form Validation ***
    // Gender validation
    const form = document.querySelector('form');
    const cinsiyetInputs = document.querySelectorAll('input[name="cinsiyet"]');
    const cinsiyetError = document.getElementById('cinsiyet-error');

    if (form) {
        form.addEventListener('submit', function(e) {
            let cinsiyetSelected = false;
            cinsiyetInputs.forEach(function(input) {
                if (input.checked) {
                    cinsiyetSelected = true;
                }
            });

            if (!cinsiyetSelected) {
                e.preventDefault();
                cinsiyetError.classList.remove('hidden');
                cinsiyetError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return false;
            } else {
                cinsiyetError.classList.add('hidden');
            }
        });
    }

    // Hide error when gender is selected
    cinsiyetInputs.forEach(function(input) {
        input.addEventListener('change', function() {
            cinsiyetError.classList.add('hidden');
        });
    });
});
</script>

    </div>
</div>

<?php include("includes/footer.php"); ?>

</body>
</html>