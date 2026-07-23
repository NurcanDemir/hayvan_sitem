<?php
// filepath: c:\xampp\htdocs\hayvan_sitem\mail\email_templates\email_verification.php
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-posta Doğrulama</title>
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
            background: linear-gradient(135deg, #ec4899, #be185d); 
            color: white; 
            padding: 30px 20px; 
            text-align: center; 
        }
        .header h1 { margin: 0; font-size: 28px; }
        .content { 
            padding: 40px 30px; 
        }
        .verification-card {
            background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
            border: 1px solid #0ea5e9;
            border-radius: 12px;
            padding: 25px;
            margin: 25px 0;
            text-align: center;
        }
        .btn {
            background: linear-gradient(135deg, #ec4899, #be185d);
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
            background: linear-gradient(135deg, #be185d, #ec4899);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(236, 72, 153, 0.3);
        }
        .footer { 
            background: #f8f9fa; 
            padding: 25px; 
            text-align: center; 
            font-size: 14px; 
            color: #666;
            border-top: 1px solid #dee2e6;
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
            <h1>🐾 Yuva Ol</h1>
            <p style="margin: 10px 0; font-size: 18px;">E-posta Doğrulama</p>
        </div>
        
        <div class="content">
            <h2 style="color: #ec4899; margin-bottom: 20px;">Merhaba <?= htmlspecialchars($kullanici_adi) ?>! 👋</h2>
            
            <p style="font-size: 16px; margin-bottom: 20px;">
                <strong>Yuva Ol</strong> platformuna hoş geldiniz! Hesabınızı aktifleştirmek için e-posta adresinizi doğrulamanız gerekmektedir.
            </p>
            
            <div class="verification-card">
                <h3 style="margin: 0; color: #0369a1; font-size: 20px;">📧 E-posta Doğrulama</h3>
                <p style="margin: 10px 0; color: #0369a1;">
                    Aşağıdaki butona tıklayarak e-posta adresinizi doğrulayın ve hesabınızı aktifleştirin.
                </p>
                
                <a href="<?= SITE_URL ?>/verify_email.php?token=<?= htmlspecialchars($verification_token) ?>" class="btn">
                    ✅ E-posta Adresimi Doğrula
                </a>
            </div>
            
            <div style="background: #fff3cd; padding: 15px; border-radius: 8px; border-left: 4px solid #ffc107; margin: 20px 0;">
                <p style="margin: 0;"><strong>📝 Not:</strong> Bu doğrulama linki 24 saat geçerlidir.</p>
            </div>
            
            <p style="font-size: 14px; color: #666; margin-top: 20px;">
                Eğer yukarıdaki butona tıklayamıyorsanız, aşağıdaki linki tarayıcınızın adres çubuğuna kopyalayın:
            </p>
            <div class="url-box">
                <?= SITE_URL ?>/verify_email.php?token=<?= htmlspecialchars($verification_token) ?>
            </div>
            
            <div style="background: #e0f2fe; padding: 15px; border-radius: 8px; border-left: 4px solid #0277bd; margin: 20px 0;">
                <p style="margin: 0;"><strong>🔒 Güvenlik:</strong> Eğer bu hesabı siz oluşturmadıysanız, bu e-postayı görmezden gelebilirsiniz.</p>
            </div>
            
            <p style="text-align: center; margin-top: 30px; font-size: 18px;">
                Teşekkürler! 🙏<br>
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
            
            <div style="border-top: 2px solid #ec4899; padding-top: 15px; margin-top: 20px;">
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