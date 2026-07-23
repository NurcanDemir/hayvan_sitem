<?php
$page_title = "Hayvan Etkinlikleri - Sıcak Patizi";
include 'includes/header.php';
include 'includes/db.php';

// Sayfalama ayarları
$sayfa = isset($_GET['sayfa']) ? (int)$_GET['sayfa'] : 1;
$limit = 9;
$offset = ($sayfa - 1) * $limit;

// Filtreleme
$kategori_filter = isset($_GET['kategori']) ? trim($_GET['kategori']) : '';
$il_filter = isset($_GET['il']) ? (int)$_GET['il'] : 0;
$zaman_filter = isset($_GET['zaman']) ? trim($_GET['zaman']) : 'gelecek';

// WHERE koşulları
$where_conditions = ["(e.aktif = 1 OR e.durum = 'yayin')"];
$params = [];
$types = "";

// Kategori filtresi
if (!empty($kategori_filter)) {
    $where_conditions[] = "e.kategori = ?";
    $params[] = $kategori_filter;
    $types .= "s";
}

// İl filtresi
if ($il_filter > 0) {
    $where_conditions[] = "e.il_id = ?";
    $params[] = $il_filter;
    $types .= "i";
}

// Zaman filtresi
switch ($zaman_filter) {
    case 'bugun':
        $where_conditions[] = "DATE(e.etkinlik_tarihi) = CURDATE()";
        break;
    case 'bu_hafta':
        $where_conditions[] = "YEARWEEK(e.etkinlik_tarihi) = YEARWEEK(CURDATE())";
        break;
    case 'gecmis':
        $where_conditions[] = "e.etkinlik_tarihi < CURDATE()";
        break;
    case 'gelecek':
    default:
        $where_conditions[] = "e.etkinlik_tarihi >= CURDATE()";
        break;
}

$where_clause = implode(' AND ', $where_conditions);

// Toplam etkinlik sayısı
$count_sql = "SELECT COUNT(*) as total FROM hayvan_etkinlikleri e WHERE $where_clause";
$count_stmt = $conn->prepare($count_sql);
if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$total_count = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_count / $limit);

// Etkinlikleri getir
$sql = "SELECT e.*, il.ad as il_ad, ilc.ad as ilce_ad,
        (SELECT COUNT(*) FROM event_subscriptions sub WHERE sub.event_id = e.id AND sub.is_active = 1) AS subscriber_count
        FROM hayvan_etkinlikleri e 
        LEFT JOIN il ON e.il_id = il.id 
        LEFT JOIN ilce ilc ON e.ilce_id = ilc.id 
        WHERE $where_clause 
        ORDER BY e.etkinlik_tarihi ASC, e.etkinlik_saati ASC 
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
$all_params = array_merge($params, [$limit, $offset]);
$all_types = $types . "ii";
if (!empty($all_params)) {
    $stmt->bind_param($all_types, ...$all_params);
}
$stmt->execute();
$result = $stmt->get_result();

// İller listesi
$iller_sql = "SELECT id, ad FROM il ORDER BY ad ASC";
$iller_result = $conn->query($iller_sql);
?>

