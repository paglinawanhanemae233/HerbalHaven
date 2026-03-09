<?php
/**
 * Homepage - Herbal Haven
 * Features: Hero section, featured herbs, quick links
 */

session_start();
require_once __DIR__ . '/includes/db-connect.php';

// Check if database tables exist
try {
    $db = getDB();
    $db->query("SELECT 1 FROM herbs LIMIT 1");
} catch (PDOException $e) {
    // Database tables don't exist - redirect to setup
    if (strpos($e->getMessage(), "doesn't exist") !== false) {
        require_once __DIR__ . '/config/config.php';
        header('Location: ' . SITE_URL . '/setup.php');
        exit;
    }
    // Other database errors
    require_once __DIR__ . '/config/config.php';
    die("Database error: " . htmlspecialchars($e->getMessage()) . "<br><br><a href='" . SITE_URL . "/setup.php'>Go to Setup Page</a>");
}

require_once __DIR__ . '/includes/header.php';

$pageTitle = 'Home';

// Get featured herbs (most popular or recently added)
$db = getDB();
$stmt = $db->query("SELECT * FROM herbs ORDER BY created_at DESC LIMIT 6");
$featuredHerbs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="hero">
    <h2>Welcome to Herbal Haven</h2>
    <p>Discover the healing power of nature with our comprehensive guide to herbal remedies</p>
</div>

<div class="container">
    <section class="quick-links">
        <h2 class="text-center" style="color: var(--primary-green); margin-bottom: 2rem;">Quick Access</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; margin-bottom: 3rem; max-width: 800px; margin-left: auto; margin-right: auto;">
            <a href="<?php echo SITE_URL; ?>/catalogue.php" class="btn btn-primary" style="text-align: center; padding: 2rem; display: block; text-decoration: none;">
                <h3 style="margin-bottom: 0.5rem;">📚 Browse Catalogue</h3>
                <p style="font-size: 0.9rem; opacity: 0.9;">Explore our complete collection of herbs</p>
            </a>
            <a href="<?php echo SITE_URL; ?>/chat-assistant.php" class="btn btn-primary" style="text-align: center; padding: 2rem; display: block; text-decoration: none;">
                <h3 style="margin-bottom: 0.5rem;">💬 AI Assistant</h3>
                <p style="font-size: 0.9rem; opacity: 0.9;">Chat with our herbal expert</p>
            </a>
        </div>
    </section>

    <section class="featured-herbs">
        <h2 class="text-center" style="color: var(--primary-green); margin-bottom: 2rem;">Featured Herbs</h2>
        <div class="card-grid">
            <?php foreach ($featuredHerbs as $herb): ?>
                <div class="herb-card">
                    <div class="herb-card-image">
                        <?php 
                        $imagePath = !empty($herb['image_url']) ? $herb['image_url'] : '';
                        // Convert /images/herbs/... to images/herbs/... for file_exists check
                        $relativePath = ltrim($imagePath, '/');
                        $fullImagePath = __DIR__ . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
                        $imageExists = $imagePath && file_exists($fullImagePath);
                        if ($imageExists): 
                            // Extract subdirectory from SITE_URL (e.g., /HerbalHaven)
                            // Need to include config since header.php is not included in index.php
                            require_once __DIR__ . '/config/config.php';
                            $subdir = parse_url(SITE_URL, PHP_URL_PATH);
                            $displayPath = $subdir . $imagePath;
                        ?>
                            <img src="<?php echo htmlspecialchars($displayPath); ?>" alt="<?php echo htmlspecialchars($herb['name']); ?>" onerror="this.style.display='none'; this.parentElement.innerHTML='🌿';">
                        <?php else: ?>
                            🌿
                        <?php endif; ?>
                    </div>
                    <div class="herb-card-content">
                        <h3><?php echo htmlspecialchars($herb['name']); ?></h3>
                        <p class="scientific-name"><?php echo htmlspecialchars($herb['scientific_name']); ?></p>
                        <p><?php echo htmlspecialchars(substr($herb['description'], 0, 120)) . '...'; ?></p>
                        <a href="<?php echo SITE_URL; ?>/herb-detail.php?id=<?php echo $herb['id']; ?>" class="herb-card-link">Learn More</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="info-section" style="background: var(--white); padding: 2rem; border-radius: 10px; margin: 3rem 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h2 class="text-center" style="color: var(--primary-green); margin-bottom: 1rem;">About Herbal Haven</h2>
        <p style="text-align: center; color: var(--secondary-text); max-width: 800px; margin: 0 auto;">
            Herbal Haven is your trusted educational resource for learning about herbal remedies and natural wellness. 
            Our platform combines traditional herbal knowledge with modern AI technology to help you discover safe and 
            effective natural remedies. Remember, this information is for educational purposes only - always consult 
            with a qualified healthcare provider before using any herbal remedies.
        </p>
    </section>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

