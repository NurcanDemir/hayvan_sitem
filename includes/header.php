<?php
// filepath: c:\xampp\htdocs\hayvan_sitem\includes\header.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Get current page name for active nav highlighting
$current_page = basename($_SERVER['PHP_SELF']);

// Check if user is logged in
$is_logged_in = isset($_SESSION['kullanici_id']);
$kullanici_adi = $_SESSION['kullanici_adi'] ?? '';
$user_role = $_SESSION['rol'] ?? 'kullanici';
$user_id = $_SESSION['kullanici_id'] ?? null;

// Get user profile photo if logged in
$user_profile_photo = '';
if ($is_logged_in && $user_id) {
    include_once(__DIR__ . '/db.php');
    $profile_sql = "SELECT profil_foto FROM kullanicilar WHERE id = ?";
    $profile_stmt = $conn->prepare($profile_sql);
    $profile_stmt->bind_param("i", $user_id);
    $profile_stmt->execute();
    $profile_result = $profile_stmt->get_result()->fetch_assoc();
    $user_profile_photo = $profile_result['profil_foto'] ?? '';
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Sıcak Patizi' ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Custom CSS -->
    <style>
        :root {
            --bg-light: #F8F9FA;
            --primary-pink: #FFB3C6;
            --action-mint: #A8DADC;
            --text-dark: #2B2D42;
            --brand-highlight: #3A868F;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background:
                radial-gradient(circle at 12% 15%, rgba(255, 179, 198, 0.28), transparent 46%),
                radial-gradient(circle at 88% 8%, rgba(168, 218, 220, 0.28), transparent 42%),
                var(--bg-light) !important;
            color: var(--text-dark);
        }

        nav {
            background: rgba(248, 249, 250, 0.94) !important;
            border-bottom: 1px solid var(--primary-pink);
            box-shadow: 0 10px 24px rgba(43, 45, 66, 0.08) !important;
            backdrop-filter: blur(10px);
        }

        .btn-gradient {
            background: var(--action-mint);
            color: var(--text-dark);
            border-radius: 14px;
            box-shadow: 0 10px 24px rgba(43, 45, 66, 0.1);
            transition: all 0.3s ease;
        }

        .btn-gradient:hover {
            background: var(--action-mint);
            transform: translateY(-1px);
            box-shadow: 0 14px 30px rgba(43, 45, 66, 0.14);
        }

        .card-hover {
            transition: all 0.3s ease;
            border: 1px solid var(--primary-pink);
            border-radius: 16px;
        }

        .card-hover:hover {
            transform: translateY(-3px);
            box-shadow: 0 16px 34px rgba(43, 45, 66, 0.12);
        }

        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .line-clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .nav-link {
            position: relative;
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
            transition: all 0.3s ease;
        }

        .brand-logo {
            white-space: nowrap;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary-pink);
            transition: width 0.3s ease;
        }

        .nav-link:hover::after,
        .nav-link.active::after {
            width: 100%;
        }

        .nav-link.active {
            color: var(--text-dark);
            font-weight: 600;
        }

        .dropdown {
            position: relative;
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background: #ffffff;
            border-radius: 14px;
            box-shadow: 0 14px 34px rgba(43, 45, 66, 0.13);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            z-index: 1000;
            min-width: 200px;
            padding: 8px 0;
            border: 1px solid var(--primary-pink);
            margin-top: 8px;
        }

        .dropdown:hover .dropdown-menu,
        .dropdown-menu:hover {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown:hover .dropdown-menu::before {
            content: '';
            position: absolute;
            top: -10px;
            left: 0;
            right: 0;
            height: 12px;
            background: transparent;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            padding: 10px 16px;
            color: var(--text-dark);
            text-decoration: none;
            transition: all 0.2s ease;
            border-bottom: 1px solid rgba(255, 179, 198, 0.45);
        }

        .dropdown-item:last-child {
            border-bottom: none;
        }

        .dropdown-item:hover {
            background: var(--bg-light);
            color: var(--text-dark);
            padding-left: 20px;
        }

        .dropdown-item i {
            margin-right: 8px;
            width: 16px;
            text-align: center;
        }

        .mobile-menu {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: #ffffff;
            border-radius: 0 0 14px 14px;
            border: 1px solid var(--primary-pink);
            box-shadow: 0 14px 30px rgba(43, 45, 66, 0.12);
            z-index: 999;
        }

        .mobile-menu.show {
            display: block;
        }

        .text-gray-800,
        .text-gray-700,
        .text-gray-600,
        .text-gray-500,
        .text-purple-600,
        .text-purple-700,
        .text-purple-800,
        .hover\:text-purple-600:hover,
        .hover\:text-purple-700:hover,
        .hover\:text-purple-800:hover,
        .hover\:text-gray-600:hover,
        .text-white {
            color: var(--text-dark) !important;
        }

        .bg-white,
        .bg-gray-50,
        .bg-purple-100 {
            background-color: #ffffff !important;
        }

        .from-purple-600,
        .to-pink-600,
        .hover\:from-purple-700:hover,
        .hover\:to-pink-700:hover,
        .bg-gradient-to-r {
            background: var(--primary-pink) !important;
            color: var(--text-dark) !important;
        }

        .cta-primary,
        .swal2-confirm,
        button[type="submit"] {
            background: var(--action-mint) !important;
            color: var(--text-dark) !important;
            border: none !important;
            border-radius: 14px !important;
            box-shadow: 0 10px 24px rgba(43, 45, 66, 0.12) !important;
        }

        .cta-secondary {
            background: var(--primary-pink) !important;
            color: var(--text-dark) !important;
            border: 1px solid rgba(43, 45, 66, 0.08) !important;
            border-radius: 14px !important;
        }

        .rounded-md,
        .rounded-full,
        .dropdown-menu,
        .mobile-menu {
            border-radius: 14px !important;
        }

        button,
        .button,
        .btn,
        .mobile-toggle {
            border-radius: 12px;
        }

        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }

            .mobile-toggle {
                display: block;
            }
        }

        @media (min-width: 769px) {
            .mobile-toggle {
                display: none;
            }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="index.php" class="flex items-center space-x-3 brand-logo">
                        <div class="w-10 h-10 bg-gradient-to-r from-purple-600 to-pink-600 rounded-full flex items-center justify-center cta-secondary">
                            <i class="fas fa-paw text-white text-lg"></i>
                        </div>
                        <span class="text-xl font-bold text-gray-800">
                            <span>Sıcak</span>
                            <span class="ml-1" style="color: var(--brand-highlight);">Patizi</span>
                        </span>
                    </a>
                </div>

                <!-- Desktop Navigation Links -->
                <div class="hidden md:flex items-center space-x-8 nav-links">
                    <a href="index.php" class="nav-link text-gray-600 hover:text-purple-600 px-3 py-2 <?= $current_page == 'index.php' ? 'active' : '' ?>">
                        <i class="fas fa-home mr-2"></i>Ana Sayfa
                    </a>
                    <a href="ilanlar.php" class="nav-link text-gray-600 hover:text-purple-600 px-3 py-2 <?= $current_page == 'ilanlar.php' ? 'active' : '' ?>">
                        <i class="fas fa-list mr-2"></i>İlanlar
                    </a>
                    <a href="ilan_ekle.php" class="nav-link text-gray-600 hover:text-purple-600 px-3 py-2 <?= $current_page == 'ilan_ekle.php' ? 'active' : '' ?>">
                        <i class="fas fa-plus mr-2"></i>İlan Ekle
                    </a>
                    <a href="etkinlikler.php" class="nav-link text-gray-600 hover:text-purple-600 px-3 py-2 <?= $current_page == 'etkinlikler.php' ? 'active' : '' ?>">
                        <i class="fas fa-calendar mr-2"></i>Etkinlikler
                    </a>
                    <a href="barinaklar.php" class="nav-link text-gray-600 hover:text-purple-600 px-3 py-2 <?= $current_page == 'barinaklar.php' ? 'active' : '' ?>">
                        <i class="fas fa-building mr-2"></i>Barınaklar
                    </a>
                    <a href="hakkimizda.php" class="nav-link text-gray-600 hover:text-purple-600 px-3 py-2 <?= $current_page == 'hakkimizda.php' ? 'active' : '' ?>">
                        <i class="fas fa-info-circle mr-2"></i>Hakkımızda
                    </a>
                </div>

                <!-- User Section -->
                <div class="flex items-center space-x-4">
                    <?php if ($is_logged_in): ?>
                        <!-- User Dropdown -->
                        <div class="dropdown">
                            <button class="flex items-center space-x-2 text-gray-600 hover:text-purple-600 focus:outline-none">
                                <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center overflow-hidden">
                                    <?php if (!empty($user_profile_photo) && file_exists('uploads/profiles/' . $user_profile_photo)): ?>
                                        <img src="uploads/profiles/<?= htmlspecialchars($user_profile_photo) ?>" 
                                             alt="Profil Fotoğrafı" class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <i class="fas fa-user text-purple-600"></i>
                                    <?php endif; ?>
                                </div>
                                <a href="profil.php" class="hidden sm:block font-medium hover:text-purple-600"><?= htmlspecialchars($kullanici_adi) ?></a>
                                <i class="fas fa-chevron-down text-sm"></i>
                            </button>
                            
                            <div class="dropdown-menu">
                                <a href="profil.php" class="dropdown-item">
                                    <i class="fas fa-user"></i>
                                    Profilim
                                </a>
                                <a href="ilanlarim.php" class="dropdown-item">
                                    <i class="fas fa-list-alt"></i>
                                    İlanlarım
                                </a>
                                <a href="favorilerim.php" class="dropdown-item">
                                    <i class="fas fa-heart"></i>
                                    Favorilerim
                                </a>
                                <a href="sahiplenme_isteklerim.php" class="dropdown-item">
                                    <i class="fas fa-handshake"></i>
                                    Sahiplenme İsteklerim
                                </a>
                                <a href="mesajlar.php" class="dropdown-item">
                                    <i class="fas fa-comments"></i>
                                    Mesajlarım
                                </a>
                                <a href="gelen_talepler.php" class="dropdown-item">
                                    <i class="fas fa-inbox"></i>
                                    Gelen Talepler
                                </a>
                                <?php if ($user_role === 'admin'): ?>
                                    <a href="admin/admin_panel.php" class="dropdown-item">
                                        <i class="fas fa-cog"></i>
                                        Yönetim Paneli
                                    </a>
                                <?php endif; ?>
                                <div class="border-t border-gray-200 my-2"></div>
                                <a href="cikis.php" class="dropdown-item text-red-600 hover:text-red-700">
                                    <i class="fas fa-sign-out-alt"></i>
                                    Çıkış Yap
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Login/Register Buttons -->
                        <div class="flex items-center space-x-2">
                            <a href="giris.php" class="text-purple-600 hover:text-purple-700 font-medium px-3 py-2 rounded-md transition-colors">
                                <i class="fas fa-sign-in-alt mr-1"></i>Giriş
                            </a>
                            <a href="kayit.php" class="bg-gradient-to-r from-purple-600 to-pink-600 text-white px-4 py-2 rounded-md font-medium hover:from-purple-700 hover:to-pink-700 transition-all duration-200 cta-primary">
                                <i class="fas fa-user-plus mr-1"></i>Kayıt Ol
                            </a>
                        </div>
                    <?php endif; ?>

                    <!-- Mobile Menu Toggle -->
                    <button class="mobile-toggle md:hidden text-gray-600 hover:text-purple-600 focus:outline-none" onclick="toggleMobileMenu()">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div id="mobileMenu" class="mobile-menu md:hidden">
                <div class="px-4 py-3 space-y-3">
                    <a href="index.php" class="block text-gray-600 hover:text-purple-600 py-2 <?= $current_page == 'index.php' ? 'text-purple-600 font-semibold' : '' ?>">
                        <i class="fas fa-home mr-2"></i>Ana Sayfa
                    </a>
                    <a href="ilanlar.php" class="block text-gray-600 hover:text-purple-600 py-2 <?= $current_page == 'ilanlar.php' ? 'text-purple-600 font-semibold' : '' ?>">
                        <i class="fas fa-list mr-2"></i>İlanlar
                    </a>
                    <a href="ilan_ekle.php" class="block text-gray-600 hover:text-purple-600 py-2 <?= $current_page == 'ilan_ekle.php' ? 'text-purple-600 font-semibold' : '' ?>">
                        <i class="fas fa-plus mr-2"></i>İlan Ekle
                    </a>
                    <a href="etkinlikler.php" class="block text-gray-600 hover:text-purple-600 py-2 <?= $current_page == 'etkinlikler.php' ? 'text-purple-600 font-semibold' : '' ?>">
                        <i class="fas fa-calendar mr-2"></i>Etkinlikler
                    </a>
                    <a href="barinaklar.php" class="block text-gray-600 hover:text-purple-600 py-2 <?= $current_page == 'barinaklar.php' ? 'text-purple-600 font-semibold' : '' ?>">
                        <i class="fas fa-building mr-2"></i>Barınaklar
                    </a>
                    <a href="hakkimizda.php" class="block text-gray-600 hover:text-purple-600 py-2 <?= $current_page == 'hakkimizda.php' ? 'text-purple-600 font-semibold' : '' ?>">
                        <i class="fas fa-info-circle mr-2"></i>Hakkımızda
                    </a>
                    
                    <?php if (!$is_logged_in): ?>
                        <div class="border-t border-gray-200 pt-3 space-y-2">
                            <a href="giris.php" class="block text-purple-600 hover:text-purple-700 font-medium py-2">
                                <i class="fas fa-sign-in-alt mr-2"></i>Giriş Yap
                            </a>
                            <a href="kayit.php" class="block bg-gradient-to-r from-purple-600 to-pink-600 text-white px-4 py-2 rounded-md font-medium text-center cta-primary">
                                <i class="fas fa-user-plus mr-2"></i>Kayıt Ol
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <script>
        function toggleMobileMenu() {
            const mobileMenu = document.getElementById('mobileMenu');
            mobileMenu.classList.toggle('show');
        }

        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            const mobileMenu = document.getElementById('mobileMenu');
            const mobileToggle = document.querySelector('.mobile-toggle');
            
            if (!mobileMenu.contains(event.target) && !mobileToggle.contains(event.target)) {
                mobileMenu.classList.remove('show');
            }
        });

        // Enhanced dropdown functionality
        document.addEventListener('DOMContentLoaded', function() {
            const dropdowns = document.querySelectorAll('.dropdown');
            
            dropdowns.forEach(dropdown => {
                const menu = dropdown.querySelector('.dropdown-menu');
                let timeout;

                dropdown.addEventListener('mouseenter', () => {
                    clearTimeout(timeout);
                    menu.style.opacity = '1';
                    menu.style.visibility = 'visible';
                    menu.style.transform = 'translateY(0)';
                });

                dropdown.addEventListener('mouseleave', () => {
                    timeout = setTimeout(() => {
                        menu.style.opacity = '0';
                        menu.style.visibility = 'hidden';
                        menu.style.transform = 'translateY(-10px)';
                    }, 300); // Increased delay to 300ms
                });

                // Keep dropdown open when hovering over menu
                menu.addEventListener('mouseenter', () => {
                    clearTimeout(timeout);
                });

                menu.addEventListener('mouseleave', () => {
                    timeout = setTimeout(() => {
                        menu.style.opacity = '0';
                        menu.style.visibility = 'hidden';
                        menu.style.transform = 'translateY(-10px)';
                    }, 300); // Increased delay to 300ms
                });
            });
        });
    </script>