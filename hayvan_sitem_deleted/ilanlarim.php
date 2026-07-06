<?php
session_start();
include("includes/db.php"); // Veritabanı bağlantısını içerir

// Kullanıcı giriş yapmamışsa yönlendirme
// Bu kontrol en başta olmalı, aksi takdirde headers zaten gönderilmiş olabilir.
if (!isset($_SESSION['kullanici_id'])) {
    // Mesajı session'a ekleyip giriş sayfasına yönlendirme daha temiz bir yaklaşımdır.
    $_SESSION['mesaj'] = "Bu sayfayı görmek için giriş yapmalısınız.";
    $_SESSION['mesaj_tur'] = "warning";
    header("Location: giris.php");
    exit;
}

$kullanici_id = intval($_SESSION['kullanici_id']); // Güvenli int dönüşümü

// Kullanıcının ilanlarını çek (PREPARED STATEMENT ile GÜVENLİ)
$stmt = $conn->prepare("
    SELECT
        i.id,
        i.baslik,
        i.aciklama,
        i.tur,
        c.ad AS cinsiyet_adi,
        i.il,
        i.foto
    FROM
        ilanlar i
    LEFT JOIN
        cinsler c ON i.cins_id = c.id
    WHERE
        i.kullanici_id = ?
    ORDER BY
        i.id DESC
");
$stmt->bind_param("i", $kullanici_id);
$stmt->execute();
$sonuc = $stmt->get_result();

// HEADER DAHİL EDİLİR - BURADAN ÖNCE ASLA HTML ÇIKTISI OLMAMALI!
include("includes/header.php");
?>

<div class="container mx-auto px-4 py-8 mt-16 md:mt-24"> <div class="max-w-4xl mx-auto bg-white p-8 rounded-lg shadow-xl">
        <h1 class="text-3xl font-extrabold text-center text-koyu-pembe mb-6">Benim İlanlarım</h1>
        <hr class="my-6 border-gray-300">

        <?php if ($sonuc->num_rows > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">
                <?php while($row = $sonuc->fetch_assoc()): ?>
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden flex flex-col">
                        <?php
                        $image_path = htmlspecialchars($row['foto']);
                        $display_image = (file_exists($image_path) && !empty($image_path)) ? $image_path : 'https://via.placeholder.com/400x300?text=Resim+Yok';
                        ?>
                        <img src="<?= $display_image; ?>"
                             alt="<?= htmlspecialchars($row['baslik']); ?>"
                             class="w-full h-48 object-cover object-center">

                        <div class="p-6 flex-grow">
                            <h3 class="text-xl font-bold text-gray-900 mb-2"><?= htmlspecialchars($row['baslik']); ?></h3>
                            <p class="text-gray-700 text-sm mb-4 line-clamp-3"><?= htmlspecialchars($row['aciklama']); ?></p>

                            <div class="text-gray-600 text-sm mb-4">
                                <p><i class="fas fa-paw mr-2"></i> Tür: <?= htmlspecialchars($row['tur']); ?></p>
                                <p><i class="fas fa-venus-mars mr-2"></i> Cinsiyet: <?= htmlspecialchars($row['cinsiyet_adi']); ?></p>
                                <p><i class="fas fa-map-marker-alt mr-2"></i> Şehir: <?= htmlspecialchars($row['il']); ?></p>
                            </div>
                        </div>

                        <div class="p-6 pt-0 flex justify-end space-x-2">
                            <a href='ilan_duzenle.php?id=<?= $row['id'] ?>'
                               class='bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-md transition duration-300 text-sm'>
                                <i class="fas fa-edit mr-1"></i> Düzenle
                            </a>
                            <a href='ilan_sil.php?id=<?= $row['id'] ?>'
                               class='bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-md transition duration-300 text-sm'
                               onclick='return confirm("Bu ilanı silmek istediğinize emin misiniz? Bu işlem geri alınamaz.")'>
                                <i class="fas fa-trash-alt mr-1"></i> Sil
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Bilgi!</strong>
                <span class="block sm:inline"> Henüz yayınlanmış ilanınız bulunmamaktadır.</span>
                <p class="mt-2 text-sm">Yeni bir ilan eklemek için <a href="ilan_ekle.php" class="text-blue-800 underline hover:text-blue-900">buraya tıklayın</a>.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php
$stmt->close(); // Statement'ı kapat
// FOOTER DAHİL EDİLİR
include("includes/footer.php");
?>