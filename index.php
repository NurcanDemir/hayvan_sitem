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

<div class="relative h-[500px] bg-cover bg-center text-white flex items-center justify-center text-center shadow-lg" 
     style="background-image: url('images/hero-bg.jpg');">
    <div class="absolute inset-0 bg-black bg-opacity-40"></div> <div class="z-10 max-w-3xl p-5 mx-auto">
        <h1 class="text-5xl font-extrabold mb-4 drop-shadow-lg">YUVA ARAYANLAR</h1>
        <p class="text-xl mb-8 drop-shadow-md">Onlara aşk, dost arkadaş, mutluluk. Siz de onların şansı olabilirsiniz.</p>
        
        <form action="index.php" method="GET" class="flex flex-col md:flex-row gap-4 items-center justify-center">
            <div class="w-full md:w-1/3">
                <label for="pet_keyword" class="sr-only">Hangi Pet?</label>
                <input type="text" id="pet_keyword" name="pet_keyword" placeholder="Hangi Pet? (Örn: Kedi, Köpek)" 
                       value="<?= htmlspecialchars($pet_keyword) ?>"
                       class="w-full p-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-koyu-pembe focus:border-koyu-pembe text-gray-700">
            </div>
            <div class="w-full md:w-1/3">
                <label for="city_keyword" class="sr-only">Hangi Şehir?</label>
                <input type="text" id="city_keyword" name="city_keyword" placeholder="Hangi Şehir? (Örn: İstanbul)" 
                       value="<?= htmlspecialchars($city_keyword) ?>"
                       class="w-full p-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-koyu-pembe focus:border-koyu-pembe text-gray-700">
            </div>
            <button type="submit" class="w-full md:w-auto bg-koyu-pembe hover:bg-pink-700 text-white font-bold py-3 px-8 rounded-md shadow-lg transition duration-300 flex items-center justify-center">
                <i class="fas fa-search mr-3"></i>Ara
            </button>
        </form>
    </div>
</div>

