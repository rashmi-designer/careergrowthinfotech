<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/admin-auth.php';
require_admin();

if (empty($_SESSION['job_status_token'])) {
    $_SESSION['job_status_token'] = bin2hex(random_bytes(16));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (($_POST['action'] ?? '') === 'toggle_job_status')) {
    $token = $_POST['job_status_token'] ?? '';
    if (!hash_equals($_SESSION['job_status_token'] ?? '', (string)$token)) {
        $_SESSION['job_status_message'] = 'Invalid form submission.';
        header('Location: jobs.php');
        exit;
    }

    $jobId = isset($_POST['job_id']) ? (int)$_POST['job_id'] : 0;
    $newStatus = isset($_POST['new_status']) ? trim((string)$_POST['new_status']) : '';
    $allowedStatuses = ['active', 'inactive'];

    if ($jobId <= 0 || !in_array($newStatus, $allowedStatuses, true)) {
        $_SESSION['job_status_message'] = 'Invalid job status update.';
        header('Location: jobs.php');
        exit;
    }

    $conn = getDbConnection();
    $stmt = $conn->prepare('UPDATE jobs SET status = ? WHERE id = ? LIMIT 1');
    if ($stmt) {
        $stmt->bind_param('si', $newStatus, $jobId);
        $stmt->execute();
        $stmt->close();
        $_SESSION['job_status_message'] = 'Job status updated successfully.';
    } else {
        error_log('jobs.php status update prepare failed: ' . $conn->error);
        $_SESSION['job_status_message'] = 'Unable to update job status.';
    }
    $conn->close();

    header('Location: jobs.php');
    exit;
}

$pageTitle = 'Jobs Management - Admin';
require_once __DIR__ . '/../includes/header.php';

$conn = getDbConnection();

$jobTypes = [];
$tstmt = $conn->prepare("SELECT DISTINCT job_type FROM jobs WHERE job_type IS NOT NULL AND job_type <> '' ORDER BY job_type ASC");
if ($tstmt) {
    $tstmt->execute();
    $tres = $tstmt->get_result();
    while ($row = $tres->fetch_row()) {
        $jobTypes[] = (string)$row[0];
    }
    $tstmt->close();
}

function safe_count(mysqli $conn, string $sql): int {
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return 0;
    }
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_row();
    $stmt->close();
    return (int)($row[0] ?? 0);
}

$kpis = [
    'total' => safe_count($conn, 'SELECT COUNT(*) FROM jobs'),
    'active' => safe_count($conn, "SELECT COUNT(*) FROM jobs WHERE status = 'active'"),
    'inactive' => safe_count($conn, "SELECT COUNT(*) FROM jobs WHERE status <> 'active'"),
    'with_applications' => safe_count($conn, 'SELECT COUNT(DISTINCT job_id) FROM applications'),
];

$q = trim((string)($_GET['q'] ?? ''));
$statusFilter = trim((string)($_GET['status'] ?? ''));
$jobTypeFilter = trim((string)($_GET['job_type'] ?? ''));
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

$where = [];
$params = [];
$types = '';

if ($q !== '') {
    $where[] = '(title LIKE ? OR location LIKE ?)';
    $like = '%' . $q . '%';
    $params[] = $like;
    $params[] = $like;
    $types .= 'ss';
}

if ($statusFilter !== '') {
    $where[] = 'status = ?';
    $params[] = $statusFilter;
    $types .= 's';
}

if ($jobTypeFilter !== '') {
    $where[] = 'job_type = ?';
    $params[] = $jobTypeFilter;
    $types .= 's';
}

$whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$countSql = "SELECT COUNT(*) FROM jobs $whereSql";
$countStmt = $conn->prepare($countSql);
$totalJobs = 0;
if ($countStmt) {
    if (!empty($params)) {
        $countStmt->bind_param($types, ...$params);
    }
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $countRow = $countResult->fetch_row();
    $totalJobs = (int)($countRow[0] ?? 0);
    $countStmt->close();
}

$totalPages = max(1, (int)ceil($totalJobs / $perPage));

$sql = "SELECT j.id, j.title, j.company, j.location, j.job_type, j.experience_level, j.openings, j.status, j.created_at, COUNT(a.id) AS app_count
        FROM jobs j
        LEFT JOIN applications a ON a.job_id = j.id
        $whereSql
        GROUP BY j.id
        ORDER BY j.created_at DESC
        LIMIT ? OFFSET ?";

