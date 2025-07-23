<?php
// api/get_nearby_shelters.php - Yakındaki barınakları getir
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Veritabanı bağlantısı
include('../includes/db.php');

// POST verilerini al
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['lat']) || !isset($input['lng'])) {
    echo json_encode(['error' => 'Geçersiz konum verisi']);
    exit;
}

$userLat = floatval($input['lat']);
$userLng = floatval($input['lng']);
$maxDistance = isset($input['maxDistance']) ? intval($input['maxDistance']) : 50; // km

try {
    // Haversine formülü ile yakındaki barınakları getir
    $sql = "
        SELECT 
            id,
            ad,
            adres,
            telefon,
            email,
            website,
            lat,
            lng,
            aciklama,
            kapasite,
            aktif_hayvan_sayisi,
            calisma_saatleri,
            ozellikler,
            resim,
            ( 6371 * ACOS( 
                COS( RADIANS(?) ) * 
                COS( RADIANS(lat) ) * 
                COS( RADIANS(lng) - RADIANS(?) ) + 
                SIN( RADIANS(?) ) * 
                SIN( RADIANS(lat) )
            ) ) AS mesafe_km
        FROM hayvan_barinaklari 
        WHERE durum = 'aktif'
        HAVING mesafe_km <= ?
        ORDER BY mesafe_km ASC
        LIMIT 20
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("dddd", $userLat, $userLng, $userLat, $maxDistance);
    $stmt->execute();
    $result = $stmt->get_result();

    $shelters = [];
    while ($row = $result->fetch_assoc()) {
        // JSON özellikleri decode et
        if ($row['ozellikler']) {
            $row['ozellikler'] = json_decode($row['ozellikler'], true);
        }
        
        // Mesafeyi round et
        $row['mesafe_km'] = round($row['mesafe_km'], 1);
        
        $shelters[] = $row;
    }

    echo json_encode($shelters, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode(['error' => 'Veritabanı hatası: ' . $e->getMessage()]);
}

$conn->close();
?>
