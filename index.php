<?php
// filepath: c:\xampp\htdocs\hayvan_sitem\index.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$page_title = "Ana Sayfa - Sıcak Patizi";
include("includes/db.php");
include("includes/header.php"); // Use the standardized header

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

// Hastalıklar
$hastaliklar = [];
$res = $conn->query("SELECT * FROM hastaliklar ORDER BY ad ASC");
while($row = $res->fetch_assoc()) $hastaliklar[] = $row;

// Filtreleme için dinamik WHERE
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

// İlanları getir (Aktif olanlar)
$sql = "SELECT i.*, k.kullanici_adi, c.ad as cins_adi, h.ad as hastalik_adi, kat.ad as kategori_adi, il.ad as il_adi, ilce.ad as ilce_adi
        FROM ilanlar i 
        LEFT JOIN kullanicilar k ON i.kullanici_id = k.id
        LEFT JOIN cinsler c ON i.cins_id = c.id
        LEFT JOIN hastaliklar h ON i.hastalik_id = h.id
        LEFT JOIN kategoriler kat ON i.kategori_id = kat.id
        LEFT JOIN il il ON i.il_id = il.id
        LEFT JOIN ilce ilce ON i.ilce_id = ilce.id
        $where
        ORDER BY i.tarih DESC
        LIMIT 8";

$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Sahiplenen hayvanları getir (Son 6 adet)
$sahiplenen_sql = "SELECT i.*, k.kullanici_adi, c.ad as cins_adi, kat.ad as kategori_adi, il.ad as il_adi, ilce.ad as ilce_adi,
                   si.sahiplenen_yorumu, si.yorum_tarihi
                   FROM ilanlar i 
                   LEFT JOIN kullanicilar k ON i.kullanici_id = k.id
                   LEFT JOIN cinsler c ON i.cins_id = c.id
                   LEFT JOIN kategoriler kat ON i.kategori_id = kat.id
                   LEFT JOIN il il ON i.il_id = il.id
                   LEFT JOIN ilce ilce ON i.ilce_id = ilce.id
                   LEFT JOIN sahiplenme_istekleri si ON i.id = si.ilan_id AND si.durum = 'tamamlandi'
                   WHERE i.durum = 'sahiplenildi'
                   ORDER BY i.tarih DESC
                   LIMIT 6";

$sahiplenen_stmt = $conn->prepare($sahiplenen_sql);
$sahiplenen_stmt->execute();
$sahiplenen_result = $sahiplenen_stmt->get_result();

$user_id = $_SESSION['kullanici_id'] ?? null;
?>

<style>
    /* Additional styles for the index page */
    .gradient-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }

    .gradient-success:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
    }

    /* SweetAlert2 Özel Stiller */
    .swal2-popup {
        border-radius: 15px !important;
        border: none !important;
    }
    
    .swal2-title {
        color: var(--primary) !important;
        font-weight: bold !important;
    }
    
    .swal2-confirm {
        background-color: var(--primary) !important;
        border: none !important;
        border-radius: 8px !important;
        padding: 10px 30px !important;
        font-weight: 600 !important;
        transition: all 0.3s ease !important;
    }
    
    .swal2-confirm:hover {
        background-color: var(--primary-light) !important;
        transform: translateY(-1px) !important;
    }
    
    .swal2-cancel {
        background-color: #6b7280 !important;
        border: none !important;
        border-radius: 8px !important;
        padding: 10px 30px !important;
        font-weight: 600 !important;
    }
    
    .swal2-toast {
        border-radius: 12px !important;
        font-family: inherit !important;
    }
    
    .swal2-toast .swal2-title {
        font-size: 16px !important;
        margin: 0 !important;
    }
    
    .swal2-toast.swal2-show {
        animation: slideInRight 0.3s ease-out !important;
    }
    
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
</style>

