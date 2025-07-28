<?php
// filepath: c:\xampp\htdocs\hayvan_sitem\mail\email_templates\event_reminder.php
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Etkinlik Hatırlatması</title>
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
        .alert-box {
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            border: 2px solid #f59e0b;
            border-radius: 12px;
            padding: 25px;
            margin: 25px 0;
            text-align: center;
            animation: pulse 2s infinite;
        }
        .event-card {
            background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
            border: 1px solid #0ea5e9;
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
        .countdown {
            background: #fef3c7;
            border: 2px solid #f59e0b;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            margin: 20px 0;
            font-weight: bold;
            color: #92400e;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); }
            100% { transform: scale(1); }
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
            <h1>🔔 Etkinlik Hatırlatması</h1>
            <p style="margin: 10px 0; font-size: 18px;">Yuva Ol</p>
        </div>
        
        <div class="content">
            <div class="alert-box">
                <h2 style="margin: 0; color: #d97706; font-size: 24px;">⏰ Etkinlik Yaklaşıyor!</h2>
                <p style="margin: 10px 0; font-size: 16px; color: #92400e;">
                    Unutmayın, kayıt olduğunuz etkinlik çok yakında başlayacak!
                </p>
            </div>
            
            <?php 
            $event_date = new DateTime($etkinlik_tarihi);
            $now = new DateTime();
            $diff = $now->diff($event_date);
            
            if ($diff->days == 0) {
                $time_message = "🚨 <strong>BUGÜN!</strong>";
            } elseif ($diff->days == 1) {
                $time_message = "📅 <strong>YARIN!</strong>";
            } else {
                $time_message = "📆 <strong>" . $diff->days . " gün sonra</strong>";
            }
            ?>
            
            <div class="countdown">
                <?= $time_message ?>
            </div>
            
            <h2 style="color: #f59e0b; margin-bottom: 20px;">Merhaba! 👋</h2>
            
            <p style="font-size: 16px; margin-bottom: 25px;">
                Kayıt olduğunuz <strong><?= htmlspecialchars($baslik) ?></strong> etkinliği yaklaşıyor!
            </p>
            
            <div class="event-card">
                <h3 style="color: #0369a1; margin-top: 0;">📅 Etkinlik Detayları</h3>
                
                <div class="event-detail">
                    <span class="icon">📝</span>
                    <strong>Etkinlik:</strong> <?= htmlspecialchars($baslik) ?>
                </div>
                
                <div class="event-detail">
                    <span class="icon">📅</span>
                    <strong>Tarih:</strong> <?= $event_date->format('d.m.Y l') ?>
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
                
                <?php if (!empty($aciklama)): ?>
                <div class="event-detail">
                    <span class="icon">📋</span>
                    <strong>Açıklama:</strong> <?= htmlspecialchars($aciklama) ?>
                </div>
                <?php endif; ?>
            </div>
            
            <div style="background: #dcfce7; border: 1px solid #16a34a; padding: 20px; border-radius: 8px; text-align: center; margin: 25px 0;">
                <h3 style="color: #15803d; margin: 0; font-size: 20px;">🐾 Etkinliğe Katılmayı Unutmayın!</h3>
                <p style="margin: 10px 0; color: #15803d; font-size: 16px;">
                    Dostlarımız için düzenlenen bu özel etkinlikte sizi görmek için sabırsızlanıyoruz.
                </p>
            </div>
            
            <div style="background: #eff6ff; padding: 15px; border-radius: 8px; border-left: 4px solid #3b82f6; margin: 20px 0;">
                <p style="margin: 0;"><strong>💡 Hatırlatma:</strong> Etkinliğe katılırken yanınıza su, rahat ayakkabı ve pozitif enerjinizi almayı unutmayın!</p>
            </div>
            
            <p style="text-align: center; margin-top: 30px; font-size: 18px;">
                Görüşmek üzere! 🙏<br>
                <strong>Yuva Ol Ekibi</strong>
            </p>
        </div>
        
        <div class="footer">
            <p>Bu hatırlatma <strong>Yuva Ol</strong> sistemi tarafından otomatik olarak gönderilmiştir.</p>
            <p style="margin-top: 10px; font-style: italic;">
                🐾 <strong>Onlar İçin Yuva, Senin İçin Dostluk</strong> 🐾
            </p>
            <p style="margin-top: 15px; font-size: 12px; color: #999;">
                Bu hatırlatmayı aldığınız için <?= htmlspecialchars($baslik) ?> etkinliğine kayıt olmuşsunuz.
            </p>
        </div>
    </div>
</body>
</html>