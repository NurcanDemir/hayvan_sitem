<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$page_title = "Barınaklar - Hayvan Dostları";
include("includes/db.php");
include("includes/header.php"); // Use the standardized header

// Filtreleme için parametreler
$where = "WHERE b.aktif = 1";
$params = [];
$types = "";

if (!empty($_GET['il_id'])) {
    $where .= " AND b.il_id = ?";
    $params[] = $_GET['il_id'];
    $types .= "i";
}

if (!empty($_GET['search'])) {
    $where .= " AND (b.ad LIKE ? OR b.adres LIKE ? OR il.ad LIKE ?)";
    $search_term = "%" . $_GET['search'] . "%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "sss";
}

// Sayfalama için
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

// Toplam kayıt sayısı
$count_sql = "SELECT COUNT(*) as total FROM hayvan_barinaklari b 
               LEFT JOIN il ON b.il_id = il.id 
               $where";
$count_stmt = $conn->prepare($count_sql);
if ($params) $count_stmt->bind_param($types, ...$params);
$count_stmt->execute();
$total_records = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);

// Barınakları getir
$sql = "SELECT b.*, il.ad as il_adi, ilce.ad as ilce_adi
        FROM hayvan_barinaklari b 
        LEFT JOIN il ON b.il_id = il.id
        LEFT JOIN ilce ON b.ilce_id = ilce.id
        $where
        ORDER BY b.ad ASC
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
if ($params) {
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";
    $stmt->bind_param($types, ...$params);
} else {
    $stmt->bind_param("ii", $limit, $offset);
}
$stmt->execute();
$result = $stmt->get_result();

// İller listesi
$iller = [];
$il_result = $conn->query("SELECT * FROM il ORDER BY ad ASC");
while($row = $il_result->fetch_assoc()) $iller[] = $row;

$user_id = $_SESSION['kullanici_id'] ?? null;
?>

<style>
    /* Barınaklar sayfası özel stilleri */
    .shelter-card {
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }
    
    .shelter-card:hover {
        transform: translateY(-5px);
        border-color: var(--primary);
        box-shadow: 0 20px 25px -5px rgba(186, 54, 137, 0.1), 0 10px 10px -5px rgba(186, 54, 137, 0.04);
    }
    
    .contact-btn {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        transition: all 0.3s ease;
    }
    
    .contact-btn:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }
    
    .search-container {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    }
    
    .stats-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
    }

    /* Pagination styles */
    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 0.5rem;
        margin-top: 2rem;
    }

    .pagination a, .pagination span {
        padding: 0.5rem 1rem;
        border: 1px solid #e5e7eb;
        border-radius: 0.375rem;
        text-decoration: none;
        transition: all 0.2s;
    }

    .pagination a:hover {
        background-color: var(--primary);
        color: white;
        border-color: var(--primary);
    }

    .pagination .current {
        background-color: var(--primary);
        color: white;
        border-color: var(--primary);
    }
</style>

