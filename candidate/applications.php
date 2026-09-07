<?php
declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = 'My Applications - Career Grow Infotech';

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

$userId = (int)$_SESSION['user_id'];
$statusFilter = isset($_GET['status']) ? trim((string)$_GET['status']) : '';

$conn = getDbConnection();

// Build query
$query = 'SELECT a.id, a.job_id, a.status, a.applied_at, j.title, j.location, j.job_type FROM applications a JOIN jobs j ON a.job_id = j.id WHERE a.user_id = ?';
$params = [$userId];
$types = 'i';

if ($statusFilter !== '') {
    $query .= ' AND a.status = ?';
    $params[] = $statusFilter;
    $types .= 's';
}

$query .= ' ORDER BY a.applied_at DESC';

$stmt = $conn->prepare($query);
$applications = [];
if ($stmt) {
    $stmt->bind_param($types, ...$params);
    $stmt->execute();

    $result = $stmt->get_result();
    if ($result !== false) {
        while ($row = $result->fetch_assoc()) {
            $applications[] = $row;
        }
    } else {
        // Fallback for environments without mysqlnd/get_result(): use bind_result
        $stmt->store_result();
        $stmt->bind_result($col_id, $col_job_id, $col_status, $col_applied_at, $col_title, $col_location, $col_job_type);
        while ($stmt->fetch()) {
            $applications[] = [
                'id' => $col_id,
                'job_id' => $col_job_id,
                'status' => $col_status,
                'applied_at' => $col_applied_at,
                'title' => $col_title,
                'location' => $col_location,
                'job_type' => $col_job_type,
            ];
        }
    }
    $stmt->close();
} else {
    error_log('applications.php prepare failed: ' . $conn->error);
}

// Get status counts
$statusCounts = [];
$statusStmt = $conn->prepare('SELECT a.status, COUNT(*) as count FROM applications a WHERE a.user_id = ? GROUP BY a.status');
if ($statusStmt) {
    $statusStmt->bind_param('i', $userId);
    $statusStmt->execute();

    $statusResult = $statusStmt->get_result();
    if ($statusResult !== false) {
        while ($row = $statusResult->fetch_assoc()) {
            $statusCounts[$row['status']] = $row['count'];
        }
    } else {
        $statusStmt->store_result();
        $statusStmt->bind_result($s_status, $s_count);
        while ($statusStmt->fetch()) {
            $statusCounts[$s_status] = $s_count;
        }
    }
    $statusStmt->close();
} else {
    error_log('applications.php status prepare failed: ' . $conn->error);
}
$conn->close();
?>

<style>
.applications-container { max-width: 900px; margin: 2rem auto; }
.applications-section { background: var(--cg-white); border: 1px solid var(--cg-border); border-radius: 1rem; padding: 2rem; margin-bottom: 1.5rem; }
.status-filter { display: flex; gap: 0.5rem; margin-bottom: 1.5rem; flex-wrap: wrap; }
.status-filter .filter-btn { padding: 0.5rem 1rem; border-radius: 0.25rem; border: 1px solid var(--cg-border); background: white; cursor: pointer; font-size: 0.9rem; }
.status-filter .filter-btn.active { background: var(--cg-primary); color: white; border-color: var(--cg-primary); }
.app-card { background: var(--cg-white); border: 1px solid var(--cg-border); border-radius: 0.5rem; padding: 1.5rem; margin-bottom: 1rem; }
.app-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
.app-header { display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem; }
.app-title { font-weight: 700; font-size: 1.1rem; }
.app-meta { display: flex; gap: 1rem; color: var(--cg-muted); font-size: 0.9rem; margin: 0.5rem 0; }
.status-badge { padding: 0.25rem 0.75rem; border-radius: 0.25rem; font-size: 0.85rem; font-weight: 600; }
.status-new { background-color: #e3f2fd; color: #1565c0; }
.status-new-applied { background-color: #e3f2fd; color: #1565c0; }
.status-reviewed { background-color: #fff3e0; color: #e65100; }
.status-accepted { background-color: #e8f5e9; color: #2e7d32; }
.status-rejected { background-color: #ffebee; color: #c62828; }
</style>

<main class="applications-container py-5">
    <div class="applications-section">
        <h2 style="margin-bottom: 1.5rem; font-weight: 700;">My Applications</h2>

        <!-- Status Filter -->
        <div class="status-filter">
            <a href="applications.php" class="filter-btn <?php echo ($statusFilter === '' ? 'active' : ''); ?>">All (<?php echo count($applications); ?>)</a>
            <a href="applications.php?status=New Applied" class="filter-btn <?php echo ($statusFilter === 'New Applied' ? 'active' : ''); ?>">New Applied (<?php echo $statusCounts['New Applied'] ?? 0; ?>)</a>
            <a href="applications.php?status=Reviewed" class="filter-btn <?php echo ($statusFilter === 'Reviewed' ? 'active' : ''); ?>">Reviewed (<?php echo $statusCounts['Reviewed'] ?? 0; ?>)</a>
            <a href="applications.php?status=Accepted" class="filter-btn <?php echo ($statusFilter === 'Accepted' ? 'active' : ''); ?>">Accepted (<?php echo $statusCounts['Accepted'] ?? 0; ?>)</a>
            <a href="applications.php?status=Rejected" class="filter-btn <?php echo ($statusFilter === 'Rejected' ? 'active' : ''); ?>">Rejected (<?php echo $statusCounts['Rejected'] ?? 0; ?>)</a>
        </div>

        <?php if (!empty($applications)): ?>
            <div>
                <?php foreach ($applications as $app): ?>
                    <div class="app-card">
                        <div class="app-header">
                            <div>
                                <h3 class="app-title" style="margin: 0;"><a href="../job-details.php?id=<?php echo $app['job_id']; ?>" style="text-decoration: none; color: inherit;"><?php echo htmlspecialchars($app['title'], ENT_QUOTES, 'UTF-8'); ?></a></h3>
                                <div class="app-meta">
                                    <span><?php echo htmlspecialchars($app['location'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
                                    <span><?php echo htmlspecialchars($app['job_type'], ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                            </div>
                            <span class="status-badge status-<?php echo str_replace(' ', '-', strtolower($app['status'])); ?>"><?php echo htmlspecialchars($app['status'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <div class="app-meta">
                            <span>Applied: <?php echo date('d M Y', strtotime($app['applied_at'])); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 3rem 0;">
                <p class="text-muted">No applications found</p>
                <a href="../jobs.php" class="btn btn-primary">Browse Jobs</a>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
