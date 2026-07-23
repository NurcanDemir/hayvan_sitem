<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// Kullanıcı giriş kontrolü
if (!isset($_SESSION['kullanici_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Giriş yapmanız gerekiyor!',
        'redirect' => 'giris.php'
    ]);
    exit;
}

include("includes/db.php");

$kullanici_id = $_SESSION['kullanici_id'];
$ilan_id = (int)$_POST['ilan_id'];
$action = $_POST['action'];

if (!$ilan_id) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Geçersiz ilan ID!'
    ]);
    exit;
}

// İlan var mı kontrol et
$ilan_check = $conn->prepare("SELECT id FROM ilanlar WHERE id = ?");
$ilan_check->bind_param("i", $ilan_id);
$ilan_check->execute();
$ilan_result = $ilan_check->get_result();

if ($ilan_result->num_rows === 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'İlan bulunamadı!'
    ]);
    exit;
}
$ilan_check->close();

try {
    if ($action === 'add') {
        // Önce mevcut favoriler tablosunun yapısını kontrol edelim
        // Favoriye ekle - tarih sütunu olmadan
        $stmt = $conn->prepare("INSERT IGNORE INTO favoriler (kullanici_id, ilan_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $kullanici_id, $ilan_id);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Favorilere eklendi! 💖'
                ]);
            } else {
                echo json_encode([
                    'status' => 'info',
                    'message' => 'Bu ilan zaten favorilerinizde! 💫'
                ]);
            }
        } else {
            throw new Exception("Veritabanı hatası: " . $stmt->error);
        }
        $stmt->close();
        
    } elseif ($action === 'remove') {
        // Favoriden kaldır
        $stmt = $conn->prepare("DELETE FROM favoriler WHERE kullanici_id = ? AND ilan_id = ?");
        $stmt->bind_param("ii", $kullanici_id, $ilan_id);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Favorilerden kaldırıldı! 💔'
                ]);
            } else {
                echo json_encode([
                    'status' => 'info',
                    'message' => 'Bu ilan zaten favorilerinizde değil! 🤷‍♀️'
                ]);
            }
        } else {
            throw new Exception("Veritabanı hatası: " . $stmt->error);
        }
        $stmt->close();
        
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Geçersiz işlem!'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Bir hata oluştu: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
