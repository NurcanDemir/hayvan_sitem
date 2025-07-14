<?php
session_start();
include("../includes/db.php"); // db.php'nin yolunu kontrol edin

header('Content-Type: application/json'); // JSON yanıtı döndüreceğimizi belirtiyoruz

$response = ['status' => 'error', 'message' => 'Bir hata oluştu.', 'action' => 'none'];

// Kullanıcı girişi kontrolü
if (!isset($_SESSION['kullanici_id']) || empty($_SESSION['kullanici_id'])) {
    $response['message'] = 'Bu işlemi yapmak için giriş yapmalısınız.';
    $response['redirect'] = 'giris.php'; // Giriş sayfasına yönlendirme sinyali
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ilan_id'])) {
    $kullanici_id = $_SESSION['kullanici_id'];
    $ilan_id = intval($_POST['ilan_id']);

    if ($ilan_id <= 0) {
        $response['message'] = 'Geçersiz ilan ID.';
        echo json_encode($response);
        exit;
    }

    // İlanın zaten favorilerde olup olmadığını kontrol et
    $stmt_check = $conn->prepare("SELECT id FROM favoriler WHERE kullanici_id = ? AND ilan_id = ?");
    if (!$stmt_check) {
        $response['message'] = 'Veritabanı kontrol hatası: ' . $conn->error;
        echo json_encode($response);
        exit;
    }
    $stmt_check->bind_param("ii", $kullanici_id, $ilan_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // Zaten favorilerde ise kaldır
        $stmt_delete = $conn->prepare("DELETE FROM favoriler WHERE kullanici_id = ? AND ilan_id = ?");
        if (!$stmt_delete) {
            $response['message'] = 'Veritabanı silme hatası: ' . $conn->error;
            echo json_encode($response);
            exit;
        }
        $stmt_delete->bind_param("ii", $kullanici_id, $ilan_id);
        if ($stmt_delete->execute()) {
            $response['status'] = 'success';
            $response['message'] = 'İlan favorilerden kaldırıldı.';
            $response['action'] = 'removed';
        } else {
            $response['message'] = 'Favoriden kaldırılırken hata oluştu: ' . $stmt_delete->error;
        }
        $stmt_delete->close();
    } else {
        // Favorilerde değilse ekle
        $stmt_insert = $conn->prepare("INSERT INTO favoriler (kullanici_id, ilan_id) VALUES (?, ?)");
        if (!$stmt_insert) {
            $response['message'] = 'Veritabanı ekleme hatası: ' . $conn->error;
            echo json_encode($response);
            exit;
        }
        $stmt_insert->bind_param("ii", $kullanici_id, $ilan_id);
        if ($stmt_insert->execute()) {
            $response['status'] = 'success';
            $response['message'] = 'İlan favorilere eklendi.';
            $response['action'] = 'added';
        } else {
            $response['message'] = 'Favoriye eklenirken hata oluştu: ' . $stmt_insert->error;
        }
        $stmt_insert->close();
    }
    $stmt_check->close();
} else {
    $response['message'] = 'Geçersiz istek veya eksik parametre.';
}

echo json_encode($response);
exit;
?>