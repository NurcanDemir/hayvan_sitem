<?php
session_start();
include("includes/db.php");
$kullanici_id = $_SESSION['kullanici_id'] ?? 0;

$sql = "SELECT s.*, i.baslik AS ilan_baslik, i.foto AS ilan_foto
        FROM sahiplenme_istekleri s
        LEFT JOIN ilanlar i ON s.ilan_id = i.id
        WHERE s.talep_eden_kullanici_id = ?
        ORDER BY s.id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $kullanici_id);
$stmt->execute();
$result = $stmt->get_result();

// Hata mesajını kontrol et
$hata_mesaj = "";
if (isset($_SESSION['hata_mesaj'])) {
    $hata_mesaj = $_SESSION['hata_mesaj'];
    unset($_SESSION['hata_mesaj']);
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Taleplerim</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <link href="./dist/output.css" rel="stylesheet">
</head>
<body class="bg-gray-50 font-sans min-h-screen flex flex-col">

<?php include("includes/header.php"); ?>

<div class="container mx-auto p-4 flex-grow pt-20">
    <div class="max-w-6xl mx-auto bg-white p-8 rounded-lg shadow-xl mt-8">
        <h2 class="text-3xl font-bold text-center mb-8">Sahiplenme Taleplerim</h2>
        
        <?php if (!empty($hata_mesaj)): ?>
            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <?= htmlspecialchars($hata_mesaj) ?>
            </div>
        <?php endif; ?>
        
        <div class="overflow-x-auto">
            <?php if ($result->num_rows > 0): ?>
                <table class="min-w-full table-auto border-collapse border border-gray-300">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border border-gray-300 px-4 py-2 text-left">İlan</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">Mesajım</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">Tarih</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">Durum</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($row['ilan_baslik']) ?></td>
                                <td class="border border-gray-300 px-4 py-2"><?= nl2br(htmlspecialchars($row['mesaj'])) ?></td>
                                <td class="border border-gray-300 px-4 py-2">
                                    <?= isset($row['talep_tarihi']) ? date('d.m.Y H:i', strtotime($row['talep_tarihi'])) : (isset($row['tarih']) ? date('d.m.Y H:i', strtotime($row['tarih'])) : 'Tarih yok') ?>
                                </td>
                <td class="border border-gray-300 px-4 py-2">
                    <?php 
                    // Durum kontrolü - küçük harflerle yapıyoruz çünkü admin paneli küçük harfle kaydediyor
                    $durum = strtolower($row['durum']);
                    ?>
                    
                    <?php if ($durum == 'tamamlandı' || $durum == 'tamamlandi'): ?>
                        <span class="inline-block bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded">✅ Tamamlandı</span>
                        <div class="text-green-600 text-sm mt-1">
                            <i class="fas fa-heart mr-1"></i>
                            Tebrikler! Sahiplendirme tamamlandı. Yeni arkadaşınızla mutlu olun!
                        </div>
                    <?php elseif ($durum == 'onaylandı' || $durum == 'onaylandi'): ?>
                        <span class="inline-block bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded">✅ Onaylandı</span>
                        <div class="text-blue-600 text-sm mt-1">
                            <i class="fas fa-check-circle mr-1"></i>
                            Tebrikler! Talebiniz onaylandı. İlan sahibi sizinle iletişime geçecek.
                        </div>
                    <?php elseif ($durum == 'reddedildi' || $durum == 'reddedilmis'): ?>
                        <span class="inline-block bg-red-100 text-red-800 text-xs font-semibold px-2.5 py-0.5 rounded">❌ Reddedildi</span>
                        <div class="text-red-600 text-sm mt-1">
                            <i class="fas fa-times-circle mr-1"></i>
                            Üzgünüz, talebiniz reddedildi. Başka ilanları inceleyebilirsiniz.
                        </div>
                    <?php elseif ($durum == 'iletişim kuruldu' || $durum == 'iletisim kuruldu'): ?>
                        <span class="inline-block bg-purple-100 text-purple-800 text-xs font-semibold px-2.5 py-0.5 rounded">📞 İletişim Kuruldu</span>
                        <div class="text-purple-600 text-sm mt-1">
                            <i class="fas fa-phone mr-1"></i>
                            İlan sahibi sizinle iletişime geçti.
                        </div>
                    <?php elseif ($durum == 'yeni' || $durum == 'beklemede'): ?>
                        <span class="inline-block bg-gray-100 text-gray-800 text-xs font-semibold px-2.5 py-0.5 rounded">⏳ Beklemede</span>
                        <div class="text-gray-600 text-sm mt-1">
                            <i class="fas fa-clock mr-1"></i>
                            Talebiniz beklemede. İlan sahibi değerlendirmesi yapacak.
                        </div>
                    <?php else: ?>
                        <span class="inline-block bg-gray-100 text-gray-800 text-xs font-semibold px-2.5 py-0.5 rounded"><?= htmlspecialchars(ucfirst($row['durum'])) ?></span>
                        <div class="text-gray-600 text-sm mt-1">
                            <i class="fas fa-info-circle mr-1"></i>
                            Durum: <?= htmlspecialchars($row['durum']) ?>
                        </div>
                    <?php endif; ?>
                </td>
                <td class="border border-gray-300 px-4 py-2">
                    <?php if ($durum == 'tamamlandı' || $durum == 'tamamlandi'): ?>
                        <div class="flex flex-col space-y-2">
                            <a href="sahiplenen_yorum_ekle.php?talep_id=<?= $row['id'] ?>" 
                               class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm transition duration-200 text-center">
                                <i class="fas fa-comment-dots mr-1"></i>
                                <?= !empty($row['sahiplenen_yorumu']) ? 'Yorumu Düzenle' : 'Yorum Ekle' ?>
                            </a>
                            
                            <?php if (!empty($row['sahiplenen_yorumu'])): ?>
                                <div class="bg-green-50 p-2 rounded text-xs">
                                    <i class="fas fa-check-circle text-green-600 mr-1"></i>
                                    <span class="text-green-700">Yorum mevcut</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php elseif ($durum == 'onaylandı' || $durum == 'onaylandi'): ?>
                        <div class="text-blue-600 text-sm">
                            <i class="fas fa-hourglass-half mr-1"></i>
                            Sahiplendirme sürecinde...
                        </div>
                    <?php elseif ($durum == 'reddedildi' || $durum == 'reddedilmis'): ?>
                        <div class="text-red-600 text-sm">
                            <i class="fas fa-times mr-1"></i>
                            İşlem tamamlandı
                        </div>
                    <?php else: ?>
                        <div class="text-gray-600 text-sm">
                            <i class="fas fa-clock mr-1"></i>
                            Beklemede
                        </div>
                    <?php endif; ?>
                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="text-center py-8">
                    <i class="fas fa-inbox text-gray-400 text-5xl mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-600 mb-2">Henüz Talep Bulunmuyor</h3>
                    <p class="text-gray-500 mb-4">Sahiplenme talebi oluşturmak için ilanları incelemeye başlayın.</p>
                    <a href="ilanlar.php" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg transition duration-200">
                        <i class="fas fa-search mr-2"></i>
                        İlanları İncele
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="bg-gray-800 text-white py-12 mt-16">
    <div class="max-w-7xl mx-auto px-6">
        <div class="text-center">
            <div class="text-3xl mb-4">🏠</div>
            <h3 class="text-2xl font-bold mb-4 text-primary-lighter">Yuva Ol</h3>
            <p class="text-gray-400">Sevgiyle Sahiplen</p>
        </div>
    </div>
</footer>

<?php include("includes/footer.php"); ?>
</body>
</html>