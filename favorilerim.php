<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include("includes/db.php");

// Kullanıcı girişi kontrolü
if (!isset($_SESSION['kullanici_id']) || empty($_SESSION['kullanici_id'])) {
    $_SESSION['message'] = "<div class='bg-yellow-500 text-white p-3 rounded-md mb-4'>Bu sayfayı görüntülemek için giriş yapmalısınız.</div>";
    header("Location: giris.php");
    exit;
}

$page_title = "Favori İlanlarım - Sıcak Patizi";
include("includes/header.php");

$kullanici_id = $_SESSION['kullanici_id']; 
?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-grow">
    <!-- Sayfa Başlığı -->
    <div class="text-center mb-10">
        <h1 class="text-4xl font-bold text-gray-800 mb-3 flex items-center justify-center">
            <i class="fas fa-heart text-primary mr-3"></i>
            Favori İlanlarım
        </h1>
        <p class="text-lg text-gray-600">Beğendiğiniz ve takip etmek istediğiniz tüm dostlarımız</p>
    </div>

    <div id="favorilerContainer">
        <?php
        // Kullanıcının favorilediği ilan ID'lerini çek
        $stmt_favoriler = $conn->prepare("SELECT ilan_id FROM favoriler WHERE kullanici_id = ?");
        $stmt_favoriler->bind_param("i", $kullanici_id);
        $stmt_favoriler->execute();
        $favoriler_sonuc = $stmt_favoriler->get_result();

        $favori_ilan_idler = [];
        while ($row = $favoriler_sonuc->fetch_assoc()) {
            $favori_ilan_idler[] = $row['ilan_id'];
        }
        $stmt_favoriler->close();

        if (empty($favori_ilan_idler)) {
            echo '
            <div class="col-span-full text-center py-16 bg-white rounded-xl shadow-lg card-hover">
                <div class="text-6xl mb-4">🐾</div>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">Henüz Favori İlanınız Yok</h3>
                <p class="text-gray-600 mb-6">Sahiplendirme ilanlarına göz atarak sevimli dostlarımızı favorilerinize ekleyebilirsiniz.</p>
                <a href="ilanlar.php" class="btn-gradient text-white px-6 py-3 rounded-lg font-semibold inline-flex items-center shadow-md">
                    <i class="fas fa-search mr-2"></i>İlanları İncele
                </a>
            </div>';
        } else {
            $placeholders = implode(',', array_fill(0, count($favori_ilan_idler), '?'));
            $types = str_repeat('i', count($favori_ilan_idler)); 

            $stmt_ilanlar = $conn->prepare("
                SELECT 
                    ilanlar.*, 
                    kullanicilar.kullanici_adi,
                    kullanicilar.eposta,     
                    cinsler.ad AS cins_adi,         
                    hastaliklar.ad AS hastalik_adi, 
                    kategoriler.ad AS kategori_adi,
                    il.ad AS il_adi,             
                    ilce.ad AS ilce_adi          
                FROM ilanlar 
                LEFT JOIN kullanicilar ON ilanlar.kullanici_id = kullanicilar.id 
                LEFT JOIN cinsler ON ilanlar.cins_id = cinsler.id         
                LEFT JOIN hastaliklar ON ilanlar.hastalik_id = hastaliklar.id 
                LEFT JOIN kategoriler ON ilanlar.kategori_id = kategoriler.id 
                LEFT JOIN il ON ilanlar.il_id = il.id    
                LEFT JOIN ilce ON ilanlar.ilce_id = ilce.id 
                WHERE ilanlar.id IN ($placeholders)
                ORDER BY ilanlar.tarih DESC
            ");
            
            $bind_params_array = [$types];
            foreach ($favori_ilan_idler as $id) { 
                $bind_params_array[] = $id; 
            }
            
            $stmt_ilanlar->bind_param(...$bind_params_array);
            $stmt_ilanlar->execute();
            $ilan_sonuc = $stmt_ilanlar->get_result();

            if ($ilan_sonuc->num_rows > 0) {
                echo '<div id="favoriGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">'; 
                while ($ilan = $ilan_sonuc->fetch_assoc()):
                    $is_favorited = true; 
                ?>
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden card-hover flex flex-col justify-between favorite-card-item">
                        <div class="relative">
                            <?php
                            $image_path = !empty($ilan['foto']) ? 'uploads/' . htmlspecialchars($ilan['foto']) : '';
                            $display_image = (file_exists($image_path) && !empty($image_path)) ? $image_path : 'https://via.placeholder.com/300x200?text=Resim+Yok';
                            ?>
                            <img src="<?= $display_image ?>" alt="<?= htmlspecialchars($ilan['baslik']) ?>" class="w-full h-48 object-cover">
                            
                            <button class="favorite-btn absolute top-3 right-3 bg-white/90 backdrop-blur-sm rounded-full p-2 shadow-md text-primary transition-colors duration-200"
                                    data-ilan-id="<?= $ilan['id'] ?>">
                                <i class="fas fa-heart"></i>
                            </button>
                        </div>
                        
                        <div class="p-4 flex-1 flex flex-col justify-between">
                            <div>
                                <h3 class="text-lg font-bold text-gray-800 mb-2 line-clamp-1"><?= htmlspecialchars($ilan['baslik']) ?></h3>
                                
                                <div class="flex flex-wrap gap-1.5 mb-3">
                                    <?php if (!empty($ilan['kategori_adi'])): ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-primary-light text-primary">
                                            <i class="fas fa-tag mr-1"></i><?= htmlspecialchars($ilan['kategori_adi']) ?>
                                        </span>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($ilan['cins_adi'])): ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <i class="fas fa-paw mr-1"></i><?= htmlspecialchars($ilan['cins_adi']) ?>
                                        </span>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($ilan['il_adi'])): ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                            <i class="fas fa-map-marker-alt mr-1"></i><?= htmlspecialchars($ilan['il_adi']) ?><?= !empty($ilan['ilce_adi']) ? ' / ' . htmlspecialchars($ilan['ilce_adi']) : '' ?>
                                        </span>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($ilan['hastalik_adi']) && $ilan['hastalik_adi'] !== 'NULL'): ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                            <i class="fas fa-heartbeat mr-1"></i><?= htmlspecialchars($ilan['hastalik_adi']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <p class="text-gray-600 text-sm mb-4 line-clamp-2"><?= htmlspecialchars($ilan['aciklama'] ?? '') ?></p>
                            </div>
                            
                            <div class="flex justify-between items-center pt-3 border-t border-gray-100 mt-auto">
                                <span class="text-xs text-gray-500">
                                    <i class="fas fa-calendar mr-1"></i>
                                    <?= date('d.m.Y', strtotime($ilan['tarih'])) ?>
                                </span>
                                <a href="ilan_detay.php?id=<?= $ilan['id'] ?>" 
                                   class="btn-gradient text-white px-4 py-2 rounded-lg text-sm font-semibold hover:shadow-md transition">
                                    Detaylar
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile;
                echo '</div>'; 
            } else {
                echo '
                <div class="col-span-full text-center py-16 bg-white rounded-xl shadow-lg card-hover">
                    <div class="text-6xl mb-4">🐾</div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">Henüz Favori İlanınız Yok</h3>
                    <p class="text-gray-600 mb-6">Sahiplendirme ilanlarına göz atarak sevimli dostlarımızı favorilerinize ekleyebilirsiniz.</p>
                    <a href="ilanlar.php" class="btn-gradient text-white px-6 py-3 rounded-lg font-semibold inline-flex items-center shadow-md">
                        <i class="fas fa-search mr-2"></i>İlanları İncele
                    </a>
                </div>';
            }
            $stmt_ilanlar->close();
        }
        ?>
    </div>
