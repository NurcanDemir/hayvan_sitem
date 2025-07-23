<?php
// header.php
// Oturum henüz başlatılmadıysa başlat (İlanlarim.php'de başlatılıyor ama güvenlik için burada da kontrol edilebilir)
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
    <title><?= isset($page_title) ? $page_title : 'Hayvan Dostları' ?></title>
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
    </style>
</head>
<body>
    <!-- Header -->
    <header class="bg-white shadow-lg sticky top-0 z-50">
        <nav class="max-w-7xl mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="text-3xl">🐾</div>
                    <h1 class="text-2xl font-bold text-primary">
                        <a href="index.php" class="hover:text-primary-light transition-colors">Hayvan Dostları</a>
                    </h1>
                </div>
                
                <!-- Desktop Menu -->
                <div class="hidden lg:flex items-center space-x-6">
                    <!-- Ana Navigasyon -->
                    <a href="index.php" class="nav-link <?= ($current_page == 'index.php') ? 'active' : 'text-stone-600' ?>">
                        <i class="fas fa-home mr-1"></i>Ana Sayfa
                    </a>
                    <a href="barinaklar.php" class="nav-link <?= ($current_page == 'barinaklar.php') ? 'active' : 'text-stone-600' ?>">
                        <i class="fas fa-home mr-1"></i>Barınaklar
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
                            // Okunmamış talep sayısını göster
                            include_once("includes/db.php");
                            $unread_count = $conn->query("SELECT COUNT(*) as count FROM sahiplendirme_talepleri WHERE ilan_sahibi_id = {$_SESSION['kullanici_id']} AND okundu = 0")->fetch_assoc()['count'] ?? 0;
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
                        <i class="fas fa-home mr-2"></i>Barınaklar
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