<?php
/**
 * Admin Dashboard
 * Overview of herbs, conditions, and chat statistics
 */

session_start();
require_once __DIR__ . '/../includes/db-connect.php';
require_once __DIR__ . '/../includes/header.php';

$pageTitle = 'Admin Dashboard';

$db = getDB();

// Get statistics
$stmt = $db->query("SELECT COUNT(*) as count FROM herbs");
$herbCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

$stmt = $db->query("SELECT COUNT(*) as count FROM health_conditions");
$conditionCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

$stmt = $db->query("SELECT COUNT(*) as count FROM chat_history");
$chatCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

$stmt = $db->query("SELECT COUNT(DISTINCT session_id) as count FROM chat_history");
$sessionCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Get recent herbs
$stmt = $db->query("SELECT * FROM herbs ORDER BY created_at DESC LIMIT 5");
$recentHerbs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent chat messages
$stmt = $db->query("SELECT * FROM chat_history ORDER BY timestamp DESC LIMIT 10");
$recentChats = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <div class="admin-header">
        <h1>Admin Dashboard</h1>
        <p>Manage herbs, conditions, and view system statistics</p>
        <div class="admin-nav">
            <a href="<?php echo SITE_URL; ?>/admin/add-herb.php">Add New Herb</a>
            <a href="<?php echo SITE_URL; ?>/admin/manage-herbs.php">Manage Herbs</a>
            <a href="<?php echo SITE_URL; ?>/index.php">Back to Site</a>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <div style="background: var(--white); padding: 1.5rem; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h3 style="color: var(--primary-green); margin-bottom: 0.5rem;">Total Herbs</h3>
            <p style="font-size: 2rem; font-weight: bold; color: var(--secondary-green);"><?php echo $herbCount; ?></p>
        </div>
        <div style="background: var(--white); padding: 1.5rem; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h3 style="color: var(--primary-green); margin-bottom: 0.5rem;">Health Conditions</h3>
            <p style="font-size: 2rem; font-weight: bold; color: var(--secondary-green);"><?php echo $conditionCount; ?></p>
        </div>
        <div style="background: var(--white); padding: 1.5rem; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h3 style="color: var(--primary-green); margin-bottom: 0.5rem;">Chat Messages</h3>
            <p style="font-size: 2rem; font-weight: bold; color: var(--secondary-green);"><?php echo $chatCount; ?></p>
        </div>
        <div style="background: var(--white); padding: 1.5rem; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h3 style="color: var(--primary-green); margin-bottom: 0.5rem;">Chat Sessions</h3>
            <p style="font-size: 2rem; font-weight: bold; color: var(--secondary-green);"><?php echo $sessionCount; ?></p>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 2rem;">
        <div>
            <h2 style="color: var(--primary-green); margin-bottom: 1rem;">Recent Herbs</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Scientific Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentHerbs as $herb): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($herb['name']); ?></td>
                            <td><?php echo htmlspecialchars($herb['scientific_name']); ?></td>
                            <td>
                                <a href="<?php echo SITE_URL; ?>/herb-detail.php?id=<?php echo $herb['id']; ?>" class="btn btn-secondary" style="padding: 0.25rem 0.75rem; font-size: 0.9rem;">View</a>
                                <a href="<?php echo SITE_URL; ?>/admin/manage-herbs.php?edit=<?php echo $herb['id']; ?>" class="btn btn-primary" style="padding: 0.25rem 0.75rem; font-size: 0.9rem; margin-left: 0.25rem;">Edit</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div>
            <h2 style="color: var(--primary-green); margin-bottom: 1rem;">Recent Chat Activity</h2>
            <div style="background: var(--white); border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); max-height: 400px; overflow-y: auto;">
                <?php foreach ($recentChats as $chat): ?>
                    <div style="padding: 1rem; border-bottom: 1px solid var(--accent-green);">
                        <p style="margin-bottom: 0.5rem;"><strong>User:</strong> <?php echo htmlspecialchars(substr($chat['user_message'], 0, 100)) . '...'; ?></p>
                        <p style="margin-bottom: 0.5rem; color: var(--secondary-text);"><strong>AI:</strong> <?php echo htmlspecialchars(substr($chat['ai_response'], 0, 100)) . '...'; ?></p>
                        <p style="font-size: 0.8rem; color: var(--secondary-text);"><?php echo date('Y-m-d H:i:s', strtotime($chat['timestamp'])); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

