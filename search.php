<?php
/**
 * Search Results Page
 * Handles search queries for herbs, conditions, and symptoms
 */

session_start();
require_once __DIR__ . '/includes/db-connect.php';
require_once __DIR__ . '/includes/header.php';

$pageTitle = 'Search Results';

$query = trim($_GET['q'] ?? '');

if (empty($query)) {
    require_once __DIR__ . '/config/config.php';
    header('Location: ' . SITE_URL . '/index.php');
    exit;
}

$db = getDB();
$searchTerm = '%' . $query . '%';

// Search herbs by name, scientific name, or description
$stmt = $db->prepare("
    SELECT DISTINCT h.* 
    FROM herbs h
    WHERE h.name LIKE :query 
       OR h.scientific_name LIKE :query 
       OR h.description LIKE :query
    ORDER BY 
        CASE 
            WHEN h.name LIKE :exact THEN 1
            WHEN h.name LIKE :query THEN 2
            ELSE 3
        END,
        h.name ASC
");
$exactTerm = $query . '%';
$stmt->execute([
    ':query' => $searchTerm,
    ':exact' => $exactTerm
]);
$herbs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Search conditions
$stmt = $db->prepare("
    SELECT DISTINCT h.* 
    FROM herbs h
    INNER JOIN herbs_conditions hc ON h.id = hc.herb_id
    INNER JOIN health_conditions cond ON hc.condition_id = cond.id
    WHERE cond.condition_name LIKE :query 
       OR cond.description LIKE :query
    ORDER BY h.name ASC
");
$stmt->execute([':query' => $searchTerm]);
$conditionHerbs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Merge results, removing duplicates
$allHerbs = [];
$herbIds = [];

foreach ($herbs as $herb) {
    if (!in_array($herb['id'], $herbIds)) {
        $allHerbs[] = $herb;
        $herbIds[] = $herb['id'];
    }
}

foreach ($conditionHerbs as $herb) {
    if (!in_array($herb['id'], $herbIds)) {
        $allHerbs[] = $herb;
        $herbIds[] = $herb['id'];
    }
}
?>

<div class="container">
    <h1 style="color: var(--primary-green); margin-bottom: 1rem;">Search Results</h1>
    <p style="color: var(--secondary-text); margin-bottom: 2rem;">
        Searching for: <strong><?php echo htmlspecialchars($query); ?></strong>
    </p>

    <?php if (empty($allHerbs)): ?>
        <div class="alert alert-info">
            <h3>No results found</h3>
            <p>We couldn't find any herbs matching "<?php echo htmlspecialchars($query); ?>"</p>
            <p style="margin-top: 1rem;">
                <a href="<?php echo SITE_URL; ?>/chat-assistant.php" class="btn btn-primary">Ask AI Assistant</a>
                <a href="<?php echo SITE_URL; ?>/catalogue.php" class="btn btn-secondary" style="margin-left: 0.5rem;">Browse All Herbs</a>
            </p>
        </div>
    <?php else: ?>
        <p style="color: var(--secondary-text); margin-bottom: 1rem;">
            Found <?php echo count($allHerbs); ?> result<?php echo count($allHerbs) !== 1 ? 's' : ''; ?>
        </p>

        <div class="card-grid">
            <?php foreach ($allHerbs as $herb): ?>
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
                            // SITE_URL is already defined in header.php
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
                        <a href="<?php echo SITE_URL; ?>/herb-detail.php?id=<?php echo $herb['id']; ?>" class="herb-card-link">View Details</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div style="margin-top: 2rem; text-align: center;">
            <p style="color: var(--secondary-text); margin-bottom: 1rem;">
                Didn't find what you're looking for?
            </p>
            <a href="<?php echo SITE_URL; ?>/chat-assistant.php" class="btn btn-primary">Ask AI Assistant</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

