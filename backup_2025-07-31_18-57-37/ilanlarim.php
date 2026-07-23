<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include("includes/db.php");

if (!isset($_SESSION['kullanici_id'])) {
    $_SESSION['mesaj'] = "Bu sayfayı görmek için giriş yapmalısınız.";
    $_SESSION['mesaj_tur'] = "warning";
    header("Location: giris.php");
    exit;
}

$kullanici_id = intval($_SESSION['kullanici_id']);

$stmt = $conn->prepare("
    SELECT
        i.id,
        i.baslik,
        i.aciklama,
        i.tarih,
        k.ad AS kategori_adi,
        c.ad AS cinsiyet_adi,
        il.ad AS il_adi,
        i.foto
    FROM
        ilanlar i
    LEFT JOIN
        kategoriler k ON i.kategori_id = k.id
    LEFT JOIN
        cinsler c ON i.cins_id = c.id
    LEFT JOIN
        il il ON i.il_id = il.id
    WHERE
        i.kullanici_id = ?
    ORDER BY
        i.id DESC
");
$stmt->bind_param("i", $kullanici_id);
$stmt->execute();
$sonuc = $stmt->get_result();

$page_title = "İlanlarım - Sıcak Patizi";
include("includes/header.php");
?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-grow">
    <!-- Sayfa Başlığı -->
    <div class="text-center mb-10">
        <h1 class="text-4xl font-bold text-gray-800 mb-3 flex items-center justify-center">
            <i class="fas fa-list-alt text-primary mr-3"></i>
            Yayınladığım İlanlar
        </h1>
        <p class="text-lg text-gray-600">Sahiplendirmek üzere verdiğiniz ilanları buradan yönetebilirsiniz</p>
    </div>

    <?php if ($sonuc->num_rows > 0): ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php while($row = $sonuc->fetch_assoc()): ?>
                <div class="bg-white rounded-xl shadow-lg overflow-hidden card-hover flex flex-col justify-between">
                    <div>
                        <div class="relative">
                            <?php
                            $image_path = !empty($row['foto']) ? 'uploads/' . htmlspecialchars($row['foto']) : '';
                            $display_image = (file_exists($image_path) && !empty($image_path)) ? $image_path : 'https://via.placeholder.com/400x300?text=Resim+Yok';
                            ?>
                            <img src="<?= $display_image; ?>" alt="<?= htmlspecialchars($row['baslik']); ?>" class="w-full h-48 object-cover">
                        </div>

                        <div class="p-5 flex-grow">
                            <h3 class="text-xl font-bold text-gray-800 mb-2"><?= htmlspecialchars($row['baslik']); ?></h3>

                            <div class="flex flex-wrap gap-1.5 mb-3">
                                <?php if (!empty($row['kategori_adi'])): ?>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-primary-light text-primary">
                                        <i class="fas fa-tag mr-1"></i><?= htmlspecialchars($row['kategori_adi']) ?>
                                    </span>
                                <?php endif; ?>
                                <?php if (!empty($row['cinsiyet_adi'])): ?>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <i class="fas fa-paw mr-1"></i><?= htmlspecialchars($row['cinsiyet_adi']) ?>
                                    </span>
                                <?php endif; ?>
                                <?php if (!empty($row['il_adi'])): ?>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                        <i class="fas fa-map-marker-alt mr-1"></i><?= htmlspecialchars($row['il_adi']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <p class="text-gray-600 text-sm mb-4 line-clamp-3"><?= htmlspecialchars($row['aciklama'] ?? ''); ?></p>
                        </div>
                    </div>

                    <div class="p-5 pt-0 flex items-center justify-between border-t border-gray-100 mt-4 pt-3">
                        <a href="ilan_detay.php?id=<?= $row['id'] ?>" class="text-sm font-semibold text-primary hover:underline">
                            <i class="fas fa-eye mr-1"></i> Görüntüle
                        </a>

                        <div class="flex space-x-2">
                            <a href='ilan_duzenle.php?id=<?= $row['id'] ?>' class='btn-gradient text-white font-semibold py-1.5 px-3 rounded-lg text-sm transition duration-300 shadow-sm'>
                                <i class="fas fa-edit mr-1"></i> Düzenle
                            </a>
                            <button onclick='confirmDelete(<?= $row['id'] ?>)' class='bg-red-500 hover:bg-red-600 text-white font-semibold py-1.5 px-3 rounded-lg transition duration-300 text-sm shadow-sm'>
                                <i class="fas fa-trash-alt mr-1"></i> Sil
                            </button>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-16 bg-white rounded-xl shadow-lg card-hover">
            <div class="text-6xl mb-4">🐾</div>
            <h3 class="text-2xl font-bold text-gray-800 mb-2">Henüz İlanınız Bulunmuyor</h3>
            <p class="text-gray-600 mb-6">Yuva arayan bir can dostumuz için hemen ücretsiz ilan oluşturabilirsiniz.</p>
            <a href="ilan_ekle.php" class="btn-gradient text-white px-6 py-3 rounded-lg font-semibold inline-flex items-center shadow-md">
                <i class="fas fa-plus mr-2"></i>Yeni İlan Oluştur
            </a>
        </div>
    <?php endif; ?>
</main>

<script>
function confirmDelete(ilanId) {
    Swal.fire({
        title: 'İlanı Silmek İstiyor musunuz?',
        text: "Bu işlem geri alınamaz!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Evet, Sil!',
        cancelButtonText: 'İptal'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'ilan_sil.php?id=' + ilanId;
        }
    });
}
</script>

<?php
$stmt->close();
include("includes/footer.php");
?>