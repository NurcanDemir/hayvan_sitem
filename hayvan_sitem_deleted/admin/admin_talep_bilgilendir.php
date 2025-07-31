<?php
session_start();

// Yetkilendirme kontrolünü ve veritabanı bağlantısını doğru yollarla dahil edin
include("../includes/auth.php"); // auth.php'yi kullanmak için
include("../includes/db.php"); // db.php yolu düzeltildi

// Bu sayfa genellikle bir işlem yaptıktan sonra başka bir sayfaya yönlendirme yapar.
// Eğer bir POST işlemi veya belirli bir mantık yoksa, sadece yönlendirme yapması yeterlidir.

if (isset($_GET['id'])) {
    $talep_id = intval($_GET['id']);
    // Buraya talep_id ile ilgili bir işlem (örneğin, talebin durumunu güncelleme) gelebilir.
    // Ancak gönderdiğiniz kodda direkt sahiplenme_talepleri.php'ye yönlendirme var.

    // Örnek: Eğer talep_id ile bir güncelleme yapılsa idi:
    // $stmt = $conn->prepare("UPDATE your_table SET status = 'read' WHERE id = ?");
    // $stmt->bind_param("i", $talep_id);
    // $stmt->execute();
    // if ($stmt) { $stmt->close(); } // $stmt null değilse kapat

    $_SESSION['mesaj'] = "Talep ID: " . $talep_id . " işlendi (örnek)."; // Sadece örnek mesaj
    $_SESSION['mesaj_tipi'] = "success";
} else {
    $_SESSION['mesaj'] = "Geçersiz talep ID.";
    $_SESSION['mesaj_tipi'] = "danger";
}

// Veritabanı bağlantısını kapatma (genellikle db.php'nin sonunda veya daha sonra yapılır)
// Eğer her sayfa sonunda otomatik kapanıyorsa veya daha merkezi bir yerde yönetiliyorsa gerek kalmayabilir.
if (isset($conn) && $conn) {
    $conn->close();
}

// Admin panelindeki bir sayfaya yönlendirme yapın, örneğin sahiplenme_talepleri.php
header("Location: sahiplenme_talepleri.php");
exit;
?>