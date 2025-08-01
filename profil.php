<?php
// filepath: c:\xampp\htdocs\hayvan_sitem\profil.php
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

// Handle profile photo upload
if (isset($_POST['upload_photo']) && isset($_FILES['profile_photo'])) {
    $upload_dir = 'uploads/profiles/';
    
    // Create directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $file = $_FILES['profile_photo'];
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    
    if (in_array($file_extension, $allowed_extensions) && $file['size'] < 5000000) { // 5MB limit
        $new_filename = 'profile_' . $user_id . '_' . time() . '.' . $file_extension;
        $upload_path = $upload_dir . $new_filename;
        
        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            // Update database with new profile photo
            $update_sql = "UPDATE kullanicilar SET profil_foto = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("si", $new_filename, $user_id);
            $update_stmt->execute();
            
            // Redirect to prevent form resubmission
            header("Location: profil.php?updated=photo");
            exit();
        }
    }
}

// Handle bio update
if (isset($_POST['update_bio'])) {
    $bio = trim($_POST['bio']);
    $update_sql = "UPDATE kullanicilar SET bio = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("si", $bio, $user_id);
    
    if ($update_stmt->execute()) {
        // Redirect to prevent form resubmission
        header("Location: profil.php?updated=bio");
        exit();
    }
}

$page_title = "Profilim - Hayvan Dostları";
include("includes/header.php");

// Get user information
$user_sql = "SELECT k.*, 
             COALESCE(k.profil_foto, '') as profil_foto,
             COALESCE(k.bio, '') as bio,
             DATE_FORMAT(k.created_at, '%d.%m.%Y') as uyelik_tarihi
             FROM kullanicilar k 
             WHERE k.id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_info = $user_stmt->get_result()->fetch_assoc();

// Get user's ads count
$ads_sql = "SELECT COUNT(*) as toplam_ilan FROM ilanlar WHERE kullanici_id = ?";
$ads_stmt = $conn->prepare($ads_sql);
$ads_stmt->bind_param("i", $user_id);
$ads_stmt->execute();
$ads_count = $ads_stmt->get_result()->fetch_assoc()['toplam_ilan'];

// Get user's adoption requests count
$requests_sql = "SELECT COUNT(*) as toplam_istek FROM sahiplenme_istekleri WHERE talep_eden_kullanici_id = ?";
$requests_stmt = $conn->prepare($requests_sql);
$requests_stmt->bind_param("i", $user_id);
$requests_stmt->execute();
$requests_count = $requests_stmt->get_result()->fetch_assoc()['toplam_istek'];

// Get user's recent ads
$recent_ads_sql = "SELECT i.*, k.ad as kategori_adi, c.ad as cins_adi,
                   DATE_FORMAT(i.tarih, '%d.%m.%Y') as ilan_tarihi
                   FROM ilanlar i
                   LEFT JOIN kategoriler k ON i.kategori_id = k.id
                   LEFT JOIN cinsler c ON i.cins_id = c.id
                   WHERE i.kullanici_id = ?
                   ORDER BY i.tarih DESC
                   LIMIT 6";
$recent_ads_stmt = $conn->prepare($recent_ads_sql);
$recent_ads_stmt->bind_param("i", $user_id);
$recent_ads_stmt->execute();
$recent_ads = $recent_ads_stmt->get_result();

// Get user's recent adoption requests
$recent_requests_sql = "SELECT si.*, i.baslik as ilan_baslik, i.foto as ilan_foto,
                        DATE_FORMAT(si.talep_tarihi, '%d.%m.%Y') as talep_tarihi_formatted,
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
                        WHERE si.talep_eden_kullanici_id = ?
                        ORDER BY si.talep_tarihi DESC
                        LIMIT 3";
$recent_requests_stmt = $conn->prepare($recent_requests_sql);
$recent_requests_stmt->bind_param("i", $user_id);
$recent_requests_stmt->execute();
$recent_requests = $recent_requests_stmt->get_result();
?>

