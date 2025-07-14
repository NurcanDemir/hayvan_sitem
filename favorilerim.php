<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

include("includes/db.php");
// include("includes/header.php"); // Header'ı HTML içinde çağıracağız

// Kullanıcı girişi kontrolü
if (!isset($_SESSION['kullanici_id']) || empty($_SESSION['kullanici_id'])) {
    // Kullanıcıya uyarı gösterip giriş sayfasına yönlendir
    $_SESSION['message'] = "<div class='bg-yellow-500 text-white p-3 rounded-md mb-4'>Bu sayfayı görüntülemek için giriş yapmalısınız.</div>";
    header("Location: giris.php");
    exit;
}

$kullanici_id = $_SESSION['kullanici_id']; 

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Favori İlanlarım</title>
    <link href="./dist/output.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Info Tag Styling for pastel look, consistent with ilanlar.php */
        .info-tag-tailwind {
            @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium;
        }

        /* SweetAlert2 Popup'ı için Genel Stiller */
        .swal2-popup {
            background-color: #f8fafc !important; /* Hafif gri bir arka plan */
            border-radius: 0.75rem !important; /* Daha yuvarlak köşeler */
            padding: 1.5rem !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important; /* Daha belirgin bir gölge */
            border: 1px solid #e2e8f0 !important; /* Hafif bir kenarlık */
        }

        /* SweetAlert2 Başlık Stili */
        .swal2-title {
            color: #92405F !important; /* Koyu pembe başlık */
            font-size: 1.875rem !important; /* Daha büyük başlık fontu */
            font-weight: 700 !important; /* Kalın font */
            margin-bottom: 1rem !important;
        }

        /* SweetAlert2 İçerik (Mesaj) Stili */
        .swal2-html-container { /* SweetAlert2'nin mesaj metni için kullandığı sınıf */
            color: #4A5568 !important; /* Koyu gri metin rengi */
            font-size: 1.125rem !important; /* İçerik fontunu biraz büyütme */
            line-height: 1.5 !important;
        }

        /* SweetAlert2 İkonları için (başarılı, hata vb.) renk düzenlemesi */
        /* Bu genellikle SweetAlert2'nin kendi varsayılan stilleriyle uyumludur, ancak özelleştirmek isterseniz: */
        .swal2-success .swal2-success-line-tip,
        .swal2-success .swal2-success-line-long {
            background-color: #68D391 !important; /* Yeşil tonu */
        }
        .swal2-info { /* .swal2-info sınıfı ikonun genel kapsayıcısıdır */
            border-color: #63B3ED !important; /* Mavi tonu */
            color: #63B3ED !important;
        }
        /* Özel ikon renkleri için doğrudan SVG'leri hedeflemek daha iyi olabilir,
           ancak genel ikon renklerini değiştirmek için bu yeterli olabilir. */
        .swal2-error-container .swal2-x-mark { /* Hata X işareti */
             background-color: #EF4444 !important; /* Kırmızı tonu */
             /* Bu aslında X işaretinin kendisi değil, arkasındaki gölge veya arkaplanı etkiler */
        }
        .swal2-error { /* Genel hata ikonu rengi */
            border-color: #EF4444 !important;
            color: #EF4444 !important;
        }

        /* SweetAlert2 Onay Butonu Stili */
        .swal2-confirm {
            background-color: #D26F90 !important; /* Açık pembe buton arka planı */
            color: white !important;
            font-weight: 600 !important;
            padding: 0.75rem 1.5rem !important;
            border-radius: 0.5rem !important;
            transition: background-color 0.2s ease-in-out !important;
        }

        .swal2-confirm:hover {
            background-color: #b75e7a !important; /* Buton hover rengi */
        }
    </style>
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal min-h-screen flex flex-col">

<?php include("includes/header.php"); ?>

