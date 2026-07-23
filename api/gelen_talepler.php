<?php
session_start();
include("includes/db.php"); // Veritabanı bağlantısı
// include("includes/header.php"); // Header'ı HTML içinde çağıracağız

$kullanici_id = $_SESSION['kullanici_id'] ?? 0;

// Kullanıcı oturum açmamışsa veya kullanıcı ID'si yoksa giriş sayfasına yönlendir
if ($kullanici_id === 0) {
    header("Location: giris.php");
    exit;
}

// Kullanıcının ilanlarına gelen talepler
// NOT: Bu sorguda 's.tarih' alanı olmadığı için hata veriyorsa, sahiplenme_istekleri tablosunda 'talep_tarihi' gibi bir alan olup olmadığını kontrol edin.
// Varsayılan olarak 's.tarih' kullandım. Eğer 'talep_tarihi' ise onu kullanın.
$sql = "SELECT s.*, i.baslik AS ilan_baslik, k.kullanici_adi AS talep_eden_adi, k.eposta AS talep_eden_email, k.telefon AS talep_eden_telefon
        FROM sahiplenme_istekleri s
        LEFT JOIN ilanlar i ON s.ilan_id = i.id
        LEFT JOIN kullanicilar k ON s.talep_eden_kullanici_id = k.id
        WHERE i.kullanici_id = ?
        ORDER BY s.id DESC"; // 's.tarih' yerine 's.id' ile sıralama güvenli bir başlangıç olabilir

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Sorgu hazırlama hatası: " . $conn->error);
}
$stmt->bind_param("i", $kullanici_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gelen Taleplerim</title>
    <link href="./dist/output.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        /* Ek özel stiller (ihtiyaç halinde eklenebilir) */
        .status-badge {
            @apply px-2 py-1 rounded-full text-xs font-semibold;
        }
        .status-beklemede {
            @apply bg-yellow-100 text-yellow-800; /* Pastel sarı */
        }
        .status-onaylandi {
            @apply bg-acik-yesil text-koyu-yesil; /* Pastel yeşil */
        }
        .status-reddedildi {
            @apply bg-red-100 text-red-800; /* Pastel kırmızı */
        }
    </style>
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal min-h-screen flex flex-col">

<?php include("includes/header.php"); ?>

<div class="container mx-auto px-4 py-8 mt-16 md:mt-24 flex-grow">
    <h1 class="text-4xl font-extrabold text-center text-koyu-pembe mb-8">İlanlarıma Gelen Talepler</h1>

    <div class="bg-white p-6 rounded-lg shadow-xl overflow-x-auto">
        <?php if ($result && $result->num_rows > 0): ?>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İlan Başlığı</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Talep Eden</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mesaj</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tarih</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İletişim</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        <?= htmlspecialchars($row['ilan_baslik'] ?? 'Bilinmiyor') ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?= htmlspecialchars($row['talep_eden_adi'] ?? 'Anonim') ?>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate" style="max-width: 250px;">
                        <?= nl2br(htmlspecialchars($row['mesaj'] ?? 'Mesaj yok')) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?= isset($row['tarih']) ? htmlspecialchars(date('d.m.Y H:i', strtotime($row['tarih']))) : 'N/A' ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <?php
                            $durum_class = '';
                            $durum_text = htmlspecialchars($row['durum'] ?? 'Bilinmiyor');
                            switch ($durum_text) {
                                case 'beklemede':
                                    $durum_class = 'status-beklemede';
                                    break;
                                case 'onaylandi':
                                    $durum_class = 'status-onaylandi';
                                    break;
                                case 'reddedildi':
                                    $durum_class = 'status-reddedildi';
                                    break;
                                default:
                                    $durum_class = 'bg-gray-200 text-gray-800'; // Varsayılan gri
                                    break;
                            }
                        ?>
                        <span class="status-badge <?= $durum_class ?>">
                            <?= $durum_text ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex flex-col space-y-2">
                            <?php if (!empty($row['talep_eden_email'])): ?>
                                <a href="mailto:<?= htmlspecialchars($row['talep_eden_email']) ?>"
                                   class="inline-flex items-center justify-center px-3 py-1 border border-soluk-mavi rounded-md shadow-sm text-sm font-medium text-blue-800 bg-soluk-mavi hover:bg-blue-200 transition duration-150 ease-in-out">
                                    <i class="fas fa-envelope mr-1"></i> E-posta
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($row['talep_eden_telefon'])): ?>
                                <a href="tel:<?= htmlspecialchars($row['talep_eden_telefon']) ?>"
                                   class="inline-flex items-center justify-center px-3 py-1 border border-acik-yesil rounded-md shadow-sm text-sm font-medium text-koyu-yesil bg-acik-yesil hover:bg-green-200 transition duration-150 ease-in-out">
                                    <i class="fas fa-phone-alt mr-1"></i> Telefon
                                </a>
                            <?php endif; ?>
                            <?php if (empty($row['talep_eden_email']) && empty($row['talep_eden_telefon'])): ?>
                                <span class="text-gray-500 text-xs">İletişim yok</span>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
            <div class="bg-soluk-mavi text-blue-900 p-4 rounded-lg text-center text-lg font-semibold shadow-md">
                Henüz ilanlarınıza gelen bir talep bulunmamaktadır.
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Footer -->
<footer class="bg-gray-800 text-white py-12 mt-16">
    <div class="max-w-7xl mx-auto px-6">
        <div class="text-center">
            <div class="text-3xl mb-4">🏠</div>
            <h3 class="text-2xl font-bold mb-4 text-primary-lighter">Yuva Ol</h3>
            <p class="text-gray-400">Sevgiyle Sahiplen</p>
        </div>
    </div>
</footer>

</body>
</html>