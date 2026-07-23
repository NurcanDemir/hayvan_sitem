<?php
// filepath: c:\xampp\htdocs\hayvan-sitem\public\ilan_ekle.php

session_start();
include("../includes/db.php");
include("../includes/functions.php");

// Formdan gelen değerler
$baslik = $_POST['baslik'] ?? '';
$aciklama = $_POST['aciklama'] ?? '';
$cins_id = $_POST['cins_id'] ?? '';
$hastalik_id = $_POST['hastalik_id'] ?? '';
$kategori_id = $_POST['kategori_id'] ?? '';
$il_id = $_POST['il_id'] ?? '';
$ilce_id = $_POST['ilce_id'] ?? '';
$durum = 'Aktif';

// Eğer form gönderilmişse
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Veritabanına ekleme işlemi
    $sql = "INSERT INTO ilanlar (baslik, aciklama, cins_id, hastalik_id, kategori_id, il_id, ilce_id, durum) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssiiisss", $baslik, $aciklama, $cins_id, $hastalik_id, $kategori_id, $il_id, $ilce_id, $durum);

    if ($stmt->execute()) {
        $_SESSION['message'] = "İlan başarıyla eklendi.";
        header("Location: ilanlar.php");
        exit();
    } else {
        $_SESSION['error'] = "İlan eklenirken bir hata oluştu.";
    }
}

$cinsler = getCinsler($conn);
$hastaliklar = getHastaliklar($conn);
$kategoriler = getKategoriler($conn);
$iller = getIller($conn);
$ilceler = getIlceler($conn);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İlan Ekle - Yuva Ol</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <?php include("../includes/header.php"); ?>

    <main class="container">
        <h1>Yeni İlan Ekle</h1>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div>
                <label for="baslik">Başlık:</label>
                <input type="text" name="baslik" id="baslik" required>
            </div>
            <div>
                <label for="aciklama">Açıklama:</label>
                <textarea name="aciklama" id="aciklama" required></textarea>
            </div>
            <div>
                <label for="cins_id">Cins:</label>
                <select name="cins_id" id="cins_id" required>
                    <?php foreach ($cinsler as $cins): ?>
                        <option value="<?= $cins['id']; ?>"><?= htmlspecialchars($cins['ad']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="hastalik_id">Hastalık:</label>
                <select name="hastalik_id" id="hastalik_id" required>
                    <?php foreach ($hastaliklar as $hastalik): ?>
                        <option value="<?= $hastalik['id']; ?>"><?= htmlspecialchars($hastalik['ad']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="kategori_id">Kategori:</label>
                <select name="kategori_id" id="kategori_id" required>
                    <?php foreach ($kategoriler as $kategori): ?>
                        <option value="<?= $kategori['id']; ?>"><?= htmlspecialchars($kategori['ad']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="il_id">İl:</label>
                <select name="il_id" id="il_id" required>
                    <?php foreach ($iller as $il): ?>
                        <option value="<?= $il['id']; ?>"><?= htmlspecialchars($il['ad']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="ilce_id">İlçe:</label>
                <select name="ilce_id" id="ilce_id" required>
                    <?php foreach ($ilceler as $ilce): ?>
                        <option value="<?= $ilce['id']; ?>"><?= htmlspecialchars($ilce['ad']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit">İlanı Ekle</button>
        </form>
    </main>

    <?php include("../includes/footer.php"); ?>

</body>
</html>

<?php
$conn->close();
?>