<?php
// header.php
// Oturum henüz başlatılmadıysa başlat (İlanlarim.php'de başlatılıyor ama güvenlik için burada da kontrol edilebilir)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yuvanın Anahtarı - Sevgi Köprüsü</title>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <link rel="alternate icon" href="favicon.ico">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        .text-koyu-pembe { color: #C02942; }
        .bg-koyu-pembe { background-color: #C02942; }
        .hover\:bg-pink-700:hover { background-color: #AD1F37; }
        .text-koyu-yesil { color: #6D926A; }
        .bg-koyu-yesil { background-color: #6D926A; }
        .hover\:bg-green-700:hover { background-color: #5A7E57; }
        
        /* Logo Animasyonları */
        .logo-svg {
            transition: all 0.3s ease-in-out;
        }
        
        .logo-svg:hover {
            transform: scale(1.1) rotate(5deg);
            filter: drop-shadow(0 5px 15px rgba(192, 41, 66, 0.3));
        }
        
        @keyframes heartbeat {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        
        .logo-svg:hover path[fill*="heartGradient"] {
            animation: heartbeat 1s ease-in-out infinite;
        }
        
        /* Logo container hover efekti */
        .logo-container:hover .logo-text {
            background: linear-gradient(45deg, #FF1493, #FF69B4, #C02942, #8B008B);
            background-size: 400% 400%;
            animation: gradientShift 2s ease infinite;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

<nav class="bg-white p-4 shadow-md fixed w-full z-50 top-0">
    <div class="container mx-auto flex justify-between items-center">
        <a href="index.php" class="logo-container flex items-center space-x-3 text-2xl font-bold text-pink-600 hover:text-pink-700 transition-colors duration-300">
            <!-- Sade Pati Logo SVG -->
            <svg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg" class="logo-svg">
                <!-- Ana Pati İzi - Tek Renk -->
                <g transform="translate(30, 30)">
                    <!-- Ana pati yastığı (alt) -->
                    <ellipse cx="0" cy="8" rx="12" ry="8" fill="#4A5568"/>
                    
                    <!-- Üst parmaklar (4 tane) -->
                    <circle cx="-8" cy="-5" r="4" fill="#4A5568"/>
                    <circle cx="-3" cy="-8" r="4" fill="#4A5568"/>
                    <circle cx="3" cy="-8" r="4" fill="#4A5568"/>
                    <circle cx="8" cy="-5" r="4" fill="#4A5568"/>
                </g>
            </svg>
            
            <div class="flex flex-col">
                <span class="logo-text text-2xl font-extrabold text-purple-700">
                    Yuvanın Anahtarı
                </span>
                <span class="text-sm text-blue-600 font-semibold -mt-1">Sevgi Köprüsü</span>
            </div>
        </a>
        <div class="flex items-center space-x-4">
            <a href="index.php" class="text-gray-700 hover:text-pink-600">Anasayfa</a>
            <a href="ilan_ekle.php" class="text-gray-700 hover:text-pink-600">İlan Ekle</a>
            <a href="ilanlarim.php" class="text-gray-700 hover:text-pink-600">İlanlarım</a>
            <a href="favorilerim.php" class="text-gray-700 hover:text-pink-600">Favorilerim</a>
            <a href="taleplerim.php" class="text-gray-700 hover:text-pink-600">Taleplerim</a>
            <a href="gelen_talepler.php" class="text-gray-700 hover:text-pink-600">Gelen Talepler</a>
            <?php if (isset($_SESSION['kullanici_id'])): ?>
                <span class="text-gray-700">Hoşgeldin, <?= htmlspecialchars($_SESSION['kullanici_adi'] ?? 'Kullanıcı') ?>!</span>
                <a href="cikis.php" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Çıkış Yap</a>
            <?php else: ?>
                <a href="giris.php" class="text-gray-700 hover:text-pink-600">Giriş Yap</a>
                <a href="kayit.php" class="text-gray-700 hover:text-pink-600">Kayıt Ol</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="flex-grow pt-20">