<?php
// filepath: c:\xampp\htdocs\hayvan_sitem\reset_password.php
session_start();
include("includes/db.php");

// Set timezone
date_default_timezone_set('Europe/Istanbul');

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';
$valid_token = false;
$user = null;
$debug_info = '';

// Check if token is provided and valid
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    // Check if password reset columns exist
    $check_columns = $conn->query("SHOW COLUMNS FROM kullanicilar LIKE 'password_reset_token'");
    if ($check_columns->num_rows == 0) {
        $error = "Sistem henüz şifre sıfırlama için yapılandırılmadı.";
    } else {
        // Find user with this token and check if not expired
        $stmt = $conn->prepare("SELECT id, kullanici_adi, email, password_reset_expires, NOW() as current_ts FROM kullanicilar WHERE password_reset_token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Debug info
            $debug_info = "Token found for user: " . $user['kullanici_adi'] . "<br>";
            $debug_info .= "Expires: " . $user['password_reset_expires'] . "<br>";
            $debug_info .= "Current: " . $user['current_ts'] . "<br>";
            
            // Check if expired
            if ($user['password_reset_expires'] && strtotime($user['password_reset_expires']) > strtotime($user['current_ts'])) {
                $valid_token = true;
                $debug_info .= "Status: VALID ✅";
            } else {
                $debug_info .= "Status: EXPIRED ❌";
                $error = "Sıfırlama linki süresi dolmuş. Lütfen tekrar şifre sıfırlama talebinde bulunun.";
            }
        } else {
            $error = "Geçersiz sıfırlama linki.";
            $debug_info = "Token not found in database";
        }
    }
} else {
    $error = "Sıfırlama token'ı bulunamadı.";
}

