<?php
// admin/admin_panel.php - Admin Paneli Anasayfası
session_start();
include("../includes/auth.php"); // Yetkilendirme kontrolünü dahil et (ana dizinden)
include("../includes/db.php"); // Veritabanı bağlantısını dahil et (ana dizinden)

// admin_header.php dosyasını dahil et
// Bu dosya artık Tailwind CSS'i ve temel HTML yapısını içeriyor.
include("includes/admin_header.php"); 

// Tüm ilanları çek
$sorgu = $conn->query("SELECT * FROM ilanlar ORDER BY id DESC");
?>

<div class="flex justify-between items-center pb-4 mb-6 border-b-2 border-gray-200">
        <h1 class="text-3xl font-semibold text-gray-800 flex items-center">
            <i class="fas fa-paw mr-3 text-blue-600"></i> İlan Yönetimi
        </h1>
    </div>

    <?php if ($sorgu->num_rows > 0): ?>
        <div class="overflow-x-auto bg-white shadow-md rounded-lg p-6">
            <table class="min-w-full leading-normal">
                <thead>
                    <tr>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            ID
                        </th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Başlık
                        </th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Açıklama
                        </th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Durum
                        </th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Tarih
                        </th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            İşlemler
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($ilan = $sorgu->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <?= htmlspecialchars($ilan['id']) ?>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <?= htmlspecialchars($ilan['baslik']) ?>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <?= nl2br(htmlspecialchars(mb_substr($ilan['aciklama'], 0, 100, 'UTF-8'))) ?><?php if (mb_strlen($ilan['aciklama'], 'UTF-8') > 100) echo '...'; ?>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <?= htmlspecialchars($ilan['durum']) ?>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <?= htmlspecialchars($ilan['tarih']) ?>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <a href="../ilan_detay.php?id=<?= $ilan['id'] ?>" 
                                   class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 mr-2" 
                                   target="_blank">
                                    <i class="fas fa-eye mr-1"></i> Görüntüle
                                </a>
                                <a href="../ilan_duzenle.php?id=<?= $ilan['id'] ?>" 
                                   class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-yellow-500 hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-400 mr-2">
                                    <i class="fas fa-edit mr-1"></i> Düzenle
                                </a>
                                <a href="../ilan_sil.php?id=<?= $ilan['id'] ?>"
                                   class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                   onclick="return confirm('Bu ilanı silmek istediğinize emin misiniz?')">
                                    <i class="fas fa-trash-alt mr-1"></i> Sil
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded-md" role="alert">
            <p class="font-bold">Bilgi</p>
            <p>Henüz kayıtlı ilan yok.</p>
        </div>
    <?php endif; ?>

<?php
// admin_footer.php dosyasını dahil et
// Bu dosya genelde </body> ve </html> etiketlerini kapatır ve JS dosyalarını içerir.
include("includes/admin_footer.php"); 
?>