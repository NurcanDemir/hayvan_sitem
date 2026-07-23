<?php
session_start();
include("includes/db.php");

// Eğer kullanıcı zaten giriş yapmışsa anasayfaya gönder
if (isset($_SESSION['kullanici_id']) && !empty($_SESSION['kullanici_id'])) {
    header("Location: index.php");
    exit;
}

$error = ""; // Hata mesajı için boş değişken
$mesaj_tur = "danger"; // Varsayılan hata mesajı türü

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $kullanici_adi = trim($_POST['kullanici_adi'] ?? '');
    $sifre = $_POST['sifre'] ?? '';
    // "Beni Hatırla" seçeneğinin işaretlenip işaretlenmediğini kontrol et
    $beni_hatirla = isset($_POST['beni_hatirla']);

    if (empty($kullanici_adi) || empty($sifre)) {
        $error = "Lütfen kullanıcı adı ve şifre girin.";
    } else {
        // SELECT sorgusuna eposta, telefon ve adres sütunlarını ekledik
        $stmt = $conn->prepare("SELECT id, sifre, kullanici_tipi, kullanici_adi, eposta, telefon, adres, email_verified FROM kullanicilar WHERE kullanici_adi = ?");
        if ($stmt) {
            $stmt->bind_param("s", $kullanici_adi);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {
                if (password_verify($sifre, $row['sifre'])) {
                    // Email doğrulama kontrolünü geçici olarak devre dışı bırakalım
                    // if (isset($row['email_verified']) && $row['email_verified'] == 0) {
                    //     $error = "E-posta adresinizi doğrulamanız gerekiyor. Lütfen gelen kutunuzu kontrol edin.";
                    //     $mesaj_tur = "warning";
                    // } else {
                        // Giriş başarılı
                        $_SESSION['kullanici_id'] = $row['id'];
                        $_SESSION['kullanici_tipi'] = $row['kullanici_tipi'];
                        $_SESSION['kullanici_adi'] = $row['kullanici_adi']; // Kullanıcı adını da saklayalım
                        $_SESSION['eposta'] = $row['eposta'];   // E-posta bilgisini session'a ekle
                        $_SESSION['telefon'] = $row['telefon']; // Telefon bilgisini session'a ekle
                        $_SESSION['adres'] = $row['adres'];     // Adres bilgisini session'a ekle

                        // "Beni Hatırla" seçeneği işaretliyse çerez oluştur
                        if ($beni_hatirla) {
                            // Çerezleri 30 gün boyunca geçerli olacak şekilde ayarla
                            setcookie('remember_kullanici_adi', $kullanici_adi, time() + (86400 * 30), "/"); // 86400 = 1 gün
                        } else {
                            // Eğer kullanıcı "Beni Hatırla" seçeneğini kaldırdıysa veya işaretlemediyse,
                            // önceden var olan çerezi sil
                            if (isset($_COOKIE['remember_kullanici_adi'])) {
                                setcookie('remember_kullanici_adi', '', time() - 3600, "/"); // Geçmiş bir zaman ayarlayarak çerezi siler
                            }
                        }

                        // Yönlendirme mantığı
                        if ($_SESSION['kullanici_tipi'] === 'admin') {
                            // Admin panelinin yolu projenizin yapısına göre değişebilir
                            header("Location: admin/index.php"); // Varsayılan admin paneli dizini
                        } else {
                            header("Location: index.php"); // Normal kullanıcı anasayfaya
                        }
                        exit;
                    // }
                } else {
                    $error = "Şifre yanlış.";
                }
            } else {
                $error = "Kullanıcı bulunamadı.";
            }
            $stmt->close();
        } else {
            $error = "Veritabanı sorgu hazırlama hatası: " . $conn->error;
        }
    }
}

// Kayıt sonrası başarı mesajını göstermek için session kontrolü
$kayit_basari_mesaj = '';
if (isset($_SESSION['kayit_basari_mesaj'])) {
    $kayit_basari_mesaj = $_SESSION['kayit_basari_mesaj'];
    unset($_SESSION['kayit_basari_mesaj']); // Mesajı gösterdikten sonra temizle
}

// Çerezde kullanıcı adı varsa formu önceden doldur
$remembered_kullanici_adi = '';
if (isset($_COOKIE['remember_kullanici_adi'])) {
    $remembered_kullanici_adi = htmlspecialchars($_COOKIE['remember_kullanici_adi']);
}

