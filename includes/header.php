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
    <title><?= $page_title ?? 'Hayvan Dostları - Hayvanlar için bir yuva' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .dropdown:hover .dropdown-menu { display: block; }
        .navbar-gradient { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="navbar-gradient shadow-lg fixed w-full top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="index.php" class="flex items-center text-white hover:text-purple-200 transition duration-200">
                        <i class="fas fa-paw text-2xl mr-3"></i>
                        <span class="text-xl font-bold">Hayvan Dostları</span>
                    </a>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-6">
                    <a href="index.php" class="text-white hover:text-purple-200 transition duration-200">
                        <i class="fas fa-home mr-1"></i>Ana Sayfa
                    </a>
                    <a href="ilanlar.php" class="text-white hover:text-purple-200 transition duration-200">
                        <i class="fas fa-list mr-1"></i>İlanlar
                    </a>
                    <a href="barinaklar.php" class="text-white hover:text-purple-200 transition duration-200">
                        <i class="fas fa-building mr-1"></i>Barınaklar
                    </a>
                    <a href="etkinlikler.php" class="text-white hover:text-purple-200 transition duration-200">
                        <i class="fas fa-calendar mr-1"></i>Etkinlikler
                    </a>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <!-- Logged in user menu -->
                        <div class="relative dropdown">
                            <button class="flex items-center text-white hover:text-purple-200 transition duration-200">
                                <i class="fas fa-user mr-2"></i>
                                <?= htmlspecialchars($_SESSION['kullanici_adi']) ?>
                                <i class="fas fa-chevron-down ml-1"></i>
                            </button>
                            <div class="dropdown-menu absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 hidden">
                                <a href="ilan_ekle.php" class="block px-4 py-2 text-gray-800 hover:bg-purple-50">
                                    <i class="fas fa-plus mr-2"></i>İlan Ekle
                                </a>
                                <a href="ilanlarim.php" class="block px-4 py-2 text-gray-800 hover:bg-purple-50">
                                    <i class="fas fa-list-alt mr-2"></i>İlanlarım
                                </a>
                                <a href="favorilerim.php" class="block px-4 py-2 text-gray-800 hover:bg-purple-50">
                                    <i class="fas fa-heart mr-2"></i>Favorilerim
                                </a>
                                <a href="taleplerim.php" class="block px-4 py-2 text-gray-800 hover:bg-purple-50">
                                    <i class="fas fa-inbox mr-2"></i>Taleplerim
                                </a>
                                <hr class="my-1">
                                <a href="cikis.php" class="block px-4 py-2 text-red-600 hover:bg-red-50">
                                    <i class="fas fa-sign-out-alt mr-2"></i>Çıkış Yap
                                </a>
                            </div>
                        </div>
                        
                        <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
                            <a href="admin/admin_panel.php" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg transition duration-200">
                                <i class="fas fa-cog mr-1"></i>Admin
                            </a>
                        <?php endif; ?>
                        
                    <?php else: ?>
                        <!-- Not logged in -->
                        <a href="giris.php" class="text-white hover:text-purple-200 transition duration-200">
                            <i class="fas fa-sign-in-alt mr-1"></i>Giriş Yap
                        </a>
                        <a href="kayit.php" class="bg-white text-purple-600 hover:bg-purple-50 px-4 py-2 rounded-lg font-medium transition duration-200">
                            <i class="fas fa-user-plus mr-1"></i>Kayıt Ol
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Mobile Menu Button -->
                <div class="md:hidden">
                    <button id="mobile-menu-button" class="text-white hover:text-purple-200">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div id="mobile-menu" class="hidden md:hidden pb-4">
                <div class="flex flex-col space-y-2">
                    <a href="index.php" class="text-white hover:text-purple-200 py-2">
                        <i class="fas fa-home mr-2"></i>Ana Sayfa
                    </a>
                    <a href="ilanlar.php" class="text-white hover:text-purple-200 py-2">
                        <i class="fas fa-list mr-2"></i>İlanlar
                    </a>
                    <a href="barinaklar.php" class="text-white hover:text-purple-200 py-2">
                        <i class="fas fa-building mr-2"></i>Barınaklar
                    </a>
                    <a href="etkinlikler.php" class="text-white hover:text-purple-200 py-2">
                        <i class="fas fa-calendar mr-2"></i>Etkinlikler
                    </a>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <hr class="border-purple-300 my-2">
                        <span class="text-purple-200 text-sm">Merhaba, <?= htmlspecialchars($_SESSION['kullanici_adi']) ?></span>
                        <a href="ilan_ekle.php" class="text-white hover:text-purple-200 py-2">
                            <i class="fas fa-plus mr-2"></i>İlan Ekle
                        </a>
                        <a href="ilanlarim.php" class="text-white hover:text-purple-200 py-2">
                            <i class="fas fa-list-alt mr-2"></i>İlanlarım
                        </a>
                        <a href="favorilerim.php" class="text-white hover:text-purple-200 py-2">
                            <i class="fas fa-heart mr-2"></i>Favorilerim
                        </a>
                        <a href="taleplerim.php" class="text-white hover:text-purple-200 py-2">
                            <i class="fas fa-inbox mr-2"></i>Taleplerim
                        </a>
                        <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
                            <a href="admin/admin_panel.php" class="text-yellow-300 hover:text-yellow-200 py-2">
                                <i class="fas fa-cog mr-2"></i>Admin Panel
                            </a>
                        <?php endif; ?>
                        <a href="cikis.php" class="text-red-300 hover:text-red-200 py-2">
                            <i class="fas fa-sign-out-alt mr-2"></i>Çıkış Yap
                        </a>
                    <?php else: ?>
                        <hr class="border-purple-300 my-2">
                        <a href="giris.php" class="text-white hover:text-purple-200 py-2">
                            <i class="fas fa-sign-in-alt mr-2"></i>Giriş Yap
                        </a>
                        <a href="kayit.php" class="bg-white text-purple-600 hover:bg-purple-50 px-4 py-2 rounded-lg font-medium">
                            <i class="fas fa-user-plus mr-2"></i>Kayıt Ol
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Content Spacer -->
    <div class="h-20"></div>

    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            const mobileMenu = document.getElementById('mobile-menu');
            mobileMenu.classList.toggle('hidden');
        });
    </script>