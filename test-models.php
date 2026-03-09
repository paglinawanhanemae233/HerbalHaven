<?php
/**
 * Test Multiple Gemini Model Names
 * Tests different model names to find one that works
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/gemini-api.php';

$apiKey = GEMINI_API_KEY;
$testMessage = "Hello";

// Models to test
$modelsToTest = [
    'gemini-1.5-flash-latest' => 'https://generativelanguage.googleapis.com/v1/models/gemini-1.5-flash-latest:generateContent',
    'gemini-1.5-pro-latest' => 'https://generativelanguage.googleapis.com/v1/models/gemini-1.5-pro-latest:generateContent',
    'gemini-1.5-flash' => 'https://generativelanguage.googleapis.com/v1/models/gemini-1.5-flash:generateContent',
    'gemini-1.5-pro' => 'https://generativelanguage.googleapis.com/v1/models/gemini-1.5-pro:generateContent',
    'gemini-pro' => 'https://generativelanguage.googleapis.com/v1/models/gemini-pro:generateContent',
    'gemini-1.5-flash-latest-v1beta' => 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent',
    'gemini-1.5-pro-latest-v1beta' => 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-pro-latest:generateContent',
];

echo "<h1>Testing Gemini Models</h1>";
echo "<pre>";

$workingModel = null;

foreach ($modelsToTest as $modelName => $apiUrl) {
    echo "Testing: {$modelName}\n";
    echo "URL: {$apiUrl}\n";
    
    $url = $apiUrl . '?key=' . $apiKey;
    
    $payload = [
        'contents' => [
            [
                'role' => 'user',
                'parts' => [['text' => $testMessage]]
            ]
        ]
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            echo "✅ SUCCESS! This model works!\n";
            echo "Response: " . substr($data['candidates'][0]['content']['parts'][0]['text'], 0, 100) . "...\n\n";
            $workingModel = ['name' => $modelName, 'url' => $apiUrl];
            break;
        } else {
            echo "⚠️  HTTP 200 but unexpected response format\n";
            echo substr($response, 0, 200) . "...\n\n";
        }
    } else {
        $errorData = json_decode($response, true);
        $errorMsg = $errorData['error']['message'] ?? 'Unknown error';
        echo "❌ FAILED (HTTP {$httpCode}): {$errorMsg}\n\n";
    }
}

echo "</pre>";

if ($workingModel) {
    echo "<h2>✅ Working Model Found!</h2>";
    echo "<p>Model: <strong>{$workingModel['name']}</strong></p>";
    echo "<p>Update your config/config.php with:</p>";
    echo "<pre>";
    echo "define('GEMINI_MODEL', '{$workingModel['name']}');\n";
    echo "define('GEMINI_API_URL', '{$workingModel['url']}');\n";
    echo "</pre>";
} else {
    echo "<h2>❌ No Working Models Found</h2>";
    echo "<p>Please check:</p>";
    echo "<ul>";
    echo "<li>Your API key is valid</li>";
    echo "<li>You have access to the Gemini API</li>";
    echo "<li>Visit <a href='list-models.php'>list-models.php</a> to see available models</li>";
    echo "</ul>";
}

echo "<p><a href='test-api.php'>Back to API Test</a> | <a href='list-models.php'>List Available Models</a></p>";
?>

