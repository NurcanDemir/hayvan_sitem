<?php
// filepath: c:\xampp\htdocs\hayvan_sitem\verify_email.php
session_start();
include("includes/db.php");

$message = '';
$success = false;

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    // Find user with this token
    $stmt = $conn->prepare("SELECT id, kullanici_adi, email, email_verified, created_at FROM kullanicilar WHERE email_verification_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Check if already verified
        if ($user['email_verified']) {
            $message = "Bu email adresi zaten doğrulanmış. Giriş yapabilirsiniz.";
        } else {
            // Check if token is not expired (24 hours)
            $created_time = strtotime($user['created_at']);
            $current_time = time();
            $time_diff = $current_time - $created_time;
            
            if ($time_diff > 86400) { // 24 hours in seconds
                $message = "Doğrulama linki süresi dolmuş. Lütfen tekrar kayıt olun.";
            } else {
                // Verify the email
                $update_stmt = $conn->prepare("UPDATE kullanicilar SET email_verified = 1, email_verified_at = NOW(), email_verification_token = NULL WHERE id = ?");
                $update_stmt->bind_param("i", $user['id']);
                
                if ($update_stmt->execute()) {
                    $success = true;
                    $message = "Email adresiniz başarıyla doğrulandı! Artık giriş yapabilirsiniz.";
                } else {
                    $message = "Doğrulama sırasında bir hata oluştu. Lütfen tekrar deneyin.";
                }
            }
        }
    } else {
        $message = "Geçersiz doğrulama linki.";
    }
} else {
    $message = "Doğrulama token'ı bulunamadı.";
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Doğrulama - Hayvan Dostları</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    </style>
</head>
<body class="min-h-screen gradient-bg flex items-center justify-center p-4">
    <div class="max-w-md w-full">
        <div class="bg-white rounded-2xl shadow-2xl p-8 text-center">
            <div class="mb-6">
                <?php if ($success): ?>
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
                        <i class="fas fa-check-circle text-3xl text-green-500"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Email Doğrulandı!</h2>
                <?php else: ?>
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-red-100 rounded-full mb-4">
                        <i class="fas fa-exclamation-triangle text-3xl text-red-500"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Doğrulama Hatası</h2>
                <?php endif; ?>
            </div>
            
            <p class="text-gray-600 mb-8"><?= htmlspecialchars($message) ?></p>
            
            <?php if ($success): ?>
                <a href="giris.php" class="inline-block bg-gradient-to-r from-green-600 to-green-700 text-white py-3 px-6 rounded-lg font-medium hover:from-green-700 hover:to-green-800 transition duration-200">
                    <i class="fas fa-sign-in-alt mr-2"></i>Giriş Yap
                </a>
            <?php else: ?>
                <div class="space-y-3">
                    <a href="kayit.php" class="block bg-gradient-to-r from-purple-600 to-purple-700 text-white py-3 px-6 rounded-lg font-medium hover:from-purple-700 hover:to-purple-800 transition duration-200">
                        <i class="fas fa-user-plus mr-2"></i>Tekrar Kayıt Ol
                    </a>
                    <a href="giris.php" class="block text-purple-600 hover:text-purple-800 font-medium">
                        <i class="fas fa-arrow-left mr-2"></i>Giriş Sayfasına Dön
                    </a>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="text-center mt-6">
            <a href="index.php" class="text-purple-100 hover:text-white">
                <i class="fas fa-home mr-2"></i>Ana Sayfaya Dön
            </a>
        </div>
    </div>
</body>
</html>