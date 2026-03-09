<?php
/**
 * List Available Gemini Models
 * This script lists all available models for your API key
 */

require_once __DIR__ . '/config/config.php';

$apiKey = GEMINI_API_KEY;

// Try to list models using the ListModels endpoint
$url = "https://generativelanguage.googleapis.com/v1beta/models?key={$apiKey}";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "<h1>Available Gemini Models</h1>";
echo "<pre>";

if ($error) {
    echo "❌ cURL Error: {$error}\n\n";
} else {
    echo "HTTP Code: {$httpCode}\n\n";
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        
        if (isset($data['models'])) {
            echo "✅ Found " . count($data['models']) . " available models:\n\n";
            
            foreach ($data['models'] as $model) {
                $name = $model['name'] ?? 'Unknown';
                $displayName = $model['displayName'] ?? 'N/A';
                $supportedMethods = isset($model['supportedGenerationMethods']) 
                    ? implode(', ', $model['supportedGenerationMethods']) 
                    : 'N/A';
                
                echo "Model: {$name}\n";
                echo "  Display Name: {$displayName}\n";
                echo "  Supported Methods: {$supportedMethods}\n";
                
                // Extract model ID from name (e.g., "models/gemini-pro" -> "gemini-pro")
                if (strpos($name, 'models/') === 0) {
                    $modelId = substr($name, 7);
                    echo "  Model ID: {$modelId}\n";
                }
                echo "\n";
            }
        } else {
            echo "Response structure:\n";
            print_r($data);
        }
    } else {
        echo "❌ Error Response:\n";
        echo $response . "\n";
    }
}

echo "</pre>";
echo "<p><a href='test-api.php'>Back to API Test</a></p>";
?>

