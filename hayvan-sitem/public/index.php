<?php
// filepath: c:\xampp\htdocs\hayvan-sitem\public\index.php

// PHP kod kısmı - en üstte
session_start();
include("../includes/db.php");

// Arama formundan gelen değerler
$pet_keyword = $_GET['pet_keyword'] ?? '';
$city_keyword = $_GET['city_keyword'] ?? '';

// --- Aktif İlanlar Sorgusu ---
$sql_aktif_ilanlar = "
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
    WHERE i.durum = 'Aktif'
";

$params_aktif = [];
$types_aktif = "";

if (!empty($pet_keyword)) {
    $sql_aktif_ilanlar .= " AND (i.baslik LIKE ? OR i.aciklama LIKE ? OR c.ad LIKE ? OR k.ad LIKE ?) ";
    $pet_search_term = '%' . $pet_keyword . '%';
    $params_aktif[] = $pet_search_term;
    $params_aktif[] = $pet_search_term;
    $params_aktif[] = $pet_search_term;
    $params_aktif[] = $pet_search_term;
    $types_aktif .= "ssss";
}

if (!empty($city_keyword)) {
    $sql_aktif_ilanlar .= " AND (il.ad LIKE ? OR ilce.ad LIKE ?) ";
    $city_search_term = '%' . $city_keyword . '%';
    $params_aktif[] = $city_search_term;
    $params_aktif[] = $city_search_term;
    $types_aktif .= "ss";
}

$sql_aktif_ilanlar .= " ORDER BY i.tarih DESC LIMIT 20";

// Sorguyu hazırla ve çalıştır
$stmt_aktif_ilanlar = $conn->prepare($sql_aktif_ilanlar);
if (!empty($params_aktif)) {
    $stmt_aktif_ilanlar->bind_param($types_aktif, ...$params_aktif);
}
$stmt_aktif_ilanlar->execute();
$result_aktif_ilanlar = $stmt_aktif_ilanlar->get_result();

// --- Sahiplenenler Sorgusu ---
$sql_sahiplenenler = "
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
    WHERE i.durum = 'sahiplenildi'
    ORDER BY i.tarih DESC
    LIMIT 20
