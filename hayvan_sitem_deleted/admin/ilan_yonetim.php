<?php
// admin/ilan_yonetim.php
session_start();
include("../includes/auth.php"); // Yetkilendirme kontrolünü dahil et
include("../includes/db.php"); // Veritabanı bağlantısını dahil et

$mesaj = ""; // İşlem mesajları için

// İlan Silme İşlemi
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $ilan_id = $_GET['id'];

    // Önce ilana ait favorileri sil (FOREIGN KEY kısıtlaması nedeniyle)
    $stmt_delete_fav = $conn->prepare("DELETE FROM favoriler WHERE ilan_id = ?");
    $stmt_delete_fav->bind_param("i", $ilan_id);
    $stmt_delete_fav->execute();
    $stmt_delete_fav->close();

    // Ardından ilanı sil
    $stmt_delete_ilan = $conn->prepare("DELETE FROM ilanlar WHERE id = ?");
    $stmt_delete_ilan->bind_param("i", $ilan_id);
    if ($stmt_delete_ilan->execute()) {
        $mesaj = "<div class='alert alert-success'>İlan başarıyla silindi.</div>";
    } else {
        $mesaj = "<div class='alert alert-danger'>İlan silinirken hata oluştu: " . $stmt_delete_ilan->error . "</div>";
    }
    $stmt_delete_ilan->close();
}

// İlan Durumu Güncelleme İşlemi (Aktif/Pasif/Sahiplenildi)
if (isset($_GET['action']) && ($_GET['action'] == 'aktif' || $_GET['action'] == 'pasif' || $_GET['action'] == 'sahiplenildi' || $_GET['action'] == 'silindi') && isset($_GET['id'])) {
    $ilan_id = $_GET['id'];
    $yeni_durum = $_GET['action'];

    $stmt_update_durum = $conn->prepare("UPDATE ilanlar SET durum = ? WHERE id = ?");
    $stmt_update_durum->bind_param("si", $yeni_durum, $ilan_id);
    if ($stmt_update_durum->execute()) {
        $mesaj = "<div class='alert alert-success'>İlan durumu başarıyla güncellendi: " . htmlspecialchars($yeni_durum) . "</div>";
    } else {
        $mesaj = "<div class='alert alert-danger'>İlan durumu güncellenirken hata oluştu: " . $stmt_update_durum->error . "</div>";
    }
    $stmt_update_durum->close();
}

// Tüm İlanları Çekme (Filtreleme ve Arama ile)
$sql = "
    SELECT
        i.*,
        u.kullanici_adi,
        c.ad AS cins_adi,
        h.ad AS hastalik_adi,
        k.ad AS kategori_adi,
        il.ad AS il_adi,
        ilce.ad AS ilce_adi
    FROM ilanlar i
    LEFT JOIN kullanicilar u ON i.kullanici_id = u.id
    LEFT JOIN cinsler c ON i.cins_id = c.id
    LEFT JOIN hastaliklar h ON i.hastalik_id = h.id
    LEFT JOIN kategoriler k ON i.kategori_id = k.id
    LEFT JOIN il il ON i.il_id = il.id
    LEFT JOIN ilce ilce ON i.ilce_id = ilce.id
    WHERE 1=1
";
$params = [];
$types = "";

// Arama Filtresi
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = "%" . $_GET['search'] . "%";
    $sql .= " AND (i.baslik LIKE ? OR u.kullanici_adi LIKE ? OR i.tur LIKE ?)";
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
    $types .= "sss";
}

// Durum Filtresi
if (isset($_GET['durum']) && $_GET['durum'] != '') {
    $durum_filter = $_GET['durum'];
    $sql .= " AND i.durum = ?";
    $params[] = $durum_filter;
    $types .= "s";
}

$sql .= " ORDER BY i.tarih DESC";

$stmt_ilanlar = $conn->prepare($sql);

// Parametreleri bind_param'a dinamik olarak geçir
if (!empty($params)) {
    $bind_params = array_merge([$types], $params);
    $refs = [];
    foreach($bind_params as $key => $value) {
        $refs[$key] = &$bind_params[$key];
    }
    call_user_func_array([$stmt_ilanlar, 'bind_param'], $refs);
}

$stmt_ilanlar->execute();
$ilan_sonuc = $stmt_ilanlar->get_result();

