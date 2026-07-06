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
    <title>Satın Alma Yuva Ol | Admin Paneli</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link href="../dist/output.css" rel="stylesheet"> 
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --bg-light: #F8F9FA;
            --primary-pink: #FFB3C6;
            --action-mint: #A8DADC;
            --text-dark: #2B2D42;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background:
                radial-gradient(circle at 10% 14%, rgba(255, 179, 198, 0.3), transparent 42%),
                radial-gradient(circle at 88% 8%, rgba(168, 218, 220, 0.3), transparent 40%),
                var(--bg-light) !important;
            color: var(--text-dark);
        }

        #sidebar {
            background: var(--primary-pink) !important;
            border-right: 1px solid rgba(43, 45, 66, 0.08);
            box-shadow: 8px 0 26px rgba(43, 45, 66, 0.08) !important;
        }

        #sidebar a {
            color: var(--text-dark) !important;
            border-radius: 12px;
        }

        #sidebar a:hover,
        #sidebar a.bg-gray-700,
        #sidebar a.text-white {
            background: rgba(248, 249, 250, 0.78) !important;
            color: var(--text-dark) !important;
        }

        #content,
        .bg-white,
        .card,
        .table-responsive,
        .modal-content,
        .swal2-popup {
            background: #ffffff !important;
            border: 1px solid var(--primary-pink) !important;
            border-radius: 16px !important;
            box-shadow: 0 12px 30px rgba(43, 45, 66, 0.1) !important;
        }

        .bg-gray-100,
        .bg-gray-50,
        .bg-gray-900 {
            background-color: var(--bg-light) !important;
        }

        .text-white,
        .text-gray-900,
        .text-gray-800,
        .text-gray-700,
        .text-gray-600,
        .text-gray-500,
        .text-gray-400 {
            color: var(--text-dark) !important;
        }

        .bg-blue-600,
        .bg-indigo-600,
        .bg-emerald-600,
        .bg-green-600,
        .btn-primary,
        button[type="submit"],
        .swal2-confirm {
            background: var(--action-mint) !important;
            color: var(--text-dark) !important;
            border: none !important;
            border-radius: 14px !important;
            box-shadow: 0 10px 24px rgba(43, 45, 66, 0.12) !important;
        }

        .bg-red-600,
        .bg-red-700,
        .bg-purple-600,
        .bg-pink-600,
        .btn,
        button {
            background: var(--primary-pink) !important;
            color: var(--text-dark) !important;
            border: 1px solid rgba(43, 45, 66, 0.08) !important;
            border-radius: 14px !important;
        }

        .hover\:bg-gray-700:hover,
        .hover\:bg-red-700:hover,
        .hover\:bg-blue-700:hover,
        .hover\:bg-indigo-700:hover,
        .hover\:bg-emerald-700:hover,
        .hover\:bg-purple-700:hover {
            background: var(--action-mint) !important;
            color: var(--text-dark) !important;
        }

        .border,
        .border-gray-200,
        .border-gray-300,
        .border-gray-700 {
            border-color: var(--primary-pink) !important;
        }

        input,
        select,
        textarea {
            border: 1px solid var(--primary-pink) !important;
            border-radius: 12px !important;
            color: var(--text-dark) !important;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: var(--action-mint) !important;
            box-shadow: 0 0 0 4px rgba(168, 218, 220, 0.35) !important;
            outline: none;
        }
    </style>
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal flex min-h-screen">
    
    <div id="sidebar" class="bg-gray-900 text-white p-5 flex flex-col items-stretch sticky top-0 bottom-0 left-0 overflow-y-auto shadow-lg flex-shrink-0" style="width: 250px;"> 
        <a class="text-white text-2xl font-bold text-center block mb-8 px-4 py-2 tracking-wide no-underline" href="admin_panel.php">
            <i class="fas fa-paw mr-2 text-yellow-400"></i>
            <span>Satın Alma</span>
            <span style="color: var(--action-mint);">Yuva Ol</span>
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
