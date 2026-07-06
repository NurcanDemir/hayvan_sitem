<?php
session_start();

// Kullanıcı giriş kontrolü
if (!isset($_SESSION['kullanici_id'])) {
    header("Location: giris.php");
    exit();
}

include('includes/db.php');

$user_id = $_SESSION['kullanici_id'];
$conversation_id = isset($_GET['conversation']) ? intval($_GET['conversation']) : 0;
$selected_conversation = null;

// Debug mode check
$debug_mode = isset($_GET['debug']) && $_GET['debug'] == '1';
if ($debug_mode) {
    echo "<div style='background: red; color: white; padding: 10px; position: fixed; top: 0; left: 0; right: 0; z-index: 9999;'>";
    echo "🐛 DEBUG MODE ACTIVE - User ID: $user_id - Conversation ID: $conversation_id";
    echo "</div>";
    echo "<br><br><br>"; // Add space for the debug bar
}

// Mark messages as read when viewing a conversation
if ($conversation_id > 0) {
    $stmt = $conn->prepare("UPDATE messages SET is_read = 1 WHERE conversation_id = ? AND receiver_id = ?");
    $stmt->bind_param("ii", $conversation_id, $user_id);
    $stmt->execute();
}

// DEBUG: Only show debug output for non-AJAX requests
if ($debug_mode && !isset($_POST['ajax']) && !isset($_GET['get_messages'])) {
    echo "<div style='background: cyan; padding: 10px; margin: 10px; border: 2px solid blue; font-size: 14px; position: relative; z-index: 1000;'>";
    echo "<strong>🔍 PAGE LOAD DEBUG</strong><br>";
    echo "Method: " . $_SERVER['REQUEST_METHOD'] . "<br>";
    echo "POST data exists: " . (isset($_POST['message_content']) && isset($_POST['conversation_id']) ? 'YES' : 'NO') . "<br>";
    echo "POST keys: " . (empty($_POST) ? 'NONE' : implode(', ', array_keys($_POST))) . "<br>";
    echo "POST count: " . count($_POST) . "<br>";
    echo "Raw POST dump: " . print_r($_POST, true) . "<br>";
    echo "Content Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'not set') . "<br>";
    echo "Content Length: " . ($_SERVER['CONTENT_LENGTH'] ?? 'not set') . "<br>";
    echo "Time: " . date('H:i:s') . "<br>";
    echo "URL: " . $_SERVER['REQUEST_URI'];
    echo "</div>";
}

