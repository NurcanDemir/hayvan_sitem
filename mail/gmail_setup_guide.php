<?php
// filepath: c:\xampp\htdocs\hayvan_sitem\mail\gmail_setup_guide.php
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gmail Kurulum Rehberi</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        .step { background: #f8f9fa; padding: 20px; margin: 15px 0; border-radius: 8px; border-left: 4px solid #007bff; }
        .warning { background: #fff3cd; padding: 15px; border-radius: 8px; border-left: 4px solid #ffc107; }
        .success { background: #d4edda; padding: 15px; border-radius: 8px; border-left: 4px solid #28a745; }
        code { background: #f1f1f1; padding: 4px 8px; border-radius: 4px; font-family: 'Courier New', monospace; }
    </style>
</head>
<body>
    <h1>📧 Gmail SMTP Kurulum Rehberi</h1>
    
    <div class="warning">
        <h3>⚠️ Önemli Not</h3>
        <p>Gmail ile SMTP kullanmak için normal şifrenizi değil, <strong>Uygulama Şifresi</strong> kullanmalısınız.</p>
    </div>
    
    <div class="step">
        <h3>Adım 1: Google Hesap Güvenlik Ayarları</h3>
        <ol>
            <li><a href="https://myaccount.google.com/security" target="_blank">Google Hesap Güvenlik</a> sayfasına gidin</li>
            <li><strong>2 Adımlı Doğrulama</strong>'yı etkinleştirin (zorunlu)</li>
            <li>2 adımlı doğrulama aktifse devam edin</li>
        </ol>
    </div>
    
    <div class="step">
        <h3>Adım 2: Uygulama Şifresi Oluşturma</h3>
        <ol>
            <li>Google Hesap ayarlarında <strong>"Uygulama şifreleri"</strong> bölümünü bulun</li>
            <li><strong>"Uygulama seçin"</strong> menüsünden <code>Diğer (özel ad)</code> seçin</li>
            <li>Ad olarak: <code>Hayvan Sitem Email</code> yazın</li>
            <li><strong>"Oluştur"</strong> butonuna tıklayın</li>
            <li>Oluşturulan 16 haneli şifreyi kopyalayın</li>
        </ol>
    </div>
    
    <div class="step">
        <h3>Adım 3: Konfigürasyon Dosyasını Güncelleme</h3>
        <p><code>mail/config/mail_config.php</code> dosyasını açın ve şu değerleri güncelleyin:</p>
        <pre><code>
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'nurcanss.demirr1907@gmail.com');
define('MAIL_PASSWORD', 'BURAYA_16_HANELI_UYGULAMA_SIFRESI');
define('MAIL_FROM_EMAIL', 'nurcanss.demirr1907@gmail.com');
define('MAIL_FROM_NAME', 'Yuva Ol - Hayvan Sitem');
define('MAIL_ENCRYPTION', 'tls');
        </code></pre>
    </div>
    
    <div class="step">
        <h3>Adım 4: Test Etme</h3>
        <ol>
            <li><a href="test_email.php" target="_blank">Test Email Sayfası</a>'na gidin</li>
            <li>Tüm testleri çalıştırın</li>
            <li>E-posta kutunuzu kontrol edin</li>
        </ol>
    </div>
    
    <div class="success">
        <h3>✅ Başarılı Kurulum Sonrası</h3>
        <p>Kurulum başarılı olduğunda:</p>
        <ul>
            <li>Test e-postaları gelecek</li>
            <li>Kullanıcılar etkinlikler için kayıt olabilecek</li>
            <li>Otomatik hatırlatma e-postaları çalışacak</li>
        </ul>
    </div>
    
    <div class="step">
        <h3>🔧 Sorun Giderme</h3>
        <ul>
            <li><strong>Authentication failed:</strong> Uygulama şifresi yanlış veya 2FA aktif değil</li>
            <li><strong>Connection timeout:</strong> SMTP port (587) engelli olabilir</li>
            <li><strong>SSL/TLS error:</strong> Encryption ayarını kontrol edin</li>
        </ul>
    </div>
    
    <div class="step">
        <h3>📁 Dosya Yapısı</h3>
        <p>Dosyalarınızın şu şekilde olması gerekiyor:</p>
        <pre><code>
c:\xampp\htdocs\hayvan_sitem\
├── mail\
│   ├── config\mail_config.php
│   ├── includes\MailSender.php
│   ├── test_email.php
│   └── gmail_setup_guide.php
├── subscribe_event.php
└── etkinlikler.php
        </code></pre>
    </div>
</body>
</html>