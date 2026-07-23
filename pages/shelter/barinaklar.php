<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include("includes/db.php");

// Filtreleme parametrelerini al
$il_id = isset($_GET['il_id']) ? (int)$_GET['il_id'] : 0;
$ilce_id = isset($_GET['ilce_id']) ? (int)$_GET['ilce_id'] : 0;

// İl ve İlçe listelerini getir
$iller = $conn->query("SELECT * FROM il ORDER BY ad ASC");
$ilceler_array = [];
if ($iller) {
    $ilceler_result = $conn->query("SELECT * FROM ilce ORDER BY ad ASC");
    if ($ilceler_result) {
        while ($ilce = $ilceler_result->fetch_assoc()) {
            $ilceler_array[$ilce['il_id']][] = $ilce;
        }
    }
}

// Filtreleme koşullarını oluştur
$where_conditions = ["b.aktif = 1"];
if ($il_id > 0) {
    $where_conditions[] = "b.il_id = " . $il_id;
}
if ($ilce_id > 0) {
    $where_conditions[] = "b.ilce_id = " . $ilce_id;
}
$where_clause = implode(' AND ', $where_conditions);

// Barınakları getir - Filtreleme ile
$barinaklar_sql = "SELECT DISTINCT b.id, b.ad, b.adres, b.telefon, b.email, b.aciklama, 
                   b.il_id, b.ilce_id, b.website, b.latitude, b.longitude,
                   il.ad as il_adi, ilce.ad as ilce_adi, b.created_at
                   FROM hayvan_barinaklari b
                   LEFT JOIN il ON b.il_id = il.id
                   LEFT JOIN ilce ON b.ilce_id = ilce.id
                   WHERE $where_clause
                   ORDER BY b.ad ASC";

$barinaklar_result = $conn->query($barinaklar_sql);

// İstatistikler için ayrı sorgular
$toplam_barinak = $conn->query("SELECT COUNT(DISTINCT id) as total FROM hayvan_barinaklari WHERE aktif = 1")->fetch_assoc()['total'];

// Hayvan sayısını ilanlardan hesaplayalım
$toplam_hayvan_result = $conn->query("SELECT COUNT(*) as total FROM ilanlar WHERE durum = 'aktif'");
$toplam_hayvan = $toplam_hayvan_result ? $toplam_hayvan_result->fetch_assoc()['total'] : 0;

// Sahiplenen hayvan sayısı
$sahiplenen_result = $conn->query("SELECT COUNT(*) as total FROM ilanlar WHERE durum = 'sahiplenildi'");
$sahiplenen_sayisi = $sahiplenen_result ? $sahiplenen_result->fetch_assoc()['total'] : 0;

