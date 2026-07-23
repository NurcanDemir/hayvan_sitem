<?php
<<<<<<< Updated upstream
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
    const FROM_NAME = 'Sıcak Patizi';
    
    // Site settings
    const SITE_NAME = 'Sıcak Patizi';
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
=======
// filepath: c:\xampp\htdocs\hayvan_sitem\mail\config\mail_config.php

// Email configuration settings
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'nurcanss.demirr1907@gmail.com'); // Your Gmail
define('MAIL_PASSWORD', 'cczv uiqo oqrd vsea'); // Your Gmail App Password
define('MAIL_FROM_EMAIL', 'nurcanss.demirr1907@gmail.com');
define('MAIL_FROM_NAME', 'Yuva Ol - Hayvan Sahiplendirme Platformu');
define('MAIL_ENCRYPTION', 'tls');

// Email templates path
define('EMAIL_TEMPLATES_PATH', __DIR__ . '/../email_templates/');

// Database connection path
define('DB_CONFIG_PATH', __DIR__ . '/../../includes/db.php');

// Debug mode (set to false in production)
define('MAIL_DEBUG', true);

// Default timezone
date_default_timezone_set('Europe/Istanbul');

// Email settings
define('MAIL_CHARSET', 'UTF-8');
define('MAIL_TIMEOUT', 30);

// Site URL for email links
define('SITE_URL', 'http://localhost/hayvan_sitem');

// Token expiration time (in hours)
define('RESET_TOKEN_EXPIRE_HOURS', 24);
>>>>>>> Stashed changes
?>