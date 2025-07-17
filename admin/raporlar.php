<?php
// admin/raporlar.php - Detaylı raporlar ve istatistikler
session_start();
include("../includes/auth.php");
include("../includes/db.php");

// Genel İstatistikler
$stats = [];

// Toplam İlan Sayısı
$result = $conn->query("SELECT COUNT(*) as toplam FROM ilanlar");
$stats['toplam_ilan'] = $result->fetch_assoc()['toplam'];

// Aktif İlan Sayısı
$result = $conn->query("SELECT COUNT(*) as aktif FROM ilanlar WHERE durum = 'Aktif'");
$stats['aktif_ilan'] = $result->fetch_assoc()['aktif'];

// Sahiplenilmiş İlan Sayısı
$result = $conn->query("SELECT COUNT(*) as sahiplenilmis FROM ilanlar WHERE durum = 'sahiplenildi'");
$stats['sahiplenilmis_ilan'] = $result->fetch_assoc()['sahiplenilmis'];

// Toplam Kullanıcı Sayısı
$result = $conn->query("SELECT COUNT(*) as toplam FROM kullanicilar WHERE kullanici_tipi = 'normal'");
$stats['toplam_kullanici'] = $result->fetch_assoc()['toplam'];

// Toplam Sahiplenme Talebi
$result = $conn->query("SELECT COUNT(*) as toplam FROM sahiplenme_istekleri");
$stats['toplam_talep'] = $result->fetch_assoc()['toplam'];

// Tamamlanmış Sahiplenme Talebi
$result = $conn->query("SELECT COUNT(*) as tamamlanan FROM sahiplenme_istekleri WHERE durum = 'tamamlandı'");
$stats['tamamlanan_talep'] = $result->fetch_assoc()['tamamlanan'];

// Kategoriye Göre İlan Dağılımı
$kategori_dagilimi = [];
$result = $conn->query("SELECT k.ad as kategori, COUNT(i.id) as ilan_sayisi FROM kategoriler k LEFT JOIN ilanlar i ON k.id = i.kategori_id GROUP BY k.id, k.ad ORDER BY ilan_sayisi DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $kategori_dagilimi[] = $row;
    }
}

// En Çok Favorilenen İlanlar
$favori_ilanlar = [];
$result = $conn->query("SELECT i.id, i.baslik, i.foto, COUNT(f.id) as favori_sayisi FROM ilanlar i LEFT JOIN favoriler f ON i.id = f.ilan_id WHERE f.id IS NOT NULL GROUP BY i.id ORDER BY favori_sayisi DESC LIMIT 10");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $favori_ilanlar[] = $row;
    }
}

// Aylık İlan Trendi (Son 12 ay)
$aylik_trend = [];
$result = $conn->query("SELECT DATE_FORMAT(tarih, '%Y-%m') as ay, COUNT(*) as ilan_sayisi FROM ilanlar WHERE tarih >= DATE_SUB(NOW(), INTERVAL 12 MONTH) GROUP BY ay ORDER BY ay DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $aylik_trend[] = $row;
    }
}

// Şehir Bazında İlan Dağılımı
$sehir_dagilimi = [];
$result = $conn->query("SELECT il.ad as sehir, COUNT(i.id) as ilan_sayisi FROM il il LEFT JOIN ilanlar i ON il.id = i.il_id GROUP BY il.id, il.ad HAVING ilan_sayisi > 0 ORDER BY ilan_sayisi DESC LIMIT 15");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $sehir_dagilimi[] = $row;
    }
}

// Sahiplenme Başarı Oranları
$basari_oranlari = [];
$result = $conn->query("SELECT 
    COUNT(CASE WHEN durum = 'tamamlandı' THEN 1 END) as tamamlanan,
    COUNT(CASE WHEN durum = 'onaylandı' THEN 1 END) as onaylanan,
    COUNT(CASE WHEN durum = 'reddedildi' THEN 1 END) as reddedilen,
    COUNT(CASE WHEN durum = 'beklemede' THEN 1 END) as beklemede,
    COUNT(*) as toplam
FROM sahiplenme_istekleri");
$basari_oranlari = $result->fetch_assoc();

// Bilgilendirme İstatistikleri
$bilgilendirme_stats = [];
$result = $conn->query("SELECT COUNT(*) as toplam FROM bilgilendirmeler");
if ($result) {
    $bilgilendirme_stats['toplam'] = $result->fetch_assoc()['toplam'];
} else {
    $bilgilendirme_stats['toplam'] = 0;
}

// Bilgi türlerine göre dağılım
$bilgi_turu_dagilimi = [];
$result = $conn->query("SELECT bilgi_turu, COUNT(*) as sayi FROM bilgilendirmeler GROUP BY bilgi_turu ORDER BY sayi DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $bilgi_turu_dagilimi[] = $row;
    }
}