<div class="container mx-auto px-4 py-8 mt-16 md:mt-24 flex-grow">
    <h1 class="text-4xl font-extrabold text-center text-koyu-pembe mb-8">Favori İlanlarım</h1>

    <?php
    // Kullanıcının favorilediği ilan ID'lerini çek
    $stmt_favoriler = $conn->prepare("SELECT ilan_id FROM favoriler WHERE kullanici_id = ?");
    $stmt_favoriler->bind_param("i", $kullanici_id); // Kullanici ID'sinin int olduğunu varsayarak 'i' kullanıldı.
    $stmt_favoriler->execute();
    $favoriler_sonuc = $stmt_favoriler->get_result();

    $favori_ilan_idler = [];
    while ($row = $favoriler_sonuc->fetch_assoc()) {
        $favori_ilan_idler[] = $row['ilan_id'];
    }
    $stmt_favoriler->close();

    if (empty($favori_ilan_idler)) {
        echo "<div class='bg-soluk-mavi text-blue-900 p-4 rounded-lg text-center text-lg font-semibold shadow-md'>
                Henüz favori ilanınız bulunmamaktadır.
              </div>";
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
            echo '<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">'; 
            while ($ilan = $ilan_sonuc->fetch_assoc()):
                $is_favorited = true; 
            ?>
                <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 flex items-center p-3 gap-3 relative">
                    <div class="w-24 h-24 flex-shrink-0">
                        <img src="uploads/<?= htmlspecialchars($ilan['foto'] ?: 'placeholder.jpg') ?>" alt="<?= htmlspecialchars($ilan['baslik']) ?>"
                             class="rounded-full w-full h-full object-cover border-2 border-koyu-pembe shadow-sm">
                        
                        <button class="favorite-btn absolute -top-1 -right-1 z-10 bg-white rounded-full p-1 text-base shadow-md
                            <?= $is_favorited ? 'text-koyu-pembe' : 'text-gray-400 hover:text-koyu-pembe' ?> transition-colors duration-200"
                            data-ilan-id="<?= $ilan['id'] ?>">
                            <i class="fas fa-heart"></i>
                        </button>
                    </div>
                    <div class="flex-grow flex flex-col justify-between">
                        <h2 class="text-lg font-semibold text-gray-800 leading-tight mb-1"><?= htmlspecialchars($ilan['baslik']) ?></h2>
                        <div class="flex flex-wrap gap-1 text-sm mb-2">
                            <?php if (!empty($ilan['kategori_adi'])): ?>
                                <span class="info-tag-tailwind bg-toz-pembe text-koyu-pembe">
                                    <i class="fas fa-folder mr-1"></i><?= htmlspecialchars($ilan['kategori_adi']) ?>
                                </span>
                            <?php endif; ?>
                            <?php if (!empty($ilan['il_adi'])): ?>
                                <span class="info-tag-tailwind bg-gray-200 text-gray-700">
                                    <i class="fas fa-map-marker-alt mr-1"></i><?= htmlspecialchars($ilan['il_adi']) ?>
                                </span>
                            <?php endif; ?>
                            <?php if (!empty($ilan['ilce_adi'])): ?>
                                <span class="info-tag-tailwind bg-gray-200 text-gray-700">
                                    <i class="fas fa-map-pin mr-1"></i><?= htmlspecialchars($ilan['ilce_adi']) ?>
                                </span>
                            <?php endif; ?>
                            <?php if (!empty($ilan['hastalik_adi']) && $ilan['hastalik_adi'] !== 'NULL'): ?>
                                <span class="info-tag-tailwind bg-soluk-mavi text-blue-800">
                                    <i class="fas fa-notes-medical mr-1"></i><?= htmlspecialchars($ilan['hastalik_adi']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <a href="ilan_detay.php?id=<?= $ilan['id'] ?>"
                           class="block text-center bg-acik-pembe hover:bg-toz-pembe text-koyu-pembe text-sm font-semibold py-1 px-2 rounded-md transition duration-300">
                            Detayları Görüntüle
                        </a>
                    </div>
                </div>
            <?php endwhile;
            echo '</div>'; 
        } else {
            echo "<div class='bg-soluk-mavi text-blue-900 p-4 rounded-lg text-center text-lg font-semibold shadow-md'>
                    Henüz favori ilanınız bulunmamaktadır.
                  </div>";
        }
        $stmt_ilanlar->close();
    }
    ?>
