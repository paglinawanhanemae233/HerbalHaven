<?php
/**
 * AI-Powered Remedy Finder
 * Users describe symptoms, AI recommends herbs from database
 */

session_start();
require_once __DIR__ . '/includes/db-connect.php';
require_once __DIR__ . '/includes/gemini-api.php';
require_once __DIR__ . '/includes/header.php';

$pageTitle = 'AI Remedy Finder';
$includeChatJS = false;

$result = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['symptoms'])) {
    $userInput = trim($_POST['symptoms']);
    
    if (empty($userInput)) {
        $error = 'Please describe your symptoms or condition.';
    } else {
        // Get all herbs from database for context
        $db = getDB();
        $stmt = $db->query("SELECT id, name, description, preparation_methods, dosage_info, safety_warnings FROM herbs ORDER BY name");
        $herbDatabase = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Call Gemini API
        $api = initGeminiAPI();
        $response = $api->analyzeSymptoms($userInput, $herbDatabase);
        
        if ($response['success']) {
            $result = GeminiAPI::formatResponse($response);
            
            // Save to chat history
            $sessionId = session_id();
            $stmt = $db->prepare("INSERT INTO chat_history (session_id, user_message, ai_response) VALUES (:session_id, :user_message, :ai_response)");
            $stmt->execute([
                ':session_id' => $sessionId,
                ':user_message' => $userInput,
                ':ai_response' => $result
            ]);
        } else {
            $error = $response['error'] ?? 'An error occurred while processing your request.';
        }
    }
}
?>

<div class="container">
    <div class="remedy-finder-container" style="max-width: 700px; margin: 0 auto;">
        <h1 style="color: var(--primary-green); margin-bottom: 0.75rem; text-align: center; font-size: 1.75rem;">AI Remedy Finder</h1>
        <p style="text-align: center; color: var(--secondary-text); margin-bottom: 1.5rem; font-size: 0.95rem;">
            Describe your symptoms or health concerns, and our AI will recommend appropriate herbs from our database.
        </p>

        <div class="alert alert-info" style="margin-bottom: 1.5rem; padding: 1rem 1.25rem; font-size: 0.9rem;">
            <strong>How it works:</strong> Simply describe what you're experiencing, and our AI assistant will analyze your symptoms 
            and recommend herbs from our verified database. All recommendations include safety information and preparation methods.
        </div>

        <form method="POST" action="<?php echo SITE_URL; ?>/remedy-finder.php" style="background: var(--white); padding: 1.5rem; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 1.5rem;">
            <div class="form-group">
                <label for="symptoms">Describe your symptoms or health concern:</label>
                <textarea 
                    id="symptoms" 
                    name="symptoms" 
                    class="form-control" 
                    rows="4" 
                    placeholder="e.g., I've been experiencing stress and trouble sleeping, and I'm looking for natural remedies..."
                    required
                ><?php echo isset($_POST['symptoms']) ? htmlspecialchars($_POST['symptoms']) : ''; ?></textarea>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    Find Remedies
                </button>
            </div>
        </form>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($result): ?>
            <div style="background: var(--white); padding: 1.5rem; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-top: 1.5rem;">
                <h2 style="color: var(--primary-green); margin-bottom: 0.75rem; font-size: 1.5rem;">AI Recommendations</h2>
                <div style="color: var(--primary-text); line-height: 1.8; white-space: pre-wrap;">
                    <?php echo nl2br(htmlspecialchars($result)); ?>
                </div>
                
                <div style="margin-top: 1.5rem; padding-top: 1.25rem; border-top: 2px solid var(--accent-green);">
                    <h3 style="color: var(--primary-green); margin-bottom: 0.75rem; font-size: 1.2rem;">Next Steps</h3>
                    <ul style="list-style: none; padding: 0;">
                        <li style="padding: 0.5rem 0;">
                            <a href="<?php echo SITE_URL; ?>/catalogue.php" class="btn btn-secondary" style="text-decoration: none; display: inline-block;">
                                Browse Recommended Herbs
                            </a>
                        </li>
                        <li style="padding: 0.5rem 0;">
                            <a href="<?php echo SITE_URL; ?>/chat-assistant.php" class="btn btn-primary" style="text-decoration: none; display: inline-block;">
                                Ask Follow-up Questions
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        <?php endif; ?>

        <div style="margin-top: 2rem; background: var(--white); padding: 1.5rem; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h3 style="color: var(--primary-green); margin-bottom: 1rem;">Important Reminders</h3>
            <ul style="color: var(--secondary-text); line-height: 1.8;">
                <li>This tool provides educational information only, not medical advice</li>
                <li>Always consult with a qualified healthcare provider before using any herbal remedies</li>
                <li>If you have a medical emergency, seek immediate medical attention</li>
                <li>Be aware of potential interactions with medications you're taking</li>
                <li>Start with small doses and monitor your body's response</li>
            </ul>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