// Handle password reset form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid_token) {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($new_password) || empty($confirm_password)) {
        $error = "Lütfen tüm alanları doldurun.";
    } elseif (strlen($new_password) < 6) {
        $error = "Şifre en az 6 karakter olmalı.";
    } elseif ($new_password !== $confirm_password) {
        $error = "Şifreler eşleşmiyor.";
    } else {
        // Update password and clear reset token
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_stmt = $conn->prepare("UPDATE kullanicilar SET sifre = ?, password_reset_token = NULL, password_reset_expires = NULL WHERE id = ?");
        $update_stmt->bind_param("si", $hashed_password, $user['id']);
        
        if ($update_stmt->execute()) {
            $success = "Şifreniz başarıyla güncellendi! Artık yeni şifrenizle giriş yapabilirsiniz.";
            $valid_token = false; // Prevent further form submissions
        } else {
            $error = "Şifre güncellenirken bir hata oluştu: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Şifre Sıfırla - Hayvan Dostları</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .form-input:focus { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .password-strength { height: 4px; border-radius: 2px; margin-top: 5px; }
        .strength-weak { background: #ef4444; width: 25%; }
        .strength-medium { background: #f59e0b; width: 50%; }
        .strength-good { background: #10b981; width: 75%; }
        .strength-strong { background: #059669; width: 100%; }
    </style>
</head>
<body class="min-h-screen gradient-bg flex items-center justify-center p-4">
    <div class="max-w-md w-full">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-white rounded-full mb-4">
                <?php if ($success): ?>
                    <i class="fas fa-check-circle text-2xl text-green-600"></i>
                <?php elseif (!$valid_token): ?>
                    <i class="fas fa-exclamation-triangle text-2xl text-red-600"></i>
                <?php else: ?>
                    <i class="fas fa-lock text-2xl text-purple-600"></i>
                <?php endif; ?>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">Şifre Sıfırla</h1>
            <?php if ($valid_token && $user): ?>
                <p class="text-purple-100">Merhaba <?= htmlspecialchars($user['kullanici_adi']) ?>, yeni şifrenizi belirleyin</p>
            <?php else: ?>
                <p class="text-purple-100">Şifre sıfırlama</p>
            <?php endif; ?>
        </div>

        <!-- Debug Info (show only if there's an issue) -->
        <?php if ($debug_info && (!$valid_token || $error)): ?>
            <div class="bg-gray-100 border border-gray-300 rounded-lg p-4 mb-4 text-sm">
                <strong>🔍 Debug Info:</strong><br>
                <?= $debug_info ?>
            </div>
        <?php endif; ?>

        <!-- Reset Password Form -->
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <?php if ($success): ?>
                <!-- Success State -->
                <div class="text-center">
                    <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6">
                        <div class="flex">
                            <i class="fas fa-check-circle text-green-400 mr-3"></i>
                            <p class="text-green-700"><?= htmlspecialchars($success) ?></p>
                        </div>
                    </div>
                    
                    <div class="space-y-3">
                        <a href="giris.php" class="block bg-gradient-to-r from-green-600 to-green-700 text-white py-3 px-6 rounded-lg font-medium hover:from-green-700 hover:to-green-800 transition duration-200">
                            <i class="fas fa-sign-in-alt mr-2"></i>Giriş Yap
                        </a>
                        <a href="index.php" class="block text-purple-600 hover:text-purple-800 font-medium">
                            <i class="fas fa-home mr-2"></i>Ana Sayfaya Dön
                        </a>
                    </div>
                </div>
                
            <?php elseif (!$valid_token): ?>
                <!-- Invalid Token State -->
                <div class="text-center">
                    <h2 class="text-2xl font-bold text-gray-800 mb-4">Geçersiz Link</h2>
                    
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
                    
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                        <h3 class="font-semibold text-blue-800 mb-2">💡 Ne yapabilirsiniz:</h3>
                        <ul class="text-sm text-blue-700 space-y-1 text-left">
                            <li>• Şifre sıfırlama linklerinin geçerlilik süresi 1 saattir</li>
                            <li>• Yeni bir şifre sıfırlama talebinde bulunun</li>
                            <li>• Email adresinizi doğru yazdığınızdan emin olun</li>
                            <li>• Linki email gelir gelmez kullanın</li>
                        </ul>
                    </div>
                    
                    <div class="space-y-3">
                        <a href="forgot_password.php" class="block bg-gradient-to-r from-purple-600 to-purple-700 text-white py-3 px-6 rounded-lg font-medium hover:from-purple-700 hover:to-purple-800 transition duration-200">
                            <i class="fas fa-redo mr-2"></i>Tekrar Şifre Sıfırlama Talebi
                        </a>
                        <a href="giris.php" class="block text-purple-600 hover:text-purple-800 font-medium">
                            <i class="fas fa-arrow-left mr-2"></i>Giriş Sayfasına Dön
                        </a>
                    </div>
                </div>
                
            <?php else: ?>
                <!-- Valid Token - Show Form -->
                <div class="text-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Yeni Şifre Belirle</h2>
                    <p class="text-gray-600">Güçlü bir şifre seçin</p>
                </div>

                <?php if ($error): ?>
                    <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                        <div class="flex">
                            <i class="fas fa-exclamation-triangle text-red-400 mr-3"></i>
                            <p class="text-red-700"><?= htmlspecialchars($error) ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="space-y-6">
                    <!-- New Password -->
                    <div>
                        <label for="new_password" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-lock mr-2 text-purple-500"></i>Yeni Şifre
                        </label>
                        <div class="relative">
                            <input 
                                type="password" 
                                id="new_password"
                                name="new_password" 
                                placeholder="••••••••" 
                                required
                                minlength="6"
                                class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200"
                                onkeyup="checkPasswordStrength()"
                            >
                            <button type="button" class="absolute right-3 top-3 text-gray-400 hover:text-gray-600" onclick="togglePassword('new_password')">
                                <i class="fas fa-eye" id="new_password-eye"></i>
                            </button>
                        </div>
                        <div class="password-strength" id="passwordStrength"></div>
                        <p class="text-xs text-gray-500 mt-1">En az 6 karakter</p>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-lock mr-2 text-purple-500"></i>Şifre Tekrar
                        </label>
                        <div class="relative">
                            <input 
                                type="password" 
                                id="confirm_password"
                                name="confirm_password" 
                                placeholder="••••••••" 
                                required
                                class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200"
                                onkeyup="checkPasswordMatch()"
                            >
                            <button type="button" class="absolute right-3 top-3 text-gray-400 hover:text-gray-600" onclick="togglePassword('confirm_password')">
                                <i class="fas fa-eye" id="confirm_password-eye"></i>
                            </button>
                        </div>
                        <p class="text-xs" id="passwordMatch"></p>
                    </div>

                    <!-- Submit Button -->
                    <button 
                        type="submit" 
                        class="w-full bg-gradient-to-r from-purple-600 to-purple-700 text-white py-3 px-4 rounded-lg font-medium hover:from-purple-700 hover:to-purple-800 focus:ring-4 focus:ring-purple-300 transition duration-200 transform hover:scale-105"
                    >
                        <i class="fas fa-save mr-2"></i>Şifreyi Güncelle
                    </button>
                </form>

                <!-- Back to Login -->
                <div class="text-center mt-6 pt-6 border-t border-gray-200">
                    <a href="giris.php" class="text-purple-600 hover:text-purple-800 font-medium">
                        <i class="fas fa-arrow-left mr-1"></i>Giriş sayfasına dön
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

    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const eye = document.getElementById(fieldId + '-eye');
            
            if (field.type === 'password') {
                field.type = 'text';
                eye.classList.remove('fa-eye');
                eye.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                eye.classList.remove('fa-eye-slash');
                eye.classList.add('fa-eye');
            }
        }

        function checkPasswordStrength() {
            const password = document.getElementById('new_password').value;
            const strengthBar = document.getElementById('passwordStrength');
            
            let strength = 0;
            
            if (password.length >= 6) strength++;
            if (password.match(/[a-z]/)) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;
            
            strengthBar.className = 'password-strength';
            
            switch(strength) {
                case 0:
                case 1:
                    strengthBar.classList.add('strength-weak');
                    break;
                case 2:
                    strengthBar.classList.add('strength-medium');
                    break;
                case 3:
                    strengthBar.classList.add('strength-good');
                    break;
                case 4:
                case 5:
                    strengthBar.classList.add('strength-strong');
                    break;
            }
        }

        function checkPasswordMatch() {
            const password = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const matchText = document.getElementById('passwordMatch');
            
            if (confirmPassword.length === 0) {
                matchText.textContent = '';
                return;
            }
            
            if (password === confirmPassword) {
                matchText.textContent = '✓ Şifreler eşleşiyor';
                matchText.className = 'text-xs text-green-600';
            } else {
                matchText.textContent = '✗ Şifreler eşleşmiyor';
                matchText.className = 'text-xs text-red-600';
            }
        }
    </script>
</body>
</html>