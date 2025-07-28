<?php
// filepath: c:\xampp\htdocs\hayvan_sitem\subscribe_event.php

header('Content-Type: application/json');
require_once 'includes/db.php';
require_once 'mail/includes/MailSender.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_id = isset($_POST['event_id']) ? (int)$_POST['event_id'] : 0;
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    
    // Validation
    if ($event_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Geçersiz etkinlik ID']);
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Geçersiz e-posta adresi']);
        exit;
    }
    
    try {
        // Check if event exists and is active
        $event_check = $conn->prepare("SELECT id, baslik, etkinlik_tarihi, etkinlik_saati, aciklama, adres, kategori FROM hayvan_etkinlikleri WHERE id = ? AND aktif = 1");
        $event_check->bind_param("i", $event_id);
        $event_check->execute();
        $event_result = $event_check->get_result();
        
        if ($event_result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Etkinlik bulunamadı veya aktif değil']);
            exit;
        }
        
        $event_data = $event_result->fetch_assoc();
        
        // Check if event is in the past
        if (strtotime($event_data['etkinlik_tarihi']) < strtotime('today')) {
            echo json_encode(['success' => false, 'message' => 'Geçmiş etkinlikler için hatırlatma oluşturulamaz']);
            exit;
        }
        
        // Check if already subscribed
        $check_subscription = $conn->prepare("SELECT id FROM event_subscriptions WHERE event_id = ? AND email = ?");
        $check_subscription->bind_param("is", $event_id, $email);
        $check_subscription->execute();
        
        if ($check_subscription->get_result()->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Bu etkinlik için zaten kayıt oldunuz']);
            exit;
        }
        
        // Insert subscription
        $insert_subscription = $conn->prepare("INSERT INTO event_subscriptions (event_id, email, subscribed_at) VALUES (?, ?, NOW())");
        $insert_subscription->bind_param("is", $event_id, $email);
        
        if ($insert_subscription->execute()) {
            // Send confirmation email
            $mailSender = new MailSender();
            $email_sent = $mailSender->sendEventNotification($email, $event_data);
            
            if ($email_sent) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Kayıt başarılı! Onay e-postası gönderildi.'
                ]);
            } else {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Kayıt başarılı! (E-posta gönderilemedi, lütfen ayarları kontrol edin)'
                ]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Kayıt sırasında hata oluştu']);
        }
        
    } catch (Exception $e) {
        error_log("Subscription error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Sistem hatası oluştu: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Geçersiz istek methodu']);
}

$conn->close();
?>