<!-- Ana İçerik -->
<main class="min-h-screen bg-gradient-to-br from-purple-50 via-pink-50 to-purple-50">
    <!-- Hero Bölümü -->
    <div class="search-container py-16">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-12">
                <h1 class="text-5xl font-bold text-white mb-6">
                    <i class="fas fa-building mr-4"></i>
                    Hayvan Barınakları
                </h1>
                <p class="text-xl text-purple-100 mb-8 max-w-3xl mx-auto">
                    Türkiye genelindeki hayvan barınaklarını keşfedin. Onlara destek olun, gönüllü olun veya sahiplendirme yapın.
                </p>
            </div>

            <!-- Arama ve Filtre -->
            <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-6 mb-8">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-white font-medium mb-2">
                            <i class="fas fa-search mr-2"></i>Barınak Ara
                        </label>
                        <input type="text" name="search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" 
                               placeholder="Barınak adı, şehir veya adres..."
                               class="w-full px-4 py-3 rounded-lg border-0 focus:ring-2 focus:ring-white focus:ring-opacity-50 text-gray-800">
                    </div>
                    
                    <div>
                        <label class="block text-white font-medium mb-2">
                            <i class="fas fa-map-marker-alt mr-2"></i>İl Seçin
                        </label>
                        <select name="il_id" class="w-full px-4 py-3 rounded-lg border-0 focus:ring-2 focus:ring-white focus:ring-opacity-50 text-gray-800">
                            <option value="">Tüm İller</option>
                            <?php foreach($iller as $il): ?>
                                <option value="<?= $il['id'] ?>" <?= (@$_GET['il_id']==$il['id'])?'selected':'' ?>>
                                    <?= htmlspecialchars($il['ad']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-white text-primary px-6 py-3 rounded-lg font-semibold hover:bg-gray-50 transition-colors duration-200">
                            <i class="fas fa-search mr-2"></i>Ara
                        </button>
                    </div>
                </form>
            </div>

            <!-- İstatistikler -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <?php
                $total_shelters = $conn->query("SELECT COUNT(*) as total FROM hayvan_barinaklari WHERE aktif = 1")->fetch_assoc()['total'];
                $total_cities = $conn->query("SELECT COUNT(DISTINCT il_id) as total FROM hayvan_barinaklari WHERE aktif = 1")->fetch_assoc()['total'];
                
                // Check if kapasite column exists before using it
                $capacity_query = "SHOW COLUMNS FROM hayvan_barinaklari LIKE 'kapasite'";
                $capacity_check = $conn->query($capacity_query);
                
                if ($capacity_check && $capacity_check->num_rows > 0) {
                    $total_capacity = $conn->query("SELECT SUM(kapasite) as total FROM hayvan_barinaklari WHERE aktif = 1 AND kapasite IS NOT NULL")->fetch_assoc()['total'] ?? 0;
                } else {
                    // If kapasite column doesn't exist, show total animals instead
                    $total_capacity_result = $conn->query("SELECT COUNT(*) as total FROM hayvanlar WHERE durum = 'sahiplendirilmedi'");
                    $total_capacity = $total_capacity_result ? $total_capacity_result->fetch_assoc()['total'] : 0;
                }
                ?>
                
                <div class="stats-card rounded-xl p-6 text-center">
                    <div class="text-3xl font-bold text-primary mb-2"><?= $total_shelters ?></div>
                    <div class="text-gray-600">Aktif Barınak</div>
                </div>
                
                <div class="stats-card rounded-xl p-6 text-center">
                    <div class="text-3xl font-bold text-purple-600 mb-2"><?= $total_cities ?></div>
                    <div class="text-gray-600">Şehir</div>
                </div>
                
                <div class="stats-card rounded-xl p-6 text-center">
                    <div class="text-3xl font-bold text-pink-600 mb-2"><?= number_format($total_capacity) ?></div>
                    <div class="text-gray-600">
                        <?php if ($capacity_check && $capacity_check->num_rows > 0): ?>
                            Toplam Kapasite
                        <?php else: ?>
                            Sahipsiz Hayvan
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Barınaklar Listesi -->
    <div class="max-w-7xl mx-auto px-6 py-12">
        <!-- Sonuç Bilgisi -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">
                    <i class="fas fa-list mr-3 text-primary"></i>
                    Barınaklar
                </h2>
                <p class="text-gray-600 mt-1">
                    <?= $total_records ?> barınak bulundu
                    <?php if (!empty($_GET['search']) || !empty($_GET['il_id'])): ?>
                        - Filtreler aktif
                    <?php endif; ?>
                </p>
            </div>
            
            <?php if (!empty($_GET['search']) || !empty($_GET['il_id'])): ?>
                <a href="barinaklar.php" class="text-primary hover:text-primary-dark transition-colors">
                    <i class="fas fa-times mr-1"></i>Filtreleri Temizle
                </a>
            <?php endif; ?>
        </div>

        <?php if ($result && $result->num_rows > 0): ?>
            <!-- Barınak Kartları -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php while ($barinak = $result->fetch_assoc()): ?>
                    <div class="shelter-card bg-white rounded-2xl shadow-lg overflow-hidden">
                        <!-- Barınak Resmi -->
                        <div class="relative h-48 bg-gradient-to-br from-purple-400 to-pink-400">
                            <?php if (!empty($barinak['resim'])): ?>
                                <img src="uploads/barinaklar/<?= htmlspecialchars($barinak['resim']) ?>" 
                                     alt="<?= htmlspecialchars($barinak['ad']) ?>" 
                                     class="w-full h-full object-cover">
                            <?php endif; ?>
                            
                            <!-- Overlay -->
                            <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                            
                            <!-- Aktif Badge -->
                            <div class="absolute top-4 right-4">
                                <span class="bg-green-500 text-white px-3 py-1 rounded-full text-sm font-semibold">
                                    <i class="fas fa-check-circle mr-1"></i>Aktif
                                </span>
                            </div>
                            
                            <!-- Şehir Badge -->
                            <div class="absolute bottom-4 left-4">
                                <span class="bg-white/90 text-gray-800 px-3 py-1 rounded-full text-sm font-semibold">
                                    <i class="fas fa-map-marker-alt mr-1"></i>
                                    <?= htmlspecialchars($barinak['il_adi']) ?>
                                </span>
                            </div>
                        </div>

                        <!-- Barınak Bilgileri -->
                        <div class="p-6">
                            <h3 class="text-xl font-bold text-gray-800 mb-3">
                                <?= htmlspecialchars($barinak['ad']) ?>
                            </h3>

                            <!-- Bilgi Kartları -->
                            <div class="space-y-3 mb-6">
                                <?php if ($barinak['il_adi'] || $barinak['ilce_adi']): ?>
                                    <div class="flex items-center text-gray-600">
                                        <i class="fas fa-map-marker-alt w-5 text-primary"></i>
                                        <span class="ml-2">
                                            <?= htmlspecialchars($barinak['il_adi']) ?>
                                            <?php if ($barinak['ilce_adi']): ?>
                                                / <?= htmlspecialchars($barinak['ilce_adi']) ?>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                <?php endif; ?>

                                <?php if (isset($barinak['telefon']) && $barinak['telefon']): ?>
                                    <div class="flex items-center text-gray-600">
                                        <i class="fas fa-phone w-5 text-green-500"></i>
                                        <a href="tel:<?= htmlspecialchars($barinak['telefon']) ?>" 
                                           class="ml-2 hover:text-primary transition-colors">
                                            <?= htmlspecialchars($barinak['telefon']) ?>
                                        </a>
                                    </div>
                                <?php endif; ?>

                                <?php if (isset($barinak['email']) && $barinak['email']): ?>
                                    <div class="flex items-center text-gray-600">
                                        <i class="fas fa-envelope w-5 text-blue-500"></i>
                                        <a href="mailto:<?= htmlspecialchars($barinak['email']) ?>" 
                                           class="ml-2 hover:text-primary transition-colors text-sm">
                                            <?= htmlspecialchars($barinak['email']) ?>
                                        </a>
                                    </div>
                                <?php endif; ?>

                                <?php 
                                // Check if kapasite column exists before displaying it
                                $capacity_check = $conn->query("SHOW COLUMNS FROM hayvan_barinaklari LIKE 'kapasite'");
                                if ($capacity_check && $capacity_check->num_rows > 0 && $barinak['kapasite']): 
                                ?>
                                    <div class="flex items-center text-gray-600">
                                        <i class="fas fa-home w-5 text-purple-500"></i>
                                        <span class="ml-2">Kapasite: <?= htmlspecialchars($barinak['kapasite']) ?></span>
                                    </div>
                                <?php endif; ?>

                                <?php 
                                // Check if kurulus_tarihi column exists before displaying it
                                $date_check = $conn->query("SHOW COLUMNS FROM hayvan_barinaklari LIKE 'kurulus_tarihi'");
                                if ($date_check && $date_check->num_rows > 0 && $barinak['kurulus_tarihi']): 
                                ?>
                                    <div class="flex items-center text-gray-600">
                                        <i class="fas fa-calendar w-5 text-orange-500"></i>
                                        <span class="ml-2">
                                            Kuruluş: <?= date('Y', strtotime($barinak['kurulus_tarihi'])) ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Açıklama - only show if column exists and has value -->
                            <?php if (isset($barinak['aciklama']) && !empty($barinak['aciklama'])): ?>
                                <p class="text-gray-600 text-sm mb-6 line-clamp-3">
                                    <?= htmlspecialchars($barinak['aciklama']) ?>
                                </p>
                            <?php endif; ?>

                            <!-- Adres -->
                            <?php if (isset($barinak['adres']) && $barinak['adres']): ?>
                                <div class="bg-gray-50 rounded-lg p-3 mb-6">
                                    <div class="flex items-start">
                                        <i class="fas fa-map-pin text-primary mt-1 mr-2"></i>
                                        <div>
                                            <div class="font-medium text-gray-800 text-sm">Adres:</div>
                                            <div class="text-gray-600 text-sm">
                                                <?= htmlspecialchars($barinak['adres']) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- İletişim Butonları -->
                            <div class="grid grid-cols-2 gap-3">
                                <?php if (isset($barinak['telefon']) && $barinak['telefon']): ?>
                                    <a href="tel:<?= htmlspecialchars($barinak['telefon']) ?>" 
                                       class="contact-btn text-white px-4 py-3 rounded-lg text-center font-semibold text-sm">
                                        <i class="fas fa-phone mr-2"></i>Ara
                                    </a>
                                <?php endif; ?>
                                
                                <?php if (isset($barinak['adres']) && $barinak['adres']): ?>
                                    <a href="https://maps.google.com/?q=<?= urlencode($barinak['adres']) ?>" 
                                       target="_blank"
                                       class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-3 rounded-lg text-center font-semibold text-sm transition-colors">
                                        <i class="fas fa-map-marked-alt mr-2"></i>Harita
                                    </a>
                                <?php endif; ?>
                            </div>

                            <!-- Web Sitesi - only show if column exists and has value -->
                            <?php if (isset($barinak['website']) && !empty($barinak['website'])): ?>
                                <div class="mt-3">
                                    <a href="<?= htmlspecialchars($barinak['website']) ?>" 
                                       target="_blank"
                                       class="btn-gradient text-white px-4 py-2 rounded-lg text-center font-semibold text-sm w-full block">
                                        <i class="fas fa-globe mr-2"></i>Web Sitesi
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <!-- Sayfalama -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" 
                           class="text-gray-600 hover:text-primary">
                            <i class="fas fa-chevron-left mr-1"></i>Önceki
                        </a>
                    <?php endif; ?>

                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="current"><?= $i ?></span>
                        <?php else: ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" 
                               class="text-gray-600 hover:text-primary"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" 
                           class="text-gray-600 hover:text-primary">
                            Sonraki<i class="fas fa-chevron-right ml-1"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <!-- Sonuç Bulunamadı -->
            <div class="text-center py-16">
                <div class="text-8xl mb-8">🏢</div>
                <h3 class="text-3xl font-bold text-gray-600 mb-4">Barınak Bulunamadı</h3>
                <p class="text-xl text-gray-500 mb-8 max-w-md mx-auto">
                    Aradığınız kriterlere uygun barınak bulunamadı. Farklı filtreler deneyebilirsiniz.
                </p>
                <div class="space-y-4">
                    <a href="barinaklar.php" 
                       class="btn-gradient text-white px-8 py-3 rounded-lg font-semibold inline-block">
                        <i class="fas fa-list mr-2"></i>Tüm Barınakları Göster
                    </a>
                    <?php if ($user_id): ?>
                        <br>
                        <a href="barinak_ekle.php" 
                           class="bg-green-500 hover:bg-green-600 text-white px-8 py-3 rounded-lg font-semibold inline-block transition-colors">
                            <i class="fas fa-plus mr-2"></i>Barınak Ekle
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bilgilendirme Bölümü -->
    <div class="bg-white py-16">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">
                    <i class="fas fa-heart mr-3 text-primary"></i>
                    Barınaklara <span class="text-primary">Nasıl Yardım</span> Edebilirsiniz?
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Hayvan barınaklarına destek olmanın birçok yolu var. İşte onlara yardım etmenin bazı yolları:
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="text-center p-6 bg-purple-50 rounded-xl">
                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-hand-holding-heart text-3xl text-purple-600"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">Bağış Yapın</h3>
                    <p class="text-gray-600">
                        Barınakların mama, veteriner ve bakım masrafları için maddi destek sağlayın.
                    </p>
                </div>

                <div class="text-center p-6 bg-green-50 rounded-xl">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-users text-3xl text-green-600"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">Gönüllü Olun</h3>
                    <p class="text-gray-600">
                        Hayvanların bakımında, temizliğinde ve sosyalleşmesinde yardımcı olun.
                    </p>
                </div>

                <div class="text-center p-6 bg-blue-50 rounded-xl">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-home text-3xl text-blue-600"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">Sahiplenin</h3>
                    <p class="text-gray-600">
                        Bir hayvanı sahiplenerek hem ona yuva verin hem de barınağa yer açın.
                    </p>
                </div>

                <div class="text-center p-6 bg-pink-50 rounded-xl">
                    <div class="w-16 h-16 bg-pink-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-share-alt text-3xl text-pink-600"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">Paylaşın</h3>
                    <p class="text-gray-600">
                        Barınakların ve hayvanların hikayelerini sosyal medyada paylaşın.
                    </p>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- JavaScript -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Shelter card hover effects
        document.querySelectorAll('.shelter-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-8px)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });

        // Stats counter animation
        const observerOptions = {
            threshold: 0.5,
            triggerOnce: true
        };

        const statsObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const counter = entry.target;
                    const target = parseInt(counter.textContent);
                    const duration = 2000;
                    const step = target / (duration / 16);
                    let current = 0;

                    const timer = setInterval(() => {
                        current += step;
                        if (current >= target) {
                            counter.textContent = target.toLocaleString();
                            clearInterval(timer);
                        } else {
                            counter.textContent = Math.floor(current).toLocaleString();
                        }
                    }, 16);
                }
            });
        }, observerOptions);

        document.querySelectorAll('.stats-card .text-3xl').forEach(counter => {
            statsObserver.observe(counter);
        });

        // Loading animation for search form
        document.querySelector('form').addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Aranıyor...';
            submitBtn.disabled = true;
        });

        // Phone number formatting
        document.querySelectorAll('a[href^="tel:"]').forEach(link => {
            const phone = link.textContent.trim();
            if (phone.length === 11 && phone.startsWith('0')) {
                // Format Turkish phone number: 0XXX XXX XX XX
                const formatted = phone.replace(/(\d{4})(\d{3})(\d{2})(\d{2})/, '$1 $2 $3 $4');
                link.textContent = formatted;
            }
        });

        // Initialize tooltips
        document.querySelectorAll('[title]').forEach(element => {
            element.addEventListener('mouseenter', function() {
                const tooltip = document.createElement('div');
                tooltip.className = 'fixed bg-gray-800 text-white px-2 py-1 rounded text-sm z-50 pointer-events-none';
                tooltip.textContent = this.title;
                document.body.appendChild(tooltip);
                
                const rect = this.getBoundingClientRect();
                tooltip.style.left = rect.left + 'px';
                tooltip.style.top = (rect.top - 30) + 'px';
                
                this.addEventListener('mouseleave', function() {
                    if (tooltip.parentNode) {
                        tooltip.parentNode.removeChild(tooltip);
                    }
                }, { once: true });
            });
        });

        console.log('🏢 Barınaklar sayfası yüklendi');
    });

    // Back to top functionality
    window.addEventListener('scroll', function() {
        const backToTop = document.getElementById('backToTop');
        if (window.scrollY > 300) {
            if (!backToTop) {
                const button = document.createElement('button');
                button.id = 'backToTop';
                button.innerHTML = '<i class="fas fa-arrow-up"></i>';
                button.className = 'fixed bottom-6 right-6 bg-primary text-white p-3 rounded-full shadow-lg hover:bg-primary-dark transition-all duration-300 z-50';
                button.addEventListener('click', () => {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });
                document.body.appendChild(button);
            }
        } else {
            if (backToTop) {
                backToTop.remove();
            }
        }
    });
</script>

<?php include("includes/footer.php"); ?>