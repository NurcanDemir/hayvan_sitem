<?php
// debug_admin_status.php - Debug admin status updates
session_start();
include("../includes/db.php");

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log all requests
$log_data = [
    'timestamp' => date('Y-m-d H:i:s'),
    'method' => $_SERVER['REQUEST_METHOD'],
    'session' => $_SESSION,
    'post' => $_POST,
    'get' => $_GET
];

file_put_contents('debug_log.txt', json_encode($log_data) . "\n", FILE_APPEND);

header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Bir hata oluştu.'];

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    $response['message'] = "Admin yetkisi gerekli. Session: " . print_r($_SESSION, true);
    echo json_encode($response);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $talep_id = filter_input(INPUT_POST, 'talep_id', FILTER_VALIDATE_INT);
    $action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING);

    $response['debug'] = [
        'talep_id' => $talep_id,
        'action' => $action,
        'raw_post' => $_POST
    ];

    if (!$talep_id || empty($action)) {
        $response['message'] = "Geçersiz talep ID'si veya işlem. ID: $talep_id, Action: $action";
        echo json_encode($response);
        exit;
    }

    // Check if request exists
    $stmt_check = $conn->prepare("SELECT id, durum FROM sahiplenme_istekleri WHERE id = ?");
    $stmt_check->bind_param("i", $talep_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    if ($result_check->num_rows == 0) {
        $response['message'] = "Talep bulunamadı. ID: $talep_id";
        echo json_encode($response);
        exit;
    }

    $current_data = $result_check->fetch_assoc();
    $stmt_check->close();

    $response['current_data'] = $current_data;

    if ($action === 'admin_note') {
        $admin_note_content = filter_input(INPUT_POST, 'admin_note', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
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
        // Action'dan gerçek durum değerine çevir
        $action_map = [
            'onayla' => 'onaylandı',
            'reddet' => 'reddedildi',
            'tamamla' => 'tamamlandı'
        ];
        
        if (!isset($action_map[$action])) {
            $response['message'] = "Geçersiz durum değeri. Action: $action, Allowed: " . implode(', ', array_keys($action_map));
            echo json_encode($response);
            exit;
        }
        $new_status = $action_map[$action];

        $conn->begin_transaction();

        try {
            $stmt_update = $conn->prepare("UPDATE sahiplenme_istekleri SET durum = ? WHERE id = ?");
            if (!$stmt_update) {
                throw new Exception("Durum güncelleme sorgusu hazırlama hatası: " . $conn->error);
            }
            $stmt_update->bind_param("si", $new_status, $talep_id);
            if (!$stmt_update->execute()) {
                throw new Exception("Durum güncellenirken hata oluştu: " . $stmt_update->error);
            }
            $stmt_update->close();

            if ($new_status === 'onaylandı' || $new_status === 'tamamlandı') {
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

            $conn->commit();
            $response['status'] = 'success';
            $response['message'] = "Talep durumu başarıyla '$new_status' olarak güncellendi.";

        } catch (Exception $e) {
            $conn->rollback();
            $response['message'] = $e->getMessage();
        }
    }

} else {
    $response['message'] = "Geçersiz istek metodu. Method: " . $_SERVER['REQUEST_METHOD'];
}

echo json_encode($response);
$conn->close();
?>
