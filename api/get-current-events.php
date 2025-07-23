
<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
include '../includes/db.php';

// Hata raporlamayı aç
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    // Tablo varlığını kontrol et
    $table_check = $conn->query("SHOW TABLES LIKE 'hayvan_etkinlikleri'");
    if ($table_check->num_rows == 0) {
        echo json_encode([
            'success' => false,
            'error' => 'hayvan_etkinlikleri tablosu bulunamadı. Lütfen check_and_create_tables.php dosyasını çalıştırın.',
            'events' => []
        ]);
        exit;
    }
    
    // Yaklaşan etkinlikleri getir (bugünden itibaren)
    $sql = "SELECT e.*, i.ad as il_ad, ilc.ad as ilce_ad 
            FROM hayvan_etkinlikleri e 
            LEFT JOIN il i ON e.il_id = i.id 
            LEFT JOIN ilce ilc ON e.ilce_id = ilc.id 
            WHERE e.aktif = 1 AND e.etkinlik_tarihi >= CURDATE() 
            ORDER BY e.etkinlik_tarihi ASC, e.etkinlik_saati ASC 
            LIMIT 10";
    
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception("Sorgu hatası: " . $conn->error);
    }
    
    $events = [];
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $events[] = [
                'id' => $row['id'],
                'baslik' => $row['baslik'],
                'aciklama' => $row['aciklama'],
                'etkinlik_tarihi' => $row['etkinlik_tarihi'],
                'etkinlik_saati' => $row['etkinlik_saati'],
                'adres' => $row['adres'],
                'il_ad' => $row['il_ad'],
                'ilce_ad' => $row['ilce_ad'],
                'resim' => $row['resim'],
                'kategori' => $row['kategori'],
                'created_at' => $row['created_at']
            ];
        }
    }
    
    echo json_encode([
        'success' => true,
        'events' => $events,
        'total' => count($events),
        'debug' => [
            'totalFound' => $result->num_rows,
            'sql' => $sql
        ]
    ]);

} catch (Exception $e) {
    error_log("Etkinlik API Hatası: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'events' => [],
        'debug' => [
            'error_details' => $e->getTraceAsString()
        ]
    ]);
}
?>