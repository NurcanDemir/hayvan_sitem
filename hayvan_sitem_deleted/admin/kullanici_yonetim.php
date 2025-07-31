<?php
// admin/kullanici_yonetim.php
session_start();
include("../includes/auth.php"); // Yetkilendirme kontrolünü dahil et
include("../includes/db.php"); // Veritabanı bağlantısını dahil et

$mesaj = ""; // İşlem mesajları için

// Kullanıcı Pasifleştirme/Aktifleştirme İşlemi
if (isset($_GET['action']) && ($_GET['action'] == 'pasif' || $_GET['action'] == 'aktif') && isset($_GET['id'])) {
    $kullanici_id = intval($_GET['id']);
    $yeni_durum = ($_GET['action'] == 'pasif') ? 'pasif' : 'aktif';

    // Kendi hesabını pasifleştirmesini engelle
    if ($kullanici_id == $_SESSION['admin_id']) {
        $mesaj = "<div class='bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4' role='alert'>Kendi hesabınızı pasifleştiremez/aktifleştiremezsiniz.</div>";
    } else {
        $stmt_update_durum = $conn->prepare("UPDATE kullanicilar SET durum = ? WHERE id = ?");
        $stmt_update_durum->bind_param("si", $yeni_durum, $kullanici_id);
        if ($stmt_update_durum->execute()) {
            $mesaj = "<div class='bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4' role='alert'>Kullanıcı durumu başarıyla güncellendi: " . htmlspecialchars($yeni_durum) . "</div>";
        } else {
            $mesaj = "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4' role='alert'>Kullanıcı durumu güncellenirken hata oluştu: " . $stmt_update_durum->error . "</div>";
        }
        $stmt_update_durum->close();
    }
}

// Kullanıcı Silme İşlemi
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $kullanici_id = intval($_GET['id']);

    // Kendi hesabını silmesini engelle
    if ($kullanici_id == $_SESSION['admin_id']) {
        $mesaj = "<div class='bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4' role='alert'>Kendi hesabınızı silemezsiniz.</div>";
    } else {
        // Kullanıcıya ait tüm ilanları, sahiplenme taleplerini ve favorileri de silmek gerekebilir
        // Bu kısım veritabanı ilişkilerinize (FOREIGN KEY ON DELETE CASCADE) bağlıdır.
        // Eğer cascade yoksa, önce ilgili verileri manuel silmelisin.
        // Örnek:
        // $conn->query("DELETE FROM favoriler WHERE kullanici_id = $kullanici_id");
        // $conn->query("DELETE FROM ilanlar WHERE kullanici_id = $kullanici_id");
        // $conn->query("DELETE FROM sahiplenme_istekleri WHERE talep_eden_kullanici_id = $kullanici_id");

        $stmt_delete_user = $conn->prepare("DELETE FROM kullanicilar WHERE id = ?");
        $stmt_delete_user->bind_param("i", $kullanici_id);
        if ($stmt_delete_user->execute()) {
            $mesaj = "<div class='bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4' role='alert'>Kullanıcı başarıyla silindi.</div>";
        } else {
            $mesaj = "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4' role='alert'>Kullanıcı silinirken hata oluştu: " . $stmt_delete_user->error . "</div>";
        }
        $stmt_delete_user->close();
    }
}

// Kullanıcıları Çekme (Filtreleme ve Arama ile)
$sql = "SELECT id, kullanici_adi, eposta, telefon, kullanici_tipi, durum, kayit_tarihi FROM kullanicilar WHERE 1=1";
$params = [];
$types = "";

// Arama Filtresi
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = "%" . $_GET['search'] . "%";
    $sql .= " AND (kullanici_adi LIKE ? OR eposta LIKE ? OR telefon LIKE ?)";
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
    $types .= "sss";
}

// Durum Filtresi
if (isset($_GET['durum']) && $_GET['durum'] != '') {
    $durum_filter = $_GET['durum'];
    $sql .= " AND durum = ?";
    $params[] = $durum_filter;
    $types .= "s";
}

// Kullanıcı Tipi Filtresi
if (isset($_GET['tip']) && $_GET['tip'] != '') {
    $tip_filter = $_GET['tip'];
    $sql .= " AND kullanici_tipi = ?";
    $params[] = $tip_filter;
    $types .= "s";
}

$sql .= " ORDER BY id DESC";

$stmt_kullanicilar = $conn->prepare($sql);

if (!empty($params)) {
    $bind_params = array_merge([$types], $params);
    $refs = [];
    foreach($bind_params as $key => $value) {
        $refs[$key] = &$bind_params[$key];
    }
    call_user_func_array([$stmt_kullanicilar, 'bind_param'], $refs);
}

$stmt_kullanicilar->execute();
$kullanici_sonuc = $stmt_kullanicilar->get_result();

?>

