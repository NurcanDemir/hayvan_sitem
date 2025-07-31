<?php
session_start(); // Oturum başlatma
include("includes/db.php");
// include("includes/header.php"); // Header'ı HTML içinde çağıracağız

$mesaj = ""; // Kullanıcıya gösterilecek mesaj
$mesaj_tur = ""; // 'success' veya 'danger'

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $kullanici_adi = trim($_POST["kullanici_adi"] ?? '');
    $sifre = $_POST["sifre"] ?? '';
    $tip = 'normal'; // Varsayılan kullanıcı tipi

    if (empty($kullanici_adi) || empty($sifre)) {
        $mesaj = "Kullanıcı adı ve şifre boş bırakılamaz.";
        $mesaj_tur = "danger";
    } else {
        // Kullanıcı adının benzersizliğini kontrol et
        $check_stmt = $conn->prepare("SELECT id FROM kullanicilar WHERE kullanici_adi = ?");
        $check_stmt->bind_param("s", $kullanici_adi);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $mesaj = "Bu kullanıcı adı zaten alınmış. Lütfen başka bir kullanıcı adı seçin.";
            $mesaj_tur = "warning";
        } else {
            $hashed_sifre = password_hash($sifre, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO kullanicilar (kullanici_adi, sifre, kullanici_tipi) VALUES (?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("sss", $kullanici_adi, $hashed_sifre, $tip);

                if ($stmt->execute()) {
                    $mesaj = "Kayıt başarılı! Şimdi giriş yapabilirsiniz.";
                    $mesaj_tur = "success";
                } else {
                    $mesaj = "Kayıt başarısız: " . $stmt->error;
                    $mesaj_tur = "danger";
                }
                $stmt->close();
            } else {
                $mesaj = "Veritabanı sorgu hazırlama hatası: " . $conn->error;
                $mesaj_tur = "danger";
            }
        }
        $check_stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıt Ol</title>
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
<body class="bg-gray-100 font-sans leading-normal tracking-normal">

<?php include("includes/header.php"); ?>

<div class="container mx-auto px-4 py-8 mt-16 md:mt-24">
    <div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow-xl">
        <h1 class="text-3xl font-extrabold text-center text-koyu-pembe mb-6">Kayıt Ol</h1>

        <?php if (!empty($mesaj)): ?>
            <div class="p-4 mb-6 rounded-lg text-white font-medium
                <?php
                    if ($mesaj_tur === "success") echo "bg-acik-yesil text-koyu-yesil";
                    elseif ($mesaj_tur === "danger") echo "bg-red-500";
                    elseif ($mesaj_tur === "warning") echo "bg-yellow-500";
                ?>">
                <?= $mesaj ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="kayit.php" class="space-y-6">
            <div>
                <label for="kullanici_adi" class="block text-gray-700 text-sm font-semibold mb-2">Kullanıcı Adı:</label>
                <input type="text" name="kullanici_adi" id="kullanici_adi" class="form-input-tailwind" required placeholder="Kullanıcı Adınız">
            </div>
            <div>
                <label for="sifre" class="block text-gray-700 text-sm font-semibold mb-2">Şifre:</label>
                <input type="password" name="sifre" id="sifre" class="form-input-tailwind" required placeholder="Şifreniz">
            </div>
            <button type="submit" class="w-full bg-koyu-pembe hover:bg-pink-700 text-white font-bold py-3 px-4 rounded-md transition duration-300 flex items-center justify-center">
                <i class="fas fa-user-plus mr-2"></i> Kayıt Ol
            </button>
        </form>

        <p class="text-center text-sm text-gray-600 mt-6">
            Zaten hesabınız var mı? <a href="giris.php" class="text-koyu-pembe hover:underline font-semibold">Giriş Yap</a>
        </p>
    </div>
</div>

<?php include("includes/footer.php"); ?>

</body>
</html>