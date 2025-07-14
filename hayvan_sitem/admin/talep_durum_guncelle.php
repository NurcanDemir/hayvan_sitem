<?php
session_start();
include("../includes/db.php");
include("../includes/auth.php"); // Yetkilendirme kontrolü için auth.php

header('Content-Type: application/json'); // JSON yanıt döndüreceğiz

$response = ['status' => 'error', 'message' => 'Bir hata oluştu.'];

// Admin yetkisi kontrolü (auth.php'de bu fonksiyonun olduğunu varsayıyoruz)
// Eğer admin değilse işlemi durdur
if (!function_exists('isAdmin') || !isAdmin()) {
    $response['message'] = "Bu işlemi yapmaya yetkiniz yok.";
    echo json_encode($response);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $talep_id = filter_input(INPUT_POST, 'talep_id', FILTER_VALIDATE_INT);
    $action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING);

    if (!$talep_id || empty($action)) {
        $response['message'] = "Geçersiz talep ID'si veya işlem.";
        echo json_encode($response);
        exit;
    }

    $new_status = '';
    $admin_note_content = null;

    if ($action === 'admin_note') {
        $admin_note_content = filter_input(INPUT_POST, 'admin_note', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
        // Eğer not boş gönderilirse NULL yap
        if (empty($admin_note_content)) {
            $admin_note_content = null; 
        }

        $stmt_update = $conn->prepare("UPDATE sahiplenme_istekleri SET admin_notlari = ? WHERE id = ?");
        if ($stmt_update) {
            $stmt_update->bind_param("si", $admin_note_content, $talep_id);
            if ($stmt_update->execute()) {
                $response['status'] = 'success';
                $response['message'] = "Admin notu başarıyla güncellendi.";
            } else {
                $response['message'] = "Admin notu güncellenirken bir hata oluştu: " . $stmt_update->error;
            }
            $stmt_update->close();
        } else {
            $response['message'] = "Veritabanı güncelleme sorgusu hazırlama hatası: " . $conn->error;
        }
    } else {
        // Durum güncellemeleri için
        $allowed_statuses = ['Yeni', 'İletişim Kuruldu', 'Onaylandı', 'Reddedildi', 'Tamamlandı']; // Sizin ENUM değerleriniz
        if (!in_array($action, $allowed_statuses)) {
            $response['message'] = "Geçersiz durum değeri.";
            echo json_encode($response);
            exit;
        }
        $new_status = $action;

        $conn->begin_transaction(); // İşlem başlat

        try {
            // Sahiplenme talebinin durumunu güncelle
            $stmt_update = $conn->prepare("UPDATE sahiplenme_istekleri SET durum = ? WHERE id = ?");
            if (!$stmt_update) {
                throw new Exception("Durum güncelleme sorgusu hazırlama hatası: " . $conn->error);
            }
            $stmt_update->bind_param("si", $new_status, $talep_id);
            if (!$stmt_update->execute()) {
                throw new Exception("Durum güncellenirken hata oluştu: " . $stmt_update->error);
            }
            $stmt_update->close();

            // Eğer talep onaylandıysa veya tamamlandıysa, ilanı da güncelle
            if ($new_status === 'Onaylandı' || $new_status === 'Tamamlandı') {
                $stmt_ilan_id = $conn->prepare("SELECT ilan_id FROM sahiplenme_istekleri WHERE id = ?");
                if (!$stmt_ilan_id) {
                    throw new Exception("İlan ID sorgusu hazırlama hatası: " . $conn->error);
                }
                $stmt_ilan_id->bind_param("i", $talep_id);
                $stmt_ilan_id->execute();
                $ilan_result = $stmt_ilan_id->get_result();
                $ilan_row = $ilan_result->fetch_assoc();
                $ilan_id = $ilan_row['ilan_id'];
                $stmt_ilan_id->close();

                // İlanın durumunu 'sahiplenildi' olarak güncelle
                $stmt_update_ilan = $conn->prepare("UPDATE ilanlar SET durum = 'sahiplenildi' WHERE id = ?");
                if (!$stmt_update_ilan) {
                    throw new Exception("İlan durumu güncelleme sorgusu hazırlama hatası: " . $conn->error);
                }
                $stmt_update_ilan->bind_param("i", $ilan_id);
                if (!$stmt_update_ilan->execute()) {
                    throw new Exception("İlan durumu güncellenirken hata oluştu: " . $stmt_update_ilan->error);
                }
                $stmt_update_ilan->close();
            }

            $conn->commit(); // İşlemi onayla
            $response['status'] = 'success';
            $response['message'] = "Talep durumu başarıyla '$new_status' olarak güncellendi.";

        } catch (Exception $e) {
            $conn->rollback(); // Hata durumunda işlemi geri al
            $response['message'] = $e->getMessage();
        }
    }

} else {
    $response['message'] = "Geçersiz istek metodu.";
}

echo json_encode($response);
$conn->close();
?>