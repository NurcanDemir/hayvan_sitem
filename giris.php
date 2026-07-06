<?php
// filepath: c:\xampp\htdocs\hayvan_sitem\giris.php
session_start();
include("includes/db.php");

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$login_input = $_POST['login_input'] ?? '';
$password = $_POST['password'] ?? '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($login_input) || empty($password)) {
        $error = "Lütfen tüm alanları doldurun.";
    } else {
        // Check if input is email or username
        $is_email = filter_var($login_input, FILTER_VALIDATE_EMAIL);
        
        if ($is_email) {
            $stmt = $conn->prepare("SELECT * FROM kullanicilar WHERE email = ?");
        } else {
            $stmt = $conn->prepare("SELECT * FROM kullanicilar WHERE kullanici_adi = ?");
        }
        
        $stmt->bind_param("s", $login_input);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Check if email is verified (only if email column exists)
            if (isset($user['email_verified']) && !$user['email_verified']) {
                $error = "Lütfen önce email adresinizi doğrulayın. Email kutunuzu kontrol edin.";
            } elseif (password_verify($password, $user['sifre'])) {
                // Successful login
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['kullanici_id'] = $user['id']; // Backward compatibility
                $_SESSION['kullanici_adi'] = $user['kullanici_adi'];
                $_SESSION['email'] = $user['email'] ?? '';
                $_SESSION['rol'] = $user['rol'] ?? 'kullanici';
                
                // Redirect based on role
                if ($_SESSION['rol'] === 'admin') {
                    header("Location: admin/admin_panel.php");
                } else {
                    header("Location: index.php");
                }
                exit();
            } else {
                $error = "Geçersiz şifre.";
            }
        } else {
            $error = "Bu bilgilerle kayıtlı kullanıcı bulunamadı.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap - Satın Alma Yuva Ol</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        :root {
            --bg-light: #F8F9FA;
            --primary-pink: #FFB3C6;
            --action-mint: #A8DADC;
            --text-dark: #2B2D42;
            --brand-highlight: #3A868F;
        }
        .gradient-bg {
            font-family: 'Poppins', sans-serif;
            background:
                radial-gradient(circle at 14% 16%, rgba(255, 179, 198, 0.28), transparent 46%),
                radial-gradient(circle at 86% 10%, rgba(168, 218, 220, 0.28), transparent 42%),
                var(--bg-light);
        }
        .form-input:focus { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
    </style>
</head>
<body class="min-h-screen gradient-bg flex items-center justify-center p-4">
    <div class="max-w-md w-full">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-white rounded-full mb-4">
                <i class="fas fa-paw text-2xl text-purple-600"></i>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">
                <span>Satın Alma</span>
                <span style="color: var(--brand-highlight);">Yuva Ol</span>
            </h1>
            <p class="text-purple-100">Hoş geldiniz!</p>
        </div>

        <!-- Login Form -->
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <div class="text-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Giriş Yap</h2>
                <p class="text-gray-600">Hesabınıza erişin</p>
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
                <!-- Login Input (Email or Username) -->
                <div>
                    <label for="login_input" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user mr-2 text-purple-500"></i>Email veya Kullanıcı Adı
                    </label>
                    <input 
                        type="text" 
                        id="login_input"
                        name="login_input" 
                        value="<?= htmlspecialchars($login_input) ?>"
                        placeholder="email@ornek.com veya kullaniciadi" 
                        required
                        class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200"
                    >
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
                            class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200"
                        >
                        <button type="button" class="absolute right-3 top-3 text-gray-400 hover:text-gray-600" onclick="togglePassword()">
                            <i class="fas fa-eye" id="password-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Forgot Password Link -->
                <div class="text-right">
                    <a href="forgot_password.php" class="text-sm text-purple-600 hover:text-purple-800 hover:underline">
                        <i class="fas fa-question-circle mr-1"></i>Şifremi unuttum
                    </a>
                </div>

                <!-- Submit Button -->
                <button 
                    type="submit" 
                    class="w-full bg-gradient-to-r from-purple-600 to-purple-700 text-white py-3 px-4 rounded-lg font-medium hover:from-purple-700 hover:to-purple-800 focus:ring-4 focus:ring-purple-300 transition duration-200 transform hover:scale-105"
                >
                    <i class="fas fa-sign-in-alt mr-2"></i>Giriş Yap
                </button>
            </form>

            <!-- Register Link -->
            <div class="text-center mt-6 pt-6 border-t border-gray-200">
                <p class="text-gray-600">
                    Hesabınız yok mu? 
                    <a href="kayit.php" class="text-purple-600 hover:text-purple-800 font-medium">
                        Kayıt olun
                    </a>
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-6">
            <a href="index.php" class="text-purple-100 hover:text-white">
                <i class="fas fa-home mr-2"></i>Ana Sayfaya Dön
            </a>
        </div>
    </div>

    <script>
        function togglePassword() {
            const field = document.getElementById('password');
            const eye = document.getElementById('password-eye');
            
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
    </script>
</body>
</html>