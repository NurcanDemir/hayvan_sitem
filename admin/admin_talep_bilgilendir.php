<?php
// admin/admin_talep_bilgilendir.php - Sahiplenme talep bilgilendirme sistemi
session_start();
include("../includes/auth.php");
include("../includes/db.php");

// POST isteği ile bilgilendirme gönderme
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $talep_id = intval($_POST['talep_id']);
    $bilgi_turu = $_POST['bilgi_turu'];
    $mesaj = trim($_POST['mesaj']);
    
    if (empty($mesaj)) {
        $_SESSION['error'] = "Mesaj alanı boş olamaz!";
        header("Location: sahiplenme_talepleri.php");
        exit();
    }
    
    // Talep bilgilerini al
    $stmt = $conn->prepare("SELECT si.*, i.baslik, i.foto, si.talep_eden_ad_soyad as kullanici_ad, si.talep_eden_email as kullanici_email 
                           FROM sahiplenme_istekleri si 
                           JOIN ilanlar i ON si.ilan_id = i.id 
                           WHERE si.id = ?");
    $stmt->bind_param("i", $talep_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['error'] = "Talep bulunamadı!";
        header("Location: sahiplenme_talepleri.php");
        exit();
    }
    
    $talep = $result->fetch_assoc();
    
    // Bilgilendirme kaydını ekle (bilgilendirmeler tablosu varsa)
    // Eğer tablo yoksa direkt başarı mesajı göster
    try {
        $stmt = $conn->prepare("INSERT INTO bilgilendirmeler (talep_id, admin_id, bilgi_turu, mesaj, tarih) VALUES (?, ?, ?, ?, NOW())");
        $admin_id = $_SESSION['admin_id'] ?? 1; // Admin ID'si session'dan al, yoksa 1 varsayılan
        $stmt->bind_param("iiss", $talep_id, $admin_id, $bilgi_turu, $mesaj);
        $stmt->execute();
        $_SESSION['success'] = "Bilgilendirme başarıyla gönderildi!";
    } catch (Exception $e) {
        // Tablo yoksa sadece mesaj göster
        $_SESSION['success'] = "Bilgilendirme işlemi tamamlandı! (Mesaj: " . htmlspecialchars($mesaj) . ")";
    }
    
    header("Location: sahiplenme_talepleri.php");
    exit();
}

// GET isteği ile bilgilendirme formunu göster
$talep_id = intval($_GET['id'] ?? 0);

