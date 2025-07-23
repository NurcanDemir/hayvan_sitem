
<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
include '../includes/db.php';

// Hata raporlamayı aç
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Haversine formülü ile mesafe hesaplama
function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371; // km
    
    $latDelta = deg2rad($lat2 - $lat1);
    $lonDelta = deg2rad($lon2 - $lon1);
    
    $a = sin($latDelta / 2) * sin($latDelta / 2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($lonDelta / 2) * sin($lonDelta / 2);
    
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    
    return $earthRadius * $c;
}

try {
    // Kullanıcı koordinatlarını al
    $userLat = isset($_GET['latitude']) ? floatval($_GET['latitude']) : (isset($_GET['lat']) ? floatval($_GET['lat']) : null);
    $userLon = isset($_GET['longitude']) ? floatval($_GET['longitude']) : (isset($_GET['lon']) ? floatval($_GET['lon']) : null);
    
    // Debug için parametreleri kontrol et
    error_log("API çağrısı - User Lat: " . $userLat . ", User Lon: " . $userLon);
    
    // Tablo varlığını kontrol et
    $table_check = $conn->query("SHOW TABLES LIKE 'hayvan_barinaklari'");
    if ($table_check->num_rows == 0) {
        echo json_encode([
            'success' => false,
            'error' => 'hayvan_barinaklari tablosu bulunamadı. Lütfen check_and_create_tables.php dosyasını çalıştırın.',
            'shelters' => []
        ]);
        exit;
    }
    
    // Tüm barınakları getir
    $sql = "SELECT id, ad, adres, telefon, email, website, latitude, longitude, aciklama FROM hayvan_barinaklari WHERE aktif = 1 ORDER BY ad";
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception("Sorgu hatası: " . $conn->error);
    }
    
    $shelters = [];
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $shelter = [
                'id' => $row['id'],
                'ad' => $row['ad'],
                'adres' => $row['adres'],
                'telefon' => $row['telefon'],
                'email' => $row['email'],
                'website' => $row['website'],
                'latitude' => floatval($row['latitude']),
                'longitude' => floatval($row['longitude']),
                'aciklama' => $row['aciklama']
            ];
            
            // Eğer kullanıcı konumu varsa mesafe hesapla
            if ($userLat && $userLon && $row['latitude'] && $row['longitude']) {
                $distance = calculateDistance($userLat, $userLon, $row['latitude'], $row['longitude']);
                $shelter['distance'] = round($distance, 1);
            }
            
            $shelters[] = $shelter;
        }
        
        // Eğer kullanıcı konumu varsa mesafeye göre sırala
        if ($userLat && $userLon) {
            // Mesafeye göre sırala
            usort($shelters, function($a, $b) {
                $aDistance = isset($a['distance']) ? $a['distance'] : 999;
                $bDistance = isset($b['distance']) ? $b['distance'] : 999;
                return $aDistance <=> $bDistance;
            });
            
            // Sadece 50km yakınındakileri al
            $shelters = array_filter($shelters, function($shelter) {
                return isset($shelter['distance']) && $shelter['distance'] <= 50;
            });
            
            // En fazla 10 barınak
            $shelters = array_slice($shelters, 0, 10);
        } else {
            // Konum yoksa ilk 5 barınağı al
            $shelters = array_slice($shelters, 0, 5);
        }
    }
    
    echo json_encode([
        'success' => true,
        'shelters' => array_values($shelters),
        'total' => count($shelters),
        'debug' => [
            'userLat' => $userLat,
            'userLon' => $userLon,
            'totalFound' => $result->num_rows
        ]
    ]);

} catch (Exception $e) {
    error_log("API Hatası: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'shelters' => [],
        'debug' => [
            'error_details' => $e->getTraceAsString()
        ]
    ]);
}
?>