<main class="container mx-auto mt-12 px-4 py-8"> 
    <section class="mb-12">
        <h2 class="text-3xl font-extrabold text-koyu-pembe mb-8 text-center">
            <i class="fas fa-paw mr-3"></i>Aktif İlanlar <?= !empty($pet_keyword) || !empty($city_keyword) ? 'için Arama Sonuçları' : '' ?>
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
            <?php if ($result_aktif_ilanlar && $result_aktif_ilanlar->num_rows > 0): ?>
                <?php while ($ilan = $result_aktif_ilanlar->fetch_assoc()): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden transform hover:scale-105 transition duration-300 border-t-4 
                         <?php 
                            // Kategoriye göre renk ataması (örnek)
                            if ($ilan['kategori_ad'] == 'Kedi') {
                                echo 'border-koyu-pembe';
                            } elseif ($ilan['kategori_ad'] == 'Köpek') {
                                echo 'border-acik-yesil';
                            } else {
                                echo 'border-gray-400'; // Varsayılan renk
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
        <h2 class="text-3xl font-extrabold text-koyu-yesil mb-8 text-center">
            <i class="fas fa-heart mr-3"></i>Mutlu Yuvalar: Sahiplenenler
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
            <?php if ($result_sahiplenenler && $result_sahiplenenler->num_rows > 0): ?>
                <?php while ($ilan = $result_sahiplenenler->fetch_assoc()): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden transform hover:scale-105 transition duration-300 border-t-4 border-toz-pembe">
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
                            
                            <a href="ilan_detay.php?id=<?= $ilan['id'] ?>" class="text-koyu-pembe hover:underline font-semibold flex items-center">
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
                <h2 class="text-3xl font-extrabold mb-6 text-koyu-pembe text-center"> 
                    <i class="fas fa-paw mr-3"></i>Hayvan Sahiplendirme: Bir Dostluğa Adım Atın
                </h2>
                <p class="text-gray-700 leading-relaxed text-lg mb-6"> 
                    Evlat edinmek, bir canlının hayatını kurtarmanın ve ona sonsuz sevgi dolu bir yuva sağlamanın en güzel yollarından biridir. Sitemiz, yuva arayan masum hayvanlar ile onlara kucak açmak isteyen sorumluluk sahibi bireyleri bir araya getirme misyonuyla kurulmuştur. Burada sadece bir hayvan sahiplenmekle kalmayacak, aynı zamanda hayatınıza neşe, sadakat ve koşulsuz sevgi katacaksınız. Unutmayın, bir hayvan sahiplendiğinizde, sadece ona bir yuva vermekle kalmaz, aynı zamanda kendinize de eşsiz bir arkadaş kazandırırsınız. Bu, karşılıklı sevgi ve bağlılık üzerine kurulu, ömür boyu sürecek bir dostluğun başlangıcıdır.
                </p>
                <hr class="my-6 border-t border-gray-300"> 
                <div class="text-left flex-grow"> 
                    <h4 class="text-xl font-bold mb-4 text-koyu-yesil"> 
                        <i class="fas fa-hand-holding-heart mr-3"></i>Neden Sahiplenmelisiniz?
                    </h4>
                    <ul class="list-none p-0 mb-6 space-y-3"> 
                        <li class="flex items-start text-gray-700 text-lg"> 
                            <i class="fas fa-check-circle text-acik-yesil mr-3 mt-1"></i> 
                            <span class="flex-1">**Bir Hayatı Kurtarırsınız:** Barınaklarda ve sokaklarda zor durumda olan hayvanlara ikinci bir şans vererek onların yaşam kalitesini artırırsınız.</span>
                        </li>
                        <li class="flex items-start text-gray-700 text-lg">
                            <i class="fas fa-check-circle text-acik-yesil mr-3 mt-1"></i>
                            <span class="flex-1">**Sürdürülebilir Yaklaşımı Desteklersiniz:** Hayvan istismarına, yasa dışı üretime ve pet shop ticaretine karşı durarak daha etik bir dünya için adım atmış olursunuz.</span>
                        </li>
                        <li class="flex items-start text-gray-700 text-lg">
                            <i class="fas fa-check-circle text-acik-yesil mr-3 mt-1"></i>
                            <span class="flex-1">**Koşulsuz Sevgi Kazanırsınız:** Bir hayvanın size vereceği sevgi saf, karşılıksız ve eşsizdir. Onlar, en zor zamanlarınızda bile yanınızda olan gerçek dostlardır.</span>
                        </li>
                        <li class="flex items-start text-gray-700 text-lg">
                            <i class="fas fa-check-circle text-acik-yesil mr-3 mt-1"></i>
                            <span class="flex-1">**Topluma Katkı Sağlarsınız:** Sahiplenme bilincini yayarak diğer insanlara da ilham verir ve hayvan refahı konusunda duyarlı bir toplumun gelişimine katkıda bulunursunuz.</span>
                        </li>
                        <li class="flex items-start text-gray-700 text-lg">
                            <i class="fas fa-check-circle text-acik-yesil mr-3 mt-1"></i>
                            <span class="flex-1">**Maddi Tasarruf Edersiniz:** Genellikle bir pet shoptan satın almaya kıyasla daha uygun maliyetli olabilir ve kısırlaştırma gibi bazı temel sağlık hizmetleri tamamlanmış olabilir.</span>
                        </li>
                    </ul>
                    <h4 class="text-xl font-bold mb-4 mt-6 text-koyu-yesil">
                        <i class="fas fa-book mr-3"></i>Sahiplenme Süreci ve Bilinmesi Gerekenler
                    </h4>
                    <p class="text-gray-700 leading-relaxed text-lg mb-6">
                        Sahiplenme kararınız ciddi bir sorumluluk gerektirir. Sitemizdeki ilanları inceleyerek size uygun dostu bulduktan sonra, ilan sahibi ile iletişime geçerek detaylı bilgi alabilirsiniz. Hayvanın sağlık durumu, karakteri, geçmişi, aşıları, kısırlaştırma durumu ve özel ihtiyaçları hakkında bilgi edinmek, doğru kararı vermeniz için hayati önem taşır. Lütfen sadece sevimli oldukları için değil, bir canlının tüm ihtiyaçlarını (barınma, beslenme, sağlık, sevgi, ilgi, oyun) karşılayabileceğinizden emin olarak sahiplenme kararı alın.
                    </p>
                    <p class="text-gray-700 leading-relaxed text-lg mb-6">
                        Evcil hayvanınızın barınma, yaşına ve türüne uygun kaliteli beslenme, düzenli veteriner sağlık kontrolleri (yıllık aşılar, parazit tedavileri), eğitim ve sosyalleşme gibi temel ihtiyaçlarını karşılamayı taahhüt etmelisiniz. Unutmayın, hayvanlar duygusal varlıklardır ve sahiplendikten sonra terk edilmek onlar için büyük bir travmadır. Bu nedenle, hayatınızdaki potansiyel değişiklikleri (taşınma, iş değişikliği, aile büyüklüğü vb.) göz önünde bulundurarak uzun vadeli bir plan yapmanız önemlidir. Özellikle evden uzun süre ayrı kalacak kişiler, hayvanlarını emanet edebilecekleri güvenli yerler veya kişiler hakkında önceden planlama yapmalıdır.
                    </p>
                    <h4 class="text-xl font-bold mb-4 mt-6 text-koyu-yesil">
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
                <h2 class="text-3xl font-extrabold mb-6 text-koyu-yesil text-center">
                    <i class="fas fa-lightbulb mr-3"></i>Hayvan Dünyasından İlginç Bilgiler
                </h2>
                <div class="text-left flex-grow">
                    <ul class="list-none p-0 space-y-6">
                        <li class="mb-4">
                            <h5 class="text-xl font-bold text-koyu-pembe mb-2">
                                <i class="fas fa-info-circle mr-3"></i>Kediler ve Su
                            </h5>
                            <p class="text-gray-700 leading-relaxed text-lg">
                                Kedilerin çoğu sudan hoşlanmaz. Bunun nedeni, kürklerinin suya maruz kaldığında ağırlaşması ve kurumalarının uzun sürmesidir. Ancak bazı kediler, özellikle Van kedileri, suya düşkündür.
                            </p>
                        </li>
                        <li class="mb-4">
                            <h5 class="text-xl font-bold text-koyu-pembe mb-2">
                                <i class="fas fa-info-circle mr-3"></i>Köpeklerin Burun İzleri
                            </h5>
                            <p class="text-gray-700 leading-relaxed text-lg">
                                Tıpkı insanların parmak izleri gibi, köpeklerin burun izleri de benzersizdir ve kimlik tespiti için kullanılabilir. Bu, her köpeğin kendine özgü bir kimliğe sahip olduğu anlamına gelir.
                            </p>
                        </li>
                        <li class="mb-4">
                            <h5 class="text-xl font-bold text-koyu-pembe mb-2">
                                <i class="fas fa-info-circle mr-3"></i>Papağanların Konuşma Yeteneği
                            </h5>
                            <p class="text-gray-700 leading-relaxed text-lg">
                                Papağanlar, sadece sesleri taklit etmekle kalmaz, bazı türler kelimelerin anlamlarını da öğrenerek bağlamına uygun kullanabilirler. Bu, onların karmaşık bilişsel yeteneklerini gösterir.
                            </p>
                        </li>
                        <li class="mb-4">
                            <h5 class="text-xl font-bold text-koyu-pembe mb-2">
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