<style>
    /* Category colors with pink theme */
    .cat-sahiplendirme { 
        background: linear-gradient(135deg, #fdf2f8 0%, #fce7f3 100%);
        color: var(--primary);
        border-color: var(--primary-lighter);
    }
    
    .cat-saglik { 
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        color: #059669;
        border-color: #6ee7b7;
    }
    
    .cat-egitim { 
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        color: #1d4ed8;
        border-color: #93c5fd;
    }
    
    .cat-bagis { 
        background: linear-gradient(135deg, #fff7ed 0%, #fed7aa 100%);
        color: #ea580c;
        border-color: #fdba74;
    }
    
    .cat-diger { 
        background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
        color: #374151;
        border-color: #d1d5db;
    }
</style>

<!-- Ana İçerik -->
<main class="max-w-7xl mx-auto px-6 py-8">
    <!-- Sayfa Başlığı -->
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-800 mb-4">
            <i class="fas fa-calendar-alt mr-3 text-primary"></i>
            Hayvan Etkinlikleri
        </h1>
        <p class="text-xl text-gray-600">Dostlarımız için düzenlenen etkinliklere katılın</p>
    </div>

    <!-- Filtreler -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-8 card-hover">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Kategori -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-tag mr-1 text-primary"></i>Kategori
                </label>
                <select name="kategori" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary focus:border-primary transition duration-300">
                    <option value="">Tüm Kategoriler</option>
                    <option value="sahiplendirme" <?= $kategori_filter == 'sahiplendirme' ? 'selected' : '' ?>>💕 Sahiplendirme</option>
                    <option value="saglik" <?= $kategori_filter == 'saglik' ? 'selected' : '' ?>>🏥 Sağlık</option>
                    <option value="egitim" <?= $kategori_filter == 'egitim' ? 'selected' : '' ?>>📚 Eğitim</option>
                    <option value="bagis" <?= $kategori_filter == 'bagis' ? 'selected' : '' ?>>🎁 Bağış</option>
                    <option value="diger" <?= $kategori_filter == 'diger' ? 'selected' : '' ?>>🌟 Diğer</option>
                </select>
            </div>

            <!-- İl -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-map-marker-alt mr-1 text-primary"></i>İl
                </label>
                <select name="il" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary focus:border-primary transition duration-300">
                    <option value="0">Tüm İller</option>
                    <?php if ($iller_result && $iller_result->num_rows > 0): ?>
                        <?php while ($il = $iller_result->fetch_assoc()): ?>
                            <option value="<?= $il['id'] ?>" <?= $il_filter == $il['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($il['ad']) ?>
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Zaman -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-clock mr-1 text-primary"></i>Zaman
                </label>
                <select name="zaman" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary focus:border-primary transition duration-300">
                    <option value="gelecek" <?= $zaman_filter == 'gelecek' ? 'selected' : '' ?>>🔮 Yaklaşan Etkinlikler</option>
                    <option value="bugun" <?= $zaman_filter == 'bugun' ? 'selected' : '' ?>>📅 Bugün</option>
                    <option value="bu_hafta" <?= $zaman_filter == 'bu_hafta' ? 'selected' : '' ?>>📆 Bu Hafta</option>
                    <option value="gecmis" <?= $zaman_filter == 'gecmis' ? 'selected' : '' ?>>⏪ Geçmiş Etkinlikler</option>
                </select>
            </div>

            <!-- Filtrele & Sıfırla Butonları -->
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 btn-gradient text-white px-4 py-2 rounded-md font-semibold text-sm">
                    <i class="fas fa-filter mr-1"></i>Filtrele
                </button>
                <a href="etkinlikler.php" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-md font-semibold text-sm transition text-center flex items-center justify-center" title="Filtreleri Sıfırla">
                    <i class="fas fa-redo mr-1"></i>Sıfırla
                </a>
            </div>
        </form>

        <!-- Sonuç Bilgisi -->
        <div class="mt-4 pt-4 border-t border-gray-200">
            <div class="flex items-center justify-between text-sm text-gray-600">
                <span>
                    <i class="fas fa-info-circle mr-1 text-primary"></i>
                    Toplam <strong class="text-primary"><?= $total_count ?></strong> etkinlik bulundu
                </span>
                <?php if (!empty($kategori_filter) || $il_filter > 0 || $zaman_filter != 'gelecek'): ?>
                    <a href="etkinlikler.php" class="text-primary hover:text-primary-light transition duration-300">
                        <i class="fas fa-times mr-1"></i>Filtreleri Temizle
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Etkinlikler Listesi -->
    <?php if ($result && $result->num_rows > 0): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
            <?php while ($etkinlik = $result->fetch_assoc()): ?>
                <?php
                $eventDate = new DateTime($etkinlik['etkinlik_tarihi']);
                $today = new DateTime();
                $isToday = $eventDate->format('Y-m-d') === $today->format('Y-m-d');
                $isPast = $eventDate < $today;
                
                // Kategori renkleri ve sınıfları
                $categoryClasses = [
                    'sahiplendirme' => 'cat-sahiplendirme',
                    'saglik' => 'cat-saglik',
                    'egitim' => 'cat-egitim',
                    'bagis' => 'cat-bagis',
                    'diger' => 'cat-diger'
                ];
                
                $categoryClass = $categoryClasses[$etkinlik['kategori']] ?? $categoryClasses['diger'];
                
                $categoryNames = [
                    'sahiplendirme' => '💕 Sahiplendirme',
                    'saglik' => '🏥 Sağlık',
                    'egitim' => '📚 Eğitim',
                    'bagis' => '🎁 Bağış',
                    'diger' => '🌟 Diğer'
                ];
                ?>
                
                <div class="bg-white rounded-xl shadow-lg overflow-hidden card-hover border-t-4 border-primary <?= $isPast ? 'opacity-75' : '' ?>">
                    <div class="p-6">
                        <!-- Kategori ve Tarih Etiketleri -->
                        <div class="flex items-center gap-2 mb-4 flex-wrap">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold border <?= $categoryClass ?>">
                                <?= $categoryNames[$etkinlik['kategori']] ?? '🌟 Diğer' ?>
                            </span>
                            <?php if ($isToday): ?>
                                <span class="px-2 py-1 bg-red-100 text-red-700 text-xs font-bold rounded-full border border-red-200">
                                    🔥 BUGÜN
                                </span>
                            <?php elseif ($isPast): ?>
                                <span class="px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded-full border border-gray-200">
                                    ⏰ GEÇMİŞ
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Başlık -->
                        <h3 class="text-xl font-bold text-gray-800 mb-3 line-clamp-2">
                            <?= htmlspecialchars($etkinlik['baslik']) ?>
                        </h3>

                        <!-- Açıklama -->
                        <p class="text-gray-600 text-sm mb-4 line-clamp-3">
                            <?= htmlspecialchars($etkinlik['aciklama']) ?>
                        </p>

                        <!-- Tarih ve Saat -->
                        <div class="space-y-2 mb-4 text-sm text-gray-600">
                            <div class="flex items-center">
                                <i class="fas fa-calendar-alt mr-2 text-primary w-4"></i>
                                <span class="font-medium"><?= $eventDate->format('d.m.Y l') ?></span>
                            </div>
                            <?php if ($etkinlik['etkinlik_saati']): ?>
                                <div class="flex items-center">
                                    <i class="fas fa-clock mr-2 text-primary w-4"></i>
                                    <span><?= substr($etkinlik['etkinlik_saati'], 0, 5) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Konum -->
                        <?php if ($etkinlik['adres']): ?>
                            <div class="mb-4">
                                <div class="flex items-start text-sm text-gray-600">
                                    <i class="fas fa-map-marker-alt mt-1 mr-2 text-primary"></i>
                                    <span class="line-clamp-2"><?= htmlspecialchars($etkinlik['adres']) ?></span>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- İl/İlçe -->
                        <?php if ($etkinlik['il_ad'] || $etkinlik['ilce_ad']): ?>
                            <div class="mb-4">
                                <span class="bg-primary-lightest text-primary px-3 py-1 rounded-full text-xs font-semibold border border-primary-lighter">
                                    📍 <?= htmlspecialchars($etkinlik['il_ad']) ?>
                                    <?php if ($etkinlik['ilce_ad']): ?>
                                        / <?= htmlspecialchars($etkinlik['ilce_ad']) ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                        <?php endif; ?>

                        <!-- Katılımcı / Abone Sayısı -->
                        <div class="mb-4">
                            <span class="inline-flex items-center text-xs font-semibold bg-pink-50 text-primary px-3 py-1 rounded-full border border-pink-100">
                                <i class="fas fa-users mr-1.5"></i>
                                <span id="sub-count-<?= $etkinlik['id'] ?>"><?= (int)($etkinlik['subscriber_count'] ?? 0) ?></span> kişi katılıyor
                            </span>
                        </div>

                        <!-- Eylem Butonları -->
                        <div class="flex gap-2">
                            <button onclick="shareEvent('<?= htmlspecialchars(addslashes($etkinlik['baslik'])) ?>', '<?= $etkinlik['etkinlik_tarihi'] ?>', '<?= htmlspecialchars(addslashes($etkinlik['adres'] ?? '')) ?>')"
                                    class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs px-2.5 py-2 rounded-md transition duration-300" title="Paylaş">
                                <i class="fas fa-share-alt"></i>
                            </button>
                            <button onclick="openSubscribeModal(<?= $etkinlik['id'] ?>, '<?= htmlspecialchars(addslashes($etkinlik['baslik'])) ?>')" 
                                    class="flex-1 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 text-xs px-3 py-2 rounded-md font-semibold transition text-center flex items-center justify-center">
                                <i class="fas fa-bell mr-1"></i>Hatırlat
                            </button>
                            <a href="#etkinlik-detay" onclick="Swal.fire({title: '<?= htmlspecialchars(addslashes($etkinlik['baslik'])) ?>', html: '<b>Tarih:</b> <?= date('d.m.Y', strtotime($etkinlik['etkinlik_tarihi'])) ?><br><b>Konum:</b> <?= htmlspecialchars(addslashes($etkinlik['adres'] ?? 'Belirtilmedi')) ?><br><br><p><?= htmlspecialchars(addslashes($etkinlik['aciklama'])) ?></p>', confirmButtonColor: '#3A868F'})"
                               class="flex-1 btn-gradient text-white text-xs px-3 py-2 rounded-md text-center font-semibold flex items-center justify-center">
                                Detaylar <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- Sayfalama -->
        <?php if ($total_pages > 1): ?>
            <div class="flex justify-center">
                <nav class="flex items-center space-x-2">
                    <!-- Önceki Sayfa -->
                    <?php if ($sayfa > 1): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['sayfa' => $sayfa - 1])) ?>" 
                           class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition duration-300">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php endif; ?>

                    <!-- Sayfa Numaraları -->
                    <?php 
                    $start_page = max(1, $sayfa - 2);
                    $end_page = min($total_pages, $sayfa + 2);
                    
                    for ($i = $start_page; $i <= $end_page; $i++): 
                    ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['sayfa' => $i])) ?>" 
                           class="px-3 py-2 text-sm font-medium <?= $i == $sayfa ? 'text-white bg-primary border-primary' : 'text-gray-500 bg-white border-gray-300 hover:bg-gray-50' ?> border rounded-md transition duration-300">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>

                    <!-- Sonraki Sayfa -->
                    <?php if ($sayfa < $total_pages): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['sayfa' => $sayfa + 1])) ?>" 
                           class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition duration-300">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </nav>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <!-- Sonuç Bulunamadı -->
        <div class="text-center py-16">
            <div class="text-6xl mb-6">📅</div>
            <h3 class="text-2xl font-semibold text-gray-600 mb-4">Etkinlik Bulunamadı</h3>
            <p class="text-gray-500 mb-6">
                <?php if (!empty($kategori_filter) || $il_filter > 0 || $zaman_filter != 'gelecek'): ?>
                    Filtre kriterlerinize uygun etkinlik bulunamadı.
                <?php else: ?>
                    Henüz kayıtlı etkinlik bulunmuyor.
                <?php endif; ?>
            </p>
            <?php if (!empty($kategori_filter) || $il_filter > 0 || $zaman_filter != 'gelecek'): ?>
                <a href="etkinlikler.php" class="btn-gradient text-white px-6 py-3 rounded-md font-semibold">
                    <i class="fas fa-times mr-2"></i>Filtreleri Temizle
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</main>

