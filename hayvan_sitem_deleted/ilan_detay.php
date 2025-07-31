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
                kullanicilar.telefon AS kullanici_telefon, -- Kullanıcı telefonunu da çek
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
    $mesaj_tur = "danger";
}
?>

<?php include("includes/header.php"); ?>

<!-- Kendi CSS'lerini kaldır, sadece modal CSS'i kalsın -->
<style>
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.4);
        justify-content: center;
        align-items: center;
    }
    .modal-content {
        background-color: white;
        padding: 20px;
        border-radius: 8px;
        width: 90%;
        max-width: 500px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
</style>

<div class="container mx-auto p-4 mt-20"> <?php if (!empty($mesaj)): ?>
        <div class="bg-<?= $mesaj_tur == 'success' ? 'green-100' : ($mesaj_tur == 'danger' ? 'red-100' : 'yellow-100') ?> border-l-4 border-<?= $mesaj_tur == 'success' ? 'green-500' : ($mesaj_tur == 'danger' ? 'red-500' : 'yellow-500') ?> text-<?= $mesaj_tur == 'success' ? 'green-700' : ($mesaj_tur == 'danger' ? 'red-700' : 'yellow-700') ?> p-4 mb-6 rounded-md shadow-md" role="alert">
            <p class="font-bold"><?= $mesaj_tur == 'success' ? 'Başarılı!' : ($mesaj_tur == 'danger' ? 'Hata!' : 'Uyarı!') ?></p>
            <p><?= htmlspecialchars($mesaj) ?></p>
        </div>
    <?php endif; ?>

    <?php if ($ilan): ?>
    <div class="bg-white rounded-lg shadow-xl overflow-hidden md:flex mb-8">
        <div class="md:w-1/2">
            <img src="images/<?= htmlspecialchars($ilan['fotograf_url'] ?: 'placeholder.jpg') ?>"
                 alt="<?= htmlspecialchars($ilan['baslik']) ?>"
                 class="w-full h-96 object-cover object-center">
        </div>
        <div class="md:w-1/2 p-8">
            <h1 class="text-4xl font-extrabold text-koyu-pembe mb-4"><?= htmlspecialchars($ilan['baslik']) ?></h1>
            <p class="text-gray-700 text-lg mb-6 leading-relaxed"><?= nl2br(htmlspecialchars($ilan['aciklama'])) ?></p>

            <div class="grid grid-cols-2 gap-4 mb-6 text-gray-800">
                <div class="flex items-center"><i class="fas fa-paw text-koyu-yesil mr-2"></i><strong>Kategori:</strong> <?= htmlspecialchars($ilan['kategori_adi']) ?></div>
                <div class="flex items-center"><i class="fas fa-dog text-koyu-yesil mr-2"></i><strong>Cins:</strong> <?= htmlspecialchars($ilan['cins_adi']) ?></div>
                <div class="flex items-center"><i class="fas fa-thermometer-half text-koyu-yesil mr-2"></i><strong>Hastalık:</strong> <?= htmlspecialchars($ilan['hastalik_adi'] ?: 'Yok') ?></div>
                <div class="flex items-center"><i class="fas fa-calendar-alt text-koyu-yesil mr-2"></i><strong>Yaş:</strong> <?= htmlspecialchars($ilan['yas']) ?></div>
                <div class="flex items-center"><i class="fas fa-venus-mars text-koyu-yesil mr-2"></i><strong>Cinsiyet:</strong> <?= htmlspecialchars($ilan['cinsiyet']) ?></div>
                <div class="flex items-center"><i class="fas fa-cut text-koyu-yesil mr-2"></i><strong>Kısırlaştırma:</strong> <?= htmlspecialchars($ilan['kisirlastirma']) ?></div>
                <div class="flex items-center"><i class="fas fa-syringe text-koyu-yesil mr-2"></i><strong>Aşı Durumu:</strong> <?= htmlspecialchars($ilan['asi_durumu']) ?></div>
                <div class="flex items-center"><i class="fas fa-map-marker-alt text-koyu-yesil mr-2"></i><strong>Konum:</strong> <?= htmlspecialchars($ilan['il_adi']) ?> / <?= htmlspecialchars($ilan['ilce_adi']) ?></div>
                <div class="flex items-center"><i class="fas fa-user-circle text-koyu-yesil mr-2"></i><strong>İlan Sahibi:</strong> <?= htmlspecialchars($ilan['kullanici_adi']) ?></div>
                <div class="flex items-center"><i class="fas fa-calendar-plus text-koyu-yesil mr-2"></i><strong>İlan Tarihi:</strong> <?= htmlspecialchars(date('d.m.Y', strtotime($ilan['olusturma_tarihi']))) ?></div>
            </div>

            <?php if (isset($_SESSION['kullanici_id']) && $_SESSION['kullanici_id'] == $ilan['kullanici_id']): ?>
                <div class="bg-blue-100 text-blue-800 p-4 rounded-lg text-center font-semibold mt-6">
                    Bu sizin ilanınız. Talepleri "Gelen Talepler" sayfasından yönetebilirsiniz.
                </div>
                <div class="flex justify-center space-x-4 mt-6">
                    <a href="ilan_duzenle.php?id=<?= $ilan['id'] ?>" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-full transition duration-300 flex items-center">
                        <i class="fas fa-edit mr-2"></i>İlanı Düzenle
                    </a>
                    <button type="button" class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-full transition duration-300 flex items-center" onclick="confirmDelete(<?= $ilan['id'] ?>)">
                        <i class="fas fa-trash-alt mr-2"></i>İlanı Sil
                    </button>
                    <a href="ilan_sahiplenildi.php?id=<?= $ilan['id'] ?>" class="bg-koyu-yesil hover:bg-green-700 text-white font-bold py-3 px-6 rounded-full transition duration-300 flex items-center">
                        <i class="fas fa-heart mr-2"></i>Sahiplenildi Olarak İşaretle
                    </a>
                </div>
                <script>
                    function confirmDelete(ilanId) {
                        Swal.fire({
                            title: 'Emin misiniz?',
                            text: "Bu ilanı silmek istediğinizden emin misiniz? Bu işlem geri alınamaz!",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#3085d6',
                            confirmButtonText: 'Evet, Sil!',
                            cancelButtonText: 'İptal'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = 'ilan_sil.php?id=' + ilanId;
                            }
                        });
                    }
                </script>
            <?php else: ?>
                <button id="sahiplenmeTalepBtn" class="bg-koyu-pembe hover:bg-pink-700 text-white font-bold py-3 px-8 rounded-full shadow-lg transition duration-300 w-full mt-6 flex items-center justify-center">
                    <i class="fas fa-envelope mr-3"></i>Sahiplenme Talebi Gönder
                </button>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

