<?php
// header.php
// Oturum henüz başlatılmadıysa başlat
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Aktif sayfa kontrolü
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title : 'Yuva Ol - Hayvan Dostları Platformu' ?></title>
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
        .hover\:text-primary:hover { color: var(--primary); }
        .border-primary { border-color: var(--primary); }
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

        .nav-link {
            transition: all 0.3s ease;
        }

        .nav-link.active {
            color: var(--primary);
            font-weight: 600;
        }

        .nav-link:hover {
            color: var(--primary);
        }

        .logo-text {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 2px 4px rgba(186, 54, 137, 0.2);
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
                            <h1 class="text-2xl font-bold logo-text">
                                <a href="index.php" class="hover:opacity-80 transition-opacity">Yuva Ol</a>
                            </h1>
                            <span class="text-xs text-gray-500 -mt-1">Onlar İçin Yuva, Senin İçin Dostluk.</span>
                        </div>
                    </div>
                </div>
                
                <!-- Desktop Menu -->
                <div class="hidden lg:flex items-center space-x-6">
                    <!-- Ana Navigasyon -->
                    <a href="index.php" class="nav-link <?= ($current_page == 'index.php') ? 'active' : 'text-stone-600' ?>">
                        <i class="fas fa-home mr-1"></i>Ana Sayfa
                    </a>
                    <a href="barinaklar.php" class="nav-link <?= ($current_page == 'barinaklar.php') ? 'active' : 'text-stone-600' ?>">
                        <i class="fas fa-building mr-1"></i>Barınaklar
                    </a>
                    <a href="etkinlikler.php" class="nav-link <?= ($current_page == 'etkinlikler.php') ? 'active' : 'text-stone-600' ?>">
                        <i class="fas fa-calendar-alt mr-1"></i>Etkinlikler
                    </a>
                    <a href="ilanlar.php" class="nav-link <?= ($current_page == 'ilanlar.php') ? 'active' : 'text-stone-600' ?>">
                        <i class="fas fa-list mr-1"></i>İlanlar
                    </a>
                    
                    <?php if (isset($_SESSION['kullanici_id'])): ?>
                        <!-- İlan Ver Butonu -->
                        <a href="ilan_ekle.php" class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-md font-semibold transition duration-300">
                            <i class="fas fa-plus mr-2"></i>İlan Ver
                        </a>
                        
                        <!-- Kullanıcı Menüleri -->
                        <a href="ilanlarim.php" class="nav-link <?= ($current_page == 'ilanlarim.php') ? 'active' : 'text-stone-600' ?>">
                            <i class="fas fa-clipboard-list mr-1"></i>İlanlarım
                        </a>
                        <a href="favorilerim.php" class="nav-link <?= ($current_page == 'favorilerim.php') ? 'active' : 'text-stone-600' ?>">
                            <i class="fas fa-heart mr-1"></i>Favorilerim
                        </a>
                        <a href="taleplerim.php" class="nav-link <?= ($current_page == 'taleplerim.php') ? 'active' : 'text-stone-600' ?>">
                            <i class="fas fa-paper-plane mr-1"></i>Taleplerim
                        </a>
                        <a href="gelen_talepler.php" class="nav-link <?= ($current_page == 'gelen_talepler.php') ? 'active' : 'text-stone-600' ?>">
                            <i class="fas fa-inbox mr-1"></i>Gelen Talepler
                            <?php
                            // Okunmamış talep sayısını güvenli şekilde göster
                            $unread_count = 0;
                            try {
                                if (file_exists("includes/db.php")) {
                                    include_once("includes/db.php");
                                } elseif (file_exists("db.php")) {
                                    include_once("db.php");
                                }
                                
                                if (isset($conn) && $conn) {
                                    $unread_result = $conn->query("SELECT COUNT(*) as count FROM sahiplenme_istekleri WHERE ilan_sahibi_kullanici_id = {$_SESSION['kullanici_id']} AND durum = 'Yeni'");
                                    if ($unread_result) {
                                        $unread_count = $unread_result->fetch_assoc()['count'] ?? 0;
                                    }
                                }
                            } catch (Exception $e) {
                                $unread_count = 0;
                            }
                            
                            if ($unread_count > 0): ?>
                                <span class="bg-red-500 text-white text-xs rounded-full px-2 py-1 ml-1"><?= $unread_count ?></span>
                            <?php endif; ?>
                        </a>
                        
                        <!-- Kullanıcı Bilgisi -->
                        <div class="flex items-center space-x-3">
                            <span class="text-stone-600 font-medium">
                                <i class="fas fa-user mr-1"></i>
                                Hoş geldin, <?= htmlspecialchars($_SESSION['kullanici_adi']) ?>!
                            </span>
                            <a href="cikis.php" class="bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-md font-semibold transition duration-300">
                                <i class="fas fa-sign-out-alt mr-2"></i>Çıkış
                            </a>
                        </div>
                    <?php else: ?>
                        <a href="giris.php" class="btn-gradient text-white px-4 py-2 rounded-md font-semibold transition duration-300">
                            <i class="fas fa-sign-in-alt mr-2"></i>Giriş Yap
                        </a>
                        <a href="kayit.php" class="border border-primary text-primary hover:bg-primary hover:text-white px-4 py-2 rounded-md font-semibold transition duration-300">
                            <i class="fas fa-user-plus mr-2"></i>Kayıt Ol
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Mobile Menu Button -->
                <div class="lg:hidden">
                    <button id="mobile-menu-btn" class="text-gray-600 hover:text-primary focus:outline-none">
                        <i class="fas fa-bars text-2xl"></i>
                    </button>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div id="mobile-menu" class="hidden lg:hidden mt-4 border-t pt-4">
                <div class="flex flex-col space-y-3">
                    <a href="index.php" class="nav-link <?= ($current_page == 'index.php') ? 'active' : 'text-stone-600' ?> py-2">
                        <i class="fas fa-home mr-2"></i>Ana Sayfa
                    </a>
                    <a href="barinaklar.php" class="nav-link <?= ($current_page == 'barinaklar.php') ? 'active' : 'text-stone-600' ?> py-2">
                        <i class="fas fa-building mr-2"></i>Barınaklar
                    </a>
                    <a href="etkinlikler.php" class="nav-link <?= ($current_page == 'etkinlikler.php') ? 'active' : 'text-stone-600' ?> py-2">
                        <i class="fas fa-calendar-alt mr-2"></i>Etkinlikler
                    </a>
                    <a href="ilanlar.php" class="nav-link <?= ($current_page == 'ilanlar.php') ? 'active' : 'text-stone-600' ?> py-2">
                        <i class="fas fa-list mr-2"></i>İlanlar
                    </a>
                    
                    <?php if (isset($_SESSION['kullanici_id'])): ?>
                        <a href="ilan_ekle.php" class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-md font-semibold transition duration-300 text-center">
                            <i class="fas fa-plus mr-2"></i>İlan Ver
                        </a>
                        <a href="ilanlarim.php" class="nav-link <?= ($current_page == 'ilanlarim.php') ? 'active' : 'text-stone-600' ?> py-2">
                            <i class="fas fa-clipboard-list mr-2"></i>İlanlarım
                        </a>
                        <a href="favorilerim.php" class="nav-link <?= ($current_page == 'favorilerim.php') ? 'active' : 'text-stone-600' ?> py-2">
                            <i class="fas fa-heart mr-2"></i>Favorilerim
                        </a>
                        <a href="taleplerim.php" class="nav-link <?= ($current_page == 'taleplerim.php') ? 'active' : 'text-stone-600' ?> py-2">
                            <i class="fas fa-paper-plane mr-2"></i>Taleplerim
                        </a>
                        <a href="gelen_talepler.php" class="nav-link <?= ($current_page == 'gelen_talepler.php') ? 'active' : 'text-stone-600' ?> py-2">
                            <i class="fas fa-inbox mr-2"></i>Gelen Talepler
                            <?php if ($unread_count > 0): ?>
                                <span class="bg-red-500 text-white text-xs rounded-full px-2 py-1 ml-1"><?= $unread_count ?></span>
                            <?php endif; ?>
                        </a>
                        
                        <div class="border-t pt-3 mt-3">
                            <span class="text-stone-600 font-medium block py-2">
                                <i class="fas fa-user mr-2"></i>
                                Hoş geldin, <?= htmlspecialchars($_SESSION['kullanici_adi']) ?>!
                            </span>
                            <a href="cikis.php" class="bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-md font-semibold transition duration-300 block text-center">
                                <i class="fas fa-sign-out-alt mr-2"></i>Çıkış
                            </a>
                        </div>
                    <?php else: ?>
                        <a href="giris.php" class="btn-gradient text-white px-4 py-2 rounded-md font-semibold transition duration-300 text-center">
                            <i class="fas fa-sign-in-alt mr-2"></i>Giriş Yap
                        </a>
                        <a href="kayit.php" class="border border-primary text-primary hover:bg-primary hover:text-white px-4 py-2 rounded-md font-semibold transition duration-300 text-center">
                            <i class="fas fa-user-plus mr-2"></i>Kayıt Ol
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>

    <script>
        // Mobile menu toggle
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuBtn = document.getElementById('mobile-menu-btn');
            const mobileMenu = document.getElementById('mobile-menu');
            
            if (mobileMenuBtn && mobileMenu) {
                mobileMenuBtn.addEventListener('click', function() {
                    mobileMenu.classList.toggle('hidden');
                });
            }
        });
    </script>

    <?php
    // filepath: c:\xampp\htdocs\hayvan_sitem\includes\layout_with_sidebar.php
    // Layout wrapper for index page with sidebar

    // Barınakları getir
    $barınak_sql = "SELECT DISTINCT b.*, s.ad as sehir_adi 
                    FROM barinaklar b 
                    LEFT JOIN sehirler s ON b.sehir_id = s.id 
                    ORDER BY s.ad ASC LIMIT 10";
    $barinaklar_result = $conn->query($barınak_sql);
    ?>

    <div class="max-w-7xl mx-auto px-6 py-8">
        <div class="flex gap-8">
            <!-- Sol Sidebar - Barınaklar -->
            <aside class="w-80 bg-white rounded-xl shadow-lg p-6 h-fit sticky top-24">
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
                        $sehir_sql = "SELECT DISTINCT s.* FROM sehirler s 
                                      INNER JOIN barinaklar b ON s.id = b.sehir_id 
                                      ORDER BY s.ad ASC";
                        $sehirler_result = $conn->query($sehir_sql);
                        while ($sehir = $sehirler_result->fetch_assoc()): ?>
                            <option value="<?= $sehir['id'] ?>"><?= htmlspecialchars($sehir['ad']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Barınaklar Listesi -->
                <div id="barinaklarListesi" class="space-y-3 max-h-96 overflow-y-auto">
                    <?php if ($barinaklar_result && $barinaklar_result->num_rows > 0): ?>
                        <?php while ($barinak = $barinaklar_result->fetch_assoc()): ?>
                            <div class="barinak-item border border-gray-200 rounded-lg p-3 hover:border-primary transition-colors" data-sehir="<?= $barinak['sehir_id'] ?>">
                                <div class="flex items-start space-x-3">
                                    <div class="text-2xl">🏢</div>
                                    <div class="flex-1">
                                        <h3 class="font-semibold text-gray-800 text-sm">
                                            <?= htmlspecialchars($barinak['ad']) ?>
                                        </h3>
                                        <p class="text-xs text-gray-600 flex items-center mt-1">
                                            <i class="fas fa-map-marker-alt mr-1"></i>
                                            <?= htmlspecialchars($barinak['sehir_adi']) ?>
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
            <main class="flex-1">