<?php
session_start();
session_unset(); // Tüm oturum değişkenlerini kaldır
session_destroy(); // Oturumu sonlandır
header("Location: admin_giris.php"); // Giriş sayfasına yönlendir
exit;
?>