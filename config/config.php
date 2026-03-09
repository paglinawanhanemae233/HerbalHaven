<?php
/**
 * Configuration File
 * IMPORTANT: Add this file to .gitignore in production
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'herbal_haven');

// Gemini API Configuration
define('GEMINI_API_KEY', 'AIzaSyDHZA_1q4OA7oyxD4bsC0JLbp-M0JJXJqE');
define('GEMINI_MODEL', 'gemini-2.5-flash');
define('GEMINI_API_URL', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent');

// App Settings
define('SITE_NAME', 'Herbal Haven');
define('SITE_URL', 'http://localhost/HerbalHaven');

// Security
define('SESSION_LIFETIME', 3600); // 1 hour

// Rate Limiting (requests per minute)
define('API_RATE_LIMIT', 30);
?>

