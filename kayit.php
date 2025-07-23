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
<body class="bg-gray-100 font-sans leading-normal tracking-normal min-h-screen flex flex-col">

<?php include("includes/header.php"); ?>

<div class="bg-gray-50 min-h-screen pt-8">
    <div class="container mx-auto px-4">
        <!-- Başlık Bölümü -->
        <div class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto py-12 px-6">
                <h1 class="text-5xl font-extrabold text-amber-700 mb-4">
                    <i class="fas fa-user-plus mr-4"></i>Kayıt Ol
                </h1>
                <p class="text-2xl text-gray-600">Yuvanın Anahtarı ailesine katılın ve hayvan sevgisi dünyasına adım atın!</p>
            </div>
        </div>

        <!-- Ana İçerik Bölümü -->
        <div class="max-w-7xl mx-auto py-12 px-6">
            <?php if (!empty($mesaj)): ?>
                <div class="mb-8 p-6 rounded-lg text-white font-medium text-lg
                    <?php
                        if ($mesaj_tur === "success") echo "bg-green-500";
                        elseif ($mesaj_tur === "danger") echo "bg-red-500";
                        elseif ($mesaj_tur === "warning") echo "bg-yellow-500";
                    ?>">
                    <?= $mesaj ?>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                <!-- Kayıt Formu - Sol taraf -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl shadow-lg p-8">
                        <h2 class="text-3xl font-bold text-gray-800 mb-8 border-b pb-4">
                            <i class="fas fa-edit mr-3 text-amber-600"></i>Yeni Hesap Oluştur
                        </h2>
                        
                        <form method="POST" action="kayit.php" class="space-y-8">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="md:col-span-2">
                                    <label for="kullanici_adi" class="block text-gray-700 text-lg font-semibold mb-3">
                                        <i class="fas fa-user mr-2 text-amber-600"></i>Kullanıcı Adı
                                    </label>
                                    <input type="text" name="kullanici_adi" id="kullanici_adi" 
                                           class="w-full px-6 py-4 text-lg border border-gray-300 rounded-lg focus:outline-none focus:ring-3 focus:ring-amber-400 focus:border-transparent transition duration-300" 
                                           required placeholder="Benzersiz kullanıcı adınızı seçin">
                                    <p class="text-sm text-gray-500 mt-2">Bu ad ile tanınacaksınız ve giriş yapacaksınız.</p>
                                </div>
                                
                                <div class="md:col-span-2">
                                    <label for="sifre" class="block text-gray-700 text-lg font-semibold mb-3">
                                        <i class="fas fa-lock mr-2 text-amber-600"></i>Şifre
                                    </label>
                                    <input type="password" name="sifre" id="sifre" 
                                           class="w-full px-6 py-4 text-lg border border-gray-300 rounded-lg focus:outline-none focus:ring-3 focus:ring-amber-400 focus:border-transparent transition duration-300" 
                                           required placeholder="Güvenli bir şifre oluşturun">
                                    <p class="text-sm text-gray-500 mt-2">En az 6 karakter kullanmanızı öneririz.</p>
                                </div>
                            </div>
                            
                            <div class="pt-6 border-t">
                                <button type="submit" class="w-full bg-amber-700 hover:bg-amber-800 text-white font-bold py-4 px-8 rounded-lg transition duration-300 shadow-lg text-xl">
                                    <i class="fas fa-user-plus mr-3"></i>Hesabımı Oluştur
                                </button>
                            </div>
                        </form>

                        <div class="mt-8 pt-6 border-t text-center">
                            <p class="text-lg text-gray-600">
                                Zaten hesabınız var mı? 
                                <a href="giris.php" class="text-amber-600 hover:text-amber-700 font-semibold hover:underline text-xl">
                                    <i class="fas fa-sign-in-alt mr-2"></i>Hemen Giriş Yapın
                                </a>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Bilgi Bölümü - Sağ taraf -->
                <div class="space-y-8">
                    <!-- Üyelik Avantajları -->
                    <div class="bg-white rounded-xl shadow-lg p-8">
                        <h3 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-3">
                            <i class="fas fa-star mr-3 text-amber-600"></i>Üyelik Avantajları
                        </h3>
                        <div class="space-y-6">
                            <div class="flex items-start">
                                <div class="bg-amber-100 p-3 rounded-full mr-4">
                                    <i class="fas fa-heart text-amber-600 text-xl"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-800 text-lg">İlan Yayınlayın</h4>
                                    <p class="text-gray-600">Sahiplendirmek istediğiniz hayvanlar için ücretsiz ilan verin</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start">
                                <div class="bg-emerald-100 p-3 rounded-full mr-4">
                                    <i class="fas fa-bookmark text-emerald-600 text-xl"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-800 text-lg">Favoriler</h4>
                                    <p class="text-gray-600">Beğendiğiniz ilanları kaydedin ve takip edin</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start">
                                <div class="bg-stone-100 p-3 rounded-full mr-4">
                                    <i class="fas fa-handshake text-stone-600 text-xl"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-800 text-lg">Sahiplenme Talepleri</h4>
                                    <p class="text-gray-600">Hayvan sahiplenmek için güvenle talepte bulunun</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start">
                                <div class="bg-amber-100 p-3 rounded-full mr-4">
                                    <i class="fas fa-shield-alt text-amber-600 text-xl"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-800 text-lg">Güvenli Platform</h4>
                                    <p class="text-gray-600">Doğrulanmış üyeler, güvenilir sahiplendirme</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- İstatistikler -->
                    <div class="bg-gradient-to-br from-amber-50 to-stone-50 rounded-xl shadow-lg p-8">
                        <h3 class="text-2xl font-bold text-gray-800 mb-6">
                            <i class="fas fa-chart-line mr-3 text-emerald-600"></i>Başarı Hikayelerimiz
                        </h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-center p-4 bg-white rounded-lg">
                                <div class="text-3xl font-bold text-amber-600">500+</div>
                                <div class="text-sm text-gray-600">Mutlu Sahiplendirme</div>
                            </div>
                            <div class="text-center p-4 bg-white rounded-lg">
                                <div class="text-3xl font-bold text-emerald-600">1200+</div>
                                <div class="text-sm text-gray-600">Aktif Üye</div>
                            </div>
                            <div class="text-center p-4 bg-white rounded-lg">
                                <div class="text-3xl font-bold text-stone-600">50+</div>
                                <div class="text-sm text-gray-600">Günlük İlan</div>
                            </div>
                            <div class="text-center p-4 bg-white rounded-lg">
                                <div class="text-3xl font-bold text-amber-700">99%</div>
                                <div class="text-sm text-gray-600">Memnuniyet</div>
                            </div>
                        </div>
                    </div>

                    <!-- Motivasyon Mesajı -->
                    <div class="bg-white rounded-xl shadow-lg p-8 border-l-4 border-amber-600">
                        <blockquote class="text-lg text-gray-700 italic">
                            <i class="fas fa-quote-left text-2xl text-amber-600 mr-3"></i>
                            "Her kayıt, yeni bir dostluğun başlangıcıdır. Hayvan sevgisi burada birleşiyor!"
                        </blockquote>
                        <div class="mt-4 text-right">
                            <span class="text-sm text-gray-500">- Yuvanın Anahtarı Ekibi</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alt Bilgi -->
            <div class="mt-16 text-center py-8 border-t border-gray-200">
                <p class="text-gray-500 text-lg">
                    Kayıt olarak 
                    <a href="#" class="text-amber-600 hover:underline font-semibold">kullanım şartlarını</a> ve 
                    <a href="#" class="text-amber-600 hover:underline font-semibold">gizlilik politikasını</a> kabul etmiş olursunuz.
                </p>
                <p class="text-gray-400 text-sm mt-2">
                    Sorularınız için: <strong>info@yuvaninanahtari.com</strong>
                </p>
            </div>
        </div>
    </div>
</div>

<?php include("includes/footer.php"); ?>

</body>
</html>