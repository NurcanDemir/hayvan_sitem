<?php
// admin/sahiplenme_talepleri.php

// 1. session_start() MUTLAKA DOSYANIN İLK SATIRI OLMALI VE SADECE BİR KEZ ÇAĞRILMALI.
// Bu satırdan önce boşluk, HTML veya BOM (Byte Order Mark) OLMAMALIDIR.
session_start();

// 2. TÜM include'lar ve PHP mantığı HTML çıktısından önce gelmelidir.
include("../includes/db.php");
include("../includes/auth.php"); // auth.php'nin içinde session_start() varsa, oradan kaldırılmalı.
                               // Ayrıca auth.php içindeki header() çağrılarından önce hiçbir çıktı olmadığından emin olun.

// Mesaj yönetimi (Bu kısım zaten doğru yerde)
$mesaj = "";
$mesaj_tur = "";
if (isset($_SESSION['mesaj'])) {
    $mesaj = $_SESSION['mesaj'];
    $mesaj_tur = $_SESSION['mesaj_tipi'];
    unset($_SESSION['mesaj']);
    unset($_SESSION['mesaj_tipi']);
}

// Talepleri veritabanından çekme işlemleri (Bu kısım zaten doğru yerde)
$sql = "SELECT
            si.id AS talep_id,
            si.ilan_id,
            si.talep_eden_kullanici_id,
            si.talep_eden_ad_soyad,
            si.talep_eden_email,
            si.talep_eden_telefon,
            si.mesaj AS talep_mesaj,
            si.talep_tarihi,
            si.durum,
            si.admin_notlari,
            i.baslik AS ilan_baslik,
            i.foto AS ilan_foto
        FROM sahiplenme_istekleri si
        LEFT JOIN ilanlar i ON si.ilan_id = i.id
        ORDER BY si.talep_tarihi DESC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$talepler = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// 3. admin_header.php dahil edildikten sonra HTML çıktısı başlar.
// Bu yüzden admin_header.php include'u tüm PHP logic'inden sonra gelmelidir.
include("includes/admin_header.php"); // Doğru yolu kontrol edin

// Buradan sonrası HTML çıktısıdır ve admin_header.php'nin devamıdır.
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">🐾 Sahiplenme Talepleri Yönetimi</h1>

    <?php if (!empty($mesaj)): ?>
        <div class="bg-<?= $mesaj_tur === 'success' ? 'green' : 'red' ?>-100 border border-<?= $mesaj_tur === 'success' ? 'green' : 'red' ?>-400 text-<?= $mesaj_tur === 'success' ? 'green' : 'red' ?>-700 px-4 py-3 rounded relative mb-4" role="alert">
            <?= htmlspecialchars($mesaj) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($talepler)): ?>
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full leading-normal">
                    <thead>
                        <tr>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Talep ID</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">İlan Başlığı</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Talep Eden</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">İletişim</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Mesaj</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Tarih</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Durum</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Yönetici Notu</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($talepler as $talep): ?>
                        <tr>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm"><?= htmlspecialchars($talep['talep_id']) ?></td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <a href="../ilan_detay.php?id=<?= $talep['ilan_id'] ?>" class="text-blue-600 hover:text-blue-900" target="_blank">
                                    <?= htmlspecialchars($talep['ilan_baslik']) ?>
                                </a>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm"><?= htmlspecialchars($talep['talep_eden_ad_soyad']) ?></td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <p><?= htmlspecialchars($talep['talep_eden_email']) ?></p>
                                <p><?= htmlspecialchars($talep['talep_eden_telefon']) ?></p>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm max-w-xs overflow-hidden truncate" title="<?= htmlspecialchars($talep['talep_mesaj']) ?>">
                                <?= nl2br(htmlspecialchars(mb_substr($talep['talep_mesaj'], 0, 100, 'UTF-8'))) ?><?php if (mb_strlen($talep['talep_mesaj'], 'UTF-8') > 100) echo '...'; ?>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm"><?= date('d.m.Y H:i', strtotime($talep['talep_tarihi'])) ?></td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <span class="relative inline-block px-3 py-1 font-semibold leading-tight
                                    <?php
                                        switch($talep['durum']) {
                                            case 'beklemede': echo 'text-yellow-900 bg-yellow-200'; break;
                                            case 'onaylandı': echo 'text-green-900 bg-green-200'; break;
                                            case 'reddedildi': echo 'text-red-900 bg-red-200'; break;
                                            case 'tamamlandı': echo 'text-blue-900 bg-blue-200'; break;
                                            default: echo 'text-gray-900 bg-gray-200'; break;
                                        }
                                    ?> rounded-full">
                                    <?= htmlspecialchars(ucfirst($talep['durum'])) ?>
                                </span>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm max-w-xs overflow-hidden truncate" title="<?= htmlspecialchars($talep['admin_notlari']) ?>">
                                <?= nl2br(htmlspecialchars(mb_substr($talep['admin_notlari'], 0, 50, 'UTF-8'))) ?><?php if (mb_strlen($talep['admin_notlari'], 'UTF-8') > 50) echo '...'; ?>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-right">
                                <div class="relative">
                                    <button class="text-gray-600 hover:text-gray-900 focus:outline-none focus:text-gray-900" id="dropdownMenuButton_<?= $talep['talep_id'] ?>" data-dropdown-toggle="dropdown_<?= $talep['talep_id'] ?>" type="button">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div id="dropdown_<?= $talep['talep_id'] ?>" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10">
                                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 action-btn" data-action="onayla" data-id="<?= $talep['talep_id'] ?>">Onayla</a>
                                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 action-btn" data-action="reddet" data-id="<?= $talep['talep_id'] ?>">Reddet</a>
                                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 action-btn" data-action="tamamla" data-id="<?= $talep['talep_id'] ?>">Tamamla</a>
                                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 action-btn" data-action="not_ekle" data-id="<?= $talep['talep_id'] ?>">Not Ekle</a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <p class="text-gray-600">Henüz sahiplenme talebi bulunmamaktadır.</p>
    <?php endif; ?>
</div>

</main>

<?php include("includes/admin_footer.php"); ?>