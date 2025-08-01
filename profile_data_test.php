<?php
// Simple test to understand the issue
session_start();

// Set test session
$_SESSION['kullanici_id'] = 24; // Use an existing user ID

include('includes/db.php');

$logged_user_id = $_SESSION['kullanici_id'];
$viewing_user_id = 25; // Another existing user
$is_own_profile = ($viewing_user_id == $logged_user_id);

echo "<h2>Profile Data Test</h2>";
echo "<p>Logged in as: User $logged_user_id</p>";
echo "<p>Viewing profile of: User $viewing_user_id</p>";
echo "<p>Is own profile: " . ($is_own_profile ? "Yes" : "No") . "</p>";

echo "<h3>What SHOULD be shown under 'İlanları' tab:</h3>";
echo "<p><strong>User $viewing_user_id's ads (animals they posted for adoption)</strong></p>";

// Query that should be used for the "İlanları" tab
$ads_sql = "SELECT i.*, k.ad as kategori_adi 
           FROM ilanlar i
           LEFT JOIN kategoriler k ON i.kategori_id = k.id
           WHERE i.kullanici_id = ?
           ORDER BY i.tarih DESC";
$ads_stmt = $conn->prepare($ads_sql);
$ads_stmt->bind_param("i", $viewing_user_id);
$ads_stmt->execute();
$ads_result = $ads_stmt->get_result();

echo "<p><strong>User $viewing_user_id's POSTED ADS:</strong></p>";
if ($ads_result && $ads_result->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr><th>Ad ID</th><th>Title</th><th>Category</th><th>Posted By</th></tr>";
    while ($ad = $ads_result->fetch_assoc()) {
        echo "<tr style='background: lightgreen;'>";
        echo "<td>" . $ad['id'] . "</td>";
        echo "<td>" . htmlspecialchars($ad['baslik']) . "</td>";
        echo "<td>" . htmlspecialchars($ad['kategori_adi']) . "</td>";
        echo "<td>User " . $ad['kullanici_id'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "<p style='color: green;'>✓ This is what should appear under 'İlanları' tab</p>";
} else {
    echo "<p>User $viewing_user_id has no posted ads.</p>";
}

echo "<h3>What should NOT be shown (User's adoption requests):</h3>";
$requests_sql = "SELECT si.*, i.baslik as ilan_baslik, i.kullanici_id as ilan_sahibi_id
                 FROM sahiplenme_istekleri si
                 LEFT JOIN ilanlar i ON si.ilan_id = i.id
                 WHERE si.talep_eden_kullanici_id = ?
                 ORDER BY si.talep_tarihi DESC";
$requests_stmt = $conn->prepare($requests_sql);
$requests_stmt->bind_param("i", $viewing_user_id);
$requests_stmt->execute();
$requests_result = $requests_stmt->get_result();

echo "<p><strong>User $viewing_user_id's ADOPTION REQUESTS:</strong></p>";
if ($requests_result && $requests_result->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr><th>Request ID</th><th>Requested Ad Title</th><th>Ad Owner</th><th>Status</th></tr>";
    while ($request = $requests_result->fetch_assoc()) {
        echo "<tr style='background: lightcoral;'>";
        echo "<td>" . $request['id'] . "</td>";
        echo "<td>" . htmlspecialchars($request['ilan_baslik']) . "</td>";
        echo "<td>User " . $request['ilan_sahibi_id'] . "</td>";
        echo "<td>" . $request['durum'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "<p style='color: red;'>✗ This should NOT appear under 'İlanları' tab for other users</p>";
} else {
    echo "<p>User $viewing_user_id has no adoption requests.</p>";
}

echo "<h3>Diagnosis:</h3>";
echo "<p>If you're seeing the RED data (adoption requests) when you visit another user's profile under the 'İlanları' tab, then there's a bug in the code.</p>";
echo "<p>You should only see the GREEN data (their posted ads).</p>";
?>
