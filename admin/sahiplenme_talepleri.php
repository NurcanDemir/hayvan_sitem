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
    $mesaj_tur = isset($_SESSION['mesaj_tipi']) ? $_SESSION['mesaj_tipi'] : 'info';
    unset($_SESSION['mesaj']);
    if (isset($_SESSION['mesaj_tipi'])) {
        unset($_SESSION['mesaj_tipi']);
    }
}

// Handle admin actions (delete requests)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_action'])) {
    $action = $_POST['admin_action'];
    $talep_id = (int)$_POST['talep_id'];
    
    if ($action === 'delete' && $talep_id > 0) {
        // First check if there's an active conversation for this request
        $check_conv_sql = "SELECT id FROM conversations WHERE sahiplenme_istek_id = ?";
        $check_stmt = $conn->prepare($check_conv_sql);
        $check_stmt->bind_param("i", $talep_id);
        $check_stmt->execute();
        $conv_result = $check_stmt->get_result();
        
        if ($conv_result->num_rows > 0) {
            // There's an active conversation - don't delete, just mark as admin-cancelled
            $update_sql = "UPDATE sahiplenme_istekleri SET durum = 'reddedildi', admin_notlari = CONCAT(IFNULL(admin_notlari, ''), '\n[Admin] Talep yönetici tarafından iptal edildi - ', NOW()) WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("i", $talep_id);
            
            if ($update_stmt->execute()) {
                $_SESSION['mesaj'] = "Talep iptal edildi. (Aktif konuşma olduğu için tamamen silinmedi)";
                $_SESSION['mesaj_tipi'] = "warning";
            } else {
                $_SESSION['mesaj'] = "Talep iptal edilirken hata oluştu.";
                $_SESSION['mesaj_tipi'] = "error";
            }
        } else {
            // No active conversation - safe to delete completely
            $delete_sql = "DELETE FROM sahiplenme_istekleri WHERE id = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bind_param("i", $talep_id);
            
            if ($delete_stmt->execute()) {
                $_SESSION['mesaj'] = "Talep başarıyla silindi.";
                $_SESSION['mesaj_tipi'] = "success";
            } else {
                $_SESSION['mesaj'] = "Talep silinirken hata oluştu.";
                $_SESSION['mesaj_tipi'] = "error";
            }
        }
        
        // Redirect to prevent form resubmission
        header("Location: sahiplenme_talepleri.php");
        exit();
    }
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

    <!-- Information Banner -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
        <div class="bg-blue-50 border border-blue-200 rounded p-4">
            <div class="flex items-center">
                <i class="fas fa-info-circle text-blue-600 mr-3"></i>
                <div>
                    <h3 class="text-lg font-semibold text-blue-800">📋 Monitoring Only - Kullanıcıdan Kullanıcıya Süreç</h3>
                    <p class="text-blue-700 mt-1">
                        Sahiplenme talepleri artık doğrudan ilan sahiplerine gidiyor. <strong>Admin onayı gerekmiyor!</strong><br>
                        Bu panel sadece süreçleri izlemek ve gerektiğinde müdahale etmek içindir.
                    </p>
                    <div class="mt-3 text-sm text-blue-600 bg-blue-100 p-2 rounded">
                        <strong>🔄 Yeni Süreç:</strong> 
                        <span class="inline-flex items-center">
                            Kullanıcı Talep → 
                            <i class="fas fa-arrow-right mx-2"></i>
                            İlan Sahibi Onayı → 
                            <i class="fas fa-arrow-right mx-2"></i>
                            Mesajlaşma → 
                            <i class="fas fa-arrow-right mx-2"></i>
                            Sahiplenme
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                                        <!-- Monitoring Actions -->
                                        <a href="admin_talep_bilgilendir.php?id=<?= $talep['talep_id'] ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <i class="fas fa-bell mr-2"></i>Bilgilendir
                                        </a>
                                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 action-btn" data-action="not_ekle" data-id="<?= $talep['talep_id'] ?>">
                                            <i class="fas fa-sticky-note mr-2"></i>Not Ekle
                                        </a>
                                        
                                        <!-- Separator -->
                                        <div class="border-t border-gray-200 my-1"></div>
                                        
                                        <!-- Administrative Actions -->
                                        <button type="button" onclick="confirmDelete(<?= $talep['talep_id'] ?>)" class="block w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-red-50">
                                            <i class="fas fa-trash mr-2"></i>Talebi Sil
                                        </button>
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

<!-- Hidden Delete Form -->
<form id="deleteForm" method="POST" style="display: none;">
    <input type="hidden" name="admin_action" value="delete">
    <input type="hidden" name="talep_id" id="deleteRequestId">
</form>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmDelete(talepId) {
    Swal.fire({
        title: 'Talebi Silmek İstediğinizden Emin Misiniz?',
        html: `
            <div class="text-left">
                <p class="mb-3">Bu işlem:</p>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li>• Eğer aktif konuşma varsa: Talebi iptal eder (tamamen silmez)</li>
                    <li>• Eğer konuşma yoksa: Talebi tamamen siler</li>
                    <li>• Bu işlem geri alınamaz</li>
                </ul>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Evet, Sil',
        cancelButtonText: 'İptal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('deleteRequestId').value = talepId;
            document.getElementById('deleteForm').submit();
        }
    });
}

// Dropdown functionality
document.addEventListener('DOMContentLoaded', function() {
    // Simple dropdown toggle
    document.querySelectorAll('[data-dropdown-toggle]').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('data-dropdown-toggle');
            const dropdown = document.getElementById(targetId);
            
            // Close all other dropdowns
            document.querySelectorAll('[id^="dropdown_"]').forEach(d => {
                if (d.id !== targetId) {
                    d.classList.add('hidden');
                }
            });
            
            // Toggle current dropdown
            dropdown.classList.toggle('hidden');
        });
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('[data-dropdown-toggle]') && !e.target.closest('[id^="dropdown_"]')) {
            document.querySelectorAll('[id^="dropdown_"]').forEach(d => {
                d.classList.add('hidden');
            });
        }
    });
});
</script>

</main>

<?php include("includes/admin_footer.php"); ?>