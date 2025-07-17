<?php
// admin_session_fix.php - Admin session sorunlarını düzelt

session_start();

// Admin session değişkenlerini kontrol et ve düzelt
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    echo "<p>Admin girişi yapılmamış. <a href='admin/admin_giris.php'>Giriş yapın</a></p>";
    exit;
}

// Admin ID'sini session'a ekle (eğer yoksa)
if (!isset($_SESSION['admin_id'])) {
    $_SESSION['admin_id'] = 1; // Varsayılan admin ID
}

// Admin bilgilerini session'a ekle (eğer yoksa)
if (!isset($_SESSION['admin_ad']) || !isset($_SESSION['admin_soyad'])) {
    include('includes/db.php');
    
    $admin_id = $_SESSION['admin_id'];
    $stmt = $conn->prepare("SELECT ad, soyad FROM admin WHERE id = ?");
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        $_SESSION['admin_ad'] = $admin['ad'];
        $_SESSION['admin_soyad'] = $admin['soyad'];
    } else {
        $_SESSION['admin_ad'] = 'Admin';
        $_SESSION['admin_soyad'] = 'User';
    }
}

echo "<h2>Admin Session Bilgileri</h2>";
echo "<p><strong>Admin ID:</strong> " . $_SESSION['admin_id'] . "</p>";
echo "<p><strong>Admin Adı:</strong> " . $_SESSION['admin_ad'] . " " . $_SESSION['admin_soyad'] . "</p>";
echo "<p><strong>Giriş Durumu:</strong> " . ($_SESSION['admin_logged_in'] ? 'Giriş Yapılmış' : 'Giriş Yapılmamış') . "</p>";
echo "<p><a href='admin/admin_panel.php'>Admin Paneline Git</a></p>";
?>
