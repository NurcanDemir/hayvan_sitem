<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include("includes/db.php");
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Sahiplendirme Rehberi - Yuvanın Anahtarı</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="./dist/output.css" rel="stylesheet">
</head>
<body class="bg-gray-50 font-sans min-h-screen flex flex-col">

<?php include("includes/header.php"); ?>

<div class="container mx-auto px-4 py-8 mt-8">
    <div class="max-w-4xl mx-auto">
        <!-- Başlık -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-extrabold text-pink-400 mb-4">
                <i class="fas fa-book-open mr-3"></i>Sahiplendirme Rehberi
            </h1>
            <p class="text-xl text-gray-600">Hayvan sahiplendirme sürecinde bilmeniz gereken her şey</p>
        </div>

        <!-- Sahiplendirme Adımları -->
        <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
            <h2 class="text-2xl font-bold text-green-400 mb-6">
                <i class="fas fa-list-ol mr-3"></i>Sahiplendirme Adımları
            </h2>
            <div class="space-y-4">
                <div class="flex items-start p-4 bg-blue-50 rounded-lg">
                    <div class="bg-blue-400 text-white rounded-full w-8 h-8 flex items-center justify-center mr-4 mt-1">1</div>
                    <div>
                        <h4 class="font-bold text-gray-800">Araştırma Yapın</h4>
                        <p class="text-gray-600">Hangi hayvanın yaşam tarzınıza uygun olduğunu araştırın.</p>
                    </div>
                </div>
                <div class="flex items-start p-4 bg-green-50 rounded-lg">
                    <div class="bg-green-400 text-white rounded-full w-8 h-8 flex items-center justify-center mr-4 mt-1">2</div>
                    <div>
                        <h4 class="font-bold text-gray-800">İlan Sahibi ile İletişim</h4>
                        <p class="text-gray-600">Beğendiğiniz ilan için sahip ile iletişime geçin.</p>
                    </div>
                </div>
                <div class="flex items-start p-4 bg-yellow-50 rounded-lg">
                    <div class="bg-yellow-400 text-white rounded-full w-8 h-8 flex items-center justify-center mr-4 mt-1">3</div>
                    <div>
                        <h4 class="font-bold text-gray-800">Tanışma</h4>
                        <p class="text-gray-600">Hayvanla tanışın ve uyum kontrolü yapın.</p>
                    </div>
                </div>
                <div class="flex items-start p-4 bg-purple-50 rounded-lg">
                    <div class="bg-purple-400 text-white rounded-full w-8 h-8 flex items-center justify-center mr-4 mt-1">4</div>
                    <div>
                        <h4 class="font-bold text-gray-800">Veteriner Kontrolü</h4>
                        <p class="text-gray-600">Gerekli sağlık kontrolleri ve aşıları yaptırın.</p>
                    </div>
                </div>
                <div class="flex items-start p-4 bg-pink-50 rounded-lg">
                    <div class="bg-pink-400 text-white rounded-full w-8 h-8 flex items-center justify-center mr-4 mt-1">5</div>
                    <div>
                        <h4 class="font-bold text-gray-800">Sahiplendirme Tamamlama</h4>
                        <p class="text-gray-600">Tüm süreçleri tamamlayın ve yeni arkadaşınızı eve getirin.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Neden Sahiplenmeli -->
        <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
            <h2 class="text-2xl font-bold text-pink-400 mb-6">
                <i class="fas fa-heart mr-3"></i>Neden Sahiplenmelisiniz?
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="p-4 border-l-4 border-green-400">
                    <h4 class="font-bold text-gray-800 mb-2">Bir Hayat Kurtarırsınız</h4>
                    <p class="text-gray-600">Barınaklarda ve sokaklarda zor durumda olan hayvanlara ikinci bir şans vererek onların yaşam kalitesini artırırsınız.</p>
                </div>
                <div class="p-4 border-l-4 border-blue-400">
                    <h4 class="font-bold text-gray-800 mb-2">Koşulsuz Sevgi Kazanırsınız</h4>
                    <p class="text-gray-600">Bir hayvanın size vereceği sevgi saf, karşılıksız ve eşsizdir.</p>
                </div>
                <div class="p-4 border-l-4 border-purple-400">
                    <h4 class="font-bold text-gray-800 mb-2">Topluma Katkı Sağlarsınız</h4>
                    <p class="text-gray-600">Sahiplenme bilincini yayarak toplumsal farkındalığa katkıda bulunursunuz.</p>
                </div>
                <div class="p-4 border-l-4 border-yellow-400">
                    <h4 class="font-bold text-gray-800 mb-2">Sürdürülebilir Yaklaşım</h4>
                    <p class="text-gray-600">Hayvan istismarına karşı durarak daha etik bir dünya için adım atarsınız.</p>
                </div>
            </div>
        </div>

        <!-- Önemli Sorular -->
        <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
            <h2 class="text-2xl font-bold text-orange-400 mb-6">
                <i class="fas fa-question-circle mr-3"></i>Sahiplenmeden Önce Kendinize Sorun
            </h2>
            <div class="space-y-4">
                <div class="p-4 bg-orange-50 rounded-lg">
                    <h4 class="font-bold text-gray-800 mb-2">Zamanınız var mı?</h4>
                    <p class="text-gray-600">Hayvanınıza günlük bakım, egzersiz ve ilgi verebilecek misiniz?</p>
                </div>
                <div class="p-4 bg-red-50 rounded-lg">
                    <h4 class="font-bold text-gray-800 mb-2">Ekonomik durumunuz uygun mu?</h4>
                    <p class="text-gray-600">Mama, veteriner, bakım masraflarını karşılayabilir misiniz?</p>
                </div>
                <div class="p-4 bg-indigo-50 rounded-lg">
                    <h4 class="font-bold text-gray-800 mb-2">Yaşam alanınız uygun mu?</h4>
                    <p class="text-gray-600">Hayvanın rahatça yaşayabileceği bir ortam sağlayabilir misiniz?</p>
                </div>
                <div class="p-4 bg-green-50 rounded-lg">
                    <h4 class="font-bold text-gray-800 mb-2">Uzun vadeli taahhüt verebilir misiniz?</h4>
                    <p class="text-gray-600">Hayvanlar 10-20 yıl yaşar. Bu süre boyunca sorumluluğu alabilir misiniz?</p>
                </div>
            </div>
        </div>

        <!-- Geri Dönüş Butonu -->
        <div class="text-center">
            <a href="index.php" class="inline-flex items-center bg-pink-400 hover:bg-pink-500 text-white font-semibold py-3 px-6 rounded-lg transition duration-300 shadow-md">
                <i class="fas fa-arrow-left mr-2"></i>
                Anasayfaya Dön
            </a>
        </div>
    </div>
</div>

<?php include("includes/footer.php"); ?>

</body>
</html>