";
$result_sahiplenenler = $conn->query($sql_sahiplenenler);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yuva Ol - Hayvan Dostları Platformu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
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
        .hover\:bg-primary:hover { background-color: var(--primary); }
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

        .gradient-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            transition: all 0.3s ease;
        }

        .gradient-success:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            transform: translateY(-1px);
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3);
        }

        .card-hover {
            transition: all 0.3s ease;
        }
        
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(186, 54, 137, 0.15);
        }

        .nav-link {
            transition: all 0.3s ease;
            font-weight: 600;
            padding: 8px 0;
        }

        .nav-link:hover {
            color: var(--primary) !important;
        }

        .nav-link.active {
            color: var(--primary) !important;
        }

        /* Logo Text Gradient */
        .logo-text {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Mobile Menu Styles */
        .mobile-menu {
            transition: all 0.3s ease;
            transform: translateY(-10px);
            opacity: 0;
        }

        .mobile-menu.show {
            transform: translateY(0);
            opacity: 1;
        }

        .text-primary { color: #ba3689; }
        .bg-primary { background-color: #ba3689; }
        .border-primary { border-color: #ba3689; }
        .hover\:bg-primary:hover { background-color: #ba3689; }
        .hover\:text-primary:hover { color: #ba3689; }
        
        /* Tüm CSS stilleriniz burada kalacak */
    </style>
</head>
<body class="bg-gray-50">

    <!-- HEADER INCLUDE - SADECE BİR KEZ! -->
    <?php include("../includes/header.php"); ?>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8 mt-16">
        <!-- Hero Section -->
        <div class="bg-gradient-to-r from-pink-500 to-purple-600 text-white rounded-xl p-8 mb-8 text-center">
            <h1 class="text-4xl font-bold mb-4">🐾 Yuva Ol - Hayvan Sahiplendirme</h1>
            <p class="text-xl mb-6">Sevgi dolu dostlarımız yeni yuva arıyor. Onlara sevgi dolu bir yuva sağlayarak hem onların hem de kendi hayatınızı değiştirin.</p>
            <a href="ilanlar.php" class="bg-white text-pink-600 font-bold py-3 px-8 rounded-full hover:bg-gray-100 transition duration-300 inline-block">
                <i class="fas fa-heart mr-2"></i>Sahiplendirme İlanlarını Gör
            </a>
        </div>

        <!-- Search Section -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <h3 class="text-2xl font-bold text-gray-800 mb-4 text-center">
                <i class="fas fa-search mr-2 text-pink-600"></i>Hızlı Arama
            </h3>
            <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <input type="text" 
                       name="pet_keyword" 
                       placeholder="Hayvan türü, cinsi..."
                       value="<?= htmlspecialchars($pet_keyword) ?>"
                       class="border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-pink-500">
                
                <input type="text" 
                       name="city_keyword" 
                       placeholder="Şehir, ilçe..."
                       value="<?= htmlspecialchars($city_keyword) ?>"
                       class="border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-pink-500">
                
                <button type="submit" 
                        class="bg-pink-600 hover:bg-pink-700 text-white font-bold py-3 px-6 rounded-lg transition duration-300">
                    <i class="fas fa-search mr-2"></i>Ara
                </button>
            </form>
        </div>

        <!-- İlanlar Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Aktif İlanlar -->
            <section class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                    <i class="fas fa-star mr-3 text-yellow-500"></i>Aktif İlanlar
                </h3>
                
                <?php if ($result_aktif_ilanlar && $result_aktif_ilanlar->num_rows > 0): ?>
                    <div class="space-y-4">
                        <?php while ($ilan = $result_aktif_ilanlar->fetch_assoc()): ?>
                            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition duration-300 card-hover">
                                <h4 class="font-bold text-lg text-gray-800 mb-2">
                                    <?= htmlspecialchars($ilan['baslik']) ?>
                                </h4>
                                <p class="text-gray-600 mb-3">
                                    <?= htmlspecialchars(substr($ilan['aciklama'], 0, 100)) ?>...
                                </p>
                                <div class="flex flex-wrap gap-2 mb-3">
                                    <?php if (!empty($ilan['kategori_ad'])): ?>
                                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-sm">
                                            <?= htmlspecialchars($ilan['kategori_ad']) ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($ilan['cins_ad'])): ?>
                                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-sm">
                                            <?= htmlspecialchars($ilan['cins_ad']) ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($ilan['il_ad'])): ?>
                                        <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded-full text-sm">
                                            <i class="fas fa-map-marker-alt mr-1"></i><?= htmlspecialchars($ilan['il_ad']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <a href="ilan_detay.php?id=<?= $ilan['id'] ?>" 
                                   class="bg-pink-600 hover:bg-pink-700 text-white px-4 py-2 rounded-md text-sm font-semibold transition duration-300 inline-block">
                                    Detayları Gör
                                </a>
                            </div>
                        <?php endwhile; ?>
                    </div>
                    
                    <div class="text-center mt-6">
                        <a href="ilanlar.php" class="text-pink-600 hover:text-pink-800 font-semibold">
                            Tüm Aktif İlanları Gör →
                        </a>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8">
                        <i class="fas fa-paw text-6xl text-gray-300 mb-4"></i>
                        <p class="text-gray-500 text-lg">Henüz aktif ilan bulunmamaktadır.</p>
                    </div>
                <?php endif; ?>
            </section>

            <!-- Sahiplenenler -->
            <section class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                    <i class="fas fa-heart mr-3 text-red-500"></i>Mutlu Son Hikayeleri
                </h3>
                
                <?php if ($result_sahiplenenler && $result_sahiplenenler->num_rows > 0): ?>
                    <div class="space-y-4">
                        <?php while ($ilan = $result_sahiplenenler->fetch_assoc()): ?>
                            <div class="border border-green-200 bg-green-50 rounded-lg p-4">
                                <h4 class="font-bold text-lg text-gray-800 mb-2">
                                    ✅ <?= htmlspecialchars($ilan['baslik']) ?>
                                </h4>
                                <p class="text-gray-600 mb-3">
                                    <?= htmlspecialchars(substr($ilan['aciklama'], 0, 100)) ?>...
                                </p>
                                <div class="flex flex-wrap gap-2">
                                    <?php if (!empty($ilan['kategori_ad'])): ?>
                                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-sm">
                                            <?= htmlspecialchars($ilan['kategori_ad']) ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($ilan['il_ad'])): ?>
                                        <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded-full text-sm">
                                            <i class="fas fa-map-marker-alt mr-1"></i><?= htmlspecialchars($ilan['il_ad']) ?>
                                        </span>
                                    <?php endif; ?>
                                    <span class="bg-green-500 text-white px-2 py-1 rounded-full text-sm font-semibold">
                                        <i class="fas fa-home mr-1"></i>Sahiplendi
                                    </span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8">
                        <i class="fas fa-heart text-6xl text-gray-300 mb-4"></i>
                        <p class="text-gray-500 text-lg">Henüz sahiplendirme başarı hikayesi bulunmamaktadır.</p>
                    </div>
                <?php endif; ?>
            </section>
        </div>

        <!-- CTA Section -->
        <div class="bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded-xl p-8 mt-12 text-center">
            <h2 class="text-3xl font-bold mb-4">Sen de Bu Güzel Hikayenin Parçası Ol!</h2>
            <p class="text-xl mb-6">Hayvan sahiplendirme konusunda topluma katkı sağla, sevgi dolu dostlar edin.</p>
            <div class="space-x-4">
                <a href="kayit.php" class="bg-white text-purple-600 font-bold py-3 px-8 rounded-full hover:bg-gray-100 transition duration-300 inline-block">
                    <i class="fas fa-user-plus mr-2"></i>Üye Ol
                </a>
                <a href="ilan_ekle.php" class="border-2 border-white text-white font-bold py-3 px-8 rounded-full hover:bg-white hover:text-purple-600 transition duration-300 inline-block">
                    <i class="fas fa-plus mr-2"></i>İlan Ver
                </a>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <?php include("../includes/footer.php"); ?>

    <!-- JavaScript'ler en sonda -->
    <script>
        // Sayfa özel script'leri buraya
        console.log('Index sayfası yüklendi');
    </script>

</body>
</html>

<?php
// Database bağlantılarını kapat
if (isset($stmt_aktif_ilanlar)) $stmt_aktif_ilanlar->close();
$conn->close();
?>