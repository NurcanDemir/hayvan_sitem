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
    <title>Hayvan Sitem</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        .text-koyu-pembe { color: #C02942; }
        .bg-koyu-pembe { background-color: #C02942; }
        .hover\:bg-pink-700:hover { background-color: #AD1F37; }
        .text-koyu-yesil { color: #6D926A; }
        .bg-koyu-yesil { background-color: #6D926A; }
        .hover\:bg-green-700:hover { background-color: #5A7E57; }
    </style>
</head>
<body class="bg-gray-100">

<nav class="bg-white p-4 shadow-md fixed w-full z-50 top-0">
    <div class="container mx-auto flex justify-between items-center">
        <a href="index.php" class="text-2xl font-bold text-pink-600">Hayvan Sitem</a>
        <div class="flex items-center space-x-4">
            <a href="index.php" class="text-gray-700 hover:text-pink-600">Anasayfa</a>
            <a href="ilan_ekle.php" class="text-gray-700 hover:text-pink-600">İlan Ekle</a>
            <a href="ilanlarim.php" class="text-gray-700 hover:text-pink-600">İlanlarım</a>
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

<div class="pt-20"> <!-- Navbar için boşluk -->
    <?php include("includes/header.php"); ?>

    <div class="main-content">
        <div class="container mx-auto flex">
            <!-- Sidebar -->
            <div class="sidebar bg-white shadow-lg p-4 sticky top-20 h-screen overflow-y-auto z-10">
                <!-- Sidebar içeriği -->
            </div>
            
            <!-- Ana içerik -->
            <div class="w-3/4">
                <!-- Ana sayfa içeriği -->
            </div>
        </div>
    </div>

    <?php include("includes/footer.php"); ?>
</div>
</body>
</html>