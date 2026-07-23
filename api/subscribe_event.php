<?php
// api/subscribe_event.php - Etkinlik aboneliği ve hatırlatma kaydı API uç noktası
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once __DIR__ . '/../includes/db.php';
@include_once __DIR__ . '/../mail/includes/MailSender.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Geçersiz istek yöntemi.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$event_id = isset($_POST['event_id']) ? intval($_POST['event_id']) : 0;
$email = isset($_POST['email']) ? trim($_POST['email']) : '';

if ($event_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Lütfen geçerli bir etkinlik seçin.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Lütfen geçerli bir e-posta adresi girin.'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // Etkinlik varlık kontrolü
    $stmt_check = $conn->prepare("SELECT id, baslik, etkinlik_tarihi, etkinlik_saati, aciklama, adres, kategori FROM hayvan_etkinlikleri WHERE id = ?");
    $stmt_check->bind_param("i", $event_id);
    $stmt_check->execute();
    $event_result = $stmt_check->get_result();

    if ($event_result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Etkinlik bulunamadı.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $event_data = $event_result->fetch_assoc();

    // Zaten abone mi kontrolü
    $sub_check = $conn->prepare("SELECT id FROM event_subscriptions WHERE event_id = ? AND email = ?");
    $sub_check->bind_param("is", $event_id, $email);
    $sub_check->execute();
    if ($sub_check->get_result()->num_rows > 0) {
        echo json_encode(['status' => 'warning', 'message' => 'Bu etkinlik için zaten kaydolmuşsunuz.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $sub_check->close();

    // Aboneliği kaydet
    $insert = $conn->prepare("INSERT INTO event_subscriptions (event_id, email, subscribed_at) VALUES (?, ?, NOW())");
    $insert->bind_param("is", $event_id, $email);

    if ($insert->execute()) {
        // Güncel abone sayısını hesapla
        $count_stmt = $conn->prepare("SELECT COUNT(*) as new_count FROM event_subscriptions WHERE event_id = ? AND is_active = 1");
        $count_stmt->bind_param("i", $event_id);
        $count_stmt->execute();
        $new_count = $count_stmt->get_result()->fetch_assoc()['new_count'];

        // Bildirim e-postası göndermeyi dene (MailSender sınıfı varsa)
        if (class_exists('MailSender')) {
            try {
                $mailSender = new MailSender();
                @$mailSender->sendEventNotification($email, $event_data);
            } catch (Exception $e) {
                // E-posta hatası aboneliği engellemez
            }
        }

        echo json_encode([
            'status' => 'success',
            'message' => 'Hatırlatma kaydınız başarıyla oluşturuldu! Etkinlik yaklaşınca bilgilendirileceksiniz.',
            'new_subscriber_count' => $new_count
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Kayıt sırasında bir veritabanı hatası oluştu.'], JSON_UNESCAPED_UNICODE);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Sistem hatası: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}

$conn->close();
?>
