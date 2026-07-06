<?php
// admin/raporlar.php
session_start();
include("../includes/auth.php"); // Yetkilendirme kontrolünü dahil et
include("../includes/db.php"); // Veritabanı bağlantısını dahil et

// admin_header.php dosyasını dahil et
include("includes/admin_header.php"); 

// Kategoriye Göre İlan Dağılımı
$kategori_dagilimi = [];
$stmt_kategori = $conn->prepare("SELECT kategori, COUNT(*) AS ilan_sayisi FROM ilanlar GROUP BY kategori ORDER BY ilan_sayisi DESC");
$stmt_kategori->execute();
$result_kategori = $stmt_kategori->get_result();
while ($row = $result_kategori->fetch_assoc()) {
    $kategori_dagilimi[] = $row;
}
$stmt_kategori->close();

// En Çok Favorilenen İlanlar (İlk 10)
$en_cok_favorilenenler = [];
$stmt_favori = $conn->prepare("
    SELECT 
        i.id, 
        i.baslik, 
        i.foto, 
        COUNT(f.id) AS favori_sayisi 
    FROM ilanlar i
    JOIN favoriler f ON i.id = f.ilan_id
    GROUP BY i.id, i.baslik, i.foto
    ORDER BY favori_sayisi DESC
    LIMIT 10
");
$stmt_favori->execute();
$result_favori = $stmt_favori->get_result();
while ($row = $result_favori->fetch_assoc()) {
    $en_cok_favorilenenler[] = $row;
}
$stmt_favori->close();

?>

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Raporlar</h1>
    </div>
    
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Kategoriye Göre İlan Dağılımı</h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($kategori_dagilimi)): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Kategori</th>
                                        <th>İlan Sayısı</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($kategori_dagilimi as $data): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($data['kategori']) ?></td>
                                            <td><?= $data['ilan_sayisi'] ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p>Henüz kategoriye göre ilan verisi bulunmamaktadır.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">En Çok Favorilenen İlanlar (İlk 10)</h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($en_cok_favorilenenler)): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>İlan Başlığı</th>
                                        <th>Favori Sayısı</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($en_cok_favorilenenler as $data): ?>
                                        <tr>
                                            <td><a href="../ilan_detay.php?id=<?= $data['id'] ?>" target="_blank"><?= htmlspecialchars($data['baslik']) ?></a></td>
                                            <td><?= $data['favori_sayisi'] ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p>Henüz favorilenen ilan verisi bulunmamaktadır.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

<?php include("includes/admin_footer.php"); // Footer dosyasını dahil et ?>