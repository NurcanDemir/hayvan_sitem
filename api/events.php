<?php
// api/events.php - Dinamik Hayvan Etkinlikleri API Uç Noktası
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once __DIR__ . '/../includes/db.php';

$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;

$kategori = isset($_GET['kategori']) ? trim($_GET['kategori']) : (isset($_GET['category']) ? trim($_GET['category']) : '');
$il_id = isset($_GET['il_id']) ? intval($_GET['il_id']) : (isset($_GET['city']) ? intval($_GET['city']) : 0);
$zaman = isset($_GET['zaman']) ? trim($_GET['zaman']) : (isset($_GET['time']) ? trim($_GET['time']) : 'gelecek');

$where_conditions = ["(e.aktif = 1 OR e.durum = 'yayin')"];
$params = [];
$types = "";

// Kategori Filtresi
if (!empty($kategori) && $kategori !== 'all') {
    $where_conditions[] = "e.kategori = ?";
    $params[] = $kategori;
    $types .= "s";
}

// İl Filtresi
if ($il_id > 0) {
    $where_conditions[] = "e.il_id = ?";
    $params[] = $il_id;
    $types .= "i";
}

// Zaman Filtresi
switch ($zaman) {
    case 'bugun':
        $where_conditions[] = "DATE(e.etkinlik_tarihi) = CURDATE()";
        break;
    case 'bu_hafta':
        $where_conditions[] = "YEARWEEK(e.etkinlik_tarihi) = YEARWEEK(CURDATE())";
        break;
    case 'gecmis':
        $where_conditions[] = "e.etkinlik_tarihi < CURDATE()";
        break;
    case 'gelecek':
    default:
        $where_conditions[] = "e.etkinlik_tarihi >= CURDATE()";
        break;
}

$where_clause = implode(' AND ', $where_conditions);

try {
    $sql = "
        SELECT 
            e.id,
            e.baslik AS title,
            e.aciklama AS description,
            e.detay_aciklama,
            e.etkinlik_tarihi AS event_date,
            e.etkinlik_saati,
            e.adres AS location_detail,
            e.il_id,
            il.ad AS city_name,
            e.resim AS image_url,
            e.kategori AS category,
            e.organizator AS organizer,
            e.iletisim_telefon,
            e.iletisim_email,
            (SELECT COUNT(*) FROM event_subscriptions sub WHERE sub.event_id = e.id AND sub.is_active = 1) AS subscriber_count
        FROM hayvan_etkinlikleri e 
        LEFT JOIN il ON e.il_id = il.id
        WHERE $where_clause
        ORDER BY e.etkinlik_tarihi ASC
        LIMIT ? OFFSET ?
    ";

    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $events = [];
    $category_names = [
        'sahiplendirme' => '💕 Sahiplendirme',
        'saglik' => '🏥 Sağlık',
        'egitim' => '📚 Eğitim',
        'bagis' => '🎁 Bağış',
        'diger' => '🌟 Diğer'
    ];

    $category_colors = [
        'sahiplendirme' => 'cat-sahiplendirme',
        'saglik' => 'cat-saglik',
        'egitim' => 'cat-egitim',
        'bagis' => 'cat-bagis',
        'diger' => 'cat-diger'
    ];

    while ($row = $result->fetch_assoc()) {
        $eventDate = new DateTime($row['event_date']);
        $today = new DateTime();

        $row['formatted_date'] = date('d.m.Y', strtotime($row['event_date']));
        $row['short_date'] = date('d M', strtotime($row['event_date']));
        $row['time'] = $row['etkinlik_saati'] ? substr($row['etkinlik_saati'], 0, 5) : '10:00';
        
        $row['category_tr'] = $category_names[$row['category']] ?? '🌟 Diğer';
        $row['category_color'] = $category_colors[$row['category']] ?? 'cat-diger';
        
        $interval = $today->diff($eventDate);
        if ($row['event_date'] < date('Y-m-d')) {
            $row['days_until'] = 'Geçmiş Etkinlik';
        } elseif ($interval->days == 0) {
            $row['days_until'] = '🔥 Bugün';
        } elseif ($interval->days == 1) {
            $row['days_until'] = '⚡ Yarın';
        } else {
            $row['days_until'] = $interval->days . ' gün sonra';
        }

        $events[] = $row;
    }

    echo json_encode([
        'status' => 'success',
        'count' => count($events),
        'events' => $events
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Veritabanı hatası: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

$conn->close();
?>
