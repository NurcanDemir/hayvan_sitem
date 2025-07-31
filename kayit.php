<?php
// filepath: c:\xampp\htdocs\hayvan_sitem\kayit.php
session_start();
include("includes/db.php");
require_once("mail/includes/MailSender.php");

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($email) || empty($username) || empty($password) || empty($confirm_password)) {
        $error = "Lütfen tüm alanları doldurun.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Geçerli bir email adresi girin.";
    } elseif (strlen($username) < 3) {
        $error = "Kullanıcı adı en az 3 karakter olmalı.";
    } elseif (strlen($password) < 6) {
        $error = "Şifre en az 6 karakter olmalı.";
    } elseif ($password !== $confirm_password) {
        $error = "Şifreler eşleşmiyor.";
    } else {
        // Check if email or username already exists
        $check_stmt = $conn->prepare("SELECT id, email_verified FROM kullanicilar WHERE email = ? OR kullanici_adi = ?");
        $check_stmt->bind_param("ss", $email, $username);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $existing_user = $check_result->fetch_assoc();
            if (isset($existing_user['email_verified']) && $existing_user['email_verified']) {
                $error = "Bu email adresi veya kullanıcı adı zaten kullanılıyor.";
            } else {
                $error = "Bu email adresi ile kayıt mevcut ancak henüz doğrulanmamış. Email kutunuzu kontrol edin.";
            }
        } else {
            // Create new user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $verification_token = bin2hex(random_bytes(32));
            
            $insert_stmt = $conn->prepare("INSERT INTO kullanicilar (email, kullanici_adi, sifre, email_verification_token, email_verified, created_at) VALUES (?, ?, ?, ?, 0, NOW())");
            $insert_stmt->bind_param("ssss", $email, $username, $hashed_password, $verification_token);
            
            if ($insert_stmt->execute()) {
                // Try to send verification email
                try {
                    $mailSender = new MailSender();
                    $email_result = $mailSender->sendVerificationEmail($email, $username, $verification_token);
                    
                    if ($email_result['success']) {
                        $success = "Kayıt başarılı! Email adresinize gönderilen doğrulama linkine tıklayarak hesabınızı aktifleştirin.";
                    } else {
                        $success = "Kayıt başarılı! Ancak email gönderilirken bir sorun oluştu. Yönetici ile iletişime geçin.";
                    }
                } catch (Exception $e) {
                    $success = "Kayıt başarılı! Email sistemi henüz yapılandırılmadı. Hesabınız otomatik olarak aktifleştirildi.";
                    
                    // Auto-verify if email system is not configured
                    $auto_verify = $conn->prepare("UPDATE kullanicilar SET email_verified = 1, email_verified_at = NOW() WHERE email = ?");
                    $auto_verify->bind_param("s", $email);
                    $auto_verify->execute();
                }
            } else {
                $error = "Kayıt sırasında bir hata oluştu. Lütfen tekrar deneyin.";
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
    <title>Kayıt Ol - Hayvan Dostları</title>
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
                <i class="fas fa-paw text-2xl text-purple-600"></i>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">Hayvan Dostları</h1>
            <p class="text-purple-100">Hayvanlar için bir yuva bulun</p>
        </div>

        <!-- Registration Form -->
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <div class="text-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Hesap Oluştur</h2>
                <p class="text-gray-600">Hayvan dostlarına katılın</p>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                    <div class="flex">
                        <i class="fas fa-exclamation-triangle text-red-400 mr-3"></i>
                        <p class="text-red-700"><?= htmlspecialchars($error) ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6">
                    <div class="flex">
                        <i class="fas fa-check-circle text-green-400 mr-3"></i>
                        <p class="text-green-700"><?= htmlspecialchars($success) ?></p>
                    </div>
                </div>
                <div class="text-center">
                    <a href="giris.php" class="text-purple-600 hover:text-purple-800 font-medium">
                        <i class="fas fa-arrow-left mr-2"></i>Giriş sayfasına dön
                    </a>
                </div>
            <?php else: ?>

            <form method="POST" action="" class="space-y-6" id="registrationForm">
                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-envelope mr-2 text-purple-500"></i>Email Adresi
                    </label>
                    <input 
                        type="email" 
                        id="email"
                        name="email" 
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                        placeholder="ornek@email.com" 
                        required
                        class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200"
                    >
                </div>

                <!-- Username -->
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user mr-2 text-purple-500"></i>Kullanıcı Adı
                    </label>
                    <input 
                        type="text" 
                        id="username"
                        name="username" 
                        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                        placeholder="kullaniciadi123" 
                        required
                        minlength="3"
                        class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200"
                    >
                    <p class="text-xs text-gray-500 mt-1">En az 3 karakter</p>
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-lock mr-2 text-purple-500"></i>Şifre
                    </label>
                    <div class="relative">
                        <input 
                            type="password" 
                            id="password"
                            name="password" 
                            placeholder="••••••••" 
                            required
                            minlength="6"
                            class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200"
                            onkeyup="checkPasswordStrength()"
                        >
                        <button type="button" class="absolute right-3 top-3 text-gray-400 hover:text-gray-600" onclick="togglePassword('password')">
                            <i class="fas fa-eye" id="password-eye"></i>
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
                    <i class="fas fa-user-plus mr-2"></i>Hesap Oluştur
                </button>
            </form>

            <!-- Login Link -->
            <div class="text-center mt-6 pt-6 border-t border-gray-200">
                <p class="text-gray-600">
                    Zaten hesabınız var mı? 
                    <a href="giris.php" class="text-purple-600 hover:text-purple-800 font-medium">
                        Giriş yapın
                    </a>
                </p>
            </div>

            <?php endif; ?>
        </div>

        <!-- Footer -->
        <div class="text-center mt-6">
            <p class="text-purple-100 text-sm">
                <i class="fas fa-heart mr-1"></i>
                Hayvanlar için yapıldı
            </p>
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
            const password = document.getElementById('password').value;
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
            const password = document.getElementById('password').value;
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