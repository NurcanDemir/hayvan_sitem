<?php
$host = "localhost";
$kullanici = "root";
$sifre = "";
$veritabani = "hayvan_sitem";

$conn = mysqli_connect($host, $kullanici, $sifre, $veritabani);
if (!$conn) {
    die("Veritabanı bağlantı hatası: " . mysqli_connect_error());
}
?>