<?php include("includes/admin_header.php"); // Admin paneline özel başlık ve menüyü dahil et ?>

    <div class="flex justify-between items-center pt-3 pb-2 mb-3 border-b border-gray-200">
        <h1 class="text-2xl font-semibold">Kullanıcı Yönetimi</h1>
    </div>

    <?= $mesaj ?>

    <div class="bg-white shadow-md rounded-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            Kullanıcı Filtreleme ve Arama
        </div>
        <div class="p-6">
            <form action="kullanici_yonetim.php" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div class="col-span-1">
                    <label for="search" class="block text-sm font-medium text-gray-700">Kullanıcı Adı, E-posta veya Telefon Ara</label>
                    <input type="text" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" id="search" name="search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                </div>
                <div class="col-span-1">
                    <label for="durum" class="block text-sm font-medium text-gray-700">Duruma Göre Filtrele</label>
                    <select class="mt-1 block w-full px-3 py-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" id="durum" name="durum">
                        <option value="">Tüm Durumlar</option>
                        <option value="aktif" <?= (($_GET['durum'] ?? '') == 'aktif') ? 'selected' : '' ?>>Aktif</option>
                        <option value="pasif" <?= (($_GET['durum'] ?? '') == 'pasif') ? 'selected' : '' ?>>Pasif</option>
                    </select>
                </div>
                <div class="col-span-1">
                    <label for="tip" class="block text-sm font-medium text-gray-700">Kullanıcı Tipine Göre Filtrele</label>
                    <select class="mt-1 block w-full px-3 py-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" id="tip" name="tip">
                        <option value="">Tüm Tipler</option>
                        <option value="admin" <?= (($_GET['tip'] ?? '') == 'admin') ? 'selected' : '' ?>>Admin</option>
                        <option value="standart" <?= (($_GET['tip'] ?? '') == 'standart') ? 'selected' : '' ?>>Standart</option>
                    </select>
                </div>
                <div class="col-span-1 flex space-x-2">
                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Filtrele</button>
                    <a href="kullanici_yonetim.php" class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Temizle</a>
                </div>
            </form>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 border border-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kullanıcı Adı</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">E-posta</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Telefon</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kullanıcı Tipi</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kayıt Tarihi</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İşlemler</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if ($kullanici_sonuc->num_rows > 0): ?>
                    <?php while ($kullanici = $kullanici_sonuc->fetch_assoc()): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($kullanici['id']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($kullanici['kullanici_adi']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($kullanici['eposta']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($kullanici['telefon']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    <?php
                                        if ($kullanici['kullanici_tipi'] == 'admin') echo 'bg-red-100 text-red-800';
                                        elseif ($kullanici['kullanici_tipi'] == 'standart') echo 'bg-blue-100 text-blue-800';
                                        else echo 'bg-gray-100 text-gray-800';
                                    ?>">
                                    <?= htmlspecialchars(ucfirst($kullanici['kullanici_tipi'])) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    <?php
                                        if ($kullanici['durum'] == 'aktif') echo 'bg-green-100 text-green-800';
                                        elseif ($kullanici['durum'] == 'pasif') echo 'bg-yellow-100 text-yellow-800';
                                        else echo 'bg-gray-100 text-gray-800';
                                    ?>">
                                    <?= htmlspecialchars(ucfirst($kullanici['durum'])) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($kullanici['kayit_tarihi']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <a href="kullanici_duzenle.php?id=<?= $kullanici['id'] ?>" class="text-indigo-600 hover:text-indigo-900 px-2 py-1 rounded-md border border-indigo-600 hover:border-indigo-900 transition-colors duration-200" title="Kullanıcıyı Düzenle"><i class="fas fa-edit"></i></a>

                                    <?php if ($kullanici['id'] != $_SESSION['admin_id']): // Kendi hesabını silmesini engelle ?>
                                        <button type="button" class="text-red-600 hover:text-red-900 px-2 py-1 rounded-md border border-red-600 hover:border-red-900 transition-colors duration-200" onclick="confirmDelete(<?= $kullanici['id'] ?>)" title="Kullanıcıyı Sil"><i class="fas fa-trash-alt"></i></button>

                                        <div class="relative inline-block text-left">
                                            <button type="button" class="text-gray-600 hover:text-gray-900 px-2 py-1 rounded-md border border-gray-600 hover:border-gray-900 transition-colors duration-200 group" title="Durum Güncelle">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                            <div class="absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 hidden group-hover:block" role="menu" aria-orientation="vertical" aria-labelledby="menu-button">
                                                <div class="py-1" role="none">
                                                    <a href="kullanici_yonetim.php?action=aktif&id=<?= $kullanici['id'] ?>" class="text-gray-700 block px-4 py-2 text-sm hover:bg-gray-100" role="menuitem">Aktif Yap</a>
                                                    <a href="kullanici_yonetim.php?action=pasif&id=<?= $kullanici['id'] ?>" class="text-gray-700 block px-4 py-2 text-sm hover:bg-gray-100" role="menuitem">Pasif Yap</a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-gray-500 ml-2"><i class="fas fa-lock"></i> Kendi Hesabın</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">Hiç kullanıcı bulunamadı.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
        function confirmDelete(kullaniciId) {
            if (confirm("Bu kullanıcıyı silmek istediğinizden emin misiniz? Bu işlem geri alınamaz! (İlgili ilanları ve talepleri de silinmelidir.)")) {
                window.location.href = 'kullanici_yonetim.php?action=delete&id=' + kullaniciId;
            }
        }
    </script>

<?php include("includes/admin_footer.php"); // Footer dosyasını dahil et ?>