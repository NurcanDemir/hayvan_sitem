<?php
// filepath: c:\xampp\htdocs\hayvan_sitem\mail\config\mail_config.php

// Email configuration settings
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'nurcanss.demirr1907@gmail.com'); // Your Gmail
define('MAIL_PASSWORD', 'cczv uiqo oqrd vsea'); // Your Gmail App Password
define('MAIL_FROM_EMAIL', 'nurcanss.demirr1907@gmail.com');
define('MAIL_FROM_NAME', 'Yuva Ol - Hayvan Sitem');
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
?>