<?php
// index.php (veya anasayfa.php) - Sitenin giriş sayfası
session_start();
include("includes/db.php");

// --- Arama Formundan Gelen Değerleri Al ---
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
    // bind_param değişken sayısına göre dinamik olarak çağrılmalı
    $stmt_aktif_ilanlar->bind_param($types_aktif, ...$params_aktif);
}
$stmt_aktif_ilanlar->execute();
$result_aktif_ilanlar = $stmt_aktif_ilanlar->get_result();


// --- Sahiplenenler (sahiplendi olanlar) Sorgusu ---
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
    <title>Hayvan Sahiplendirme - Anasayfa</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <link href="./dist/output.css" rel="stylesheet"> 

    <style>
        /* Kaydırma çubuğu için özel stil (eğer hala gerekliyse, Tailwind ile de yapılabilir) */
        /* Bu stiller, Tailwind ile tamamen kontrol edilebilen 'scroll-row' sınıfı yerine doğrudan 'overflow-x-auto' ile de elde edilebilir,
           ancak özelleştirilmiş kaydırma çubuğu görünümü için burada bırakıldı. */
        .scroll-row {
            display: flex;
            flex-wrap: nowrap;
            overflow-x: auto;
            gap: 2rem; /* Tailwind 'gap-8' karşılığı */
            padding-bottom: 1rem;
            scrollbar-width: thin;
            scrollbar-color: #888 #f1f1f1;
            padding-left: 1rem; /* Örnek: Sol boşluk */
            padding-right: 1rem; /* Örnek: Sağ boşluk */
        }
        .scroll-row::-webkit-scrollbar {
            height: 10px;
        }
        .scroll-row::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }
        .scroll-row::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        /* Slide paneller için CSS (header'daki JS ile birlikte çalışır) */
        /* Bu stiller header.php içindeki JS ile entegre çalışır. */
        .slide-panel-left {
            position: fixed; top: 0; left: -300px; width: 300px; height: 100%; box-shadow: 2px 0 5px rgba(0,0,0,0.3); padding: 20px; z-index: 1050; transition: left 0.4s ease-in-out;
        }
        .slide-panel-left.show { left: 0; }
        .slide-panel-right {
            position: fixed; top: 0; right: -300px; width: 300px; height: 100%; box-shadow: -2px 0 5px rgba(0,0,0,0.3); padding: 20px; z-index: 1050; transition: right 0.4s ease-in-out;
        }
        .slide-panel-right.show { right: 0; }
        .panel-icerik .kapat {
            font-size: 2rem; cursor: pointer;
        }
    </style>
</head>
<body class="bg-gray-50 font-sans">

<?php include("includes/header.php"); ?>

<div class="container mx-auto p-4">
    <h1 class="text-3xl font-bold text-center mb-8">Hayvan Sahiplendirme Sitesi</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- İlanlar burada listelenecek -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-bold mb-4">Örnek İlan</h2>
            <p class="text-gray-600">Bu bir örnek ilan açıklamasıdır.</p>
        </div>
    </div>
</div>

<?php include("includes/footer.php"); ?>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const girisButonu = document.getElementById("girisButonu");
        const kayitButonu = document.getElementById("kayitButonu");
        const girisPaneli = document.getElementById("girisPaneli");
        const kayitPaneli = document.getElementById("kayitPaneli");
        const girisKapat = document.getElementById("girisKapat");
        const kayitKapat = document.getElementById("kayitKapat");
        const mobileMenuToggle = document.getElementById("mobileMenuToggle");
        const mobileMenu = document.getElementById("mobileMenu");
        const girisButonuMobile = document.getElementById("girisButonuMobile");
        const kayitButonuMobile = document.getElementById("kayitButonuMobile");

        // Null kontrolü ekleyelim, elementler her sayfada olmayabilir
        if (girisButonu) { 
            girisButonu.addEventListener("click", function () {
                girisPaneli.classList.add("show"); 
                kayitPaneli.classList.remove("show"); 
            });
        }
        if (kayitButonu) { 
            kayitButonu.addEventListener("click", function () {
                kayitPaneli.classList.add("show");
                girisPaneli.classList.remove("show"); 
            });
        }
        if (girisButonuMobile) {
            girisButonuMobile.addEventListener("click", function () {
                girisPaneli.classList.add("show");
                kayitPaneli.classList.remove("show");
                mobileMenu.classList.add("hidden"); 
            });
        }
        if (kayitButonuMobile) {
            kayitButonuMobile.addEventListener("click", function () {
                kayitPaneli.classList.add("show");
                girisPaneli.classList.remove("show");
                mobileMenu.classList.add("hidden"); 
            });
        }
        // Kapatma butonları her zaman olmalı
        if (girisKapat) {
            girisKapat.addEventListener("click", function () {
                girisPaneli.classList.remove("show");
            });
        }
        if (kayitKapat) {
            kayitKapat.addEventListener("click", function () {
                kayitPaneli.classList.remove("show");
            });
        }
        if (mobileMenuToggle) {
            mobileMenuToggle.addEventListener("click", function () {
                mobileMenu.classList.toggle("hidden"); 
            });
        }
    });
</script>

</body>
</html>