$jobs = [];
$stmt = $conn->prepare($sql);
if ($stmt) {
    if (!empty($params)) {
        $bindTypes = $types . 'ii';
        $bindParams = array_merge($params, [$perPage, $offset]);
        $stmt->bind_param($bindTypes, ...$bindParams);
    } else {
        $stmt->bind_param('ii', $perPage, $offset);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $jobs[] = $row;
    }
    $stmt->close();
}

$conn->close();
?>

<style>
.admin-root {
    min-height: 100vh;
    display: flex;
    align-items: stretch;
    gap: 1.5rem;
    padding: 1.5rem;
    background: linear-gradient(180deg, rgba(13,110,253,0.02), rgba(255,255,255,0));
}

.sidebar {
    width: 260px;
    background: var(--cg-white);
    border: 1px solid var(--cg-border);
    border-radius: 1rem;
    padding: 1rem;
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.05);
    position: sticky;
    top: 1rem;
    height: fit-content;
}

.brand-wrap {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    padding: 0.3rem 0 1rem;
    border-bottom: 1px solid rgba(15, 23, 42, 0.04);
}

.sidebar-nav {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
    margin-top: 1rem;
}

.nav-link-admin {
    display: flex;
    align-items: center;
    gap: 0.7rem;
    padding: 0.7rem 0.8rem;
    border-radius: 0.65rem;
    font-weight: 600;
    color: var(--cg-accent);
    text-decoration: none;
}

.nav-link-admin:hover,
.nav-link-admin:focus {
    color: var(--cg-primary);
    background: rgba(13,110,253,0.04);
    text-decoration: none;
}

.nav-link-admin.active {
    background: rgba(13,110,253,0.07);
    color: var(--cg-primary);
}

.sidebar-footer {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid rgba(15, 23, 42, 0.04);
}

.main-panel {
    flex: 1 1 auto;
    min-width: 0;
}

.topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
}

.topbar-title h1 {
    font-size: clamp(1.5rem, 2vw, 2rem);
    margin: 0;
    font-weight: 700;
    letter-spacing: -0.02em;
}

.topbar-subtitle {
    font-size: 0.9rem;
    color: var(--cg-muted);
}

.user-pill {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    background: var(--cg-white);
    border: 1px solid var(--cg-border);
    border-radius: 999px;
    padding: 0.5rem 0.8rem;
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.04);
}

.user-pill .avatar {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: rgba(13,110,253,0.1);
    color: var(--cg-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

.page-shell {
    background: transparent;
}

.card-panel {
    background: var(--cg-white);
    border: 1px solid var(--cg-border);
    border-radius: 1rem;
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.04);
}

.header-panel {
    padding: 1.2rem 1.4rem;
    margin-bottom: 1rem;
}

.page-kicker {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--cg-primary);
    font-size: 0.78rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    font-weight: 700;
    margin-bottom: 0.4rem;
}

.header-panel h2 {
    margin: 0;
    font-size: clamp(1.6rem, 2.4vw, 2.2rem);
    letter-spacing: -0.03em;
}

.header-panel p {
    color: var(--cg-muted);
    margin-top: 0.35rem;
}

.kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 0.9rem;
    margin-bottom: 1rem;
}

.kpi-card {
    padding: 1rem 1rem 0.9rem;
    border-radius: 0.9rem;
    border: 1px solid var(--cg-border);
    background: var(--cg-white);
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
}

.kpi-card .meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
}

.kpi-card .label {
    font-size: 0.8rem;
    color: var(--cg-muted);
    margin-bottom: 0.4rem;
}

.kpi-card .value {
    font-size: 1.7rem;
    font-weight: 800;
    line-height: 1.2;
    margin: 0;
}

