<?php
// filepath: c:\xampp\htdocs\hayvan_sitem\mail\cron\send_reminders.php

// This file should be run daily via cron job
// Example cron: 0 9 * * * /usr/bin/php /path/to/send_reminders.php

set_time_limit(300); // 5 minutes max execution time
ini_set('memory_limit', '256M');

require_once __DIR__ . '/../config/mail_config.php';
require_once __DIR__ . '/../includes/MailSender.php';

// Include main database connection
if (file_exists(DB_CONFIG_PATH)) {
    require_once DB_CONFIG_PATH;
} else {
    // Fallback database connection
    require_once __DIR__ . '/../../includes/db.php';
}

// Log start
error_log("=== Email Reminder Cron Job Started: " . date('Y-m-d H:i:s') . " ===");

$mailSender = new MailSender();

// Test email connection first
if (!$mailSender->testConnection()) {
    error_log("SMTP connection test failed. Aborting reminder job.");
    exit(1);
}

// Get events happening tomorrow that have active subscriptions
$tomorrow = date('Y-m-d', strtotime('+1 day'));
$today = date('Y-m-d');

// Query for tomorrow's events
$sql = "SELECT DISTINCT 
            e.id, 
            e.baslik, 
            e.etkinlik_tarihi, 
            e.etkinlik_saati, 
            e.aciklama, 
            e.adres,
            e.kategori,
            es.email,
            es.id as subscription_id
        FROM hayvan_etkinlikleri e
        INNER JOIN event_subscriptions es ON e.id = es.event_id
        WHERE DATE(e.etkinlik_tarihi) = ? 
        AND e.aktif = 1 
        AND es.is_active = 1 
        AND es.reminded_at IS NULL
        ORDER BY e.etkinlik_tarihi, es.email";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log("Failed to prepare reminder query: " . $conn->error);
    exit(1);
}

$stmt->bind_param("s", $tomorrow);
$stmt->execute();
$result = $stmt->get_result();

$sent_count = 0;
$error_count = 0;
$processed_emails = [];

error_log("Found " . $result->num_rows . " reminders to send for date: $tomorrow");

while ($row = $result->fetch_assoc()) {
    $event_data = [
        'id' => $row['id'],
        'baslik' => $row['baslik'],
        'etkinlik_tarihi' => $row['etkinlik_tarihi'],
        'etkinlik_saati' => $row['etkinlik_saati'],
        'aciklama' => $row['aciklama'],
        'adres' => $row['adres'],
        'kategori' => $row['kategori']
    ];
    
    $email = $row['email'];
    $subscription_id = $row['subscription_id'];
    
    // Avoid duplicate emails to same address for same event
    $email_key = $email . '_' . $row['id'];
    if (in_array($email_key, $processed_emails)) {
        continue;
    }
    
    try {
        if ($mailSender->sendEventReminder($email, $event_data)) {
            // Mark as reminded
            $update_stmt = $conn->prepare("UPDATE event_subscriptions SET reminded_at = NOW() WHERE id = ?");
            $update_stmt->bind_param("i", $subscription_id);
            
            if ($update_stmt->execute()) {
                $sent_count++;
                $processed_emails[] = $email_key;
                error_log("Reminder sent successfully to: $email for event: {$row['baslik']}");
            } else {
                error_log("Failed to update reminder status for subscription ID: $subscription_id");
                $error_count++;
            }
            
            $update_stmt->close();
            
            // Small delay to avoid overwhelming SMTP server
            usleep(500000); // 0.5 seconds
            
        } else {
            $error_count++;
            error_log("Failed to send reminder to: $email for event: {$row['baslik']}");
        }
    } catch (Exception $e) {
        $error_count++;
        error_log("Exception sending reminder to $email: " . $e->getMessage());
    }
}

$stmt->close();

// Also send same-day reminders (optional - for events happening today)
$same_day_sql = "SELECT DISTINCT 
                    e.id, 
                    e.baslik, 
                    e.etkinlik_tarihi, 
                    e.etkinlik_saati, 
                    e.aciklama, 
                    e.adres,
                    e.kategori,
                    es.email,
                    es.id as subscription_id
                FROM hayvan_etkinlikleri e
                INNER JOIN event_subscriptions es ON e.id = es.event_id
                WHERE DATE(e.etkinlik_tarihi) = ? 
                AND e.aktif = 1 
                AND es.is_active = 1 
                AND es.reminded_at IS NULL
                AND TIME(e.etkinlik_tarihi) > TIME(NOW() + INTERVAL 2 HOUR)
                ORDER BY e.etkinlik_tarihi";

$same_day_stmt = $conn->prepare($same_day_sql);
if ($same_day_stmt) {
    $same_day_stmt->bind_param("s", $today);
    $same_day_stmt->execute();
    $same_day_result = $same_day_stmt->get_result();
    
    $same_day_count = 0;
    error_log("Found " . $same_day_result->num_rows . " same-day reminders to send for date: $today");
    
    while ($row = $same_day_result->fetch_assoc()) {
        $event_data = [
            'id' => $row['id'],
            'baslik' => $row['baslik'],
            'etkinlik_tarihi' => $row['etkinlik_tarihi'],
            'etkinlik_saati' => $row['etkinlik_saati'],
            'aciklama' => $row['aciklama'],
            'adres' => $row['adres'],
            'kategori' => $row['kategori']
        ];
        
        $email = $row['email'];
        $subscription_id = $row['subscription_id'];
        
        try {
            if ($mailSender->sendEventReminder($email, $event_data)) {
                $update_stmt = $conn->prepare("UPDATE event_subscriptions SET reminded_at = NOW() WHERE id = ?");
                $update_stmt->bind_param("i", $subscription_id);
                $update_stmt->execute();
                $update_stmt->close();
                
                $same_day_count++;
                error_log("Same-day reminder sent to: $email for event: {$row['baslik']}");
                
                usleep(500000); // 0.5 seconds delay
            }
        } catch (Exception $e) {
            error_log("Exception sending same-day reminder to $email: " . $e->getMessage());
        }
    }
    
    $sent_count += $same_day_count;
    $same_day_stmt->close();
}

// Cleanup old subscriptions (events older than 30 days)
$cleanup_sql = "UPDATE event_subscriptions es 
                INNER JOIN hayvan_etkinlikleri e ON es.event_id = e.id 
                SET es.is_active = 0 
                WHERE e.etkinlik_tarihi < DATE_SUB(NOW(), INTERVAL 30 DAY)";
$cleanup_result = $conn->query($cleanup_sql);
$cleaned_count = $conn->affected_rows;

if ($cleaned_count > 0) {
    error_log("Cleaned up $cleaned_count old subscriptions");
}

// Final log
$total_processed = $sent_count + $error_count;
error_log("=== Email Reminder Cron Job Completed ===");
error_log("Total processed: $total_processed");
error_log("Successfully sent: $sent_count");
error_log("Errors: $error_count");
error_log("Cleaned up old subscriptions: $cleaned_count");
error_log("Execution time: " . round(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 2) . " seconds");

$conn->close();

// Exit with appropriate code
exit($error_count > 0 ? 1 : 0);
?>