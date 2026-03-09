<?php
/**
 * Herb Comparison Tool
 * Allows users to compare multiple herbs side-by-side
 */

session_start();
require_once __DIR__ . '/includes/db-connect.php';
require_once __DIR__ . '/includes/header.php';

$pageTitle = 'Compare Herbs';

$db = getDB();

// Get selected herb IDs from query string
$herbIds = [];
if (isset($_GET['herbs'])) {
    $ids = explode(',', $_GET['herbs']);
    foreach ($ids as $id) {
        $id = (int)trim($id);
        if ($id > 0 && !in_array($id, $herbIds) && count($herbIds) < 3) {
            $herbIds[] = $id;
        }
    }
}

// Get all herbs for selection
$stmt = $db->query("SELECT id, name, scientific_name FROM herbs ORDER BY name");
$allHerbs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get selected herbs data
$selectedHerbs = [];
if (!empty($herbIds)) {
    $placeholders = implode(',', array_fill(0, count($herbIds), '?'));
    $stmt = $db->prepare("SELECT * FROM herbs WHERE id IN ($placeholders)");
    $stmt->execute($herbIds);
    $selectedHerbs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get conditions for each herb
    foreach ($selectedHerbs as &$herb) {
        $stmt = $db->prepare("
            SELECT hc.condition_name, hc_effect.effectiveness_note
            FROM herbs_conditions hc_effect
            INNER JOIN health_conditions hc ON hc_effect.condition_id = hc.id
            WHERE hc_effect.herb_id = ?
            ORDER BY hc.condition_name
        ");
        $stmt->execute([$herb['id']]);
        $herb['conditions'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get contraindications
        $stmt = $db->prepare("
            SELECT * FROM contraindications 
            WHERE herb_id = ? 
            ORDER BY 
                CASE severity 
                    WHEN 'high' THEN 1 
                    WHEN 'medium' THEN 2 
                    WHEN 'low' THEN 3 
                END
            LIMIT 5
        ");
        $stmt->execute([$herb['id']]);
        $herb['contraindications'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    unset($herb);
}
?>

<div class="container">
    <h1 style="color: var(--primary-green); margin-bottom: 1rem;">Compare Herbs</h1>
    <p style="color: var(--secondary-text); margin-bottom: 2rem;">
        Select up to 3 herbs to compare their properties, uses, and safety information side-by-side.
    </p>

    <!-- Herb Selection Form -->
    <div class="filters" style="margin-bottom: 2rem;">
        <form method="GET" action="<?php echo SITE_URL; ?>/herb-comparison.php" class="filter-group" id="comparisonForm">
            <div style="width: 100%;">
                <label for="herb1" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Select Herbs (up to 3):</label>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <div>
                        <select name="herb1" id="herb1" class="form-control comparison-select">
                            <option value="">-- Select Herb 1 --</option>
                            <?php foreach ($allHerbs as $herb): ?>
                                <option value="<?php echo $herb['id']; ?>" 
                                        <?php echo isset($herbIds[0]) && $herbIds[0] == $herb['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($herb['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <select name="herb2" id="herb2" class="form-control comparison-select">
                            <option value="">-- Select Herb 2 --</option>
                            <?php foreach ($allHerbs as $herb): ?>
                                <option value="<?php echo $herb['id']; ?>" 
                                        <?php echo isset($herbIds[1]) && $herbIds[1] == $herb['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($herb['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <select name="herb3" id="herb3" class="form-control comparison-select">
                            <option value="">-- Select Herb 3 (Optional) --</option>
                            <?php foreach ($allHerbs as $herb): ?>
                                <option value="<?php echo $herb['id']; ?>" 
                                        <?php echo isset($herbIds[2]) && $herbIds[2] == $herb['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($herb['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div style="margin-top: 1rem;">
                <button type="submit" class="btn btn-primary">Compare</button>
                <a href="<?php echo SITE_URL; ?>/herb-comparison.php" class="btn btn-secondary" style="margin-left: 0.5rem;">Clear</a>
            </div>
        </form>
    </div>

    <?php if (empty($selectedHerbs)): ?>
        <div class="alert alert-info">
            <h3>No herbs selected</h3>
            <p>Please select at least one herb from the dropdowns above to start comparing.</p>
            <p style="margin-top: 1rem;">
                <a href="<?php echo SITE_URL; ?>/catalogue.php" class="btn btn-primary">Browse Herbs</a>
            </p>
        </div>
    <?php else: ?>
        <!-- Comparison Table -->
        <div class="comparison-container" style="overflow-x: auto;">
            <table class="comparison-table">
                <thead>
                    <tr>
                        <th>Property</th>
                        <?php foreach ($selectedHerbs as $herb): ?>
                            <th>
                                <div style="text-align: center;">
                                    <strong><?php echo htmlspecialchars($herb['name']); ?></strong>
                                    <div style="font-size: 0.875rem; color: var(--secondary-text); font-weight: normal; margin-top: 0.25rem;">
                                        <?php echo htmlspecialchars($herb['scientific_name']); ?>
                                    </div>
                                </div>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <!-- Description -->
                    <tr>
                        <td><strong>Description</strong></td>
                        <?php foreach ($selectedHerbs as $herb): ?>
                            <td><?php echo htmlspecialchars($herb['description']); ?></td>
                        <?php endforeach; ?>
                    </tr>

                    <!-- Preparation Methods -->
                    <tr>
                        <td><strong>Preparation Methods</strong></td>
                        <?php foreach ($selectedHerbs as $herb): ?>
                            <td>
                                <?php if ($herb['preparation_methods']): ?>
                                    <?php echo nl2br(htmlspecialchars($herb['preparation_methods'])); ?>
                                <?php else: ?>
                                    <span style="color: var(--secondary-text);">Not specified</span>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>

                    <!-- Dosage Info -->
                    <tr>
                        <td><strong>Dosage Information</strong></td>
                        <?php foreach ($selectedHerbs as $herb): ?>
                            <td>
                                <?php if ($herb['dosage_info']): ?>
                                    <?php echo nl2br(htmlspecialchars($herb['dosage_info'])); ?>
                                <?php else: ?>
                                    <span style="color: var(--secondary-text);">Not specified</span>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>

                    <!-- Health Conditions -->
                    <tr>
                        <td><strong>Health Conditions</strong></td>
                        <?php foreach ($selectedHerbs as $herb): ?>
                            <td>
                                <?php if (!empty($herb['conditions'])): ?>
                                    <ul style="margin: 0; padding-left: 1.25rem; text-align: left;">
                                        <?php foreach ($herb['conditions'] as $condition): ?>
                                            <li>
                                                <strong><?php echo htmlspecialchars($condition['condition_name']); ?></strong>
                                                <?php if ($condition['effectiveness_note']): ?>
                                                    <div style="font-size: 0.875rem; color: var(--secondary-text); margin-top: 0.25rem;">
                                                        <?php echo htmlspecialchars($condition['effectiveness_note']); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <span style="color: var(--secondary-text);">None listed</span>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>

                    <!-- Contraindications -->
                    <tr>
                        <td><strong>Contraindications</strong></td>
                        <?php foreach ($selectedHerbs as $herb): ?>
                            <td>
                                <?php if (!empty($herb['contraindications'])): ?>
                                    <ul style="margin: 0; padding-left: 1.25rem; text-align: left;">
                                        <?php foreach ($herb['contraindications'] as $contra): ?>
                                            <li>
                                                <span style="color: <?php 
                                                    echo $contra['severity'] === 'high' ? '#dc3545' : 
                                                        ($contra['severity'] === 'medium' ? '#ffc107' : '#28a745'); 
                                                ?>; font-weight: 600;">
                                                    <?php echo ucfirst($contra['severity']); ?> Risk:
                                                </span>
                                                <?php echo htmlspecialchars($contra['description']); ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <span style="color: var(--secondary-text);">None listed</span>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>

                    <!-- Safety Warnings -->
                    <tr>
                        <td><strong>Safety Warnings</strong></td>
                        <?php foreach ($selectedHerbs as $herb): ?>
                            <td>
                                <?php if ($herb['safety_warnings']): ?>
                                    <?php echo nl2br(htmlspecialchars($herb['safety_warnings'])); ?>
                                <?php else: ?>
                                    <span style="color: var(--secondary-text);">None specified</span>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>

                    <!-- Actions -->
                    <tr>
                        <td><strong>Actions</strong></td>
                        <?php foreach ($selectedHerbs as $herb): ?>
                            <td style="text-align: center;">
                                <a href="<?php echo SITE_URL; ?>/herb-detail.php?id=<?php echo $herb['id']; ?>" 
                                   class="btn btn-primary" style="display: inline-block;">
                                    View Full Details
                                </a>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                </tbody>
            </table>
        </div>

        <div style="margin-top: 2rem; text-align: center;">
            <a href="<?php echo SITE_URL; ?>/catalogue.php" class="btn btn-secondary">Back to Catalogue</a>
        </div>
    <?php endif; ?>
</div>

<script>
// Auto-submit form when herb selections change
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('comparisonForm');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Build herbs parameter from selected values
        const selectedIds = [];
        const selects = document.querySelectorAll('.comparison-select');
        selects.forEach(s => {
            if (s.value) selectedIds.push(s.value);
        });
        
        if (selectedIds.length > 0) {
            const url = new URL(window.location.href);
            url.searchParams.set('herbs', selectedIds.join(','));
            window.location.href = url.toString();
        } else {
            window.location.href = '<?php echo SITE_URL; ?>/herb-comparison.php';
        }
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

