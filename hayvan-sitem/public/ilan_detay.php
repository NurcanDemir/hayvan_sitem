<?php
// filepath: /hayvan-sitem/hayvan-sitem/public/ilan_detay.php

session_start();
include("../includes/db.php");

// İlan detayını almak için gerekli ID
$ilan_id = $_GET['id'] ?? null;

if (!$ilan_id) {
    header("Location: ilanlar.php");
    exit;
}

// İlan detayını sorgulama
$sql_ilan_detay = "
    SELECT
        i.*,
        c.ad AS cins_ad,
        h.ad AS hastalik_ad,
        k.ad AS kategori_ad,
        il.ad AS il_ad,
        ilce.ad AS ilce_ad
    FROM ilanlar i
    LEFT JOIN cinsler c ON i.cins_id = c.id
    LEFT JOIN hastaliklar h ON i.hastalik_id = h.id
    LEFT JOIN kategoriler k ON i.kategori_id = k.id
    LEFT JOIN il il ON i.il_id = il.id
    LEFT JOIN ilce ilce ON i.ilce_id = ilce.id
    WHERE i.id = ? AND i.durum = 'Aktif'
";

$stmt_ilan_detay = $conn->prepare($sql_ilan_detay);
$stmt_ilan_detay->bind_param("i", $ilan_id);
$stmt_ilan_detay->execute();
$result_ilan_detay = $stmt_ilan_detay->get_result();

if ($result_ilan_detay->num_rows === 0) {
    header("Location: ilanlar.php");
    exit;
}

$ilan = $result_ilan_detay->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($ilan['baslik']) ?> - Yuva Ol</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <?php include("../includes/header.php"); ?>

    <main class="container mx-auto px-4 py-8 mt-16">
        <h1 class="text-3xl font-bold mb-4"><?= htmlspecialchars($ilan['baslik']) ?></h1>
        <p class="text-gray-600 mb-4"><?= htmlspecialchars($ilan['aciklama']) ?></p>
        
        <div class="flex flex-wrap gap-4 mb-4">
            <?php if (!empty($ilan['kategori_ad'])): ?>
                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-sm">
                    <?= htmlspecialchars($ilan['kategori_ad']) ?>
                </span>
            <?php endif; ?>
            <?php if (!empty($ilan['cins_ad'])): ?>
                <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-sm">
                    <?= htmlspecialchars($ilan['cins_ad']) ?>
                </span>
            <?php endif; ?>
            <?php if (!empty($ilan['il_ad'])): ?>
                <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded-full text-sm">
                    <i class="fas fa-map-marker-alt mr-1"></i><?= htmlspecialchars($ilan['il_ad']) ?>
                </span>
            <?php endif; ?>
        </div>

        <div class="mb-6">
            <h3 class="text-xl font-bold">İletişim Bilgileri</h3>
            <p><?= htmlspecialchars($ilan['iletisim_bilgileri']) ?></p>
        </div>

        <a href="ilanlar.php" class="bg-pink-600 hover:bg-pink-700 text-white font-bold py-3 px-6 rounded-lg transition duration-300">
            Geri Dön
        </a>
    </main>

    <?php include("../includes/footer.php"); ?>

</body>
</html>

<?php
$stmt_ilan_detay->close();
$conn->close();
?>