<style>
    .profile-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 20px;
        transition: all 0.3s ease;
    }
    
    .profile-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        border: 4px solid white;
        object-fit: cover;
        transition: all 0.3s ease;
    }
    
    .profile-avatar:hover {
        transform: scale(1.05);
    }
    
    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        text-align: center;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        border-color: #ba3689;
    }
    
    .section-card {
        background: white;
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }
    
    .ad-item {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }
    
    .ad-item:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        border-color: #ba3689;
    }
    
    .request-item {
        background: #f8fafc;
        border-radius: 12px;
        padding: 1rem;
        transition: all 0.3s ease;
        border-left: 4px solid #ba3689;
    }
    
    .request-item:hover {
        background: white;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
    
    .bio-section {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        border-radius: 16px;
        padding: 1.5rem;
        color: white;
    }
    
    .upload-btn {
        position: absolute;
        bottom: 0;
        right: 0;
        background: #ba3689;
        color: white;
        border-radius: 50%;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .upload-btn:hover {
        background: #9c2e73;
        transform: scale(1.1);
    }
    
    .tab-content {
        display: none;
    }
    
    .tab-content.active {
        display: block;
    }
    
    .tab-btn {
        padding: 12px 24px;
        border-radius: 25px;
        transition: all 0.3s ease;
        border: 2px solid #e5e7eb;
    }
    
    .tab-btn.active {
        background: linear-gradient(135deg, #ba3689 0%, #764ba2 100%);
        color: white;
        border-color: #ba3689;
    }
</style>

<!-- Main Content -->
<main class="min-h-screen bg-gradient-to-br from-purple-50 via-pink-50 to-purple-50 py-8 mt-16">
    <div class="max-w-6xl mx-auto px-6">
        
        <!-- Profile Header -->
        <div class="profile-card text-white p-8 mb-8">
            <div class="flex flex-col md:flex-row items-center gap-8">
                <!-- Profile Photo -->
                <div class="relative">
                    <?php if (!empty($user_info['profil_foto']) && file_exists('uploads/profiles/' . $user_info['profil_foto'])): ?>
                        <img src="uploads/profiles/<?= htmlspecialchars($user_info['profil_foto']) ?>" 
                             alt="Profil Fotoğrafı" class="profile-avatar">
                    <?php else: ?>
                        <div class="profile-avatar bg-white/20 flex items-center justify-center">
                            <i class="fas fa-user text-4xl text-white"></i>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Upload Button -->
                    <label for="profile_photo_input" class="upload-btn">
                        <i class="fas fa-camera text-sm"></i>
                        <input type="file" id="profile_photo_input" accept="image/*" class="hidden" onchange="uploadPhoto(this)">
                    </label>
                </div>
                
                <!-- Profile Info -->
                <div class="flex-1 text-center md:text-left">
                    <h1 class="text-4xl font-bold mb-3"><?= htmlspecialchars($user_info['kullanici_adi']) ?></h1>
                    
                    <!-- Bio Section -->
                    <div class="bio-section mb-4">
                        <div id="bio-display" class="<?= empty($user_info['bio']) ? 'hidden' : '' ?>">
                            <p class="text-lg"><?= nl2br(htmlspecialchars($user_info['bio'])) ?></p>
                            <button onclick="editBio()" class="mt-2 text-white/80 hover:text-white">
                                <i class="fas fa-edit mr-1"></i>Düzenle
                            </button>
                        </div>
                        
                        <div id="bio-edit" class="<?= !empty($user_info['bio']) ? 'hidden' : '' ?>">
                            <form method="POST" class="space-y-3">
                                <textarea name="bio" rows="3" 
                                          placeholder="Kendinizi tanıtın... (Örn: Hayvan sever, köpek eğitmeni, veteriner...)"
                                          class="w-full px-3 py-2 rounded-lg text-gray-800 resize-none"><?= htmlspecialchars($user_info['bio']) ?></textarea>
                                <div class="flex gap-2">
                                    <button type="submit" name="update_bio" 
                                            class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg transition-colors">
                                        <i class="fas fa-save mr-1"></i>Kaydet
                                    </button>
                                    <button type="button" onclick="cancelBioEdit()" 
                                            class="bg-white/10 hover:bg-white/20 px-4 py-2 rounded-lg transition-colors">
                                        İptal
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <p class="text-purple-100">
                        <i class="fas fa-calendar mr-2"></i>
                        <?= $user_info['uyelik_tarihi'] ?> tarihinden beri üye
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="stat-card">
                <div class="text-3xl font-bold text-purple-600 mb-2"><?= $ads_count ?></div>
                <div class="text-gray-600 font-medium">Yayınlanan İlan</div>
                <a href="ilanlarim.php" class="text-purple-500 text-sm hover:underline mt-2 inline-block">
                    <i class="fas fa-arrow-right mr-1"></i>Tümünü Gör
                </a>
            </div>
            
            <div class="stat-card">
                <div class="text-3xl font-bold text-pink-600 mb-2"><?= $requests_count ?></div>
                <div class="text-gray-600 font-medium">Sahiplenme İsteği</div>
                <a href="sahiplenme_isteklerim.php" class="text-pink-500 text-sm hover:underline mt-2 inline-block">
                    <i class="fas fa-arrow-right mr-1"></i>Tümünü Gör
                </a>
            </div>
            
            <div class="stat-card">
                <div class="text-3xl font-bold text-indigo-600 mb-2">0</div>
                <div class="text-gray-600 font-medium">Mesaj</div>
                <div class="text-gray-400 text-sm mt-2">
                    <i class="fas fa-clock mr-1"></i>Yakında Aktif
                </div>
            </div>
        </div>
        
        <!-- Tab Navigation -->
        <div class="flex flex-wrap gap-4 mb-8 justify-center">
            <button onclick="showTab('ads')" class="tab-btn active" id="ads-tab">
                <i class="fas fa-bullhorn mr-2"></i>İlanlarım
            </button>
            <button onclick="showTab('requests')" class="tab-btn" id="requests-tab">
                <i class="fas fa-heart mr-2"></i>İsteklerim
            </button>
            <button onclick="showTab('messages')" class="tab-btn" id="messages-tab">
                <i class="fas fa-envelope mr-2"></i>Mesajlar
                <span class="ml-2 bg-gray-300 text-gray-600 px-2 py-1 rounded-full text-xs">Yakında</span>
            </button>
        </div>
        
        <!-- Tab Contents -->
        
        <!-- My Ads Tab -->
        <div id="ads-content" class="tab-content active">
            <div class="section-card">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">
                        <i class="fas fa-bullhorn mr-3 text-purple-600"></i>
                        Son İlanlarım
                    </h2>
                    <a href="ilan_ekle.php" 
                       class="bg-gradient-to-r from-purple-600 to-pink-600 text-white px-6 py-3 rounded-lg font-semibold hover:from-purple-700 hover:to-pink-700 transition-all duration-200">
                        <i class="fas fa-plus mr-2"></i>Yeni İlan
                    </a>
                </div>
                
                <?php if ($recent_ads && $recent_ads->num_rows > 0): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php while ($ad = $recent_ads->fetch_assoc()): ?>
                            <div class="ad-item">
                                <div class="relative h-48">
                                    <?php 
                                    $image_path = !empty($ad['foto']) ? 'uploads/' . htmlspecialchars($ad['foto']) : '';
                                    $display_image = (file_exists($image_path) && !empty($image_path)) ? $image_path : '';
                                    ?>
                                    <?php if ($display_image): ?>
                                        <img src="<?= $display_image ?>" 
                                             alt="<?= htmlspecialchars($ad['baslik']) ?>" 
                                             class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <div class="w-full h-full bg-gradient-to-br from-purple-400 to-pink-400 flex items-center justify-center">
                                            <i class="fas fa-paw text-white text-4xl"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="absolute top-3 right-3">
                                        <span class="bg-white/90 text-gray-800 px-2 py-1 rounded-full text-xs font-medium">
                                            <?= htmlspecialchars($ad['kategori_adi']) ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="p-4">
                                    <h3 class="font-bold text-gray-800 mb-2 line-clamp-2">
                                        <?= htmlspecialchars($ad['baslik']) ?>
                                    </h3>
                                    
                                    <div class="flex items-center gap-2 mb-3">
                                        <?php if ($ad['cins_adi']): ?>
                                            <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded-full text-xs">
                                                <?= htmlspecialchars($ad['cins_adi']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="flex items-center justify-between">
                                        <span class="text-gray-500 text-sm">
                                            <i class="fas fa-calendar mr-1"></i>
                                            <?= $ad['ilan_tarihi'] ?>
                                        </span>
                                        
                                        <div class="flex gap-1">
                                            <a href="ilan_detay.php?id=<?= $ad['id'] ?>" 
                                               class="bg-purple-500 hover:bg-purple-600 text-white p-2 rounded-lg text-sm transition-colors">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="ilan_duzenle.php?id=<?= $ad['id'] ?>" 
                                               class="bg-blue-500 hover:bg-blue-600 text-white p-2 rounded-lg text-sm transition-colors">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                    
                    <?php if ($ads_count > 6): ?>
                        <div class="text-center mt-8">
                            <a href="ilanlarim.php" 
                               class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-semibold transition-colors">
                                <i class="fas fa-th-large mr-2"></i>Tüm İlanları Görüntüle (<?= $ads_count ?>)
                            </a>
                        </div>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <div class="text-center py-12">
                        <div class="text-6xl mb-4">📢</div>
                        <h3 class="text-xl font-bold text-gray-600 mb-3">Henüz İlan Yayınlamadınız</h3>
                        <p class="text-gray-500 mb-6">İlk ilanınızı oluşturun ve hayvan dostlarınızı yeni ailelerine kavuşturun.</p>
                        <a href="ilan_ekle.php" 
                           class="bg-gradient-to-r from-purple-600 to-pink-600 text-white px-8 py-3 rounded-lg font-semibold hover:from-purple-700 hover:to-pink-700 transition-all duration-200 inline-block">
                            <i class="fas fa-plus mr-2"></i>İlk İlanımı Oluştur
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- My Requests Tab -->
        <div id="requests-content" class="tab-content">
            <div class="section-card">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">
                        <i class="fas fa-heart mr-3 text-pink-600"></i>
                        Son İsteklerim
                    </h2>
                    <a href="ilanlar.php" 
                       class="bg-gradient-to-r from-pink-600 to-purple-600 text-white px-6 py-3 rounded-lg font-semibold hover:from-pink-700 hover:to-purple-700 transition-all duration-200">
                        <i class="fas fa-search mr-2"></i>İlan Ara
                    </a>
                </div>
                
                <?php if ($recent_requests && $recent_requests->num_rows > 0): ?>
                    <div class="space-y-4">
                        <?php while ($request = $recent_requests->fetch_assoc()): ?>
                            <div class="request-item">
                                <div class="flex items-center gap-4">
                                    <div class="flex-shrink-0">
                                        <?php 
                                        $image_path = !empty($request['ilan_foto']) ? 'uploads/' . htmlspecialchars($request['ilan_foto']) : '';
                                        $display_image = (file_exists($image_path) && !empty($image_path)) ? $image_path : '';
                                        ?>
                                        <?php if ($display_image): ?>
                                            <img src="<?= $display_image ?>" 
                                                 alt="<?= htmlspecialchars($request['ilan_baslik']) ?>" 
                                                 class="w-16 h-16 object-cover rounded-lg">
                                        <?php else: ?>
                                            <div class="w-16 h-16 bg-gradient-to-br from-purple-400 to-pink-400 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-paw text-white text-xl"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-gray-800 mb-1">
                                            <?= htmlspecialchars($request['ilan_baslik']) ?>
                                        </h4>
                                        <div class="flex items-center gap-3">
                                            <span class="text-sm text-gray-500">
                                                <i class="fas fa-calendar mr-1"></i>
                                                <?= $request['talep_tarihi_formatted'] ?>
                                            </span>
                                            <span class="px-2 py-1 rounded-full text-xs font-medium 
                                                       <?= $request['durum_class'] == 'warning' ? 'bg-yellow-100 text-yellow-800' : 
                                                          ($request['durum_class'] == 'success' ? 'bg-green-100 text-green-800' : 
                                                          ($request['durum_class'] == 'danger' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')) ?>">
                                                <?= $request['durum_text'] ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="flex-shrink-0">
                                        <a href="ilan_detay.php?id=<?= $request['ilan_id'] ?>" 
                                           class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg text-sm transition-colors">
                                            <i class="fas fa-eye mr-1"></i>Görüntüle
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                    
                    <?php if ($requests_count > 3): ?>
                        <div class="text-center mt-6">
                            <a href="sahiplenme_isteklerim.php" 
                               class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-semibold transition-colors">
                                <i class="fas fa-th-list mr-2"></i>Tüm İstekleri Görüntüle (<?= $requests_count ?>)
                            </a>
                        </div>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <div class="text-center py-12">
                        <div class="text-6xl mb-4">💌</div>
                        <h3 class="text-xl font-bold text-gray-600 mb-3">Henüz Sahiplenme İsteğiniz Yok</h3>
                        <p class="text-gray-500 mb-6">İlanları inceleyerek sahiplenme isteği gönderebilirsiniz.</p>
                        <a href="ilanlar.php" 
                           class="bg-gradient-to-r from-pink-600 to-purple-600 text-white px-8 py-3 rounded-lg font-semibold hover:from-pink-700 hover:to-purple-700 transition-all duration-200 inline-block">
                            <i class="fas fa-search mr-2"></i>İlanları İncele
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Messages Tab -->
        <div id="messages-content" class="tab-content">
            <div class="section-card">
                <div class="text-center py-16">
                    <div class="text-8xl mb-6">💬</div>
                    <h2 class="text-3xl font-bold text-gray-600 mb-4">Mesaj Özelliği Yakında</h2>
                    <p class="text-xl text-gray-500 mb-8 max-w-2xl mx-auto">
                        Yakında diğer kullanıcılarla doğrudan mesajlaşabilecek, sahiplenme süreçlerinizi daha kolay yönetebileceksiniz.
                    </p>
                    <div class="bg-gradient-to-r from-purple-100 to-pink-100 rounded-lg p-6 max-w-md mx-auto">
                        <h3 class="font-bold text-gray-800 mb-3">Gelecek Özellikler:</h3>
                        <ul class="text-left text-gray-600 space-y-2">
                            <li><i class="fas fa-check text-green-500 mr-2"></i>Anlık mesajlaşma</li>
                            <li><i class="fas fa-check text-green-500 mr-2"></i>Fotoğraf paylaşımı</li>
                            <li><i class="fas fa-check text-green-500 mr-2"></i>Mesaj bildirimları</li>
                            <li><i class="fas fa-check text-green-500 mr-2"></i>Engelleme sistemi</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</main>

<!-- Profile Photo Upload Form -->
<form id="photoUploadForm" method="POST" enctype="multipart/form-data" style="display: none;">
    <input type="hidden" name="upload_photo" value="1">
    <input type="file" name="profile_photo" id="photoFileInput" accept="image/*">
</form>

<script>
// Tab functionality
function showTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
    });
    
    // Remove active class from all tab buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show selected tab content
    document.getElementById(tabName + '-content').classList.add('active');
    document.getElementById(tabName + '-tab').classList.add('active');
}

// Bio editing functionality
function editBio() {
    document.getElementById('bio-display').classList.add('hidden');
    document.getElementById('bio-edit').classList.remove('hidden');
}

function cancelBioEdit() {
    document.getElementById('bio-display').classList.remove('hidden');
    document.getElementById('bio-edit').classList.add('hidden');
}

// Profile photo upload
function uploadPhoto(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        // Validate file type
        if (!file.type.match('image.*')) {
            Swal.fire({
                title: 'Hata!',
                text: 'Lütfen geçerli bir resim dosyası seçin.',
                icon: 'error',
                confirmButtonColor: '#ef4444',
            });
            return;
        }
        
        // Validate file size (5MB)
        if (file.size > 5000000) {
            Swal.fire({
                title: 'Hata!',
                text: 'Dosya boyutu 5MB\'dan küçük olmalıdır.',
                icon: 'error',
                confirmButtonColor: '#ef4444',
            });
            return;
        }
        
        // Show confirmation
        Swal.fire({
            title: 'Profil Fotoğrafını Değiştir',
            text: 'Seçtiğiniz fotoğraf profil resminiz olarak ayarlanacak.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Evet, Değiştir',
            cancelButtonText: 'İptal',
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6b7280',
        }).then((result) => {
            if (result.isConfirmed) {
                // Create form data and submit
                const formData = new FormData();
                formData.append('upload_photo', '1');
                formData.append('profile_photo', file);
                
                // Show loading
                Swal.fire({
                    title: 'Yükleniyor...',
                    text: 'Profil fotoğrafınız yükleniyor.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Submit form
                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                }).then(response => {
                    window.location.reload();
                }).catch(error => {
                    Swal.fire({
                        title: 'Hata!',
                        text: 'Fotoğraf yüklenirken bir hata oluştu.',
                        icon: 'error',
                        confirmButtonColor: '#ef4444',
                    });
                });
            }
        });
    }
}

// Page load animations
document.addEventListener('DOMContentLoaded', function() {
    // Check for update messages
    const urlParams = new URLSearchParams(window.location.search);
    const updated = urlParams.get('updated');
    
    if (updated === 'bio') {
        Swal.fire({
            title: 'Başarılı!',
            text: 'Biyografiniz güncellendi.',
            icon: 'success',
            confirmButtonColor: '#10b981',
        }).then(() => {
            // Remove the parameter from URL without reloading
            window.history.replaceState({}, document.title, window.location.pathname);
        });
    } else if (updated === 'photo') {
        Swal.fire({
            title: 'Başarılı!',
            text: 'Profil fotoğrafınız güncellendi.',
            icon: 'success',
            confirmButtonColor: '#10b981',
        }).then(() => {
            // Remove the parameter from URL without reloading
            window.history.replaceState({}, document.title, window.location.pathname);
        });
    }
    
    // Animate cards on load
    const cards = document.querySelectorAll('.stat-card, .ad-item, .request-item');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
    
    console.log('🎉 Profil sayfası yüklendi');
});

// Initialize bio edit state
<?php if (empty($user_info['bio'])): ?>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('bio-display').classList.add('hidden');
    document.getElementById('bio-edit').classList.remove('hidden');
});
<?php endif; ?>
</script>

<?php include("includes/footer.php"); ?>
