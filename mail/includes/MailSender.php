<?php
// filepath: c:\xampp\htdocs\hayvan_sitem\mail\includes\MailSender.php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../config/mail_config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class MailSender {
    private $mailer;
    
    public function __construct() {
        $this->mailer = new PHPMailer(true);
        $this->configureSMTP();
    }
    
    private function configureSMTP() {
        $config = MailConfig::getSMTPConfig();
        
        try {
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
            
        } catch (Exception $e) {
            error_log("SMTP Configuration Error: " . $e->getMessage());
        }
    }
    
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
            
        } catch (Exception $e) {
            error_log("Email Send Error: " . $e->getMessage());
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
                    <p>Hayvan Dostları platformuna hoş geldiniz! Hesabınızı aktifleştirmek için aşağıdaki butona tıklayın:</p>
                    
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
                </div>
            </div>
        </body>
        </html>";
    }
}
?>