// Handle AJAX requests for sending messages
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax']) && $_POST['ajax'] === '1') {
    header('Content-Type: application/json');
    
    $message_content = trim($_POST['message_content']);
    $conversation_id = intval($_POST['conversation_id']);
    
    if (!empty($message_content) && $conversation_id > 0) {
        // Verify user is part of this conversation
        $stmt = $conn->prepare("SELECT * FROM conversations WHERE id = ? AND (ilan_sahibi_id = ? OR talep_eden_id = ?)");
        $stmt->bind_param("iii", $conversation_id, $user_id, $user_id);
        $stmt->execute();
        $conversation = $stmt->get_result()->fetch_assoc();
        
        if ($conversation) {
            // Check if conversation is still active (not blocked)
            $other_user_id = ($conversation['ilan_sahibi_id'] == $user_id) ? $conversation['talep_eden_id'] : $conversation['ilan_sahibi_id'];
            
            $stmt = $conn->prepare("SELECT COUNT(*) as blocked FROM blocked_users WHERE (blocker_id = ? AND blocked_id = ?) OR (blocker_id = ? AND blocked_id = ?)");
            $stmt->bind_param("iiii", $user_id, $other_user_id, $other_user_id, $user_id);
            $stmt->execute();
            $blocked_result = $stmt->get_result()->fetch_assoc();
            
            if ($blocked_result['blocked'] == 0) {
                // Send message
                $stmt = $conn->prepare("INSERT INTO messages (conversation_id, sender_id, receiver_id, message, created_at) VALUES (?, ?, ?, ?, NOW())");
                $stmt->bind_param("iiis", $conversation_id, $user_id, $other_user_id, $message_content);
                
                if ($stmt->execute()) {
                    $message_id = $stmt->insert_id;
                    
                    // Update conversation's last activity
                    $stmt = $conn->prepare("UPDATE conversations SET updated_at = NOW() WHERE id = ?");
                    $stmt->bind_param("i", $conversation_id);
                    $stmt->execute();
                    
                    // Return success response with message data
                    echo json_encode([
                        'success' => true,
                        'message_id' => $message_id,
                        'message' => htmlspecialchars($message_content),
                        'time' => date('H:i'),
                        'timestamp' => time()
                    ]);
                    exit();
                } else {
                    echo json_encode(['success' => false, 'error' => 'Failed to send message']);
                    exit();
                }
            } else {
                echo json_encode(['success' => false, 'error' => 'Conversation is blocked']);
                exit();
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Conversation not found']);
            exit();
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid message data']);
        exit();
    }
}

// Handle AJAX requests for getting new messages
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['get_messages']) && $_GET['get_messages'] === '1') {
    header('Content-Type: application/json');
    
    $conversation_id = isset($_GET['conversation_id']) ? intval($_GET['conversation_id']) : 0;
    $since_timestamp = isset($_GET['since']) ? intval($_GET['since']) : 0;
    
    if ($conversation_id > 0) {
        // Get new messages since timestamp
        $stmt = $conn->prepare("
            SELECT m.*, u.kullanici_adi, u.profil_foto
            FROM messages m
            JOIN kullanicilar u ON m.sender_id = u.id
            WHERE m.conversation_id = ? AND UNIX_TIMESTAMP(m.created_at) > ?
            ORDER BY m.created_at ASC
        ");
        $stmt->bind_param("ii", $conversation_id, $since_timestamp);
        $stmt->execute();
        $new_messages = $stmt->get_result();
        
        $messages_array = [];
        while ($message = $new_messages->fetch_assoc()) {
            $messages_array[] = [
                'id' => $message['id'],
                'sender_id' => $message['sender_id'],
                'message' => htmlspecialchars($message['message']),
                'time' => date('H:i', strtotime($message['created_at'])),
                'timestamp' => strtotime($message['created_at']),
                'is_read' => $message['is_read'],
                'sender_name' => htmlspecialchars($message['kullanici_adi'])
            ];
        }
        
        echo json_encode(['success' => true, 'messages' => $messages_array]);
        exit();
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid conversation ID']);
        exit();
    }
}

// Handle sending new message - Check for POST data instead of button
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message_content']) && isset($_POST['conversation_id'])) {
    $message_content = trim($_POST['message_content']);
    $conversation_id = intval($_POST['conversation_id']);
    
    // ALWAYS show debug info when debug mode is on, regardless of success
    if ($debug_mode) {
        echo "<div style='background: yellow; padding: 10px; margin: 10px; border: 2px solid orange; position: relative; z-index: 1000;'>";
        echo "<h3>🔍 DEBUG: Message Submission</h3>";
        echo "<p><strong>POST Method:</strong> " . $_SERVER['REQUEST_METHOD'] . "</p>";
        echo "<p><strong>User ID:</strong> $user_id</p>";
        echo "<p><strong>Conversation ID:</strong> $conversation_id</p>";
        echo "<p><strong>Message:</strong> " . htmlspecialchars($message_content) . "</p>";
        echo "<p><strong>POST Data:</strong></p>";
        echo "<pre>" . print_r($_POST, true) . "</pre>";
        echo "<p><strong>Timestamp:</strong> " . date('Y-m-d H:i:s') . "</p>";
        echo "</div>";
    }
    
    // Debug logging - also output to screen for testing
    $debug_msg = "MESSAGE_SEND_START - User ID: $user_id, Conversation ID: $conversation_id, Message: " . substr($message_content, 0, 50);
    error_log($debug_msg);
    
    if (!empty($message_content) && $conversation_id > 0) {
        // Verify user is part of this conversation
        $stmt = $conn->prepare("SELECT * FROM conversations WHERE id = ? AND (ilan_sahibi_id = ? OR talep_eden_id = ?)");
        $stmt->bind_param("iii", $conversation_id, $user_id, $user_id);
        $stmt->execute();
        $conversation = $stmt->get_result()->fetch_assoc();
        
        if ($conversation) {
            error_log("MESSAGE_SEND_CONV_FOUND: " . json_encode($conversation));
            
            if ($debug_mode) {
                echo "<div style='background: lightgreen; padding: 10px; margin: 10px; border: 2px solid green;'>";
                echo "✅ Conversation found and user authorized";
                echo "</div>";
            }
            
            // Check if conversation is still active (not blocked)
            $other_user_id = ($conversation['ilan_sahibi_id'] == $user_id) ? $conversation['talep_eden_id'] : $conversation['ilan_sahibi_id'];
            
            $stmt = $conn->prepare("SELECT COUNT(*) as blocked FROM blocked_users WHERE (blocker_id = ? AND blocked_id = ?) OR (blocker_id = ? AND blocked_id = ?)");
            $stmt->bind_param("iiii", $user_id, $other_user_id, $other_user_id, $user_id);
            $stmt->execute();
            $blocked_result = $stmt->get_result()->fetch_assoc();
            
            if ($blocked_result['blocked'] == 0) {
                // Send message
                $stmt = $conn->prepare("INSERT INTO messages (conversation_id, sender_id, receiver_id, message, created_at) VALUES (?, ?, ?, ?, NOW())");
                $stmt->bind_param("iiis", $conversation_id, $user_id, $other_user_id, $message_content);
                
                if ($stmt->execute()) {
                    error_log("MESSAGE_SEND_SUCCESS - Message ID: " . $stmt->insert_id);
                    
                    if ($debug_mode) {
                        echo "<div style='background: lightblue; padding: 10px; margin: 10px; border: 2px solid blue;'>";
                        echo "✅ Message sent successfully! ID: " . $stmt->insert_id;
                        echo "</div>";
                    }
                    
                    // Update conversation's last activity
                    $stmt = $conn->prepare("UPDATE conversations SET updated_at = NOW() WHERE id = ?");
                    $stmt->bind_param("i", $conversation_id);
                    $stmt->execute();
                    
                    if (!$debug_mode) {
                        header("Location: mesajlar.php?conversation=" . $conversation_id . "&sent=1");
                        exit();
                    }
                } else {
                    error_log("MESSAGE_SEND_ERROR: " . $stmt->error);
                    
                    if ($debug_mode) {
                        echo "<div style='background: lightcoral; padding: 10px; margin: 10px; border: 2px solid red;'>";
                        echo "❌ Error inserting message: " . $stmt->error;
                        echo "</div>";
                    }
                }
            } else {
                error_log("MESSAGE_SEND_BLOCKED");
                
                if ($debug_mode) {
                    echo "<div style='background: orange; padding: 10px; margin: 10px; border: 2px solid darkorange;'>";
                    echo "⚠️ Conversation is blocked";
                    echo "</div>";
                }
            }
        } else {
            error_log("MESSAGE_SEND_NO_CONV - Conversation not found or user not authorized");
            
            if ($debug_mode) {
                echo "<div style='background: lightcoral; padding: 10px; margin: 10px; border: 2px solid red;'>";
                echo "❌ Conversation not found or user not authorized";
                echo "</div>";
            }
        }
    } else {
        error_log("MESSAGE_SEND_INVALID_DATA - Empty message or invalid conversation ID");
        
        if ($debug_mode) {
            echo "<div style='background: orange; padding: 10px; margin: 10px; border: 2px solid darkorange;'>";
            echo "⚠️ Invalid message data - Empty message or invalid conversation ID";
            echo "</div>";
        }
    }
}

