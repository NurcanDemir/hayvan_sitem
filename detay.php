<?php include("includes/db.php"); ?>
<?php include("includes/header.php"); ?>

<?php
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sorgu = "SELECT * FROM ilanlar WHERE id = $id";
    $sonuc = mysqli_query($conn, $sorgu);

    if ($row = mysqli_fetch_assoc($sonuc)) {
        echo "<div class='card mb-4'>";
        echo "<img src='uploads/{$row['foto']}' class='card-img-top' alt=''>";
        echo "<div class='card-body'>";
        echo "<h4 class='card-title'>" . htmlspecialchars($row['baslik']) . "</h4>";
        echo "<p><strong>Tür:</strong> {$row['tur']}</p>";
        echo "<p><strong>İl:</strong> {$row['il']}</p>";
        echo "<p class='card-text'>" . nl2br(htmlspecialchars($row['aciklama'])) . "</p>";
        echo "<p class='text-muted'><em>İlan Tarihi: {$row['tarih']}</em></p>";
        echo "</div></div>";
    } else {
        echo "<div class='alert alert-warning'>İlan bulunamadı.</div>";
    }
} else {
    echo "<div class='alert alert-danger'>Geçersiz istek.</div>";
}
?>
