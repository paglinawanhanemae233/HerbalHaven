<?php
/**
 * Configuration Template
 * Copy this file to config.php and add your actual values
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'herbal_haven');

// Gemini API Configuration
define('GEMINI_API_KEY', 'your_api_key_here');
define('GEMINI_MODEL', 'gemini-pro');
define('GEMINI_API_URL', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent');

// App Settings
define('SITE_NAME', 'Herbal Haven');
define('SITE_URL', 'http://localhost/herbal-haven');

// Security
define('SESSION_LIFETIME', 3600); // 1 hour

// Rate Limiting (requests per minute)
define('API_RATE_LIMIT', 30);
?>

