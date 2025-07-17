<?php
// admin/includes/admin_header.php
// Bu dosya, admin panelinin genel HTML başlık kısmı ve sol sidebar menüsünü içerir.
// Her admin paneli sayfasının başında include edilmelidir.
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Paneli</title>

    <link href="../dist/output.css" rel="stylesheet"> 
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* #sidebar için width'i doğrudan belirtebiliriz veya Tailwind'in w-* sınıflarını kullanırız. */
        /* Eğer w-64 (256px) sizin için uygunsa, width: 250px; yerine Tailwind sınıfı kullanın. */
        /* #sidebar { width: 250px; } */
        
        /* Genel kart ve tablo stilleri - bunlar da Tailwind sınıflarına dönüştürülmelidir. */
        /* .card.shadow { border: none; border-radius: .5rem; box-shadow: 0 .125rem .25rem rgba(0,0,0,.075)!important; } */
        /* .table thead th { background-color: #e2e6ea; color: #495057; border-bottom: 2px solid #dee2e6; } */
        /* .table tbody tr:hover { background-color: #f2f2f2; } */
        /* .alert { margin-top: 15px; } */
        /* .table-responsive img.ilan-thumb { max-width: 70px; height: auto; border-radius: 5px; object-fit: cover; } */
    </style>
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal flex min-h-screen">
    
    <div id="sidebar" class="bg-gray-900 text-white p-5 flex flex-col items-stretch sticky top-0 bottom-0 left-0 overflow-y-auto shadow-lg flex-shrink-0" style="width: 250px;"> 
        <a class="text-white text-2xl font-bold text-center block mb-8 px-4 py-2 tracking-wide no-underline" href="admin_panel.php">
            <i class="fas fa-paw mr-2 text-yellow-400"></i> Admin Paneli
        </a>
        
        <ul class="flex flex-col space-y-2 flex-grow"> <li class="w-full">
                <a class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 hover:text-white text-gray-400 
                    <?= (basename($_SERVER['PHP_SELF']) == 'admin_panel.php') ? 'bg-gray-700 text-white' : '' ?> text-base" href="admin_panel.php">
                    <i class="fas fa-home mr-2"></i>Anasayfa
                </a>
            </li>
            <li class="w-full">
                <a class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 hover:text-white text-gray-400
                    <?= (basename($_SERVER['PHP_SELF']) == 'ilan_yonetim.php') ? 'bg-gray-700 text-white' : '' ?> text-base" href="ilan_yonetim.php">
                    <i class="fas fa-clipboard-list mr-2"></i>İlan Yönetimi
                </a>
            </li>
            <li class="w-full">
                <a class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 hover:text-white text-gray-400
                    <?= (basename($_SERVER['PHP_SELF']) == 'kullanici_yonetim.php') ? 'bg-gray-700 text-white' : '' ?> text-base" href="kullanici_yonetim.php">
                    <i class="fas fa-users mr-2"></i>Kullanıcı Yönetimi
                </a>
            </li>
            <li class="w-full">
                <a class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 hover:text-white text-gray-400
                    <?= (basename($_SERVER['PHP_SELF']) == 'sahiplenme_talepleri.php') ? 'bg-gray-700 text-white' : '' ?> text-base" href="sahiplenme_talepleri.php">
                    <i class="fas fa-hand-holding-heart mr-2"></i>Sahiplenme Talepleri
                </a>
            </li>
            <li class="w-full">
                <a class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 hover:text-white text-gray-400
                    <?= (basename($_SERVER['PHP_SELF']) == 'admin_talep_bilgilendir.php' || strpos($_SERVER['REQUEST_URI'], 'admin_talep_bilgilendir.php') !== false) ? 'bg-gray-700 text-white' : '' ?> text-base" href="admin_talep_bilgilendir.php">
                    <i class="fas fa-bell mr-2"></i>Bilgilendirme
                </a>
            </li>
            <li class="w-full">
                <a class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 hover:text-white text-gray-400
                    <?= (basename($_SERVER['PHP_SELF']) == 'raporlar.php') ? 'bg-gray-700 text-white' : '' ?> text-base" href="raporlar.php">
                    <i class="fas fa-chart-bar mr-2"></i>Raporlar
                </a>
            </li>
            <li class="w-full mt-auto"> 
                <a class="block py-2.5 px-4 rounded transition duration-200 bg-red-600 hover:bg-red-700 text-white text-base" href="admin_cikis.php">
                    <i class="fas fa-sign-out-alt mr-2"></i>Çıkış Yap
                </a>
            </li>
        </ul>
    </div>
    
    <main id="content" class="flex-grow p-8 overflow-x-hidden">
        ```
