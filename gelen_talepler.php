<?php
// filepath: c:\xampp\htdocs\hayvan_sitem\gelen_talepler.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Giriş kontrolü
if (!isset($_SESSION['kullanici_id'])) {
    header("Location: giris.php");
    exit();
}

include("includes/db.php");

$user_id = $_SESSION['kullanici_id'];

// Handle request actions (accept, deny, block)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $istek_id = (int)$_POST['istek_id'];
    $action = $_POST['action'];
    
    // Verify this request belongs to user's ad
    $verify_sql = "SELECT si.*, i.kullanici_id as ilan_sahibi_id, i.baslik as ilan_baslik
                   FROM sahiplenme_istekleri si 
                   JOIN ilanlar i ON si.ilan_id = i.id 
                   WHERE si.id = ? AND i.kullanici_id = ?";
    $verify_stmt = $conn->prepare($verify_sql);
    $verify_stmt->bind_param("ii", $istek_id, $user_id);
    $verify_stmt->execute();
    $istek = $verify_stmt->get_result()->fetch_assoc();
    
    if ($istek) {
        if ($action === 'accept') {
            // Accept the request and create conversation
            $update_sql = "UPDATE sahiplenme_istekleri SET durum = 'onaylandi', durum_guncellenme_tarihi = NOW(), mesajlasma_aktif = TRUE WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("i", $istek_id);
            
            if ($update_stmt->execute()) {
                // Create conversation
                $conv_sql = "INSERT INTO conversations (ilan_id, ilan_sahibi_id, talep_eden_id, sahiplenme_istek_id) 
                            VALUES (?, ?, ?, ?)";
                $conv_stmt = $conn->prepare($conv_sql);
                $conv_stmt->bind_param("iiii", $istek['ilan_id'], $user_id, $istek['talep_eden_kullanici_id'], $istek_id);
                $conv_stmt->execute();
                
                header("Location: gelen_talepler.php?success=accepted");
                exit();
            }
        } elseif ($action === 'deny') {
            $update_sql = "UPDATE sahiplenme_istekleri SET durum = 'reddedildi', durum_guncellenme_tarihi = NOW() WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("i", $istek_id);
            
            if ($update_stmt->execute()) {
                header("Location: gelen_talepler.php?success=denied");
                exit();
            }
        } elseif ($action === 'block') {
            // Block user and deny request
            $update_sql = "UPDATE sahiplenme_istekleri SET durum = 'reddedildi', durum_guncellenme_tarihi = NOW() WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("i", $istek_id);
            $update_stmt->execute();
            
            // Add to blocked users
            $block_sql = "INSERT IGNORE INTO blocked_users (blocker_id, blocked_id) VALUES (?, ?)";
            $block_stmt = $conn->prepare($block_sql);
            $block_stmt->bind_param("ii", $user_id, $istek['talep_eden_kullanici_id']);
            $block_stmt->execute();
            
            header("Location: gelen_talepler.php?success=blocked");
            exit();
        }
    }
}

$page_title = "Gelen Talepler - Hayvan Dostları";
include("includes/header.php");
// Get incoming adoption requests for user's ads
$requests_sql = "SELECT si.*, i.baslik as ilan_baslik, i.foto as ilan_foto, i.id as ilan_id,
                        k.kullanici_adi as talep_eden_adi, k.profil_foto as talep_eden_foto,
                        c.id as conversation_id,
                        DATE_FORMAT(si.talep_tarihi, '%d.%m.%Y %H:%i') as talep_tarihi_formatted,
                        CASE 
                            WHEN si.durum = 'beklemede' THEN 'Bekliyor'
                            WHEN si.durum = 'onaylandı' THEN 'Onaylandı'
                            WHEN si.durum = 'onaylandi' THEN 'Onaylandı'
                            WHEN si.durum = 'reddedildi' THEN 'Reddedildi'
                            ELSE si.durum
                        END as durum_text,
                        CASE 
                            WHEN si.durum = 'beklemede' THEN 'warning'
                            WHEN si.durum IN ('onaylandı', 'onaylandi') THEN 'success'
                            WHEN si.durum = 'reddedildi' THEN 'danger'
                            ELSE 'secondary'
                        END as durum_class,
                        (SELECT COUNT(*) FROM blocked_users bu WHERE bu.blocker_id = ? AND bu.blocked_id = si.talep_eden_kullanici_id) as is_blocked
                 FROM sahiplenme_istekleri si
                 JOIN ilanlar i ON si.ilan_id = i.id
                 JOIN kullanicilar k ON si.talep_eden_kullanici_id = k.id
                 LEFT JOIN conversations c ON c.sahiplenme_istek_id = si.id
                 WHERE i.kullanici_id = ?
                 ORDER BY si.talep_tarihi DESC";

$requests_stmt = $conn->prepare($requests_sql);
$requests_stmt->bind_param("ii", $user_id, $user_id);
$requests_stmt->execute();
$requests = $requests_stmt->get_result();

