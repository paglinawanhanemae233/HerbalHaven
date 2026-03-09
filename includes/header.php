<?php
/**
 * Header Include
 * Common header for all pages
 */
require_once __DIR__ . '/../config/config.php';

if (!isset($pageTitle)) {
    $pageTitle = SITE_NAME;
} else {
    $pageTitle = $pageTitle . ' - ' . SITE_NAME;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Herbal Haven - Your trusted source for herbal remedy information and natural wellness guidance">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/style.css">
</head>
<body>
    <header class="main-header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="<?php echo SITE_URL; ?>/index.php">
                        <h1>🌿 Herbal Haven</h1>
                    </a>
                </div>
                <nav class="main-nav">
                    <ul>
                        <li><a href="<?php echo SITE_URL; ?>/index.php">Home</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/catalogue.php">Catalogue</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <div class="disclaimer-banner">
        <div class="container">
            <p><strong>⚠️ Medical Disclaimer:</strong> This information is for educational purposes only and is not intended as medical advice. Always consult with a qualified healthcare provider before using herbal remedies, especially if you are pregnant, nursing, taking medications, or have a medical condition.</p>
        </div>
    </div>

    <main class="main-content">

