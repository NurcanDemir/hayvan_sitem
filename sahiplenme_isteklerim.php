<?php
// filepath: c:\xampp\htdocs\hayvan_sitem\sahiplenme_isteklerim.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Giriş kontrolü
if (!isset($_SESSION['kullanici_id'])) {
    header("Location: giris.php");
    exit();
}

$page_title = "Sahiplenme İsteklerim - Hayvan Dostları";
include("includes/db.php");
include("includes/header.php");

$user_id = $_SESSION['kullanici_id'];

// Sahiplenme isteklerini getir - gerçek tablo yapısına göre düzeltildi
$sql = "SELECT si.*, i.baslik as ilan_baslik, i.foto as ilan_foto, i.aciklama,
               k.ad as kategori_adi, c.ad as cins_adi, il.ad as il_adi,
               CASE 
                   WHEN si.durum = 'beklemede' THEN 'Değerlendiriliyor'
                   WHEN si.durum = 'onaylandı' THEN 'Onaylandı'
                   WHEN si.durum = 'onaylandi' THEN 'Onaylandı'
                   WHEN si.durum = 'reddedildi' THEN 'Reddedildi'
                   WHEN si.durum = 'tamamlandı' THEN 'Tamamlandı'
                   WHEN si.durum = 'tamamlandi' THEN 'Tamamlandı'
                   ELSE si.durum
               END as durum_text,
               CASE 
                   WHEN si.durum IN ('beklemede') THEN 'warning'
                   WHEN si.durum IN ('onaylandı', 'onaylandi', 'tamamlandı', 'tamamlandi') THEN 'success'
                   WHEN si.durum = 'reddedildi' THEN 'danger'
                   ELSE 'secondary'
               END as durum_class
        FROM sahiplenme_istekleri si
        LEFT JOIN ilanlar i ON si.ilan_id = i.id
        LEFT JOIN kategoriler k ON i.kategori_id = k.id
        LEFT JOIN cinsler c ON i.cins_id = c.id
        LEFT JOIN il ON i.il_id = il.id
        WHERE si.talep_eden_kullanici_id = ?
        ORDER BY si.talep_tarihi DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// İstatistikler
$stats_sql = "SELECT 
                COUNT(*) as toplam,
                SUM(CASE WHEN durum = 'beklemede' THEN 1 ELSE 0 END) as bekliyor,
                SUM(CASE WHEN durum IN ('onaylandı', 'onaylandi', 'tamamlandı', 'tamamlandi') THEN 1 ELSE 0 END) as onaylandi,
                SUM(CASE WHEN durum = 'reddedildi' THEN 1 ELSE 0 END) as reddedildi
              FROM sahiplenme_istekleri 
              WHERE talep_eden_kullanici_id = ?";

$stats_stmt = $conn->prepare($stats_sql);
$stats_stmt->bind_param("i", $user_id);
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();
?>

<style>
    .request-card {
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }
    
    .request-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        border-color: var(--primary);
    }
    
    .status-badge {
        font-size: 0.875rem;
        font-weight: 600;
        padding: 0.5rem 1rem;
        border-radius: 9999px;
        text-transform: uppercase;
        letter-spacing: 0.025em;
    }
    
    .status-warning {
        background-color: #fef3c7;
        color: #92400e;
        border: 1px solid #fbbf24;
    }
    
    .status-success {
        background-color: #d1fae5;
        color: #065f46;
        border: 1px solid #10b981;
    }
    
    .status-danger {
        background-color: #fee2e2;
        color: #991b1b;
        border: 1px solid #ef4444;
    }

    .stats-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        transition: transform 0.3s ease;
    }

    .stats-card:hover {
        transform: translateY(-5px);
    }

    .ilan-image {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 12px;
    }
</style>