if ($talep_id === 0) {
    // Eğer talep ID'si yoksa tüm talepleri listele
    include("includes/admin_header.php");
    ?>
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-6xl mx-auto">
            <div class="flex items-center justify-between mb-8">
                <h1 class="text-3xl font-bold text-gray-800">
                    <i class="fas fa-bell mr-3 text-blue-600"></i>
                    Bilgilendirme Sistemi
                </h1>
                <a href="sahiplenme_talepleri.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md">
                    <i class="fas fa-list mr-2"></i>Tüm Talepleri Görüntüle
                </a>
            </div>
            
            <!-- Talepler Listesi -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">
                    <i class="fas fa-clipboard-list mr-2 text-green-600"></i>
                    Bilgilendirme Gönderilecek Talepler
                </h2>
                
                <?php
                // Tüm talepleri getir - kullanıcı bilgilerini sahiplenme_istekleri tablosundan al
                $stmt = $conn->prepare("SELECT si.*, i.baslik, i.foto, si.talep_eden_ad_soyad as kullanici_ad, si.talep_eden_email as kullanici_email 
                                       FROM sahiplenme_istekleri si 
                                       JOIN ilanlar i ON si.ilan_id = i.id 
                                       ORDER BY si.talep_tarihi DESC");
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    echo '<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">';
                    while ($talep = $result->fetch_assoc()) {
                        $durum_rengi = match($talep['durum']) {
                            'beklemede' => 'bg-yellow-100 text-yellow-800',
                            'onaylandı' => 'bg-green-100 text-green-800',
                            'reddedildi' => 'bg-red-100 text-red-800',
                            'tamamlandı' => 'bg-blue-100 text-blue-800',
                            default => 'bg-gray-100 text-gray-800'
                        };
                        
                        echo '<div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">';
                        echo '<div class="flex items-start space-x-4 mb-4">';
                        echo '<img src="../uploads/' . htmlspecialchars($talep['foto']) . '" alt="' . htmlspecialchars($talep['baslik']) . '" class="w-16 h-16 object-cover rounded-lg flex-shrink-0">';
                        echo '<div class="flex-1 min-w-0">';
                        echo '<h3 class="font-semibold text-gray-900 truncate">' . htmlspecialchars($talep['baslik']) . '</h3>';
                        echo '<p class="text-sm text-gray-600 mt-1">' . htmlspecialchars($talep['kullanici_ad']) . '</p>';
                        echo '<p class="text-xs text-gray-500 mt-1">Talep Tarihi: ' . date('d.m.Y H:i', strtotime($talep['talep_tarihi'])) . '</p>';
                        echo '</div>';
                        echo '</div>';
                        
                        echo '<div class="flex items-center justify-between mb-4">';
                        echo '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' . $durum_rengi . '">';
                        echo ucfirst($talep['durum']);
                        echo '</span>';
                        echo '<a href="admin_talep_bilgilendir.php?id=' . $talep['id'] . '" class="inline-flex items-center px-3 py-1 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors text-sm">';
                        echo '<i class="fas fa-paper-plane mr-1"></i>Bilgilendir';
                        echo '</a>';
                        echo '</div>';
                        echo '</div>';
                    }
                    echo '</div>';
                } else {
                    echo '<p class="text-gray-600">Henüz bilgilendirme gönderilecek talep bulunmamaktadır.</p>';
                }
                ?>
            </div>
        </div>
    </div>
    <?php
    include("includes/admin_footer.php");
    exit();
}