<!-- Ana İçerik -->
<main class="max-w-7xl mx-auto px-6 py-8">
    <div class="flex gap-8">
        <!-- Sol Sidebar - Barınaklar -->
        <aside class="hidden lg:block w-80 bg-white rounded-xl shadow-lg p-6 h-fit sticky top-24">
            <div class="mb-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-building mr-2 text-primary"></i>
                    Yakındaki Barınaklar
                </h2>
                <p class="text-sm text-gray-600 mb-4">Size en yakın barınakları keşfedin</p>
            </div>

            <!-- Şehir Seçici -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Şehir Seçin:</label>
                <select id="sehirFilter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                    <option value="">Tüm Şehirler</option>
                    <?php
                    // Barınakları getir
                    $barinaklar_sql = "SELECT DISTINCT b.*, il.ad as il_adi 
                                      FROM hayvan_barinaklari b 
                                      LEFT JOIN il ON b.il_id = il.id 
                                      WHERE b.aktif = 1
                                      ORDER BY il.ad ASC LIMIT 10";
                    $barinaklar_result = $conn->query($barinaklar_sql);
                    
                    $sehir_sql = "SELECT DISTINCT il.* FROM il 
                                  INNER JOIN hayvan_barinaklari b ON il.id = b.il_id 
                                  WHERE b.aktif = 1
                                  ORDER BY il.ad ASC";
                    $sehirler_result = $conn->query($sehir_sql);
                    if ($sehirler_result) {
                        while ($sehir = $sehirler_result->fetch_assoc()): ?>
                            <option value="<?= $sehir['id'] ?>"><?= htmlspecialchars($sehir['ad']) ?></option>
                        <?php endwhile;
                    } ?>
                </select>
            </div>

            <!-- Barınaklar Listesi -->
            <div id="barinaklarListesi" class="space-y-3 max-h-96 overflow-y-auto">
                <?php if ($barinaklar_result && $barinaklar_result->num_rows > 0): ?>
                    <?php while ($barinak = $barinaklar_result->fetch_assoc()): ?>
                        <div class="barinak-item border border-gray-200 rounded-lg p-3 hover:border-primary transition-colors" data-sehir="<?= $barinak['il_id'] ?>">
                            <div class="flex items-start space-x-3">
                                <div class="text-2xl">🏢</div>
                                <div class="flex-1">
                                    <h3 class="font-semibold text-gray-800 text-sm">
                                        <?= htmlspecialchars($barinak['ad']) ?>
                                    </h3>
                                    <p class="text-xs text-gray-600 flex items-center mt-1">
                                        <i class="fas fa-map-marker-alt mr-1"></i>
                                        <?= htmlspecialchars($barinak['il_adi']) ?>
                                    </p>
                                    <?php if ($barinak['telefon']): ?>
                                        <p class="text-xs text-gray-600 flex items-center mt-1">
                                            <i class="fas fa-phone mr-1"></i>
                                            <a href="tel:<?= htmlspecialchars($barinak['telefon']) ?>" 
                                               class="text-primary hover:underline">
                                                <?= htmlspecialchars($barinak['telefon']) ?>
                                            </a>
                                        </p>
                                    <?php endif; ?>
                                    <?php if ($barinak['adres']): ?>
                                        <p class="text-xs text-gray-500 mt-1 line-clamp-2">
                                            <?= htmlspecialchars(substr($barinak['adres'], 0, 50)) ?>...
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-building text-3xl mb-2"></i>
                        <p>Henüz barınak bulunmuyor</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Tüm Barınakları Görüntüle -->
            <div class="mt-4 pt-4 border-t">
                <a href="barinaklar.php" class="btn-gradient text-white px-4 py-2 rounded-md text-sm font-semibold w-full text-center block">
                    <i class="fas fa-eye mr-2"></i>Tüm Barınakları Görüntüle
                </a>
            </div>
        </aside>

        <!-- Ana İçerik Alanı -->
        <div class="flex-1">
            <!-- Hero Bölümü -->
            <div class="text-center mb-16">
                <h1 class="text-5xl font-bold text-gray-800 mb-6">
                    <i class="fas fa-heart mr-4 text-primary"></i>
                    Sıcak Patizi
                </h1>
                <p class="text-xl text-gray-600 mb-8">Onlar İçin Yuva, Senin İçin Dostluk</p>
                <div class="flex justify-center space-x-4">
                    <a href="#aktif-ilanlar" class="btn-gradient text-white px-6 py-3 rounded-md font-semibold">
                        <i class="fas fa-search mr-2"></i>İlanları İncele
                    </a>
                    <a href="ilan_ekle.php" class="gradient-success text-white px-6 py-3 rounded-md font-semibold">
                        <i class="fas fa-plus mr-2"></i>İlan Ver
                    </a>
                </div>
            </div>

            <!-- İstatistikler -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-16">
                <?php
                // İstatistikleri çek
                $aktif_ilan_sayisi = $conn->query("SELECT COUNT(*) as total FROM ilanlar WHERE durum = 'aktif'")->fetch_assoc()['total'];
                $sahiplenen_sayisi = $conn->query("SELECT COUNT(*) as total FROM ilanlar WHERE durum = 'sahiplenildi'")->fetch_assoc()['total'];
                $toplam_kullanici = $conn->query("SELECT COUNT(*) as total FROM kullanicilar WHERE kullanici_tipi = 'kullanici'")->fetch_assoc()['total'];
                $toplam_etkinlik = $conn->query("SELECT COUNT(*) as total FROM hayvan_etkinlikleri WHERE aktif = 1")->fetch_assoc()['total'];
                ?>
                
                <div class="bg-white rounded-xl shadow-lg p-6 text-center card-hover">
                    <div class="text-3xl font-bold text-primary mb-2"><?= $aktif_ilan_sayisi ?></div>
                    <div class="text-gray-600">Aktif İlan</div>
                </div>
                
                <div class="bg-white rounded-xl shadow-lg p-6 text-center card-hover">
                    <div class="text-3xl font-bold text-green-600 mb-2"><?= $sahiplenen_sayisi ?></div>
                    <div class="text-gray-600">Mutlu Son</div>
                </div>
                
                <div class="bg-white rounded-xl shadow-lg p-6 text-center card-hover">
                    <div class="text-3xl font-bold text-blue-600 mb-2"><?= $toplam_kullanici ?></div>
                    <div class="text-gray-600">Üye Sayısı</div>
                </div>
                
                <div class="bg-white rounded-xl shadow-lg p-6 text-center card-hover">
                    <div class="text-3xl font-bold text-primary mb-2"><?= $toplam_etkinlik ?></div>
                    <div class="text-gray-600">Etkinlik</div>
                </div>
            </div>

            <!-- Filtreler -->
            <div id="aktif-ilanlar" class="bg-white rounded-xl shadow-lg p-6 mb-8 card-hover">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">
                    <i class="fas fa-filter mr-3 text-primary"></i>
                    İlanları Filtrele
                </h2>
                <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-tag mr-1 text-primary"></i>Kategori
                        </label>
                        <select name="kategori_id" id="kategori" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary focus:border-primary">
                            <option value="">Tüm Kategoriler</option>
                            <?php foreach($kategoriler as $kat): ?>
                                <option value="<?= $kat['id'] ?>" <?= (@$_GET['kategori_id']==$kat['id'])?'selected':'' ?>><?= htmlspecialchars($kat['ad']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-paw mr-1 text-primary"></i>Cins
                        </label>
                        <select name="cins_id" id="cins" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary focus:border-primary">
                            <option value="">Tüm Cinsler</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-map-marker-alt mr-1 text-primary"></i>İl
                        </label>
                        <select name="il_id" id="il" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary focus:border-primary">
                            <option value="">Tüm İller</option>
                            <?php foreach($iller as $il_data): ?>
                                <option value="<?= $il_data['id'] ?>" <?= (@$_GET['il_id']==$il_data['id'])?'selected':'' ?>><?= htmlspecialchars($il_data['ad']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-map-pin mr-1 text-primary"></i>İlçe
                        </label>
                        <select name="ilce_id" id="ilce" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary focus:border-primary">
                            <option value="">Tüm İlçeler</option>
                        </select>
                    </div>

                    <div class="flex items-end">
                        <button type="submit" class="w-full btn-gradient text-white px-4 py-2 rounded-md font-semibold">
                            <i class="fas fa-filter mr-2"></i>Filtrele
                        </button>
                    </div>
                </form>
            </div>

            <!-- Aktif İlanlar -->
            <div class="mb-16">
                <div class="flex justify-between items-center mb-8">
                    <h2 class="text-3xl font-bold text-gray-800">
                        <i class="fas fa-heart mr-3 text-primary"></i>
                        Sahiplenmeyi Bekleyen Dostlarımız
                    </h2>
                    <a href="ilanlar.php" class="btn-gradient text-white px-4 py-2 rounded-md font-semibold">
                        <i class="fas fa-arrow-right mr-2"></i>Tümünü Gör
                    </a>
                </div>
                
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
                            
                            <div class="bg-white rounded-xl shadow-lg overflow-hidden card-hover">
                                <div class="relative">
                                    <?php
                                    $image_path = !empty($ilan['foto']) ? 'uploads/' . htmlspecialchars($ilan['foto']) : '';
                                    $display_image = (file_exists($image_path) && !empty($image_path)) ? $image_path : 'https://via.placeholder.com/300x200?text=Resim+Yok';
                                    ?>
                                    <img src="<?= $display_image ?>" alt="<?= htmlspecialchars($ilan['baslik']) ?>" class="w-full h-48 object-cover">
                                    
                                    <!-- Favori Butonu -->
                                    <?php if ($user_id): ?>
                                        <button class="favorite-btn absolute top-3 right-3 bg-white rounded-full p-2 shadow-md <?= $is_favorited ? 'text-primary' : 'text-gray-400 hover:text-primary' ?> transition-colors duration-200"
                                                data-ilan-id="<?= $ilan['id'] ?>">
                                            <i class="fas fa-heart"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="p-4">
                                    <h3 class="text-lg font-bold text-gray-800 mb-2"><?= htmlspecialchars($ilan['baslik']) ?></h3>
                                    
                                    <!-- İlan Bilgileri -->
                                    <div class="space-y-2 mb-4">
                                        <?php if ($ilan['kategori_adi']): ?>
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-primary-light text-primary">
                                                <i class="fas fa-tag mr-1"></i><?= htmlspecialchars($ilan['kategori_adi']) ?>
                                            </span>
                                        <?php endif; ?>
                                        
                                        <?php if ($ilan['cins_adi']): ?>
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                <i class="fas fa-paw mr-1"></i><?= htmlspecialchars($ilan['cins_adi']) ?>
                                            </span>
                                        <?php endif; ?>
                                        
                                        <?php if ($ilan['il_adi']): ?>
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                                <i class="fas fa-map-marker-alt mr-1"></i><?= htmlspecialchars($ilan['il_adi']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <p class="text-gray-600 text-sm mb-4 line-clamp-3"><?= htmlspecialchars($ilan['aciklama']) ?></p>
                                    
                                    <div class="flex justify-between items-center">
                                        <span class="text-xs text-gray-500">
                                            <i class="fas fa-calendar mr-1"></i>
                                            <?= date('d.m.Y', strtotime($ilan['tarih'])) ?>
                                        </span>
                                        <a href="ilan_detay.php?id=<?= $ilan['id'] ?>" 
                                           class="btn-gradient text-white px-4 py-2 rounded-md text-sm font-semibold">
                                            Detay
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-span-full text-center py-16">
                            <div class="text-6xl mb-6">🐾</div>
                            <h3 class="text-2xl font-semibold text-gray-600 mb-4">İlan Bulunamadı</h3>
                            <p class="text-gray-500 mb-6">Aradığınız kriterlere uygun ilan bulunamadı.</p>
                            <a href="ilan_ekle.php" class="btn-gradient text-white px-6 py-3 rounded-md font-semibold">
                                <i class="fas fa-plus mr-2"></i>İlk İlanı Sen Ver
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sahiplenen Hayvanlar -->
            <div class="mb-16">
                <div class="flex justify-between items-center mb-8">
                    <h2 class="text-3xl font-bold text-gray-800">
                        <i class="fas fa-home mr-3 text-green-600"></i>
                        Mutlu Son Bulan Dostlarımız
                    </h2>
                    <span class="text-green-600 font-semibold">
                        <i class="fas fa-heart mr-2"></i>
                        <?= $sahiplenen_sayisi ?> mutlu son
                    </span>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php if ($sahiplenen_result && $sahiplenen_result->num_rows > 0): ?>
                        <?php while ($sahiplenen = $sahiplenen_result->fetch_assoc()): ?>
                            <div class="bg-white rounded-xl shadow-lg overflow-hidden card-hover border-l-4 border-green-500">
                                <div class="relative">
                                    <?php
                                    $image_path = !empty($sahiplenen['foto']) ? 'uploads/' . htmlspecialchars($sahiplenen['foto']) : '';
                                    $display_image = (file_exists($image_path) && !empty($image_path)) ? $image_path : 'https://via.placeholder.com/300x200?text=Resim+Yok';
                                    ?>
                                    <img src="<?= $display_image ?>" alt="<?= htmlspecialchars($sahiplenen['baslik']) ?>" class="w-full h-48 object-cover">
                                    
                                    <!-- Sahiplenildi Badge -->
                                    <div class="absolute top-3 left-3 bg-green-500 text-white px-3 py-1 rounded-full text-sm font-semibold">
                                        <i class="fas fa-home mr-1"></i>Sahiplenildi
                                    </div>
                                </div>
                                
                                <div class="p-4">
                                    <h3 class="text-lg font-bold text-gray-800 mb-2"><?= htmlspecialchars($sahiplenen['baslik']) ?></h3>
                                    
                                    <!-- İlan Bilgileri -->
                                    <div class="space-y-2 mb-4">
                                        <?php if ($sahiplenen['kategori_adi']): ?>
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <i class="fas fa-tag mr-1"></i><?= htmlspecialchars($sahiplenen['kategori_adi']) ?>
                                            </span>
                                        <?php endif; ?>
                                        
                                        <?php if ($sahiplenen['cins_adi']): ?>
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                <i class="fas fa-paw mr-1"></i><?= htmlspecialchars($sahiplenen['cins_adi']) ?>
                                            </span>
                                        <?php endif; ?>
                                        
                                        <?php if ($sahiplenen['il_adi']): ?>
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                                <i class="fas fa-map-marker-alt mr-1"></i><?= htmlspecialchars($sahiplenen['il_adi']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Sahiplenme Yorumu -->
                                    <?php if (!empty($sahiplenen['sahiplenen_yorumu'])): ?>
                                        <div class="bg-green-50 border border-green-200 rounded-lg p-3 mb-4">
                                            <div class="text-green-800 font-semibold text-sm mb-1">
                                                <i class="fas fa-quote-left mr-1"></i>Sahiplenme Yorumu:
                                            </div>
                                            <p class="text-green-700 text-sm italic"><?= htmlspecialchars($sahiplenen['sahiplenen_yorumu']) ?></p>
                                            <?php if ($sahiplenen['yorum_tarihi']): ?>
                                                <div class="text-green-600 text-xs mt-2">
                                                    <i class="fas fa-calendar mr-1"></i><?= date('d.m.Y', strtotime($sahiplenen['yorum_tarihi'])) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="flex justify-between items-center">
                                        <span class="text-xs text-gray-500">
                                            <i class="fas fa-calendar mr-1"></i>
                                            <?= date('d.m.Y', strtotime($sahiplenen['tarih'])) ?>
                                        </span>
                                        <a href="ilan_detay.php?id=<?= $sahiplenen['id'] ?>" 
                                           class="gradient-success text-white px-4 py-2 rounded-md text-sm font-semibold">
                                            Detay
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-span-full text-center py-16">
                            <div class="text-6xl mb-6">🏠</div>
                            <h3 class="text-2xl font-semibold text-gray-600 mb-4">Henüz Sahiplenen Hayvan Yok</h3>
                            <p class="text-gray-500">İlk mutlu sonu siz yaratın!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sahiplendirme Rehberi ve Bilgilendirme Bölümü -->
            <div class="mb-16">
                <div class="text-center mb-12">
                    <h2 class="text-4xl font-bold text-gray-800 mb-4">
                        <i class="fas fa-heart mr-3 text-primary"></i>
                        Neden <span class="text-primary">Sahiplenmeli</span>?
                    </h2>
                    <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                        Bir hayvanı sahiplenmek
                        İşte sahiplendirmenin faydaları ve bilmeniz gerekenler:
                    </p>
                </div>

                <!-- Sahiplendirme Faydaları -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-16">
                    <div class="bg-white rounded-xl shadow-lg p-6 card-hover text-center">
                        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-heart text-3xl text-red-500"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-3">Koşulsuz Sevgi</h3>
                        <p class="text-gray-600">
                            Hayvanlar size koşulsuz sevgi ve sadakat gösterir. Onların varlığı 
                            evinizi sıcak bir yuva haline getirir.
                        </p>
                    </div>

                    <div class="bg-white rounded-xl shadow-lg p-6 card-hover text-center">
                        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-user-friends text-3xl text-blue-500"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-3">Sosyal Yaşam</h3>
                        <p class="text-gray-600">
                            Hayvan sahipleri daha sosyal olur, parkta yürüyüş yapar ve 
                            diğer hayvan severlerle tanışma fırsatı bulur.
                        </p>
                    </div>

                    <div class="bg-white rounded-xl shadow-lg p-6 card-hover text-center">
                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-heartbeat text-3xl text-green-500"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-3">Sağlık Faydaları</h3>
                        <p class="text-gray-600">
                            Hayvanlarla vakit geçirmek stresi azaltır, kan basıncını düşürür 
                            ve genel sağlığı iyileştirir.
                        </p>
                    </div>

                    <div class="bg-white rounded-xl shadow-lg p-6 card-hover text-center">
                        <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-shield-alt text-3xl text-primary"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-3">Sorumluluk</h3>
                        <p class="text-gray-600">
                            Hayvan bakımı size düzenli yaşam alışkanlığı kazandırır ve 
                            sorumluluk bilincini geliştirir.
                        </p>
                    </div>

                    <div class="bg-white rounded-xl shadow-lg p-6 card-hover text-center">
                        <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-smile text-3xl text-yellow-500"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-3">Mutluluk</h3>
                        <p class="text-gray-600">
                            Evcil hayvanlar günlük yaşamınıza neşe katar, yalnızlık hissini 
                            azaltır ve size eğlenceli anlar yaşatır.
                        </p>
                    </div>

                    <div class="bg-white rounded-xl shadow-lg p-6 card-hover text-center">
                        <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-home text-3xl text-primary"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-3">Hayat Kurtarma</h3>
                        <p class="text-gray-600">
                            Sahiplenerek bir canın hayatını kurtarır ve ona güvenli, 
                            sevgi dolu bir yuva sağlarsınız.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Hayvan Bakım Rehberi -->
            <div class="mb-16">
                <div class="text-center mb-12">
                    <h2 class="text-4xl font-bold text-gray-800 mb-4">
                        <i class="fas fa-paw mr-3 text-primary"></i>
                        Hayvan <span class="text-primary">Bakım Rehberi</span>
                    </h2>
                    <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                        Evcil hayvanınızın sağlıklı ve mutlu olması için bilmeniz gereken temel bakım kuralları:
                    </p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Köpek Bakımı -->
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden card-hover">
                        <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-6 text-white">
                            <h3 class="text-2xl font-bold flex items-center">
                                <i class="fas fa-dog mr-3"></i>Köpek Bakımı
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <div class="flex items-start space-x-3">
                                    <i class="fas fa-utensils text-blue-500 mt-1"></i>
                                    <div>
                                        <h4 class="font-semibold text-gray-800">Beslenme</h4>
                                        <p class="text-gray-600 text-sm">Yaşına ve büyüklüğüne uygun kaliteli mama, günde 2-3 öğün</p>
                                    </div>
                                </div>
                                <div class="flex items-start space-x-3">
                                    <i class="fas fa-walking text-blue-500 mt-1"></i>
                                    <div>
                                        <h4 class="font-semibold text-gray-800">Egzersiz</h4>
                                        <p class="text-gray-600 text-sm">Günlük en az 30-60 dakika yürüyüş ve oyun</p>
                                    </div>
                                </div>
                                <div class="flex items-start space-x-3">
                                    <i class="fas fa-shower text-blue-500 mt-1"></i>
                                    <div>
                                        <h4 class="font-semibold text-gray-800">Temizlik</h4>
                                        <p class="text-gray-600 text-sm">Ayda 1-2 kez banyo, düzenli diş ve kulak temizliği</p>
                                    </div>
                                </div>
                                <div class="flex items-start space-x-3">
                                    <i class="fas fa-syringe text-blue-500 mt-1"></i>
                                    <div>
                                        <h4 class="font-semibold text-gray-800">Sağlık</h4>
                                        <p class="text-gray-600 text-sm">Yıllık aşılar, parazit koruması ve veteriner kontrolleri</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kedi Bakımı -->
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden card-hover">
                        <div class="bg-gradient-to-r from-primary to-primary-darker p-6 text-white">
                            <h3 class="text-2xl font-bold flex items-center">
                                <i class="fas fa-cat mr-3"></i>Kedi Bakımı
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <div class="flex items-start space-x-3">
                                    <i class="fas fa-utensils text-primary mt-1"></i>
                                    <div>
                                        <h4 class="font-semibold text-gray-800">Beslenme</h4>
                                        <p class="text-gray-600 text-sm">Yaşına uygun kedi maması, taze su her zaman erişilebilir</p>
                                    </div>
                                </div>
                                <div class="flex items-start space-x-3">
                                    <i class="fas fa-box text-primary mt-1"></i>
                                    <div>
                                        <h4 class="font-semibold text-gray-800">Kum Kabı</h4>
                                        <p class="text-gray-600 text-sm">Günlük temizlik, uygun kum seçimi</p>
                                    </div>
                                </div>
                                <div class="flex items-start space-x-3">
                                    <i class="fas fa-cut text-primary mt-1"></i>
                                    <div>
                                        <h4 class="font-semibold text-gray-800">Tırnak Bakımı</h4>
                                        <p class="text-gray-600 text-sm">Tırnak tahtası, düzenli tırnak kesimi</p>
                                    </div>
                                </div>
                                <div class="flex items-start space-x-3">
                                    <i class="fas fa-syringe text-primary mt-1"></i>
                                    <div>
                                        <h4 class="font-semibold text-gray-800">Sağlık</h4>
                                        <p class="text-gray-600 text-sm">Yıllık aşılar, kısırlaştırma, veteriner kontrolleri</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sahiplendirme Süreci -->
            <div class="mb-16">
                <div class="bg-white rounded-2xl shadow-lg p-8 border border-gray-200">
                    <div class="text-center mb-8">
                        <h2 class="text-4xl font-bold mb-4 text-gray-800">
                            <i class="fas fa-route mr-3 text-primary"></i>
                            Sahiplendirme Süreci
                        </h2>
                        <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                            Hayalinizdeki dostunuzu sahiplenmek için izlemeniz gereken adımlar:
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div class="text-center bg-blue-50 rounded-xl p-6 border border-blue-200 card-hover">
                            <div class="w-16 h-16 bg-blue-500 rounded-full flex items-center justify-center mx-auto mb-4">
                                <span class="text-2xl font-bold text-white">1</span>
                            </div>
                            <h3 class="text-lg font-semibold mb-2 text-blue-800">İlanı İnceleyin</h3>
                            <p class="text-sm text-blue-700">Beğendiğiniz hayvanın detaylarını okuyun, fotoğraflarını inceleyin</p>
                        </div>

                        <div class="text-center bg-yellow-50 rounded-xl p-6 border border-yellow-200 card-hover">
                            <div class="w-16 h-16 bg-yellow-500 rounded-full flex items-center justify-center mx-auto mb-4">
                                <span class="text-2xl font-bold text-white">2</span>
                            </div>
                            <h3 class="text-lg font-semibold mb-2 text-yellow-800">İletişime Geçin</h3>
                            <p class="text-sm text-yellow-700">İlan sahibi ile iletişime geçin, sorularınızı sorun</p>
                        </div>

                        <div class="text-center bg-green-50 rounded-xl p-6 border border-green-200 card-hover">
                            <div class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-4">
                                <span class="text-2xl font-bold text-white">3</span>
                            </div>
                            <h3 class="text-lg font-semibold mb-2 text-green-800">Hayvanla Tanışın</h3>
                            <p class="text-sm text-green-700">Hayvanı görmeye gidin, uyumunuzu test edin</p>
                        </div>

                        <div class="text-center bg-purple-50 rounded-xl p-6 border border-purple-200 card-hover">
                            <div class="w-16 h-16 bg-primary rounded-full flex items-center justify-center mx-auto mb-4">
                                <span class="text-2xl font-bold text-white">4</span>
                            </div>
                            <h3 class="text-lg font-semibold mb-2 text-primary">Yuvanıza Alın</h3>
                            <p class="text-sm text-purple-700">Gerekli hazırlıkları yapın ve yeni dostunuzu yuvanıza alın</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- İpuçları ve Uyarılar -->
            <div class="mb-16">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Önemli İpuçları -->
                    <div class="bg-green-50 rounded-xl p-6 border border-green-200">
                        <h3 class="text-2xl font-bold text-green-800 mb-4 flex items-center">
                            <i class="fas fa-lightbulb mr-3"></i>
                            Önemli İpuçları
                        </h3>
                        <ul class="space-y-3 text-green-700">
                            <li class="flex items-start space-x-2">
                                <i class="fas fa-check-circle mt-1 text-green-600"></i>
                                <span>Sahiplenmeden önce ailenizle konuşun ve herkesin onayını alın</span>
                            </li>
                            <li class="flex items-start space-x-2">
                                <i class="fas fa-check-circle mt-1 text-green-600"></i>
                                <span>Ekonomik durumunuzu değerlendirin, hayvan bakımı masraf gerektirir</span>
                            </li>
                            <li class="flex items-start space-x-2">
                                <i class="fas fa-check-circle mt-1 text-green-600"></i>
                                <span>Yaşam alanınızın hayvan bakımına uygun olduğundan emin olun</span>
                            </li>
                            <li class="flex items-start space-x-2">
                                <i class="fas fa-check-circle mt-1 text-green-600"></i>
                                <span>Yakınınızda güvenilir veteriner hekim bulundurun</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Dikkat Edilmesi Gerekenler -->
                    <div class="bg-orange-50 rounded-xl p-6 border border-orange-200">
                        <h3 class="text-2xl font-bold text-orange-800 mb-4 flex items-center">
                            <i class="fas fa-exclamation-triangle mr-3"></i>
                            Dikkat Edilmesi Gerekenler
                        </h3>
                        <ul class="space-y-3 text-orange-700">
                            <li class="flex items-start space-x-2">
                                <i class="fas fa-exclamation-circle mt-1 text-orange-600"></i>
                                <span>Sahiplendirme uzun vadeli bir taahhüttür, düşünerek karar verin</span>
                            </li>
                            <li class="flex items-start space-x-2">
                                <i class="fas fa-exclamation-circle mt-1 text-orange-600"></i>
                                <span>Hayvanın sağlık durumunu mutlaka kontrol ettirin</span>
                            </li>
                            <li class="flex items-start space-x-2">
                                <i class="fas fa-exclamation-circle mt-1 text-orange-600"></i>
                                <span>İlan sahibinden hayvanın geçmişi hakkında bilgi alın</span>
                            </li>
                            <li class="flex items-start space-x-2">
                                <i class="fas fa-exclamation-circle mt-1 text-orange-600"></i>
                                <span>Acil durumlar için veteriner hekim iletişim bilgisi bulundurun</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Acil Durum Bilgileri -->
            <div class="mb-16">
                <div class="bg-red-50 rounded-xl p-6 border border-red-200">
                    <div class="text-center mb-6">
                        <h3 class="text-2xl font-bold text-red-800 mb-2 flex items-center justify-center">
                            <i class="fas fa-phone-alt mr-3"></i>
                            Acil Durum Telefonları
                        </h3>
                        <p class="text-red-600">Hayvanınızın acil sağlık durumlarında arayabileceğiniz numaralar:</p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
                        <div class="bg-white rounded-lg p-4">
                            <i class="fas fa-hospital text-red-500 text-2xl mb-2"></i>
                            <h4 class="font-semibold text-gray-800">Veteriner Acil</h4>
                            <p class="text-red-600 font-bold">📞 444 8 VET</p>
                        </div>
                        
                        <div class="bg-white rounded-lg p-4">
                            <i class="fas fa-paw text-red-500 text-2xl mb-2"></i>
                            <h4 class="font-semibold text-gray-800">Hayvan Hakları</h4>
                            <p class="text-red-600 font-bold">📞 444 4 HAYTAP</p>
                        </div>
                        
                        <div class="bg-white rounded-lg p-4">
                            <i class="fas fa-shield-alt text-red-500 text-2xl mb-2"></i>
                            <h4 class="font-semibold text-gray-800">Hayvan Polisi</h4>
                            <p class="text-red-600 font-bold">📞 153</p>
                        </div>
                    </div>
                </div>
            </div>
        </div> <!-- Ana içerik alanı kapanışı -->
    </div> <!-- Flex container kapanışı -->
</main>

<!-- Footer -->
<footer class="bg-gray-800 text-white py-12 mt-16">
    <div class="max-w-7xl mx-auto px-6">
        <div class="text-center">
            <div class="text-3xl mb-4">🏠</div>
            <h3 class="text-2xl font-bold mb-4 text-primary-lighter">Sıcak Patizi</h3>
            <p class="text-gray-400">Onlar İçin Yuva, Senin İçin Dostluk</p>
        </div>
    </div>
</footer>

<!-- JavaScript -->
<script>
    const cinsler = <?= json_encode($cinsler) ?>;
    const ilceler = <?= json_encode($ilceler) ?>;

    // SweetAlert2 Yardımcı Fonksiyonlar
    function showSuccessToast(message) {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: message,
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            background: '#f0fdf4',
            color: '#15803d',
            iconColor: '#22c55e',
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });
    }

    function showErrorToast(message) {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'error',
            title: message,
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true,
            background: '#fef2f2',
            color: '#dc2626',
            iconColor: '#ef4444',
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });
    }

    function showLoginRequired() {
        Swal.fire({
            title: '🔐 Giriş Gerekli',
            text: 'Bu işlemi yapabilmek için giriş yapmanız gerekiyor.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-sign-in-alt mr-2"></i>Giriş Yap',
            cancelButtonText: '<i class="fas fa-times mr-2"></i>İptal',
            confirmButtonColor: '#a855f7',
            cancelButtonColor: '#6b7280',
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'giris.php';
            }
        });
    }

    document.addEventListener("DOMContentLoaded", function() {
        // Cins dinamik doldurma
        const kategoriSelect = document.getElementById('kategori');
        const cinsSelect = document.getElementById('cins');
        
        kategoriSelect.addEventListener('change', function() {
            const kategoriId = this.value;
            cinsSelect.innerHTML = '<option value="">Tüm Cinsler</option>';
            
            if (kategoriId && cinsler[kategoriId]) {
                cinsler[kategoriId].forEach(function(cins) {
                    const option = document.createElement('option');
                    option.value = cins.id;
                    option.textContent = cins.ad;
                    cinsSelect.appendChild(option);
                });
            }
        });

        // İlçe dinamik doldurma
        const ilSelect = document.getElementById('il');
        const ilceSelect = document.getElementById('ilce');
        
        ilSelect.addEventListener('change', function() {
            const ilId = this.value;
            ilceSelect.innerHTML = '<option value="">Tüm İlçeler</option>';
            
            if (ilId && ilceler[ilId]) {
                ilceler[ilId].forEach(function(ilce) {
                    const option = document.createElement('option');
                    option.value = ilce.id;
                    option.textContent = ilce.ad;
                    ilceSelect.appendChild(option);
                });
            }
        });

        // Favori kalp butonu
        document.querySelectorAll('.favorite-btn').forEach(button => {
            button.addEventListener('click', function() {
                const ilanId = this.dataset.ilanId;
                const currentButton = this;
                const isFavorited = currentButton.classList.contains('text-primary');

                // Kullanıcı giriş kontrolü
                <?php if (!$user_id): ?>
                    showLoginRequired();
                    return;
                <?php endif; ?>

                fetch('favori_islem.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `ilan_id=${ilanId}&action=${isFavorited ? 'remove' : 'add'}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        if (data.message.includes('eklendi')) {
                            currentButton.classList.remove('text-gray-400', 'hover:text-primary');
                            currentButton.classList.add('text-primary');
                            showSuccessToast('💖 Favorilere eklendi!');
                        } else {
                            currentButton.classList.remove('text-primary');
                            currentButton.classList.add('text-gray-400', 'hover:text-primary');
                            showSuccessToast('💔 Favorilerden kaldırıldı');
                        }
                    } else {
                        showErrorToast(data.message || 'Bir hata oluştu!');
                        if (data.redirect) {
                            setTimeout(() => {
                                window.location.href = data.redirect;
                            }, 2000);
                        }
                    }
                })
                .catch(error => {
                    console.error("AJAX error:", error);
                    showErrorToast('❌ Bağlantı hatası! Lütfen tekrar deneyin.');
                });
            });
        });

        // Sayfa yüklendiğinde mevcut filtrelere göre cins ve ilçe doldur
        <?php if (!empty($_GET['kategori_id']) && isset($cinsler[$_GET['kategori_id']])): ?>
            kategoriSelect.dispatchEvent(new Event('change'));
            <?php if (!empty($_GET['cins_id'])): ?>
                setTimeout(function() {
                    cinsSelect.value = '<?= (int)$_GET['cins_id'] ?>';
                }, 100);
            <?php endif; ?>
        <?php endif; ?>

        <?php if (!empty($_GET['il_id']) && isset($ilceler[$_GET['il_id']])): ?>
            ilSelect.dispatchEvent(new Event('change'));
            <?php if (!empty($_GET['ilce_id'])): ?>
                setTimeout(function() {
                    ilceSelect.value = '<?= (int)$_GET['ilce_id'] ?>';
                }, 100);
            <?php endif; ?>
        <?php endif; ?>
    });

    // Şehir filtreleme
    document.addEventListener('DOMContentLoaded', function() {
        const sehirFilter = document.getElementById('sehirFilter');
        const barinaklarListesi = document.getElementById('barinaklarListesi');
        
        if (sehirFilter) {
            sehirFilter.addEventListener('change', function() {
                const selectedSehir = this.value;
                const barinaklarItems = barinaklarListesi.querySelectorAll('.barinak-item');
                
                barinaklarItems.forEach(item => {
                    if (selectedSehir === '' || item.getAttribute('data-sehir') === selectedSehir) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        }
    });

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Lazy loading for images
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });

        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }

    // Card hover effects
    document.querySelectorAll('.card-hover').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)';
        });
    });

    // Loading animation for form submissions
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Yükleniyor...';
                submitBtn.disabled = true;
                
                // Re-enable after 5 seconds as fallback
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 5000);
            }
        });
    });

    // Search functionality for shelter list
    function createShelterSearch() {
        const searchInput = document.createElement('input');
        searchInput.type = 'text';
        searchInput.placeholder = 'Barınak ara...';
        searchInput.className = 'w-full px-3 py-2 mb-4 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-sm';
        
        const shelterList = document.getElementById('barinaklarListesi');
        const shelterSection = shelterList.parentElement;
        shelterSection.insertBefore(searchInput, shelterList);
        
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const shelterItems = shelterList.querySelectorAll('.barinak-item');
            
            shelterItems.forEach(item => {
                const shelterName = item.querySelector('h3').textContent.toLowerCase();
                const shelterLocation = item.querySelector('.text-xs').textContent.toLowerCase();
                
                if (shelterName.includes(searchTerm) || shelterLocation.includes(searchTerm)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }

    // Initialize shelter search if shelter list exists
    if (document.getElementById('barinaklarListesi')) {
        createShelterSearch();
    }

    // Statistics counter animation
    function animateCounters() {
        const counters = document.querySelectorAll('.text-3xl.font-bold');
        const speed = 200;

        counters.forEach(counter => {
            const updateCount = () => {
                const target = +counter.innerText;
                const count = +counter.getAttribute('data-count') || 0;
                const inc = target / speed;

                if (count < target) {
                    counter.setAttribute('data-count', Math.ceil(count + inc));
                    counter.innerText = Math.ceil(count + inc);
                    setTimeout(updateCount, 1);
                } else {
                    counter.innerText = target;
                }
            };

            // Only animate if element is visible
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting && !counter.hasAttribute('data-animated')) {
                        counter.setAttribute('data-animated', 'true');
                        counter.innerText = '0';
                        updateCount();
                    }
                });
            });

            observer.observe(counter);
        });
    }

    // Initialize counter animation
    animateCounters();

    // Navigation scroll effect
    window.addEventListener('scroll', function() {
        const header = document.querySelector('header');
        if (header) {
            if (window.scrollY > 100) {
                header.classList.add('bg-white', 'shadow-lg');
                header.classList.remove('bg-transparent');
            } else {
                header.classList.remove('bg-white', 'shadow-lg');
                header.classList.add('bg-transparent');
            }
        }
    });

    // Performance: Debounce scroll events
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Apply debounce to scroll events
    window.addEventListener('scroll', debounce(function() {
        // Any scroll-based functionality can go here
    }, 10));

    // Error handling for failed image loads
    document.querySelectorAll('img').forEach(img => {
        img.addEventListener('error', function() {
            if (!this.hasAttribute('data-error-handled')) {
                this.setAttribute('data-error-handled', 'true');
                this.src = 'https://via.placeholder.com/300x200?text=Resim+Yok&bg=f3f4f6&color=9ca3af';
                this.alt = 'Resim yüklenemedi';
            }
        });
    });

    // Auto-hide notifications after interaction
    document.querySelectorAll('.alert, .notification').forEach(notification => {
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateY(-20px)';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 5000);
    });

    // Keyboard navigation support
    document.addEventListener('keydown', function(e) {
        // Escape key closes modals
        if (e.key === 'Escape') {
            const activeModal = document.querySelector('.modal.active');
            if (activeModal) {
                activeModal.classList.remove('active');
            }
        }
        
        // Enter key on favorite buttons
        if (e.key === 'Enter' && e.target.classList.contains('favorite-btn')) {
            e.target.click();
        }
    });

    // Global fonksiyonlar - diğer sayfalarda da kullanabilmek için
    window.showSuccessToast = showSuccessToast;
    window.showErrorToast = showErrorToast;
    window.showLoginRequired = showLoginRequired;

    // Performance monitoring (development only)
    <?php if (isset($_GET['debug']) || (defined('ENVIRONMENT') && ENVIRONMENT === 'development')): ?>
    window.addEventListener('load', function() {
        if (window.performance) {
            const loadTime = window.performance.timing.loadEventEnd - window.performance.timing.navigationStart;
            console.log('Page load time:', loadTime + 'ms');
            
            if (loadTime > 3000) {
                console.warn('Sayfa yavaş yüklendi. Optimizasyon gerekli olabilir.');
            }
        }
    });
    <?php endif; ?>

    // Initialize tooltips for accessibility
    document.querySelectorAll('[title]').forEach(element => {
        element.addEventListener('mouseenter', function() {
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip bg-gray-800 text-white px-2 py-1 rounded text-sm absolute z-50';
            tooltip.textContent = this.title;
            tooltip.style.top = (this.offsetTop - 30) + 'px';
            tooltip.style.left = this.offsetLeft + 'px';
            document.body.appendChild(tooltip);
            
            this.addEventListener('mouseleave', function() {
                if (tooltip.parentNode) {
                    tooltip.parentNode.removeChild(tooltip);
                }
            }, { once: true });
        });
    });

    // Service Worker registration for PWA support (if available)
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
            navigator.serviceWorker.register('/sw.js')
                .then(function(registration) {
                    console.log('SW registered: ', registration);
                })
                .catch(function(registrationError) {
                    console.log('SW registration failed: ', registrationError);
                });
        });
    }

    // Back to top button
    const backToTopButton = document.createElement('button');
    backToTopButton.innerHTML = '<i class="fas fa-arrow-up"></i>';
    backToTopButton.className = 'fixed bottom-6 right-6 bg-primary text-white p-3 rounded-full shadow-lg hover:bg-primary-darker transition-all duration-300 z-50 opacity-0 invisible';
    backToTopButton.addEventListener('click', function() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
    document.body.appendChild(backToTopButton);

    // Show/hide back to top button
    window.addEventListener('scroll', debounce(function() {
        if (window.scrollY > 300) {
            backToTopButton.classList.remove('opacity-0', 'invisible');
            backToTopButton.classList.add('opacity-100', 'visible');
        } else {
            backToTopButton.classList.add('opacity-0', 'invisible');
            backToTopButton.classList.remove('opacity-100', 'visible');
        }
    }, 100));

    // Initialize all features when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🏠 Yuva Ol - Ana sayfa yüklendi');
        
        // Track page view for analytics (if implemented)
        if (typeof gtag !== 'undefined') {
            gtag('event', 'page_view', {
                page_title: 'Ana Sayfa',
                page_location: window.location.href
            });
        }
    });

</script>

<!-- Include SweetAlert2 for better notifications -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Include any additional libraries -->
<script>
    // Additional utility functions
    window.utils = {
        formatDate: function(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('tr-TR', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        },
        
        formatPhone: function(phone) {
            // Format Turkish phone numbers
            return phone.replace(/(\d{3})(\d{3})(\d{2})(\d{2})/, '$1 $2 $3 $4');
        },
        
        truncateText: function(text, length = 100) {
            if (text.length <= length) return text;
            return text.substr(0, length) + '...';
        },
        
        showConfirmDialog: function(title, text, confirmCallback) {
            Swal.fire({
                title: title,
                text: text,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Evet',
                cancelButtonText: 'Hayır',
                confirmButtonColor: '#a855f7',
                cancelButtonColor: '#6b7280',
            }).then((result) => {
                if (result.isConfirmed && typeof confirmCallback === 'function') {
                    confirmCallback();
                }
            });
        }
    };
</script>

<!-- Close body and html tags -->
</body>
</html>