<!-- Ana İçerik -->
<main class="min-h-screen bg-gradient-to-br from-purple-50 via-pink-50 to-purple-50">
    <!-- Hero Bölümü -->
    <div class="bg-gradient-to-r from-purple-600 to-pink-600 py-16 mt-16">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center">
                <h1 class="text-5xl font-bold text-white mb-6">
                    <i class="fas fa-heart mr-4"></i>
                    Sahiplenme İsteklerim
                </h1>
                <p class="text-xl text-purple-100 max-w-3xl mx-auto">
                    Gönderdiğiniz sahiplenme isteklerini takip edin ve durumlarını kontrol edin.
                </p>
            </div>
        </div>
    </div>

    <!-- İstatistikler -->
    <div class="max-w-7xl mx-auto px-6 -mt-8 relative z-10">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
            <div class="stats-card rounded-xl p-6 text-center">
                <div class="text-3xl font-bold mb-2"><?= $stats['toplam'] ?></div>
                <div class="text-sm opacity-90">Toplam İstek</div>
            </div>
            
            <div class="bg-yellow-500 rounded-xl p-6 text-center text-white">
                <div class="text-3xl font-bold mb-2"><?= $stats['bekliyor'] ?></div>
                <div class="text-sm opacity-90">Değerlendiriliyor</div>
            </div>
            
            <div class="bg-green-500 rounded-xl p-6 text-center text-white">
                <div class="text-3xl font-bold mb-2"><?= $stats['onaylandi'] ?></div>
                <div class="text-sm opacity-90">Onaylandı</div>
            </div>
            
            <div class="bg-red-500 rounded-xl p-6 text-center text-white">
                <div class="text-3xl font-bold mb-2"><?= $stats['reddedildi'] ?></div>
                <div class="text-sm opacity-90">Reddedildi</div>
            </div>
        </div>
    </div>

    <!-- İstekler Listesi -->
    <div class="max-w-7xl mx-auto px-6 pb-12">
        <?php if ($result && $result->num_rows > 0): ?>
            <div class="space-y-6">
                <?php while ($istek = $result->fetch_assoc()): ?>
                    <div class="request-card bg-white rounded-2xl shadow-lg p-6">
                        <div class="flex flex-col lg:flex-row lg:items-center gap-6">
                            <!-- İlan Bilgileri -->
                            <div class="flex items-center gap-4 lg:flex-1">
                                <div class="flex-shrink-0">
                                    <?php 
                                    $image_path = !empty($istek['ilan_foto']) ? 'uploads/' . htmlspecialchars($istek['ilan_foto']) : '';
                                    $display_image = (file_exists($image_path) && !empty($image_path)) ? $image_path : '';
                                    ?>
                                    <?php if ($display_image): ?>
                                        <img src="<?= $display_image ?>" 
                                             alt="<?= htmlspecialchars($istek['ilan_baslik']) ?>" 
                                             class="ilan-image">
                                    <?php else: ?>
                                        <div class="ilan-image bg-gradient-to-br from-purple-400 to-pink-400 flex items-center justify-center">
                                            <i class="fas fa-paw text-white text-2xl"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="flex-1">
                                    <h3 class="text-xl font-bold text-gray-800 mb-1">
                                        <?= htmlspecialchars($istek['ilan_baslik']) ?>
                                    </h3>
                                    
                                    <div class="flex flex-wrap gap-2 mb-2">
                                        <?php if ($istek['kategori_adi']): ?>
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                <i class="fas fa-tag mr-1"></i><?= htmlspecialchars($istek['kategori_adi']) ?>
                                            </span>
                                        <?php endif; ?>
                                        
                                        <?php if ($istek['cins_adi']): ?>
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                <i class="fas fa-paw mr-1"></i><?= htmlspecialchars($istek['cins_adi']) ?>
                                            </span>
                                        <?php endif; ?>
                                        
                                        <?php if ($istek['il_adi']): ?>
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                                <i class="fas fa-map-marker-alt mr-1"></i><?= htmlspecialchars($istek['il_adi']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- İstek Bilgileri -->
                            <div class="lg:flex-1">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600 mb-1">İstek Tarihi</label>
                                        <div class="text-gray-800">
                                            <i class="fas fa-calendar mr-2 text-blue-500"></i>
                                            <?= date('d.m.Y H:i', strtotime($istek['talep_tarihi'])) ?>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600 mb-1">Durum</label>
                                        <span class="status-badge status-<?= $istek['durum_class'] ?>">
                                            <i class="fas fa-<?= $istek['durum'] == 'beklemede' ? 'clock' : ($istek['durum_class'] == 'success' ? 'check' : 'times') ?> mr-1"></i>
                                            <?= $istek['durum_text'] ?>
                                        </span>
                                    </div>
                                </div>

                                <!-- Mesaj varsa göster -->
                                <?php if (!empty($istek['mesaj'])): ?>
                                    <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                                        <label class="block text-sm font-medium text-gray-600 mb-2">Mesajınız</label>
                                        <p class="text-gray-700 text-sm"><?= nl2br(htmlspecialchars($istek['mesaj'])) ?></p>
                                    </div>
                                <?php endif; ?>

                                <!-- Admin cevabı varsa göster -->
                                <?php if (!empty($istek['admin_notlari'])): ?>
                                    <div class="mt-4 p-4 bg-blue-50 rounded-lg border-l-4 border-blue-500">
                                        <label class="block text-sm font-medium text-blue-700 mb-2">
                                            <i class="fas fa-reply mr-1"></i>Admin Notu
                                        </label>
                                        <p class="text-blue-800 text-sm"><?= nl2br(htmlspecialchars($istek['admin_notlari'])) ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Aksiyonlar -->
                            <div class="lg:flex-shrink-0">
                                <div class="flex flex-col gap-2">
                                    <a href="ilan_detay.php?id=<?= $istek['ilan_id'] ?>" 
                                       class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg text-center font-semibold text-sm transition-colors">
                                        <i class="fas fa-eye mr-2"></i>İlanı Görüntüle
                                    </a>
                                    
                                    <?php if ($istek['durum'] == 'beklemede'): ?>
                                        <button onclick="cancelRequest(<?= $istek['id'] ?>)"
                                                class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-center font-semibold text-sm transition-colors">
                                            <i class="fas fa-times mr-2"></i>İptal Et
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

        <?php else: ?>
            <!-- Hiç İstek Yok -->
            <div class="text-center py-16">
                <div class="text-8xl mb-8">💌</div>
                <h3 class="text-3xl font-bold text-gray-600 mb-4">Henüz Sahiplenme İsteğiniz Yok</h3>
                <p class="text-xl text-gray-500 mb-8 max-w-md mx-auto">
                    İlanları inceleyerek sahiplenme isteği gönderebilirsiniz.
                </p>
                <a href="ilanlar.php" 
                   class="bg-gradient-to-r from-purple-600 to-pink-600 text-white px-8 py-3 rounded-lg font-semibold hover:from-purple-700 hover:to-pink-700 transition-all duration-200 inline-block">
                    <i class="fas fa-paw mr-2"></i>İlanları İncele
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bilgilendirme Bölümü -->
    <div class="bg-white py-16">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">
                    <i class="fas fa-info-circle mr-3 text-purple-600"></i>
                    Sahiplenme <span class="text-purple-600">Süreci</span>
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-heart text-2xl text-purple-600"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-3">1. İstek Gönder</h3>
                    <p class="text-gray-600 text-sm">
                        Beğendiğiniz hayvan için sahiplenme isteği gönderin.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-clock text-2xl text-yellow-600"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-3">2. Değerlendirme</h3>
                    <p class="text-gray-600 text-sm">
                        İlan sahibi isteğinizi inceler ve değerlendirir.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-check text-2xl text-green-600"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-3">3. Onay</h3>
                    <p class="text-gray-600 text-sm">
                        İsteğiniz onaylanırsa iletişim bilgileri paylaşılır.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-pink-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-home text-2xl text-pink-600"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-3">4. Yuva</h3>
                    <p class="text-gray-600 text-sm">
                        Hayvan dostunuzu yeni yuvasına kavuşturun.
                    </p>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function cancelRequest(requestId) {
        Swal.fire({
            title: 'İsteği İptal Et',
            text: 'Bu sahiplenme isteğini iptal etmek istediğinizden emin misiniz?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Evet, İptal Et',
            cancelButtonText: 'Hayır',
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
        }).then((result) => {
            if (result.isConfirmed) {
                // Basit bir form gönderimi ile isteği iptal et
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '';
                
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'cancel_request';
                input.value = requestId;
                
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    // Sayfa yüklendiğinde animasyon
    document.addEventListener('DOMContentLoaded', function() {
        // Request kartlarına fade-in animasyonu ekle
        const cards = document.querySelectorAll('.request-card');
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                card.style.transition = 'all 0.5s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });

        console.log('💌 Sahiplenme isteklerim sayfası yüklendi');
    });
</script>

<?php
// İptal isteği işleme
if (isset($_POST['cancel_request'])) {
    $request_id = (int)$_POST['cancel_request'];
    
    $cancel_sql = "DELETE FROM sahiplenme_istekleri WHERE id = ? AND talep_eden_kullanici_id = ?";
    $cancel_stmt = $conn->prepare($cancel_sql);
    $cancel_stmt->bind_param("ii", $request_id, $user_id);
    
    if ($cancel_stmt->execute()) {
        echo "<script>
            Swal.fire({
                title: 'Başarılı!',
                text: 'Sahiplenme isteğiniz iptal edildi.',
                icon: 'success',
                confirmButtonColor: '#10b981',
            }).then(() => {
                window.location.href = 'sahiplenme_isteklerim.php';
            });
        </script>";
    } else {
        echo "<script>
            Swal.fire({
                title: 'Hata!',
                text: 'İstek iptal edilirken bir hata oluştu.',
                icon: 'error',
                confirmButtonColor: '#ef4444',
            });
        </script>";
    }
    $cancel_stmt->close();
}

include("includes/footer.php"); 
?>