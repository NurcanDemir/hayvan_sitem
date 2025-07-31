<?php
// filepath: mail/config/mail_config.php
class MailConfig {
    // Gmail SMTP settings
    const SMTP_HOST = 'smtp.gmail.com';
    const SMTP_PORT = 587;
    const SMTP_USERNAME = 'nurcanss.demirr1907@gmail.com'; // Your Gmail
    const SMTP_PASSWORD = 'cczv uiqo oqrd vsea';    // Gmail App Password
    const SMTP_ENCRYPTION = 'tls';
    
    // From email settings
    const FROM_EMAIL = 'nurcanss.demirr1907@gmail.com';
    const FROM_NAME = 'Hayvan Dostları';
    
    // Site settings
    const SITE_NAME = 'Hayvan Dostları';
    const SITE_URL = 'http://localhost/hayvan_sitem';
    
    public static function getSMTPConfig() {
        return [
            'host' => self::SMTP_HOST,
            'port' => self::SMTP_PORT,
            'username' => self::SMTP_USERNAME,
            'password' => self::SMTP_PASSWORD,
            'encryption' => self::SMTP_ENCRYPTION,
            'from_email' => self::FROM_EMAIL,
            'from_name' => self::FROM_NAME
        ];
    }
}
?>