</div>

<div id="sahiplenmeModal" class="modal">
    <div class="modal-content">
        <span class="close-button" id="closeModalButton">&times;</span>
        <h2 class="text-2xl font-bold text-koyu-pembe mb-6 text-center">Sahiplenme Talebi Gönder</h2>
        <form id="sahiplenmeTalepForm" method="POST" action="talep_olustur.php">
            <input type="hidden" name="ilan_id" value="<?= htmlspecialchars($ilan['id'] ?? '') ?>">
            <input type="hidden" name="ilan_sahibi_id" value="<?= htmlspecialchars($ilan['kullanici_id'] ?? '') ?>">

            <div class="mb-4">
                <label for="talep_eden_ad_soyad" class="block text-gray-700 text-sm font-bold mb-2">Adınız Soyadınız</label>
                <input type="text" id="talep_eden_ad_soyad" name="talep_eden_ad_soyad" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-koyu-pembe" required>
            </div>
            <div class="mb-4">
                <label for="talep_eden_telefon" class="block text-gray-700 text-sm font-bold mb-2">Telefon Numaranız</label>
                <input type="tel" id="talep_eden_telefon" name="talep_eden_telefon" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-koyu-pembe" required>
            </div>
            <div class="mb-4">
                <label for="talep_eden_email" class="block text-gray-700 text-sm font-bold mb-2">E-posta Adresiniz</label>
                <input type="email" id="talep_eden_email" name="talep_eden_email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-koyu-pembe" required>
            </div>
            <div class="mb-6">
                <label for="adres" class="block text-gray-700 text-sm font-bold mb-2">Adresiniz (İlçe/İl)</label>
                <input type="text" id="adres" name="adres" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-koyu-pembe" placeholder="Örn: Kadıköy/İstanbul" required>
            </div>
            <div class="mb-6">
                <label for="mesaj" class="block text-gray-700 text-sm font-bold mb-2">Mesajınız</label>
                <textarea id="mesaj" name="mesaj" rows="5" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-koyu-pembe" placeholder="Neden sahiplenmek istediğinizi ve yaşam koşullarınızı kısaca açıklayın." required></textarea>
            </div>

            <button type="submit" class="bg-koyu-yesil hover:bg-green-700 text-white font-bold py-2 px-4 rounded-md focus:outline-none focus:shadow-outline transition duration-300 w-full">
                Talebi Gönder
            </button>
        </form>
    </div>
</div>

<?php include("includes/footer.php"); ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sahiplenmeTalepBtn = document.getElementById('sahiplenmeTalepBtn');
        const sahiplenmeModal = document.getElementById('sahiplenmeModal');
        const closeModalButton = document.getElementById('closeModalButton');
        const sahiplenmeTalepForm = document.getElementById('sahiplenmeTalepForm');

        if (sahiplenmeTalepBtn) {
            sahiplenmeTalepBtn.addEventListener('click', function() {
                sahiplenmeModal.style.display = 'flex'; // Modalı göster
                // Eğer giriş yapmış kullanıcı ise, form alanlarını kendi bilgileriyle doldur
                <?php if (isset($_SESSION['kullanici_id'])): ?>
                    document.getElementById('talep_eden_ad_soyad').value = '<?= htmlspecialchars($_SESSION['kullanici_adi'] ?? '') ?>';
                    document.getElementById('talep_eden_email').value = '<?= htmlspecialchars($_SESSION['eposta'] ?? '') ?>';
                    <?php if (isset($_SESSION['telefon'])): // Eğer session'da telefon bilgisi varsa ?>
                        document.getElementById('talep_eden_telefon').value = '<?= htmlspecialchars($_SESSION['telefon'] ?? '') ?>';
                    <?php else: // Aksi takdirde, ilan sahibinin telefonunu (sadece örnek, genelde talep eden kendi telefonunu girer) ?>
                        document.getElementById('talep_eden_telefon').value = ''; // Kendi telefonunu girmeli
                    <?php endif; ?>
                <?php endif; ?>
            });
        }

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

        // Form gönderimi (AJAX ile)
        if (sahiplenmeTalepForm) {
            sahiplenmeTalepForm.addEventListener('submit', function(e) {
                e.preventDefault(); // Varsayılan form gönderimini engelle

                const formData = new FormData(this);

                fetch(this.action, {
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