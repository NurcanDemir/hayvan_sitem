<?php
session_start();
include("includes/db.php");
$kullanici_id = $_SESSION['kullanici_id'] ?? 0;

$sql = "SELECT s.*, i.baslik AS ilan_baslik
        FROM sahiplenme_istekleri s
        LEFT JOIN ilanlar i ON s.ilan_id = i.id
        WHERE s.talep_eden_kullanici_id = ?
        ORDER BY s.id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $kullanici_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Taleplerim</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <link href="./dist/output.css" rel="stylesheet">
</head>
<body class="bg-gray-50 font-sans min-h-screen flex flex-col">

<?php include("includes/header.php"); ?>

<div class="container mx-auto p-4 flex-grow pt-20">
    <div class="max-w-6xl mx-auto bg-white p-8 rounded-lg shadow-xl mt-8">
        <h2 class="text-3xl font-bold text-center mb-8">Sahiplenme Taleplerim</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full table-auto border-collapse border border-gray-300">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border border-gray-300 px-4 py-2 text-left">İlan</th>
                        <th class="border border-gray-300 px-4 py-2 text-left">Mesajım</th>
                        <th class="border border-gray-300 px-4 py-2 text-left">Tarih</th>
                        <th class="border border-gray-300 px-4 py-2 text-left">Durum</th>
                    </tr>
                </thead>
                <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
            <tr class="hover:bg-gray-50">
                <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($row['ilan_baslik']) ?></td>
                <td class="border border-gray-300 px-4 py-2"><?= nl2br(htmlspecialchars($row['mesaj'])) ?></td>
                <td class="border border-gray-300 px-4 py-2"><?= isset($row['tarih']) ? htmlspecialchars($row['tarih']) : '' ?></td>
                <td class="border border-gray-300 px-4 py-2">
                    <?php if ($row['durum'] == 'Onaylandı'): ?>
                        <span class="inline-block bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded">Onaylandı</span>
                        <div class="text-green-600 text-sm mt-1">Tebrikler! Talebiniz onaylandı. İlan sahibi sizinle iletişime geçecek.</div>
                    <?php elseif ($row['durum'] == 'Reddedildi'): ?>
                        <span class="inline-block bg-red-100 text-red-800 text-xs font-semibold px-2.5 py-0.5 rounded">Reddedildi</span>
                        <div class="text-red-600 text-sm mt-1">Üzgünüz, talebiniz reddedildi.</div>
                    <?php elseif ($row['durum'] == 'İletişim Kuruldu'): ?>
                        <span class="inline-block bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded">İletişim Kuruldu</span>
                        <div class="text-blue-600 text-sm mt-1">İlan sahibi sizinle iletişime geçti.</div>
                    <?php else: ?>
                        <span class="inline-block bg-gray-100 text-gray-800 text-xs font-semibold px-2.5 py-0.5 rounded">Yeni</span>
                        <div class="text-gray-600 text-sm mt-1">Talebiniz beklemede.</div>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
        </div>
    </div>
</div>

<?php include("includes/footer.php"); ?>
</body>
</html>