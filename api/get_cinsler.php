<?php
include("includes/db.php");
header('Content-Type: application/json');

$kategori_id = isset($_GET['kategori_id']) ? intval($_GET['kategori_id']) : 0;

$cinsler = [];
if ($kategori_id > 0) {
    $sorgu = $conn->prepare("SELECT id, adi FROM cinsler WHERE kategori_id = ? ORDER BY adi ASC");
    $sorgu->bind_param("i", $kategori_id);
    $sorgu->execute();
    $sonuc = $sorgu->get_result();
    while ($row = $sonuc->fetch_assoc()) {
        $cinsler[] = $row;
    }
    $sorgu->close();
}
echo json_encode($cinsler);
?>