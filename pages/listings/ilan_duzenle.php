<?php
session_start();

// Kullanıcı oturum açmamışsa giriş sayfasına yönlendir
if (!isset($_SESSION['kullanici_id'])) {
    header("Location: giris.php");
    exit;
}

include("includes/db.php");

// Hata raporlamayı aç
error_reporting(E_ALL);
ini_set('display_errors', 1);

$mesaj = "";
$mesaj_tur = "";
$ilan = null;

// İlan ID'sini al
$ilan_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$kullanici_id = $_SESSION['kullanici_id'];

if ($ilan_id > 0) {
    // İlanın varlığını ve kullanıcının sahibi olduğunu kontrol et
    $stmt = $conn->prepare("SELECT * FROM ilanlar WHERE id = ? AND kullanici_id = ?");
    $stmt->bind_param("ii", $ilan_id, $kullanici_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $ilan = $result->fetch_assoc();
    } else {
        $_SESSION['mesaj'] = "İlan bulunamadı veya bu ilanı düzenleme yetkiniz yok.";
        $_SESSION['mesaj_tur'] = "danger";
        header("Location: ilanlarim.php");
        exit;
    }
    $stmt->close();
} else {
    $_SESSION['mesaj'] = "Geçersiz ilan ID'si.";
    $_SESSION['mesaj_tur'] = "danger";
    header("Location: ilanlarim.php");
    exit;
}

// Kategorileri çek
$kategoriler = [];
$kategorisor = mysqli_query($conn, "SELECT * FROM kategoriler ORDER BY ad ASC");
while($kat = mysqli_fetch_assoc($kategorisor)) $kategoriler[] = $kat;

// Cinsleri çek
$cinsler = [];
$cinssor = mysqli_query($conn, "SELECT id, kategori_id, ad FROM cinsler ORDER BY kategori_id, ad ASC");
while($cins = mysqli_fetch_assoc($cinssor)) {
    $cinsler[$cins['kategori_id']][] = [
        'id' => $cins['id'],
        'ad' => $cins['ad']
    ];
}

// İlleri çek
$iller = [];
$ilsor = mysqli_query($conn, "SELECT * FROM il ORDER BY ad ASC");
while($il = mysqli_fetch_assoc($ilsor)) $iller[] = $il;

// İlçeleri çek
$ilceler = [];
$ilcesor = mysqli_query($conn, "SELECT id, il_id, ad FROM ilce ORDER BY il_id, ad ASC");
while($ilce = mysqli_fetch_assoc($ilcesor)) {
    $ilceler[$ilce['il_id']][] = [
        'id' => $ilce['id'],
        'ad' => $ilce['ad']
    ];
}