</main>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll('.favorite-btn').forEach(button => {
            button.addEventListener('click', function() {
                const ilanId = this.dataset.ilanId;
                const currentButton = this;

                fetch('includes/ajax_favori_toggle.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `ilan_id=${ilanId}`
                })
                .then(response => response.json())
                .then(res => {
                    if (res.status === 'success') {
                        if (res.action === 'removed') {
                            const cardToRemove = currentButton.closest('.favorite-card-item');
                            if (cardToRemove) {
                                cardToRemove.remove();
                            }
                            
                            Swal.fire({
                                title: 'Bilgi',
                                text: 'İlan favorilerinizden çıkarıldı.',
                                icon: 'info',
                                timer: 2000, 
                                showConfirmButton: false
                            });

                            const favoriteGrid = document.getElementById('favoriGrid');
                            if (favoriteGrid && favoriteGrid.children.length === 0) {
                                const container = document.getElementById('favorilerContainer');
                                if (container) {
                                    container.innerHTML = `
                                        <div class="col-span-full text-center py-16 bg-white rounded-xl shadow-lg card-hover">
                                            <div class="text-6xl mb-4">🐾</div>
                                            <h3 class="text-2xl font-bold text-gray-800 mb-2">Henüz Favori İlanınız Yok</h3>
                                            <p class="text-gray-600 mb-6">Sahiplendirme ilanlarına göz atarak sevimli dostlarımızı favorilerinize ekleyebilirsiniz.</p>
                                            <a href="ilanlar.php" class="btn-gradient text-white px-6 py-3 rounded-lg font-semibold inline-flex items-center shadow-md">
                                                <i class="fas fa-search mr-2"></i>İlanları İncele
                                            </a>
                                        </div>`;
                                }
                            }
                        }
                    } else {
                        if (res.redirect) {
                            window.location.href = res.redirect;
                        } else {
                            alert(res.message || 'Bir hata oluştu.');
                        }
                    }
                })
                .catch(error => {
                    console.error("AJAX error:", error);
                    alert('Sunucu ile iletişim kurulurken bir hata oluştu.');
                });
            });
        });
    });
</script>

<?php include("includes/footer.php"); ?>