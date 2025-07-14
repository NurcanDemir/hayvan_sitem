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
    <title>Taleplerim</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Sahiplenme Taleplerim</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>İlan</th>
                <th>Mesajım</th>
                <th>Tarih</th>
                <th>Durum</th>
            </tr>
        </thead>
        <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['ilan_baslik']) ?></td>
                <td><?= nl2br(htmlspecialchars($row['mesaj'])) ?></td>
                <td><?= isset($row['tarih']) ? htmlspecialchars($row['tarih']) : '' ?></td>
                <td>
                    <?php if ($row['durum'] == 'Onaylandı'): ?>
                        <span class="badge bg-success">Onaylandı</span>
                        <div class="text-success small mt-1">Tebrikler! Talebiniz onaylandı. İlan sahibi sizinle iletişime geçecek.</div>
                    <?php elseif ($row['durum'] == 'Reddedildi'): ?>
                        <span class="badge bg-danger">Reddedildi</span>
                        <div class="text-danger small mt-1">Üzgünüz, talebiniz reddedildi.</div>
                    <?php elseif ($row['durum'] == 'İletişim Kuruldu'): ?>
                        <span class="badge bg-info text-dark">İletişim Kuruldu</span>
                        <div class="text-info small mt-1">İlan sahibi sizinle iletişime geçti.</div>
                    <?php else: ?>
                        <span class="badge bg-secondary">Yeni</span>
                        <div class="small text-muted mt-1">Talebiniz beklemede.</div>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>