</div>

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
                            const cardToRemove = currentButton.closest('.bg-white.rounded-lg.shadow-md');
                            if (cardToRemove) {
                                cardToRemove.remove();
                            }
                            // SweetAlert2 bildirimi: Favorilerden çıkarıldı
                            Swal.fire({
                                title: 'Bilgi',
                                text: 'İlan favorilerden çıkarıldı.',
                                icon: 'info',
                                timer: 2000, 
                                showConfirmButton: false,
                                customClass: {
                                    popup: 'swal2-popup',
                                    title: 'swal2-title',
                                    htmlContainer: 'swal2-html-container', // İçerik mesajı için doğru sınıf
                                    confirmButton: 'swal2-confirm',
                                },
                                buttonsStyling: false, // Tailwind veya kendi CSS'inizle stil verebilmek için false
                            });

                            const favoriteGrid = document.querySelector('.grid.grid-cols-1.sm\\:grid-cols-2.lg\\:grid-cols-3.xl\\:grid-cols-4');
                            if (favoriteGrid && favoriteGrid.children.length === 0) {
                                const container = document.querySelector('.container.mx-auto');
                                if (container) {
                                    container.innerHTML = `<h1 class="text-4xl font-extrabold text-center text-koyu-pembe mb-8">Favori İlanlarım</h1>
                                        <div class='bg-soluk-mavi text-blue-900 p-4 rounded-lg text-center text-lg font-semibold shadow-md'>
                                            Henüz favori ilanınız bulunmamaktadır.
                                        </div>`;
                                }
                            }
                        } else { // Favorilerim sayfasında "added" durumu normalde olmaz ama olursa diye
                            currentButton.classList.remove('text-gray-400', 'hover:text-koyu-pembe');
                            currentButton.classList.add('text-koyu-pembe');
                            // SweetAlert2 bildirimi: Favorilere eklendi (Bu durum favorilerim sayfasında pek beklenmez)
                            Swal.fire({
                                title: 'Başarılı!',
                                text: 'İlan favorilere eklendi.',
                                icon: 'success',
                                timer: 2000, 
                                showConfirmButton: false,
                                customClass: {
                                    popup: 'swal2-popup',
                                    title: 'swal2-title',
                                    htmlContainer: 'swal2-html-container',
                                    confirmButton: 'swal2-confirm',
                                },
                                buttonsStyling: false,
                            });
                        }
                    } else {
                        // Hata durumunda SweetAlert2 uyarısı
                        Swal.fire({
                            title: 'Hata!',
                            text: res.message || 'Bir hata oluştu. Lütfen tekrar deneyin.',
                            icon: 'error',
                            confirmButtonText: 'Tamam',
                            customClass: {
                                popup: 'swal2-popup',
                                title: 'swal2-title',
                                htmlContainer: 'swal2-html-container',
                                confirmButton: 'swal2-confirm',
                            },
                            buttonsStyling: false,
                        });
                        if (res.redirect) {
                            setTimeout(() => { 
                                window.location.href = res.redirect;
                            }, 2000); 
                        }
                    }
                })
                .catch(error => {
                    console.error("AJAX error:", error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Bağlantı Hatası!',
                        text: 'Sunucu ile iletişim kurulurken bir hata oluştu. Lütfen internet bağlantınızı kontrol edin.',
                        confirmButtonText: 'Tamam',
                        customClass: {
                            popup: 'swal2-popup', // Genel popup stili için kendi sınıfımızı kullanıyoruz
                            title: 'swal2-title',
                            htmlContainer: 'swal2-html-container',
                            confirmButton: 'swal2-confirm',
                        },
                        buttonsStyling: false, // Kendi CSS'imizle stil verebilmek için false
                    });
                });
            });
        });
    });
</script>

<?php include("includes/footer.php"); ?>

</body>
</html>