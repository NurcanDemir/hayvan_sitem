<?php
session_start();
include("includes/db.php");

$ilan = null;
$mesaj = "";
$mesaj_tur = "";

if (isset($_GET['id'])) {
    $ilan_id = intval($_GET['id']);

    if ($ilan_id > 0) {
        // İlan detaylarını çekme sorgusu (JOIN ile ilgili diğer tabloları da dahil ediyoruz)
        $stmt = $conn->prepare("
            SELECT
                ilanlar.*,
                ilanlar.foto AS fotograf_url,        -- 'foto' sütununu 'fotograf_url' olarak adlandırdık
                ilanlar.tarih AS olusturma_tarihi,   -- 'tarih' sütununu 'olusturma_tarihi' olarak adlandırdık
                kullanicilar.kullanici_adi,
                kullanicilar.eposta,
                kullanicilar.telefon AS kullanici_telefon,
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
            WHERE ilanlar.id = ?
        ");
        $stmt->bind_param("i", $ilan_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $ilan = $result->fetch_assoc();
        } else {
            $mesaj = "İlan bulunamadı.";
            $mesaj_tur = "danger";
        }
        $stmt->close();
    } else {
        $mesaj = "Geçersiz ilan ID'si.";
        $mesaj_tur = "danger";
    }
} else {
    $mesaj = "İlan ID'si belirtilmedi.";
    $mesaj_tur = "warning";
}

// Favori kontrolü
$is_favorited = false;
if (isset($_SESSION['kullanici_id']) && $ilan) {
    $stmt_fav = $conn->prepare("SELECT id FROM favoriler WHERE kullanici_id = ? AND ilan_id = ?");
    $stmt_fav->bind_param("ii", $_SESSION['kullanici_id'], $ilan_id);
    $stmt_fav->execute();
    $fav_result = $stmt_fav->get_result();
    if ($fav_result->num_rows > 0) {
        $is_favorited = true;
    }
    $stmt_fav->close();
}

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $ilan ? htmlspecialchars($ilan['baslik']) . ' - İlan Detayı' : 'İlan Detayı' ?></title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <link href="./dist/output.css" rel="stylesheet">
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background-color: white;
            margin: auto;
            padding: 20px;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
        }

        .close-button {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            position: absolute;
            right: 15px;
            top: 10px;
        }

        .close-button:hover,
        .close-button:focus {
            color: black;
            text-decoration: none;
        }
    </style>