// Get user's conversations
$conversations_query = "
    SELECT 
        c.*,
        CASE 
            WHEN c.ilan_sahibi_id = ? THEN u2.kullanici_adi 
            ELSE u1.kullanici_adi 
        END as other_user_name,
        CASE 
            WHEN c.ilan_sahibi_id = ? THEN u2.profil_foto 
            ELSE u1.profil_foto 
        END as other_user_photo,
        CASE 
            WHEN c.ilan_sahibi_id = ? THEN c.talep_eden_id 
            ELSE c.ilan_sahibi_id 
        END as other_user_id,
        (SELECT COUNT(*) FROM messages m WHERE m.conversation_id = c.id AND m.receiver_id = ? AND m.is_read = 0) as unread_count,
        (SELECT m.message FROM messages m WHERE m.conversation_id = c.id ORDER BY m.created_at DESC LIMIT 1) as last_message,
        (SELECT m.created_at FROM messages m WHERE m.conversation_id = c.id ORDER BY m.created_at DESC LIMIT 1) as last_message_time,
        i.baslik as animal_name,
        i.tur as animal_type
    FROM conversations c
    LEFT JOIN kullanicilar u1 ON c.ilan_sahibi_id = u1.id
    LEFT JOIN kullanicilar u2 ON c.talep_eden_id = u2.id
    LEFT JOIN ilanlar i ON c.ilan_id = i.id
    WHERE (c.ilan_sahibi_id = ? OR c.talep_eden_id = ?)
    AND NOT EXISTS (
        SELECT 1 FROM blocked_users bu 
        WHERE (bu.blocker_id = ? AND bu.blocked_id = CASE WHEN c.ilan_sahibi_id = ? THEN c.talep_eden_id ELSE c.ilan_sahibi_id END)
        OR (bu.blocked_id = ? AND bu.blocker_id = CASE WHEN c.ilan_sahibi_id = ? THEN c.talep_eden_id ELSE c.ilan_sahibi_id END)
    )
    ORDER BY c.updated_at DESC
