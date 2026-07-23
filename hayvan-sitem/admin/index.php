<?php
// filepath: c:\xampp\htdocs\hayvan_sitem\admin\index.php

session_start();
include("../includes/db.php");
include("../includes/config.php");

// Check if the user is logged in as an admin
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

// Fetch some statistics for the dashboard
$sql_stats = "
    SELECT COUNT(*) AS total_listings FROM ilanlar WHERE durum = 'Aktif';
";
$result_stats = $conn->query($sql_stats);
$total_listings = $result_stats->fetch_assoc()['total_listings'];

// Fetch recent listings
$sql_recent_listings = "
    SELECT * FROM ilanlar ORDER BY tarih DESC LIMIT 5;
";
$result_recent_listings = $conn->query($sql_recent_listings);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Yuva Ol</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <h1>Admin Dashboard</h1>
        <nav>
            <a href="index.php">Ana Sayfa</a>
            <a href="manage-listings.php">İlanları Yönet</a>
            <a href="logout.php">Çıkış</a>
        </nav>
    </header>

    <main>
        <h2>İstatistikler</h2>
        <p>Toplam Aktif İlan: <?= htmlspecialchars($total_listings) ?></p>

        <h2>Son İlanlar</h2>
        <ul>
            <?php while ($ilan = $result_recent_listings->fetch_assoc()): ?>
                <li>
                    <a href="../ilan_detay.php?id=<?= $ilan['id'] ?>">
                        <?= htmlspecialchars($ilan['baslik']) ?>
                    </a>
                </li>
            <?php endwhile; ?>
        </ul>
    </main>

    <footer>
        <p>&copy; <?= date("Y") ?> Yuva Ol. Tüm hakları saklıdır.</p>
    </footer>
</body>
</html>

<?php
$conn->close();
?>