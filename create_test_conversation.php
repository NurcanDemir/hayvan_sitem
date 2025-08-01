<?php
session_start();
include('includes/db.php');

// Get current user or set a default
if (!isset($_SESSION['kullanici_id'])) {
    // Check what users exist in database
    $users_result = $conn->query("SELECT id, kullanici_adi FROM kullanicilar ORDER BY id LIMIT 5");
    if ($users_result && $users_result->num_rows > 0) {
        $first_user = $users_result->fetch_assoc();
        $_SESSION['kullanici_id'] = $first_user['id'];
        echo "<p style='color: blue;'>Auto-logged in as: " . htmlspecialchars($first_user['kullanici_adi']) . " (ID: " . $first_user['id'] . ")</p>";
    } else {
        die("No users found in database!");
    }
}

$user_id = $_SESSION['kullanici_id'];

// Verify user exists
$user_check = $conn->prepare("SELECT kullanici_adi FROM kullanicilar WHERE id = ?");
$user_check->bind_param("i", $user_id);
$user_check->execute();
$user_info = $user_check->get_result()->fetch_assoc();

if (!$user_info) {
    die("Current user ID $user_id does not exist!");
}

echo "<h2>Create Test Conversation</h2>";
echo "<p><strong>Current User: " . htmlspecialchars($user_info['kullanici_adi']) . " (ID: $user_id)</strong></p>";

// Get available ads
echo "<h3>Available Ads for Testing:</h3>";
$ads_result = $conn->query("SELECT i.*, u.kullanici_adi FROM ilanlar i JOIN kullanicilar u ON i.kullanici_id = u.id WHERE i.kullanici_id != $user_id ORDER BY i.id DESC LIMIT 10");

if ($ads_result && $ads_result->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr><th>Ad ID</th><th>Title</th><th>Owner</th><th>Action</th></tr>";
    while ($ad = $ads_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $ad['id'] . "</td>";
        echo "<td>" . htmlspecialchars($ad['baslik']) . "</td>";
        echo "<td>" . htmlspecialchars($ad['kullanici_adi']) . " (ID: " . $ad['kullanici_id'] . ")</td>";
        echo "<td><a href='?create_conversation=" . $ad['id'] . "&owner=" . $ad['kullanici_id'] . "'>Create Conversation</a></td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No ads available</p>";
}

// Handle conversation creation
if (isset($_GET['create_conversation'])) {
    $ilan_id = intval($_GET['create_conversation']);
    $owner_id = intval($_GET['owner']);
    
    // Check if conversation already exists
    $check_sql = "SELECT c.id FROM conversations c WHERE c.ilan_id = ? AND ((c.ilan_sahibi_id = ? AND c.talep_eden_id = ?) OR (c.ilan_sahibi_id = ? AND c.talep_eden_id = ?))";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("iiiii", $ilan_id, $owner_id, $user_id, $user_id, $owner_id);
    $check_stmt->execute();
    $existing = $check_stmt->get_result()->fetch_assoc();
    
    if ($existing) {
        echo "<p style='color: orange;'>Conversation already exists! <a href='mesajlar.php?conversation=" . $existing['id'] . "'>Open it</a></p>";
    } else {
        // First create an adoption request
        $request_sql = "INSERT INTO sahiplenme_istekleri (ilan_id, talep_eden_kullanici_id, durum, talep_tarihi, mesajlasma_aktif) VALUES (?, ?, 'onaylandi', NOW(), 1)";
        $request_stmt = $conn->prepare($request_sql);
        $request_stmt->bind_param("ii", $ilan_id, $user_id);
        
        if ($request_stmt->execute()) {
            $request_id = $request_stmt->insert_id;
            echo "<p style='color: blue;'>✓ Adoption request created (ID: $request_id)</p>";
            
            // Now create conversation with the request ID
            $create_sql = "INSERT INTO conversations (ilan_id, ilan_sahibi_id, talep_eden_id, sahiplenme_istek_id, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())";
            $create_stmt = $conn->prepare($create_sql);
            $create_stmt->bind_param("iiii", $ilan_id, $owner_id, $user_id, $request_id);
            
            if ($create_stmt->execute()) {
                $conversation_id = $create_stmt->insert_id;
                echo "<p style='color: green;'>✓ Conversation created! <a href='mesajlar.php?conversation=$conversation_id'>Open it</a></p>";
            } else {
                echo "<p style='color: red;'>✗ Error creating conversation: " . $create_stmt->error . "</p>";
            }
        } else {
            echo "<p style='color: red;'>✗ Error creating adoption request: " . $request_stmt->error . "</p>";
        }
    }
}

echo "<h3>Your Current Conversations:</h3>";
$conv_result = $conn->query("SELECT c.*, CASE WHEN c.ilan_sahibi_id = $user_id THEN u2.kullanici_adi ELSE u1.kullanici_adi END as other_user_name FROM conversations c LEFT JOIN kullanicilar u1 ON c.ilan_sahibi_id = u1.id LEFT JOIN kullanicilar u2 ON c.talep_eden_id = u2.id WHERE c.ilan_sahibi_id = $user_id OR c.talep_eden_id = $user_id");

if ($conv_result && $conv_result->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr><th>Conversation ID</th><th>Other User</th><th>Action</th></tr>";
    while ($conv = $conv_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $conv['id'] . "</td>";
        echo "<td>" . htmlspecialchars($conv['other_user_name']) . "</td>";
        echo "<td><a href='mesajlar.php?conversation=" . $conv['id'] . "'>Open Chat</a></td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No conversations found</p>";
}
?>