";

$stmt = $conn->prepare($conversations_query);
$stmt->bind_param("iiiiiiiiii", $user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id);
$stmt->execute();
$conversations = $stmt->get_result();

// Get messages for selected conversation
$messages = null;
if ($conversation_id > 0) {
    $stmt = $conn->prepare("
        SELECT c.*, 
               CASE WHEN c.ilan_sahibi_id = ? THEN u2.kullanici_adi ELSE u1.kullanici_adi END as other_user_name,
               CASE WHEN c.ilan_sahibi_id = ? THEN u2.profil_foto ELSE u1.profil_foto END as other_user_photo,
               i.baslik as animal_name
        FROM conversations c
        LEFT JOIN kullanicilar u1 ON c.ilan_sahibi_id = u1.id
        LEFT JOIN kullanicilar u2 ON c.talep_eden_id = u2.id
        LEFT JOIN ilanlar i ON c.ilan_id = i.id
        WHERE c.id = ? AND (c.ilan_sahibi_id = ? OR c.talep_eden_id = ?)
    ");
    $stmt->bind_param("iiiii", $user_id, $user_id, $conversation_id, $user_id, $user_id);
    $stmt->execute();
    $selected_conversation = $stmt->get_result()->fetch_assoc();
    
    if ($selected_conversation) {
        $stmt = $conn->prepare("
            SELECT m.*, u.kullanici_adi, u.profil_foto
            FROM messages m
            JOIN kullanicilar u ON m.sender_id = u.id
            WHERE m.conversation_id = ?
            ORDER BY m.created_at ASC
        ");
        $stmt->bind_param("i", $conversation_id);
        $stmt->execute();
        $messages = $stmt->get_result();
    }
}

// Function to format time ago
function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'Az önce';
    if ($time < 3600) return floor($time/60) . ' dk önce';
    if ($time < 86400) return floor($time/3600) . ' sa önce';
    if ($time < 2592000) return floor($time/86400) . ' gün önce';
    
    return date('d.m.Y', strtotime($datetime));
}

include("includes/header.php");
?>

<style>
    .conversation-item {
        transition: all 0.2s ease;
        border-left: 4px solid transparent;
    }
    
    .conversation-item:hover {
        background: #f9fafb;
        border-left-color: #8b5cf6;
    }
    
    .conversation-item.active {
        background: linear-gradient(135deg, #f3e8ff, #fdf4ff);
        border-left-color: #8b5cf6;
    }
    
    .message-bubble {
        max-width: 70%;
        word-wrap: break-word;
    }
    
    .message-sent {
        background: linear-gradient(135deg, #8b5cf6, #a855f7);
        color: white;
        margin-left: auto;
    }
    
    .message-received {
        background: #f3f4f6;
        color: #374151;
    }
    
    .chat-container {
        height: calc(100vh - 200px);
        min-height: 500px;
    }
    
    .messages-container {
        height: calc(100% - 80px);
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: #d1d5db #f3f4f6;
    }
    
    .messages-container::-webkit-scrollbar {
        width: 8px;
    }
    
    .messages-container::-webkit-scrollbar-track {
        background: #f3f4f6;
        border-radius: 4px;
    }
    
    .messages-container::-webkit-scrollbar-thumb {
        background: #d1d5db;
        border-radius: 4px;
    }
    
    .messages-container::-webkit-scrollbar-thumb:hover {
        background: #9ca3af;
    }
    
    .message-input {
        resize: none;
        min-height: 40px;
        max-height: 120px;
    }
    
    .unread-badge {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: white;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.25rem 0.5rem;
        border-radius: 12px;
        min-width: 20px;
        text-align: center;
    }
</style>

<!-- Main Content -->
<main class="min-h-screen bg-gradient-to-br from-purple-50 via-pink-50 to-purple-50 mt-16">
    <div class="max-w-7xl mx-auto p-6">
        
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-4">
                <i class="fas fa-comments mr-3 text-purple-600"></i>
                Mesajlarım
            </h1>
            <p class="text-lg text-gray-600">
                Sahiplenme görüşmelerinizi burada gerçekleştirin
            </p>
        </div>
        
        <!-- Chat Interface -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden chat-container">
            <div class="flex h-full">
                
                <!-- Conversations Sidebar -->
                <div class="w-1/3 border-r border-gray-200 flex flex-col">
                    <div class="p-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="font-semibold text-gray-800">Konuşmalar</h3>
                    </div>
                    
                    <div class="flex-1 overflow-y-auto">
                        <?php if ($conversations && $conversations->num_rows > 0): ?>
                            <?php while ($conv = $conversations->fetch_assoc()): ?>
                                <a href="mesajlar.php?conversation=<?= $conv['id'] ?>" 
                                   class="conversation-item block p-4 border-b border-gray-100 <?= ($conversation_id == $conv['id']) ? 'active' : '' ?>">
                                    <div class="flex items-center gap-3">
                                        <div class="flex-shrink-0 relative">
                                            <?php if (!empty($conv['other_user_photo']) && file_exists('uploads/profiles/' . $conv['other_user_photo'])): ?>
                                                <img src="uploads/profiles/<?= htmlspecialchars($conv['other_user_photo']) ?>" 
                                                     alt="<?= htmlspecialchars($conv['other_user_name']) ?>" 
                                                     class="w-12 h-12 rounded-full object-cover">
                                            <?php else: ?>
                                                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-purple-400 to-pink-400 flex items-center justify-center">
                                                    <i class="fas fa-user text-white"></i>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if ($conv['unread_count'] > 0): ?>
                                                <span class="unread-badge absolute -top-2 -right-2">
                                                    <?= $conv['unread_count'] ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between mb-1">
                                                <h4 class="font-semibold text-gray-800 truncate">
                                                    <?= htmlspecialchars($conv['other_user_name']) ?>
                                                </h4>
                                                <?php if ($conv['last_message_time']): ?>
                                                    <span class="text-xs text-gray-500">
                                                        <?= timeAgo($conv['last_message_time']) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <?php if ($conv['animal_name']): ?>
                                                <p class="text-xs text-purple-600 mb-1">
                                                    <i class="fas fa-paw mr-1"></i>
                                                    <?= htmlspecialchars($conv['animal_name']) ?>
                                                </p>
                                            <?php endif; ?>
                                            
                                            <?php if ($conv['last_message']): ?>
                                                <p class="text-sm text-gray-600 truncate">
                                                    <?= htmlspecialchars(substr($conv['last_message'], 0, 40)) ?><?= strlen($conv['last_message']) > 40 ? '...' : '' ?>
                                                </p>
                                            <?php else: ?>
                                                <p class="text-sm text-gray-400 italic">Henüz mesaj yok</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </a>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="p-8 text-center">
                                <div class="text-6xl mb-4">💬</div>
                                <h3 class="text-xl font-semibold text-gray-600 mb-2">Henüz Konuşma Yok</h3>
                                <p class="text-gray-500">
                                    Sahiplenme taleplerini onayladığınızda konuşmalar başlayacak.
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Chat Area -->
                <div class="flex-1 flex flex-col">
                    <?php if ($selected_conversation): ?>
                        <!-- Chat Header -->
                        <div class="p-4 border-b border-gray-200 bg-gray-50">
                            <div class="flex items-center gap-3">
                                <?php if (!empty($selected_conversation['other_user_photo']) && file_exists('uploads/profiles/' . $selected_conversation['other_user_photo'])): ?>
                                    <img src="uploads/profiles/<?= htmlspecialchars($selected_conversation['other_user_photo']) ?>" 
                                         alt="<?= htmlspecialchars($selected_conversation['other_user_name']) ?>" 
                                         class="w-10 h-10 rounded-full object-cover">
                                <?php else: ?>
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-400 to-pink-400 flex items-center justify-center">
                                        <i class="fas fa-user text-white text-sm"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <div>
                                    <a href="profil.php?kullanici_id=<?= $selected_conversation['ilan_sahibi_id'] == $user_id ? $selected_conversation['talep_eden_id'] : $selected_conversation['ilan_sahibi_id'] ?>" 
                                       class="font-semibold text-gray-800 hover:text-purple-600 transition-colors duration-200">
                                        <?= htmlspecialchars($selected_conversation['other_user_name']) ?>
                                    </a>
                                    <?php if ($selected_conversation['animal_name']): ?>
                                        <p class="text-sm text-purple-600">
                                            <i class="fas fa-paw mr-1"></i>
                                            <?= htmlspecialchars($selected_conversation['animal_name']) ?> hakkında
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Messages -->
                        <div class="messages-container p-4 space-y-4" id="messagesContainer">
                            <?php if ($messages && $messages->num_rows > 0): ?>
                                <?php while ($message = $messages->fetch_assoc()): ?>
                                    <div class="flex <?= ($message['sender_id'] == $user_id) ? 'justify-end' : 'justify-start' ?>">
                                        <div class="message-bubble p-3 rounded-lg <?= ($message['sender_id'] == $user_id) ? 'message-sent' : 'message-received' ?>">
                                            <p class="text-sm"><?= nl2br(htmlspecialchars($message['message'])) ?></p>
                                            <p class="text-xs mt-2 opacity-70">
                                                <?= date('H:i', strtotime($message['created_at'])) ?>
                                                <?php if ($message['sender_id'] == $user_id && $message['is_read']): ?>
                                                    <i class="fas fa-check-double ml-1"></i>
                                                <?php elseif ($message['sender_id'] == $user_id): ?>
                                                    <i class="fas fa-check ml-1"></i>
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="text-center py-8">
                                    <div class="text-6xl mb-4">👋</div>
                                    <h3 class="text-xl font-semibold text-gray-600 mb-2">Konuşma Başlasın!</h3>
                                    <p class="text-gray-500">İlk mesajınızı göndererek konuşmayı başlatın.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Message Input -->
                        <div class="p-4 border-t border-gray-200 bg-white">
                            <?php if ($debug_mode): ?>
                                <div style="background: orange; padding: 5px; margin-bottom: 10px;">
                                    <strong>DEBUG FORM:</strong> Conversation ID = <?= $conversation_id ?>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" class="flex gap-3" id="messageForm">
                                <input type="hidden" name="conversation_id" value="<?= $conversation_id ?>">
                                <textarea name="message_content" 
                                         id="messageTextarea"
                                         class="message-input flex-1 p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" 
                                         placeholder="Mesajınızı yazın..." 
                                         required
                                         rows="1"></textarea>
                                <button type="submit" 
                                        name="send_message"
                                        id="sendButton"
                                        class="px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg hover:from-purple-700 hover:to-pink-700 transition-all duration-200">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </form>
                            
                            <?php if ($debug_mode): ?>
                                <div style="background: lightgray; padding: 5px; margin-top: 10px; font-size: 12px;">
                                    <strong>Debug Info:</strong><br>
                                    - Form action: (empty - submits to same page)<br>
                                    - Method: POST<br>
                                    - Conversation ID: <?= $conversation_id ?><br>
                                    - User ID: <?= $user_id ?><br>
                                    <br>
                                    <button type="button" onclick="testFormSubmission()" style="background: red; color: white; padding: 5px;">
                                        🧪 Test Form Submission
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                    <?php else: ?>
                        <!-- No Conversation Selected -->
                        <div class="flex-1 flex items-center justify-center">
                            <div class="text-center">
                                <div class="text-8xl mb-6">💬</div>
                                <h3 class="text-2xl font-semibold text-gray-600 mb-4">Bir Konuşma Seçin</h3>
                                <p class="text-gray-500 max-w-md">
                                    Sol taraftan bir konuşma seçerek mesajlaşmaya başlayın.
                                </p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Auto-scroll to bottom of messages - moved outside to be globally accessible
function scrollToBottom() {
    const messagesContainer = document.getElementById('messagesContainer');
    if (messagesContainer) {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
        console.log('📜 Scrolled to bottom');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('🔧 Mesajlar page loaded');
    
    // Auto-resize textarea
    const textarea = document.querySelector('#messageTextarea');
    const form = document.querySelector('#messageForm');
    const sendButton = document.querySelector('#sendButton');
    
    if (textarea && form && sendButton) {
        console.log('✅ Form elements found');
        
        // Auto-resize functionality
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 120) + 'px';
        });
        
        // Simple Enter key handler - AJAX VERSION
        textarea.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                console.log('📤 Enter pressed - AJAX mode');
                
                const messageContent = this.value.trim();
                console.log('📝 Message content before submit:', messageContent);
                
                if (messageContent) {
                    console.log('🎯 Sending via AJAX...');
                    sendMessageAjax(messageContent);
                } else {
                    console.log('⚠️ Empty message');
                    this.style.borderColor = '#ef4444';
                    setTimeout(() => {
                        this.style.borderColor = '#d1d5db';
                    }, 1000);
                }
            }
        });
        
        // Add click handler for send button - AJAX VERSION
        sendButton.addEventListener('click', function(e) {
            e.preventDefault(); // Prevent form submission
            console.log('🖱️ Send button clicked - AJAX mode');
            
            const messageContent = textarea.value.trim();
            console.log('📝 Message content:', messageContent);
            
            if (!messageContent) {
                console.log('❌ Empty message');
                textarea.style.borderColor = '#ef4444';
                setTimeout(() => {
                    textarea.style.borderColor = '#d1d5db';
                }, 1000);
                return;
            }
            
            // Send message via AJAX
            sendMessageAjax(messageContent);
        });
        
        // Focus on message input when page loads
        textarea.focus();
        console.log('🎯 Textarea focused');
    } else {
        console.error('❌ Form elements not found:', {
            textarea: !!textarea,
            form: !!form,
            sendButton: !!sendButton
        });
    }
    
    // Initial scroll to bottom
    scrollToBottom();
    
    // Focus on textarea
    if (textarea) {
        textarea.focus();
        console.log('🎯 Textarea focused');
    }
    
    // Scroll to bottom after images load (in case there are profile pictures)
    setTimeout(scrollToBottom, 500);
    
    // Success message
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('sent') === '1') {
        // Scroll to bottom when new message is sent
        setTimeout(scrollToBottom, 100);
        // Remove the parameter from URL
        window.history.replaceState({}, document.title, window.location.pathname + '?conversation=' + urlParams.get('conversation'));
    }
    
    console.log('💬 Mesajlaşma sayfası yüklendi');
    
    // Check for previous form submissions in localStorage
    const lastSubmit = localStorage.getItem('lastFormSubmit');
    if (lastSubmit) {
        const submitData = JSON.parse(lastSubmit);
        console.log('📚 Previous form submission found:', submitData);
        
        // Clear the stored data
        localStorage.removeItem('lastFormSubmit');
    }
});

