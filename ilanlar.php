<?php
// ilanlar.php - Sadece aktif ilanların listelendiği sayfa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include("includes/db.php"); // Veritabanı bağlantısı

// Kategoriler
$kategoriler = [];
$res = $conn->query("SELECT * FROM kategoriler ORDER BY ad ASC");
while($row = $res->fetch_assoc()) $kategoriler[] = $row;

// Cinsler
$cinsler = [];
$res = $conn->query("SELECT id, kategori_id, ad FROM cinsler ORDER BY kategori_id, ad ASC");
while($row = $res->fetch_assoc()) $cinsler[$row['kategori_id']][] = $row;

// İller
$iller = [];
$res = $conn->query("SELECT * FROM il ORDER BY ad ASC");
while($row = $res->fetch_assoc()) $iller[] = $row;

// İlçeler
$ilceler = [];
$res = $conn->query("SELECT id, il_id, ad FROM ilce ORDER BY il_id, ad ASC");
while($row = $res->fetch_assoc()) $ilceler[$row['il_id']][] = $row;

// Hastalıklar (cins bazında)
$hastaliklar_cins = [];
$res = $conn->query("SELECT hc.cins_id, h.id, h.ad FROM hastaliklar_cinsler hc
                     JOIN hastaliklar h ON hc.hastalik_id = h.id ORDER BY hc.cins_id, h.ad ASC");
while($row = $res->fetch_assoc()) {
    $hastaliklar_cins[$row['cins_id']][] = [
        'id' => $row['id'],
        'ad' => $row['ad']
    ];
}

// --- Filtreleme için dinamik WHERE ---
$where = "WHERE i.durum = 'aktif'";
$params = [];
$types = "";

if (!empty($_GET['kategori_id'])) {
    $where .= " AND i.kategori_id = ?";
    $params[] = $_GET['kategori_id'];
    $types .= "i";
}
if (!empty($_GET['cins_id'])) {
    $where .= " AND i.cins_id = ?";
    $params[] = $_GET['cins_id'];
    $types .= "i";
}
if (!empty($_GET['il_id'])) {
    $where .= " AND i.il_id = ?";
    $params[] = $_GET['il_id'];
    $types .= "i";
}
if (!empty($_GET['ilce_id'])) {
    $where .= " AND i.ilce_id = ?";
    $params[] = $_GET['ilce_id'];
    $types .= "i";
}
if (!empty($_GET['hastalik_id'])) {
    $where .= " AND i.hastalik_id = ?";
    $params[] = $_GET['hastalik_id'];
    $types .= "i";
}

$sql = "
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
    $where
    ORDER BY i.tarih DESC
";
$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$user_id = $_SESSION['kullanici_id'] ?? null; // user_id veya kullanici_id'nizi kontrol edin
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sahiplendirme İlanları</title>
    <link href="./dist/output.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        /* Custom Tailwind form select styling (if not already in input.css) */
        .form-select-tailwind {
            @apply block w-full px-3 py-2 text-base font-normal text-gray-700 bg-white bg-clip-padding bg-no-repeat border border-solid border-gray-300 rounded-md transition ease-in-out m-0
                   focus:text-gray-700 focus:bg-white focus:border-koyu-pembe focus:outline-none;
        }

        /* Info Tag Styling for pastel look */
        .info-tag-tailwind {
            @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium;
        }
    </style>
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal min-h-screen flex flex-col">

<?php include("includes/header.php"); ?>

<div class="container mx-auto px-4 py-8 mt-16 md:mt-24 flex-grow"> <h1 class="text-4xl font-extrabold text-center text-koyu-pembe mb-8">Sahiplendirme İlanları</h1>

    <form method="GET" class="bg-white p-6 rounded-lg shadow-md mb-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 items-end">
            <div>
                <label for="kategori" class="block text-gray-700 text-sm font-semibold mb-2">Hayvan Türü:</label>
                <select name="kategori_id" id="kategori" class="form-select-tailwind">
                    <option value="">Tümü</option>
                    <?php foreach($kategoriler as $kat): ?>
                        <option value="<?= $kat['id'] ?>" <?= (@$_GET['kategori_id']==$kat['id'])?'selected':'' ?>><?= htmlspecialchars($kat['ad']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="cins" class="block text-gray-700 text-sm font-semibold mb-2">Cins:</label>
                <select name="cins_id" id="cins" class="form-select-tailwind">
                    <option value="">Tümü</option>
                    </select>
            </div>
            <div>
                <label for="il" class="block text-gray-700 text-sm font-semibold mb-2">İl:</label>
                <select name="il_id" id="il" class="form-select-tailwind">
                    <option value="">Tümü</option>
                    <?php foreach($iller as $il_data): // $il değişken adı çakışmasından kaçınmak için $il_data kullanıldı ?>
                        <option value="<?= $il_data['id'] ?>" <?= (@$_GET['il_id']==$il_data['id'])?'selected':'' ?>><?= htmlspecialchars($il_data['ad']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="ilce" class="block text-gray-700 text-sm font-semibold mb-2">İlçe:</label>
                <select name="ilce_id" id="ilce" class="form-select-tailwind">
                    <option value="">Tümü</option>
                    </select>
            </div>
            <div>
                <label for="hastalik" class="block text-gray-700 text-sm font-semibold mb-2">Hastalık:</label>
                <select name="hastalik_id" id="hastalik" class="form-select-tailwind">
                    <option value="">Tümü</option>
                    <!-- Hastalıklar dinamik olarak yüklenecek -->
                </select>
            </div>
            <div>
                <button type="submit" class="bg-koyu-pembe hover:bg-pink-700 text-white font-bold py-2 px-4 rounded w-full transition duration-300">
                    <i class="fas fa-filter mr-2"></i>Filtrele
                </button>
            </div>
        </div>
    </form>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($ilan = $result->fetch_assoc()): ?>
                <?php
                // Favori kontrolü
                $is_favorited = false;
                if ($user_id) {
                    $stmt = $conn->prepare("SELECT COUNT(*) FROM favoriler WHERE kullanici_id = ? AND ilan_id = ?");
                    $stmt->bind_param("ii", $user_id, $ilan['id']);
                    $stmt->execute();
                    $stmt->bind_result($fav_count);
                    $stmt->fetch();
                    $stmt->close();
                    if ($fav_count > 0) $is_favorited = true;
                }
                ?>
                <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 flex items-center p-3 gap-3 relative">
                    <div class="w-24 h-24 flex-shrink-0">
                        <img src="uploads/<?= htmlspecialchars($ilan['foto'] ?: 'placeholder.jpg') ?>" alt="<?= htmlspecialchars($ilan['baslik']) ?>"
                             class="rounded-full w-full h-full object-cover border-2 border-koyu-pembe shadow-sm">
                        <?php if ($user_id): ?>
                            <button class="favorite-btn absolute -top-1 -right-1 z-10 bg-white rounded-full p-1 text-base shadow-md
                                <?= $is_favorited ? 'text-koyu-pembe' : 'text-gray-400 hover:text-koyu-pembe' ?> transition-colors duration-200"
                                data-ilan-id="<?= $ilan['id'] ?>">
                                <i class="fas fa-heart"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                    <div class="flex-grow flex flex-col justify-between">
                        <h2 class="text-lg font-semibold text-gray-800 leading-tight mb-1"><?= htmlspecialchars($ilan['baslik']) ?></h2>
                        <div class="flex flex-wrap gap-1 text-sm mb-2">
                            <?php if (!empty($ilan['kategori_ad'])): ?>
                                <span class="info-tag-tailwind bg-toz-pembe text-koyu-pembe">
                                    <i class="fas fa-folder mr-1"></i><?= htmlspecialchars($ilan['kategori_ad']) ?>
                                </span>
                            <?php endif; ?>
                            <?php if (!empty($ilan['il_ad'])): ?>
                                <span class="info-tag-tailwind bg-gray-200 text-gray-700">
                                    <i class="fas fa-map-marker-alt mr-1"></i><?= htmlspecialchars($ilan['il_ad']) ?>
                                </span>
                            <?php endif; ?>
                            <?php if (!empty($ilan['ilce_ad'])): ?>
                                <span class="info-tag-tailwind bg-gray-200 text-gray-700">
                                    <i class="fas fa-map-pin mr-1"></i><?= htmlspecialchars($ilan['ilce_ad']) ?>
                                </span>
                            <?php endif; ?>
                            </div>
                        <a href="ilan_detay.php?id=<?= $ilan['id'] ?>"
                           class="block text-center bg-acik-pembe hover:bg-toz-pembe text-koyu-pembe text-sm font-semibold py-1 px-2 rounded-md transition duration-300">
                           Detayları Görüntüle
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-span-full">
                <div class="bg-soluk-mavi text-blue-900 p-4 rounded-lg text-center text-lg font-semibold shadow-md">
                    Henüz aktif ilan bulunmamaktadır.
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include("includes/footer.php"); ?>

<script>
    const cinsler = <?= json_encode($cinsler) ?>;
    const ilceler = <?= json_encode($ilceler) ?>;
    const hastaliklarCins = <?= json_encode($hastaliklar_cins) ?>;

    document.addEventListener("DOMContentLoaded", function() {
        // Elements
        const kategoriSelect = document.getElementById('kategori');
        const cinsSelect = document.getElementById('cins');
        const hastalikSelect = document.getElementById('hastalik');
        const ilSelect = document.getElementById('il');
        const ilceSelect = document.getElementById('ilce');

        // Functions
        function populateCins(kategoriId, selectedCinsId = null) {
            cinsSelect.innerHTML = '<option value="">Tümü</option>';
            if (cinsler[kategoriId]) {
                cinsler[kategoriId].forEach(function(c) {
                    const option = document.createElement('option');
                    option.value = c.id;
                    option.textContent = c.ad;
                    if (selectedCinsId && selectedCinsId == c.id) {
                        option.selected = true;
                    }
                    cinsSelect.appendChild(option);
                });
            }
            // Reset hastalık when kategori changes
            populateHastalik('', null);
        }

        function populateHastalik(cinsId, selectedHastalikId = null) {
            hastalikSelect.innerHTML = '<option value="">Tümü</option>';
            if (cinsId && hastaliklarCins[cinsId]) {
                hastaliklarCins[cinsId].forEach(function(h) {
                    const option = document.createElement('option');
                    option.value = h.id;
                    option.textContent = h.ad;
                    if (selectedHastalikId && selectedHastalikId == h.id) {
                        option.selected = true;
                    }
                    hastalikSelect.appendChild(option);
                });
            }
        }

        function populateIlce(ilId, selectedIlceId = null) {
            ilceSelect.innerHTML = '<option value="">Tümü</option>';
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

        // Event listeners
        if (kategoriSelect && cinsSelect) {
            kategoriSelect.addEventListener('change', function() {
                const kategoriId = this.value;
                populateCins(kategoriId);
            });
        }

        if (cinsSelect && hastalikSelect) {
            cinsSelect.addEventListener('change', function() {
                const cinsId = this.value;
                populateHastalik(cinsId);
            });
        }

        if (ilSelect && ilceSelect) {
            ilSelect.addEventListener('change', function() {
                const ilId = this.value;
                populateIlce(ilId);
            });
        }

        // Favori kalp butonu için Vanilla JS
        document.querySelectorAll('.favorite-btn').forEach(button => {
            button.addEventListener('click', function() {
                const ilanId = this.dataset.ilanId;
                const currentButton = this;

                fetch('includes/ajax_favori_toggle.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `ilan_id=${ilanId}`
                })
                .then(response => response.json())
                .then(res => {
                    if (res.status === 'success') {
                        if (res.action === 'added') {
                            currentButton.classList.remove('text-gray-400', 'hover:text-koyu-pembe');
                            currentButton.classList.add('text-koyu-pembe');
                        } else {
                            currentButton.classList.remove('text-koyu-pembe');
                            currentButton.classList.add('text-gray-400', 'hover:text-koyu-pembe');
                        }
                    } else {
                        alert(res.message);
                        if (res.redirect) {
                            window.location.href = res.redirect;
                        }
                    }
                })
                .catch(error => {
                    console.error("AJAX error:", error);
                    alert('Bir hata oluştu. Lütfen tekrar deneyin.');
                });
            });
        });

        // Initialize dropdowns based on current GET parameters
        <?php if (!empty($_GET['kategori_id'])): ?>
            const initialKategoriId = '<?= (int)$_GET['kategori_id'] ?>';
            populateCins(initialKategoriId, '<?= (int)($_GET['cins_id'] ?? 0) ?>');
            
            <?php if (!empty($_GET['cins_id'])): ?>
                setTimeout(function() {
                    const initialCinsId = '<?= (int)$_GET['cins_id'] ?>';
                    populateHastalik(initialCinsId, '<?= (int)($_GET['hastalik_id'] ?? 0) ?>');
                }, 100);
            <?php endif; ?>
        <?php endif; ?>

        <?php if (!empty($_GET['il_id'])): ?>
            const initialIlId = '<?= (int)$_GET['il_id'] ?>';
            populateIlce(initialIlId, '<?= (int)($_GET['ilce_id'] ?? 0) ?>');
        <?php endif; ?>
    });
</script>

</body>
</html>