<?php
// ilanlar.php - Sadece aktif ilanların listelendiği sayfa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$page_title = "Sahiplendirme İlanları - Sıcak Patizi";
include("includes/db.php"); // Veritabanı bağlantısı
include("includes/header.php"); // Tema ve Header

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
$user_id = $_SESSION['kullanici_id'] ?? null;
?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Sayfa Başlığı -->
    <div class="text-center mb-10">
        <h1 class="text-4xl font-bold text-gray-800 mb-3 flex items-center justify-center">
            <i class="fas fa-paw text-primary mr-3"></i>
            Sahiplendirme İlanları
        </h1>
        <p class="text-lg text-gray-600">Can dostlarımıza sıcak bir yuva olun, sevgiyi paylaşın</p>
    </div>

    <!-- Filtreler Kartı -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-10 card-hover border border-pink-100/50">
        <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-filter text-primary mr-2"></i>
            İlanlarda Ara & Filtrele
        </h2>
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 items-end">
            <div>
                <label for="kategori" class="block text-sm font-medium text-gray-700 mb-1">
                    <i class="fas fa-tag mr-1 text-primary"></i>Tür
                </label>
                <select name="kategori_id" id="kategori" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-sm bg-white">
                    <option value="">Tümü</option>
                    <?php foreach($kategoriler as $kat): ?>
                        <option value="<?= $kat['id'] ?>" <?= (@$_GET['kategori_id']==$kat['id'])?'selected':'' ?>><?= htmlspecialchars($kat['ad']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="cins" class="block text-sm font-medium text-gray-700 mb-1">
                    <i class="fas fa-paw mr-1 text-primary"></i>Cins
                </label>
                <select name="cins_id" id="cins" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-sm bg-white">
                    <option value="">Tümü</option>
                </select>
            </div>
            <div>
                <label for="il" class="block text-sm font-medium text-gray-700 mb-1">
                    <i class="fas fa-map-marker-alt mr-1 text-primary"></i>İl
                </label>
                <select name="il_id" id="il" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-sm bg-white">
                    <option value="">Tümü</option>
                    <?php foreach($iller as $il_data): ?>
                        <option value="<?= $il_data['id'] ?>" <?= (@$_GET['il_id']==$il_data['id'])?'selected':'' ?>><?= htmlspecialchars($il_data['ad']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="ilce" class="block text-sm font-medium text-gray-700 mb-1">
                    <i class="fas fa-map-pin mr-1 text-primary"></i>İlçe
                </label>
                <select name="ilce_id" id="ilce" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-sm bg-white">
                    <option value="">Tümü</option>
                </select>
            </div>
            <div>
                <label for="hastalik" class="block text-sm font-medium text-gray-700 mb-1">
                    <i class="fas fa-heartbeat mr-1 text-primary"></i>Hastalık
                </label>
                <select name="hastalik_id" id="hastalik" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-sm bg-white">
                    <option value="">Tümü</option>
                </select>
            </div>
            <div>
                <button type="submit" class="w-full btn-gradient text-white font-semibold py-2 px-4 rounded-lg transition duration-300 flex items-center justify-center">
                    <i class="fas fa-search mr-2"></i>Filtrele
                </button>
            </div>
        </form>
    </div>

    <!-- İlan Kartları Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($ilan = $result->fetch_assoc()): ?>
                <?php
                // Favori kontrolü
                $is_favorited = false;
                if ($user_id) {
                    $stmt_fav = $conn->prepare("SELECT COUNT(*) FROM favoriler WHERE kullanici_id = ? AND ilan_id = ?");
                    $stmt_fav->bind_param("ii", $user_id, $ilan['id']);
                    $stmt_fav->execute();
                    $stmt_fav->bind_result($fav_count);
                    $stmt_fav->fetch();
                    $stmt_fav->close();
                    if ($fav_count > 0) $is_favorited = true;
                }
                ?>
                <div class="bg-white rounded-xl shadow-lg overflow-hidden card-hover flex flex-col justify-between">
                    <div class="relative">
                        <?php
                        $image_path = !empty($ilan['foto']) ? 'uploads/' . htmlspecialchars($ilan['foto']) : '';
                        $display_image = (file_exists($image_path) && !empty($image_path)) ? $image_path : 'https://via.placeholder.com/300x200?text=Resim+Yok';
                        ?>
                        <img src="<?= $display_image ?>" alt="<?= htmlspecialchars($ilan['baslik']) ?>" class="w-full h-48 object-cover">

                        <!-- Favori Butonu -->
                        <?php if ($user_id): ?>
                            <button class="favorite-btn absolute top-3 right-3 bg-white/90 backdrop-blur-sm rounded-full p-2 shadow-md <?= $is_favorited ? 'text-primary' : 'text-gray-400 hover:text-primary' ?> transition-colors duration-200"
                                    data-ilan-id="<?= $ilan['id'] ?>">
                                <i class="fas fa-heart"></i>
                            </button>
                        <?php endif; ?>
                    </div>

                    <div class="p-4 flex-1 flex flex-col justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-gray-800 mb-2 line-clamp-1"><?= htmlspecialchars($ilan['baslik']) ?></h3>

                            <!-- Etiketler -->
                            <div class="flex flex-wrap gap-1.5 mb-3">
                                <?php if (!empty($ilan['kategori_ad'])): ?>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-primary-light text-primary">
                                        <i class="fas fa-tag mr-1"></i><?= htmlspecialchars($ilan['kategori_ad']) ?>
                                    </span>
                                <?php endif; ?>

                                <?php if (!empty($ilan['cins_ad'])): ?>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <i class="fas fa-paw mr-1"></i><?= htmlspecialchars($ilan['cins_ad']) ?>
                                    </span>
                                <?php endif; ?>

                                <?php if (!empty($ilan['il_ad'])): ?>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                        <i class="fas fa-map-marker-alt mr-1"></i><?= htmlspecialchars($ilan['il_ad']) ?><?= !empty($ilan['ilce_ad']) ? ' / ' . htmlspecialchars($ilan['ilce_ad']) : '' ?>
                                    </span>
                                <?php endif; ?>

                                <?php if (!empty($ilan['hastalik_ad'])): ?>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                        <i class="fas fa-heartbeat mr-1"></i><?= htmlspecialchars($ilan['hastalik_ad']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <p class="text-gray-600 text-sm mb-4 line-clamp-2"><?= htmlspecialchars($ilan['aciklama'] ?? '') ?></p>
                        </div>

                        <div class="flex justify-between items-center pt-3 border-t border-gray-100 mt-auto">
                            <span class="text-xs text-gray-500">
                                <i class="fas fa-calendar mr-1"></i>
                                <?= date('d.m.Y', strtotime($ilan['tarih'])) ?>
                            </span>
                            <a href="ilan_detay.php?id=<?= $ilan['id'] ?>" 
                               class="btn-gradient text-white px-4 py-2 rounded-lg text-sm font-semibold hover:shadow-md transition">
                                Detaylar
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-span-full text-center py-16 bg-white rounded-xl shadow-lg card-hover">
                <div class="text-6xl mb-4">🐾</div>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">İlan Bulunamadı</h3>
                <p class="text-gray-600 mb-6">Aradığınız kriterlere uygun henüz ilan bulunmuyor.</p>
                <a href="ilan_ekle.php" class="btn-gradient text-white px-6 py-3 rounded-lg font-semibold inline-flex items-center shadow-md">
                    <i class="fas fa-plus mr-2"></i>İlk İlanı Sen Ver
                </a>
            </div>
        <?php endif; ?>
    </div>
</main>

<script>
    const cinsler = <?= json_encode($cinsler) ?>;
    const ilceler = <?= json_encode($ilceler) ?>;
    const hastaliklarCins = <?= json_encode($hastaliklar_cins) ?>;

    document.addEventListener("DOMContentLoaded", function() {
        const kategoriSelect = document.getElementById('kategori');
        const cinsSelect = document.getElementById('cins');
        const hastalikSelect = document.getElementById('hastalik');
        const ilSelect = document.getElementById('il');
        const ilceSelect = document.getElementById('ilce');

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

        if (kategoriSelect && cinsSelect) {
            kategoriSelect.addEventListener('change', function() {
                populateCins(this.value);
            });
        }

        if (cinsSelect && hastalikSelect) {
            cinsSelect.addEventListener('change', function() {
                populateHastalik(this.value);
            });
        }

        if (ilSelect && ilceSelect) {
            ilSelect.addEventListener('change', function() {
                populateIlce(this.value);
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
                            currentButton.classList.remove('text-gray-400', 'hover:text-primary');
                            currentButton.classList.add('text-primary');
                        } else {
                            currentButton.classList.remove('text-primary');
                            currentButton.classList.add('text-gray-400', 'hover:text-primary');
                        }
                    } else {
                        if (res.redirect) {
                            window.location.href = res.redirect;
                        } else {
                            alert(res.message);
                        }
                    }
                })
                .catch(error => {
                    console.error("AJAX error:", error);
                    alert('Bir hata oluştu. Lütfen tekrar deneyin.');
                });
            });
        });

        // Current GET params
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

<<<<<<< Updated upstream:ilanlar.php
<?php include("includes/footer.php"); ?>
=======
>>>>>>> Stashed changes:pages/listings/ilanlar.php
