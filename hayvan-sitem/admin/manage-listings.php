<?php
// filepath: c:\xampp\htdocs\hayvan_sitem\admin\manage-listings.php

session_start();
include("../includes/db.php");
include("../includes/functions.php");

// Check if the user is logged in as admin
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

// Fetch listings from the database
$sql = "
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
    ORDER BY i.tarih DESC
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İlanları Yönet - Admin Paneli</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <?php include("../includes/header.php"); ?>

    <main class="container">
        <h1>İlanları Yönet</h1>

        <?php if ($result && $result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Başlık</th>
                        <th>Açıklama</th>
                        <th>Kategori</th>
                        <th>Cins</th>
                        <th>Şehir</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($ilan = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($ilan['baslik']) ?></td>
                            <td><?= htmlspecialchars(substr($ilan['aciklama'], 0, 50)) ?>...</td>
                            <td><?= htmlspecialchars($ilan['kategori_ad']) ?></td>
                            <td><?= htmlspecialchars($ilan['cins_ad']) ?></td>
                            <td><?= htmlspecialchars($ilan['il_ad']) ?></td>
                            <td>
                                <a href="edit-listing.php?id=<?= $ilan['id'] ?>">Düzenle</a>
                                <a href="delete-listing.php?id=<?= $ilan['id'] ?>" onclick="return confirm('Bu ilanı silmek istediğinize emin misiniz?');">Sil</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Henüz ilan bulunmamaktadır.</p>
        <?php endif; ?>
    </main>

    <?php include("../includes/footer.php"); ?>

</body>
</html>

<?php
$conn->close();
?>