// Count statistics
$stats_sql = "SELECT 
                COUNT(*) as toplam,
                SUM(CASE WHEN si.durum = 'beklemede' THEN 1 ELSE 0 END) as bekliyor,
                SUM(CASE WHEN si.durum IN ('onaylandı', 'onaylandi') THEN 1 ELSE 0 END) as onaylandi,
                SUM(CASE WHEN si.durum = 'reddedildi' THEN 1 ELSE 0 END) as reddedildi
              FROM sahiplenme_istekleri si
              JOIN ilanlar i ON si.ilan_id = i.id
              WHERE i.kullanici_id = ?";
$stats_stmt = $conn->prepare($stats_sql);
$stats_stmt->bind_param("i", $user_id);
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();
?>

<style>
    .request-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }
    
    .request-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        border-color: #ba3689;
    }
    
    .user-avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #f3f4f6;
    }
    
    .action-btn {
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.875rem;
        transition: all 0.2s ease;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .btn-accept {
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
    }
    
    .btn-accept:hover {
        background: linear-gradient(135deg, #059669, #047857);
        transform: translateY(-1px);
    }
    
    .btn-deny {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: white;
    }
    
    .btn-deny:hover {
        background: linear-gradient(135deg, #dc2626, #b91c1c);
        transform: translateY(-1px);
    }
    
    .btn-block {
        background: linear-gradient(135deg, #6b7280, #4b5563);
        color: white;
    }
    
    .btn-block:hover {
        background: linear-gradient(135deg, #4b5563, #374151);
        transform: translateY(-1px);
    }
    
    .btn-message {
        background: linear-gradient(135deg, #ba3689, #a855f7);
        color: white;
    }
    
    .btn-message:hover {
        background: linear-gradient(135deg, #a855f7, #9333ea);
        transform: translateY(-1px);
    }
    
    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    .status-warning {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fbbf24;
    }
    
    .status-success {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #10b981;
    }
    
    .status-danger {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #ef4444;
    }
</style>

<!-- Main Content -->
<main class="min-h-screen bg-gradient-to-br from-purple-50 via-pink-50 to-purple-50 py-8 mt-16">
    <div class="max-w-7xl mx-auto px-6">
        
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-800 mb-4">
                <i class="fas fa-inbox mr-3 text-purple-600"></i>
                Gelen Sahiplenme Talepleri
            </h1>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                İlanlarınıza gelen sahiplenme taleplerini inceleyin, onaylayın veya reddedin.
            </p>
        </div>
        
        <!-- Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl p-6 text-center shadow-lg">
                <div class="text-3xl font-bold text-gray-800 mb-2"><?= $stats['toplam'] ?></div>
                <div class="text-gray-600 font-medium">Toplam Talep</div>
            </div>
            
            <div class="bg-yellow-500 rounded-xl p-6 text-center text-white shadow-lg">
                <div class="text-3xl font-bold mb-2"><?= $stats['bekliyor'] ?></div>
                <div class="text-yellow-100 font-medium">Bekleyen</div>
            </div>
            
            <div class="bg-green-500 rounded-xl p-6 text-center text-white shadow-lg">
                <div class="text-3xl font-bold mb-2"><?= $stats['onaylandi'] ?></div>
                <div class="text-green-100 font-medium">Onaylanan</div>
            </div>
            
            <div class="bg-red-500 rounded-xl p-6 text-center text-white shadow-lg">
                <div class="text-3xl font-bold mb-2"><?= $stats['reddedildi'] ?></div>
                <div class="text-red-100 font-medium">Reddedilen</div>
            </div>
        </div>
        
        <!-- Requests List -->
        <?php if ($requests && $requests->num_rows > 0): ?>
            <div class="space-y-6">
                <?php while ($request = $requests->fetch_assoc()): ?>
                    <div class="request-card">
                        <div class="flex flex-col lg:flex-row lg:items-center gap-6">
                            
                            <!-- User Info -->
                            <div class="flex items-center gap-4 lg:flex-1">
                                <div class="flex-shrink-0">
                                    <?php if (!empty($request['talep_eden_foto']) && file_exists('uploads/profiles/' . $request['talep_eden_foto'])): ?>
                                        <img src="uploads/profiles/<?= htmlspecialchars($request['talep_eden_foto']) ?>" 
                                             alt="<?= htmlspecialchars($request['talep_eden_adi']) ?>" 
                                             class="user-avatar">
                                    <?php else: ?>
                                        <div class="user-avatar bg-gradient-to-br from-purple-400 to-pink-400 flex items-center justify-center">
                                            <i class="fas fa-user text-white text-xl"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="flex-1">
                                    <h3 class="text-lg font-bold text-gray-800 mb-1">
                                        <?= htmlspecialchars($request['talep_eden_adi']) ?>
                                    </h3>
                                    <p class="text-gray-600 text-sm mb-2">
                                        İlan: <strong><?= htmlspecialchars($request['ilan_baslik']) ?></strong>
                                    </p>
                                    <p class="text-gray-500 text-sm">
                                        <i class="fas fa-clock mr-1"></i>
                                        <?= $request['talep_tarihi_formatted'] ?>
                                    </p>
                                </div>
                            </div>
                            
                            <!-- Message -->
                            <?php if (!empty($request['mesaj'])): ?>
                                <div class="lg:flex-1">
                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <h4 class="font-semibold text-gray-800 mb-2">Mesaj:</h4>
                                        <p class="text-gray-700 text-sm"><?= nl2br(htmlspecialchars($request['mesaj'])) ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Status & Actions -->
                            <div class="lg:flex-shrink-0">
                                <div class="flex flex-col items-center gap-3">
                                    <!-- Status Badge -->
                                    <span class="status-badge status-<?= $request['durum_class'] ?>">
                                        <?= $request['durum_text'] ?>
                                    </span>
                                    
                                    <!-- Action Buttons -->
                                    <?php if ($request['durum'] === 'beklemede' && !$request['is_blocked']): ?>
                                        <div class="flex gap-2">
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="istek_id" value="<?= $request['id'] ?>">
                                                <input type="hidden" name="action" value="accept">
                                                <button type="submit" class="action-btn btn-accept" 
                                                        onclick="return confirm('Bu talebi onaylamak istediğinizden emin misiniz? Onaylandığında mesajlaşma başlayacak.')">
                                                    <i class="fas fa-check"></i>
                                                    Onayla
                                                </button>
                                            </form>
                                            
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="istek_id" value="<?= $request['id'] ?>">
                                                <input type="hidden" name="action" value="deny">
                                                <button type="submit" class="action-btn btn-deny"
                                                        onclick="return confirm('Bu talebi reddetmek istediğinizden emin misiniz?')">
                                                    <i class="fas fa-times"></i>
                                                    Reddet
                                                </button>
                                            </form>
                                        </div>
                                        
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="istek_id" value="<?= $request['id'] ?>">
                                            <input type="hidden" name="action" value="block">
                                            <button type="submit" class="action-btn btn-block"
                                                    onclick="return confirm('Bu kullanıcıyı engellemek istediğinizden emin misiniz? Engellenen kullanıcı size tekrar talep gönderemeyecek.')">
                                                <i class="fas fa-ban"></i>
                                                Engelle
                                            </button>
                                        </form>
                                        
                                    <?php elseif ($request['durum'] === 'onaylandi' || $request['durum'] === 'onaylandı'): ?>
                                        <?php if ($request['conversation_id']): ?>
                                            <a href="mesajlar.php?conversation=<?= $request['conversation_id'] ?>" 
                                               class="action-btn btn-message">
                                                <i class="fas fa-comments"></i>
                                                Mesajlaş
                                            </a>
                                        <?php else: ?>
                                            <span class="text-sm text-gray-500">
                                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                                Konuşma Bulunamadı
                                            </span>
                                        <?php endif; ?>
                                    <?php elseif ($request['is_blocked']): ?>
                                        <span class="text-sm text-gray-500">
                                            <i class="fas fa-ban mr-1"></i>
                                            Kullanıcı Engellendi
                                        </span>
                                    <?php endif; ?>
                                    
                                    <!-- View Ad Button -->
                                    <a href="ilan_detay.php?id=<?= $request['ilan_id'] ?>" 
                                       class="text-purple-600 hover:text-purple-700 text-sm font-medium">
                                        <i class="fas fa-eye mr-1"></i>
                                        İlanı Görüntüle
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            
        <?php else: ?>
            <!-- No Requests -->
            <div class="text-center py-16">
                <div class="text-8xl mb-8">📫</div>
                <h3 class="text-3xl font-bold text-gray-600 mb-4">Henüz Talep Gelmedi</h3>
                <p class="text-xl text-gray-500 mb-8 max-w-md mx-auto">
                    İlanlarınıza gelen sahiplenme talepleri burada görünecek.
                </p>
                <a href="ilan_ekle.php" 
                   class="bg-gradient-to-r from-purple-600 to-pink-600 text-white px-8 py-3 rounded-lg font-semibold hover:from-purple-700 hover:to-pink-700 transition-all duration-200 inline-block">
                    <i class="fas fa-plus mr-2"></i>Yeni İlan Ekle
                </a>
            </div>
        <?php endif; ?>
        
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Success messages
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const success = urlParams.get('success');
    
    if (success === 'accepted') {
        Swal.fire({
            title: 'Başarılı!',
            text: 'Talep onaylandı ve mesajlaşma başlatıldı.',
            icon: 'success',
            confirmButtonColor: '#10b981',
        }).then(() => {
            window.history.replaceState({}, document.title, window.location.pathname);
        });
    } else if (success === 'denied') {
        Swal.fire({
            title: 'Başarılı!',
            text: 'Talep reddedildi.',
            icon: 'success',
            confirmButtonColor: '#10b981',
        }).then(() => {
            window.history.replaceState({}, document.title, window.location.pathname);
        });
    } else if (success === 'blocked') {
        Swal.fire({
            title: 'Başarılı!',
            text: 'Kullanıcı engellendi.',
            icon: 'success',
            confirmButtonColor: '#10b981',
        }).then(() => {
            window.history.replaceState({}, document.title, window.location.pathname);
        });
    }
    
    console.log('📨 Gelen talepler sayfası yüklendi');
});
</script>

<?php include("includes/footer.php"); ?>