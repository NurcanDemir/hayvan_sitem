<?php
// filepath: c:\xampp\htdocs\hayvan_sitem\mail\email_templates\password_reset.php
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Şifre Sıfırlama</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            line-height: 1.6; 
            color: #333; 
            margin: 0; 
            padding: 0; 
            background-color: #f8f9fa;
        }
        .container { 
            max-width: 600px; 
            margin: 0 auto; 
            background: white;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .header { 
            background: linear-gradient(135deg, #f59e0b, #d97706); 
            color: white; 
            padding: 30px 20px; 
            text-align: center; 
        }
        .header h1 { margin: 0; font-size: 28px; }
        .content { 
            padding: 40px 30px; 
        }
        .reset-card {
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            border: 1px solid #f59e0b;
            border-radius: 12px;
            padding: 25px;
            margin: 25px 0;
            text-align: center;
        }
        .btn {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 8px;
            display: inline-block;
            margin: 20px 0;
            font-weight: bold;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        .btn:hover {
            background: linear-gradient(135deg, #d97706, #f59e0b);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
        }
        .footer { 
            background: #f8f9fa; 
            padding: 25px; 
            text-align: center; 
            font-size: 14px; 
            color: #666;
            border-top: 1px solid #dee2e6;
        }
        .warning-box {
            background: #fef3c7;
            border: 2px solid #f59e0b;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .url-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            word-break: break-all;
            font-family: monospace;
            font-size: 12px;
            margin: 15px 0;
            border: 1px solid #dee2e6;
        }
        @media only screen and (max-width: 600px) {
            .container { width: 100% !important; }
            .content { padding: 20px !important; }
            .header { padding: 20px !important; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔑 Yuva Ol</h1>
            <p style="margin: 10px 0; font-size: 18px;">Şifre Sıfırlama</p>
        </div>
        
        <div class="content">
            <h2 style="color: #f59e0b; margin-bottom: 20px;">Merhaba <?= htmlspecialchars($kullanici_adi) ?>! 👋</h2>
            
            <p style="font-size: 16px; margin-bottom: 20px;">
                Hesabınız için şifre sıfırlama talebinde bulundunuz. Yeni bir şifre oluşturmak için aşağıdaki butona tıklayın.
            </p>
            
            <div class="reset-card">
                <h3 style="margin: 0; color: #92400e; font-size: 20px;">🔐 Şifre Sıfırlama</h3>
                <p style="margin: 10px 0; color: #92400e;">
                    Güvenli bir şekilde yeni şifrenizi oluşturun.
                </p>
                
                <a href="<?= SITE_URL ?>/reset_password.php?token=<?= htmlspecialchars($reset_token) ?>" class="btn">
                    🔑 Şifremi Sıfırla
                </a>
            </div>
            
            <div class="warning-box">
                <p style="margin: 0; font-weight: bold; color: #92400e;">⚠️ Güvenlik Uyarısı:</p>
                <p style="margin: 5px 0 0 0; color: #92400e;">
                    Bu linki sadece şifrenizi değiştirmek istiyorsanız kullanın. Eğer bu talebi siz yapmadıysanız, bu e-postayı görmezden gelebilirsiniz.
                </p>
            </div>
            
            <p style="font-size: 14px; color: #666; margin-top: 20px;">
                Eğer yukarıdaki butona tıklayamıyorsanız, aşağıdaki linki tarayıcınızın adres çubuğuna kopyalayın:
            </p>
            <div class="url-box">
                <?= SITE_URL ?>/reset_password.php?token=<?= htmlspecialchars($reset_token) ?>
            </div>
            
            <div style="background: #dbeafe; padding: 15px; border-radius: 8px; border-left: 4px solid #3b82f6; margin: 20px 0;">
                <p style="margin: 0;"><strong>⏰ Süre:</strong> Bu şifre sıfırlama linki 24 saat geçerlidir.</p>
            </div>
            
            <p style="text-align: center; margin-top: 30px; font-size: 18px;">
                Güvenli kalın! 🛡️<br>
                <strong>Yuva Ol Ekibi</strong>
            </p>
        </div>
        
        <div class="footer">
            <div style="margin-bottom: 15px;">
                <p style="margin: 0; font-weight: bold; color: #333;">
                    Bu e-posta <strong>Yuva Ol</strong> sistemi tarafından otomatik olarak gönderilmiştir.
                </p>
            </div>
            
            <div style="margin: 15px 0; padding: 15px; background: linear-gradient(135deg, #fdf2f8, #fce7f3); border-radius: 8px;">
                <p style="margin: 0; font-style: italic; color: #be185d; font-weight: bold;">
                    🐾 <strong>"Onlar İçin Yuva, Senin İçin Dostluk"</strong> 🐾
                </p>
            </div>
            
            <div style="border-top: 2px solid #f59e0b; padding-top: 15px; margin-top: 20px;">
                <p style="font-size: 12px; color: #666; margin: 3px 0;">
                    © 2025 <strong>Yuva Ol</strong> - Hayvan Sahiplendirme Platformu
                </p>
                <p style="font-size: 11px; color: #999; margin: 3px 0;">
                    Sokak hayvanlarının korunması ve sahiplendirilmesi amacıyla hizmet vermektedir.
                </p>
                <p style="font-size: 11px; color: #999; margin: 8px 0;">
                    📧 info@yuvaol.com | 🆘 destek@yuvaol.com | 📞 +90 312 555 01 23
                </p>
            </div>
        </div>
    </div>
</body>
</html>