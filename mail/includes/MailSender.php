<?php
// filepath: c:\xampp\htdocs\hayvan_sitem\mail\includes\MailSender.php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/mail_config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class MailSender {
    private $mail;
    private $debug;
    
    public function __construct($debug = false) {
        $this->debug = $debug || MAIL_DEBUG;
        $this->mail = new PHPMailer(true);
        $this->configureSMTP();
    }
    
    private function configureSMTP() {
        try {
            // Server settings
            $this->mail->isSMTP();
            $this->mail->Host = MAIL_HOST;
            $this->mail->SMTPAuth = true;
            $this->mail->Username = MAIL_USERNAME;
            $this->mail->Password = MAIL_PASSWORD;
            $this->mail->SMTPSecure = MAIL_ENCRYPTION;
            $this->mail->Port = MAIL_PORT;
            $this->mail->CharSet = MAIL_CHARSET;
            $this->mail->Timeout = MAIL_TIMEOUT;
            
            // Debug settings
            if ($this->debug) {
                $this->mail->SMTPDebug = SMTP::DEBUG_SERVER;
                $this->mail->Debugoutput = function($str, $level) {
                    error_log("SMTP Debug ($level): $str");
                };
            }
            
            // Set from address
            $this->mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
            
        } catch (Exception $e) {
            error_log("SMTP Configuration Error: " . $e->getMessage());
            throw new Exception("Email configuration failed");
        }
    }
    
    public function sendEventNotification($to_email, $event_data) {
        try {
            $this->resetMail();
            $this->mail->addAddress($to_email);
            
            $this->mail->isHTML(true);
            $this->mail->Subject = '✅ Etkinlik Kaydınız Onaylandı - ' . $event_data['baslik'];
            
            $template = $this->loadTemplate('event_notification', $event_data);
            $this->mail->Body = $template;
            
            // Text version
            $this->mail->AltBody = $this->createTextVersion($event_data);
            
            $result = $this->mail->send();
            
            if ($result) {
                $this->logEmail('notification', $to_email, $event_data['id'], 'success');
            }
            
            return $result;
            
        } catch (Exception $e) {
            $this->logEmail('notification', $to_email, $event_data['id'], 'failed', $e->getMessage());
            error_log("Email notification error: " . $e->getMessage());
            return false;
        }
    }
    
    public function sendEventReminder($to_email, $event_data) {
        try {
            $this->resetMail();
            $this->mail->addAddress($to_email);
            
            $this->mail->isHTML(true);
            $this->mail->Subject = '🔔 Etkinlik Hatırlatması - ' . $event_data['baslik'];
            
            $template = $this->loadTemplate('event_reminder', $event_data);
            $this->mail->Body = $template;
            
            $this->mail->AltBody = $this->createReminderTextVersion($event_data);
            
            $result = $this->mail->send();
            
            if ($result) {
                $this->logEmail('reminder', $to_email, $event_data['id'], 'success');
            }
            
            return $result;
            
        } catch (Exception $e) {
            $this->logEmail('reminder', $to_email, $event_data['id'], 'failed', $e->getMessage());
            error_log("Email reminder error: " . $e->getMessage());
            return false;
        }
    }
    
    private function resetMail() {
        $this->mail->clearAddresses();
        $this->mail->clearAttachments();
        $this->mail->clearCustomHeaders();
        $this->mail->clearReplyTos();
    }
    
    private function loadTemplate($template_name, $data) {
        $template_path = EMAIL_TEMPLATES_PATH . $template_name . '.php';
        
        if (file_exists($template_path)) {
            ob_start();
            extract($data);
            include $template_path;
            return ob_get_clean();
        }
        
        return $this->getDefaultTemplate($template_name, $data);
    }
    
    private function getDefaultTemplate($type, $data) {
        if ($type === 'event_reminder') {
            return $this->createReminderTemplate($data);
        }
        return $this->createNotificationTemplate($data);
    }
    
    private function createNotificationTemplate($data) {
        $event_date = new DateTime($data['etkinlik_tarihi']);
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #ec4899, #be185d); color: white; padding: 20px; text-align: center; }
                .content { background: white; padding: 30px; border: 1px solid #ddd; }
                .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🐾 Yuva Ol</h1>
                    <p>Etkinlik Bildirimi</p>
                </div>
                <div class='content'>
                    <h2>✅ Etkinlik kaydınız onaylandı!</h2>
                    <p>Merhaba,</p>
                    <p><strong>{$data['baslik']}</strong> etkinliği için hatırlatma kaydınız başarıyla alınmıştır.</p>
                    
                    <div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                        <h3>📅 Etkinlik Detayları</h3>
                        <p><strong>📝 Etkinlik:</strong> {$data['baslik']}</p>
                        <p><strong>📅 Tarih:</strong> " . $event_date->format('d.m.Y l') . "</p>
                        " . (!empty($data['etkinlik_saati']) ? "<p><strong>🕒 Saat:</strong> " . substr($data['etkinlik_saati'], 0, 5) . "</p>" : "") . "
                        " . (!empty($data['adres']) ? "<p><strong>📍 Adres:</strong> {$data['adres']}</p>" : "") . "
                        <p><strong>📋 Açıklama:</strong> {$data['aciklama']}</p>
                    </div>
                    
                    <p>Etkinlik tarihi yaklaştığında size hatırlatma e-postası göndereceğiz.</p>
                </div>
                <div class='footer'>
                    <p>Bu e-posta Yuva Ol sistemi tarafından otomatik olarak gönderilmiştir.</p>
                    <p>🐾 Onlar İçin Yuva, Senin İçin Dostluk 🐾</p>
                </div>
            </div>
        </body>
        </html>";
    }
    
    private function createReminderTemplate($data) {
        $event_date = new DateTime($data['etkinlik_tarihi']);
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #f59e0b, #d97706); color: white; padding: 20px; text-align: center; }
                .content { background: white; padding: 30px; border: 1px solid #ddd; }
                .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666; }
                .alert { background: #fef3c7; border: 1px solid #f59e0b; padding: 15px; border-radius: 8px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🔔 Etkinlik Hatırlatması</h1>
                    <p>Yuva Ol</p>
                </div>
                <div class='content'>
                    <div class='alert'>
                        <h2>⏰ Etkinlik Yaklaşıyor!</h2>
                    </div>
                    
                    <p>Merhaba,</p>
                    <p>Kayıt olduğunuz <strong>{$data['baslik']}</strong> etkinliği yaklaşıyor!</p>
                    
                    <div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                        <h3>📅 Etkinlik Detayları</h3>
                        <p><strong>📝 Etkinlik:</strong> {$data['baslik']}</p>
                        <p><strong>📅 Tarih:</strong> " . $event_date->format('d.m.Y l') . "</p>
                        " . (!empty($data['etkinlik_saati']) ? "<p><strong>🕒 Saat:</strong> " . substr($data['etkinlik_saati'], 0, 5) . "</p>" : "") . "
                        " . (!empty($data['adres']) ? "<p><strong>📍 Adres:</strong> {$data['adres']}</p>" : "") . "
                    </div>
                    
                    <p>Etkinliğe katılmayı unutmayın! 🐾</p>
                </div>
                <div class='footer'>
                    <p>Bu hatırlatma Yuva Ol sistemi tarafından otomatik olarak gönderilmiştir.</p>
                    <p>🐾 Onlar İçin Yuva, Senin İçin Dostluk 🐾</p>
                </div>
            </div>
        </body>
        </html>";
    }
    
    private function createTextVersion($data) {
        $event_date = new DateTime($data['etkinlik_tarihi']);
        return "Etkinlik Bildirimi - Yuva Ol\n\n" .
               "Etkinlik kaydınız onaylandı!\n\n" .
               "Etkinlik: {$data['baslik']}\n" .
               "Tarih: " . $event_date->format('d.m.Y l') . "\n" .
               (!empty($data['etkinlik_saati']) ? "Saat: " . substr($data['etkinlik_saati'], 0, 5) . "\n" : "") .
               (!empty($data['adres']) ? "Adres: {$data['adres']}\n" : "") .
               "Açıklama: {$data['aciklama']}\n\n" .
               "Onlar İçin Yuva, Senin İçin Dostluk";
    }
    
    private function createReminderTextVersion($data) {
        $event_date = new DateTime($data['etkinlik_tarihi']);
        return "Etkinlik Hatırlatması - Yuva Ol\n\n" .
               "Etkinlik yaklaşıyor!\n\n" .
               "Etkinlik: {$data['baslik']}\n" .
               "Tarih: " . $event_date->format('d.m.Y l') . "\n" .
               (!empty($data['etkinlik_saati']) ? "Saat: " . substr($data['etkinlik_saati'], 0, 5) . "\n" : "") .
               (!empty($data['adres']) ? "Adres: {$data['adres']}\n" : "") .
               "\nEtkinliğe katılmayı unutmayın!";
    }
    
    private function logEmail($type, $email, $event_id, $status, $error = null) {
        $log_message = date('Y-m-d H:i:s') . " | " . 
                      strtoupper($type) . " | " . 
                      $email . " | " . 
                      "Event ID: $event_id | " . 
                      strtoupper($status);
        
        if ($error) {
            $log_message .= " | Error: $error";
        }
        
        error_log($log_message);
    }
    
    public function testConnection() {
        try {
            $this->mail->smtpConnect();
            $this->mail->smtpClose();
            return true;
        } catch (Exception $e) {
            error_log("SMTP Test failed: " . $e->getMessage());
            return false;
        }
    }
}
?>