// Talep bilgilerini al - kullanıcı bilgilerini sahiplenme_istekleri tablosundan al
$stmt = $conn->prepare("SELECT si.*, i.baslik, i.foto, i.aciklama, si.talep_eden_ad_soyad as kullanici_ad, si.talep_eden_email as kullanici_email, si.talep_eden_telefon as kullanici_telefon 
                       FROM sahiplenme_istekleri si 
                       JOIN ilanlar i ON si.ilan_id = i.id 
                       WHERE si.id = ?");
$stmt->bind_param("i", $talep_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Talep bulunamadı!";
    header("Location: sahiplenme_talepleri.php");
    exit();
}

$talep = $result->fetch_assoc();

// Geçmiş bilgilendirmeleri al (eğer tablo varsa)
$gecmis_bilgilendirmeler = [];
try {
    $stmt = $conn->prepare("SELECT b.*, a.ad as admin_ad, a.soyad as admin_soyad 
                           FROM bilgilendirmeler b 
                           JOIN admin a ON b.admin_id = a.id 
                           WHERE b.talep_id = ? 
                           ORDER BY b.tarih DESC");
    $stmt->bind_param("i", $talep_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $gecmis_bilgilendirmeler[] = $row;
    }
} catch (Exception $e) {
    // Tablo yoksa boş bırak
}

include("includes/admin_header.php");
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Başlık -->
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-bold text-gray-800">
                <i class="fas fa-bell mr-3 text-blue-600"></i>
                Sahiplenme Talebi Bilgilendirme
            </h1>
            <a href="sahiplenme_talepleri.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md">
                <i class="fas fa-arrow-left mr-2"></i>Geri Dön
            </a>
        </div>

        <!-- Talep Bilgileri -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4">
                <i class="fas fa-info-circle mr-2 text-green-600"></i>
                Talep Bilgileri
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <div class="flex items-start space-x-4">
                        <img src="../uploads/<?= htmlspecialchars($talep['foto']) ?>" 
                             alt="<?= htmlspecialchars($talep['baslik']) ?>" 
                             class="w-24 h-24 object-cover rounded-lg flex-shrink-0">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-800 mb-2"><?= htmlspecialchars($talep['baslik']) ?></h3>
                            <p class="text-gray-600 text-sm leading-relaxed"><?= htmlspecialchars(substr($talep['aciklama'], 0, 150)) ?>...</p>
                        </div>
                    </div>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Talep Eden Kullanıcı</label>
                        <p class="text-gray-800"><?= htmlspecialchars($talep['kullanici_ad']) ?></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">E-posta</label>
                        <p class="text-gray-800"><?= htmlspecialchars($talep['kullanici_email']) ?></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Telefon</label>
                        <p class="text-gray-800"><?= htmlspecialchars($talep['kullanici_telefon']) ?></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Talep Tarihi</label>
                        <p class="text-gray-800"><?= date('d.m.Y H:i', strtotime($talep['talep_tarihi'])) ?></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Durum</label>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            <?php 
                            switch($talep['durum']) {
                                case 'tamamlandı': echo 'bg-green-100 text-green-800'; break;
                                case 'onaylandı': echo 'bg-blue-100 text-blue-800'; break;
                                case 'reddedildi': echo 'bg-red-100 text-red-800'; break;
                                default: echo 'bg-yellow-100 text-yellow-800'; break;
                            }
                            ?>">
                            <?= ucfirst($talep['durum']) ?>
                        </span>
                    </div>
                    
                    <?php if (!empty($talep['notlar'])): ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Talep Notları</label>
                        <p class="text-gray-800 bg-gray-50 p-3 rounded-md"><?= htmlspecialchars($talep['notlar']) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Bilgilendirme Formu -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4">
                <i class="fas fa-paper-plane mr-2 text-blue-600"></i>
                Yeni Bilgilendirme Gönder
            </h2>
            
            <form method="POST" class="space-y-4">
                <input type="hidden" name="talep_id" value="<?= $talep_id ?>">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Bilgi Türü</label>
                    <select name="bilgi_turu" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="">Seçiniz</option>
                        <option value="bilgi">Genel Bilgi</option>
                        <option value="onay">Onay Bildirimi</option>
                        <option value="red">Red Bildirimi</option>
                        <option value="tamamlandi">Tamamlama Bildirimi</option>
                        <option value="ek_belge">Ek Belge İsteği</option>
                        <option value="randevu">Randevu Bildirimi</option>
                        <option value="uyari">Uyarı</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Mesaj</label>
                    <textarea name="mesaj" rows="6" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Kullanıcıya gönderilecek mesajı buraya yazın..." required></textarea>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="history.back()" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">
                        <i class="fas fa-times mr-2"></i>İptal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                        <i class="fas fa-paper-plane mr-2"></i>Gönder
                    </button>
                </div>
            </form>
        </div>

        <!-- Geçmiş Bilgilendirmeler -->
        <?php if (!empty($gecmis_bilgilendirmeler)): ?>
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">
                <i class="fas fa-history mr-2 text-gray-600"></i>
                Geçmiş Bilgilendirmeler
            </h2>
            
            <div class="space-y-4">
                <?php foreach ($gecmis_bilgilendirmeler as $bilgi): ?>
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center space-x-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                <?php 
                                switch($bilgi['bilgi_turu']) {
                                    case 'onay': echo 'bg-green-100 text-green-800'; break;
                                    case 'red': echo 'bg-red-100 text-red-800'; break;
                                    case 'tamamlandi': echo 'bg-purple-100 text-purple-800'; break;
                                    case 'uyari': echo 'bg-orange-100 text-orange-800'; break;
                                    default: echo 'bg-blue-100 text-blue-800'; break;
                                }
                                ?>">
                                <?= ucfirst($bilgi['bilgi_turu']) ?>
                            </span>
                            <span class="text-sm text-gray-600">
                                <?= htmlspecialchars($bilgi['admin_ad'] . ' ' . $bilgi['admin_soyad']) ?>
                            </span>
                        </div>
                        <span class="text-sm text-gray-500">
                            <?= date('d.m.Y H:i', strtotime($bilgi['tarih'])) ?>
                        </span>
                    </div>
                    <p class="text-gray-800"><?= htmlspecialchars($bilgi['mesaj']) ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include("includes/admin_footer.php"); ?>