// Auto-refresh messages and scroll to bottom when new messages arrive
let lastMessageCount = document.querySelectorAll('.message-bubble').length;

setInterval(function() {
    const conversationId = new URLSearchParams(window.location.search).get('conversation');
    if (conversationId) {
        // Check for new messages by counting message bubbles
        const currentMessageCount = document.querySelectorAll('.message-bubble').length;
        
        // Simple refresh approach - you could implement more sophisticated AJAX here
        fetch(window.location.href + '&check_new=1', {
            method: 'HEAD'
        }).then(() => {
            // If we want to implement real-time messaging, we would check for new messages here
            // For now, we'll just keep the conversation active
        }).catch(error => {
            console.log('Auto-refresh check error:', error);
        });
    }
}, 5000);

console.log('🎉 All event handlers set up successfully');

// AJAX Functions for real-time messaging
let lastMessageTimestamp = Math.floor(Date.now() / 1000);

// Send message via AJAX
function sendMessageAjax(messageContent) {
    console.log('🚀 sendMessageAjax called with:', messageContent);
    
    const conversationId = new URLSearchParams(window.location.search).get('conversation');
    if (!conversationId) {
        console.error('❌ No conversation ID found');
        alert('Konuşma ID bulunamadı!');
        return;
    }
    
    console.log('📋 Conversation ID:', conversationId);
    
    // Get elements fresh (in case they weren't available globally)
    const sendButton = document.querySelector('#sendButton');
    const textarea = document.querySelector('#messageTextarea');
    
    if (!sendButton || !textarea) {
        console.error('❌ Required elements not found:', { sendButton: !!sendButton, textarea: !!textarea });
        alert('Form elemanları bulunamadı!');
        return;
    }
    
    console.log('📤 Sending message via AJAX:', messageContent);
    
    // Show loading state
    const originalButtonContent = sendButton.innerHTML;
    sendButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    sendButton.disabled = true;
    
    // Add message optimistically (instant feedback)
    addMessageToChat(messageContent, true, true);
    
    // Clear textarea immediately
    textarea.value = '';
    textarea.style.height = 'auto';
    
    // Send AJAX request
    const currentUrl = window.location.pathname + '?conversation=' + conversationId;
    console.log('📍 Sending to URL:', currentUrl);
    
    fetch(currentUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `ajax=1&conversation_id=${conversationId}&message_content=${encodeURIComponent(messageContent)}`
    })
    .then(response => {
        console.log('📨 Response status:', response.status);
        console.log('📨 Response headers:', response.headers.get('content-type'));
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        return response.text(); // Get as text first to see what we're receiving
    })
    .then(responseText => {
        console.log('📄 Raw response:', responseText);
        
        // Try to parse as JSON
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (e) {
            console.error('❌ JSON Parse Error:', e);
            console.error('❌ Raw response that failed to parse:', responseText);
            throw new Error('Invalid JSON response: ' + responseText.substring(0, 100));
        }
        
        console.log('✅ AJAX Response:', data);
        
        // Reset button
        sendButton.innerHTML = originalButtonContent;
        sendButton.disabled = false;
        
        if (data.success) {
            console.log('✅ Message sent successfully!');
            // Update timestamp for new message polling
            lastMessageTimestamp = data.timestamp;
            
            // Update the optimistic message with real data
            updateOptimisticMessage(data.message_id, data.time);
        } else {
            console.error('❌ Failed to send message:', data.error);
            // Remove the optimistic message
            removeOptimisticMessage();
            alert('Mesaj gönderilemedi: ' + data.error);
        }
        
        // Focus back to textarea
        textarea.focus();
    })
    .catch(error => {
        console.error('❌ AJAX Error:', error);
        
        // Reset button in error case
        sendButton.innerHTML = originalButtonContent;
        sendButton.disabled = false;
        
        // Remove the optimistic message
        removeOptimisticMessage();
        alert('Bağlantı hatası! Lütfen tekrar deneyin.\n\nHata: ' + error.message);
        
        // Restore message to textarea
        textarea.value = messageContent;
        textarea.focus();
    });
}

