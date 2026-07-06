<?php
session_start();
include("../includes/db.php"); // Ana veritabanı bağlantınızı doğru yoldan include edin

$hata_mesaji = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kullanici_adi = $_POST['kullanici_adi'] ?? '';
    $sifre = $_POST['sifre'] ?? '';

    // Boş alan kontrolü
    if (empty($kullanici_adi) || empty($sifre)) {
        $hata_mesaji = "Kullanıcı adı ve şifre boş bırakılamaz.";
    } else {
        // Admin tablosundan kullanıcıyı çek
        $stmt = $conn->prepare("SELECT id, kullanici_adi, sifre, ad, soyad FROM admin WHERE kullanici_adi = ?");
        $stmt->bind_param("s", $kullanici_adi);
        $stmt->execute();
        $sonuc = $stmt->get_result();

        if ($sonuc->num_rows == 1) {
            $admin = $sonuc->fetch_assoc();

            // Şifre doğrulama
            if (password_verify($sifre, $admin['sifre']) || $sifre === $admin['sifre']) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_kullanici_adi'] = $admin['kullanici_adi'];
                $_SESSION['admin_ad'] = $admin['ad'] ?? 'Admin';
                $_SESSION['admin_soyad'] = $admin['soyad'] ?? 'User';
                
                header("Location: admin_panel.php");
                exit();
            } else {
                $hata_mesaji = "Geçersiz şifre.";
            }
        } else {
            $hata_mesaji = "Kullanıcı bulunamadı.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Satın Alma Yuva Ol | Admin Giriş</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --bg-light: #F8F9FA;
            --primary-pink: #FFB3C6;
            --action-mint: #A8DADC;
            --text-dark: #2B2D42;
            --brand-highlight: #3A868F;
        }

        body {
            font-family: 'Poppins', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background:
                radial-gradient(circle at 12% 18%, rgba(255, 179, 198, 0.28), transparent 45%),
                radial-gradient(circle at 85% 12%, rgba(168, 218, 220, 0.28), transparent 40%),
                var(--bg-light);
            color: var(--text-dark);
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 15px;
            margin: auto;
        }

        .card {
            border: 1px solid var(--primary-pink);
            border-radius: 16px;
            box-shadow: 0 14px 30px rgba(43, 45, 66, 0.12) !important;
        }

        .card-header {
            border-bottom: 1px solid rgba(255, 179, 198, 0.55);
            background-color: #fff;
            border-radius: 16px 16px 0 0 !important;
        }

        .form-control {
            border: 1px solid var(--primary-pink);
            border-radius: 12px;
        }

        .form-control:focus {
            border-color: var(--action-mint);
            box-shadow: 0 0 0 4px rgba(168, 218, 220, 0.35);
        }

        .btn-primary {
            background: var(--action-mint);
            color: var(--text-dark);
            border: none;
            border-radius: 14px;
            box-shadow: 0 10px 24px rgba(43, 45, 66, 0.12);
            font-weight: 600;
        }

        .btn-primary:hover,
        .btn-primary:focus {
            background: var(--action-mint);
            color: var(--text-dark);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="card shadow-sm">
            <div class="card-header text-center">
                <h3>
                    <span>Satın Alma</span>
                    <span style="color: var(--brand-highlight);">Yuva Ol</span>
                    <span>Admin Giriş</span>
                </h3>
            </div>
            <div class="card-body">
                <?php if (!empty($hata_mesaji)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?= $hata_mesaji ?>
                    </div>
                <?php endif; ?>
                <form action="admin_giris.php" method="POST">
                    <div class="mb-3">
                        <label for="kullanici_adi" class="form-label">Kullanıcı Adı</label>
                        <input type="text" class="form-control" id="kullanici_adi" name="kullanici_adi" required>
                    </div>
                    <div class="mb-3">
                        <label for="sifre" class="form-label">Şifre</label>
                        <input type="password" class="form-control" id="sifre" name="sifre" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Giriş Yap</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>