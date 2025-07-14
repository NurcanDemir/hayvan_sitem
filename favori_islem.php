<?php
session_start();
header('Content-Type: application/json');

// Kullanıcı giriş kontrolü
if (!isset($_SESSION['kullanici_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Bu işlem için giriş yapmanız gerekiyor.',
        'redirect' => 'giris.php'
    ]);
    exit;
}

include("includes/db.php");

$kullanici_id = intval($_SESSION['kullanici_id']);
$ilan_id = intval($_POST['ilan_id'] ?? 0);
$action = $_POST['action'] ?? '';

if ($ilan_id <= 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Geçersiz ilan ID\'si.'
    ]);
    exit;
}

// İlanın varlığını kontrol et
$check_ilan = $conn->prepare("SELECT id FROM ilanlar WHERE id = ? AND durum = 'Aktif'");
$check_ilan->bind_param("i", $ilan_id);
$check_ilan->execute();
$ilan_result = $check_ilan->get_result();

if ($ilan_result->num_rows == 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'İlan bulunamadı veya aktif değil.'
    ]);
    exit;
}

if ($action === 'add') {
    // Favorilere ekleme
    
    // Önce zaten favorilerde olup olmadığını kontrol et
    $check_stmt = $conn->prepare("SELECT id FROM favoriler WHERE kullanici_id = ? AND ilan_id = ?");
    $check_stmt->bind_param("ii", $kullanici_id, $ilan_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Bu ilan zaten favorilerinizde.'
        ]);
    } else {
        // Favorilere ekle
        $insert_stmt = $conn->prepare("INSERT INTO favoriler (kullanici_id, ilan_id) VALUES (?, ?)");
        $insert_stmt->bind_param("ii", $kullanici_id, $ilan_id);
        
        if ($insert_stmt->execute()) {
            echo json_encode([
                'status' => 'success',
                'message' => 'İlan favorilerinize eklendi.'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Favorilere eklenirken bir hata oluştu.'
            ]);
        }
        $insert_stmt->close();
    }
    $check_stmt->close();
    
} elseif ($action === 'remove') {
    // Favorilerden çıkarma
    
    $delete_stmt = $conn->prepare("DELETE FROM favoriler WHERE kullanici_id = ? AND ilan_id = ?");
    $delete_stmt->bind_param("ii", $kullanici_id, $ilan_id);
    
    if ($delete_stmt->execute()) {
        if ($delete_stmt->affected_rows > 0) {
            echo json_encode([
                'status' => 'success',
                'message' => 'İlan favorilerinizden çıkarıldı.'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Bu ilan zaten favorilerinizde değil.'
            ]);
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Favorilerden çıkarılırken bir hata oluştu.'
        ]);
    }
    $delete_stmt->close();
    
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Geçersiz işlem.'
    ]);
}

$check_ilan->close();
$conn->close();
?>