// Filtrelenen barınak sayısı
$filtrelenen_barinak = $barinaklar_result ? $barinaklar_result->num_rows : 0;
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hayvan Barınakları - Hayvan Dostları</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    
    <style>
        :root {
            --primary: #ba3689;
            --primary-light: #d95bb0;
            --primary-lighter: #e581c7;
            --primary-lightest: #f0b1df;
        }

        body {
            background: linear-gradient(135deg, var(--primary-lightest) 0%, #fdf2f8 30%, #f9fafb 70%, var(--primary-lightest) 100%);
            min-height: 100vh;
        }

        .bg-primary { background-color: var(--primary); }
        .bg-primary-light { background-color: var(--primary-light); }
        .bg-primary-lighter { background-color: var(--primary-lighter); }
        .bg-primary-lightest { background-color: var(--primary-lightest); }
        
        .text-primary { color: var(--primary); }
        .hover\:text-primary:hover { color: var(--primary); }
        .border-primary { border-color: var(--primary); }
        .focus\:ring-primary:focus { --tw-ring-color: var(--primary); }
        .focus\:border-primary:focus { border-color: var(--primary); }

        .btn-gradient {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            transition: all 0.3s ease;
        }
        
        .btn-gradient:hover {
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
            transform: translateY(-1px);
            box-shadow: 0 10px 25px rgba(186, 54, 137, 0.3);
        }

        .card-hover {
            transition: all 0.3s ease;
        }
        
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(186, 54, 137, 0.15);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="bg-white shadow-lg sticky top-0 z-50">
        <nav class="max-w-7xl mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="text-3xl">🐾</div>
                    <h1 class="text-2xl font-bold text-primary">
                        <a href="index.php">Hayvan Dostları</a>
                    </h1>
                </div>
                <div class="hidden md:flex items-center space-x-8">
                    <a href="index.php" class="text-stone-600 hover:text-primary font-semibold transition duration-300">Ana Sayfa</a>
                    <a href="#" class="text-primary font-semibold">Barınaklar</a>
                    <a href="etkinlikler.php" class="text-stone-600 hover:text-primary font-semibold transition duration-300">Etkinlikler</a>
                    <a href="ilanlar.php" class="text-stone-600 hover:text-primary font-semibold transition duration-300">İlanlar</a>
                    
                    <?php if (isset($_SESSION['kullanici_id'])): ?>
                        <a href="ilan_ekle.php" class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-md font-semibold transition duration-300">
                            <i class="fas fa-plus mr-2"></i>İlan Ver
                        </a>
                        <a href="ilanlarim.php" class="text-stone-600 hover:text-primary font-semibold transition duration-300">İlanlarım</a>
                        <a href="favorilerim.php" class="text-stone-600 hover:text-primary font-semibold transition duration-300">Favorilerim</a>
                        <span class="text-stone-600">Hoş geldin, <?= htmlspecialchars($_SESSION['kullanici_adi']) ?>!</span>
                        <a href="cikis.php" class="bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-md font-semibold transition duration-300">
                            <i class="fas fa-sign-out-alt mr-2"></i>Çıkış
                        </a>
                    <?php else: ?>
                        <a href="giris.php" class="btn-gradient text-white px-4 py-2 rounded-md transition duration-300">
                            <i class="fas fa-sign-in-alt mr-2"></i>Giriş Yap
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>

    <!-- Ana İçerik -->
    <main class="max-w-7xl mx-auto px-6 py-8">
        <!-- Sayfa Başlığı -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-800 mb-4">
                <i class="fas fa-home mr-3 text-primary"></i>
                Hayvan Barınakları
            </h1>
            <p class="text-xl text-gray-600">Canlarımıza ev sahipliği yapan barınaklar</p>
        </div>

        <!-- Filtreler -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8 card-hover">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">
                <i class="fas fa-filter mr-3 text-primary"></i>
                Barınakları Filtrele
            </h2>
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-map-marker-alt mr-1 text-primary"></i>İl
                    </label>
                    <select name="il_id" id="il" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary focus:border-primary">
                        <option value="">Tüm İller</option>
                        <?php 
                        if ($iller) {
                            $iller->data_seek(0); // Reset pointer
                            while($il = $iller->fetch_assoc()): 
                        ?>
                            <option value="<?= $il['id'] ?>" <?= ($il_id == $il['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($il['ad']) ?>
                            </option>
                        <?php endwhile; } ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-location-arrow mr-1 text-primary"></i>İlçe
                    </label>
                    <select name="ilce_id" id="ilce" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary focus:border-primary">
                        <option value="">Tüm İlçeler</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="w-full btn-gradient text-white px-6 py-2 rounded-md font-semibold transition duration-300">
                        <i class="fas fa-search mr-2"></i>Filtrele
                    </button>
                </div>

                <div class="flex items-end">
                    <a href="barinaklar.php" class="w-full bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-md font-semibold text-center transition duration-300">
                        <i class="fas fa-times mr-2"></i>Temizle
                    </a>
                </div>
            </form>
        </div>

        <!-- İstatistikler -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
            <div class="bg-white rounded-xl shadow-lg p-6 text-center card-hover">
                <div class="text-3xl font-bold text-primary mb-2"><?= $toplam_barinak ?></div>
                <div class="text-gray-600">Toplam Barınak</div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 text-center card-hover">
                <div class="text-3xl font-bold text-orange-600 mb-2"><?= $filtrelenen_barinak ?></div>
                <div class="text-gray-600">Filtrelenen Barınak</div>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg p-6 text-center card-hover">
                <div class="text-3xl font-bold text-green-600 mb-2"><?= $toplam_hayvan ?></div>
                <div class="text-gray-600">Sahiplenmeyi Bekleyen</div>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg p-6 text-center card-hover">
                <div class="text-3xl font-bold text-blue-600 mb-2"><?= $sahiplenen_sayisi ?></div>
                <div class="text-gray-600">Mutlu Son Bulan</div>
            </div>
        </div>

        <!-- Sonuç Başlığı -->
        <?php if ($il_id > 0 || $ilce_id > 0): ?>
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-info-circle text-blue-600"></i>
                        <span class="text-blue-800 font-semibold">
                            <?php
                            $filter_text = [];
                            if ($il_id > 0) {
                                $il_result = $conn->query("SELECT ad FROM il WHERE id = $il_id");
                                if ($il_result && $il_row = $il_result->fetch_assoc()) {
                                    $filter_text[] = $il_row['ad'];
                                }
                            }
                            if ($ilce_id > 0) {
                                $ilce_result = $conn->query("SELECT ad FROM ilce WHERE id = $ilce_id");
                                if ($ilce_result && $ilce_row = $ilce_result->fetch_assoc()) {
                                    $filter_text[] = $ilce_row['ad'];
                                }
                            }
                            echo implode(' / ', $filter_text) . ' bölgesinde ' . $filtrelenen_barinak . ' barınak bulundu';
                            ?>
                        </span>
                    </div>
                    <a href="barinaklar.php" class="text-blue-600 hover:text-blue-800 font-semibold text-sm">
                        <i class="fas fa-times mr-1"></i>Filtreyi Kaldır
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Barınaklar Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php 
            if ($barinaklar_result && $barinaklar_result->num_rows > 0): 
                // Unique ID'leri kontrol etmek için array
                $seen_ids = array();
                
                while ($barinak = $barinaklar_result->fetch_assoc()): 
                    // Eğer bu ID daha önce görüldüyse, atla
                    if (in_array($barinak['id'], $seen_ids)) {
                        continue;
                    }
                    // ID'yi görüldü olarak işaretle
                    $seen_ids[] = $barinak['id'];
            ?>
                <div class="bg-white rounded-xl shadow-lg overflow-hidden card-hover">
                    <div class="p-6">
                        <div class="flex items-start justify-between mb-4">
                            <h3 class="text-xl font-bold text-gray-800"><?= htmlspecialchars($barinak['ad']) ?></h3>
                            <div class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-sm font-semibold">
                                <i class="fas fa-check-circle mr-1"></i>Aktif
                            </div>
                        </div>
                        
                        <!-- Barınak Bilgileri -->
                        <div class="space-y-3 mb-4">
                            <?php if ($barinak['il_adi']): ?>
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-map-marker-alt mr-2 text-primary"></i>
                                    <span><?= htmlspecialchars($barinak['il_adi']) ?><?= $barinak['ilce_adi'] ? ' / ' . htmlspecialchars($barinak['ilce_adi']) : '' ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($barinak['telefon']): ?>
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-phone mr-2 text-primary"></i>
                                    <a href="tel:<?= htmlspecialchars($barinak['telefon']) ?>" class="hover:text-primary transition-colors">
                                        <?= htmlspecialchars($barinak['telefon']) ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($barinak['email']): ?>
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-envelope mr-2 text-primary"></i>
                                    <a href="mailto:<?= htmlspecialchars($barinak['email']) ?>" class="hover:text-primary transition-colors">
                                        <?= htmlspecialchars($barinak['email']) ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($barinak['website']): ?>
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-globe mr-2 text-primary"></i>
                                    <a href="<?= htmlspecialchars($barinak['website']) ?>" target="_blank" class="hover:text-primary transition-colors">
                                        Web Sitesi
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Konum Bilgisi (Latitude/Longitude varsa) -->
                        <?php if ($barinak['latitude'] && $barinak['longitude']): ?>
                            <div class="bg-blue-50 rounded-lg p-3 mb-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-semibold text-blue-700">
                                        <i class="fas fa-map-pin mr-1"></i>Konum Mevcut
                                    </span>
                                    <a href="https://www.google.com/maps?q=<?= $barinak['latitude'] ?>,<?= $barinak['longitude'] ?>" 
                                       target="_blank" 
                                       class="text-blue-600 hover:text-blue-800 text-sm font-semibold">
                                        <i class="fas fa-external-link-alt mr-1"></i>Haritada Gör
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Açıklama -->
                        <?php if ($barinak['aciklama']): ?>
                            <p class="text-gray-600 text-sm mb-4 line-clamp-3"><?= htmlspecialchars($barinak['aciklama']) ?></p>
                        <?php endif; ?>
                        
                        <!-- Adres -->
                        <?php if ($barinak['adres']): ?>
                            <div class="border-t pt-4 mb-4">
                                <div class="flex items-start text-gray-600">
                                    <i class="fas fa-map mr-2 text-primary mt-1"></i>
                                    <p class="text-sm"><?= htmlspecialchars($barinak['adres']) ?></p>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Kayıt Tarihi -->
                        <div class="text-xs text-gray-500 mb-4">
                            <i class="fas fa-calendar mr-1"></i>
                            Kayıt: <?= date('d.m.Y', strtotime($barinak['created_at'])) ?>
                        </div>
                        
                        <!-- İletişim Butonları -->
                        <div class="grid grid-cols-1 gap-2">
                            <?php if ($barinak['telefon']): ?>
                                <a href="tel:<?= htmlspecialchars($barinak['telefon']) ?>" 
                                   class="bg-green-500 hover:bg-green-600 text-white px-3 py-2 rounded-md text-sm font-semibold text-center transition duration-300">
                                    <i class="fas fa-phone mr-1"></i>Telefon: <?= htmlspecialchars($barinak['telefon']) ?>
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($barinak['email']): ?>
                                <a href="mailto:<?= htmlspecialchars($barinak['email']) ?>" 
                                   class="btn-gradient text-white px-3 py-2 rounded-md text-sm font-semibold text-center">
                                    <i class="fas fa-envelope mr-1"></i>E-posta Gönder
                                </a>
                            <?php endif; ?>

                            <?php if ($barinak['website']): ?>
                                <a href="<?= htmlspecialchars($barinak['website']) ?>" 
                                   target="_blank"
                                   class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 rounded-md text-sm font-semibold text-center transition duration-300">
                                    <i class="fas fa-globe mr-1"></i>Web Sitesi
                                </a>
                            <?php endif; ?>

                            <?php if ($barinak['latitude'] && $barinak['longitude']): ?>
                                <a href="https://www.google.com/maps?q=<?= $barinak['latitude'] ?>,<?= $barinak['longitude'] ?>" 
                                   target="_blank"
                                   class="bg-orange-500 hover:bg-orange-600 text-white px-3 py-2 rounded-md text-sm font-semibold text-center transition duration-300">
                                    <i class="fas fa-map-marked-alt mr-1"></i>Haritada Görüntüle
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php 
                endwhile; 
            else: 
            ?>
                <div class="col-span-full text-center py-16">
                    <div class="text-6xl mb-6">🏠</div>
                    <h3 class="text-2xl font-semibold text-gray-600 mb-4">
                        <?= ($il_id > 0 || $ilce_id > 0) ? 'Bu Bölgede Barınak Bulunamadı' : 'Barınak Bulunamadı' ?>
                    </h3>
                    <p class="text-gray-500 mb-6">
                        <?= ($il_id > 0 || $ilce_id > 0) ? 'Seçilen kriterlere uygun barınak bulunmuyor. Farklı bir bölge deneyin.' : 'Henüz kayıtlı barınak bulunmuyor.' ?>
                    </p>
                    
                    <?php if ($il_id > 0 || $ilce_id > 0): ?>
                        <a href="barinaklar.php" class="btn-gradient text-white px-6 py-3 rounded-md font-semibold">
                            <i class="fas fa-list mr-2"></i>Tüm Barınakları Görüntüle
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Barınak Bilgilendirme -->
        <div class="mt-16 bg-white rounded-xl shadow-lg p-8">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold text-gray-800 mb-4">
                    <i class="fas fa-info-circle mr-3 text-primary"></i>
                    Barınaklar Hakkında
                </h2>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">
                        <i class="fas fa-heart mr-2 text-red-500"></i>
                        Barınaklara Nasıl Yardım Edebilirim?
                    </h3>
                    <ul class="space-y-2 text-gray-600">
                        <li class="flex items-start space-x-2">
                            <i class="fas fa-check text-green-500 mt-1"></i>
                            <span>Mama, su kabı ve oyuncak bağışı yapabilirsiniz</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <i class="fas fa-check text-green-500 mt-1"></i>
                            <span>Gönüllü olarak barınakta yardım edebilirsiniz</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <i class="fas fa-check text-green-500 mt-1"></i>
                            <span>Finansal bağış yapabilirsiniz</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <i class="fas fa-check text-green-500 mt-1"></i>
                            <span>Sosyal medyada paylaşarak farkındalık yaratabilirsiniz</span>
                        </li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">
                        <i class="fas fa-phone-alt mr-2 text-blue-500"></i>
                        İletişime Geçmeden Önce
                    </h3>
                    <ul class="space-y-2 text-gray-600">
                        <li class="flex items-start space-x-2">
                            <i class="fas fa-check text-green-500 mt-1"></i>
                            <span>Ziyaret saatlerini öğrenin</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <i class="fas fa-check text-green-500 mt-1"></i>
                            <span>Önceden randevu alın</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <i class="fas fa-check text-green-500 mt-1"></i>
                            <span>Sahiplendirme şartlarını öğrenin</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <i class="fas fa-check text-green-500 mt-1"></i>
                            <span>Gerekli belgeleri hazırlayın</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-12 mt-16">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center">
                <div class="text-3xl mb-4">🐾</div>
                <h3 class="text-2xl font-bold mb-4 text-primary-lighter">Hayvan Dostları</h3>
                <p class="text-gray-400">Sevgi dolu dostlarımıza yuva bulma platformu</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script>
        const ilceler = <?= json_encode($ilceler_array) ?>;

        document.addEventListener("DOMContentLoaded", function() {
            const ilSelect = document.getElementById('il');
            const ilceSelect = document.getElementById('ilce');
            
            // İl değiştiğinde ilçeleri güncelle
            ilSelect.addEventListener('change', function() {
                const ilId = this.value;
                ilceSelect.innerHTML = '<option value="">Tüm İlçeler</option>';
                
                if (ilId && ilceler[ilId]) {
                    ilceler[ilId].forEach(function(ilce) {
                        const option = document.createElement('option');
                        option.value = ilce.id;
                        option.textContent = ilce.ad;
                        <?php if ($ilce_id > 0): ?>
                            if (ilce.id == <?= $ilce_id ?>) {
                                option.selected = true;
                            }
                        <?php endif; ?>
                        ilceSelect.appendChild(option);
                    });
                }
            });

            // Sayfa yüklendiğinde mevcut filtrelere göre ilçe doldur
            <?php if ($il_id > 0): ?>
                ilSelect.dispatchEvent(new Event('change'));
            <?php endif; ?>
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>