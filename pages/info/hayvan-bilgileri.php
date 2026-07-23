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
    <title>Hayvan Bilgileri - Yuvanın Anahtarı</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="./dist/output.css" rel="stylesheet">
</head>
<body class="bg-gray-50 font-sans min-h-screen flex flex-col">

<?php include("includes/header.php"); ?>

<div class="container mx-auto px-4 py-8 mt-8">
    <div class="max-w-5xl mx-auto">
        <!-- Başlık -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-extrabold text-green-400 mb-4">
                <i class="fas fa-paw mr-3"></i>Hayvan Dünyasından İlginç Bilgiler
            </h1>
            <p class="text-xl text-gray-600">Dostlarımız hakkında şaşırtıcı gerçekler</p>
        </div>

        <!-- Kedi Bilgileri -->
        <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
            <h2 class="text-2xl font-bold text-pink-400 mb-6">
                <i class="fas fa-cat mr-3"></i>Kediler Hakkında İlginç Bilgiler
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="p-4 border-l-4 border-pink-400 bg-pink-50">
                    <h4 class="font-bold text-gray-800 mb-2">Kediler ve Su</h4>
                    <p class="text-gray-600 mb-2">Kedilerin çoğu sudan hoşlanmaz çünkü kürkleri suya maruz kaldığında ağırlaşır ve kurumakta zorlanırlar.</p>
                    <p class="text-sm text-pink-600"><strong>İstisna:</strong> Van kedileri suya düşkündür!</p>
                </div>
                <div class="p-4 border-l-4 border-purple-400 bg-purple-50">
                    <h4 class="font-bold text-gray-800 mb-2">Mırıldama Frekansı</h4>
                    <p class="text-gray-600 mb-2">Kedilerin mırıldama frekansı (20-50 Hz) kemik iyileşmesini hızlandırabilir.</p>
                    <p class="text-sm text-purple-600"><strong>Şaşırtıcı:</strong> Bu frekans tıpta da kullanılır!</p>
                </div>
                <div class="p-4 border-l-4 border-indigo-400 bg-indigo-50">
                    <h4 class="font-bold text-gray-800 mb-2">Gece Görüş</h4>
                    <p class="text-gray-600 mb-2">Kediler insanlardan 6 kat daha iyi gece görürler.</p>
                    <p class="text-sm text-indigo-600"><strong>Sebep:</strong> Gözlerindeki özel reflektör tabaka</p>
                </div>
                <div class="p-4 border-l-4 border-red-400 bg-red-50">
                    <h4 class="font-bold text-gray-800 mb-2">Uyku Şampiyonu</h4>
                    <p class="text-gray-600 mb-2">Kediler günde 12-16 saat uyur, bu da hayatlarının %70'ine denk gelir.</p>
                    <p class="text-sm text-red-600"><strong>Neden:</strong> Doğadaki avcılık enerjisini korumak için</p>
                </div>
            </div>
        </div>

        <!-- Köpek Bilgileri -->
        <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
            <h2 class="text-2xl font-bold text-green-400 mb-6">
                <i class="fas fa-dog mr-3"></i>Köpekler Hakkında İlginç Bilgiler
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="p-4 border-l-4 border-green-400 bg-green-50">
                    <h4 class="font-bold text-gray-800 mb-2">Burun İzi Kimliği</h4>
                    <p class="text-gray-600 mb-2">Her köpeğin burun izi benzersizdir, tıpkı insanların parmak izi gibi.</p>
                    <p class="text-sm text-green-600"><strong>Kullanım:</strong> Kayıp köpekleri bulmak için kullanılabilir</p>
                </div>
                <div class="p-4 border-l-4 border-blue-400 bg-blue-50">
                    <h4 class="font-bold text-gray-800 mb-2">Süper Koku Alma</h4>
                    <p class="text-gray-600 mb-2">Köpeklerin koku alma duyusu insanlardan 10,000-100,000 kat daha güçlüdür.</p>
                    <p class="text-sm text-blue-600"><strong>Yetenek:</strong> Hastalıkları bile kokularından anlayabilirler</p>
                </div>
                <div class="p-4 border-l-4 border-yellow-400 bg-yellow-50">
                    <h4 class="font-bold text-gray-800 mb-2">Renk Körlüğü</h4>
                    <p class="text-gray-600 mb-2">Köpekler kırmızı-yeşil renk körüdür, mavi ve sarıyı en iyi görürler.</p>
                    <p class="text-sm text-yellow-600"><strong>Gerçek:</strong> Tamamen renk körü değiller!</p>
                </div>
                <div class="p-4 border-l-4 border-orange-400 bg-orange-50">
                    <h4 class="font-bold text-gray-800 mb-2">Kelime Hazinesi</h4>
                    <p class="text-gray-600 mb-2">Ortalama bir köpek 150-200 kelime öğrenebilir.</p>
                    <p class="text-sm text-orange-600"><strong>En zeki:</strong> Border Collie'ler 1000+ kelime öğrenebilir</p>
                </div>
            </div>
        </div>

        <!-- Kuş Bilgileri -->
        <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
            <h2 class="text-2xl font-bold text-purple-400 mb-6">
                <i class="fas fa-dove mr-3"></i>Kuşlar Hakkında İlginç Bilgiler
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="p-4 border-l-4 border-purple-400 bg-purple-50">
                    <h4 class="font-bold text-gray-800 mb-2">Papağan Zekası</h4>
                    <p class="text-gray-600 mb-2">Papağanlar sadece taklit etmez, kelimelerin anlamını öğrenip bağlamına uygun kullanırlar.</p>
                    <p class="text-sm text-purple-600"><strong>Örnek:</strong> Alex papağanı 100+ kelime biliyordu</p>
                </div>
                <div class="p-4 border-l-4 border-teal-400 bg-teal-50">
                    <h4 class="font-bold text-gray-800 mb-2">Manyetik Navigasyon</h4>
                    <p class="text-gray-600 mb-2">Kuşlar Dünya'nın manyetik alanını kullanarak yön bulabilirler.</p>
                    <p class="text-sm text-teal-600"><strong>Yetenek:</strong> Binlerce kilometre göç edebilirler</p>
                </div>
                <div class="p-4 border-l-4 border-cyan-400 bg-cyan-50">
                    <h4 class="font-bold text-gray-800 mb-2">Kalp Atışı</h4>
                    <p class="text-gray-600 mb-2">Küçük kuşların kalbi dakikada 1000+ kez atabilir.</p>
                    <p class="text-sm text-cyan-600"><strong>Karşılaştırma:</strong> İnsan kalbi dakikada 60-100 kez atar</p>
                </div>
                <div class="p-4 border-l-4 border-rose-400 bg-rose-50">
                    <h4 class="font-bold text-gray-800 mb-2">Koku Alma</h4>
                    <p class="text-gray-600 mb-2">Çoğu kuş türü koku alamaz, ama kiwi kuşları mükemmel koku alır.</p>
                    <p class="text-sm text-rose-600"><strong>İstisna:</strong> Akbabalar da çok iyi koku alır</p>
                </div>
            </div>
        </div>

        <!-- Balık Bilgileri -->
        <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
            <h2 class="text-2xl font-bold text-blue-400 mb-6">
                <i class="fas fa-fish mr-3"></i>Balıklar Hakkında İlginç Bilgiler
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="p-4 border-l-4 border-blue-400 bg-blue-50">
                    <h4 class="font-bold text-gray-800 mb-2">Hafıza Efsanesi</h4>
                    <p class="text-gray-600 mb-2">Balıkların hafızası 3 saniye değil! Aylarca süren olayları hatırlayabilirler.</p>
                    <p class="text-sm text-blue-600"><strong>Gerçek:</strong> Karmaşık görevleri bile öğrenebilirler</p>
                </div>
                <div class="p-4 border-l-4 border-emerald-400 bg-emerald-50">
                    <h4 class="font-bold text-gray-800 mb-2">Sosyal Zeka</h4>
                    <p class="text-gray-600 mb-2">Balıklar birbirlerini tanır ve sosyal hiyerarşi oluştururlar.</p>
                    <p class="text-sm text-emerald-600"><strong>Yetenek:</strong> İşbirliği yapıp plan kurabilirler</p>
                </div>
                <div class="p-4 border-l-4 border-amber-400 bg-amber-50">
                    <h4 class="font-bold text-gray-800 mb-2">Ağrı Hissi</h4>
                    <p class="text-gray-600 mb-2">Balıklar ağrıyı hisseder ve stres yaşarlar.</p>
                    <p class="text-sm text-amber-600"><strong>Bilim:</strong> Sinir sistemi ağrı sinyallerini iletir</p>
                </div>
                <div class="p-4 border-l-4 border-violet-400 bg-violet-50">
                    <h4 class="font-bold text-gray-800 mb-2">Renk Değiştirme</h4>
                    <p class="text-gray-600 mb-2">Bazı balıklar duygularına göre renk değiştirir.</p>
                    <p class="text-sm text-violet-600"><strong>Örnek:</strong> Papağan balığı uyurken renk değiştirir</p>
                </div>
            </div>
        </div>

        <!-- Geri Dönüş Butonu -->
        <div class="text-center">
            <a href="index.php" class="inline-flex items-center bg-green-400 hover:bg-green-500 text-white font-semibold py-3 px-6 rounded-lg transition duration-300 shadow-md">
                <i class="fas fa-arrow-left mr-2"></i>
                Anasayfaya Dön
            </a>
        </div>
    </div>
</div>

<?php include("includes/footer.php"); ?>

</body>
</html>