// Hastalıkları çek
$hastaliklar_cins = [];
$hc_sor = mysqli_query($conn, "SELECT hc.cins_id, h.id, h.ad FROM hastaliklar_cinsler hc
                                JOIN hastaliklar h ON hc.hastalik_id = h.id ORDER BY hc.cins_id, h.ad ASC");
while($row = mysqli_fetch_assoc($hc_sor)) {
    $hastaliklar_cins[$row['cins_id']][] = [
        'id' => $row['id'],
        'ad' => $row['ad']
    ];
}

// Form gönderildiyse
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guncelle'])) {
    // Form verilerini güvenli hale getir
    $baslik = mysqli_real_escape_string($conn, trim($_POST['baslik']));
    $aciklama = mysqli_real_escape_string($conn, trim($_POST['aciklama']));
    $kategori_id = intval($_POST['kategori_id']);
    $cins_id = intval($_POST['cins_id']);
    $hastalik_id = intval($_POST['hastalik_id'] ?? 0);
    $il_id = intval($_POST['il_id']);
    $ilce_id = intval($_POST['ilce_id']);
    $iletisim = mysqli_real_escape_string($conn, trim($_POST['iletisim']));
    $yas = intval($_POST['yas']);
    $cinsiyet = mysqli_real_escape_string($conn, $_POST['cinsiyet']);
    $asi_durumu = mysqli_real_escape_string($conn, trim($_POST['asi_durumu']));
    $kisirlastirma = isset($_POST['kisirlastirma']) ? 1 : 0;
    $adres = mysqli_real_escape_string($conn, trim($_POST['adres']));

    // Hastalık kontrolü
    $hastalik_id_for_db = ($hastalik_id === 0) ? null : $hastalik_id;

    // Fotoğraf güncelleme (eğer yeni fotoğraf yüklendiyse)
    $foto_adi = $ilan['foto']; // Mevcut fotoğrafı koru
    
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($_FILES['foto']['type'], $allowed_types)) {
            $foto_adi = uniqid() . "." . pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $foto_yolu = "uploads/" . $foto_adi;
            
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $foto_yolu)) {
                // Eski fotoğrafı sil
                if (!empty($ilan['foto']) && file_exists("uploads/" . $ilan['foto'])) {
                    unlink("uploads/" . $ilan['foto']);
                }
                $foto_adi = basename($foto_yolu); // Sadece dosya adını sakla
            } else {
                $mesaj = "Fotoğraf yüklenirken hata oluştu.";
                $mesaj_tur = "warning";
            }
        } else {
            $mesaj = "Yalnızca JPG, PNG ve GIF formatlarında fotoğraf yükleyebilirsiniz.";
            $mesaj_tur = "warning";
        }
    }
    
    // Kameradan gelen fotoğraf
    if (isset($_POST['camera_image_data']) && !empty($_POST['camera_image_data'])) {
        $img_data = $_POST['camera_image_data'];
        $img_data = str_replace('data:image/png;base64,', '', $img_data);
        $img_data = str_replace(' ', '+', $img_data);
        $data = base64_decode($img_data);

        $foto_adi = uniqid() . ".png";
        $foto_yolu = "uploads/" . $foto_adi;

        if (!is_dir("uploads/")) {
            mkdir("uploads/", 0777, true);
        }

        if (file_put_contents($foto_yolu, $data)) {
            // Eski fotoğrafı sil
            if (!empty($ilan['foto']) && file_exists("uploads/" . $ilan['foto'])) {
                unlink("uploads/" . $ilan['foto']);
            }
            $foto_adi = basename($foto_yolu); // Sadece dosya adını sakla
        }
    }

    // Veritabanını güncelle
    if (empty($mesaj)) {
        // Handle NULL value for hastalik_id
        $hastalik_id_value = ($hastalik_id === 0) ? NULL : $hastalik_id;
        
        $sorgu = "UPDATE ilanlar SET 
                    baslik = ?, aciklama = ?, foto = ?, kategori_id = ?, cins_id = ?, hastalik_id = ?, 
                    il_id = ?, ilce_id = ?, iletisim = ?, yas = ?, cinsiyet = ?, asi_durumu = ?, 
                    kisirlastirma = ?, adres = ? 
                  WHERE id = ? AND kullanici_id = ?";

        $stmt = $conn->prepare($sorgu);
        if ($stmt) {
            $stmt->bind_param("sssiiiiisiisiiii", 
                $baslik, $aciklama, $foto_adi, $kategori_id, $cins_id, $hastalik_id_value,
                $il_id, $ilce_id, $iletisim, $yas, $cinsiyet, $asi_durumu, 
                $kisirlastirma, $adres, $ilan_id, $kullanici_id);
        }

        if ($stmt) {
            if ($stmt->execute()) {
                $_SESSION['mesaj'] = "İlan başarıyla güncellendi.";
                $_SESSION['mesaj_tur'] = "success";
                header("Location: ilanlarim.php");
                exit;
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
    <title>İlan Düzenle</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <link href="./dist/output.css" rel="stylesheet">
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-50 font-sans min-h-screen flex flex-col">

<?php include("includes/header.php"); ?>

<div class="container mx-auto p-4 flex-grow pt-20">
    <div class="max-w-4xl mx-auto bg-white p-8 rounded-lg shadow-xl mt-8">
        <h1 class="text-3xl font-bold text-center mb-8">İlan Düzenle</h1>

        <?php if (!empty($mesaj)): ?>
            <div class="p-4 mb-4 rounded-md text-center <?= $mesaj_tur == 'success' ? 'bg-green-100 text-green-700' : ($mesaj_tur == 'danger' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') ?>">
                <?= htmlspecialchars($mesaj) ?>
            </div>
        <?php endif; ?>

        <form action="ilan_duzenle.php?id=<?= $ilan_id ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
            <div>
                <label for="baslik" class="block text-sm font-medium text-gray-700 mb-2">İlan Başlığı</label>
                <input type="text" id="baslik" name="baslik" value="<?= htmlspecialchars($ilan['baslik']) ?>" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>

            <div>
                <label for="aciklama" class="block text-sm font-medium text-gray-700 mb-2">Açıklama</label>
                <textarea id="aciklama" name="aciklama" rows="4" 
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required><?= htmlspecialchars($ilan['aciklama']) ?></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="kategori" class="block text-sm font-medium text-gray-700 mb-2">Hayvan Türü</label>
                    <select id="kategori" name="kategori_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="">Seçiniz</option>
                        <?php foreach ($kategoriler as $kategori): ?>
                            <option value="<?= $kategori['id'] ?>" <?= $ilan['kategori_id'] == $kategori['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($kategori['ad']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="cins" class="block text-sm font-medium text-gray-700 mb-2">Cins</label>
                    <select id="cins" name="cins_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="">Önce kategori seçiniz</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="il" class="block text-sm font-medium text-gray-700 mb-2">İl</label>
                    <select id="il" name="il_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="">Seçiniz</option>
                        <?php foreach ($iller as $il): ?>
                            <option value="<?= $il['id'] ?>" <?= $ilan['il_id'] == $il['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($il['ad']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="ilce" class="block text-sm font-medium text-gray-700 mb-2">İlçe</label>
                    <select id="ilce" name="ilce_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="">Önce il seçiniz</option>
                    </select>
                </div>
            </div>

            <div>
                <label for="hastalik_id" class="block text-sm font-medium text-gray-700 mb-2">Hastalık Durumu</label>
                <select id="hastalik_id" name="hastalik_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="0">Hastalık Yok</option>
                </select>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="yas" class="block text-sm font-medium text-gray-700 mb-2">Yaş</label>
                    <input type="number" id="yas" name="yas" value="<?= htmlspecialchars($ilan['yas']) ?>" min="0" max="50" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cinsiyet</label>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="radio" name="cinsiyet" value="erkek" <?= $ilan['cinsiyet'] == 'erkek' ? 'checked' : '' ?> class="mr-2">
                            Erkek
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="cinsiyet" value="dişi" <?= $ilan['cinsiyet'] == 'dişi' ? 'checked' : '' ?> class="mr-2">
                            Dişi
                        </label>
                    </div>
                </div>

                <div>
                    <label for="asi_durumu" class="block text-sm font-medium text-gray-700 mb-2">Aşı Durumu</label>
                    <input type="text" id="asi_durumu" name="asi_durumu" value="<?= htmlspecialchars($ilan['asi_durumu']) ?>" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div>
                <label class="flex items-center">
                    <input type="checkbox" name="kisirlastirma" value="1" <?= $ilan['kisirlastirma'] ? 'checked' : '' ?> class="mr-2">
                    Kısırlaştırılmış
                </label>
            </div>

            <div>
                <label for="adres" class="block text-sm font-medium text-gray-700 mb-2">Adres</label>
                <textarea id="adres" name="adres" rows="3" 
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($ilan['adres']) ?></textarea>
            </div>

            <div>
                <label for="iletisim" class="block text-sm font-medium text-gray-700 mb-2">İletişim</label>
                <input type="text" id="iletisim" name="iletisim" value="<?= htmlspecialchars($ilan['iletisim']) ?>" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Fotoğraf</label>
                <?php if (!empty($ilan['foto'])): ?>
                    <div class="mb-4">
                        <p class="text-sm text-gray-600 mb-2">Mevcut fotoğraf:</p>
                        <img src="uploads/<?= htmlspecialchars($ilan['foto']) ?>" alt="Mevcut fotoğraf" class="w-32 h-32 object-cover rounded">
                    </div>
                <?php endif; ?>
                <input type="file" id="foto" name="foto" accept="image/*" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="text-sm text-gray-500 mt-1">Yeni fotoğraf seçerseniz mevcut fotoğraf değiştirilecektir.</p>
            </div>

            <div class="flex space-x-4">
                <button type="submit" name="guncelle" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-6 rounded-md transition duration-300">
                    <i class="fas fa-save mr-2"></i> Güncelle
                </button>
                <a href="ilanlarim.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded-md transition duration-300">
                    <i class="fas fa-arrow-left mr-2"></i> Geri Dön
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const cinsler = <?= json_encode($cinsler) ?>;
    const ilceler = <?= json_encode($ilceler) ?>;
    const hastaliklarCins = <?= json_encode($hastaliklar_cins) ?>;

    const kategoriSelect = document.getElementById('kategori');
    const cinsSelect = document.getElementById('cins');
    const hastalikSelect = document.getElementById('hastalik_id');
    const ilSelect = document.getElementById('il');
    const ilceSelect = document.getElementById('ilce');

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
        populateHastaliklar(cinsSelect.value, null);
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
        hastalikSelect.innerHTML = '<option value="0">Hastalık Yok</option>';

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
        }
    }

    kategoriSelect.addEventListener('change', function() {
        populateCinses(this.value);
    });

    cinsSelect.addEventListener('change', function() {
        populateHastaliklar(this.value);
    });

    ilSelect.addEventListener('change', function() {
        populateIlces(this.value);
    });

    // Sayfa yüklendiğinde mevcut değerleri doldur
    const initialKategoriId = kategoriSelect.value;
    if (initialKategoriId) {
        populateCinses(initialKategoriId, '<?= htmlspecialchars($ilan['cins_id']) ?>');
    }

    const initialIlId = ilSelect.value;
    if (initialIlId) {
        populateIlces(initialIlId, '<?= htmlspecialchars($ilan['ilce_id']) ?>');
    }

    // Hastalık durumunu ayarla
    setTimeout(() => {
        populateHastaliklar('<?= htmlspecialchars($ilan['cins_id']) ?>', '<?= htmlspecialchars($ilan['hastalik_id'] ?? '0') ?>');
    }, 100);
});
</script>

<?php include("includes/footer.php"); ?>

</body>
</html>