// Artık çıktı verebiliriz, header dahil edelim
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap - Yuva Ol</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <style>
        :root {
            --primary: #ba3689;
            --primary-light: #d95bb0;
        }

        body {
            background: linear-gradient(135deg, #f0b1df 0%, #fdf2f8 30%, #f9fafb 70%, #f0b1df 100%);
            min-height: 100vh;
        }

        .bg-primary { background-color: var(--primary); }
        .text-primary { color: var(--primary); }
        .hover\:bg-primary:hover { background-color: var(--primary); }
        .hover\:text-primary:hover { color: var(--primary); }
        .border-primary { border-color: var(--primary); }
        .focus\:ring-primary:focus { --tw-ring-color: var(--primary); }
        .focus\:border-primary:focus { border-color: var(--primary); }

        .form-input-tailwind {
            @apply block w-full px-4 py-3 text-base font-normal text-gray-700 bg-white bg-clip-padding bg-no-repeat border border-solid border-gray-300 rounded-lg transition ease-in-out m-0
                     focus:text-gray-700 focus:bg-white focus:border-primary focus:ring-2 focus:ring-pink-200 focus:outline-none;
        }

        .btn-gradient {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            transition: all 0.3s ease;
        }
        
        .btn-gradient:hover {
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
            transform: translateY(-1px);
            box-shadow: 0 10px 25px rgba(186, 54, 137, 0.3);
        }
    </style>
</head>
<body class="font-sans leading-normal tracking-normal min-h-screen flex flex-col">

    <!-- Include Header -->
    <?php include("includes/header.php"); ?>

    <div class="container mx-auto px-4 py-8 mt-16 flex-grow">
        <div class="max-w-md mx-auto bg-white p-8 rounded-xl shadow-lg">
            <div class="text-center mb-8">
                <div class="text-5xl mb-4">🔐</div>
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Giriş Yap</h1>
                <p class="text-gray-600">Hesabınıza giriş yapın</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="p-4 mb-6 rounded-lg bg-red-50 border border-red-200 text-red-800">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <?= htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($kayit_basari_mesaj)): ?>
                <div class="p-4 mb-6 rounded-lg bg-green-50 border border-green-200 text-green-800">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?= htmlspecialchars($kayit_basari_mesaj); ?>
                </div>
            <?php endif; ?>

            <form action="giris.php" method="POST" class="space-y-6">
                <div>
                    <label for="kullanici_adi" class="block text-gray-700 text-sm font-semibold mb-2">
                        <i class="fas fa-user mr-2 text-primary"></i>Kullanıcı Adı
                    </label>
                    <input type="text" 
                           class="form-input-tailwind" 
                           name="kullanici_adi" 
                           id="kullanici_adi" 
                           required 
                           placeholder="Kullanıcı adınızı girin"
                           value="<?= htmlspecialchars($remembered_kullanici_adi); ?>">
                </div>

                <div>
                    <label for="sifre" class="block text-gray-700 text-sm font-semibold mb-2">
                        <i class="fas fa-lock mr-2 text-primary"></i>Şifre
                    </label>
                    <input type="password" 
                           class="form-input-tailwind" 
                           name="sifre" 
                           id="sifre" 
                           required 
                           placeholder="Şifrenizi girin">
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input type="checkbox" 
                               name="beni_hatirla" 
                               id="beni_hatirla" 
                               class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2"
                               <?= (isset($_COOKIE['remember_kullanici_adi']) ? 'checked' : ''); ?>>
                        <label for="beni_hatirla" class="ml-2 text-sm text-gray-700 font-medium">
                            Beni Hatırla
                        </label>
                    </div>
                    <a href="forgot_password.php" class="text-sm text-primary hover:underline font-medium">
                        Şifremi Unuttum?
                    </a>
                </div>

                <button type="submit" class="w-full btn-gradient text-white font-bold py-3 px-6 rounded-lg transition duration-300 flex items-center justify-center">
                    <i class="fas fa-sign-in-alt mr-2"></i> Giriş Yap
                </button>
            </form>

            <div class="text-center mt-6 space-y-2">
                <p class="text-sm text-gray-600">
                    Hesabınız yok mu? 
                    <a href="kayit.php" class="text-primary hover:underline font-semibold">Kayıt Ol</a>
                </p>
                <p class="text-sm text-gray-500">
                    <a href="index.php" class="text-gray-500 hover:text-primary">
                        <i class="fas fa-arrow-left mr-1"></i>Ana Sayfaya Dön
                    </a>
                </p>
            </div>
        </div>
    </div>

    <!-- Include Footer -->
    <?php include("includes/footer.php"); ?>

</body>
</html>