</head>
<body class="bg-gray-50 font-sans min-h-screen flex flex-col">
    <?php include("includes/header.php"); ?>

    <div class="container mx-auto p-4 flex-grow pt-20">
        <div class="max-w-6xl mx-auto bg-white p-8 rounded-lg shadow-xl mt-8">
        <?php if ($mesaj): ?>
            <div class="p-4 mb-4 rounded-md text-center <?= $mesaj_tur == 'success' ? 'bg-green-100 text-green-700' : ($mesaj_tur == 'danger' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') ?>">
                <?= htmlspecialchars($mesaj) ?>
            </div>
        <?php endif; ?>

        <?php if ($ilan): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="image-section">
                    <?php
                    $image_path = !empty($ilan['fotograf_url']) ? 'uploads/' . $ilan['fotograf_url'] : '';
                    $display_image = (file_exists($image_path) && !empty($image_path)) ? $image_path : 'https://via.placeholder.com/500x400?text=Resim+Yok';
                    ?>
                    <img src="<?= htmlspecialchars($display_image) ?>" alt="<?= htmlspecialchars($ilan['baslik']) ?>" class="w-full h-auto object-cover rounded-lg shadow-md">
                </div>

                <div class="text-section">
                    <h1 class="text-3xl font-bold text-gray-800 mb-4"><?= htmlspecialchars($ilan['baslik']) ?></h1>
                    <p class="text-gray-700 mb-2"><strong>Tür:</strong> <?= htmlspecialchars($ilan['kategori_adi']) ?></p>
                    <p class="text-gray-700 mb-2"><strong>Cins:</strong> <?= htmlspecialchars($ilan['cins_adi']) ?></p>
                    <p class="text-gray-700 mb-2"><strong>Yaş:</strong> <?= htmlspecialchars($ilan['yas'] ?? 'Belirtilmemiş') ?></p>
                    <p class="text-gray-700 mb-2"><strong>Cinsiyet:</strong> <?= htmlspecialchars($ilan['cinsiyet'] == 'e' ? 'Erkek' : ($ilan['cinsiyet'] == 'd' ? 'Dişi' : 'Belirtilmemiş')) ?></p>
                    <p class="text-gray-700 mb-2"><strong>Aşı Durumu:</strong> <?= htmlspecialchars($ilan['asi_durumu'] ?? 'Belirtilmemiş') ?></p>
                    <p class="text-gray-700 mb-2"><strong>Kısırlaştırma:</strong> <?= htmlspecialchars($ilan['kisirlastirma'] ? 'Evet' : ($ilan['kisirlastirma'] === 0 ? 'Hayır' : 'Belirtilmemiş')) ?></p>
                    <p class="text-gray-700 mb-2"><strong>Hastalık:</strong> <?= $ilan['hastalik_adi'] ? htmlspecialchars($ilan['hastalik_adi']) : 'Yok' ?></p>
                    <p class="text-gray-700 mb-2"><strong>Şehir:</strong> <?= htmlspecialchars($ilan['il_adi']) ?></p>
                    <p class="text-gray-700 mb-2"><strong>İlçe:</strong> <?= htmlspecialchars($ilan['ilce_adi']) ?></p>
                    <p class="text-gray-700 mb-4"><strong>Açıklama:</strong> <?= nl2br(htmlspecialchars($ilan['aciklama'])) ?></p>
                    <p class="text-sm text-gray-500 mb-4">İlan Sahibi: <?= htmlspecialchars($ilan['kullanici_adi']) ?> (<?= date('d.m.Y', strtotime($ilan['olusturma_tarihi'] ?? '1970-01-01')) ?> tarihinde eklendi)</p>

                    <div class="flex space-x-4 mt-6">
                        <?php if ($ilan['durum'] == 'sahiplenildi'): ?>
                            <!-- Sahiplenilmiş ilan için özel gösterim -->
                            <div class="bg-green-100 border border-green-400 text-green-700 px-6 py-4 rounded-lg w-full text-center">
                                <i class="fas fa-heart text-green-600 mr-2"></i>
                                <strong>Bu sevimli arkadaş mutlu yuvasını buldu!</strong>
                                <p class="text-sm mt-2">Sahiplenme işlemi tamamlandı.</p>
                            </div>
                        <?php else: ?>
                            <!-- Aktif ilan için butonlar -->
                            <?php if (isset($_SESSION['kullanici_id'])): ?>
                                <button id="favoriteButton" data-ilan-id="<?= $ilan['id'] ?>"
                                        class="<?= $is_favorited ? 'bg-yellow-500 hover:bg-yellow-600' : 'bg-gray-500 hover:bg-gray-600' ?> text-white font-bold py-2 px-4 rounded-md transition duration-300">
                                    <i class="fas fa-heart mr-2"></i>
                                    <?= $is_favorited ? 'Favorilerden Çıkar' : 'Favorilere Ekle' ?>
                                </button>
                            <?php else: ?>
                                <a href="giris.php" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded-md transition duration-300">
                                    <i class="fas fa-heart mr-2"></i> Favorilere Ekle (Giriş Yap)
                                </a>
                            <?php endif; ?>

                            <?php if (isset($_SESSION['kullanici_id'])): ?>
                                <?php if ($_SESSION['kullanici_id'] != $ilan['kullanici_id']): // Kendi ilanına talep gönderemez ?>
                                    <button id="sahiplenmeTalepButonu" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-md transition duration-300">
                                        <i class="fas fa-paw mr-2"></i> Sahiplenmek İstiyorum
                                    </button>
                                <?php else: ?>
                                    <button class="bg-gray-500 text-white font-bold py-2 px-4 rounded-md cursor-not-allowed" disabled>
                                        <i class="fas fa-info-circle mr-2"></i> Kendi ilanınız
                                    </button>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="giris.php" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-md transition duration-300">
                                    <i class="fas fa-paw mr-2"></i> Sahiplenmek İstiyorum (Giriş Yap)
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($ilan['durum'] == 'sahiplenildi'): ?>
                        <!-- Sahiplenme yorumlarını göster -->
                        <?php
                        // Önce sahiplenen_yorumu kolonunun var olup olmadığını kontrol et
                        $columns_check = $conn->query("SHOW COLUMNS FROM sahiplenme_istekleri LIKE 'sahiplenen_yorumu'");
                        $column_exists = $columns_check->num_rows > 0;
                        
                        if ($column_exists) {
                            $stmt_yorum = $conn->prepare("
                                SELECT si.sahiplenen_yorumu, si.yorum_tarihi, si.talep_eden_ad_soyad
                                FROM sahiplenme_istekleri si
                                WHERE si.ilan_id = ? AND si.durum = 'tamamlandı' AND si.sahiplenen_yorumu IS NOT NULL
                                ORDER BY si.yorum_tarihi DESC
                            ");
                            $stmt_yorum->bind_param("i", $ilan['id']);
                            $stmt_yorum->execute();
                            $yorum_result = $stmt_yorum->get_result();
                        } else {
                            // Kolon yoksa boş result set oluştur
                            $yorum_result = null;
                        }
                        ?>
                        
                        <?php if ($column_exists && $yorum_result && $yorum_result->num_rows > 0): ?>
                            <div class="mt-8 bg-gradient-to-r from-green-50 to-blue-50 p-6 rounded-lg">
                                <h3 class="text-xl font-bold text-gray-800 mb-4">
                                    <i class="fas fa-comments text-green-600 mr-2"></i>
                                    Yeni Ailesinden Haberler
                                </h3>
                                
                                <?php while ($yorum = $yorum_result->fetch_assoc()): ?>
                                    <div class="bg-white p-4 rounded-lg shadow-sm mb-4 border-l-4 border-green-500">
                                        <div class="flex items-center mb-2">
                                            <i class="fas fa-user-circle text-green-600 mr-2"></i>
                                            <strong class="text-green-800"><?= htmlspecialchars($yorum['talep_eden_ad_soyad']) ?></strong>
                                            <span class="text-gray-500 text-sm ml-2">
                                                • <?= date('d.m.Y H:i', strtotime($yorum['yorum_tarihi'])) ?>
                                            </span>
                                        </div>
                                        <p class="text-gray-700 italic leading-relaxed">
                                            <i class="fas fa-quote-left text-green-400 mr-1"></i>
                                            <?= nl2br(htmlspecialchars($yorum['sahiplenen_yorumu'])) ?>
                                            <i class="fas fa-quote-right text-green-400 ml-1"></i>
                                        </p>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php elseif ($column_exists): ?>
                            <div class="mt-8 bg-yellow-50 p-6 rounded-lg border-l-4 border-yellow-500">
                                <h3 class="text-lg font-semibold text-yellow-800 mb-2">
                                    <i class="fas fa-clock text-yellow-600 mr-2"></i>
                                    Henüz Yorum Yok
                                </h3>
                                <p class="text-yellow-700">Bu sevimli arkadaş yeni ailesinden henüz deneyimlerini paylaşmadı.</p>
                            </div>
                        <?php else: ?>
                            <div class="mt-8 bg-blue-50 p-6 rounded-lg border-l-4 border-blue-500">
                                <h3 class="text-lg font-semibold text-blue-800 mb-2">
                                    <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                                    Yorum Sistemi
                                </h3>
                                <p class="text-blue-700">Yorum sistemi için veritabanı güncellemesi gerekiyor.</p>
                                <p class="text-blue-600 text-sm mt-2">
                                    <a href="manual_db_update.php" class="underline">Güncelleme için tıklayın</a>
                                </p>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($column_exists && isset($stmt_yorum)) $stmt_yorum->close(); ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div id="sahiplenmeModal" class="modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Sahiplenme Talebi Formu</h2>
            <form id="sahiplenmeTalepForm">
                <input type="hidden" name="ilan_id" id="modal_ilan_id" value="<?= htmlspecialchars($ilan['id'] ?? '') ?>">
                <input type="hidden" name="ilan_sahibi_id" id="modal_ilan_sahibi_id" value="<?= htmlspecialchars($ilan['kullanici_id'] ?? '') ?>">

                <div class="mb-4">
                    <label for="talep_eden_ad_soyad" class="block text-sm font-medium text-gray-700 mb-2">Adınız Soyadınız:</label>
                    <input type="text" id="talep_eden_ad_soyad" name="talep_eden_ad_soyad" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Adınız Soyadınız" value="<?= htmlspecialchars($_SESSION['kullanici_adi'] ?? '') ?>" required>
                </div>

                <div class="mb-4">
                    <label for="talep_eden_telefon" class="block text-sm font-medium text-gray-700 mb-2">Telefon Numaranız:</label>
                    <input type="tel" id="talep_eden_telefon" name="talep_eden_telefon" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Telefon Numaranız" value="<?= htmlspecialchars($_SESSION['telefon'] ?? '') ?>" required>
                </div>

                <div class="mb-4">
                    <label for="talep_eden_email" class="block text-sm font-medium text-gray-700 mb-2">E-posta Adresiniz:</label>
                    <input type="email" id="talep_eden_email" name="talep_eden_email" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="E-posta Adresiniz" value="<?= htmlspecialchars($_SESSION['eposta'] ?? '') ?>" required>
                </div>

                <div class="mb-4">
                    <label for="adres" class="block text-sm font-medium text-gray-700 mb-2">Adresiniz (İl, İlçe ve Mahalle/Açık Adres)</label>
                    <textarea id="adres" name="adres" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Talep eden olarak iletişime geçildiğinde geleceğiniz veya bulunduğunuz adres bilgisi... (Zorunlu)" required><?= htmlspecialchars($_SESSION['adres'] ?? '') ?></textarea>
                </div>

                <div class="mb-4">
                    <label for="mesaj" class="block text-sm font-medium text-gray-700 mb-2">İlan Sahibine Mesajınız (İsteğe Bağlı):</label>
                    <textarea id="mesaj" name="mesaj" rows="5" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Neden sahiplenmek istediğinizi ve kendinizi tanıtan kısa bir mesaj..."></textarea>
                </div>

                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-6 rounded-md transition duration-300 w-full">Talebi Gönder</button>
            </form>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const favoriteButton = document.getElementById('favoriteButton');
        const sahiplenmeTalepButonu = document.getElementById('sahiplenmeTalepButonu');
        const sahiplenmeModal = document.getElementById('sahiplenmeModal');
        const closeModalButton = sahiplenmeModal ? sahiplenmeModal.querySelector('.close-button') : null;
        const sahiplenmeTalepForm = document.getElementById('sahiplenmeTalepForm');

        // Favori butonu
        if (favoriteButton) {
            favoriteButton.addEventListener('click', function() {
                const ilanId = this.dataset.ilanId;
                const isFavorited = this.classList.contains('bg-yellow-500'); // Favoride olup olmadığını kontrol et

                fetch('favori_islem.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `ilan_id=${ilanId}&action=${isFavorited ? 'remove' : 'add'}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire('Başarılı!', data.message, 'success');
                        // Butonun rengini ve metnini güncelle
                        if (isFavorited) {
                            // If it was favorited, now it's removed
                            favoriteButton.classList.remove('bg-yellow-500', 'hover:bg-yellow-600');
                            favoriteButton.classList.add('bg-gray-500', 'hover:bg-gray-600');
                            favoriteButton.innerHTML = '<i class="fas fa-heart mr-2"></i> Favorilere Ekle';
                        } else {
                            // If it wasn't favorited, now it's added
                            favoriteButton.classList.remove('bg-gray-500', 'hover:bg-gray-600');
                            favoriteButton.classList.add('bg-yellow-500', 'hover:bg-yellow-600');
                            favoriteButton.innerHTML = '<i class="fas fa-heart mr-2"></i> Favorilerden Çıkar';
                        }
                    } else {
                        Swal.fire('Hata!', data.message, 'error');
                        if (data.redirect) {
                            setTimeout(() => { window.location.href = data.redirect; }, 1500);
                        }
                    }
                })
                .catch(error => {
                    console.error('Hata:', error);
                    Swal.fire('Hata!', 'Bir sorun oluştu. Lütfen tekrar deneyin.', 'error');
                });
            });
        }

        // Sahiplenmek İstiyorum Modalı Açma
        if (sahiplenmeTalepButonu) {
            sahiplenmeTalepButonu.addEventListener('click', function() {
                if (sahiplenmeModal) {
                    sahiplenmeModal.style.display = 'flex'; // Modalı Flex ile göstererek ortala
                    // Formdaki ilan_id ve ilan_sahibi_id alanlarını gizli inputlardan doldur
                    // NOT: ilanın id ve ilan_sahibi_id'si doğrudan HTML'den alınıyor
                    // PHP ile hidden inputlara değer atandığı için doğrudan çekilebilir
                    // <input type="hidden" name="ilan_id" id="modal_ilan_id" value="<?= htmlspecialchars($ilan['id'] ?? '') ?>">
                    // <input type="hidden" name="ilan_sahibi_id" id="modal_ilan_sahibi_id" value="<?= htmlspecialchars($ilan['kullanici_id'] ?? '') ?>">
                    // Bu değerler PHP tarafından zaten atanmış durumda, tekrar atamaya gerek yok.

                    // Eğer session'da kullanıcı bilgileri varsa, form alanlarını doldur
                    <?php if (isset($_SESSION['kullanici_id'])): ?>
                        document.getElementById('talep_eden_ad_soyad').value = '<?= htmlspecialchars($_SESSION['kullanici_adi'] ?? '') ?>';
                        document.getElementById('talep_eden_email').value = '<?= htmlspecialchars($_SESSION['eposta'] ?? '') ?>';
                        document.getElementById('talep_eden_telefon').value = '<?= htmlspecialchars($_SESSION['telefon'] ?? '') ?>';
                        document.getElementById('adres').value = '<?= htmlspecialchars($_SESSION['adres'] ?? '') ?>';
                    <?php endif; ?>
                }
            });
        }

        // Modalı Kapatma Butonu
        if (closeModalButton) {
            closeModalButton.addEventListener('click', function() {
                sahiplenmeModal.style.display = 'none'; // Modalı gizle
            });
        }

        // Modal dışına tıklayınca kapatma
        if (sahiplenmeModal) {
            sahiplenmeModal.addEventListener('click', function(e) {
                if (e.target === sahiplenmeModal) {
                    sahiplenmeModal.style.display = 'none';
                }
            });
        }

        // Sahiplenme Formu Gönderimi (AJAX)
        if (sahiplenmeTalepForm) {
            sahiplenmeTalepForm.addEventListener('submit', function(e) {
                e.preventDefault(); // Formun varsayılan gönderimini engelle

                const formData = new FormData(sahiplenmeTalepForm);

                fetch('talep_olustur.php', { // Talebi işleyecek PHP dosyası
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            title: 'Başarılı!',
                            text: data.message,
                            icon: 'success'
                        }).then(() => {
                            sahiplenmeModal.style.display = 'none'; // Modalı kapat
                            sahiplenmeTalepForm.reset(); // Formu temizle
                            if (data.redirect) {
                                window.location.href = data.redirect;
                            }
                        });
                    } else {
                        Swal.fire({
                            title: 'Hata!',
                            text: data.message,
                            icon: 'error'
                        }).then(() => {
                            if (data.redirect) {
                                window.location.href = data.redirect;
                            }
                        });
                    }
                })
                .catch(error => {
                    console.error('Hata:', error);
                    Swal.fire('Hata!', 'Bir ağ hatası oluştu veya sunucuya ulaşılamıyor.', 'error');
                });
            });
        }
    });
    </script>

        </div>
    </div>

    <?php include("includes/footer.php"); ?>

</body>
</html>