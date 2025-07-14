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
        $stmt = $conn->prepare("SELECT id, sifre, kullanici_tipi, kullanici_adi, eposta, telefon, adres FROM kullanicilar WHERE kullanici_adi = ?");
        if ($stmt) {
            $stmt->bind_param("s", $kullanici_adi);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {
                if (password_verify($sifre, $row['sifre'])) {
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
    <title>Giriş Yap</title>
    <link href="./dist/output.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        .form-input-tailwind {
            @apply block w-full px-3 py-2 text-base font-normal text-gray-700 bg-white bg-clip-padding bg-no-repeat border border-solid border-gray-300 rounded-md transition ease-in-out m-0
                     focus:text-gray-700 focus:bg-white focus:border-koyu-pembe focus:ring-1 focus:ring-koyu-pembe focus:outline-none;
        }
        /* Yeni eklenen CSS kuralları */
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
        }
    </style>
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal min-h-screen flex flex-col">

<?php include("includes/header.php"); ?>

<div class="container mx-auto px-4 py-8 mt-16 md:mt-24 flex-grow">
    <div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow-xl">
        <h1 class="text-3xl font-extrabold text-center text-koyu-pembe mb-6">Giriş Yap</h1>

        <?php if (!empty($error)): ?>
            <div class="p-4 mb-6 rounded-lg text-white font-medium bg-red-500">
                <?= htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($kayit_basari_mesaj)): ?>
            <div class="p-4 mb-6 rounded-lg text-white font-medium bg-acik-yesil text-koyu-yesil">
                <?= htmlspecialchars($kayit_basari_mesaj); ?>
            </div>
        <?php endif; ?>

        <form action="giris.php" method="POST" class="space-y-6">
            <div>
                <label for="kullanici_adi" class="block text-gray-700 text-sm font-semibold mb-2">Kullanıcı Adı</label>
                <input type="text" class="form-input-tailwind" name="kullanici_adi" id="kullanici_adi" required placeholder="Kullanıcı Adınız" value="<?= $remembered_kullanici_adi; ?>">
            </div>
            <div>
                <label for="sifre" class="block text-gray-700 text-sm font-semibold mb-2">Şifre</label>
                <input type="password" class="form-input-tailwind" name="sifre" id="sifre" required placeholder="Şifreniz">
            </div>
            <div>
                <input type="checkbox" name="beni_hatirla" id="beni_hatirla" class="mr-2" <?= (isset($_COOKIE['remember_kullanici_adi']) ? 'checked' : ''); ?>>
                <label for="beni_hatirla" class="text-gray-700 text-sm font-semibold">Beni Hatırla</label>
            </div>
            <button type="submit" class="w-full bg-koyu-pembe hover:bg-pink-700 text-white font-bold py-3 px-4 rounded-md transition duration-300 flex items-center justify-center">
                <i class="fas fa-sign-in-alt mr-2"></i> Giriş Yap
            </button>
        </form>

        <p class="text-center text-sm text-gray-600 mt-6">
            Hesabınız yok mu? <a href="kayit.php" class="text-koyu-pembe hover:underline font-semibold">Kayıt Ol</a>
        </p>
    </div>
</div>

<?php include("includes/footer.php"); ?>

</body>
</html>