<!-- Footer -->
<footer class="bg-gray-800 text-white py-12 mt-16">
    <div class="max-w-7xl mx-auto px-6">
        <div class="text-center">
            <div class="text-3xl mb-4">🐾</div>
            <h3 class="text-2xl font-bold mb-4 text-primary-lighter">Sıcak Patizi</h3>
            <p class="text-gray-400">Onlar İçin Yuva, Senin İçin Dostluk.</p>
        </div>
    </div>
</footer>

<!-- JavaScript -->
<script>
    // Etkinlik paylaş
    function shareEvent(title, date, address) {
        if (navigator.share) {
            navigator.share({
                title: title,
                text: `${title} - ${new Date(date).toLocaleDateString('tr-TR')}${address ? ` - ${address}` : ''}`,
                url: window.location.href
            });
        } else {
            const text = `${title} - ${new Date(date).toLocaleDateString('tr-TR')}${address ? ` - ${address}` : ''}\n${window.location.href}`;
            navigator.clipboard.writeText(text).then(() => {
                showToast('📋 Etkinlik bilgisi panoya kopyalandı!', 'success');
            }).catch(() => {
                showToast('❌ Paylaşılamadı', 'error');
            });
        }
    }

    // Toast bildirim fonksiyonu
    function showToast(message, type) {
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg transform transition-all duration-300 ${
            type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
        }`;
        toast.innerHTML = message;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.transform = 'translateX(0)';
        }, 100);
        
        setTimeout(() => {
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (document.body.contains(toast)) {
                    document.body.removeChild(toast);
                }
            }, 300);
        }, 3000);
    }

    // Form otomatik submit
    document.querySelectorAll('select').forEach(select => {
        select.addEventListener('change', function() {
            this.form.submit();
        });
    });

    // Sayfa yüklenme animasyonu
    document.addEventListener('DOMContentLoaded', function() {
        const cards = document.querySelectorAll('.card-hover');
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            setTimeout(() => {
                card.style.transition = 'all 0.5s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });
    });
</script>

<!-- Event Subscribe Modal -->
<div id="subscribeModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center hidden transition-opacity duration-300">
    <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4 shadow-2xl border border-pink-100 transform transition-transform duration-300 relative">
        <button onclick="closeSubscribeModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-xl focus:outline-none">
            <i class="fas fa-times"></i>
        </button>
        <div class="text-center mb-6">
            <div class="w-14 h-14 bg-pink-100 rounded-full flex items-center justify-center mx-auto mb-3 text-primary text-2xl">
                <i class="fas fa-bell"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-1">Etkinlik Hatırlatıcısı</h3>
            <p id="modalEventTitle" class="text-sm font-semibold text-primary mb-2 line-clamp-1"></p>
            <p class="text-xs text-gray-500">E-posta adresinizi girin, etkinlik yaklaşınca size haber verelim!</p>
        </div>
        <form id="subscribeForm" onsubmit="handleSubscribeSubmit(event)" class="space-y-4">
            <input type="hidden" id="modalEventId" name="event_id">
            <div>
                <label for="subscribeEmail" class="block text-xs font-semibold text-gray-700 mb-1">E-posta Adresiniz</label>
                <input type="email" id="subscribeEmail" name="email" required placeholder="ornek@email.com" 
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary text-sm">
            </div>
            <button type="submit" id="subscribeBtn" class="w-full btn-gradient text-white py-2.5 rounded-xl font-semibold text-sm shadow-md transition flex items-center justify-center">
                <i class="fas fa-paper-plane mr-2"></i>Hatırlatma Oluştur
            </button>
        </form>
    </div>
</div>

<script>
function openSubscribeModal(eventId, eventTitle) {
    document.getElementById('modalEventId').value = eventId;
    document.getElementById('modalEventTitle').textContent = eventTitle;
    document.getElementById('subscribeModal').classList.remove('hidden');
}

function closeSubscribeModal() {
    document.getElementById('subscribeModal').classList.add('hidden');
    document.getElementById('subscribeForm').reset();
}

function handleSubscribeSubmit(e) {
    e.preventDefault();
    const eventId = document.getElementById('modalEventId').value;
    const email = document.getElementById('subscribeEmail').value;
    const btn = document.getElementById('subscribeBtn');

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Kaydediliyor...';

    const formData = new FormData();
    formData.append('event_id', eventId);
    formData.append('email', email);

    fetch('api/subscribe_event.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane mr-2"></i>Hatırlatma Oluştur';

        if (data.status === 'success') {
            closeSubscribeModal();
            Swal.fire({
                icon: 'success',
                title: 'Başarılı!',
                text: data.message,
                confirmButtonColor: '#3A868F'
            });
            const subCountEl = document.getElementById('sub-count-' + eventId);
            if (subCountEl && data.new_subscriber_count !== undefined) {
                subCountEl.textContent = data.new_subscriber_count;
            }
        } else if (data.status === 'warning') {
            Swal.fire({
                icon: 'info',
                title: 'Bilgi',
                text: data.message,
                confirmButtonColor: '#3A868F'
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Hata',
                text: data.message || 'Bir hata oluştu.',
                confirmButtonColor: '#ef4444'
            });
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane mr-2"></i>Hatırlatma Oluştur';
        Swal.fire({
            icon: 'error',
            title: 'Bağlantı Hatası',
            text: 'Sunucuya bağlanırken bir hata oluştu.'
        });
    });
}
</script>
</body>
</html>

<?php
if (isset($stmt)) $stmt->close();
if (isset($count_stmt)) $count_stmt->close();
$conn->close();
?>