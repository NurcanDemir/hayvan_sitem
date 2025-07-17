<?php
// index.php (veya anasayfa.php) - Sitenin giriş sayfası
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Veritabanı bağlantısı dosyasını dahil et.
// NOT: db.php dosyanızın $conn değişkenini sağladığından emin olun.
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
// Önce sahiplenen_yorumu kolonunun var olup olmadığını kontrol et
$columns_check = $conn->query("SHOW COLUMNS FROM sahiplenme_istekleri LIKE 'sahiplenen_yorumu'");
$column_exists = $columns_check->num_rows > 0;

if ($column_exists) {
    $sql_sahiplenenler = "
        SELECT
            i.*,
            c.ad AS cins_ad,
            h.ad AS hastalik_ad,
            k.ad AS kategori_ad,
            il.ad AS il_ad,
            ilce.ad AS ilce_ad,
            si.sahiplenen_yorumu,
            si.yorum_tarihi,
            si.talep_eden_ad_soyad
        FROM ilanlar i
        LEFT JOIN cinsler c ON i.cins_id = c.id
        LEFT JOIN hastaliklar h ON i.hastalik_id = h.id
        LEFT JOIN kategoriler k ON i.kategori_id = k.id
        LEFT JOIN il il ON i.il_id = il.id
        LEFT JOIN ilce ilce ON i.ilce_id = ilce.id
        LEFT JOIN sahiplenme_istekleri si ON i.id = si.ilan_id AND si.durum = 'tamamlandı'
        WHERE i.durum = 'sahiplenildi'
        ORDER BY i.tarih DESC
        LIMIT 20
    ";
} else {
    $sql_sahiplenenler = "
        SELECT
            i.*,
            c.ad AS cins_ad,
            h.ad AS hastalik_ad,
            k.ad AS kategori_ad,
            il.ad AS il_ad,
            ilce.ad AS ilce_ad,
            NULL AS sahiplenen_yorumu,
            NULL AS yorum_tarihi,
            NULL AS talep_eden_ad_soyad
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
}
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
        
        /* Hero Section Animations */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        @keyframes pulse-soft {
            0%, 100% { opacity: 0.1; }
            50% { opacity: 0.3; }
        }
        
        .hero-float {
            animation: float 6s ease-in-out infinite;
        }
        
        .hero-pulse {
            animation: pulse-soft 4s ease-in-out infinite;
        }
        
        /* Gradient Text Animation */
        @keyframes gradient-shift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .gradient-text {
            background: linear-gradient(-45deg, #B8D4F0, #E1BEE7, #C8E6C9, #F8BBD9);
            background-size: 400% 400%;
            animation: gradient-shift 4s ease infinite;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        /* Glass Effect */
        .glass-effect {
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
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
<body class="bg-gray-50 font-sans min-h-screen flex flex-col">

<?php include("includes/header.php"); ?>

<div class="relative h-[600px] bg-gradient-to-br from-blue-200 via-purple-200 to-green-200 text-gray-700 flex items-center justify-center text-center shadow-lg overflow-hidden">
    <!-- Arkaplan SVG Desenler - Pastel Karikatür Hayvanlar -->
    <div class="absolute inset-0 opacity-20 hero-pulse">
        <svg width="100%" height="100%" viewBox="0 0 800 600" xmlns="http://www.w3.org/2000/svg">
            <!-- Mutlu Köpek (Sol üst) - Pastel Mavi -->
            <g class="hero-float">
                <g transform="translate(80, 80)">
                    <!-- Vücut -->
                    <ellipse cx="50" cy="80" rx="35" ry="25" fill="#B8D4F0" stroke="#8FB8E8" stroke-width="2"/>
                    <!-- Kafa -->
                    <circle cx="35" cy="50" r="25" fill="#D1E7F7" stroke="#8FB8E8" stroke-width="2"/>
                    <!-- Kulaklar -->
                    <ellipse cx="15" cy="35" rx="12" ry="20" fill="#A8CCE8" stroke="#7BA5D6" stroke-width="1.5"/>
                    <ellipse cx="55" cy="35" rx="12" ry="20" fill="#A8CCE8" stroke="#7BA5D6" stroke-width="1.5"/>
                    <!-- Gözler (sevimli) -->
                    <circle cx="25" cy="45" r="6" fill="#FFFFFF" stroke="#8FB8E8" stroke-width="1"/>
                    <circle cx="45" cy="45" r="6" fill="#FFFFFF" stroke="#8FB8E8" stroke-width="1"/>
                    <circle cx="25" cy="45" r="3" fill="#5A8BC4"/>
                    <circle cx="45" cy="45" r="3" fill="#5A8BC4"/>
                    <circle cx="27" cy="43" r="1" fill="#FFFFFF"/>
                    <circle cx="47" cy="43" r="1" fill="#FFFFFF"/>
                    <!-- Burun -->
                    <ellipse cx="35" cy="55" rx="4" ry="3" fill="#E8A5C4" stroke="#D480A8" stroke-width="1"/>
                    <!-- Ağız (gülümseme) -->
                    <path d="M25 65 Q35 72 45 65" stroke="#8FB8E8" stroke-width="2.5" fill="none"/>
                    <!-- Dil -->
                    <ellipse cx="35" cy="70" rx="6" ry="3" fill="#F4C2C2"/>
                    <!-- Kuyruk (sallanıyor) -->
                    <path d="M85 75 Q100 60 95 45 Q90 30 100 20" stroke="#8FB8E8" stroke-width="6" fill="none"/>
                    <!-- Bacaklar -->
                    <ellipse cx="25" cy="105" rx="8" ry="15" fill="#C6DBF2" stroke="#8FB8E8" stroke-width="1.5"/>
                    <ellipse cx="45" cy="105" rx="8" ry="15" fill="#C6DBF2" stroke="#8FB8E8" stroke-width="1.5"/>
                    <ellipse cx="65" cy="105" rx="8" ry="15" fill="#C6DBF2" stroke="#8FB8E8" stroke-width="1.5"/>
                    <ellipse cx="85" cy="105" rx="8" ry="15" fill="#C6DBF2" stroke="#8FB8E8" stroke-width="1.5"/>
                    <!-- Pati detayları -->
                    <ellipse cx="25" cy="118" rx="6" ry="3" fill="#E8A5C4"/>
                    <ellipse cx="45" cy="118" rx="6" ry="3" fill="#E8A5C4"/>
                    <ellipse cx="65" cy="118" rx="6" ry="3" fill="#E8A5C4"/>
                    <ellipse cx="85" cy="118" rx="6" ry="3" fill="#E8A5C4"/>
                </g>
            </g>
            
            <!-- Sevimli Kedi (Sağ üst) - Pastel Yeşil -->
            <g class="hero-float" style="animation-delay: 1s;">
                <g transform="translate(550, 60)">
                    <!-- Vücut -->
                    <ellipse cx="50" cy="70" rx="30" ry="20" fill="#C8E6C9" stroke="#A5D6A7" stroke-width="2"/>
                    <!-- Kafa -->
                    <circle cx="50" cy="40" r="20" fill="#DCEDC8" stroke="#A5D6A7" stroke-width="2"/>
                    <!-- Kulaklar (sivri) -->
                    <polygon points="35,25 40,10 45,25" fill="#B8E6B8" stroke="#88C999" stroke-width="1.5"/>
                    <polygon points="55,25 60,10 65,25" fill="#B8E6B8" stroke="#88C999" stroke-width="1.5"/>
                    <!-- İç kulak -->
                    <polygon points="37,22 40,15 43,22" fill="#F4C2C2"/>
                    <polygon points="57,22 60,15 63,22" fill="#F4C2C2"/>
                    <!-- Gözler (büyük ve sevimli) -->
                    <ellipse cx="42" cy="37" rx="5" ry="7" fill="#FFFFFF" stroke="#A5D6A7" stroke-width="1"/>
                    <ellipse cx="58" cy="37" rx="5" ry="7" fill="#FFFFFF" stroke="#A5D6A7" stroke-width="1"/>
                    <ellipse cx="42" cy="39" rx="3" ry="5" fill="#66BB6A"/>
                    <ellipse cx="58" cy="39" rx="3" ry="5" fill="#66BB6A"/>
                    <ellipse cx="43" cy="37" rx="1" ry="2" fill="#FFFFFF"/>
                    <ellipse cx="59" cy="37" rx="1" ry="2" fill="#FFFFFF"/>
                    <!-- Burun (üçgen) -->
                    <polygon points="48,43 52,43 50,47" fill="#E8A5C4" stroke="#D480A8" stroke-width="1"/>
                    <!-- Ağız -->
                    <path d="M50 47 Q45 52 40 50" stroke="#A5D6A7" stroke-width="2" fill="none"/>
                    <path d="M50 47 Q55 52 60 50" stroke="#A5D6A7" stroke-width="2" fill="none"/>
                    <!-- Bıyıklar -->
                    <line x1="25" y1="42" x2="35" y2="40" stroke="#88C999" stroke-width="2"/>
                    <line x1="25" y1="47" x2="35" y2="47" stroke="#88C999" stroke-width="2"/>
                    <line x1="65" y1="40" x2="75" y2="42" stroke="#88C999" stroke-width="2"/>
                    <line x1="65" y1="47" x2="75" y2="47" stroke="#88C999" stroke-width="2"/>
                    <!-- Kuyruk (kıvrık) -->
                    <path d="M20 75 Q10 60 15 45 Q20 30 10 20 Q5 10 15 5" stroke="#A5D6A7" stroke-width="5" fill="none"/>
                    <!-- Bacaklar -->
                    <ellipse cx="35" cy="90" rx="6" ry="12" fill="#D7E8D7" stroke="#A5D6A7" stroke-width="1.5"/>
                    <ellipse cx="50" cy="90" rx="6" ry="12" fill="#D7E8D7" stroke="#A5D6A7" stroke-width="1.5"/>
                    <ellipse cx="65" cy="90" rx="6" ry="12" fill="#D7E8D7" stroke="#A5D6A7" stroke-width="1.5"/>
                    <!-- Pati detayları -->
                    <ellipse cx="35" cy="100" rx="4" ry="2" fill="#E8A5C4"/>
                    <ellipse cx="50" cy="100" rx="4" ry="2" fill="#E8A5C4"/>
                    <ellipse cx="65" cy="100" rx="4" ry="2" fill="#E8A5C4"/>
                </g>
            </g>
            
            <!-- Oyuncu Köpek Yavrusu (Sol alt) - Pastel Mor -->
            <g class="hero-float" style="animation-delay: 2s;">
                <g transform="translate(100, 400)">
                    <!-- Vücut (küçük) -->
                    <ellipse cx="30" cy="50" rx="25" ry="18" fill="#D1C4E9" stroke="#B39DDB" stroke-width="2"/>
                    <!-- Kafa (büyük - yavru oranları) -->
                    <circle cx="30" cy="25" r="20" fill="#E1BEE7" stroke="#B39DDB" stroke-width="2"/>
                    <!-- Kulaklar (yumuşak) -->
                    <ellipse cx="15" cy="15" rx="8" ry="15" fill="#C8A2C8" stroke="#9575CD" stroke-width="1.5"/>
                    <ellipse cx="45" cy="15" rx="8" ry="15" fill="#C8A2C8" stroke="#9575CD" stroke-width="1.5"/>
                    <!-- Gözler (büyük parlak) -->
                    <circle cx="23" cy="22" r="6" fill="#FFFFFF" stroke="#B39DDB" stroke-width="1"/>
                    <circle cx="37" cy="22" r="6" fill="#FFFFFF" stroke="#B39DDB" stroke-width="1"/>
                    <circle cx="23" cy="22" r="4" fill="#7E57C2"/>
                    <circle cx="37" cy="22" r="4" fill="#7E57C2"/>
                    <circle cx="25" cy="20" r="1.5" fill="#FFFFFF"/>
                    <circle cx="39" cy="20" r="1.5" fill="#FFFFFF"/>
                    <!-- Burun -->
                    <circle cx="30" cy="30" r="3" fill="#E8A5C4" stroke="#D480A8" stroke-width="1"/>
                    <!-- Ağız (şaşkın/mutlu) -->
                    <ellipse cx="30" cy="35" rx="5" ry="3" fill="#F4C2C2" stroke="#B39DDB" stroke-width="1"/>
                    <!-- Kuyruk (heyecanlı) -->
                    <path d="M55 45 Q70 35 65 20 Q60 5 70 -5" stroke="#B39DDB" stroke-width="5" fill="none"/>
                    <!-- Bacaklar -->
                    <ellipse cx="18" cy="68" rx="5" ry="10" fill="#E0D2F2" stroke="#B39DDB" stroke-width="1.5"/>
                    <ellipse cx="30" cy="68" rx="5" ry="10" fill="#E0D2F2" stroke="#B39DDB" stroke-width="1.5"/>
                    <ellipse cx="42" cy="68" rx="5" ry="10" fill="#E0D2F2" stroke="#B39DDB" stroke-width="1.5"/>
                    <!-- Pati detayları -->
                    <ellipse cx="18" cy="76" rx="3" ry="2" fill="#E8A5C4"/>
                    <ellipse cx="30" cy="76" rx="3" ry="2" fill="#E8A5C4"/>
                    <ellipse cx="42" cy="76" rx="3" ry="2" fill="#E8A5C4"/>
                </g>
            </g>
            
            <!-- Uyuyan Kedi (Sağ alt) - Pastel Pembe -->
            <g class="hero-float" style="animation-delay: 3s;">
                <g transform="translate(580, 420)">
                    <!-- Vücut (yuvarlak) -->
                    <ellipse cx="40" cy="50" rx="35" ry="25" fill="#F8BBD9" stroke="#F48FB1" stroke-width="2"/>
                    <!-- Kafa -->
                    <ellipse cx="40" cy="30" rx="18" ry="15" fill="#FCE4EC" stroke="#F48FB1" stroke-width="2"/>
                    <!-- Kulaklar -->
                    <polygon points="25,20 30,8 35,20" fill="#F5A9D0" stroke="#EC407A" stroke-width="1.5"/>
                    <polygon points="45,20 50,8 55,20" fill="#F5A9D0" stroke="#EC407A" stroke-width="1.5"/>
                    <!-- İç kulak -->
                    <polygon points="27,17 30,12 33,17" fill="#F4C2C2"/>
                    <polygon points="47,17 50,12 53,17" fill="#F4C2C2"/>
                    <!-- Gözler (kapalı) -->
                    <path d="M32 28 Q37 25 42 28" stroke="#EC407A" stroke-width="2" fill="none"/>
                    <path d="M38 28 Q43 25 48 28" stroke="#EC407A" stroke-width="2" fill="none"/>
                    <!-- Kirpikler -->
                    <line x1="30" y1="26" x2="29" y2="24" stroke="#EC407A" stroke-width="1"/>
                    <line x1="34" y1="25" x2="33" y2="23" stroke="#EC407A" stroke-width="1"/>
                    <line x1="46" y1="25" x2="47" y2="23" stroke="#EC407A" stroke-width="1"/>
                    <line x1="50" y1="26" x2="51" y2="24" stroke="#EC407A" stroke-width="1"/>
                    <!-- Burun -->
                    <polygon points="38,32 42,32 40,35" fill="#E8A5C4" stroke="#D480A8" stroke-width="1"/>
                    <!-- Ağız (memnun) -->
                    <path d="M40 35 Q35 38 32 36" stroke="#F48FB1" stroke-width="1.5" fill="none"/>
                    <path d="M40 35 Q45 38 48 36" stroke="#F48FB1" stroke-width="1.5" fill="none"/>
                    <!-- Kuyruk (sakin) -->
                    <path d="M75 55 Q85 45 80 35" stroke="#F48FB1" stroke-width="4" fill="none"/>
                    <!-- Bacaklar (toplu) -->
                    <ellipse cx="25" cy="65" rx="8" ry="6" fill="#FADDE1" stroke="#F48FB1" stroke-width="1.5"/>
                    <ellipse cx="55" cy="65" rx="8" ry="6" fill="#FADDE1" stroke="#F48FB1" stroke-width="1.5"/>
                </g>
            </g>
            
            <!-- Pastel Kalpler -->
            <g class="hero-pulse">
                <g transform="translate(200, 150)" fill="#E1BEE7" opacity="0.6">
                    <path d="M0 10 C-3 5, -8 5, -8 10 C-8 5, -13 5, -10 10 C-10 15, 0 20, 0 20 C0 20, 10 15, 10 10 C13 5, 8 5, 8 10 C8 5, 3 5, 0 10 Z"/>
                </g>
                <g transform="translate(400, 300)" fill="#C8E6C9" opacity="0.6">
                    <path d="M0 8 C-2 4, -6 4, -6 8 C-6 4, -10 4, -8 8 C-8 12, 0 16, 0 16 C0 16, 8 12, 8 8 C10 4, 6 4, 6 8 C6 4, 2 4, 0 8 Z"/>
                </g>
                <g transform="translate(600, 200)" fill="#F8BBD9" opacity="0.6">
                    <path d="M0 6 C-2 3, -4 3, -4 6 C-4 3, -6 3, -5 6 C-5 9, 0 12, 0 12 C0 12, 5 9, 5 6 C6 3, 4 3, 4 6 C4 3, 2 3, 0 6 Z"/>
                </g>
            </g>
            
            <!-- Pastel Oyun Kemikleri -->
            <g class="hero-float" style="animation-delay: 4s;">
                <g transform="translate(150, 350)" fill="#B8D4F0" opacity="0.7">
                    <ellipse cx="0" cy="0" rx="15" ry="3"/>
                    <circle cx="-12" cy="0" r="5"/>
                    <circle cx="12" cy="0" r="5"/>
                    <circle cx="-12" cy="-3" r="2" fill="#D1E7F7"/>
                    <circle cx="-12" cy="3" r="2" fill="#D1E7F7"/>
                    <circle cx="12" cy="-3" r="2" fill="#D1E7F7"/>
                    <circle cx="12" cy="3" r="2" fill="#D1E7F7"/>
                </g>
                <g transform="translate(500, 120) rotate(30)" fill="#C8E6C9" opacity="0.7">
                    <ellipse cx="0" cy="0" rx="12" ry="2"/>
                    <circle cx="-10" cy="0" r="4"/>
                    <circle cx="10" cy="0" r="4"/>
                    <circle cx="-10" cy="-2" r="1.5" fill="#DCEDC8"/>
                    <circle cx="-10" cy="2" r="1.5" fill="#DCEDC8"/>
                    <circle cx="10" cy="-2" r="1.5" fill="#DCEDC8"/>
                    <circle cx="10" cy="2" r="1.5" fill="#DCEDC8"/>
                </g>
            </g>
            
            <!-- Pastel Yuva/Ev Simgesi -->
            <g class="hero-pulse" style="animation-delay: 1.5s;">
                <g transform="translate(350, 80)" fill="#E1BEE7" opacity="0.8">
                    <!-- Ev -->
                    <polygon points="0,20 -15,35 15,35" fill="#E1BEE7" stroke="#B39DDB" stroke-width="2"/>
                    <rect x="-10" y="35" width="20" height="15" fill="#F3E5F5" stroke="#B39DDB" stroke-width="1.5"/>
                    <!-- Kapı -->
                    <rect x="-3" y="42" width="6" height="8" fill="#F8BBD9" stroke="#F48FB1" stroke-width="1"/>
                    <!-- Pencere -->
                    <rect x="5" y="38" width="4" height="4" fill="#B8D4F0" stroke="#8FB8E8" stroke-width="1"/>
                    <!-- Çiçekler -->
                    <circle cx="-8" cy="50" r="2" fill="#C8E6C9"/>
                    <circle cx="8" cy="50" r="2" fill="#F8BBD9"/>
                    <!-- Kalp (yuva sevgisi) -->
                    <g transform="translate(0, 45)">
                        <path d="M0 3 C-1 1, -3 1, -3 3 C-3 1, -5 1, -4 3 C-4 5, 0 7, 0 7 C0 7, 4 5, 4 3 C5 1, 3 1, 3 3 C3 1, 1 1, 0 3 Z" fill="#E8A5C4"/>
                    </g>
                </g>
            </g>
        </svg>
    </div>
    
    <!-- Ana İçerik -->
    <div class="z-10 max-w-4xl p-8 mx-auto relative hero-float">
        <!-- Çerçeve Efekti -->
        <div class="absolute inset-0 bg-white bg-opacity-20 rounded-3xl glass-effect border border-white border-opacity-40 shadow-2xl"></div>
        
        <div class="relative z-10">
            <div class="relative flex flex-col items-center justify-center">
                <!-- Pati izleriyle çevrili başlık -->
                <div class="relative inline-block">
                    <!-- Üst pati izleri -->
                    <svg class="absolute -top-10 -left-16 w-16 h-16 opacity-90" viewBox="0 0 60 60"><g><ellipse cx="30" cy="40" rx="14" ry="10" fill="#4A90E2"/><circle cx="12" cy="20" r="7" fill="#4A90E2"/><circle cx="48" cy="20" r="7" fill="#4A90E2"/><circle cx="20" cy="10" r="5" fill="#4A90E2"/><circle cx="40" cy="10" r="5" fill="#4A90E2"/></g></svg>
                    <svg class="absolute -top-10 -right-16 w-16 h-16 opacity-90" viewBox="0 0 60 60"><g><ellipse cx="30" cy="40" rx="14" ry="10" fill="#F687B3"/><circle cx="12" cy="20" r="7" fill="#F687B3"/><circle cx="48" cy="20" r="7" fill="#F687B3"/><circle cx="20" cy="10" r="5" fill="#F687B3"/><circle cx="40" cy="10" r="5" fill="#F687B3"/></g></svg>
                    <!-- Sol pati -->
                    <svg class="absolute top-8 -left-20 w-12 h-12 opacity-80" viewBox="0 0 60 60"><g><ellipse cx="30" cy="40" rx="12" ry="8" fill="#48BB78"/><circle cx="12" cy="20" r="6" fill="#48BB78"/><circle cx="48" cy="20" r="6" fill="#48BB78"/><circle cx="20" cy="10" r="4" fill="#48BB78"/><circle cx="40" cy="10" r="4" fill="#48BB78"/></g></svg>
                    <!-- Sağ pati -->
                    <svg class="absolute top-8 -right-20 w-12 h-12 opacity-80" viewBox="0 0 60 60"><g><ellipse cx="30" cy="40" rx="12" ry="8" fill="#B39DDB"/><circle cx="12" cy="20" r="6" fill="#B39DDB"/><circle cx="48" cy="20" r="6" fill="#B39DDB"/><circle cx="20" cy="10" r="4" fill="#B39DDB"/><circle cx="40" cy="10" r="4" fill="#B39DDB"/></g></svg>
                    <!-- Alt pati izleri -->
                    <svg class="absolute -bottom-10 left-1/2 -translate-x-1/2 w-20 h-12 opacity-80" viewBox="0 0 60 40"><g><ellipse cx="30" cy="30" rx="16" ry="8" fill="#E1BEE7"/><circle cx="12" cy="10" r="6" fill="#E1BEE7"/><circle cx="48" cy="10" r="6" fill="#E1BEE7"/><circle cx="20" cy="2" r="4" fill="#E1BEE7"/><circle cx="40" cy="2" r="4" fill="#E1BEE7"/></g></svg>
                    <h1 class="text-7xl font-extrabold mb-6 drop-shadow-2xl tracking-wide px-12 py-4 bg-white bg-opacity-80 rounded-3xl border-4 border-blue-300 relative z-10 shadow-xl">
                        <span class="gradient-text text-blue-700">YUVA ARAYANLAR</span>
                    </h1>
                </div>
                <p class="text-2xl mb-8 drop-shadow-lg leading-relaxed font-medium text-gray-700 bg-white bg-opacity-70 px-6 py-3 rounded-xl border-2 border-pink-200 mt-2">
                    Onlara <span class="text-blue-500 font-bold">aşk</span>, <span class="text-pink-500 font-bold">dost arkadaş</span>, <span class="text-green-500 font-bold">mutluluk</span>.<br>
                    Siz de onların <span class="text-purple-500 font-bold text-3xl">şansı</span> olabilirsiniz.
                </p>
            </div>
        </div>
        
        <!-- Arama Formu -->
        <div class="relative z-10 mt-8">
            <div class="bg-white bg-opacity-25 glass-effect rounded-2xl p-6 border border-white border-opacity-40 shadow-2xl">
                <form action="index.php" method="GET" class="flex flex-col md:flex-row gap-4 items-center justify-center">
                    <div class="w-full md:w-1/3 relative">
                        <label for="pet_keyword" class="sr-only">Hangi Pet?</label>
                        <div class="relative">
                            <i class="fas fa-paw absolute left-3 top-1/2 transform -translate-y-1/2 text-blue-400 text-lg"></i>
                            <input type="text" id="pet_keyword" name="pet_keyword" placeholder="Hangi Pet? (Örn: Kedi, Köpek)" 
                                   value="<?= htmlspecialchars($pet_keyword) ?>"
                                   class="w-full pl-12 p-4 border-0 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-200 focus:ring-opacity-50 text-gray-700 bg-white bg-opacity-95 glass-effect transition-all duration-300 hover:shadow-xl">
                        </div>
                    </div>
                    <div class="w-full md:w-1/3 relative">
                        <label for="city_keyword" class="sr-only">Hangi Şehir?</label>
                        <div class="relative">
                            <i class="fas fa-map-marker-alt absolute left-3 top-1/2 transform -translate-y-1/2 text-green-400 text-lg"></i>
                            <input type="text" id="city_keyword" name="city_keyword" placeholder="Hangi Şehir? (Örn: İstanbul)" 
                                   value="<?= htmlspecialchars($city_keyword) ?>"
                                   class="w-full pl-12 p-4 border-0 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-200 focus:ring-opacity-50 text-gray-700 bg-white bg-opacity-95 glass-effect transition-all duration-300 hover:shadow-xl">
                        </div>
                    </div>
                    <button type="submit" class="w-full md:w-auto bg-gradient-to-r from-blue-300 to-purple-300 hover:from-blue-400 hover:to-purple-400 text-white font-bold py-4 px-8 rounded-xl shadow-xl transition-all duration-300 transform hover:scale-105 hover:shadow-2xl flex items-center justify-center">
                        <i class="fas fa-search mr-3 text-lg"></i>
                        <span class="text-lg font-semibold">Ara</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<main class="container mx-auto mt-12 px-4 py-8"> 
    <section class="mb-12">
        <h2 class="text-3xl font-extrabold text-pink-400 mb-8 text-center">
            <i class="fas fa-paw mr-3"></i>Aktif İlanlar <?= !empty($pet_keyword) || !empty($city_keyword) ? 'için Arama Sonuçları' : '' ?>
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
            <?php if ($result_aktif_ilanlar && $result_aktif_ilanlar->num_rows > 0): ?>
                <?php while ($ilan = $result_aktif_ilanlar->fetch_assoc()): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden transform hover:scale-105 transition duration-300 border-t-4 
                         <?php 
                            // Kategoriye göre renk ataması (örnek)
                            if ($ilan['kategori_ad'] == 'Kedi') {
                                echo 'border-pink-300';
                            } elseif ($ilan['kategori_ad'] == 'Köpek') {
                                echo 'border-green-300';
                            } else {
                                echo 'border-purple-300'; // Varsayılan renk
                            }
                         ?>">
                        <img src="uploads/<?= htmlspecialchars($ilan['foto'] ?: 'placeholder.jpg') ?>" 
                             alt="<?= htmlspecialchars($ilan['baslik']) ?>" 
                             class="w-full h-48 object-cover">
                        <div class="p-6">
                            <h3 class="text-xl font-bold text-gray-800 mb-2"><?= htmlspecialchars($ilan['baslik']) ?></h3>
                            <div class="mb-4 text-sm text-gray-600">
                                <?php if (!empty($ilan['kategori_ad'])): ?>
                                    <span class="inline-flex items-center bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-xs font-semibold mr-2 mb-1">
                                        <i class="fas fa-folder mr-1"></i><?= htmlspecialchars($ilan['kategori_ad']) ?>
                                    </span>
                                <?php endif; ?>
                                <?php if (!empty($ilan['cins_ad'])): ?>
                                    <span class="inline-flex items-center bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-xs font-semibold mr-2 mb-1">
                                        <i class="fas fa-paw mr-1"></i><?= htmlspecialchars($ilan['cins_ad']) ?>
                                    </span>
                                <?php endif; ?>
                                <?php if (!empty($ilan['il_ad'])): ?>
                                    <span class="inline-flex items-center bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-xs font-semibold mr-2 mb-1">
                                        <i class="fas fa-map-marker-alt mr-1"></i><?= htmlspecialchars($ilan['il_ad']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <a href="ilan_detay.php?id=<?= $ilan['id'] ?>" class="inline-block bg-koyu-pembe hover:bg-pink-700 text-white font-bold py-2 px-4 rounded-md transition duration-300">
                                Detayları Gör <i class="fas fa-chevron-right ml-2 text-sm"></i>
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
                <?php $stmt_aktif_ilanlar->close(); // Statement'ı kapat ?>
            <?php else: ?>
                <div class="col-span-full bg-blue-100 text-blue-800 p-4 rounded-lg text-center">
                    <?= !empty($pet_keyword) || !empty($city_keyword) ? 'Aradığınız kriterlere uygun aktif ilan bulunamadı.' : 'Henüz aktif ilan bulunmamaktadır.' ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="text-center mt-10">
            <a href="ilanlar.php" class="bg-koyu-pembe hover:bg-pink-700 text-white font-bold py-3 px-8 rounded-full shadow-lg transition duration-300 text-lg">
                <i class="fas fa-list-alt mr-2"></i>Tüm İlanları Gör
            </a>
        </div>
    </section>

    <section class="mb-12">
        <h2 class="text-3xl font-extrabold text-green-400 mb-8 text-center">
            <i class="fas fa-heart mr-3"></i>Mutlu Yuvalar: Sahiplenenler
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
            <?php if ($result_sahiplenenler && $result_sahiplenenler->num_rows > 0): ?>
                <?php while ($ilan = $result_sahiplenenler->fetch_assoc()): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden transform hover:scale-105 transition duration-300 border-t-4 border-pink-300">
                        <div class="relative">
                            <img src="uploads/<?= htmlspecialchars($ilan['foto'] ?: 'placeholder.jpg') ?>" 
                                 alt="<?= htmlspecialchars($ilan['baslik']) ?>" 
                                 class="w-full h-48 object-cover">
                            <div class="absolute top-2 right-2 bg-green-500 text-white px-2 py-1 rounded-full text-xs font-bold">
                                <i class="fas fa-heart mr-1"></i>Sahiplendi
                            </div>
                        </div>
                        <div class="p-6">
                            <h3 class="text-xl font-bold text-gray-800 mb-2"><?= htmlspecialchars($ilan['baslik']) ?></h3>
                            <div class="mb-4 text-sm text-gray-600">
                                <?php if (!empty($ilan['kategori_ad'])): ?>
                                    <span class="inline-flex items-center bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-xs font-semibold mr-2 mb-1">
                                        <i class="fas fa-folder mr-1"></i><?= htmlspecialchars($ilan['kategori_ad']) ?>
                                    </span>
                                <?php endif; ?>
                                <?php if (!empty($ilan['cins_ad'])): ?>
                                    <span class="inline-flex items-center bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-xs font-semibold mr-2 mb-1">
                                        <i class="fas fa-paw mr-1"></i><?= htmlspecialchars($ilan['cins_ad']) ?>
                                    </span>
                                <?php endif; ?>
                                <?php if (!empty($ilan['il_ad'])): ?>
                                    <span class="inline-flex items-center bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-xs font-semibold mr-2 mb-1">
                                        <i class="fas fa-map-marker-alt mr-1"></i><?= htmlspecialchars($ilan['il_ad']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Sahiplenen Yorumu -->
                            <?php if ($column_exists && !empty($ilan['sahiplenen_yorumu'])): ?>
                                <div class="mb-4 p-3 bg-green-50 rounded-lg border-l-4 border-green-500">
                                    <p class="text-sm text-green-800 font-semibold mb-1">
                                        <i class="fas fa-quote-left mr-1"></i>
                                        <?= htmlspecialchars($ilan['talep_eden_ad_soyad']) ?> diyor ki:
                                    </p>
                                    <p class="text-sm text-green-700 italic">
                                        "<?= htmlspecialchars(mb_substr($ilan['sahiplenen_yorumu'], 0, 100)) ?><?= strlen($ilan['sahiplenen_yorumu']) > 100 ? '...' : '' ?>"
                                    </p>
                                    <?php if (!empty($ilan['yorum_tarihi'])): ?>
                                        <p class="text-xs text-green-600 mt-2">
                                            <i class="fas fa-calendar mr-1"></i>
                                            <?= date('d.m.Y', strtotime($ilan['yorum_tarihi'])) ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            <?php elseif ($column_exists): ?>
                                <div class="mb-4 p-3 bg-yellow-50 rounded-lg border-l-4 border-yellow-500">
                                    <p class="text-sm text-yellow-700">
                                        <i class="fas fa-hourglass-half mr-1"></i>
                                        Yeni sahiplenen arkadaş henüz deneyimini paylaşmadı.
                                    </p>
                                </div>
                            <?php else: ?>
                                <div class="mb-4 p-3 bg-blue-50 rounded-lg border-l-4 border-blue-500">
                                    <p class="text-sm text-blue-700">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Yorum sistemi için veritabanı güncellemesi gerekiyor.
                                    </p>
                                </div>
                            <?php endif; ?>
                            
                            <a href="ilan_detay.php?id=<?= $ilan['id'] ?>" class="text-pink-400 hover:text-pink-500 hover:underline font-semibold flex items-center">
                                Detayları Gör <i class="fas fa-arrow-right ml-2 text-sm"></i>
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-span-full bg-blue-100 text-blue-800 p-4 rounded-lg text-center">
                    Henüz sahiplenen hayvan bulunmamaktadır.
                </div>
            <?php endif; ?>
        </div>
        <div class="text-center mt-10">
            <a href="sahiplenenler.php" class="bg-koyu-yesil hover:bg-green-700 text-white font-bold py-3 px-8 rounded-full shadow-lg transition duration-300 text-lg">
                <i class="fas fa-eye mr-2"></i>Tüm Mutlu Hikayeleri Gör
            </a>
        </div>
    </section>

</main>


<section class="bg-soluk-mavi py-12 mt-12 border-t border-b border-gray-200"> 
    <div class="container mx-auto px-4 flex-grow pt-20"> 
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-12 items-start"> 
            <div class="bg-white rounded-xl shadow-xl p-8 flex flex-col h-full"> 
                <h2 class="text-3xl font-extrabold mb-6 text-pink-400 text-center"> 
                    <i class="fas fa-paw mr-3"></i>Hayvan Sahiplendirme: Bir Dostluğa Adım Atın
                </h2>
                <p class="text-gray-700 leading-relaxed text-lg mb-6"> 
                    Evlat edinmek, bir canlının hayatını kurtarmanın ve ona sonsuz sevgi dolu bir yuva sağlamanın en güzel yollarından biridir. Sitemiz, yuva arayan masum hayvanlar ile onlara kucak açmak isteyen sorumluluk sahibi bireyleri bir araya getirme misyonuyla kurulmuştur. Burada sadece bir hayvan sahiplenmekle kalmayacak, aynı zamanda hayatınıza neşe, sadakat ve koşulsuz sevgi katacaksınız. Unutmayın, bir hayvan sahiplendiğinizde, sadece ona bir yuva vermekle kalmaz, aynı zamanda kendinize de eşsiz bir arkadaş kazandırırsınız. Bu, karşılıklı sevgi ve bağlılık üzerine kurulu, ömür boyu sürecek bir dostluğun başlangıcıdır.
                </p>
                <hr class="my-6 border-t border-gray-300"> 
                <div class="text-left flex-grow"> 
                    <h4 class="text-xl font-bold mb-4 text-green-400"> 
                        <i class="fas fa-hand-holding-heart mr-3"></i>Neden Sahiplenmelisiniz?
                    </h4>
                    <ul class="list-none p-0 mb-6 space-y-3"> 
                        <li class="flex items-start text-gray-700 text-lg"> 
                            <i class="fas fa-check-circle text-green-400 mr-3 mt-1"></i> 
                            <span class="flex-1">**Bir Hayatı Kurtarırsınız:** Barınaklarda ve sokaklarda zor durumda olan hayvanlara ikinci bir şans vererek onların yaşam kalitesini artırırsınız.</span>
                        </li>
                        <li class="flex items-start text-gray-700 text-lg">
                            <i class="fas fa-check-circle text-green-400 mr-3 mt-1"></i>
                            <span class="flex-1">**Sürdürülebilir Yaklaşımı Desteklersiniz:** Hayvan istismarına, yasa dışı üretime ve pet shop ticaretine karşı durarak daha etik bir dünya için adım atmış olursunuz.</span>
                        </li>
                        <li class="flex items-start text-gray-700 text-lg">
                            <i class="fas fa-check-circle text-green-400 mr-3 mt-1"></i>
                            <span class="flex-1">**Koşulsuz Sevgi Kazanırsınız:** Bir hayvanın size vereceği sevgi saf, karşılıksız ve eşsizdir. Onlar, en zor zamanlarınızda bile yanınızda olan gerçek dostlardır.</span>
                        </li>
                        <li class="flex items-start text-gray-700 text-lg">
                            <i class="fas fa-check-circle text-green-400 mr-3 mt-1"></i>
                            <span class="flex-1">**Topluma Katkı Sağlarsınız:** Sahiplenme bilincini yayarak diğer insanlara da ilham verir ve hayvan refahı konusunda duyarlı bir toplumun gelişimine katkıda bulunursunuz.</span>
                        </li>
                        <li class="flex items-start text-gray-700 text-lg">
                            <i class="fas fa-check-circle text-green-400 mr-3 mt-1"></i>
                            <span class="flex-1">**Maddi Tasarruf Edersiniz:** Genellikle bir pet shoptan satın almaya kıyasla daha uygun maliyetli olabilir ve kısırlaştırma gibi bazı temel sağlık hizmetleri tamamlanmış olabilir.</span>
                        </li>
                    </ul>
                    <h4 class="text-xl font-bold mb-4 mt-6 text-green-400">
                        <i class="fas fa-book mr-3"></i>Sahiplenme Süreci ve Bilinmesi Gerekenler
                    </h4>
                    <p class="text-gray-700 leading-relaxed text-lg mb-6">
                        Sahiplenme kararınız ciddi bir sorumluluk gerektirir. Sitemizdeki ilanları inceleyerek size uygun dostu bulduktan sonra, ilan sahibi ile iletişime geçerek detaylı bilgi alabilirsiniz. Hayvanın sağlık durumu, karakteri, geçmişi, aşıları, kısırlaştırma durumu ve özel ihtiyaçları hakkında bilgi edinmek, doğru kararı vermeniz için hayati önem taşır. Lütfen sadece sevimli oldukları için değil, bir canlının tüm ihtiyaçlarını (barınma, beslenme, sağlık, sevgi, ilgi, oyun) karşılayabileceğinizden emin olarak sahiplenme kararı alın.
                    </p>
                    <p class="text-gray-700 leading-relaxed text-lg mb-6">
                        Evcil hayvanınızın barınma, yaşına ve türüne uygun kaliteli beslenme, düzenli veteriner sağlık kontrolleri (yıllık aşılar, parazit tedavileri), eğitim ve sosyalleşme gibi temel ihtiyaçlarını karşılamayı taahhüt etmelisiniz. Unutmayın, hayvanlar duygusal varlıklardır ve sahiplendikten sonra terk edilmek onlar için büyük bir travmadır. Bu nedenle, hayatınızdaki potansiyel değişiklikleri (taşınma, iş değişikliği, aile büyüklüğü vb.) göz önünde bulundurarak uzun vadeli bir plan yapmanız önemlidir. Özellikle evden uzun süre ayrı kalacak kişiler, hayvanlarını emanet edebilecekleri güvenli yerler veya kişiler hakkında önceden planlama yapmalıdır.
                    </p>
                    <h4 class="text-xl font-bold mb-4 mt-6 text-green-400">
                        <i class="fas fa-heartbeat mr-3"></i>Sağlık ve Bakım
                    </h4>
                    <p class="text-gray-700 leading-relaxed text-lg mb-6">
                        Sahiplendiğiniz hayvanın düzenli veteriner kontrolünden geçmesi, aşılarının tam olması ve parazitlere karşı korunması gerekmektedir. Ayrıca, hayvanınızın yaşına, türüne ve sağlık durumuna uygun mama seçimi, yeterli su temini ve temiz bir yaşam alanı sağlamak da sizin sorumluluğunuzdadır. Hayvanınızla kaliteli zaman geçirmek, onunla oynamak ve ona ilgi göstermek, hem sizin hem de onun ruh sağlığı için çok değerlidir. Hayvanlar, düzenli egzersiz ve mental uyarım ile daha mutlu ve sağlıklı olurlar. Herhangi bir sağlık veya davranışsal sorunla karşılaştığınızda, profesyonel bir veterinerden veya hayvan davranış uzmanından yardım almaktan çekinmeyin.
                    </p>
                    <p class="text-right italic text-gray-600 text-lg mt-6">
                        "Bir hayvanın kalbini kazanırsanız, hayatınızın en saf sevgisini de kazanmış olursunuz."
                    </p>
                </div>
                <a href="ilan_ekle.php" class="mt-8 self-center bg-koyu-pembe hover:bg-pink-700 text-white font-bold py-3 px-8 rounded-full text-xl transition duration-300">
                    <i class="fas fa-plus-circle mr-3"></i>Siz de Bir İlan Verin!
                </a>
            </div>

            <div class="bg-white rounded-xl shadow-xl p-8 flex flex-col h-full">
                <h2 class="text-3xl font-extrabold mb-6 text-green-400 text-center">
                    <i class="fas fa-lightbulb mr-3"></i>Hayvan Dünyasından İlginç Bilgiler
                </h2>
                <div class="text-left flex-grow">
                    <ul class="list-none p-0 space-y-6">
                        <li class="mb-4">
                            <h5 class="text-xl font-bold text-pink-400 mb-2">
                                <i class="fas fa-info-circle mr-3"></i>Kediler ve Su
                            </h5>
                            <p class="text-gray-700 leading-relaxed text-lg">
                                Kedilerin çoğu sudan hoşlanmaz. Bunun nedeni, kürklerinin suya maruz kaldığında ağırlaşması ve kurumalarının uzun sürmesidir. Ancak bazı kediler, özellikle Van kedileri, suya düşkündür.
                            </p>
                        </li>
                        <li class="mb-4">
                            <h5 class="text-xl font-bold text-pink-400 mb-2">
                                <i class="fas fa-info-circle mr-3"></i>Köpeklerin Burun İzleri
                            </h5>
                            <p class="text-gray-700 leading-relaxed text-lg">
                                Tıpkı insanların parmak izleri gibi, köpeklerin burun izleri de benzersizdir ve kimlik tespiti için kullanılabilir. Bu, her köpeğin kendine özgü bir kimliğe sahip olduğu anlamına gelir.
                            </p>
                        </li>
                        <li class="mb-4">
                            <h5 class="text-xl font-bold text-pink-400 mb-2">
                                <i class="fas fa-info-circle mr-3"></i>Papağanların Konuşma Yeteneği
                            </h5>
                            <p class="text-gray-700 leading-relaxed text-lg">
                                Papağanlar, sadece sesleri taklit etmekle kalmaz, bazı türler kelimelerin anlamlarını da öğrenerek bağlamına uygun kullanabilirler. Bu, onların karmaşık bilişsel yeteneklerini gösterir.
                            </p>
                        </li>
                        <li class="mb-4">
                            <h5 class="text-xl font-bold text-pink-400 mb-2">
                                <i class="fas fa-info-circle mr-3"></i>Balıkların Hafızası
                            </h5>
                            <p class="text-gray-700 leading-relaxed text-lg">
                                Yaygın inanışın aksine, balıkların hafızası sadece birkaç saniye değildir. Bilimsel çalışmalar, balıkların aylarca süren olayları hatırlayabildiğini ve hatta karmaşık görevleri öğrenebildiğini göstermektedir.
                            </p>
                        </li>
                    </ul>
                </div>
                <a href="#" class="mt-8 self-center bg-gray-400 text-white font-bold py-3 px-8 rounded-full text-xl cursor-not-allowed">
                    <i class="fas fa-newspaper mr-3"></i>Tüm Haberler / Bilgiler (Yakında)
                </a>
            </div>
        </div>
    </div>
</section>

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