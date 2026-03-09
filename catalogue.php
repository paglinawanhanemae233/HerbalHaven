<?php
/**
 * Herb Catalogue - Browse all herbs
 * Features: Grid display with pagination
 */

session_start();
require_once __DIR__ . '/includes/db-connect.php';
require_once __DIR__ . '/includes/header.php';

$pageTitle = 'Herb Catalogue';

$db = getDB();

// Get pagination parameters
$currentPage = max(1, (int)($_GET['page'] ?? 1));
$itemsPerPage = 12;

// Get total count
$stmt = $db->query("SELECT COUNT(*) as total FROM herbs");
$totalHerbs = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = max(1, ceil($totalHerbs / $itemsPerPage));
$currentPage = min($currentPage, $totalPages);

// Get herbs with pagination
$offset = ($currentPage - 1) * $itemsPerPage;
$stmt = $db->prepare("SELECT * FROM herbs ORDER BY name ASC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $itemsPerPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$herbs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <h1 style="color: var(--primary-green); margin-bottom: 2rem;">Herb Catalogue</h1>

    <?php if (empty($herbs)): ?>
        <div class="alert alert-info">
            <p>No herbs found in the database.</p>
        </div>
    <?php else: ?>
        <p style="color: var(--secondary-text); margin-bottom: 1rem;">
            Showing <?php echo count($herbs); ?> of <?php echo $totalHerbs; ?> herb<?php echo $totalHerbs !== 1 ? 's' : ''; ?>
            <?php if ($totalPages > 1): ?>
                (Page <?php echo $currentPage; ?> of <?php echo $totalPages; ?>) 
            <?php endif; ?>
        </p>
        
        <div class="card-grid">
            <?php foreach ($herbs as $herb): ?>
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

        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($currentPage > 1): ?>
                <a href="<?php echo SITE_URL; ?>/catalogue.php?page=<?php echo $currentPage - 1; ?>" 
                   class="btn btn-secondary">
                    ← Previous
                </a>
            <?php else: ?>
                <span class="btn btn-secondary" style="opacity: 0.5; cursor: not-allowed; pointer-events: none;">← Previous</span>
            <?php endif; ?>

            <div class="page-numbers">
                <?php
                // Show page numbers (show up to 5 pages around current)
                $startPage = max(1, $currentPage - 2);
                $endPage = min($totalPages, $currentPage + 2);
                
                if ($startPage > 1): ?>
                    <a href="<?php echo SITE_URL; ?>/catalogue.php?page=1" 
                       class="btn btn-secondary">1</a>
                    <?php if ($startPage > 2): ?>
                        <span>...</span>
                    <?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                    <?php if ($i == $currentPage): ?>
                        <span class="btn btn-primary" style="cursor: default;"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="<?php echo SITE_URL; ?>/catalogue.php?page=<?php echo $i; ?>" 
                           class="btn btn-secondary"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($endPage < $totalPages): ?>
                    <?php if ($endPage < $totalPages - 1): ?>
                        <span>...</span>
                    <?php endif; ?>
                    <a href="<?php echo SITE_URL; ?>/catalogue.php?page=<?php echo $totalPages; ?>" 
                       class="btn btn-secondary"><?php echo $totalPages; ?></a>
                <?php endif; ?>
            </div>

            <?php if ($currentPage < $totalPages): ?>
                <a href="<?php echo SITE_URL; ?>/catalogue.php?page=<?php echo $currentPage + 1; ?>" 
                   class="btn btn-secondary">
                    Next →
                </a>
            <?php else: ?>
                <span class="btn btn-secondary" style="opacity: 0.5; cursor: not-allowed; pointer-events: none;">Next →</span>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

