<?php
session_start();
include('includes/db.php');

// Auto-login for testing
if (!isset($_SESSION['kullanici_id'])) {
    $users_result = $conn->query("SELECT id FROM kullanicilar ORDER BY id LIMIT 1");
    if ($users_result && $users_result->num_rows > 0) {
        $user = $users_result->fetch_assoc();
        $_SESSION['kullanici_id'] = $user['id'];
    }
}

$user_id = $_SESSION['kullanici_id'];

echo "<h2>Simple Messaging Test</h2>";
echo "<p>User ID: $user_id</p>";

// Get a conversation
$conv_query = "SELECT c.*, 
               CASE WHEN c.ilan_sahibi_id = ? THEN u2.kullanici_adi ELSE u1.kullanici_adi END as other_user_name
               FROM conversations c
               LEFT JOIN kullanicilar u1 ON c.ilan_sahibi_id = u1.id
               LEFT JOIN kullanicilar u2 ON c.talep_eden_id = u2.id
               WHERE (c.ilan_sahibi_id = ? OR c.talep_eden_id = ?)
               LIMIT 1";
$conv_stmt = $conn->prepare($conv_query);
$conv_stmt->bind_param("iii", $user_id, $user_id, $user_id);
$conv_stmt->execute();
$conversation = $conv_stmt->get_result()->fetch_assoc();

if (!$conversation) {
    echo "<p style='color: red;'>No conversation found. <a href='create_test_conversation.php'>Create one first</a></p>";
    exit;
}

$conversation_id = $conversation['id'];
echo "<p>Testing with conversation ID: $conversation_id</p>";
echo "<p>Other user: " . htmlspecialchars($conversation['other_user_name']) . "</p>";

// Handle message sending
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    
    if (!empty($message)) {
        $other_user_id = ($conversation['ilan_sahibi_id'] == $user_id) ? $conversation['talep_eden_id'] : $conversation['ilan_sahibi_id'];
        
        $stmt = $conn->prepare("INSERT INTO messages (conversation_id, sender_id, receiver_id, message, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("iiis", $conversation_id, $user_id, $other_user_id, $message);
        
        if ($stmt->execute()) {
            echo "<p style='color: green;'>✅ Message sent! ID: " . $stmt->insert_id . "</p>";
            
            // Update conversation
            $update_stmt = $conn->prepare("UPDATE conversations SET updated_at = NOW() WHERE id = ?");
            $update_stmt->bind_param("i", $conversation_id);
            $update_stmt->execute();
        } else {
            echo "<p style='color: red;'>❌ Error: " . $stmt->error . "</p>";
        }
    }
}

// Show recent messages
echo "<h3>Recent Messages:</h3>";
$msg_query = "SELECT m.*, u.kullanici_adi FROM messages m JOIN kullanicilar u ON m.sender_id = u.id WHERE m.conversation_id = ? ORDER BY m.created_at DESC LIMIT 10";
$msg_stmt = $conn->prepare($msg_query);
$msg_stmt->bind_param("i", $conversation_id);
$msg_stmt->execute();
$messages = $msg_stmt->get_result();

if ($messages && $messages->num_rows > 0) {
    echo "<div style='border: 1px solid #ccc; padding: 10px; height: 300px; overflow-y: auto;'>";
    while ($msg = $messages->fetch_assoc()) {
        $is_own = ($msg['sender_id'] == $user_id);
        $style = $is_own ? 'text-align: right; background: lightblue;' : 'text-align: left; background: lightgray;';
        
        echo "<div style='margin: 5px; padding: 5px; $style'>";
        echo "<strong>" . htmlspecialchars($msg['kullanici_adi']) . ":</strong> ";
        echo htmlspecialchars($msg['message']);
        echo "<br><small>" . $msg['created_at'] . "</small>";
        echo "</div>";
    }
    echo "</div>";
} else {
    echo "<p>No messages yet</p>";
}

// Simple message form
echo "<h3>Send Message:</h3>";
echo "<form method='POST'>";
echo "<input type='text' name='message' placeholder='Type your message...' required style='width: 70%;'>";
echo "<button type='submit' style='padding: 5px 10px;'>Send</button>";
echo "</form>";

echo "<p><a href='mesajlar.php?conversation=$conversation_id'>Go to main messages page</a></p>";
echo "<p><a href='mesajlar.php?conversation=$conversation_id&debug=1'>Go to main messages page with debug</a></p>";
?>