// Son bilgilendirmeler
$son_bilgilendirmeler = [];
$result = $conn->query("SELECT b.*, si.talep_eden_ad_soyad, i.baslik as ilan_baslik 
                       FROM bilgilendirmeler b 
                       LEFT JOIN sahiplenme_istekleri si ON b.talep_id = si.id 
                       LEFT JOIN ilanlar i ON si.ilan_id = i.id 
                       ORDER BY b.tarih DESC LIMIT 10");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $son_bilgilendirmeler[] = $row;
    }
}

include("includes/admin_header.php");
?>

<link rel="stylesheet" href="print-styles.css">

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800">
            <i class="fas fa-chart-bar mr-3 text-blue-600"></i>
            Raporlar ve İstatistikler
        </h1>
        <button onclick="printReport()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md">
            <i class="fas fa-print mr-2"></i>Raporu Yazdır
        </button>
    </div>

    <!-- Genel İstatistikler -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <a href="ilan_yonetim.php" class="bg-white border-l-4 border-blue-500 p-6 rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 cursor-pointer">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium uppercase tracking-wider">Toplam İlan</p>
                    <p class="text-3xl font-bold text-gray-800"><?= number_format($stats['toplam_ilan']) ?></p>
                </div>
                <div class="bg-blue-500 p-3 rounded-full hover:bg-blue-600 transition-colors">
                    <i class="fas fa-clipboard-list text-2xl text-white"></i>
                </div>
            </div>
        </a>

        <a href="ilan_yonetim.php" class="bg-white border-l-4 border-green-500 p-6 rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 cursor-pointer">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium uppercase tracking-wider">Aktif İlan</p>
                    <p class="text-3xl font-bold text-gray-800"><?= number_format($stats['aktif_ilan']) ?></p>
                </div>
                <div class="bg-green-500 p-3 rounded-full hover:bg-green-600 transition-colors">
                    <i class="fas fa-eye text-2xl text-white"></i>
                </div>
            </div>
        </a>

        <a href="ilan_yonetim.php" class="bg-white border-l-4 border-purple-500 p-6 rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 cursor-pointer">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium uppercase tracking-wider">Sahiplenilen</p>
                    <p class="text-3xl font-bold text-gray-800"><?= number_format($stats['sahiplenilmis_ilan']) ?></p>
                </div>
                <div class="bg-purple-500 p-3 rounded-full hover:bg-purple-600 transition-colors">
                    <i class="fas fa-heart text-2xl text-white"></i>
                </div>
            </div>
        </a>

        <a href="sahiplenme_talepleri.php" class="bg-white border-l-4 border-orange-500 p-6 rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 cursor-pointer">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium uppercase tracking-wider">Toplam Talep</p>
                    <p class="text-3xl font-bold text-gray-800"><?= number_format($stats['toplam_talep']) ?></p>
                </div>
                <div class="bg-orange-500 p-3 rounded-full hover:bg-orange-600 transition-colors">
                    <i class="fas fa-clipboard-check text-2xl text-white"></i>
                </div>
            </div>
        </a>
    </div>

    <!-- Bilgilendirme İstatistikleri -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white border-l-4 border-indigo-500 p-6 rounded-lg shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium uppercase tracking-wider">Toplam Bilgilendirme</p>
                    <p class="text-3xl font-bold text-gray-800"><?= number_format($bilgilendirme_stats['toplam']) ?></p>
                </div>
                <div class="bg-indigo-500 p-3 rounded-full">
                    <i class="fas fa-bell text-2xl text-white"></i>
                </div>
            </div>
        </div>

        <div class="bg-white border-l-4 border-teal-500 p-6 rounded-lg shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium uppercase tracking-wider">Tamamlanan Talep</p>
                    <p class="text-3xl font-bold text-gray-800"><?= number_format($stats['tamamlanan_talep']) ?></p>
                </div>
                <div class="bg-teal-500 p-3 rounded-full">
                    <i class="fas fa-check-circle text-2xl text-white"></i>
                </div>
            </div>
        </div>

        <div class="bg-white border-l-4 border-pink-500 p-6 rounded-lg shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium uppercase tracking-wider">Başarı Oranı</p>
                    <p class="text-3xl font-bold text-gray-800"><?= $stats['toplam_talep'] > 0 ? number_format(($stats['tamamlanan_talep'] / $stats['toplam_talep']) * 100, 1) : 0 ?>%</p>
                </div>
                <div class="bg-pink-500 p-3 rounded-full">
                    <i class="fas fa-chart-line text-2xl text-white"></i>
                </div>
            </div>
        </div>

        <div class="bg-white border-l-4 border-cyan-500 p-6 rounded-lg shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium uppercase tracking-wider">Bilgi Türü Sayısı</p>
                    <p class="text-3xl font-bold text-gray-800"><?= count($bilgi_turu_dagilimi) ?></p>
                </div>
                <div class="bg-cyan-500 p-3 rounded-full">
                    <i class="fas fa-info-circle text-2xl text-white"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Sahiplenme İstatistikleri -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">
                <i class="fas fa-chart-pie mr-2 text-green-600"></i>
                Sahiplenme Talep Durumları
            </h2>
            <div class="space-y-4">
                <?php 
                $total_requests = $basari_oranlari['toplam'] ?: 1;
                $statuses = [
                    'tamamlanan' => ['label' => 'Tamamlanan', 'color' => 'bg-green-500', 'icon' => 'fa-check-circle'],
                    'onaylanan' => ['label' => 'Onaylanan', 'color' => 'bg-blue-500', 'icon' => 'fa-thumbs-up'],
                    'reddedilen' => ['label' => 'Reddedilen', 'color' => 'bg-red-500', 'icon' => 'fa-times-circle'],
                    'beklemede' => ['label' => 'Beklemede', 'color' => 'bg-yellow-500', 'icon' => 'fa-clock']
                ];
                
                foreach ($statuses as $key => $status):
                    $count = $basari_oranlari[$key];
                    $percentage = ($count / $total_requests) * 100;
                ?>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <i class="fas <?= $status['icon'] ?> text-lg mr-3 text-gray-600"></i>
                            <span class="font-medium text-gray-800"><?= $status['label'] ?></span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="flex-1 bg-gray-200 rounded-full h-2 w-32">
                                <div class="<?= $status['color'] ?> h-2 rounded-full" style="width: <?= $percentage ?>%"></div>
                            </div>
                            <span class="text-sm font-semibold w-16 text-right text-gray-800"><?= number_format($count) ?> (<?= number_format($percentage, 1) ?>%)</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">
                <i class="fas fa-chart-bar mr-2 text-blue-600"></i>
                Kategoriye Göre İlan Dağılımı
            </h2>
            <?php if (!empty($kategori_dagilimi)): ?>
                <div class="space-y-4">
                    <?php foreach ($kategori_dagilimi as $kategori): ?>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-blue-500 rounded-full mr-3"></div>
                                <span class="font-medium text-gray-800"><?= htmlspecialchars($kategori['kategori']) ?></span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <div class="bg-gray-200 rounded-full h-3 w-32">
                                    <div class="bg-blue-500 h-3 rounded-full" style="width: <?= $stats['toplam_ilan'] > 0 ? ($kategori['ilan_sayisi'] / $stats['toplam_ilan']) * 100 : 0 ?>%"></div>
                                </div>
                                <span class="text-sm font-semibold text-gray-800 min-w-[40px]"><?= number_format($kategori['ilan_sayisi']) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-600">Henüz kategori verisi bulunmamaktadır.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Aylık İlan Trendi -->
    <?php if (!empty($aylik_trend)): ?>
    <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
        <h2 class="text-xl font-bold text-gray-800 mb-4">
            <i class="fas fa-chart-line mr-2 text-green-600"></i>
            Aylık İlan Trendi (Son 12 Ay)
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            <?php foreach ($aylik_trend as $ay): ?>
                <div class="bg-gradient-to-r from-green-400 to-green-500 text-white p-4 rounded-lg shadow-md">
                    <div class="text-center">
                        <i class="fas fa-calendar-alt text-2xl mb-2"></i>
                        <h3 class="text-lg font-semibold"><?= date('M Y', strtotime($ay['ay'] . '-01')) ?></h3>
                        <p class="text-2xl font-bold"><?= number_format($ay['ilan_sayisi']) ?></p>
                        <p class="text-sm opacity-90">İlan</p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- En Çok Favorilenen İlanlar -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
        <h2 class="text-xl font-bold text-gray-800 mb-4">
            <i class="fas fa-star mr-2 text-yellow-600"></i>
            En Çok Favorilenen İlanlar
        </h2>
        
        <?php if (!empty($favori_ilanlar)): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($favori_ilanlar as $index => $ilan): ?>
                    <div class="bg-white border border-gray-200 rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                        <div class="relative">
                            <img src="../uploads/<?= htmlspecialchars($ilan['foto']) ?>" 
                                 alt="<?= htmlspecialchars($ilan['baslik']) ?>" 
                                 class="w-full h-48 object-cover">
                            <div class="absolute top-2 left-2 bg-yellow-500 text-white px-2 py-1 rounded-full text-xs font-bold">
                                #<?= $index + 1 ?>
                            </div>
                        </div>
                        <div class="p-4">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2"><?= htmlspecialchars($ilan['baslik']) ?></h3>
                            <div class="flex items-center justify-between">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-star mr-1"></i>
                                    <?= number_format($ilan['favori_sayisi']) ?> favori
                                </span>
                                <a href="../ilan_detay.php?id=<?= $ilan['id'] ?>" target="_blank" class="text-blue-600 hover:text-blue-900 text-sm">
                                    <i class="fas fa-external-link-alt mr-1"></i>
                                    Görüntüle
                                </a>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">ID: <?= $ilan['id'] ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-gray-600">Henüz favorilenen ilan bulunmamaktadır.</p>
        <?php endif; ?>
    </div>

    <!-- Şehir Bazında İlan Dağılımı -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
        <h2 class="text-xl font-bold text-gray-800 mb-4">
            <i class="fas fa-map-marker-alt mr-2 text-red-600"></i>
            Şehir Bazında İlan Dağılımı
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($sehir_dagilimi as $sehir): ?>
                <div class="bg-gray-50 p-4 rounded-lg border">
                    <div class="flex items-center justify-between">
                        <span class="font-medium text-gray-800"><?= htmlspecialchars($sehir['sehir']) ?></span>
                        <span class="text-sm bg-blue-500 text-white px-2 py-1 rounded-full font-semibold">
                            <?= number_format($sehir['ilan_sayisi']) ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Bilgilendirme Türü Dağılımı ve Son Bilgilendirmeler -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Bilgilendirme Türü Dağılımı -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">
                <i class="fas fa-chart-bar mr-2 text-purple-600"></i>
                Bilgilendirme Türü Dağılımı
            </h2>
            <?php if (!empty($bilgi_turu_dagilimi)): ?>
                <div class="space-y-4">
                    <?php foreach ($bilgi_turu_dagilimi as $bilgi_turu): ?>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-purple-500 rounded-full mr-3"></div>
                                <span class="font-medium text-gray-800 capitalize"><?= htmlspecialchars($bilgi_turu['bilgi_turu']) ?></span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <div class="bg-gray-200 rounded-full h-3 w-32">
                                    <div class="bg-purple-500 h-3 rounded-full" style="width: <?= $bilgilendirme_stats['toplam'] > 0 ? ($bilgi_turu['sayi'] / $bilgilendirme_stats['toplam']) * 100 : 0 ?>%"></div>
                                </div>
                                <span class="text-sm font-semibold text-gray-800 min-w-[40px]"><?= number_format($bilgi_turu['sayi']) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-600">Henüz bilgilendirme verisi bulunmamaktadır.</p>
            <?php endif; ?>
        </div>

        <!-- Son Bilgilendirmeler -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">
                <i class="fas fa-bell mr-2 text-indigo-600"></i>
                Son Bilgilendirmeler
            </h2>
            <?php if (!empty($son_bilgilendirmeler)): ?>
                <div class="space-y-4">
                    <?php foreach ($son_bilgilendirmeler as $bilgi): ?>
                        <div class="border-l-4 border-indigo-500 pl-4 py-2">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-indigo-600 capitalize"><?= htmlspecialchars($bilgi['bilgi_turu']) ?></span>
                                <span class="text-xs text-gray-500"><?= date('d.m.Y H:i', strtotime($bilgi['tarih'])) ?></span>
                            </div>
                            <p class="text-sm text-gray-800 mb-1">
                                <strong><?= htmlspecialchars($bilgi['talep_eden_ad_soyad']) ?></strong> - 
                                <?= htmlspecialchars($bilgi['ilan_baslik']) ?>
                            </p>
                            <p class="text-xs text-gray-600"><?= htmlspecialchars(substr($bilgi['mesaj'], 0, 100)) ?>...</p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-600">Henüz bilgilendirme bulunmamaktadır.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function printReport() {
    window.print();
}

// Grafik animasyonları için basit bir fonksiyon
document.addEventListener('DOMContentLoaded', function() {
    // Progress bar animasyonları
    const progressBars = document.querySelectorAll('.bg-blue-500, .bg-green-500, .bg-red-500, .bg-yellow-500');
    progressBars.forEach(bar => {
        const width = bar.style.width;
        bar.style.width = '0%';
        setTimeout(() => {
            bar.style.width = width;
            bar.style.transition = 'width 1s ease-in-out';
        }, 100);
    });
});
</script>

<?php include("includes/admin_footer.php"); ?>