.kpi-card .icon {
    width: 42px;
    height: 42px;
    border-radius: 0.75rem;
    background: #eaf1ff;
    color: #2763d7;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

/* Match the dashboard KPI colour system while keeping each metric distinct. */
.kpi-card.kpi-green .icon { background: #e7f8f0; color: #08976b; }
.kpi-card.kpi-purple .icon { background: #f2ebff; color: #8856d8; }
.kpi-card.kpi-orange .icon { background: #fff3df; color: #d88020; }

.toolbar-panel {
    padding: 1rem 1rem 0.9rem;
    margin-bottom: 1rem;
}

.toolbar-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.filter-actions {
    display: inline-flex;
    align-items: center;
    gap: 0.55rem;
    flex: 0 0 auto;
}

.filter-actions .btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.45rem;
    min-height: 44px;
    padding: 0.65rem 1rem;
    font-weight: 700;
}

.filter-actions .filter-apply {
    min-width: 108px;
    color: #fff;
    border-color: var(--cg-primary);
    background: var(--cg-primary);
    box-shadow: 0 8px 16px rgba(13, 110, 253, 0.16);
}

.filter-actions .filter-apply:hover,
.filter-actions .filter-apply:focus {
    color: #fff;
    border-color: #095fd5;
    background: #095fd5;
}

.filter-actions .filter-reset {
    min-width: 90px;
    color: #526b8e;
    border: 1px solid #d6e1ef;
    background: #fff;
}

.filter-actions .filter-reset:hover,
.filter-actions .filter-reset:focus {
    color: var(--cg-primary);
    border-color: #b9d2f7;
    background: #f3f8ff;
}

.search-wrap {
    position: relative;
    flex: 1 1 330px;
    min-width: 0;
}

.search-wrap .bi {
    position: absolute;
    top: 50%;
    left: 0.8rem;
    transform: translateY(-50%);
    color: var(--cg-muted);
}

.search-wrap input {
    width: 100%;
    padding-left: 2.4rem;
    min-height: 44px;
}

.toolbar-row .form-select {
    min-height: 44px;
}

.result-count {
    margin-left: auto;
    color: var(--cg-muted);
    font-size: 0.85rem;
    white-space: nowrap;
}

.table-panel {
    padding: 0.25rem 0;
    border-color: #d9e6f5;
    overflow: hidden;
}

.table-wrap {
    width: 100%;
    overflow: auto;
}

.table {
    margin-bottom: 0;
}

.table thead th {
    background: linear-gradient(180deg, #f3f8ff 0%, #eaf3ff 100%);
    color: #42648d;
    font-size: 0.72rem;
    font-weight: 800;
    letter-spacing: 0.075em;
    text-transform: uppercase;
    padding: 1rem;
    border-top: 1px solid #dbe9fa;
    border-bottom: 1px solid #cddff5;
}

.table tbody td {
    padding: 1rem;
    vertical-align: middle;
    color: #273f60;
    font-size: 0.95rem;
    border-bottom: 1px solid #edf1f6;
}

.table tbody tr:hover {
    background: #f9fbff;
}

.job-cell {
    min-width: 0;
}

.job-title {
    font-weight: 700;
    color: var(--cg-accent);
    line-height: 1.3;
    font-size: 1rem;
}

.job-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
    align-items: center;
    color: var(--cg-muted);
    font-size: 0.83rem;
    margin-top: 0.2rem;
}

.table tbody td:not(:first-child) {
    font-weight: 500;
}

.job-meta .dot {
    color: rgba(108,117,125,0.5);
}

.badge-status {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.42rem 0.7rem;
    border-radius: 999px;
    font-size: 0.76rem;
    font-weight: 700;
    letter-spacing: 0.02em;
}

.badge-status.active {
    background: rgba(13,110,253,0.09);
    color: var(--cg-primary);
    border: 1px solid rgba(13,110,253,0.12);
}

.badge-status.inactive {
    background: rgba(108,117,125,0.08);
    color: #5c636a;
    border: 1px solid rgba(108,117,125,0.12);
}

.status-label {
    text-transform: capitalize;
}

.table-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.35rem;
    white-space: nowrap;
}

.table-actions .btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2.25rem;
    height: 2.25rem;
    padding: 0;
    border-radius: 0.7rem;
    font-size: 1rem;
}

@media (min-width: 1200px) {
    .table-wrap .table {
        width: 100%;
        table-layout: fixed;
    }

    .table-wrap .table th,
    .table-wrap .table td {
        padding-right: 0.7rem;
        padding-left: 0.7rem;
    }

    .table-wrap .table th:nth-child(1),
    .table-wrap .table td:nth-child(1) { width: 27%; }
    .table-wrap .table th:nth-child(2),
    .table-wrap .table td:nth-child(2) { width: 14%; }
    .table-wrap .table th:nth-child(3),
    .table-wrap .table td:nth-child(3) { width: 10%; }
    .table-wrap .table th:nth-child(4),
    .table-wrap .table td:nth-child(4) { width: 9%; }
    .table-wrap .table th:nth-child(5),
    .table-wrap .table td:nth-child(5) { width: 9%; }
    .table-wrap .table th:nth-child(6),
    .table-wrap .table td:nth-child(6) { width: 12%; white-space: nowrap; }
    .table-wrap .table th:nth-child(7),
    .table-wrap .table td:nth-child(7) { width: 8%; }
    .table-wrap .table th:nth-child(8),
    .table-wrap .table td:nth-child(8) { width: 11%; }

    .table-wrap .table td:nth-child(2),
    .table-wrap .table td:nth-child(3),
    .table-wrap .table td:nth-child(4) {
        overflow-wrap: anywhere;
    }
}

.table-actions .action-toggle {
    color: #d97706;
    border-color: #f4d6a7;
    background: #fffaf1;
}

.table-actions .action-toggle:hover,
.table-actions .action-toggle:focus {
    color: #fff;
    border-color: #d97706;
    background: #d97706;
}

.table-actions .action-view {
    color: #2563eb;
    border-color: #c9dcff;
    background: #f6f9ff;
}

.table-actions .action-view:hover,
.table-actions .action-view:focus {
    color: #fff;
    border-color: #2563eb;
    background: #2563eb;
}

.table-actions .action-edit {
    box-shadow: 0 7px 14px rgba(13, 110, 253, 0.14);
}

.empty-box {
    padding: 2.2rem 1.5rem;
    text-align: center;
    color: var(--cg-muted);
}

.empty-box .icon {
    width: 54px;
    height: 54px;
    border-radius: 50%;
    background: rgba(13,110,253,0.06);
    color: var(--cg-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 0.8rem;
    font-size: 1.5rem;
}

.empty-box h4 {
    color: var(--cg-accent);
    margin-bottom: 0.35rem;
}

.pagination {
    justify-content: flex-end;
    margin: 1rem 1rem 1rem 0;
}

@media (max-width: 991.98px) {
    .admin-root {
        flex-direction: column;
    }

    .sidebar {
        width: 100%;
        position: static;
    }

    .kpi-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 575.98px) {
    .admin-root {
        padding: 1rem;
    }

    .topbar {
        flex-wrap: wrap;
    }

    .kpi-grid {
        grid-template-columns: 1fr;
    }

    .toolbar-row {
        flex-direction: column;
        align-items: stretch;
    }

    .filter-actions {
        width: 100%;
    }

    .filter-actions .btn {
        flex: 1 1 0;
    }

    .result-count {
        width: 100%;
        margin-left: 0;
        text-align: left;
    }
}
/* Shared admin visual language: presentation-only overrides. */
.admin-root { gap:24px; padding:24px; background:#f4f7fb; }
.admin-root .sidebar { width:237px; min-width:237px; flex:0 0 237px; min-height:calc(100vh - 48px); height:auto; align-self:flex-start; padding:18px 14px; border:1px solid rgba(255,255,255,.14); border-radius:20px; background:linear-gradient(155deg,#102c54 0%,#16497f 56%,#0c8879 130%) !important; box-shadow:0 18px 42px rgba(20,57,102,.22); }
.sidebar .brand-wrap { gap:11px; padding:4px 8px 18px; border-color:rgba(255,255,255,.18); }.sidebar .brand-mark-sm { display:inline-flex; align-items:center; justify-content:center; width:42px; height:42px; padding:4px; border-radius:12px; background:#fff; }.sidebar .brand-title { color:#fff; font-size:.92rem; font-weight:800; }.sidebar .brand-subtitle { color:rgba(232,244,255,.72); }
.sidebar .sidebar-nav { gap:4px; margin-top:20px; }.sidebar .nav-link-admin { position:relative; gap:12px; padding:11px 12px; border-radius:11px; color:rgba(235,246,255,.8); font-size:.92rem; font-weight:650; transition:background .2s ease,color .2s ease,transform .2s ease; }.sidebar .nav-link-admin i { width:20px; font-size:1.08rem; text-align:center; }.sidebar .nav-link-admin:hover,.sidebar .nav-link-admin:focus { color:#fff; background:rgba(255,255,255,.12); transform:translateX(2px); }.sidebar .nav-link-admin.active { color:#fff; background:rgba(255,255,255,.18); box-shadow:inset 0 1px 0 rgba(255,255,255,.12); }.sidebar .nav-link-admin.active::before { content:""; position:absolute; left:0; width:4px; height:24px; border-radius:0 5px 5px 0; background:#54e2a6; }.sidebar .sidebar-footer { margin-top:20px; padding-top:14px; border-color:rgba(255,255,255,.18); }.sidebar .sidebar-footer .nav-link-admin:last-child { color:#ffd0cd; }.sidebar .sidebar-footer .nav-link-admin:last-child:hover { color:#fff; background:rgba(220,38,38,.2); }
.main-panel .admin-topbar { min-height:73px; padding:4px 0 20px; margin-bottom:8px; border:0; }.main-panel .admin-topbar .title-area h1 { color:#12213d; font-size:1.65rem; letter-spacing:-.04em; }.main-panel .admin-topbar .subtitle { color:#77849a; }.main-panel .header-search,.main-panel .header-clock,.main-panel .dark-toggle,.main-panel .profile-control { border-color:#e0e8f2; border-radius:12px; box-shadow:0 5px 16px rgba(29,59,101,.04); }.main-panel .profile-control .avatar { color:#fff; background:linear-gradient(135deg,#1e40af,#3b82f6); }
.card-panel { border-color:#e4ebf4; border-radius:17px; box-shadow:0 9px 26px rgba(31,54,88,.05); }.header-panel { padding:22px 24px; }.page-kicker { color:#2563eb; }.header-panel h2 { color:#12213d; font-weight:800; }.table-panel .table thead th { color:#77849a; font-size:.72rem; letter-spacing:.06em; text-transform:uppercase; }.btn { border-radius:10px; }
@media (max-width:991.98px) { .admin-root { padding:16px; gap:16px; }.admin-root .sidebar { width:100%; min-height:0; } }
.page-shell > .header-panel { position:relative; overflow:hidden; border-color:transparent; color:#fff; background:linear-gradient(118deg,#102c54 0%,#16497f 56%,#0c8879 130%); box-shadow:0 18px 34px rgba(20,57,102,.18); }.page-shell > .header-panel::after { content:""; position:absolute; right:48px; top:-88px; width:230px; height:230px; border:1px solid rgba(255,255,255,.15); border-radius:50%; }.page-shell > .header-panel > * { position:relative; z-index:1; }.page-shell > .header-panel .page-kicker { color:#c7ece4; }.page-shell > .header-panel h2 { color:#fff; }.page-shell > .header-panel p { color:rgba(235,247,255,.82); }.page-shell > .header-panel .btn-primary { color:#fff; border-color:rgba(255,255,255,.28); background:rgba(255,255,255,.12); }.page-shell > .header-panel .btn-primary:hover { color:#12345a; border-color:#fff; background:#fff; }
</style>

<main class="container-fluid admin-root">
    <aside class="sidebar">
        <div class="brand-wrap">
            <span class="brand-mark brand-mark-sm">
                <a href="dashboard.php" aria-label="Admin dashboard"><img src="../assets/images/logo.webp" alt="Career Grow Infotech logo" width="34" height="34" loading="lazy"></a>
            </span>
            <div>
                <div class="brand-title">Career Grow Infotech</div>
                <div class="brand-subtitle">Admin Portal</div>
            </div>
        </div>

        <nav class="sidebar-nav" aria-label="Sidebar navigation">
            <a href="dashboard.php" class="nav-link-admin"><i class="bi bi-speedometer2"></i> Dashboard</a>
            <a href="jobs.php" class="nav-link-admin active"><i class="bi bi-briefcase"></i> Jobs</a>
            <a href="applicants.php" class="nav-link-admin"><i class="bi bi-people"></i> Applicants</a>
            <a href="candidate-details.php" class="nav-link-admin"><i class="bi bi-person-badge"></i> Candidates</a>
            <a href="contact-messages.php" class="nav-link-admin"><i class="bi bi-envelope-paper"></i> Contact Messages</a>
            <a href="settings.php" class="nav-link-admin"><i class="bi bi-gear"></i> Settings</a>
        </nav>

        <div class="sidebar-footer">
            <a href="../index.php" class="nav-link-admin"><i class="bi bi-house"></i> Back to Homepage</a>
            <a href="../logout.php" class="nav-link-admin"><i class="bi bi-box-arrow-right"></i> Logout</a>
        </div>
    </aside>

    <section class="main-panel">
        <?php
        $pageH1 = 'Jobs';
        $pageSubtitle = 'Recruitment pipeline overview';
        require_once __DIR__ . '/../includes/admin-header.php';
        ?>

        <div class="page-shell">
            <div class="card-panel header-panel">
                <div class="page-kicker"><i class="bi bi-diagram-3"></i> Admin / Jobs</div>
                <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
                    <div>
                        <h2>Jobs Management</h2>
                        <p>Track active recruitment listings and manage hiring opportunities across the platform.</p>
                    </div>
                    <a href="add-job.php" class="btn btn-primary">Add New Job</a>
                </div>
            </div>

            <?php if (!empty($_SESSION['job_status_message'])): ?>
                <div class="alert alert-info mx-3 mb-3">
                    <?php echo htmlspecialchars((string)$_SESSION['job_status_message'], ENT_QUOTES, 'UTF-8'); ?>
                </div>
                <?php unset($_SESSION['job_status_message']); ?>
            <?php endif; ?>

            <div class="kpi-grid">
                <div class="kpi-card kpi-blue">
                    <div class="meta">
                        <div>
                            <div class="label">Total Jobs</div>
                            <p class="value"><?php echo htmlspecialchars((string)$kpis['total'], ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                        <div class="icon"><i class="bi bi-briefcase"></i></div>
                    </div>
                </div>

                <div class="kpi-card kpi-green">
                    <div class="meta">
                        <div>
                            <div class="label">Active Jobs</div>
                            <p class="value"><?php echo htmlspecialchars((string)$kpis['active'], ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                        <div class="icon"><i class="bi bi-check-circle"></i></div>
                    </div>
                </div>

                <div class="kpi-card kpi-purple">
                    <div class="meta">
                        <div>
                            <div class="label">Inactive Jobs</div>
                            <p class="value"><?php echo htmlspecialchars((string)$kpis['inactive'], ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                        <div class="icon"><i class="bi bi-slash-circle"></i></div>
                    </div>
                </div>

                <div class="kpi-card kpi-orange">
                    <div class="meta">
                        <div>
                            <div class="label">Jobs with Applications</div>
                            <p class="value"><?php echo htmlspecialchars((string)$kpis['with_applications'], ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                        <div class="icon"><i class="bi bi-file-earmark-text"></i></div>
                    </div>
                </div>
            </div>

            <div class="card-panel toolbar-panel">
                <form method="get" class="toolbar-row">
                    <div class="search-wrap">
                        <i class="bi bi-search"></i>
                        <input type="text" name="q" value="<?php echo htmlspecialchars($q, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" placeholder="Search by job title or location">
                    </div>

                    <select name="status" class="form-select" aria-label="Filter by status">
                        <option value="">Status</option>
                        <option value="active" <?php if ($statusFilter === 'active') echo 'selected'; ?>>Active</option>
                        <option value="inactive" <?php if ($statusFilter === 'inactive') echo 'selected'; ?>>Inactive</option>
                    </select>

                    <select name="job_type" class="form-select" aria-label="Filter by job type">
                        <option value="">Job Types</option>
                        <?php foreach ($jobTypes as $jobType): ?>
                            <option value="<?php echo htmlspecialchars($jobType, ENT_QUOTES, 'UTF-8'); ?>" <?php if ($jobTypeFilter === $jobType) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($jobType, ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <div class="filter-actions">
                        <button type="submit" class="btn filter-apply"><i class="bi bi-funnel"></i>Apply</button>
                        <a href="jobs.php" class="btn filter-reset"><i class="bi bi-arrow-counterclockwise"></i>Reset</a>
                    </div>

                    <div class="result-count">Showing <?php echo count($jobs); ?> of <?php echo $totalJobs; ?> results</div>
                </form>
            </div>

            <div class="card-panel table-panel">
                <?php if (empty($jobs)): ?>
                    <div class="empty-box">
                        <div class="icon"><i class="bi bi-briefcase"></i></div>
                        <h4><?php echo ($q !== '' || $statusFilter !== '' || $jobTypeFilter !== '') ? 'No matching jobs found' : 'No jobs found'; ?></h4>
                        <p class="mb-3"><?php echo ($q !== '' || $statusFilter !== '' || $jobTypeFilter !== '') ? 'Try adjusting your filters or resetting the search.' : 'There are no jobs in the database yet.'; ?></p>
                        <?php if ($q !== '' || $statusFilter !== '' || $jobTypeFilter !== ''): ?>
                            <a href="jobs.php" class="btn btn-outline-primary">Clear Filters</a>
                        <?php else: ?>
                            <a href="add-job.php" class="btn btn-primary">Add New Job</a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Job Title</th>
                                    <th>Company</th>
                                    <th>Location</th>
                                    <th>Job Type</th>
                                    <th>Status</th>
                                    <th>Posted</th>
                                    <th class="text-end">Applications</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($jobs as $job): ?>
                                    <?php $companyName = trim((string)($job['company'] ?? '')); $companyDisplay = $companyName !== '' ? $companyName : 'Career Grow Infotech'; ?>
                                    <tr>
                                        <td class="job-cell">
                                            <div class="job-title"><?php echo htmlspecialchars((string)($job['title'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div>
                                            <div class="job-meta">
                                                <span><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars((string)($job['location'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></span>
                                                <span class="dot">•</span>
                                                <span><?php echo htmlspecialchars((string)($job['experience_level'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></span>
                                                <span class="dot">•</span>
                                                <span><?php echo htmlspecialchars((string)($job['openings'] ?? '0'), ENT_QUOTES, 'UTF-8'); ?> openings</span>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($companyDisplay, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string)($job['location'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string)($job['job_type'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <?php $status = strtolower((string)($job['status'] ?? 'inactive')); ?>
                                            <span class="badge-status <?php echo ($status === 'active') ? 'active' : 'inactive'; ?>"><?php echo htmlspecialchars(ucfirst($status), ENT_QUOTES, 'UTF-8'); ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars((string)($job['created_at'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="text-end"><?php echo htmlspecialchars((string)($job['app_count'] ?? 0), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="text-end">
                                            <div class="table-actions">
                                                <form method="post" action="jobs.php" style="display: inline;">
                                                    <input type="hidden" name="action" value="toggle_job_status">
                                                    <input type="hidden" name="job_status_token" value="<?php echo htmlspecialchars($_SESSION['job_status_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                    <input type="hidden" name="job_id" value="<?php echo (int)($job['id'] ?? 0); ?>">
                                                    <?php $status = strtolower((string)($job['status'] ?? 'inactive')); $toggleStatus = $status === 'active' ? 'inactive' : 'active'; $toggleLabel = $status === 'active' ? 'Deactivate' : 'Activate'; ?>
                                                    <input type="hidden" name="new_status" value="<?php echo htmlspecialchars($toggleStatus, ENT_QUOTES, 'UTF-8'); ?>">
                                                    <button type="submit" class="btn btn-sm action-toggle" title="<?php echo htmlspecialchars($toggleLabel, ENT_QUOTES, 'UTF-8'); ?> job" aria-label="<?php echo htmlspecialchars($toggleLabel, ENT_QUOTES, 'UTF-8'); ?> job">
                                                        <i class="bi <?php echo $status === 'active' ? 'bi-pause-circle' : 'bi-play-circle'; ?>" aria-hidden="true"></i>
                                                    </button>
                                                </form>
                                                <a href="job-details.php?id=<?php echo (int)($job['id'] ?? 0); ?>" class="btn btn-sm action-view" title="View job" aria-label="View job"><i class="bi bi-eye" aria-hidden="true"></i></a>
                                                <a href="edit-job.php?id=<?php echo (int)($job['id'] ?? 0); ?>" class="btn btn-sm btn-primary action-edit" title="Edit job" aria-label="Edit job"><i class="bi bi-pencil-square" aria-hidden="true"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($totalPages > 1): ?>
                        <nav aria-label="Jobs pagination">
                            <ul class="pagination">
                                <?php if ($page > 1): ?>
                                    <li class="page-item"><a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">Previous</a></li>
                                <?php else: ?>
                                    <li class="page-item disabled"><span class="page-link">Previous</span></li>
                                <?php endif; ?>

                                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                                    <li class="page-item <?php if ($p === $page) echo 'active'; ?>">
                                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $p])); ?>"><?php echo (int)$p; ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($page < $totalPages): ?>
                                    <li class="page-item"><a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">Next</a></li>
                                <?php else: ?>
                                    <li class="page-item disabled"><span class="page-link">Next</span></li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
