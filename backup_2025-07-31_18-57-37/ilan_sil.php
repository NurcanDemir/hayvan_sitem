<?php
session_start();
include("includes/db.php");

if (!isset($_SESSION["kullanici_id"])) {
    header("Location: giris.php");
    exit;
}

$kullanici_id = $_SESSION["kullanici_id"];
$kullanici_tipi = $_SESSION["kullanici_tipi"];

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    if ($kullanici_tipi === "admin") {
        $stmt = $conn->prepare("DELETE FROM ilanlar WHERE id = ?");
        $stmt->bind_param("i", $id);
    } else {
        $stmt = $conn->prepare("DELETE FROM ilanlar WHERE id = ? AND kullanici_id = ?");
        $stmt->bind_param("ii", $id, $kullanici_id);
    }

    if (!$stmt->execute()) {
        echo "Silme işlemi başarısız: " . $stmt->error;
        exit;
    }

    $stmt->close();

    if ($kullanici_tipi === "admin") {
        header("Location: admin.php");
    } else {
        header("Location: ilanlarim.php");
    }
    exit;
} else {
    header("Location: ilanlar.php");
    exit;
}