// Add message to chat instantly (optimistic update)
function addMessageToChat(message, isSent, isOptimistic = false) {
    const messagesContainer = document.getElementById('messagesContainer');
    if (!messagesContainer) return;
    
    const messageDiv = document.createElement('div');
    messageDiv.className = `flex ${isSent ? 'justify-end' : 'justify-start'}`;
    if (isOptimistic) messageDiv.classList.add('optimistic-message');
    
    const currentTime = new Date().toLocaleTimeString('tr-TR', { 
        hour: '2-digit', 
        minute: '2-digit' 
    });
    
    messageDiv.innerHTML = `
        <div class="message-bubble p-3 rounded-lg ${isSent ? 'message-sent' : 'message-received'} ${isOptimistic ? 'opacity-70' : ''}">
            <p class="text-sm">${message.replace(/\n/g, '<br>')}</p>
            <p class="text-xs mt-2 opacity-70">
                ${currentTime}
                ${isSent ? '<i class="fas fa-clock ml-1"></i>' : ''}
            </p>
        </div>
    `;
    
    messagesContainer.appendChild(messageDiv);
    scrollToBottom();
}

// Update optimistic message with real data
function updateOptimisticMessage(messageId, realTime) {
    const optimisticMessage = document.querySelector('.optimistic-message');
    if (optimisticMessage) {
        // Remove optimistic styling
        optimisticMessage.classList.remove('optimistic-message');
        const bubble = optimisticMessage.querySelector('.message-bubble');
        bubble.classList.remove('opacity-70');
        
        // Update time and add check mark
        const timeElement = bubble.querySelector('.text-xs');
        timeElement.innerHTML = `${realTime} <i class="fas fa-check ml-1"></i>`;
        
        console.log('✅ Updated optimistic message');
    }
}

