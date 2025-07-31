<?php
include("includes/db.php");
header('Content-Type: application/json');

$cins_id = isset($_GET['cins_id']) ? intval($_GET['cins_id']) : 0;

$hastaliklar = [];
if ($cins_id > 0) {
    $sorgu = $conn->prepare("SELECT id, adi FROM hastaliklar WHERE cins_id = ? ORDER BY adi ASC");
    $sorgu->bind_param("i", $cins_id);
    $sorgu->execute();
    $sonuc = $sorgu->get_result();
    while ($row = $sonuc->fetch_assoc()) {
        $hastaliklar[] = $row;
    }
    $sorgu->close();
}
echo json_encode($hastaliklar);
?>