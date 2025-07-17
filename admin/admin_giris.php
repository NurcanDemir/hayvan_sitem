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
    <title>Admin Giriş</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 15px;
            margin: auto;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="card shadow-sm">
            <div class="card-header text-center">
                <h3>Admin Paneli Giriş</h3>
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