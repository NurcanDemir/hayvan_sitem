<?php

session_start();
// Kullanıcı oturum açmamışsa giriş sayfasına yönlendir
if (!isset($_SESSION['kullanici_id'])) {
    header("Location: giris.php");
    exit;
}

// Veritabanı bağlantısı ve başlık (header) dosyalarını dahil et
include("includes/db.php");

// Hata raporlamayı aç (geçici olarak, hata ayıklama için)
// error_reporting(E_ALL); // Üretim ortamında kapatılmalı
// ini_set('display_errors', 1); // Üretim ortamında kapatılmalı

$mesaj = ""; // Kullanıcıya gösterilecek mesaj değişkeni
$mesaj_tur = ""; // 'success' veya 'danger' veya 'warning'

// Kategorileri (Hayvan Türleri) veritabanından çek
$kategoriler = [];
$kategorisor = mysqli_query($conn, "SELECT * FROM kategoriler ORDER BY ad ASC");
while($kat = mysqli_fetch_assoc($kategorisor)) $kategoriler[] = $kat;

// Cinsleri veritabanından çek ve kategoriye göre grupla
$cinsler = [];
$cinssor = mysqli_query($conn, "SELECT id, kategori_id, ad FROM cinsler ORDER BY kategori_id, ad ASC");
while($cins = mysqli_fetch_assoc($cinssor)) {
    $cinsler[$cins['kategori_id']][] = [
        'id' => $cins['id'],
        'ad' => $cins['ad']
    ];
}

// İlleri veritabanından çek
$iller = [];
$ilsor = mysqli_query($conn, "SELECT * FROM il ORDER BY ad ASC");
while($il = mysqli_fetch_assoc($ilsor)) $iller[] = $il;

// İlçeleri veritabanından çek ve ile göre grupla
$ilceler = [];
$ilcesor = mysqli_query($conn, "SELECT id, il_id, ad FROM ilce ORDER BY il_id, ad ASC");
while($ilce = mysqli_fetch_assoc($ilcesor)) {
    $ilceler[$ilce['il_id']][] = [
        'id' => $ilce['id'],
        'ad' => $ilce['ad']
    ];
}

