<?php
// filepath: c:\xampp\htdocs\hayvan_sitem\forgot_password.php
session_start();
include("includes/db.php");
require_once("mail/includes/MailSender.php");

// Set timezone
date_default_timezone_set('Europe/Istanbul');

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $error = "Lütfen email adresinizi girin.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Geçerli bir email adresi girin.";
    } else {
        // Check if password reset columns exist
        $check_columns = $conn->query("SHOW COLUMNS FROM kullanicilar LIKE 'password_reset_token'");
        if ($check_columns->num_rows == 0) {
            $error = "Sistem henüz şifre sıfırlama için yapılandırılmadı. Lütfen debug_password_reset.php sayfasını ziyaret edin.";
        } else {
            // Check if email exists and is verified
            $stmt = $conn->prepare("SELECT id, kullanici_adi, email_verified FROM kullanicilar WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                
                // Check if email is verified (skip if column doesn't exist)
                if (isset($user['email_verified']) && !$user['email_verified']) {
                    $error = "Bu email adresi henüz doğrulanmamış. Lütfen önce email doğrulamanızı yapın.";
                } else {
                    // Generate password reset token
                    $reset_token = bin2hex(random_bytes(32));
                    
                    // Set expiry to 1 hour from now
                    $update_stmt = $conn->prepare("UPDATE kullanicilar SET password_reset_token = ?, password_reset_expires = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE id = ?");
                    $update_stmt->bind_param("si", $reset_token, $user['id']);
                    
                    if ($update_stmt->execute()) {
                        // Verify the token was saved correctly
                        $verify_stmt = $conn->prepare("SELECT password_reset_expires, NOW() as current_ts FROM kullanicilar WHERE id = ?");
                        $verify_stmt->bind_param("i", $user['id']);
                        $verify_stmt->execute();
                        $verify_result = $verify_stmt->get_result()->fetch_assoc();
                        
                        // Send reset email
                        try {
                            $mailSender = new MailSender();
                            $email_result = $mailSender->sendPasswordResetEmail($email, $user['kullanici_adi'], $reset_token);
                            
                            if ($email_result['success']) {
                                $success = "Şifre sıfırlama linki email adresinize gönderildi. Link 1 saat geçerlidir.";
                                
                                // Debug info (remove in production)
                                $success .= "<br><small style='color: #666;'>Debug: Token expires at " . $verify_result['password_reset_expires'] . " (Current: " . $verify_result['current_ts'] . ")</small>";
                            } else {
                                $error = "Email gönderilirken bir hata oluştu: " . $email_result['message'];
                            }
                        } catch (Exception $e) {
                            // If email fails, still create a manual link for testing
                            $manual_link = "http://localhost/hayvan_sitem/reset_password.php?token=" . $reset_token;
                            $success = "Email sistemi yapılandırılmadı. Test için bu linki kullanın:<br><a href='$manual_link' target='_blank' style='color: #007bff; text-decoration: underline;'>Manual Reset Link</a>";
                        }
                    } else {
                        $error = "Token oluşturulurken bir hata oluştu: " . $conn->error;
                    }
                }
            } else {
                // Don't reveal if email exists or not for security
                $success = "Eğer bu email adresi sistemde kayıtlıysa, şifre sıfırlama linki gönderilmiştir.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Şifremi Unuttum - Hayvan Dostları</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .form-input:focus { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
    </style>
</head>
<body class="min-h-screen gradient-bg flex items-center justify-center p-4">
    <div class="max-w-md w-full">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-white rounded-full mb-4">
                <i class="fas fa-key text-2xl text-purple-600"></i>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">Şifre Sıfırlama</h1>
            <p class="text-purple-100">Şifrenizi sıfırlamak için email adresinizi girin</p>
        </div>

        <!-- Forgot Password Form -->
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <div class="text-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-2">
                    <i class="fas fa-question-circle mr-2 text-purple-600"></i>Şifremi Unuttum
                </h2>
                <p class="text-gray-600">Email adresinize şifre sıfırlama linki göndereceğiz</p>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                    <div class="flex">
                        <i class="fas fa-exclamation-triangle text-red-400 mr-3"></i>
                        <div class="text-red-700">
                            <?= htmlspecialchars($error) ?>
                            <?php if (strpos($error, 'yapılandırılmadı') !== false): ?>
                                <br><br>
                                <a href="debug_password_reset.php" class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700">
                                    <i class="fas fa-tools mr-1"></i>Fix Database
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6">
                    <div class="flex">
                        <i class="fas fa-check-circle text-green-400 mr-3"></i>
                        <div class="text-green-700"><?= $success ?></div>
                    </div>
                </div>
                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <h3 class="font-semibold text-blue-800 mb-2">📧 Email Kontrol Listesi:</h3>
                    <ul class="text-sm text-blue-700 space-y-1">
                        <li>• Gelen kutusu klasörünüzü kontrol edin</li>
                        <li>• Spam/Gereksiz klasörünü kontrol edin</li>
                        <li>• Link 1 saat geçerlidir</li>
                        <li>• Email gelmediyse tekrar deneyin</li>
                    </ul>
                </div>
                
                <div class="text-center space-y-3">
                    <a href="giris.php" class="block bg-gradient-to-r from-green-600 to-green-700 text-white py-3 px-6 rounded-lg font-medium hover:from-green-700 hover:to-green-800 transition duration-200">
                        <i class="fas fa-sign-in-alt mr-2"></i>Giriş Sayfasına Dön
                    </a>
                    <a href="debug_password_reset.php" class="block text-purple-600 hover:text-purple-800 text-sm">
                        <i class="fas fa-bug mr-1"></i>Debug Info (Test Only)
                    </a>
                </div>
            <?php else: ?>

            <form method="POST" action="" class="space-y-6">
                <!-- Email Input -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-envelope mr-2 text-purple-500"></i>Email Adresi
                    </label>
                    <input 
                        type="email" 
                        id="email"
                        name="email" 
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                        placeholder="kayitli@email.com" 
                        required
                        class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200"
                        autofocus
                    >
                    <p class="text-xs text-gray-500 mt-2">Kayıt olurken kullandığınız email adresini girin</p>
                </div>

                <!-- Submit Button -->
                <button 
                    type="submit" 
                    class="w-full bg-gradient-to-r from-purple-600 to-purple-700 text-white py-3 px-4 rounded-lg font-medium hover:from-purple-700 hover:to-purple-800 focus:ring-4 focus:ring-purple-300 transition duration-200 transform hover:scale-105"
                >
                    <i class="fas fa-paper-plane mr-2"></i>Şifre Sıfırlama Linki Gönder
                </button>
            </form>

            <!-- Back to Login -->
            <div class="text-center mt-6 pt-6 border-t border-gray-200">
                <p class="text-gray-600">
                    Şifrenizi hatırladınız mı? 
                    <a href="giris.php" class="text-purple-600 hover:text-purple-800 font-medium">
                        <i class="fas fa-arrow-left mr-1"></i>Giriş yapın
                    </a>
                </p>
                <a href="debug_password_reset.php" class="text-gray-500 hover:text-gray-700 text-xs">
                    <i class="fas fa-tools mr-1"></i>Database Setup
                </a>
            </div>

            <?php endif; ?>
        </div>

        <!-- Footer -->
        <div class="text-center mt-6">
            <a href="index.php" class="text-purple-100 hover:text-white">
                <i class="fas fa-home mr-2"></i>Ana Sayfaya Dön
            </a>
        </div>
    </div>
</body>
</html>