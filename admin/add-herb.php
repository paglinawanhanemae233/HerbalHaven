<?php
/**
 * Add New Herb
 * Form to add new herbs to the database
 */

session_start();
require_once __DIR__ . '/../includes/db-connect.php';
require_once __DIR__ . '/../includes/header.php';

$pageTitle = 'Add New Herb';

$db = getDB();
$success = false;
$error = null;

// Get all conditions for checkboxes
$stmt = $db->query("SELECT * FROM health_conditions ORDER BY condition_name");
$conditions = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db->beginTransaction();

        // Insert herb
        $stmt = $db->prepare("
            INSERT INTO herbs (name, scientific_name, description, image_url, preparation_methods, dosage_info, safety_warnings)
            VALUES (:name, :scientific_name, :description, :image_url, :preparation_methods, :dosage_info, :safety_warnings)
        ");
        
        $stmt->execute([
            ':name' => trim($_POST['name']),
            ':scientific_name' => trim($_POST['scientific_name'] ?? ''),
            ':description' => trim($_POST['description'] ?? ''),
            ':image_url' => trim($_POST['image_url'] ?? ''),
            ':preparation_methods' => trim($_POST['preparation_methods'] ?? ''),
            ':dosage_info' => trim($_POST['dosage_info'] ?? ''),
            ':safety_warnings' => trim($_POST['safety_warnings'] ?? '')
        ]);
        
        $herbId = $db->lastInsertId();

        // Link conditions
        if (isset($_POST['conditions']) && is_array($_POST['conditions'])) {
            $stmt = $db->prepare("
                INSERT INTO herbs_conditions (herb_id, condition_id, effectiveness_note)
                VALUES (:herb_id, :condition_id, :note)
            ");
            
            foreach ($_POST['conditions'] as $conditionId) {
                $note = $_POST['condition_note_' . $conditionId] ?? '';
                $stmt->execute([
                    ':herb_id' => $herbId,
                    ':condition_id' => (int)$conditionId,
                    ':note' => $note
                ]);
            }
        }

        // Add contraindications
        if (isset($_POST['contraindications']) && is_array($_POST['contraindications'])) {
            $stmt = $db->prepare("
                INSERT INTO contraindications (herb_id, warning_text, severity, category)
                VALUES (:herb_id, :warning_text, :severity, :category)
            ");
            
            foreach ($_POST['contraindications'] as $index => $warning) {
                if (!empty(trim($warning))) {
                    $stmt->execute([
                        ':herb_id' => $herbId,
                        ':warning_text' => trim($warning),
                        ':severity' => $_POST['contra_severity'][$index] ?? 'medium',
                        ':category' => $_POST['contra_category'][$index] ?? ''
                    ]);
                }
            }
        }

        $db->commit();
        $success = true;
    } catch (Exception $e) {
        $db->rollBack();
        $error = 'Error adding herb: ' . $e->getMessage();
    }
}
?>

<div class="container">
    <div class="admin-header">
        <h1>Add New Herb</h1>
        <div class="admin-nav">
            <a href="<?php echo SITE_URL; ?>/admin/index.php">Dashboard</a>
            <a href="<?php echo SITE_URL; ?>/admin/manage-herbs.php">Manage Herbs</a>
            <a href="<?php echo SITE_URL; ?>/index.php">Back to Site</a>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success">
            <strong>Success!</strong> Herb added successfully. 
            <a href="<?php echo SITE_URL; ?>/admin/manage-herbs.php">View all herbs</a>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error">
            <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo SITE_URL; ?>/admin/add-herb.php" style="background: var(--white); padding: 2rem; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h2 style="color: var(--primary-green); margin-bottom: 1.5rem;">Basic Information</h2>
        
        <div class="form-group">
            <label for="name">Herb Name *</label>
            <input type="text" id="name" name="name" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="scientific_name">Scientific Name</label>
            <input type="text" id="scientific_name" name="scientific_name" class="form-control">
        </div>

        <div class="form-group">
            <label for="description">Description *</label>
            <textarea id="description" name="description" class="form-control" rows="5" required></textarea>
        </div>

        <div class="form-group">
            <label for="image_url">Image URL</label>
            <input type="url" id="image_url" name="image_url" class="form-control" placeholder="/images/herbs/herb-name.jpg">
        </div>

        <h2 style="color: var(--primary-green); margin-top: 2rem; margin-bottom: 1.5rem;">Usage Information</h2>

        <div class="form-group">
            <label for="preparation_methods">Preparation Methods *</label>
            <textarea id="preparation_methods" name="preparation_methods" class="form-control" rows="4" required></textarea>
            <small style="color: var(--secondary-text);">Describe how to prepare this herb (tea, tincture, capsules, etc.)</small>
        </div>

        <div class="form-group">
            <label for="dosage_info">Dosage Information *</label>
            <textarea id="dosage_info" name="dosage_info" class="form-control" rows="3" required></textarea>
            <small style="color: var(--secondary-text);">Provide recommended dosages and usage guidelines</small>
        </div>

        <div class="form-group">
            <label for="safety_warnings">Safety Warnings</label>
            <textarea id="safety_warnings" name="safety_warnings" class="form-control" rows="3"></textarea>
            <small style="color: var(--secondary-text);">General safety information and warnings</small>
        </div>

        <h2 style="color: var(--primary-green); margin-top: 2rem; margin-bottom: 1.5rem;">Related Conditions</h2>
        <div style="max-height: 300px; overflow-y: auto; border: 1px solid var(--accent-green); padding: 1rem; border-radius: 5px;">
            <?php foreach ($conditions as $condition): ?>
                <div style="margin-bottom: 1rem;">
                    <label style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" name="conditions[]" value="<?php echo $condition['id']; ?>">
                        <strong><?php echo htmlspecialchars($condition['condition_name']); ?></strong>
                    </label>
                    <input type="text" name="condition_note_<?php echo $condition['id']; ?>" 
                           placeholder="Effectiveness note (optional)" 
                           class="form-control" 
                           style="margin-top: 0.5rem; margin-left: 1.5rem;">
                </div>
            <?php endforeach; ?>
        </div>

        <h2 style="color: var(--primary-green); margin-top: 2rem; margin-bottom: 1.5rem;">Contraindications</h2>
        <div id="contraindications-container">
            <div class="contraindication-item" style="margin-bottom: 1rem;">
                <div class="form-group">
                    <label>Warning Text</label>
                    <input type="text" name="contraindications[]" class="form-control" placeholder="e.g., May interact with blood-thinning medications">
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>Severity</label>
                        <select name="contra_severity[]" class="form-control">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Category</label>
                        <input type="text" name="contra_category[]" class="form-control" placeholder="e.g., medication_interaction">
                    </div>
                </div>
            </div>
        </div>
        <button type="button" id="add-contraindication" class="btn btn-secondary">Add Another Contraindication</button>

        <div style="margin-top: 2rem;">
            <button type="submit" class="btn btn-primary">Add Herb</button>
            <a href="<?php echo SITE_URL; ?>/admin/manage-herbs.php" class="btn btn-secondary" style="margin-left: 0.5rem;">Cancel</a>
        </div>
    </form>
</div>

<script>
document.getElementById('add-contraindication').addEventListener('click', function() {
    const container = document.getElementById('contraindications-container');
    const newItem = document.createElement('div');
    newItem.className = 'contraindication-item';
    newItem.style.marginBottom = '1rem';
    newItem.innerHTML = `
        <div class="form-group">
            <label>Warning Text</label>
            <input type="text" name="contraindications[]" class="form-control" placeholder="e.g., May interact with blood-thinning medications">
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label>Severity</label>
                <select name="contra_severity[]" class="form-control">
                    <option value="low">Low</option>
                    <option value="medium" selected>Medium</option>
                    <option value="high">High</option>
                </select>
            </div>
            <div class="form-group">
                <label>Category</label>
                <input type="text" name="contra_category[]" class="form-control" placeholder="e.g., medication_interaction">
            </div>
        </div>
        <button type="button" class="btn btn-danger" onclick="this.parentElement.remove()" style="margin-top: 0.5rem;">Remove</button>
    `;
    container.appendChild(newItem);
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

