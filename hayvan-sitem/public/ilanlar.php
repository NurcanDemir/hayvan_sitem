<?php
// filepath: c:\xampp\htdocs\hayvan-sitem\public\ilanlar.php

session_start();
include("../includes/db.php");

// --- Aktif İlanlar Sorgusu ---
$sql_ilanlar = "
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
    WHERE i.durum = 'Aktif'
    ORDER BY i.tarih DESC
";

$result_ilanlar = $conn->query($sql_ilanlar);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İlanlar - Hayvan Dostları Platformu</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <!-- HEADER INCLUDE -->
    <?php include("../includes/header.php"); ?>

    <main class="container">
        <h1>Aktif İlanlar</h1>

        <?php if ($result_ilanlar && $result_ilanlar->num_rows > 0): ?>
            <div class="ilanlar-listesi">
                <?php while ($ilan = $result_ilanlar->fetch_assoc()): ?>
                    <div class="ilan">
                        <h2><?= htmlspecialchars($ilan['baslik']) ?></h2>
                        <p><?= htmlspecialchars(substr($ilan['aciklama'], 0, 100)) ?>...</p>
                        <a href="ilan_detay.php?id=<?= $ilan['id'] ?>">Detayları Gör</a>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p>Henüz aktif ilan bulunmamaktadır.</p>
        <?php endif; ?>
    </main>

    <!-- FOOTER INCLUDE -->
    <?php include("../includes/footer.php"); ?>

</body>
</html>

<?php
// Database bağlantısını kapat
$conn->close();
?>