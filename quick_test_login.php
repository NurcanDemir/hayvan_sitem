<?php
// Temporary login simulation for testing messaging
session_start();

echo "<h2>Quick Messaging Test</h2>";

echo "<h3>Available Test Users:</h3>";
echo "<p><a href='?test_user=24'>Login as User 24</a> | <a href='?test_user=25'>Login as User 25</a> | <a href='?logout=1'>Logout</a></p>";

if (isset($_GET['test_user'])) {
    $_SESSION['kullanici_id'] = intval($_GET['test_user']);
    echo "<p style='color: green;'>Logged in as User " . $_SESSION['kullanici_id'] . "</p>";
}

if (isset($_GET['logout'])) {
    session_destroy();
    session_start();
    echo "<p>Logged out</p>";
}

if (isset($_SESSION['kullanici_id'])) {
    echo "<p><strong>Current user: " . $_SESSION['kullanici_id'] . "</strong></p>";
    echo "<p><a href='mesajlar.php'>Go to Messages</a></p>";
    echo "<p><a href='profil.php'>Go to Profile</a></p>";
    
    // Show their conversations
    include('includes/db.php');
    $user_id = $_SESSION['kullanici_id'];
    
    $conversations_query = "
        SELECT c.*, 
               CASE WHEN c.ilan_sahibi_id = ? THEN u2.kullanici_adi ELSE u1.kullanici_adi END as other_user_name
        FROM conversations c
        LEFT JOIN kullanicilar u1 ON c.ilan_sahibi_id = u1.id
        LEFT JOIN kullanicilar u2 ON c.talep_eden_id = u2.id
        WHERE (c.ilan_sahibi_id = ? OR c.talep_eden_id = ?)
    ";
    
    $stmt = $conn->prepare($conversations_query);
    $stmt->bind_param("iii", $user_id, $user_id, $user_id);
    $stmt->execute();
    $conversations = $stmt->get_result();
    
    echo "<h4>Your Conversations:</h4>";
    if ($conversations && $conversations->num_rows > 0) {
        while ($conv = $conversations->fetch_assoc()) {
            echo "<p>Conversation with: " . htmlspecialchars($conv['other_user_name']) . " - <a href='mesajlar.php?conversation=" . $conv['id'] . "'>Open</a></p>";
        }
    } else {
        echo "<p>No conversations</p>";
    }
} else {
    echo "<p>Not logged in</p>";
}
?>
