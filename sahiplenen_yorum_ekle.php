<?php
// sahiplenen_yorum_ekle.php - Sahiplenen kişinin yorum eklemesi için sayfa

session_start();
include('includes/db.php');

// Kullanıcı girişi kontrolü
if (!isset($_SESSION['kullanici_id'])) {
    header("Location: giris.php");
    exit;
}

$kullanici_id = $_SESSION['kullanici_id'];
$mesaj = "";
$mesaj_tipi = "";

// Talep ID'si kontrol et
if (!isset($_GET['talep_id'])) {
    header("Location: taleplerim.php");
    exit;
}

$talep_id = (int)$_GET['talep_id'];

// Talep bilgilerini al ve bu kullanıcının talebi olup olmadığını kontrol et
$sql = "SELECT si.*, i.baslik, i.foto 
        FROM sahiplenme_istekleri si 
        INNER JOIN ilanlar i ON si.ilan_id = i.id 
        WHERE si.id = ? AND si.talep_eden_kullanici_id = ? AND si.durum = 'tamamlandı'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $talep_id, $kullanici_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: taleplerim.php");
    exit;
}

$talep = $result->fetch_assoc();
$stmt->close();

// Form gönderilmişse yorumu kaydet
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $yorum = trim($_POST['yorum']);
    
    if (empty($yorum)) {
        $mesaj = "Yorum alanı boş bırakılamaz.";
        $mesaj_tipi = "error";
    } else {
        $sql_update = "UPDATE sahiplenme_istekleri SET sahiplenen_yorumu = ?, yorum_tarihi = NOW() WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("si", $yorum, $talep_id);
        
        if ($stmt_update->execute()) {
            $mesaj = "Yorumunuz başarıyla kaydedildi!";
            $mesaj_tipi = "success";
            $talep['sahiplenen_yorumu'] = $yorum;
            $talep['yorum_tarihi'] = date('Y-m-d H:i:s');
        } else {
            $mesaj = "Yorum kaydedilirken bir hata oluştu.";
            $mesaj_tipi = "error";
        }
        $stmt_update->close();
    }
}

include('includes/header.php');
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">
                <i class="fas fa-heart text-red-500 mr-2"></i>
                Sahiplendirme Yorumu
            </h1>

            <?php if (!empty($mesaj)): ?>
                <div class="mb-4 p-4 rounded-lg <?= $mesaj_tipi == 'success' ? 'bg-green-100 text-green-800 border border-green-300' : 'bg-red-100 text-red-800 border border-red-300' ?>">
                    <?= htmlspecialchars($mesaj) ?>
                </div>
            <?php endif; ?>

            <!-- İlan Bilgisi -->
            <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                <div class="flex items-center space-x-4">
                    <img src="uploads/<?= htmlspecialchars($talep['foto']) ?>" 
                         alt="<?= htmlspecialchars($talep['baslik']) ?>" 
                         class="w-16 h-16 object-cover rounded-lg">
                    <div>
                        <h3 class="font-semibold text-lg"><?= htmlspecialchars($talep['baslik']) ?></h3>
                        <p class="text-gray-600">Sahiplendirme Durumu: <span class="text-green-600 font-semibold">Tamamlandı</span></p>
                    </div>
                </div>
            </div>

            <!-- Yorum Formu -->
            <form method="POST" class="space-y-4">
                <div>
                    <label for="yorum" class="block text-sm font-medium text-gray-700 mb-2">
                        Sahiplendirme Deneyiminizi Paylaşın
                    </label>
                    <textarea 
                        name="yorum" 
                        id="yorum" 
                        rows="6" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Yeni arkadaşınızla ilgili deneyiminizi, mutluluk dolu anılarınızı paylaşın..."
                        required><?= htmlspecialchars($talep['sahiplenen_yorumu'] ?? '') ?></textarea>
                </div>

                <div class="flex space-x-4">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-md transition duration-200">
                        <i class="fas fa-save mr-2"></i>
                        <?= empty($talep['sahiplenen_yorumu']) ? 'Yorum Ekle' : 'Yorumu Güncelle' ?>
                    </button>
                    <a href="taleplerim.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-md transition duration-200">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Geri Dön
                    </a>
                </div>
            </form>

            <?php if (!empty($talep['sahiplenen_yorumu'])): ?>
                <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                    <h4 class="font-semibold text-blue-800 mb-2">Mevcut Yorumunuz:</h4>
                    <p class="text-blue-700"><?= nl2br(htmlspecialchars($talep['sahiplenen_yorumu'])) ?></p>
                    <?php if (!empty($talep['yorum_tarihi'])): ?>
                        <p class="text-sm text-blue-600 mt-2">
                            <i class="fas fa-calendar mr-1"></i>
                            <?= date('d.m.Y H:i', strtotime($talep['yorum_tarihi'])) ?>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>