// Remove optimistic message if sending failed
function removeOptimisticMessage() {
    const optimisticMessage = document.querySelector('.optimistic-message');
    if (optimisticMessage) {
        optimisticMessage.remove();
        console.log('❌ Removed failed optimistic message');
    }
}

// Check for new messages periodically
function checkForNewMessages() {
    const conversationId = new URLSearchParams(window.location.search).get('conversation');
    if (!conversationId) return;
    
    fetch(`${window.location.href}&get_messages=1&conversation_id=${conversationId}&since=${lastMessageTimestamp}`)
    .then(response => response.json())
    .then(data => {
        if (data.success && data.messages.length > 0) {
            const currentUserId = <?= $user_id ?>;
            
            data.messages.forEach(message => {
                const isSent = message.sender_id == currentUserId;
                if (!isSent) { // Only add messages from other users
                    addMessageToChat(message.message, false);
                    console.log('📨 New message received:', message.message);
                }
                // Update timestamp
                if (message.timestamp > lastMessageTimestamp) {
                    lastMessageTimestamp = message.timestamp;
                }
            });
        }
    })
    .catch(error => {
        console.log('Auto-refresh error:', error);
    });
}

// Start checking for new messages every 3 seconds
setInterval(checkForNewMessages, 3000);

// Test function for debugging
function testFormSubmission() {
    console.log('🧪 TEST: Manual form submission triggered');
    
    const form = document.querySelector('#messageForm');
    const textarea = document.querySelector('#messageTextarea');
    
    if (form && textarea) {
        console.log('📝 Current textarea value:', textarea.value);
        
        // Set a test message
        textarea.value = 'TEST MESSAGE FROM DEBUG';
        console.log('✏️ Set test message');
        
        try {
            console.log('🚀 Calling form.submit()...');
            form.submit();
            console.log('✅ form.submit() completed');
        } catch (error) {
            console.error('❌ form.submit() error:', error);
        }
    } else {
        console.error('❌ Form or textarea not found');
    }
}
</script>

<?php include("includes/footer.php"); ?>
