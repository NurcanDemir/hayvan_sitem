<?php
// filepath: c:\xampp\htdocs\hayvan_sitem\admin\dashboard.php

session_start();
include("../includes/db.php");
include("../includes/config.php");

// Check if the user is logged in as an admin
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

// Fetch statistics or data for the dashboard
$sql_total_listings = "SELECT COUNT(*) as total FROM ilanlar WHERE durum = 'Aktif'";
$result_total_listings = $conn->query($sql_total_listings);
$total_listings = $result_total_listings->fetch_assoc()['total'];

$sql_total_adopted = "SELECT COUNT(*) as total FROM ilanlar WHERE durum = 'sahiplenildi'";
$result_total_adopted = $conn->query($sql_total_adopted);
$total_adopted = $result_total_adopted->fetch_assoc()['total'];

$sql_total_users = "SELECT COUNT(*) as total FROM users";
$result_total_users = $conn->query($sql_total_users);
$total_users = $result_total_users->fetch_assoc()['total'];

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Hayvan Dostları Platformu</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <?php include("../includes/header.php"); ?>

    <main class="container mx-auto px-4 py-8 mt-16">
        <h1 class="text-3xl font-bold mb-6">Admin Dashboard</h1>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white p-4 rounded shadow">
                <h2 class="text-xl font-semibold">Toplam İlan</h2>
                <p class="text-2xl"><?= htmlspecialchars($total_listings) ?></p>
            </div>
            <div class="bg-white p-4 rounded shadow">
                <h2 class="text-xl font-semibold">Sahiplenilen İlan</h2>
                <p class="text-2xl"><?= htmlspecialchars($total_adopted) ?></p>
            </div>
            <div class="bg-white p-4 rounded shadow">
                <h2 class="text-xl font-semibold">Toplam Kullanıcı</h2>
                <p class="text-2xl"><?= htmlspecialchars($total_users) ?></p>
            </div>
        </div>

        <div class="mt-8">
            <h2 class="text-2xl font-bold mb-4">Son Aktif İlanlar</h2>
            <table class="min-w-full bg-white border border-gray-300">
                <thead>
                    <tr>
                        <th class="border px-4 py-2">Başlık</th>
                        <th class="border px-4 py-2">Durum</th>
                        <th class="border px-4 py-2">Tarih</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql_recent_listings = "SELECT baslik, durum, tarih FROM ilanlar WHERE durum = 'Aktif' ORDER BY tarih DESC LIMIT 5";
                    $result_recent_listings = $conn->query($sql_recent_listings);
                    while ($row = $result_recent_listings->fetch_assoc()) {
                        echo "<tr>
                                <td class='border px-4 py-2'>" . htmlspecialchars($row['baslik']) . "</td>
                                <td class='border px-4 py-2'>" . htmlspecialchars($row['durum']) . "</td>
                                <td class='border px-4 py-2'>" . htmlspecialchars($row['tarih']) . "</td>
                              </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </main>

    <?php include("../includes/footer.php"); ?>

</body>
</html>

<?php
$conn->close();
?>