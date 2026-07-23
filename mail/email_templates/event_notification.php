<?php
// filepath: c:\xampp\htdocs\hayvan_sitem\mail\email_templates\event_notification.php
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Etkinlik Bildirimi</title>
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
        .event-card {
            background: linear-gradient(135deg, #fdf2f8, #fce7f3);
            border: 1px solid #f3e8ff;
            border-radius: 12px;
            padding: 25px;
            margin: 25px 0;
        }
        .event-detail {
            display: flex;
            align-items: center;
            margin: 12px 0;
            font-size: 16px;
        }
        .icon {
            margin-right: 12px;
            font-size: 18px;
            width: 20px;
        }
        .footer { 
            background: #f8f9fa; 
            padding: 25px; 
            text-align: center; 
            font-size: 14px; 
            color: #666;
            border-top: 1px solid #dee2e6;
        }
        .success-badge {
            background: #d1fae5;
            color: #065f46;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
            display: inline-block;
            margin: 10px 0;
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
            <p style="margin: 10px 0; font-size: 18px;">Etkinlik Bildirimi</p>
        </div>
        
        <div class="content">
            <div class="success-badge">
                ✅ Kayıt Başarılı!
            </div>
            
            <h2 style="color: #ec4899; margin-bottom: 20px;">Merhaba! 👋</h2>
            
            <p style="font-size: 16px; margin-bottom: 25px;">
                <strong><?= htmlspecialchars($baslik) ?></strong> etkinliği için hatırlatma kaydınız başarıyla alınmıştır.
            </p>
            
            <div class="event-card">
                <h3 style="color: #be185d; margin-top: 0;">📅 Etkinlik Detayları</h3>
                
                <div class="event-detail">
                    <span class="icon">📝</span>
                    <strong>Etkinlik:</strong> <?= htmlspecialchars($baslik) ?>
                </div>
                
                <div class="event-detail">
                    <span class="icon">📅</span>
                    <strong>Tarih:</strong> <?= (new DateTime($etkinlik_tarihi))->format('d.m.Y l') ?>
                </div>
                
                <?php if (!empty($etkinlik_saati)): ?>
                <div class="event-detail">
                    <span class="icon">🕒</span>
                    <strong>Saat:</strong> <?= substr($etkinlik_saati, 0, 5) ?>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($adres)): ?>
                <div class="event-detail">
                    <span class="icon">📍</span>
                    <strong>Adres:</strong> <?= htmlspecialchars($adres) ?>
                </div>
                <?php endif; ?>
                
                <div class="event-detail">
                    <span class="icon">📋</span>
                    <strong>Açıklama:</strong> <?= htmlspecialchars($aciklama) ?>
                </div>
                
                <?php if (!empty($kategori)): ?>
                <div class="event-detail">
                    <span class="icon">🏷️</span>
                    <strong>Kategori:</strong> <?= htmlspecialchars($kategori) ?>
                </div>
                <?php endif; ?>
            </div>
            
            <div style="background: #e0f2fe; padding: 15px; border-radius: 8px; border-left: 4px solid #0277bd; margin: 20px 0;">
                <p style="margin: 0;"><strong>📨 Hatırlatma:</strong> Etkinlik tarihi yaklaştığında size hatırlatma e-postası göndereceğiz.</p>
            </div>
            
            <div style="text-align: center; margin-top: 30px; padding: 20px; background: #f0f9ff; border-radius: 8px;">
                <p style="margin: 0; color: #0369a1; font-size: 16px;">
                    Hayvanlar için birlikte güzel bir etkinlik geçireceğiz! 🐕🐱
                </p>
            </div>
            
            <p style="text-align: center; margin-top: 30px;">
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
                <p style="font-size: 10px; color: #aaa; margin: 8px 0;">
                    Bu e-postayı <strong><?= htmlspecialchars($baslik ?? 'Etkinlik') ?></strong> için kayıt olduğunuz için aldınız.
                </p>
                <p style="font-size: 10px; color: #aaa; margin: 5px 0;">
                    🔗 <a href="https://yuvaol.com" style="color: #ec4899;">www.yuvaol.com</a> | 
                    📘 <a href="https://facebook.com/yuvaol" style="color: #ec4899;">Facebook</a> | 
                    📷 <a href="https://instagram.com/yuvaol" style="color: #ec4899;">Instagram</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>