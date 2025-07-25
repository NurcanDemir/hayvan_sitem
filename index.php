<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include("includes/db.php");

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

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hayvan Dostları - Ana Sayfa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    
    <style>
        :root {
            --primary: #ba3689;
            --primary-light: #d95bb0;
            --primary-lighter: #e581c7;
            --primary-lightest: #f0b1df;
        }

        body {
            background: linear-gradient(135deg, var(--primary-lightest) 0%, #fdf2f8 30%, #f9fafb 70%, var(--primary-lightest) 100%);
            min-height: 100vh;
        }

        .bg-primary { background-color: var(--primary); }
        .bg-primary-light { background-color: var(--primary-light); }
        .bg-primary-lighter { background-color: var(--primary-lighter); }
        .bg-primary-lightest { background-color: var(--primary-lightest); }
        
        .text-primary { color: var(--primary); }
        .text-primary-light { color: var(--primary-light); }
        .text-primary-lighter { color: var(--primary-lighter); }
        
        .border-primary { border-color: var(--primary); }
        .border-primary-light { border-color: var(--primary-light); }
        
        .hover\:bg-primary:hover { background-color: var(--primary); }
        .hover\:bg-primary-light:hover { background-color: var(--primary-light); }
        .hover\:text-primary:hover { color: var(--primary); }
        
        .focus\:ring-primary:focus { --tw-ring-color: var(--primary); }
        .focus\:border-primary:focus { border-color: var(--primary); }

        .btn-gradient {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            transition: all 0.3s ease;
        }
        
        .btn-gradient:hover {
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
            transform: translateY(-1px);
            box-shadow: 0 10px 25px rgba(186, 54, 137, 0.3);
        }

        .card-hover {
            transition: all 0.3s ease;
        }
        
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(186, 54, 137, 0.15);
        }

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
</head>
<body>
    <!-- Header -->
    <header class="bg-white shadow-lg sticky top-0 z-50">
        <nav class="max-w-7xl mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <!-- Logo ve Site Adı -->
                    <div class="flex items-center space-x-3">
                        <div class="text-3xl">🏠</div>
                        <div class="flex flex-col">
                            <h1 class="text-2xl font-bold text-primary">
                                <a href="index.php">Yuva Ol</a>
                            </h1>
                            <span class="text-xs text-gray-500 -mt-1">Onlar İçin Yuva, Senin İçin Dostluk.</span>
                        </div>
                    </div>
                </div>
                <div class="hidden md:flex items-center space-x-8">
                    <a href="index.php" class="text-primary font-semibold">Ana Sayfa</a>
                    <a href="etkinlikler.php" class="text-stone-600 hover:text-primary font-semibold transition duration-300">Etkinlikler</a>
                    <a href="ilanlar.php" class="text-stone-600 hover:text-primary font-semibold transition duration-300">İlanlar</a>
                    
                    <?php if (isset($_SESSION['kullanici_id'])): ?>
                        <a href="ilan_ekle.php" class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-md font-semibold transition duration-300">
                            <i class="fas fa-plus mr-2"></i>İlan Ver
                        </a>
                        <a href="ilanlarim.php" class="text-stone-600 hover:text-primary font-semibold transition duration-300">İlanlarım</a>
                        <a href="favorilerim.php" class="text-stone-600 hover:text-primary font-semibold transition duration-300">Favorilerim</a>
                        <a href="taleplerim.php" class="text-stone-600 hover:text-primary font-semibold transition duration-300">Taleplerim</a>
                        <a href="gelen_talepler.php" class="text-stone-600 hover:text-primary font-semibold transition duration-300">Gelen Talepler</a>
                        <span class="text-stone-600">Hoş geldin, <?= htmlspecialchars($_SESSION['kullanici_adi']) ?>!</span>
                        <a href="cikis.php" class="bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-md font-semibold transition duration-300">
                            <i class="fas fa-sign-out-alt mr-2"></i>Çıkış
                        </a>
                    <?php else: ?>
                        <a href="giris.php" class="btn-gradient text-white px-4 py-2 rounded-md transition duration-300">
                            <i class="fas fa-sign-in-alt mr-2"></i>Giriş Yap
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>

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
                        Yuva Ol
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
                        <div class="text-3xl font-bold text-purple-600 mb-2"><?= $toplam_etkinlik ?></div>
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
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-primary-lightest text-primary">
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
                            Bir hayvanı sahiplenmek, hem onun hem de sizin hayatınızı değiştirecek güzel bir deneyim. 
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
                                <i class="fas fa-shield-alt text-3xl text-purple-500"></i>
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
                            <div class="w-16 h-16 bg-primary-lightest rounded-full flex items-center justify-center mx-auto mb-4">
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
                            <div class="bg-gradient-to-r from-purple-500 to-purple-600 p-6 text-white">
                                <h3 class="text-2xl font-bold flex items-center">
                                    <i class="fas fa-cat mr-3"></i>Kedi Bakımı
                                </h3>
                            </div>
                            <div class="p-6">
                                <div class="space-y-4">
                                    <div class="flex items-start space-x-3">
                                        <i class="fas fa-utensils text-purple-500 mt-1"></i>
                                        <div>
                                            <h4 class="font-semibold text-gray-800">Beslenme</h4>
                                            <p class="text-gray-600 text-sm">Yaşına uygun kedi maması, taze su her zaman erişilebilir</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start space-x-3">
                                        <i class="fas fa-box text-purple-500 mt-1"></i>
                                        <div>
                                            <h4 class="font-semibold text-gray-800">Kum Kabı</h4>
                                            <p class="text-gray-600 text-sm">Günlük temizlik, uygun kum seçimi</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start space-x-3">
                                        <i class="fas fa-cut text-purple-500 mt-1"></i>
                                        <div>
                                            <h4 class="font-semibold text-gray-800">Tırnak Bakımı</h4>
                                            <p class="text-gray-600 text-sm">Tırnak tahtası, düzenli tırnak kesimi</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start space-x-3">
                                        <i class="fas fa-syringe text-purple-500 mt-1"></i>
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
                                <div class="w-16 h-16 bg-purple-500 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <span class="text-2xl font-bold text-white">4</span>
                                </div>
                                <h3 class="text-lg font-semibold mb-2 text-purple-800">Yuvanıza Alın</h3>
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
                <h3 class="text-2xl font-bold mb-4 text-primary-lighter">Yuva Ol</h3>
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

        function showInfoToast(message) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'info',
                title: message,
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                background: '#eff6ff',
                color: '#1d4ed8',
                iconColor: '#3b82f6',
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });
        }

        function showWarningToast(message) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'warning',
                title: message,
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true,
                background: '#fffbeb',
                color: '#d97706',
                iconColor: '#f59e0b',
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
                confirmButtonColor: '#ba3689',
                cancelButtonColor: '#6b7280',
                backdrop: `
                    rgba(0,0,0,0.4)
                    url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 100 100'%3E%3Ctext y='.9em' font-size='90'%3E🐾%3C/text%3E%3C/svg%3E")
                    left top
                    no-repeat
                `,
                customClass: {
                    popup: 'animate__animated animate__fadeInDown'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'giris.php';
                }
            });
        }

        function showLoading(message = 'İşlem yapılıyor...') {
            Swal.fire({
                title: message,
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }

        function closeLoading() {
            Swal.close();
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

            // Favori kalp butonu - SweetAlert2 ile güncellenmiş
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

                    // Loading göster
                    showLoading('Favori işlemi yapılıyor...');

                    fetch('favori_islem.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `ilan_id=${ilanId}&action=${isFavorited ? 'remove' : 'add'}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        closeLoading();
                        
                        if (data.status === 'success') {
                            if (data.message.includes('eklendi')) {
                                currentButton.classList.remove('text-gray-400', 'hover:text-primary');
                                currentButton.classList.add('text-primary');
                                showSuccessToast('💖 Favorilere eklendi!');
                            } else {
                                currentButton.classList.remove('text-primary');
                                currentButton.classList.add('text-gray-400', 'hover:text-primary');
                                showInfoToast('💔 Favorilerden kaldırıldı');
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
                        closeLoading();
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

            // Örnek bildirimler (sayfa yüklendiğinde test için - kaldırabilirsiniz)
            // showSuccessToast('Hoş geldiniz! 🐾');
        });

        // Şehir filtreleme için ekleme
        document.addEventListener('DOMContentLoaded', function() {
            // Şehir filtreleme
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

        // Global fonksiyonlar - diğer sayfalarda da kullanabilmek için
        window.showSuccessToast = showSuccessToast;
        window.showErrorToast = showErrorToast;
        window.showInfoToast = showInfoToast;
        window.showWarningToast = showWarningToast;
        window.showLoginRequired = showLoginRequired;
        window.showLoading = showLoading;
        window.closeLoading = closeLoading;
    </script>
</body>
</html>

<?php
if (isset($stmt)) $stmt->close();
if (isset($sahiplenen_stmt)) $sahiplenen_stmt->close();
$conn->close();
?>