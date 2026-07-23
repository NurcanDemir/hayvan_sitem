<?php
// filepath: c:\xampp\htdocs\hayvan_sitem\mail\includes\MailSender.php
<<<<<<< Updated upstream
require_once __DIR__ . '/../../vendor/autoload.php';
=======

>>>>>>> Stashed changes
require_once __DIR__ . '/../config/mail_config.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class MailSender {
<<<<<<< Updated upstream
    private $mailer;
    
    public function __construct() {
        $this->mailer = new PHPMailer(true);
=======
    private $mail;
    
    public function __construct() {
        $this->mail = new PHPMailer(true);
>>>>>>> Stashed changes
        $this->configureSMTP();
    }
    
    private function configureSMTP() {
        $config = MailConfig::getSMTPConfig();
        
        try {
<<<<<<< Updated upstream
            // SMTP configuration
            $this->mailer->isSMTP();
            $this->mailer->Host = $config['host'];
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $config['username'];
            $this->mailer->Password = $config['password'];
            $this->mailer->SMTPSecure = $config['encryption'];
            $this->mailer->Port = $config['port'];
            $this->mailer->CharSet = 'UTF-8';
            
            // From address
            $this->mailer->setFrom($config['from_email'], $config['from_name']);
=======
            $this->mail->isSMTP();
            $this->mail->Host = MAIL_HOST;
            $this->mail->SMTPAuth = true;
            $this->mail->Username = MAIL_USERNAME;
            $this->mail->Password = MAIL_PASSWORD;
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mail->Port = MAIL_PORT;
            $this->mail->CharSet = MAIL_CHARSET;
            
            $this->mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
>>>>>>> Stashed changes
            
            if (MAIL_DEBUG) {
                $this->mail->SMTPDebug = SMTP::DEBUG_SERVER;
            }
        } catch (Exception $e) {
            error_log("SMTP Configuration Error: " . $e->getMessage());
        }
    }
    
<<<<<<< Updated upstream
    public function sendVerificationEmail($to_email, $username, $verification_token) {
        try {
            // FIXED: Point to the correct file location
            $verification_link = MailConfig::SITE_URL . "/verify_email.php?token=" . $verification_token;
            
            $this->mailer->addAddress($to_email, $username);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = MailConfig::SITE_NAME . ' - Email Adresinizi Doğrulayın';
            
            $html_body = $this->getVerificationEmailTemplate($username, $verification_link);
            $this->mailer->Body = $html_body;
            
            // Text version
            $this->mailer->AltBody = "Merhaba $username,\n\n" .
                "Hesabınızı aktifleştirmek için bu linke tıklayın:\n" .
                $verification_link . "\n\n" .
                "Bu link 24 saat geçerlidir.\n\n" .
                MailConfig::SITE_NAME . " Ekibi";
            
            $result = $this->mailer->send();
            $this->mailer->clearAddresses();
            
            return ['success' => true, 'message' => 'Email gönderildi'];
=======
    /**
     * Send email verification to new users
     */
    public function sendEmailVerification($email, $userData) {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($email, $userData['kullanici_adi']);
            
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Yuva Ol - E-posta Doğrulama';
            
            // Load email template
            $verification_url = "http://localhost/hayvan_sitem/verify_email.php?token=" . $userData['verification_token'];
            $emailBody = $this->getEmailVerificationTemplate($userData, $verification_url);
            
            $this->mail->Body = $emailBody;
            $this->mail->AltBody = $this->getEmailVerificationTextVersion($userData, $verification_url);
>>>>>>> Stashed changes
            
            return $this->mail->send();
        } catch (Exception $e) {
<<<<<<< Updated upstream
            error_log("Email Send Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Email gönderilemedi: ' . $e->getMessage()];
        }
    }
    
    public function sendPasswordResetEmail($to_email, $username, $reset_token) {
        try {
            $reset_link = MailConfig::SITE_URL . "/reset_password.php?token=" . $reset_token;
            
            $this->mailer->addAddress($to_email, $username);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = MailConfig::SITE_NAME . ' - Şifre Sıfırlama';
            
            $html_body = $this->getPasswordResetEmailTemplate($username, $reset_link);
            $this->mailer->Body = $html_body;
            
            // Text version
            $this->mailer->AltBody = "Merhaba $username,\n\n" .
                "Şifrenizi sıfırlamak için bu linke tıklayın:\n" .
                $reset_link . "\n\n" .
                "Bu link 1 saat geçerlidir.\n" .
                "Eğer bu talebi siz yapmadıysanız, bu emaili görmezden gelin.\n\n" .
                MailConfig::SITE_NAME . " Ekibi";
            
            $result = $this->mailer->send();
            $this->mailer->clearAddresses();
            
            return ['success' => true, 'message' => 'Şifre sıfırlama emaili gönderildi'];
=======
            error_log("Email Verification Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send password reset email
     */
    public function sendPasswordReset($email, $userData, $resetToken) {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($email, $userData['kullanici_adi']);
            
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Yuva Ol - Şifre Sıfırlama';
            
            // Load email template
            $reset_url = "http://localhost/hayvan_sitem/reset_password.php?token=" . $resetToken;
            $emailBody = $this->getPasswordResetTemplate($userData, $reset_url);
            
            $this->mail->Body = $emailBody;
            $this->mail->AltBody = $this->getPasswordResetTextVersion($userData, $reset_url);
>>>>>>> Stashed changes
            
            return $this->mail->send();
        } catch (Exception $e) {
<<<<<<< Updated upstream
            error_log("Password Reset Email Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Email gönderilemedi: ' . $e->getMessage()];
        }
    }
    
    private function getVerificationEmailTemplate($username, $verification_link) {
        return "
        <!DOCTYPE html>
        <html lang='tr'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Email Doğrulama</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
                .btn { display: inline-block; background: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 20px 0; }
                .btn:hover { background: #218838; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 14px; }
                .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🐾 " . MailConfig::SITE_NAME . "</h1>
                    <p>Hoş geldiniz!</p>
                </div>
                <div class='content'>
                    <h2>Merhaba $username!</h2>
                    <p>Sıcak Patizi platformuna hoş geldiniz! Hesabınızı aktifleştirmek için aşağıdaki butona tıklayın:</p>
                    
                    <div style='text-align: center;'>
                        <a href='$verification_link' class='btn'>📧 Email Adresimi Doğrula</a>
                    </div>
                    
                    <div class='warning'>
                        <strong>⚠️ Önemli:</strong>
                        <ul>
                            <li>Bu doğrulama linki 24 saat geçerlidir</li>
                            <li>Eğer bu kaydı siz yapmadıysanız, bu emaili görmezden gelin</li>
                            <li>Link çalışmıyorsa, aşağıdaki adresi tarayıcınıza kopyalayın:</li>
                        </ul>
                        <p style='word-break: break-all; background: white; padding: 10px; border-radius: 3px; font-family: monospace;'>
                            $verification_link
                        </p>
                    </div>
                    
                    <p>Email doğrulamanızdan sonra şunları yapabileceksiniz:</p>
                    <ul>
                        <li>🏠 Hayvan ilanları verebilirsiniz</li>
                        <li>❤️ Favori hayvanlarınızı kaydedebilirsiniz</li>
                        <li>📞 Sahiplendirme talepleri oluşturabilirsiniz</li>
                        <li>🎪 Etkinliklere katılabilirsiniz</li>
                    </ul>
                </div>
                <div class='footer'>
                    <p>Bu email otomatik olarak gönderilmiştir.</p>
                    <p><strong>" . MailConfig::SITE_NAME . "</strong> - Hayvanlar için bir yuva</p>
=======
            error_log("Password Reset Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Email verification template
     */
    private function getEmailVerificationTemplate($userData, $verification_url) {
        return '
        <!DOCTYPE html>
        <html lang="tr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>E-posta Doğrulama</title>
        </head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f8f9fa;">
            <div style="max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                
                <!-- Header -->
                <div style="text-align: center; padding: 20px 0; border-bottom: 3px solid #ec4899;">
                    <div style="font-size: 36px; margin-bottom: 10px;">🐾</div>
                    <h1 style="color: #ec4899; margin: 0; font-size: 28px;">Yuva Ol</h1>
                    <p style="color: #666; margin: 5px 0; font-style: italic;">Onlar İçin Yuva, Senin İçin Dostluk</p>
                </div>
                
                <!-- Content -->
                <div style="padding: 30px 0;">
                    <h2 style="color: #333; margin-bottom: 20px;">Merhaba ' . htmlspecialchars($userData['kullanici_adi']) . '! 👋</h2>
                    
                    <p style="font-size: 16px; margin-bottom: 20px;">
                        <strong>Yuva Ol</strong> platformuna hoş geldiniz! Hesabınızı aktifleştirmek için e-posta adresinizi doğrulamanız gerekmektedir.
                    </p>
                    
                    <div style="background: linear-gradient(135deg, #fdf2f8, #fce7f3); padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #ec4899;">
                        <p style="margin: 0; color: #be185d; font-weight: bold;">
                            🔒 Güvenlik için e-posta adresinizi doğrulayın
                        </p>
                    </div>
                    
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="' . $verification_url . '" 
                           style="display: inline-block; background: linear-gradient(135deg, #ec4899, #be185d); color: white; text-decoration: none; padding: 15px 30px; border-radius: 50px; font-weight: bold; font-size: 16px; box-shadow: 0 4px 15px rgba(236, 72, 153, 0.3);">
                            ✅ E-postamı Doğrula
                        </a>
                    </div>
                    
                    <div style="background: #f1f5f9; padding: 15px; border-radius: 8px; margin: 20px 0;">
                        <p style="margin: 0; font-size: 14px; color: #64748b;">
                            <strong>🔗 Link çalışmıyor mu?</strong><br>
                            Aşağıdaki bağlantıyı tarayıcınıza kopyalayın:<br>
                            <code style="background: white; padding: 5px; border-radius: 3px; word-break: break-all;">' . $verification_url . '</code>
                        </p>
                    </div>
                    
                    <div style="background: #fef3cd; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #fbbf24;">
                        <p style="margin: 0; font-size: 14px; color: #92400e;">
                            <strong>⚠️ Önemli:</strong> Bu doğrulama linki 24 saat geçerlidir. Eğer siz bu hesabı oluşturmadıysanız, bu e-postayı dikkate almayın.
                        </p>
                    </div>
                </div>
                
                <!-- Footer -->
                <div style="border-top: 2px solid #ec4899; padding-top: 20px; margin-top: 30px;">
                    <div style="text-align: center;">
                        <p style="margin: 0; font-style: italic; color: #be185d; font-weight: bold;">
                            🐾 "Onlar İçin Yuva, Senin İçin Dostluk" 🐾
                        </p>
                    </div>
                    
                    <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #dee2e6;">
                        <p style="font-size: 12px; color: #666; margin: 3px 0; text-align: center;">
                            © 2025 <strong>Yuva Ol</strong> - Hayvan Sahiplendirme Platformu
                        </p>
                        <p style="font-size: 11px; color: #999; margin: 3px 0; text-align: center;">
                            Sokak hayvanlarının korunması ve sahiplendirilmesi amacıyla hizmet vermektedir.
                        </p>
                        <p style="font-size: 11px; color: #999; margin: 8px 0; text-align: center;">
                            📧 info@yuvaol.com | 📞 +90 312 555 01 23 | 🌐 www.yuvaol.com
                        </p>
                    </div>
>>>>>>> Stashed changes
                </div>
            </div>
        </body>
        </html>';
    }
    
<<<<<<< Updated upstream
    private function getPasswordResetEmailTemplate($username, $reset_link) {
        return "
        <!DOCTYPE html>
        <html lang='tr'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Şifre Sıfırlama</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #dc2626, #991b1b); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
                .btn { display: inline-block; background: #dc2626; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 20px 0; }
                .btn:hover { background: #991b1b; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 14px; }
                .warning { background: #fef2f2; border: 1px solid #fecaca; padding: 15px; border-radius: 5px; margin: 20px 0; }
                .security-note { background: #fff7ed; border: 1px solid #fed7aa; padding: 15px; border-radius: 5px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🔐 " . MailConfig::SITE_NAME . "</h1>
                    <p>Şifre Sıfırlama Talebi</p>
                </div>
                <div class='content'>
                    <h2>Merhaba $username!</h2>
                    <p>Hesabınız için şifre sıfırlama talebinde bulunuldu. Şifrenizi sıfırlamak için aşağıdaki butona tıklayın:</p>
                    
                    <div style='text-align: center;'>
                        <a href='$reset_link' class='btn'>🔑 Şifremi Sıfırla</a>
                    </div>
                    
                    <div class='warning'>
                        <strong>⚠️ GÜVENLİK UYARISI:</strong>
                        <ul>
                            <li>Bu link yalnızca 1 saat geçerlidir</li>
                            <li>Link yalnızca bir kez kullanılabilir</li>
                            <li>Eğer bu talebi siz yapmadıysanız, bu emaili görmezden gelin</li>
                            <li>Şüpheli bir durum varsa hemen bizimle iletişime geçin</li>
                        </ul>
                    </div>
                    
                    <div class='security-note'>
                        <strong>🛡️ Güvenlik İpuçları:</strong>
                        <ul>
                            <li>Güçlü bir şifre seçin (en az 6 karakter)</li>
                            <li>Büyük/küçük harf, rakam ve özel karakter kullanın</li>
                            <li>Şifrenizi kimseyle paylaşmayın</li>
                            <li>Düzenli olarak şifrenizi değiştirin</li>
                        </ul>
                    </div>
                    
                    <p style='font-size: 12px; color: #666; margin-top: 20px;'>
                        Link çalışmıyorsa, aşağıdaki adresi tarayıcınıza kopyalayın:<br>
                        <span style='word-break: break-all; background: white; padding: 10px; border-radius: 3px; font-family: monospace; display: block; margin-top: 10px;'>
                            $reset_link
                        </span>
                    </p>
                </div>
                <div class='footer'>
                    <p>Bu email otomatik olarak gönderilmiştir.</p>
                    <p><strong>" . MailConfig::SITE_NAME . "</strong> - Hayvanlar için bir yuva</p>
                    <p style='margin-top: 15px; font-size: 12px; color: #999;'>
                        Eğer bu talebi siz yapmadıysanız, hesabınız güvende - bu emaili görmezden gelebilirsiniz.
                    </p>
=======
    /**
     * Password reset template
     */
    private function getPasswordResetTemplate($userData, $reset_url) {
        return '
        <!DOCTYPE html>
        <html lang="tr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Şifre Sıfırlama</title>
        </head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f8f9fa;">
            <div style="max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                
                <!-- Header -->
                <div style="text-align: center; padding: 20px 0; border-bottom: 3px solid #f59e0b;">
                    <div style="font-size: 36px; margin-bottom: 10px;">🔐</div>
                    <h1 style="color: #f59e0b; margin: 0; font-size: 28px;">Şifre Sıfırlama</h1>
                    <p style="color: #666; margin: 5px 0; font-style: italic;">Yuva Ol - Güvenli Giriş</p>
                </div>
                
                <!-- Content -->
                <div style="padding: 30px 0;">
                    <h2 style="color: #333; margin-bottom: 20px;">Merhaba ' . htmlspecialchars($userData['kullanici_adi']) . '! 👋</h2>
                    
                    <p style="font-size: 16px; margin-bottom: 20px;">
                        Hesabınız için şifre sıfırlama talebinde bulundunuz. Yeni şifre oluşturmak için aşağıdaki butona tıklayın.
                    </p>
                    
                    <div style="background: linear-gradient(135deg, #fef3cd, #fde68a); padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #f59e0b;">
                        <p style="margin: 0; color: #92400e; font-weight: bold;">
                            🔒 Güvenli şifre sıfırlama işlemi
                        </p>
                    </div>
                    
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="' . $reset_url . '" 
                           style="display: inline-block; background: linear-gradient(135deg, #f59e0b, #d97706); color: white; text-decoration: none; padding: 15px 30px; border-radius: 50px; font-weight: bold; font-size: 16px; box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);">
                            🔑 Şifremi Sıfırla
                        </a>
                    </div>
                    
                    <div style="background: #f1f5f9; padding: 15px; border-radius: 8px; margin: 20px 0;">
                        <p style="margin: 0; font-size: 14px; color: #64748b;">
                            <strong>🔗 Link çalışmıyor mu?</strong><br>
                            Aşağıdaki bağlantıyı tarayıcınıza kopyalayın:<br>
                            <code style="background: white; padding: 5px; border-radius: 3px; word-break: break-all;">' . $reset_url . '</code>
                        </p>
                    </div>
                    
                    <div style="background: #fee2e2; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #ef4444;">
                        <p style="margin: 0; font-size: 14px; color: #991b1b;">
                            <strong>⚠️ Güvenlik Uyarısı:</strong><br>
                            • Bu link 1 saat geçerlidir<br>
                            • Eğer bu talebi siz yapmadıysanız, bu e-postayı dikkate almayın<br>
                            • Şifreniz değiştirilmeyecektir
                        </p>
                    </div>
                </div>
                
                <!-- Footer -->
                <div style="border-top: 2px solid #f59e0b; padding-top: 20px; margin-top: 30px;">
                    <div style="text-align: center;">
                        <p style="margin: 0; font-style: italic; color: #d97706; font-weight: bold;">
                            🐾 "Güvenli Platform, Güvenli Sahiplendirme" 🐾
                        </p>
                    </div>
                    
                    <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #dee2e6;">
                        <p style="font-size: 12px; color: #666; margin: 3px 0; text-align: center;">
                            © 2025 <strong>Yuva Ol</strong> - Hayvan Sahiplendirme Platformu
                        </p>
                        <p style="font-size: 11px; color: #999; margin: 8px 0; text-align: center;">
                            📧 info@yuvaol.com | 📞 +90 312 555 01 23 | 🌐 www.yuvaol.com
                        </p>
                    </div>
>>>>>>> Stashed changes
                </div>
            </div>
        </body>
        </html>';
    }
<<<<<<< Updated upstream
=======
    
    /**
     * Text versions for email clients that don't support HTML
     */
    private function getEmailVerificationTextVersion($userData, $verification_url) {
        return "
Yuva Ol - E-posta Doğrulama

Merhaba " . $userData['kullanici_adi'] . "!

Yuva Ol platformuna hoş geldiniz! Hesabınızı aktifleştirmek için e-posta adresinizi doğrulamanız gerekmektedir.

Doğrulama linki: " . $verification_url . "

Bu link 24 saat geçerlidir. Eğer siz bu hesabı oluşturmadıysanız, bu e-postayı dikkate almayın.

© 2025 Yuva Ol - Hayvan Sahiplendirme Platformu
        ";
    }
    
    private function getPasswordResetTextVersion($userData, $reset_url) {
        return "
Yuva Ol - Şifre Sıfırlama

Merhaba " . $userData['kullanici_adi'] . "!

Hesabınız için şifre sıfırlama talebinde bulundunuz.

Şifre sıfırlama linki: " . $reset_url . "

Bu link 1 saat geçerlidir. Eğer bu talebi siz yapmadıysanız, bu e-postayı dikkate almayın.

© 2025 Yuva Ol - Hayvan Sahiplendirme Platformu
        ";
    }
>>>>>>> Stashed changes
}
?>