<?php
// ilan_duzenle.php - İlan Düzenleme Sayfası (Geliştirilmiş Tasarım)

session_start(); // Oturumu başlat
include("../includes/auth.php"); // Yetkilendirme kontrolünü dahil et
include("../includes/db.php"); // Veritabanı bağlantısını dahil et

$mesaj = ""; // İşlem mesajları için değişken

// --- 1. İlan ID'sini Al ve Mevcut Veriyi Çek ---
$ilan_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($ilan_id === 0) {
    $_SESSION['mesaj'] = "<div class='alert alert-danger'>Geçersiz ilan ID'si belirtildi.</div>";
    $_SESSION['mesaj_tipi'] = "danger";
    header("Location: ilan_yonetim.php"); // Yönlendirme
    exit;
}

// İlanın mevcut verilerini veritabanından çek
$stmt_select_ilan = $conn->prepare("
    SELECT
        i.*,
        c.ad AS cins_ad,
        h.ad AS hastalik_ad,
        k.ad AS kategori_ad,
        il.ad AS il_ad,
        ilce.ad AS ilce_ad
    FROM ilanlar i
    LEFT JOIN cinsler c ON i.cins_id = c.id
    LEFT JOIN hastaliklar h ON i.hastalik_id = h.id
    LEFT JOIN kategoriler k ON i.kategori_id = k.id
    LEFT JOIN il il ON i.il_id = il.id
    LEFT JOIN ilce ilce ON i.ilce_id = ilce.id
    WHERE i.id = ?
");
$stmt_select_ilan->bind_param("i", $ilan_id);
$stmt_select_ilan->execute();
$result_ilan = $stmt_select_ilan->get_result();

if ($result_ilan->num_rows === 0) {
    $_SESSION['mesaj'] = "<div class='alert alert-danger'>Düzenlenecek ilan bulunamadı.</div>";
    $_SESSION['mesaj_tipi'] = "danger";
    header("Location: ilan_yonetim.php"); // Yönlendirme
    exit;
}

$ilan = $result_ilan->fetch_assoc();
$stmt_select_ilan->close();

// --- 2. Form Gönderildiğinde Veriyi Güncelle ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Formdan gelen verileri al
    $baslik = $_POST['baslik'] ?? '';
    $aciklama = $_POST['aciklama'] ?? '';
    $durum = $_POST['durum'] ?? '';
    $kategori_id = intval($_POST['kategori_id'] ?? 0);
    $cins_id = intval($_POST['cins_id'] ?? 0);
    $hastalik_id = intval($_POST['hastalik_id'] ?? 0);
    $il_id = intval($_POST['il_id'] ?? 0);
    $ilce_id = intval($_POST['ilce_id'] ?? 0);

    $hedef_dosya = $ilan['foto']; // Mevcut fotoğrafı varsayılan olarak tut

    // Fotoğraf yükleme işlemini kontrol et
    if (isset($_FILES['ilan_foto']) && $_FILES['ilan_foto']['error'] === UPLOAD_ERR_OK) {
        $dosya_adi = basename($_FILES['ilan_foto']['name']);
        $hedef_dizin = "../uploads/"; // Fotoğrafların yükleneceği dizin
        $hedef_dosya_yolu = $hedef_dizin . uniqid() . "_" . $dosya_adi; // Benzersiz dosya adı

        $imageFileType = strtolower(pathinfo($hedef_dosya_yolu, PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($imageFileType, $allowed_types)) {
            $mesaj = "<div class='alert alert-danger'>Sadece JPG, JPEG, PNG ve GIF dosyaları yüklenebilir.</div>";
        } elseif ($_FILES['ilan_foto']['size'] > 5000000) { // 5MB limit
            $mesaj = "<div class='alert alert-danger'>Dosya boyutu çok büyük. Maksimum 5MB.</div>";
        } else {
            // Eski fotoğrafı sil (eğer varsa ve yeni fotoğraf yüklendiyse)
            if (!empty($ilan['foto']) && file_exists($ilan['foto'])) {
                unlink($ilan['foto']);
            }
            if (move_uploaded_file($_FILES['ilan_foto']['tmp_name'], $hedef_dosya_yolu)) {
                $hedef_dosya = $hedef_dosya_yolu;
            } else {
                $mesaj = "<div class='alert alert-danger'>Fotoğraf yüklenirken hata oluştu.</div>";
            }
        }
    }

    if (empty($mesaj)) { // Eğer fotoğraf yükleme hatası yoksa devam et
        // Veritabanı güncelleme işlemi
        $stmt_update_ilan = $conn->prepare("
            UPDATE ilanlar
            SET
                baslik = ?,
                aciklama = ?,
                durum = ?,
                ilan_foto = ?,
                kategori_id = ?,
                cins_id = ?,
                hastalik_id = ?,
                il_id = ?,
                ilce_id = ?
            WHERE id = ?
        ");
        $stmt_update_ilan->bind_param(
            "sssiiiiiii",
            $baslik,
            $aciklama,
            $durum,
            $hedef_dosya,
            $kategori_id,
            $cins_id,
            $hastalik_id,
            $il_id,
            $ilce_id,
            $ilan_id
        );

        if ($stmt_update_ilan->execute()) {
            $_SESSION['mesaj'] = "<div class='alert alert-success'>İlan başarıyla güncellendi.</div>";
            $_SESSION['mesaj_tipi'] = "success";
            header("Location: ilan_yonetim.php"); // Yönetim sayfasına yönlendir
            exit;
        } else {
            $mesaj = "<div class='alert alert-danger'>İlan güncellenirken hata oluştu: " . $stmt_update_ilan->error . "</div>";
        }
        $stmt_update_ilan->close();
    }
}

// --- 3. Dropdownlar için Veri Çekme (kategoriler, cinsler, hastalıklar, iller, ilçeler) ---
$kategoriler = $conn->query("SELECT id, ad FROM kategoriler ORDER BY ad ASC")->fetch_all(MYSQLI_ASSOC);
$cinsler = $conn->query("SELECT id, ad FROM cinsler ORDER BY ad ASC")->fetch_all(MYSQLI_ASSOC);
$hastaliklar = $conn->query("SELECT id, ad FROM hastaliklar ORDER BY ad ASC")->fetch_all(MYSQLI_ASSOC);
$iller = $conn->query("SELECT id, ad FROM il ORDER BY ad ASC")->fetch_all(MYSQLI_ASSOC);

$ilceler = [];
if ($ilan['il_id']) {
    $stmt_ilce = $conn->prepare("SELECT id, ad FROM ilce WHERE il_id = ? ORDER BY ad ASC");
    $stmt_ilce->bind_param("i", $ilan['il_id']);
    $stmt_ilce->execute();
    $ilceler = $stmt_ilce->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_ilce->close();
}

// --- HTML Kısım ---
include("includes/admin_header.php"); // Başlık ve genel admin panel yapısı
?>

<div class="container mt-5">
    <div class="card shadow-lg border-primary">
        <div class="card-header bg-primary text-white py-3">
            <h2 class="mb-0 text-center"><i class="fas fa-edit me-2"></i> İlanı Düzenle: <?= htmlspecialchars($ilan['baslik']) ?></h2>
        </div>
        <div class="card-body p-4">
            <?php if (!empty($mesaj)): ?>
                <?= $mesaj ?>
            <?php elseif (isset($_SESSION['mesaj'])): ?>
                <div class='alert alert-<?= $_SESSION['mesaj_tipi'] ?? 'info' ?> alert-dismissible fade show' role='alert'>
                    <?= $_SESSION['mesaj'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['mesaj']); unset($_SESSION['mesaj_tipi']); ?>
            <?php endif; ?>

            <form action="ilan_duzenle.php?id=<?= $ilan_id ?>" method="POST" enctype="multipart/form-data">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="baslik" class="form-label fw-bold">İlan Başlığı <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="baslik" name="baslik" value="<?= htmlspecialchars($ilan['baslik']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="durum" class="form-label fw-bold">Durum <span class="text-danger">*</span></label>
                        <select class="form-select" id="durum" name="durum" required>
                            <option value="Aktif" <?= ($ilan['durum'] === 'Aktif') ? 'selected' : '' ?>>Aktif</option>
                            <option value="Pasif" <?= ($ilan['durum'] === 'Pasif') ? 'selected' : '' ?>>Pasif</option>
                            <option value="Sahiplenildi" <?= ($ilan['durum'] === 'Sahiplenildi') ? 'selected' : '' ?>>Sahiplenildi</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label for="aciklama" class="form-label fw-bold">Açıklama <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="aciklama" name="aciklama" rows="6" required><?= htmlspecialchars($ilan['aciklama']) ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label for="kategori_id" class="form-label fw-bold">Kategori <span class="text-danger">*</span></label>
                        <select class="form-select" id="kategori_id" name="kategori_id" required>
                            <?php foreach ($kategoriler as $kategori): ?>
                                <option value="<?= $kategori['id'] ?>" <?= ($ilan['kategori_id'] == $kategori['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($kategori['ad']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="cins_id" class="form-label fw-bold">Cins <span class="text-danger">*</span></label>
                        <select class="form-select" id="cins_id" name="cins_id" required>
                            <?php foreach ($cinsler as $cins): ?>
                                <option value="<?= $cins['id'] ?>" <?= ($ilan['cins_id'] == $cins['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cins['ad']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="hastalik_id" class="form-label fw-bold">Hastalık (Varsa)</label>
                        <select class="form-select" id="hastalik_id" name="hastalik_id">
                            <option value="0">Yok</option>
                            <?php foreach ($hastaliklar as $hastalik): ?>
                                <option value="<?= $hastalik['id'] ?>" <?= ($ilan['hastalik_id'] == $hastalik['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($hastalik['ad']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="il_id" class="form-label fw-bold">İl <span class="text-danger">*</span></label>
                        <select class="form-select" id="il_id" name="il_id" required>
                            <?php foreach ($iller as $il): ?>
                                <option value="<?= $il['id'] ?>" <?= ($ilan['il_id'] == $il['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($il['ad']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="ilce_id" class="form-label fw-bold">İlçe <span class="text-danger">*</span></label>
                        <select class="form-select" id="ilce_id" name="ilce_id" required>
                            <?php if (empty($ilceler)): ?>
                                <option value="">Lütfen önce il seçin</option>
                            <?php else: ?>
                                <?php foreach ($ilceler as $ilce): ?>
                                    <option value="<?= $ilce['id'] ?>" <?= ($ilan['ilce_id'] == $ilce['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($ilce['ad']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="ilan_foto" class="form-label fw-bold">İlan Fotoğrafı</label>
                        <?php if (!empty($ilan['foto'])): ?>
                            <div class="mb-2">
                                <img src="<?= htmlspecialchars("../" . $ilan['foto']) ?>" alt="Mevcut İlan Fotoğrafı" class="img-thumbnail" style="max-width: 150px; height: auto;">
                                <small class="text-muted d-block mt-1">Mevcut fotoğraf</small>
                            </div>
                        <?php endif; ?>
                        <input class="form-control" type="file" id="ilan_foto" name="ilan_foto" accept="image/*">
                        <small class="form-text text-muted">Yeni bir fotoğraf seçerseniz mevcut fotoğraf değişecektir.</small>
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-success btn-lg me-3"><i class="fas fa-check-circle me-2"></i> İlanı Güncelle</button>
                    <a href="ilan_yonetim.php" class="btn btn-secondary btn-lg"><i class="fas fa-arrow-alt-circle-left me-2"></i> Geri Dön</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('il_id').addEventListener('change', function() {
        var ilId = this.value;
        var ilceSelect = document.getElementById('ilce_id');
        ilceSelect.innerHTML = '<option value="">Yükleniyor...</option>';

        if (ilId) {
            fetch('../get_ilceler.php?il_id=' + ilId)
                .then(response => response.json())
                .then(data => {
                    ilceSelect.innerHTML = '<option value="">İlçe Seçin</option>';
                    data.forEach(function(ilce) {
                        var option = document.createElement('option');
                        option.value = ilce.id;
                        option.textContent = ilce.ad;
                        ilceSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error fetching districts:', error);
                    ilceSelect.innerHTML = '<option value="">İlçeler yüklenemedi</option>';
                });
        } else {
            ilceSelect.innerHTML = '<option value="">Lütfen önce il seçin</option>';
        }
    });
</script>

<?php
include("includes/admin_footer.php"); // Altbilgi ve kapanış etiketleri
?>