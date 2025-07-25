
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
            transition: all 0.3s ease;
        }

        .gradient-success:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            transform: translateY(-1px);
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3);
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

        /* Logo Text Gradient */
        .logo-text {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="bg-white shadow-lg sticky top-0 z-50">
        <nav class="max-w-7xl mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
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

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-8">
                    <!-- Ana Menü -->
                    <a href="index.php" class="nav-link <?= ($current_page == 'index.php') ? 'active text-primary' : 'text-stone-600' ?>">
                        Ana Sayfa
                    </a>
                    <a href="etkinlikler.php" class="nav-link <?= ($current_page == 'etkinlikler.php') ? 'active text-primary' : 'text-stone-600' ?>">
                        Etkinlikler
                    </a>
                    <a href="ilanlar.php" class="nav-link <?= ($current_page == 'ilanlar.php') ? 'active text-primary' : 'text-stone-600' ?>">
                        İlanlar
                    </a>
                    
                    <?php if (isset($_SESSION['kullanici_id'])): ?>
                        <!-- İlan Ver Butonu -->
                        <a href="ilan_ekle.php" class="gradient-success text-white px-4 py-2 rounded-md font-semibold transition duration-300">
                            <i class="fas fa-plus mr-2"></i>İlan Ver
                        </a>
                        
                        <!-- Kullanıcı Menüleri -->
                        <a href="ilanlarim.php" class="nav-link <?= ($current_page == 'ilanlarim.php') ? 'active text-primary' : 'text-stone-600' ?>">
                            İlanlarım
                        </a>
                        <a href="favorilerim.php" class="nav-link <?= ($current_page == 'favorilerim.php') ? 'active text-primary' : 'text-stone-600' ?>">
                            Favorilerim
                        </a>
                        <a href="taleplerim.php" class="nav-link <?= ($current_page == 'taleplerim.php') ? 'active text-primary' : 'text-stone-600' ?>">
                            Taleplerim
                        </a>
                        <a href="gelen_talepler.php" class="nav-link <?= ($current_page == 'gelen_talepler.php') ? 'active text-primary' : 'text-stone-600' ?> relative">
                            Gelen Talepler
                            <?php
                            // Okunmamış talep sayısını güvenli şekilde göster
                            $unread_count = 0;
                            try {
                                if (isset($conn) && $conn) {
                                    $unread_result = $conn->query("SELECT COUNT(*) as count FROM sahiplenme_istekleri WHERE ilan_sahibi_kullanici_id = {$_SESSION['kullanici_id']} AND durum = 'beklemede'");
                                    if ($unread_result) {
                                        $unread_count = $unread_result->fetch_assoc()['count'] ?? 0;
                                    }
                                }
                            } catch (Exception $e) {
                                $unread_count = 0;
                            }
                            
                            if ($unread_count > 0): ?>
                                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold"><?= $unread_count ?></span>
                            <?php endif; ?>
                        </a>
                        
                        <!-- Kullanıcı Bilgisi ve Çıkış -->
                        <div class="flex items-center space-x-4">
                            <span class="text-stone-600 font-medium">
                                Hoş geldin, <span class="text-primary font-semibold"><?= htmlspecialchars($_SESSION['kullanici_adi']) ?></span>!
                            </span>
                            <a href="cikis.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md font-semibold transition duration-300">
                                <i class="fas fa-sign-out-alt mr-2"></i>Çıkış
                            </a>
                        </div>
                    <?php else: ?>
                        <!-- Giriş ve Kayıt Butonları -->
                        <a href="giris.php" class="btn-gradient text-white px-4 py-2 rounded-md font-semibold transition duration-300">
                            <i class="fas fa-sign-in-alt mr-2"></i>Giriş Yap
                        </a>
                        <a href="kayit.php" class="border border-primary text-primary hover:bg-primary hover:text-white px-4 py-2 rounded-md font-semibold transition duration-300">
                            <i class="fas fa-user-plus mr-2"></i>Kayıt Ol
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Mobile Menu Button -->
                <div class="md:hidden">
                    <button id="mobile-menu-btn" class="text-gray-600 hover:text-primary focus:outline-none transition duration-300">
                        <i class="fas fa-bars text-2xl"></i>
                    </button>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div id="mobile-menu" class="hidden md:hidden mobile-menu">
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <div class="flex flex-col space-y-3">
                        <!-- Ana Menü - Mobile -->
                        <a href="index.php" class="nav-link <?= ($current_page == 'index.php') ? 'active text-primary' : 'text-stone-600' ?> block px-4 py-3 rounded-md hover:bg-gray-50">
                            <i class="fas fa-home mr-3"></i>Ana Sayfa
                        </a>
                        <a href="etkinlikler.php" class="nav-link <?= ($current_page == 'etkinlikler.php') ? 'active text-primary' : 'text-stone-600' ?> block px-4 py-3 rounded-md hover:bg-gray-50">
                            <i class="fas fa-calendar-alt mr-3"></i>Etkinlikler
                        </a>
                        <a href="ilanlar.php" class="nav-link <?= ($current_page == 'ilanlar.php') ? 'active text-primary' : 'text-stone-600' ?> block px-4 py-3 rounded-md hover:bg-gray-50">
                            <i class="fas fa-list mr-3"></i>İlanlar
                        </a>
                        
                        <?php if (isset($_SESSION['kullanici_id'])): ?>
                            <!-- İlan Ver - Mobile -->
                            <a href="ilan_ekle.php" class="gradient-success text-white px-4 py-3 rounded-md font-semibold transition duration-300 text-center block my-3">
                                <i class="fas fa-plus mr-2"></i>İlan Ver
                            </a>
                            
                            <!-- Kullanıcı Menüleri - Mobile -->
                            <div class="bg-gray-50 rounded-lg p-4 my-4">
                                <div class="text-sm font-semibold text-gray-600 mb-3 flex items-center">
                                    <i class="fas fa-user mr-2 text-primary"></i>
                                    Hoş geldin, <span class="text-primary"><?= htmlspecialchars($_SESSION['kullanici_adi']) ?></span>!
                                </div>
                                
                                <div class="space-y-2">
                                    <a href="ilanlarim.php" class="nav-link <?= ($current_page == 'ilanlarim.php') ? 'active text-primary' : 'text-stone-600' ?> block px-3 py-2 rounded-md hover:bg-white">
                                        <i class="fas fa-clipboard-list mr-3"></i>İlanlarım
                                    </a>
                                    <a href="favorilerim.php" class="nav-link <?= ($current_page == 'favorilerim.php') ? 'active text-primary' : 'text-stone-600' ?> block px-3 py-2 rounded-md hover:bg-white">
                                        <i class="fas fa-heart mr-3"></i>Favorilerim
                                    </a>
                                    <a href="taleplerim.php" class="nav-link <?= ($current_page == 'taleplerim.php') ? 'active text-primary' : 'text-stone-600' ?> block px-3 py-2 rounded-md hover:bg-white">
                                        <i class="fas fa-paper-plane mr-3"></i>Taleplerim
                                    </a>
                                    <a href="gelen_talepler.php" class="nav-link <?= ($current_page == 'gelen_talepler.php') ? 'active text-primary' : 'text-stone-600' ?> block px-3 py-2 rounded-md hover:bg-white flex items-center justify-between">
                                        <span><i class="fas fa-inbox mr-3"></i>Gelen Talepler</span>
                                        <?php if (isset($unread_count) && $unread_count > 0): ?>
                                            <span class="bg-red-500 text-white text-xs rounded-full px-2 py-1 font-bold"><?= $unread_count ?></span>
                                        <?php endif; ?>
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Çıkış - Mobile -->
                            <a href="cikis.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-3 rounded-md font-semibold transition duration-300 text-center block">
                                <i class="fas fa-sign-out-alt mr-2"></i>Çıkış Yap
                            </a>
                        <?php else: ?>
                            <!-- Giriş ve Kayıt - Mobile -->
                            <div class="space-y-3 pt-3">
                                <a href="giris.php" class="btn-gradient text-white px-4 py-3 rounded-md font-semibold transition duration-300 text-center block">
                                    <i class="fas fa-sign-in-alt mr-2"></i>Giriş Yap
                                </a>
                                <a href="kayit.php" class="border border-primary text-primary hover:bg-primary hover:text-white px-4 py-3 rounded-md font-semibold transition duration-300 text-center block">
                                    <i class="fas fa-user-plus mr-2"></i>Kayıt Ol
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
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
                const menuIcon = mobileMenuBtn.querySelector('i');
                
                mobileMenuBtn.addEventListener('click', function() {
                    const isHidden = mobileMenu.classList.contains('hidden');
                    
                    if (isHidden) {
                        mobileMenu.classList.remove('hidden');
                        setTimeout(() => {
                            mobileMenu.classList.add('show');
                        }, 10);
                        menuIcon.classList.remove('fa-bars');
                        menuIcon.classList.add('fa-times');
                    } else {
                        mobileMenu.classList.remove('show');
                        setTimeout(() => {
                            mobileMenu.classList.add('hidden');
                        }, 300);
                        menuIcon.classList.remove('fa-times');
                        menuIcon.classList.add('fa-bars');
                    }
                });
                
                // Sayfa dışına tıklandığında menüyü kapat
                document.addEventListener('click', function(e) {
                    if (!mobileMenuBtn.contains(e.target) && !mobileMenu.contains(e.target)) {
                        if (!mobileMenu.classList.contains('hidden')) {
                            mobileMenu.classList.remove('show');
                            setTimeout(() => {
                                mobileMenu.classList.add('hidden');
                            }, 300);
                            menuIcon.classList.remove('fa-times');
                            menuIcon.classList.add('fa-bars');
                        }
                    }
                });
            }
        });
    </script>