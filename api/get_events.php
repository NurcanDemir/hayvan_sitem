<?php
// api/get_events.php - Hayvan etkinliklerini getir
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// Veritabanı bağlantısı
include('../includes/db.php');

$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 6;
$type = isset($_GET['type']) ? $_GET['type'] : 'all';

try {
    $sql = "
        SELECT 
            id,
            baslik,
            aciklama,
            etkinlik_tarihi,
            bitis_tarihi,
            adres,
            organizator,
            iletisim_telefon,
            website,
            resim,
            etkinlik_turu,
            olusturma_tarihi
        FROM hayvan_etkinlikleri 
        WHERE durum = 'yayin' 
        AND etkinlik_tarihi >= NOW()
    ";

    if ($type !== 'all') {
        $sql .= " AND etkinlik_turu = ?";
    }

    $sql .= " ORDER BY etkinlik_tarihi ASC LIMIT ?";

    $stmt = $conn->prepare($sql);
    
    if ($type !== 'all') {
        $stmt->bind_param("si", $type, $limit);
    } else {
        $stmt->bind_param("i", $limit);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();

    $events = [];
    while ($row = $result->fetch_assoc()) {
        // Tarih formatlaması
        $row['formatted_date'] = date('d.m.Y H:i', strtotime($row['etkinlik_tarihi']));
        $row['short_date'] = date('d M', strtotime($row['etkinlik_tarihi']));
        $row['time'] = date('H:i', strtotime($row['etkinlik_tarihi']));
        
        // Etkinlik türü rengi
        $colors = [
            'sahiplendirme' => 'emerald',
            'egitim' => 'blue',
            'bagis' => 'amber',
            'tedavi' => 'red',
            'sosyal' => 'purple',
            'yarışma' => 'orange',
            'sergi' => 'pink',
            'diğer' => 'gray'
        ];
        $row['color'] = $colors[$row['etkinlik_turu']] ?? 'gray';
        
        // Etkinlik türü Türkçe
        $types = [
            'sahiplendirme' => 'Sahiplendirme',
            'egitim' => 'Eğitim',
            'bagis' => 'Bağış',
            'tedavi' => 'Tedavi',
            'sosyal' => 'Sosyal',
            'yarışma' => 'Yarışma',
            'sergi' => 'Sergi',
            'diğer' => 'Diğer'
        ];
        $row['etkinlik_turu_tr'] = $types[$row['etkinlik_turu']] ?? 'Diğer';
        
        // Kaç gün kaldı
        $eventDate = new DateTime($row['etkinlik_tarihi']);
        $today = new DateTime();
        $interval = $today->diff($eventDate);
        
        if ($interval->days == 0) {
            $row['days_until'] = 'Bugün';
        } elseif ($interval->days == 1) {
            $row['days_until'] = 'Yarın';
        } elseif ($interval->days <= 7) {
            $row['days_until'] = $interval->days . ' gün sonra';
        } else {
            $row['days_until'] = $interval->days . ' gün sonra';
        }
        
        $events[] = $row;
    }

    echo json_encode($events, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode(['error' => 'Veritabanı hatası: ' . $e->getMessage()]);
}

$conn->close();
?>
