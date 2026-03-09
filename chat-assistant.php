<?php
/**
 * AI Chat Assistant
 * Real-time chat interface with Gemini API
 */

// Handle AJAX chat requests FIRST - before any HTML output
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_message') {
    // Start session and load required files for AJAX
    session_start();
    require_once __DIR__ . '/config/config.php';
    require_once __DIR__ . '/includes/db-connect.php';
    require_once __DIR__ . '/includes/gemini-api.php';
    
    // Set JSON header
    header('Content-Type: application/json');
    
    $message = trim($_POST['message'] ?? '');
    $sessionId = session_id();
    
    if (empty($message)) {
        echo json_encode(['success' => false, 'error' => 'Message cannot be empty']);
        exit;
    }
    
    // Get conversation history
    $db = getDB();
    $stmt = $db->prepare("
        SELECT user_message, ai_response 
        FROM chat_history 
        WHERE session_id = :session_id 
        ORDER BY timestamp DESC 
        LIMIT 10
    ");
    $stmt->execute([':session_id' => $sessionId]);
    $history = array_reverse($stmt->fetchAll(PDO::FETCH_ASSOC));
    
    // Build conversation history for API
    $conversationHistory = [];
    foreach ($history as $msg) {
        $conversationHistory[] = ['role' => 'user', 'content' => $msg['user_message']];
        $conversationHistory[] = ['role' => 'assistant', 'content' => $msg['ai_response']];
    }
    
    // Get initial herb context if provided
    $initialHerb = $_POST['initial_herb'] ?? '';
    if ($initialHerb && empty($conversationHistory)) {
        $message = "I'd like to learn about {$initialHerb}. " . $message;
    }
    
    // Call Gemini API
    $api = initGeminiAPI();
    $response = $api->sendChatMessage($message, $conversationHistory);
    
    if ($response['success']) {
        $aiResponse = GeminiAPI::formatResponse($response);
        
        // Save to chat history
        $stmt = $db->prepare("INSERT INTO chat_history (session_id, user_message, ai_response) VALUES (:session_id, :user_message, :ai_response)");
        $stmt->execute([
            ':session_id' => $sessionId,
            ':user_message' => $message,
            ':ai_response' => $aiResponse
        ]);
        
        echo json_encode(['success' => true, 'response' => $aiResponse]);
    } else {
        echo json_encode(['success' => false, 'error' => $response['error'] ?? 'An error occurred']);
    }
    exit;
}

// Regular page load - include header and display page
session_start();
require_once __DIR__ . '/includes/db-connect.php';
require_once __DIR__ . '/includes/gemini-api.php';
require_once __DIR__ . '/includes/header.php';

$pageTitle = 'AI Chat Assistant';
$includeChatJS = true;

// Get initial herb from query parameter
$initialHerb = $_GET['herb'] ?? '';
?>

<div class="container">
    <div class="chatbot-wrapper">
        <div class="chat-container">
            <!-- Chat Header -->
            <div class="chat-header">
                <div class="chat-header-content">
                    <div class="chat-avatar">🤖</div>
                    <div class="chat-header-info">
                        <h3 class="chat-title">Herbal Haven AI</h3>
                        <span class="chat-status">Online</span>
                    </div>
                </div>
                <?php if ($initialHerb): ?>
                    <div class="chat-context-badge">
                        <span>🌿</span> <?php echo htmlspecialchars($initialHerb); ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Chat Messages Area -->
            <div class="chat-messages" id="chatMessages">
                <div class="chat-message ai">
                    <div class="message-bubble">
                        <p>Hello! I'm your Herbal Haven AI assistant. How can I help you today?</p>
                    </div>
                </div>
                
                <!-- Suggested Questions (inside chat) -->
                <div class="suggestions-container">
                    <p class="suggestions-label">Try asking:</p>
                    <div class="suggestions-grid">
                        <button class="suggestion-chip" data-question="What herbs can help with stress?">Stress relief</button>
                        <button class="suggestion-chip" data-question="How do I prepare chamomile tea?">Tea preparation</button>
                        <button class="suggestion-chip" data-question="Are there any safety concerns with ginger?">Safety info</button>
                        <button class="suggestion-chip" data-question="What herbs support immune health?">Immune support</button>
                    </div>
                </div>
            </div>
            
            <!-- Chat Input Area -->
            <div class="chat-input-container">
                <form id="chatForm" class="chat-input-form" action="javascript:void(0);" onsubmit="return false;">
                    <input 
                        type="text" 
                        id="chatInput" 
                        class="chat-input" 
                        placeholder="Type your message..."
                        autocomplete="off"
                    >
                    <button type="submit" class="chat-send-btn" id="sendBtn" title="Send message">
                        <span class="send-icon">➤</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Store initial herb for context (in global scope for chat.js to access)
window.initialHerb = <?php echo json_encode($initialHerb); ?>;
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

