<?php
/**
 * Herb Detail Page
 * Shows complete information about a specific herb
 */

session_start();
require_once __DIR__ . '/includes/db-connect.php';
require_once __DIR__ . '/includes/header.php';

$pageTitle = 'Herb Details';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    require_once __DIR__ . '/config/config.php';
    header('Location: ' . SITE_URL . '/catalogue.php');
    exit;
}

$herbId = (int)$_GET['id'];
$db = getDB();

// Get herb details
$stmt = $db->prepare("SELECT * FROM herbs WHERE id = :id");
$stmt->execute([':id' => $herbId]);
$herb = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$herb) {
    require_once __DIR__ . '/config/config.php';
    header('Location: ' . SITE_URL . '/catalogue.php');
    exit;
}

// Get related conditions
$stmt = $db->prepare("
    SELECT hc.condition_name, hc.description, hc_effect.effectiveness_note 
    FROM herbs_conditions hc_effect
    INNER JOIN health_conditions hc ON hc_effect.condition_id = hc.id
    WHERE hc_effect.herb_id = :id
");
$stmt->execute([':id' => $herbId]);
$relatedConditions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get contraindications
$stmt = $db->prepare("
    SELECT * FROM contraindications 
    WHERE herb_id = :id 
    ORDER BY 
        CASE severity 
            WHEN 'high' THEN 1 
            WHEN 'medium' THEN 2 
            WHEN 'low' THEN 3 
        END
");
$stmt->execute([':id' => $herbId]);
$contraindications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get related herbs (herbs that share conditions)
$stmt = $db->prepare("
    SELECT DISTINCT h.* 
    FROM herbs h
    INNER JOIN herbs_conditions hc1 ON h.id = hc1.herb_id
    INNER JOIN herbs_conditions hc2 ON hc1.condition_id = hc2.condition_id
    WHERE hc2.herb_id = :current_herb_id AND h.id != :exclude_herb_id
    LIMIT 4
");
$stmt->execute([
    ':current_herb_id' => $herbId,
    ':exclude_herb_id' => $herbId
]);
$relatedHerbs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = $herb['name'];
?>

<div class="container">
    <div class="herb-detail">
        <div class="herb-detail-header">
            <div class="herb-detail-image">
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
            <div class="herb-detail-info">
                <h1><?php echo htmlspecialchars($herb['name']); ?></h1>
                <p class="scientific-name"><?php echo htmlspecialchars($herb['scientific_name']); ?></p>
                <a href="<?php echo SITE_URL; ?>/chat-assistant.php?herb=<?php echo urlencode($herb['name']); ?>" class="btn btn-primary" style="margin-top: 0.75rem; padding: 0.625rem 1.25rem; font-size: 0.95rem;">
                    Ask AI about this herb
                </a>
            </div>
        </div>

        <div class="herb-detail-section">
            <h2>Description</h2>
            <p><?php echo nl2br(htmlspecialchars($herb['description'])); ?></p>
        </div>

        <?php if (!empty($relatedConditions)): ?>
        <div class="herb-detail-section">
            <h2>Common Uses</h2>
            <ul style="list-style: none; padding: 0;">
                <?php foreach ($relatedConditions as $condition): ?>
                    <li style="padding: 0.375rem 0; border-bottom: 1px solid rgba(74, 124, 89, 0.2);">
                        <strong style="color: var(--primary-green); font-size: 0.95rem;"><?php echo htmlspecialchars($condition['condition_name']); ?></strong>
                        <?php if ($condition['effectiveness_note']): ?>
                            <p style="margin: 0.2rem 0 0 0.75rem; color: var(--secondary-text); font-size: 0.9rem;">
                                <?php echo htmlspecialchars($condition['effectiveness_note']); ?>
                            </p>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <div class="herb-detail-section">
            <h2>Preparation Methods</h2>
            <p><?php echo nl2br(htmlspecialchars($herb['preparation_methods'])); ?></p>
        </div>

        <div class="herb-detail-section">
            <h2>Dosage Information</h2>
            <p><?php echo nl2br(htmlspecialchars($herb['dosage_info'])); ?></p>
        </div>

        <?php if (!empty($contraindications)): ?>
        <div class="safety-warnings <?php echo in_array('high', array_column($contraindications, 'severity')) ? 'high' : ''; ?>">
            <h3>⚠️ Safety Warnings & Contraindications</h3>
            <?php foreach ($contraindications as $contra): ?>
                <div class="contraindication-item <?php echo $contra['severity']; ?>">
                    <strong><?php echo htmlspecialchars($contra['warning_text']); ?></strong>
                    <span class="severity-badge <?php echo $contra['severity']; ?>">
                        <?php echo ucfirst($contra['severity']); ?> Risk
                    </span>
                    <?php if ($contra['category']): ?>
                        <span style="color: var(--secondary-text); font-size: 0.9rem; display: block; margin-top: 0.25rem;">
                            Category: <?php echo htmlspecialchars($contra['category']); ?>
                        </span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if ($herb['safety_warnings']): ?>
        <div class="herb-detail-section">
            <h2>Additional Safety Information</h2>
            <p><?php echo nl2br(htmlspecialchars($herb['safety_warnings'])); ?></p>
        </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($relatedHerbs)): ?>
    <section style="margin-top: 2rem;">
        <h2 style="color: var(--primary-green); margin-bottom: 1rem; font-size: 1.5rem;">Related Herbs</h2>
        <div class="card-grid">
            <?php foreach ($relatedHerbs as $relatedHerb): ?>
                <div class="herb-card">
                    <div class="herb-card-image">
                        <?php 
                        $imagePath = !empty($relatedHerb['image_url']) ? $relatedHerb['image_url'] : '';
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
                            <img src="<?php echo htmlspecialchars($displayPath); ?>" alt="<?php echo htmlspecialchars($relatedHerb['name']); ?>" onerror="this.style.display='none'; this.parentElement.innerHTML='🌿';">
                        <?php else: ?>
                            🌿
                        <?php endif; ?>
                    </div>
                    <div class="herb-card-content">
                        <h3><?php echo htmlspecialchars($relatedHerb['name']); ?></h3>
                        <p class="scientific-name"><?php echo htmlspecialchars($relatedHerb['scientific_name']); ?></p>
                        <a href="<?php echo SITE_URL; ?>/herb-detail.php?id=<?php echo $relatedHerb['id']; ?>" class="herb-card-link">View Details</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