// Assuming admin_header.php already includes the basic HTML structure up to <body>
include("includes/admin_header.php"); // Admin paneline özel başlık ve menüyü dahil et
?>

    <div class="flex justify-between items-center pb-4 mb-6 border-b-2 border-gray-200">
        <h1 class="text-3xl font-semibold text-gray-800">İlan Yönetimi</h1>
    </div>

    <?php if (!empty($mesaj)): ?>
        <?php
            $alert_class = '';
            if (strpos($mesaj, 'alert-success') !== false) {
                $alert_class = 'bg-green-100 border-green-400 text-green-700';
            } elseif (strpos($mesaj, 'alert-danger') !== false) {
                $alert_class = 'bg-red-100 border-red-400 text-red-700';
            }
        ?>
        <div class="mb-4 <?php echo $alert_class; ?> border px-4 py-3 rounded relative" role="alert">
            <?php 
                // Remove Bootstrap alert classes for cleaner output
                echo str_replace(['<div class=\'alert alert-success\'>', '<div class=\'alert alert-danger\'>', '</div>'], ['', '', ''], $mesaj); 
            ?>
        </div>
    <?php endif; ?>

    <div class="bg-white p-6 rounded-lg shadow-md mb-6">
        <div class="text-lg font-semibold mb-4 border-b pb-2">
            İlan Filtreleme ve Arama
        </div>
        <form action="ilan_yonetim.php" method="GET" class="flex flex-wrap items-end -mx-2">
            <div class="w-full md:w-1/3 px-2 mb-4 md:mb-0">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Başlık, İlan Sahibi veya Tür Ara</label>
                <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" id="search" name="search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            </div>
            <div class="w-full md:w-1/4 px-2 mb-4 md:mb-0">
                <label for="durum" class="block text-sm font-medium text-gray-700 mb-1">Duruma Göre Filtrele</label>
                <select class="block w-full px-3 py-2 border border-gray-300 rounded-md bg-white focus:outline-none focus:ring-2 focus:ring-blue-500" id="durum" name="durum">
                    <option value="">Tüm Durumlar</option>
                    <option value="aktif" <?= (($_GET['durum'] ?? '') == 'aktif') ? 'selected' : '' ?>>Aktif</option>
                    <option value="sahiplenildi" <?= (($_GET['durum'] ?? '') == 'sahiplenildi') ? 'selected' : '' ?>>Sahiplenildi</option>
                    <option value="pasif" <?= (($_GET['durum'] ?? '') == 'pasif') ? 'selected' : '' ?>>Pasif</option>
                    <option value="silindi" <?= (($_GET['durum'] ?? '') == 'silindi') ? 'selected' : '' ?>>Silindi</option>
                </select>
            </div>
            <div class="w-full md:w-auto px-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">Filtrele</button>
                <a href="ilan_yonetim.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400 ml-2">Temizle</a>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto bg-white shadow-md rounded-lg p-6">
        <table class="min-w-full leading-normal table-fixed">
            <thead>
                <tr>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-16">ID</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-24">Foto</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider max-w-xs overflow-hidden text-ellipsis whitespace-nowrap">Başlık</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">İlan Sahibi</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider max-w-xs overflow-hidden text-ellipsis whitespace-nowrap">Kategori</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Tür</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-24">Durum</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-24">İlan Tarihi</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-48">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($ilan_sonuc->num_rows > 0): ?>
                    <?php while ($ilan = $ilan_sonuc->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm whitespace-nowrap overflow-hidden text-ellipsis"><?= htmlspecialchars($ilan['id']) ?></td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <?php if (!empty($ilan['foto'])): ?>
                                    <img src="../images/<?= htmlspecialchars($ilan['foto']) ?>" alt="<?= htmlspecialchars($ilan['baslik']) ?>" class="w-20 h-20 object-cover rounded-md mx-auto block">
                                <?php else: ?>
                                    <span class="text-gray-500 text-xs">Resim Yok</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm whitespace-nowrap overflow-hidden text-ellipsis max-w-xs"><?= htmlspecialchars($ilan['baslik']) ?></td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm whitespace-nowrap overflow-hidden text-ellipsis"><?= htmlspecialchars($ilan['kullanici_adi']) ?></td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm whitespace-nowrap overflow-hidden text-ellipsis max-w-xs"><?= htmlspecialchars($ilan['kategori_adi'] ?? $ilan['kategori']) ?></td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm whitespace-nowrap overflow-hidden text-ellipsis"><?= htmlspecialchars($ilan['tur']) ?></td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    <?php
                                        if ($ilan['durum'] == 'aktif') echo 'bg-green-100 text-green-800';
                                        elseif ($ilan['durum'] == 'sahiplenildi') echo 'bg-blue-100 text-blue-800';
                                        elseif ($ilan['durum'] == 'pasif') echo 'bg-yellow-100 text-yellow-800';
                                        elseif ($ilan['durum'] == 'silindi') echo 'bg-red-100 text-red-800';
                                        else echo 'bg-gray-100 text-gray-800';
                                    ?>">
                                    <?= htmlspecialchars(ucfirst($ilan['durum'])) ?>
                                </span>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm whitespace-nowrap overflow-hidden text-ellipsis"><?= htmlspecialchars($ilan['tarih']) ?></td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center">
                                <div class="flex flex-wrap items-center justify-center gap-1">
                                    <a href="../ilan_detay.php?id=<?= $ilan['id'] ?>" class="inline-flex items-center p-2 border border-blue-500 text-blue-500 hover:bg-blue-500 hover:text-white rounded-md text-xs transition-colors duration-200" target="_blank" title="Detayları Görüntüle"><i class="fas fa-eye"></i></a>
                                    <a href="ilan_duzenle.php?id=<?= $ilan['id'] ?>" class="inline-flex items-center p-2 border border-indigo-500 text-indigo-500 hover:bg-indigo-500 hover:text-white rounded-md text-xs transition-colors duration-200" title="İlanı Düzenle"><i class="fas fa-edit"></i></a>
                                    <button type="button" class="inline-flex items-center p-2 border border-red-500 text-red-500 hover:bg-red-500 hover:text-white rounded-md text-xs transition-colors duration-200" onclick="confirmDelete(<?= $ilan['id'] ?>)" title="İlanı Sil"><i class="fas fa-trash-alt"></i></button>
                                    
                                    <div class="relative inline-block text-left">
                                        <button type="button" class="inline-flex items-center p-2 border border-gray-400 text-gray-600 hover:bg-gray-100 rounded-md text-xs ml-1 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300" id="dropdownMenuButton<?= $ilan['id'] ?>" aria-expanded="false" title="Durum Güncelle">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                        <div class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-10 hidden" role="menu" aria-orientation="vertical" aria-labelledby="dropdownMenuButton<?= $ilan['id'] ?>">
                                            <div class="py-1" role="none">
                                                <a href="ilan_yonetim.php?action=aktif&id=<?= $ilan['id'] ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Aktif Yap</a>
                                                <a href="ilan_yonetim.php?action=sahiplenildi&id=<?= $ilan['id'] ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Sahiplenildi Yap</a>
                                                <a href="ilan_yonetim.php?action=pasif&id=<?= $ilan['id'] ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Pasif Yap</a>
                                                <a href="ilan_yonetim.php?action=silindi&id=<?= $ilan['id'] ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Silindi İşaretle</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center text-gray-500">Hiç ilan bulunamadı.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
        // İlan Silme Onayı
        function confirmDelete(ilanId) {
            if (confirm("Bu ilanı ve tüm favori kayıtlarını silmek istediğinizden emin misiniz? Bu işlem geri alınamaz!")) {
                window.location.href = 'ilan_yonetim.php?action=delete&id=' + ilanId;
            }
        }

        // Tailwind Dropdown İşlevselliği
        document.addEventListener('DOMContentLoaded', function() {
            const dropdownButtons = document.querySelectorAll('[id^="dropdownMenuButton"]');
            dropdownButtons.forEach(button => {
                button.addEventListener('click', function(event) {
                    const dropdownMenu = this.nextElementSibling;
                    dropdownMenu.classList.toggle('hidden');
                    event.stopPropagation(); // Butona tıklayınca belgeye yayılmasını engelle
                });
            });

            // Belgenin herhangi bir yerine tıklanınca tüm dropdown'ları kapat
            document.addEventListener('click', function(event) {
                dropdownButtons.forEach(button => {
                    const dropdownMenu = button.nextElementSibling;
                    if (!dropdownMenu.classList.contains('hidden') && !button.contains(event.target)) {
                        dropdownMenu.classList.add('hidden');
                    }
                });
            });
        });
    </script>

<?php include("includes/admin_footer.php"); // Footer dosyasını dahil et ?>