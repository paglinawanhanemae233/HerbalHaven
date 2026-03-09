<?php
/**
 * Gemini API Integration
 * Handles all interactions with Google Gemini API
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/db-connect.php';

class GeminiAPI {
    private $apiKey;
    private $apiUrl;
    private $model;

    public function __construct() {
        $this->apiKey = GEMINI_API_KEY;
        $this->apiUrl = GEMINI_API_URL;
        $this->model = GEMINI_MODEL;
    }

    /**
     * Initialize Gemini API
     */
    public static function init() {
        return new self();
    }

    /**
     * Send chat message to Gemini
     * @param string $message User message
     * @param array $conversationHistory Previous messages for context
     * @return array Response from API
     */
    public function sendChatMessage($message, $conversationHistory = []) {
        if (empty($this->apiKey) || $this->apiKey === 'your_api_key_here') {
            return [
                'success' => false,
                'error' => 'Gemini API key not configured. Please set your API key in config/config.php'
            ];
        }

        $systemPrompt = $this->getSystemPrompt();
        
        // Build conversation context
        $contents = [];
        
        // Add system context as first message
        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $systemPrompt]]
        ];
        
        $contents[] = [
            'role' => 'model',
            'parts' => [['text' => 'I understand. I am a knowledgeable herbal remedy assistant for Herbal Haven. I will provide accurate, safe, and educational information about herbs and natural remedies while always reminding users to consult healthcare providers.']]
        ];

        // Add conversation history
        foreach ($conversationHistory as $msg) {
            $contents[] = [
                'role' => $msg['role'] === 'user' ? 'user' : 'model',
                'parts' => [['text' => $msg['content']]]
            ];
        }

        // Add current message
        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $message]]
        ];

        $payload = [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => 0.7,
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => 2048,
            ],
            'safetySettings' => [
                [
                    'category' => 'HARM_CATEGORY_HARASSMENT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ],
                [
                    'category' => 'HARM_CATEGORY_HATE_SPEECH',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ],
                [
                    'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ],
                [
                    'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ]
            ]
        ];

        return $this->makeRequest($payload);
    }

    /**
     * Analyze symptoms and recommend remedies
     * @param string $userInput User's symptom description
     * @param array $herbDatabase Array of herbs from database
     * @return array Response with recommendations
     */
    public function analyzeSymptoms($userInput, $herbDatabase) {
        $herbNames = array_column($herbDatabase, 'name');
        $herbList = implode(', ', $herbNames);
        
        $prompt = "A user is experiencing the following symptoms/conditions: {$userInput}\n\n";
        $prompt .= "Available herbs in our database: {$herbList}\n\n";
        $prompt .= "Please analyze their symptoms and recommend appropriate herbs from our database. ";
        $prompt .= "For each recommended herb, explain why it might help and how it could be used. ";
        $prompt .= "Always emphasize safety, contraindications, and the need to consult a healthcare provider. ";
        $prompt .= "If no herbs in our database are appropriate, say so clearly.";

        return $this->sendChatMessage($prompt, []);
    }

    /**
     * Get information about a specific herb
     * @param string $herbName Name of the herb
     * @param string $question User's question about the herb
     * @param array $herbData Herb data from database
     * @return array Response
     */
    public function getHerbInfo($herbName, $question, $herbData) {
        $context = "Herb: {$herbName}\n";
        $context .= "Scientific Name: {$herbData['scientific_name']}\n";
        $context .= "Description: {$herbData['description']}\n";
        $context .= "Preparation Methods: {$herbData['preparation_methods']}\n";
        $context .= "Dosage Info: {$herbData['dosage_info']}\n";
        $context .= "Safety Warnings: {$herbData['safety_warnings']}\n\n";
        $context .= "User Question: {$question}";

        return $this->sendChatMessage($context, []);
    }

    /**
     * Check safety of herb for user conditions
     * @param string $herbName Name of the herb
     * @param array $userConditions User's health conditions/allergies
     * @param array $contraindications Contraindications from database
     * @return array Safety assessment
     */
    public function checkSafety($herbName, $userConditions, $contraindications) {
        $contraText = implode("\n", array_column($contraindications, 'warning_text'));
        
        $prompt = "Check the safety of {$herbName} for a user with the following conditions: ";
        $prompt .= implode(', ', $userConditions) . "\n\n";
        $prompt .= "Known contraindications for this herb:\n{$contraText}\n\n";
        $prompt .= "Provide a safety assessment and any warnings.";

        return $this->sendChatMessage($prompt, []);
    }

    /**
     * Get system prompt for Gemini
     * @return string System prompt
     */
    private function getSystemPrompt() {
        $db = getDB();
        
        // Get all herb names from database
        $stmt = $db->query("SELECT name FROM herbs ORDER BY name");
        $herbs = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $herbList = implode(', ', $herbs);

        $prompt = "You are a knowledgeable herbal remedy assistant for Herbal Haven, an educational platform about herbs and natural remedies.\n\n";
        $prompt .= "IMPORTANT RULES:\n";
        $prompt .= "1. Always remind users to consult healthcare providers before using any herbal remedies\n";
        $prompt .= "2. Never diagnose medical conditions - you can only provide educational information\n";
        $prompt .= "3. Always mention contraindications and safety warnings when discussing herbs\n";
        $prompt .= "4. Be clear that this is educational information, not medical advice\n";
        $prompt .= "5. When recommending herbs, only suggest those in our database: {$herbList}\n";
        $prompt .= "6. If you're unsure about something, say so - don't make up information\n";
        $prompt .= "7. Prioritize user safety above all else\n";
        $prompt .= "8. Be friendly, helpful, and professional\n";
        $prompt .= "9. Provide accurate, evidence-based information when available\n";
        $prompt .= "10. If a user asks about herbs not in our database, acknowledge this and suggest they consult our catalogue\n\n";
        $prompt .= "Available herbs in database: {$herbList}";

        return $prompt;
    }

    /**
     * Make HTTP request to Gemini API
     * @param array $payload Request payload
     * @return array Response
     */
    private function makeRequest($payload) {
        $url = $this->apiUrl . '?key=' . $this->apiKey;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return [
                'success' => false,
                'error' => 'API request failed: ' . $error
            ];
        }

        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            $errorMessage = 'API error';
            if (isset($errorData['error']['message'])) {
                $errorMessage = $errorData['error']['message'];
            } elseif (isset($errorData['error'])) {
                $errorMessage = is_string($errorData['error']) ? $errorData['error'] : json_encode($errorData['error']);
            } elseif (!empty($response)) {
                $errorMessage = 'Unexpected response: ' . substr($response, 0, 200);
            }
            return [
                'success' => false,
                'error' => $errorMessage,
                'http_code' => $httpCode,
                'raw_response' => $response
            ];
        }

        $data = json_decode($response, true);

        if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            return [
                'success' => true,
                'text' => $data['candidates'][0]['content']['parts'][0]['text'],
                'full_response' => $data
            ];
        } else {
            return [
                'success' => false,
                'error' => 'Unexpected API response format',
                'response' => $data
            ];
        }
    }

    /**
     * Format response for display
     * @param array $geminiResponse Raw API response
     * @return string Formatted text
     */
    public static function formatResponse($geminiResponse) {
        if (!$geminiResponse['success']) {
            return 'I apologize, but I encountered an error. Please try again later.';
        }

        $text = $geminiResponse['text'];
        
        // Clean up excessive asterisks (markdown formatting)
        // Remove standalone asterisks used for bullet points, convert to cleaner format
        $text = preg_replace('/^\s*\*\s+/m', '• ', $text); // Convert * to bullet
        $text = preg_replace('/\*\*([^*]+)\*\*/', '$1', $text); // Remove **bold** markers
        $text = preg_replace('/\*([^*]+)\*/', '$1', $text); // Remove *italic* markers
        $text = preg_replace('/\*{3,}/', '', $text); // Remove multiple asterisks
        $text = preg_replace('/\s+\*\s+/', ' ', $text); // Remove standalone asterisks with spaces
        
        // Clean up extra whitespace
        $text = preg_replace('/\n{3,}/', "\n\n", $text); // Max 2 newlines
        $text = trim($text);
        
        // Add disclaimer if not already present
        if (stripos($text, 'consult') === false && stripos($text, 'healthcare') === false) {
            $text .= "\n\n⚠️ Remember: This information is for educational purposes only. Always consult with a qualified healthcare provider before using herbal remedies.";
        }

        return $text;
    }
}

// Helper functions
function initGeminiAPI() {
    return GeminiAPI::init();
}

function sendChatMessage($message, $conversationHistory = []) {
    $api = initGeminiAPI();
    return $api->sendChatMessage($message, $conversationHistory);
}

function analyzeSymptoms($userInput, $herbDatabase) {
    $api = initGeminiAPI();
    return $api->analyzeSymptoms($userInput, $herbDatabase);
}

function getHerbInfo($herbName, $question, $herbData) {
    $api = initGeminiAPI();
    return $api->getHerbInfo($herbName, $question, $herbData);
}

function checkSafety($herbName, $userConditions, $contraindications) {
    $api = initGeminiAPI();
    return $api->checkSafety($herbName, $userConditions, $contraindications);
}
?>

