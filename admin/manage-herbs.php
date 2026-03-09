<?php
/**
 * Manage Herbs
 * View, edit, and delete herbs
 */

session_start();
require_once __DIR__ . '/../includes/db-connect.php';
require_once __DIR__ . '/../includes/header.php';

$pageTitle = 'Manage Herbs';

$db = getDB();
$message = null;
$messageType = 'info';

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    try {
        $stmt = $db->prepare("DELETE FROM herbs WHERE id = :id");
        $stmt->execute([':id' => (int)$_GET['delete']]);
        $message = 'Herb deleted successfully.';
        $messageType = 'success';
    } catch (Exception $e) {
        $message = 'Error deleting herb: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Get all herbs
$stmt = $db->query("SELECT * FROM herbs ORDER BY name ASC");
$herbs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <div class="admin-header">
        <h1>Manage Herbs</h1>
        <div class="admin-nav">
            <a href="<?php echo SITE_URL; ?>/admin/index.php">Dashboard</a>
            <a href="<?php echo SITE_URL; ?>/admin/add-herb.php">Add New Herb</a>
            <a href="<?php echo SITE_URL; ?>/index.php">Back to Site</a>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div style="margin-bottom: 1rem;">
        <p style="color: var(--secondary-text);">
            Total: <?php echo count($herbs); ?> herb<?php echo count($herbs) !== 1 ? 's' : ''; ?>
        </p>
    </div>

    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Scientific Name</th>
                <th>Description</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($herbs)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 2rem; color: var(--secondary-text);">
                        No herbs found. <a href="<?php echo SITE_URL; ?>/admin/add-herb.php">Add your first herb</a>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($herbs as $herb): ?>
                    <tr>
                        <td><?php echo $herb['id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($herb['name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($herb['scientific_name']); ?></td>
                        <td><?php echo htmlspecialchars(substr($herb['description'], 0, 100)) . '...'; ?></td>
                        <td><?php echo date('Y-m-d', strtotime($herb['created_at'])); ?></td>
                        <td>
                            <a href="<?php echo SITE_URL; ?>/herb-detail.php?id=<?php echo $herb['id']; ?>" 
                               class="btn btn-secondary" 
                               style="padding: 0.25rem 0.75rem; font-size: 0.9rem; text-decoration: none;"
                               target="_blank">View</a>
                            <a href="<?php echo SITE_URL; ?>/admin/add-herb.php?edit=<?php echo $herb['id']; ?>" 
                               class="btn btn-primary" 
                               style="padding: 0.25rem 0.75rem; font-size: 0.9rem; text-decoration: none; margin-left: 0.25rem;">Edit</a>
                            <a href="<?php echo SITE_URL; ?>/admin/manage-herbs.php?delete=<?php echo $herb['id']; ?>" 
                               class="btn btn-danger" 
                               style="padding: 0.25rem 0.75rem; font-size: 0.9rem; text-decoration: none; margin-left: 0.25rem;"
                               onclick="return confirm('Are you sure you want to delete this herb? This action cannot be undone.');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

