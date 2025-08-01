<?php
session_start();
include("includes/db.php");

header('Content-Type: application/json'); // JSON yanıt döndüreceğiz

$response = ['status' => 'error', 'message' => 'Bir hata oluştu.', 'redirect' => ''];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_SESSION['kullanici_id'])) {
        $response['message'] = "Bu işlemi yapmak için giriş yapmalısınız.";
        $response['redirect'] = 'giris.php';
        echo json_encode($response);
        exit;
    }

    $ilan_id = filter_input(INPUT_POST, 'ilan_id', FILTER_VALIDATE_INT);
    $ilan_sahibi_id = filter_input(INPUT_POST, 'ilan_sahibi_id', FILTER_VALIDATE_INT);
    $talep_eden_kullanici_id = $_SESSION['kullanici_id'];
    
    // Tablonuzdaki sütun adlarına uygun olarak input'ları al
    $talep_eden_ad_soyad = filter_input(INPUT_POST, 'talep_eden_ad_soyad', FILTER_SANITIZE_STRING);
    $talep_eden_telefon = filter_input(INPUT_POST, 'talep_eden_telefon', FILTER_SANITIZE_STRING);
    $talep_eden_email = filter_input(INPUT_POST, 'talep_eden_email', FILTER_SANITIZE_EMAIL);
    $adres = filter_input(INPUT_POST, 'adres', FILTER_SANITIZE_STRING); // Yeni eklenen 'adres' sütunu için
    $mesaj = filter_input(INPUT_POST, 'mesaj', FILTER_SANITIZE_STRING);

    // Zorunlu alanların kontrolü
    if (!$ilan_id || !$ilan_sahibi_id || !$talep_eden_kullanici_id || empty($talep_eden_ad_soyad) || empty($talep_eden_telefon) || empty($talep_eden_email) || empty($adres)) {
        $response['message'] = "Lütfen tüm zorunlu alanları doldurun.";
        echo json_encode($response);
        exit;
    }

    // Kendi ilanına talep gönderme kontrolü
    if ($talep_eden_kullanici_id == $ilan_sahibi_id) {
        $response['message'] = "Kendi ilanınıza sahiplenme talebi gönderemezsiniz.";
        echo json_encode($response);
        exit;
    }

    // Daha önce aynı kullanıcı aynı ilana talep göndermiş mi kontrol et (tablo adı güncellendi)
    $stmt_check = $conn->prepare("SELECT id FROM sahiplenme_istekleri WHERE ilan_id = ? AND talep_eden_kullanici_id = ?");
    if ($stmt_check) {
        $stmt_check->bind_param("ii", $ilan_id, $talep_eden_kullanici_id);
        $stmt_check->execute();
        $check_result = $stmt_check->get_result();
        if ($check_result->num_rows > 0) {
            $response['message'] = "Bu ilana zaten bir sahiplenme talebi göndermişsiniz.";
            echo json_encode($response);
            exit;
        }
        $stmt_check->close();
    } else {
        $response['message'] = "Talep kontrolü sırasında bir veritabanı hatası oluştu.";
        echo json_encode($response);
        exit;
    }


    // Veritabanına talep ekleme (Direct to pet owner - no admin approval needed)
    $stmt = $conn->prepare("INSERT INTO sahiplenme_istekleri (ilan_id, talep_eden_kullanici_id, ilan_sahibi_kullanici_id, talep_eden_ad_soyad, talep_eden_telefon, talep_eden_email, adres, mesaj, durum) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'beklemede')");
    if ($stmt) {
        // İlan sahibi ID'si de eklendiği için bind_param da bir 'i' arttırıldı
        $stmt->bind_param("iiisssss", $ilan_id, $talep_eden_kullanici_id, $ilan_sahibi_id, $talep_eden_ad_soyad, $talep_eden_telefon, $talep_eden_email, $adres, $mesaj);
        if ($stmt->execute()) {
            $response['status'] = 'success';
            $response['message'] = "Sahiplenme talebiniz başarıyla gönderildi! İlan sahibi talebinizi inceleyecek ve size geri dönüş yapacak.";
        } else {
            $response['message'] = "Talep kaydedilirken bir hata oluştu: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $response['message'] = "Veritabanı sorgu hazırlama hatası: " . $conn->error;
    }
} else {
    $response['message'] = "Geçersiz istek metodu.";
}

echo json_encode($response);
$conn->close();
?>