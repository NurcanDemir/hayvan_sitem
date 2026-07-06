<?php
// admin/includes/auth.php - Yönetici Yetkilendirme Kontrolü

// Oturum henüz başlamamışsa başlat
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// isAdmin fonksiyonu: Admin'in giriş yapıp yapmadığını kontrol eder
// Bu fonksiyon, talep_durum_guncelle.php gibi dosyalarda yetki kontrolü yapmak için kullanılacak.
function isAdmin() {
    // $_SESSION['admin_logged_in'] değişkeninin varlığını ve değerinin true olup olmadığını kontrol et
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// Eğer admin giriş yapmamışsa (veya isAdmin() false döndürüyorsa)
// Bu kısım, her sayfanın başında admin yetkisi olmayanları yönlendirmek içindir.
if (!isAdmin()) {
    // Admin giriş sayfasına yönlendir.
    // auth.php 'hayvan_sitem/includes/' içinde, admin_giris.php ise 'hayvan_sitem/admin/' içinde.
    // Bu yüzden 'includes/' içinden 'admin/' klasörüne gitmek için '../admin/' kullanıyoruz.
    header("Location: ../admin/admin_giris.php");
    exit;
}

// İsteğe bağlı: Eğer admin panelinde $_SESSION['admin_id'] veya $_SESSION['admin_kullanici_adi']'nı kullanacaksanız,
// bunların varlığını da burada kontrol edebilirsiniz, ancak sadece admin_logged_in yeterli olabilir.
?>