// Cinslere göre hastalık eşleştirmelerini veritabanından çek
$hastaliklar_cins = [];
$hc_sor = mysqli_query($conn, "SELECT hc.cins_id, h.id, h.ad FROM hastaliklar_cinsler hc
                                JOIN hastaliklar h ON hc.hastalik_id = h.id ORDER BY hc.cins_id, h.ad ASC");
while($row = mysqli_fetch_assoc($hc_sor)) {
    $hastaliklar_cins[$row['cins_id']][] = [
        'id' => $row['id'],
        'ad' => $row['ad']
    ];
}

// Form gönderildiyse (POST isteği ve 'ekle' butonu tıklandıysa)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ekle'])) {
    // Form verilerini güvenli hale getir
    $baslik = mysqli_real_escape_string($conn, trim($_POST['baslik']));
    $aciklama = mysqli_real_escape_string($conn, trim($_POST['aciklama']));
    $kategori_id = intval($_POST['kategori_id']);
    $cins_id = intval($_POST['cins_id']);
    $hastalik_id = intval($_POST['hastalik_id'] ?? 0); // Hastalık ID'si alınır, yoksa 0 (Hastalık Yok)
    $il_id = intval($_POST['il_id']);
    $ilce_id = intval($_POST['ilce_id']);
    $iletisim = mysqli_real_escape_string($conn, trim($_POST['iletisim']));
    $kullanici_id = $_SESSION['kullanici_id'];

    // Yeni eklenen alanlar
    $yas = intval($_POST['yas']);
    $cinsiyet = mysqli_real_escape_string($conn, $_POST['cinsiyet']);
    $asi_durumu = mysqli_real_escape_string($conn, trim($_POST['asi_durumu']));
    $kisirlastirma = isset($_POST['kisirlastirma']) ? 1 : 0; // Checkbox işaretliyse 1, değilse 0
    $adres = mysqli_real_escape_string($conn, trim($_POST['adres'])); // Adres alanı

    // "Hastalık Yok" seçeneği (value="0") gelirse, veritabanına NULL olarak kaydet
    $hastalik_id_for_db = ($hastalik_id === 0) ? "NULL" : $hastalik_id;

    $foto_adi = "";
    $hedef_klasor = "uploads/"; // Fotoğrafların kaydedileceği klasör (önceki uyarımıza göre güncellendi)

    // Eğer bir dosya yüklendi ise
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($_FILES['foto']['type'], $allowed_types)) {
            $uzanti = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $foto_adi = uniqid() . "." . $uzanti; // Benzersiz dosya adı oluştur
            $foto_tmp = $_FILES['foto']['tmp_name'];
            $foto_yolu = $hedef_klasor . $foto_adi;

            if (!is_dir($hedef_klasor)) { // Hedef klasör yoksa oluştur
                mkdir($hedef_klasor, 0777, true);
            }

            if (move_uploaded_file($foto_tmp, $foto_yolu)) {
                // Başarılı yükleme
            } else {
                $mesaj = "Fotoğraf yüklenirken hata oluştu.";
                $mesaj_tur = "warning";
            }
        } else {
            $mesaj = "Yalnızca JPG, PNG ve GIF formatlarında fotoğraf yükleyebilirsiniz.";
            $mesaj_tur = "warning";
        }
    }
    // Eğer kameradan çekilen bir fotoğraf base64 formatında gönderildi ise
    else if (isset($_POST['camera_image_data']) && !empty($_POST['camera_image_data'])) {
        $img_data = $_POST['camera_image_data'];
        $img_data = str_replace('data:image/png;base64,', '', $img_data); // PNG olduğunu varsaydık
        $img_data = str_replace(' ', '+', $img_data);
        $data = base64_decode($img_data);

        $foto_adi = uniqid() . ".png"; // Kameradan gelen genellikle PNG olur
        $foto_yolu = $hedef_klasor . $foto_adi;

        if (!is_dir($hedef_klasor)) {
            mkdir($hedef_klasor, 0777, true);
        }

        if (file_put_contents($foto_yolu, $data)) {
            // Başarılı yükleme
        } else {
            $mesaj = "Kameradan alınan fotoğraf kaydedilirken hata oluştu.";
            $mesaj_tur = "warning";
        }
    } else {
        $mesaj = "Fotoğraf seçilmedi veya kamera verisi alınamadı.";
        $mesaj_tur = "warning";
    }

    // Sadece fotoğraf başarıyla yüklendiyse (veya kamera fotoğrafı alındıysa) ilanı kaydet
    if (!empty($foto_adi)) {
        // Veritabanına ilan bilgilerini ekle (yeni sütunlar eklendi)
        $sorgu = "INSERT INTO ilanlar
                    (baslik, aciklama, foto, kullanici_id, kategori_id, cins_id, hastalik_id, il_id, ilce_id, iletisim, yas, cinsiyet, asi_durumu, kisirlastirma, adres)
                    VALUES
                    (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"; // 15 adet soru işareti

        $stmt = $conn->prepare($sorgu);
        if ($stmt) {
            // hastalik_id_for_db NULL olabilir, bu yüzden bind_param'da 'i' yerine uygun tip kullanılmalı
            // Eğer $hastalik_id_for_db 'NULL' string'i ise bind_param hatası verir, int olarak ayarlayalım.
            $hastalik_bind_param = ($hastalik_id === 0) ? null : $hastalik_id; // mysqli::bind_param null değeri için 'i' tipiyle çalışmayabilir.

            $stmt->bind_param("sssiisiiisissis",
                $baslik, $aciklama, $foto_adi, $kullanici_id, $kategori_id, $cins_id, $hastalik_bind_param, // hastalik_bind_param null olabilir
                $il_id, $ilce_id, $iletisim, $yas, $cinsiyet, $asi_durumu, $kisirlastirma, $adres);

            // Eğer hastalik_bind_param null ise ve bind_param hata verirse,
            // $hastalik_bind_param'ı $hastalik_id (0 veya int) olarak kullanın ve veritabanı sütununu NULL kabul edecek şekilde ayarlayın.
            // Alternatif olarak, sorguyu doğrudan NULL ekleyecek şekilde değiştirebiliriz:
            /*
            $hastalik_kolonu = ($hastalik_id === 0) ? "NULL" : "?";
            $sorgu = "INSERT INTO ilanlar (baslik, aciklama, foto, kullanici_id, kategori_id, cins_id, hastalik_id, il_id, ilce_id, iletisim, yas, cinsiyet, asi_durumu, kisirlastirma, adres)
                        VALUES (?, ?, ?, ?, ?, ?, $hastalik_kolonu, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sorgu);
            if ($hastalik_kolonu === "NULL") {
                $stmt->bind_param("sssiisissis", ...); // h atlandı
            } else {
                $stmt->bind_param("sssiisiiisissis", ...); // h dahil
            }
            */
            // Şimdilik varsayılan haliyle devam edelim, eğer hata alırsanız bu kısma döneriz.


            if ($stmt->execute()) {
                $mesaj = "İlan başarıyla eklendi.";
                $mesaj_tur = "success";
                // Formu temizle
                $_POST = array(); // Tüm POST verilerini temizleyerek formu sıfırla
            } else {
                $mesaj = "Veritabanı hatası: " . $stmt->error;
                $mesaj_tur = "danger";
            }
            $stmt->close();
        } else {
            $mesaj = "Sorgu hazırlanamadı: " . $conn->error;
            $mesaj_tur = "danger";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yeni İlan Oluştur</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Genel Pastel Renk Tanımlamaları */
        :root {
            --pastel-bej: #F5EFE6;
            --pastel-mint: #D6EAD7;
            --pastel-gokyuzu: #BDE0FE;
            --pastel-gul: #FFC7D8;
            --pastel-lavanta: #E0BBE4;
            --pastel-gri: #DADBDB;
            --koyu-pembe: #C2185B; /* Koyu Pembe - ana vurgu */
            --acik-mavi: #e0f2f7; /* Açık mavimsi (modal/bg) */
            --orta-mavi: #a7d9ed; /* Orta mavi */
            --koyu-gri: #4A5568; /* Metinler için koyu gri */
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: var(--pastel-bej); /* Pastel Bej arka plan */
            line-height: 1.6;
            color: #333;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .container {
            max-width: 1000px; /* Daha geniş bir form alanı */
            margin: 2rem auto;
            padding: 2.5rem; /* Daha fazla padding */
            background-color: white;
            border-radius: 1.5rem; /* Daha yuvarlak köşeler */
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15); /* Daha belirgin gölge */
            flex-grow: 1; /* İçeriği doldursun */
        }

        h1 {
            color: var(--koyu-pembe); /* Koyu pembe başlık */
            font-size: 2.8rem; /* Daha büyük başlık */
            font-weight: 800; /* Ekstra kalın */
            text-align: center;
            margin-bottom: 2.5rem; /* Alt boşluk */
            padding-bottom: 1rem;
            border-bottom: 3px solid var(--pastel-gul); /* Pastel gül rengi alt çizgi */
        }

        label {
            display: block;
            margin-bottom: 0.6rem;
            font-weight: 700;
            color: var(--koyu-gri); /* Koyu gri label metinleri */
            font-size: 1.05rem;
        }

        .form-input-custom, .form-textarea-custom, .form-select-custom {
            width: 100%;
            padding: 0.9rem 1.2rem;
            margin-bottom: 1.5rem; /* Boşluk artırıldı */
            border: 1px solid var(--pastel-gri); /* Pastel Gri kenarlık */
            border-radius: 0.75rem; /* Yuvarlak köşeler */
            box-sizing: border-box;
            font-size: 1rem;
            color: #333;
            background-color: #FDFDFD;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-input-custom:focus, .form-textarea-custom:focus, .form-select-custom:focus {
            outline: none;
            border-color: var(--pastel-gokyuzu);
            box-shadow: 0 0 0 4px rgba(189, 224, 254, 0.4); /* Pastel mavi odak gölgesi */
        }
        .form-textarea-custom {
            min-height: 120px;
            resize: vertical;
        }
        .form-select-custom {
            appearance: none; /* Varsayılan select okunu gizle */
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 16px 12px;
        }

        .radio-group label {
            display: inline-flex;
            align-items: center;
            margin-right: 1.5rem;
            font-weight: normal; /* Radyo buton label'ları kalın olmasın */
            color: #555;
        }
        .radio-group input[type="radio"] {
            margin-right: 0.5rem;
            transform: scale(1.1); /* Radyo butonları biraz büyüt */
            accent-color: var(--koyu-pembe); /* İşaretli rengi */
        }
        .checkbox-group label {
            display: inline-flex;
            align-items: center;
            font-weight: normal;
            color: #555;
        }
        .checkbox-group input[type="checkbox"] {
            margin-right: 0.5rem;
            transform: scale(1.1);
            accent-color: var(--koyu-pembe);
        }

        .btn-submit {
            background-color: var(--koyu-pembe);
            color: white;
            padding: 1rem 2rem;
            border-radius: 0.75rem;
            font-size: 1.25rem;
            font-weight: 700;
            cursor: pointer;
            transition: background-color 0.3s ease-in-out, transform 0.2s, box-shadow 0.2s;
            border: none;
            width: 100%;
            box-shadow: 0 6px 15px rgba(194, 24, 91, 0.2); /* Koyu pembe tonunda gölge */
        }
        .btn-submit:hover {
            background-color: #A2144C; /* Daha koyu pembe hover */
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(194, 24, 91, 0.3);
        }

        .file-upload-container {
            border: 2px dashed var(--pastel-gokyuzu);
            border-radius: 1rem;
            padding: 2rem;
            text-align: center;
            background-color: var(--acik-mavi); /* Açık mavi arka plan */
            transition: background-color 0.3s ease;
        }
        .file-upload-container:hover {
            background-color: var(--orta-mavi);
        }
        .file-input {
            opacity: 0;
            position: absolute;
            width: 0;
            height: 0;
            overflow: hidden;
        }
        .custom-file-upload, .camera-capture-button {
            display: inline-flex;
            align-items: center;
            padding: 0.8rem 1.5rem;
            background-color: var(--pastel-gokyuzu);
            color: #333;
            border-radius: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s;
            margin: 0.5rem;
            border: 1px solid var(--pastel-gokyuzu);
        }
        .custom-file-upload:hover, .camera-capture-button:hover {
            background-color: #A2D2FF;
            transform: translateY(-1px);
        }
        .camera-preview {
            width: 100%;
            max-width: 400px;
            margin-top: 1rem;
            border-radius: 0.75rem;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            display: none; /* Başlangıçta gizli */
        }
        .captured-image {
            display: none; /* Başlangıçta gizli */
            width: 100%;
            max-width: 400px;
            margin-top: 1rem;
            border-radius: 0.75rem;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .image-preview-area {
            margin-top: 1.5rem;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100px;
            background-color: #f0f8ff; /* Çok açık mavi */
            border-radius: 1rem;
            border: 1px solid var(--pastel-gokyuzu);
            padding: 1rem;
            overflow: hidden;
            position: relative;
        }
        .image-preview-area img, .image-preview-area video, .image-preview-area canvas {
            max-width: 100%;
            height: auto;
            border-radius: 0.5rem;
            display: block;
        }
        .image-placeholder {
            color: #999;
            font-style: italic;
        }
        .message-box {
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            border-radius: 0.75rem;
            font-weight: 500;
            font-size: 1.05rem;
            display: flex;
            align-items: center;
        }
        .message-box i {
            margin-right: 0.75rem;
            font-size: 1.2rem;
        }
        .message-success { background-color: var(--pastel-mint); color: #155724; border: 1px solid #C3E3C4; }
        .message-danger { background-color: #FFC7D8; color: #721C24; border: 1px solid #FFB0C7; }
        .message-warning { background-color: #FFEDCC; color: #856404; border: 1px solid #FFE0A6; }

        footer {
            margin-top: auto; /* Sayfanın altına yapışsın */
        }
    </style>
</head>
<body class="bg-gradient-to-br from-pastel-bej to-pastel-lavanta font-sans leading-normal tracking-normal min-h-screen flex flex-col pt-16">

<?php include("includes/header.php"); ?>

<div class="container mx-auto p-4 mt-20"> <!-- Header'dan sonra boşluk için mt-20 -->
    <div class="bg-white rounded-lg shadow-xl p-8 max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-koyu-pembe mb-6 text-center">Yeni İlan Oluştur</h1>

        <?php if (!empty($mesaj)): ?>
            <div class="bg-<?= $mesaj_tur == 'success' ? 'green-100' : ($mesaj_tur == 'danger' ? 'red-100' : 'yellow-100') ?> border-l-4 border-<?= $mesaj_tur == 'success' ? 'green-500' : ($mesaj_tur == 'danger' ? 'red-500' : 'yellow-500') ?> text-<?= $mesaj_tur == 'success' ? 'green-700' : ($mesaj_tur == 'danger' ? 'red-700' : 'yellow-700') ?> p-4 mb-6 rounded-md" role="alert">
                <?= htmlspecialchars($mesaj) ?>
            </div>
        <?php endif; ?>

        <form action="ilan_ekle.php" method="POST" enctype="multipart/form-data">
            <!-- Form alanları burada, Tailwind CSS sınıflarıyla -->
            <div class="mb-4">
                <label for="baslik" class="block text-gray-700 text-sm font-bold mb-2">Başlık</label>
                <input type="text" name="baslik" id="baslik" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div class="mb-4">
                <label for="aciklama" class="block text-gray-700 text-sm font-bold mb-2">Açıklama</label>
                <textarea name="aciklama" id="aciklama" rows="6" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="mb-4">
                    <label for="kategori" class="block text-gray-700 text-sm font-bold mb-2">Hayvan Türü (Kategori)</label>
                    <select name="kategori_id" id="kategori" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                        <option value="">Seçiniz</option>
                        <?php foreach($kategoriler as $kat): ?>
                            <option value="<?= $kat['id'] ?>" <?= (isset($_POST['kategori_id']) && $_POST['kategori_id'] == $kat['id']) ? 'selected' : '' ?>><?= htmlspecialchars($kat['ad']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="cins" class="block text-gray-700 text-sm font-bold mb-2">Cins</label>
                    <select name="cins_id" id="cins" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                        <option value="">Önce kategori seçiniz</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="mb-4">
                    <label for="yas" class="block text-gray-700 text-sm font-bold mb-2">Yaş</label>
                    <input type="number" name="yas" id="yas" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Hayvanın yaşı (ör: 2)" min="0">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Cinsiyet</label>
                    <div class="radio-group flex items-center h-full">
                        <label class="mr-4">
                            <input type="radio" name="cinsiyet" value="e" required> Erkek
                        </label>
                        <label>
                            <input type="radio" name="cinsiyet" value="d"> Dişi
                        </label>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label for="asi_durumu" class="block text-gray-700 text-sm font-bold mb-2">Aşı Durumu</label>
                <input type="text" name="asi_durumu" id="asi_durumu" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Yapılan aşılar veya 'Tam', 'Eksik', 'Yok'">
            </div>

            <div class="checkbox-group mb-4">
                <label class="inline-flex items-center">
                    <input type="checkbox" name="kisirlastirma" value="1" class="form-checkbox h-5 w-5 text-koyu-pembe">
                    <span class="ml-2 text-gray-700 text-sm font-medium">Kısırlaştırılmış</span>
                </label>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="mb-4">
                    <label for="il" class="block text-gray-700 text-sm font-bold mb-2">İl</label>
                    <select name="il_id" id="il" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                        <option value="">Seçiniz</option>
                        <?php foreach($iller as $il): ?>
                            <option value="<?= $il['id'] ?>" <?= (isset($_POST['il_id']) && $_POST['il_id'] == $il['id']) ? 'selected' : '' ?>><?= htmlspecialchars($il['ad']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="ilce" class="block text-gray-700 text-sm font-bold mb-2">İlçe</label>
                    <select name="ilce_id" id="ilce" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                        <option value="">Önce il seçiniz</option>
                    </select>
                </div>
            </div>
            <div class="mb-4">
                <label for="adres" class="block text-gray-700 text-sm font-bold mb-2">Adres (Mahalle, Cadde vb. detaylar)</label>
                <textarea name="adres" id="adres" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Hayvanın bulunduğu konumun daha detaylı adresi"></textarea>
            </div>

            <div class="mb-4">
                <label for="hastalik_id" class="block text-gray-700 text-sm font-bold mb-2">Hastalığı (Varsa)</label>
                <select name="hastalik_id" id="hastalik_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <option value="0">Hastalık Yok</option>
                    <option value="">Önce cins seçiniz</option>
                </select>
            </div>

            <div class="mb-4">
                <label for="iletisim" class="block text-gray-700 text-sm font-bold mb-2">İletişim Bilgisi (Telefon, E-posta vb.)</label>
                <input type="text" name="iletisim" id="iletisim" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required placeholder="İletişim numaranız veya e-posta adresiniz">
            </div>

            <div class="file-upload-container mb-6">
                <p class="text-xl font-semibold text-gray-700 mb-4">Fotoğraf Yükle</p>
                <p class="text-gray-600 mb-6">Hayvanın en iyi fotoğrafını seçin veya anında çekin.</p>

                <div class="flex flex-wrap justify-center items-center gap-4">
                    <label for="foto" class="custom-file-upload">
                        <i class="fas fa-upload mr-2"></i> Dosyadan Seç
                    </label>
                    <input type="file" name="foto" id="foto" class="file-input" accept="image/*">

                    <button type="button" id="openCameraButton" class="camera-capture-button">
                        <i class="fas fa-camera mr-2"></i> Kamera ile Çek
                    </button>
                </div>
                <p class="text-sm text-gray-500 mt-4">Sadece JPG, PNG ve GIF formatları desteklenir.</p>

                <div class="image-preview-area mt-4">
                    <video id="cameraFeed" class="camera-preview"></video>
                    <canvas id="cameraCanvas" class="captured-image"></canvas>
                    <img id="imagePreview" class="captured-image" src="#" alt="Önizleme">
                    <p id="imagePlaceholder" class="image-placeholder">Buraya fotoğraf önizlemesi gelecek</p>
                </div>
                <button type="button" id="captureButton" class="btn-submit mt-4 hidden">Fotoğrafı Çek</button>
                <button type="button" id="resetImageButton" class="btn-submit bg-gray-500 hover:bg-gray-600 mt-2 hidden">Fotoğrafı Sıfırla</button>
                <input type="hidden" name="camera_image_data" id="cameraImageData">
            </div>
            <button type="submit" name="ekle" class="bg-koyu-pembe hover:bg-pink-700 text-white font-bold py-3 px-6 rounded-full w-full transition duration-300">
                <i class="fas fa-plus-circle mr-2"></i>İlanı Oluştur
            </button>
        </form>
    </div>
</div>

<?php include("includes/footer.php"); ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // PHP'den gelen JSON verilerini JavaScript değişkenlerine ata
    const cinsler = <?= json_encode($cinsler) ?>;
    const ilceler = <?= json_encode($ilceler) ?>;
    const hastaliklarCins = <?= json_encode($hastaliklar_cins) ?>;

    // HTML elementlerini seç
    const kategoriSelect = document.getElementById('kategori');
    const cinsSelect = document.getElementById('cins');
    const hastalikSelect = document.getElementById('hastalik_id');
    const ilSelect = document.getElementById('il');
    const ilceSelect = document.getElementById('ilce');

    // Kamera ve Fotoğraf Elemanları
    const openCameraButton = document.getElementById('openCameraButton');
    const captureButton = document.getElementById('captureButton');
    const resetImageButton = document.getElementById('resetImageButton');
    const cameraFeed = document.getElementById('cameraFeed');
    const cameraCanvas = document.getElementById('cameraCanvas');
    const imagePreview = document.getElementById('imagePreview');
    const imagePlaceholder = document.getElementById('imagePlaceholder');
    const fileInput = document.getElementById('foto');
    const cameraImageDataInput = document.getElementById('cameraImageData');

    let stream = null; // Kamera akışını tutacak değişken

    // *** Dropdown İşlemleri ***
    function populateCinses(kategoriId, selectedCinsId = null) {
        cinsSelect.innerHTML = '<option value="">Seçiniz</option>';
        if (cinsler[kategoriId]) {
            cinsler[kategoriId].forEach(function(cins) {
                const option = document.createElement('option');
                option.value = cins.id;
                option.textContent = cins.ad;
                if (selectedCinsId && selectedCinsId == cins.id) {
                    option.selected = true;
                }
                cinsSelect.appendChild(option);
            });
        }
        // Kategori değiştiğinde hastalıkları da sıfırla veya doldur
        populateHastaliklar(cinsSelect.value, null);
    }

    function populateIlces(ilId, selectedIlceId = null) {
        ilceSelect.innerHTML = '<option value="">Seçiniz</option>';
        if (ilceler[ilId]) {
            ilceler[ilId].forEach(function(ilce) {
                const option = document.createElement('option');
                option.value = ilce.id;
                option.textContent = ilce.ad;
                if (selectedIlceId && selectedIlceId == ilce.id) {
                    option.selected = true;
                }
                ilceSelect.appendChild(option);
            });
        }
    }

    function populateHastaliklar(cinsId, selectedHastalikId = null) {
        hastalikSelect.innerHTML = '<option value="0">Hastalık Yok</option>'; // Her zaman 'Hastalık Yok' ile başla

        if (hastaliklarCins[cinsId]) {
            hastaliklarCins[cinsId].forEach(function(hastalik) {
                const option = document.createElement('option');
                option.value = hastalik.id;
                option.textContent = hastalik.ad;
                if (selectedHastalikId && selectedHastalikId == hastalik.id) {
                    option.selected = true;
                }
                hastalikSelect.appendChild(option);
            });
        } else if (cinsId === "") {
            // Eğer cins seçimi boşsa veya yoksa
            hastalikSelect.innerHTML = '<option value="0">Hastalık Yok</option><option value="">Önce cins seçiniz</option>';
        }
    }

    // Olay Dinleyicileri
    if (kategoriSelect) {
        kategoriSelect.addEventListener('change', function() {
            populateCinses(this.value);
        });
    }

    if (cinsSelect) {
        cinsSelect.addEventListener('change', function() {
            populateHastaliklar(this.value);
        });
    }

    if (ilSelect) {
        ilSelect.addEventListener('change', function() {
            populateIlces(this.value);
        });
    }

    // Sayfa yüklendiğinde mevcut değerleri doldur (eğer formda hata olduysa inputlar kalmış olabilir)
    const initialKategoriId = kategoriSelect ? kategoriSelect.value : '';
    if (initialKategoriId) {
        populateCinses(initialKategoriId, '<?= htmlspecialchars($_POST['cins_id'] ?? '') ?>');
    } else {
        cinsSelect.innerHTML = '<option value="">Önce kategori seçiniz</option>';
    }

    const initialCinsIdForHastalik = cinsSelect ? cinsSelect.value : '';
    if (initialCinsIdForHastalik) {
        populateHastaliklar(initialCinsIdForHastalik, '<?= htmlspecialchars($_POST['hastalik_id'] ?? '0') ?>');
    } else {
        hastalikSelect.innerHTML = '<option value="0">Hastalık Yok</option><option value="">Önce cins seçiniz</option>';
    }

    const initialIlId = ilSelect ? ilSelect.value : '';
    if (initialIlId) {
        populateIlces(initialIlId, '<?= htmlspecialchars($_POST['ilce_id'] ?? '') ?>');
    } else {
        ilceSelect.innerHTML = '<option value="">Önce il seçiniz</option>';
    }


    // *** Kamera İşlemleri ***
    if (openCameraButton) {
        openCameraButton.addEventListener('click', async () => {
            try {
                // Mevcut akış varsa durdur
                if (stream) {
                    stream.getTracks().forEach(track => track.stop());
                }

                stream = await navigator.mediaDevices.getUserMedia({ video: true });
                cameraFeed.srcObject = stream;
                cameraFeed.style.display = 'block'; // Videoyu göster
                imagePreview.style.display = 'none'; // Önizleme resmini gizle
                cameraCanvas.style.display = 'none'; // Canvas'ı gizle
                imagePlaceholder.style.display = 'none'; // Yer tutucuyu gizle
                captureButton.classList.remove('hidden'); // Fotoğraf çek butonunu göster
                resetImageButton.classList.add('hidden'); // Sıfırla butonunu gizle
                fileInput.value = ''; // Dosya inputunu sıfırla
                cameraImageDataInput.value = ''; // Kamera veri inputunu sıfırla
            } catch (err) {
                console.error("Kamera erişim hatası: ", err);
                Swal.fire('Hata!', 'Kameraya erişilemedi veya izin verilmedi. Lütfen izinleri kontrol edin.', 'error');
            }
        });
    }

    if (captureButton) {
        captureButton.addEventListener('click', () => {
            if (stream) {
                cameraCanvas.width = cameraFeed.videoWidth;
                cameraCanvas.height = cameraFeed.videoHeight;
                const context = cameraCanvas.getContext('2d');
                context.drawImage(cameraFeed, 0, 0, cameraCanvas.width, cameraCanvas.height);

                // Kamerayı durdur
                stream.getTracks().forEach(track => track.stop());
                cameraFeed.style.display = 'none'; // Kamera akışını gizle

                cameraCanvas.style.display = 'block'; // Çekilen görüntüyü göster
                imagePreview.style.display = 'none'; // Önizleme resmini gizle (sadece kamera görüntüsü)
                imagePlaceholder.style.display = 'none'; // Yer tutucuyu gizle

                // Canvas'taki görüntüyü Base64'e çevir ve gizli inputa kaydet
                cameraImageDataInput.value = cameraCanvas.toDataURL('image/png');
                fileInput.removeAttribute('required'); // Kamera ile çekildiyse dosya yükleme zorunluluğunu kaldır

                captureButton.classList.add('hidden'); // Fotoğraf çek butonunu gizle
                resetImageButton.classList.remove('hidden'); // Sıfırla butonunu göster
            }
        });
    }

    if (resetImageButton) {
        resetImageButton.addEventListener('click', () => {
            // Tüm görsel elementlerini sıfırla
            cameraFeed.srcObject = null;
            cameraFeed.style.display = 'none';
            cameraCanvas.getContext('2d').clearRect(0, 0, cameraCanvas.width, cameraCanvas.height);
            cameraCanvas.style.display = 'none';
            imagePreview.src = '#';
            imagePreview.style.display = 'none';
            imagePlaceholder.style.display = 'block'; // Yer tutucuyu tekrar göster

            fileInput.value = ''; // Dosya inputunu temizle
            fileInput.setAttribute('required', 'required'); // Dosya yükleme zorunluluğunu geri getir
            cameraImageDataInput.value = ''; // Kamera verisini temizle

            captureButton.classList.add('hidden'); // Fotoğraf çek butonunu gizle
            resetImageButton.classList.add('hidden'); // Sıfırla butonunu gizle
        });
    }

    // Dosya inputu değiştiğinde önizleme yap
    if (fileInput) {
        fileInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreview.style.display = 'block';
                    imagePlaceholder.style.display = 'none';
                    cameraFeed.style.display = 'none'; // Kamera açıksa kapat
                    if (stream) { // Kamera akışı varsa durdur
                        stream.getTracks().forEach(track => track.stop());
                        stream = null;
                    }
                    cameraCanvas.style.display = 'none';
                    captureButton.classList.add('hidden');
                    resetImageButton.classList.remove('hidden'); // Sıfırla butonu göster
                    cameraImageDataInput.value = ''; // Kamera verisini temizle
                };
                reader.readAsDataURL(file);
            } else {
                imagePreview.src = '#';
                imagePreview.style.display = 'none';
                imagePlaceholder.style.display = 'block';
                resetImageButton.classList.add('hidden');
            }
        });
    }
});
</script>

</body>
</html>