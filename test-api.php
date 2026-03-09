<?php
/**
 * Test Script for Gemini API Integration
 * This script tests if the API key and integration are working correctly
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/gemini-api.php';

echo "<h1>Gemini API Integration Test</h1>";
echo "<pre>";

// Test 1: Check API Key Configuration
echo "=== Test 1: API Key Configuration ===\n";
if (defined('GEMINI_API_KEY')) {
    $apiKey = GEMINI_API_KEY;
    if ($apiKey === 'your_api_key_here' || empty($apiKey)) {
        echo "❌ FAIL: API key not configured\n";
        echo "   Please set GEMINI_API_KEY in config/config.php\n\n";
    } else {
        echo "✅ PASS: API key is configured\n";
        echo "   Key: " . substr($apiKey, 0, 10) . "...\n\n";
    }
} else {
    echo "❌ FAIL: GEMINI_API_KEY constant not defined\n\n";
}

// Test 2: Check API URL Configuration
echo "=== Test 2: API URL Configuration ===\n";
if (defined('GEMINI_API_URL')) {
    echo "✅ PASS: API URL is configured\n";
    echo "   URL: " . GEMINI_API_URL . "\n\n";
} else {
    echo "❌ FAIL: GEMINI_API_URL constant not defined\n\n";
}

// Test 3: Initialize Gemini API Class
echo "=== Test 3: Initialize Gemini API Class ===\n";
try {
    $api = initGeminiAPI();
    echo "✅ PASS: Gemini API class initialized successfully\n\n";
} catch (Exception $e) {
    echo "❌ FAIL: Error initializing API class\n";
    echo "   Error: " . $e->getMessage() . "\n\n";
}

// Test 4: Test API Connection with Simple Message
echo "=== Test 4: Test API Connection ===\n";
try {
    $testMessage = "Hello, can you respond with just 'API test successful'?";
    echo "Sending test message: \"$testMessage\"\n";
    echo "Please wait...\n\n";
    
    $response = $api->sendChatMessage($testMessage, []);
    
    if ($response['success']) {
        echo "✅ PASS: API connection successful!\n";
        echo "Response:\n";
        echo "----------------------------------------\n";
        echo $response['text'] . "\n";
        echo "----------------------------------------\n\n";
    } else {
        echo "❌ FAIL: API request failed\n";
        echo "Error: " . ($response['error'] ?? 'Unknown error') . "\n";
        if (isset($response['http_code'])) {
            echo "HTTP Code: " . $response['http_code'] . "\n";
        }
        if (isset($response['response'])) {
            echo "Full Response: " . print_r($response['response'], true) . "\n";
        }
        echo "\n";
    }
} catch (Exception $e) {
    echo "❌ FAIL: Exception occurred\n";
    echo "Error: " . $e->getMessage() . "\n\n";
}

// Test 5: Check cURL Extension
echo "=== Test 5: PHP cURL Extension ===\n";
if (function_exists('curl_init')) {
    echo "✅ PASS: cURL extension is available\n\n";
} else {
    echo "❌ FAIL: cURL extension is not available\n";
    echo "   Please enable the cURL extension in PHP\n\n";
}

// Test 6: Check Database Connection (for system prompt)
echo "=== Test 6: Database Connection ===\n";
try {
    require_once __DIR__ . '/includes/db-connect.php';
    $db = getDB();
    echo "✅ PASS: Database connection successful\n";
    
    // Check if herbs table exists
    $stmt = $db->query("SELECT COUNT(*) as count FROM herbs");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   Herbs in database: " . $result['count'] . "\n\n";
} catch (Exception $e) {
    echo "⚠️  WARNING: Database connection failed\n";
    echo "   Error: " . $e->getMessage() . "\n";
    echo "   Note: API can still work without database, but system prompt won't include herb list\n\n";
}

echo "</pre>";
echo "<p><a href='chat-assistant.php'>Go to Chat Assistant</a> | <a href='remedy-finder.php'>Go to Remedy Finder</a></p>";
?>

