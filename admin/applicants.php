<?php

declare(strict_types=1);
session_start();

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/admin-auth.php';
require_admin();

$pageTitle = 'Applicants - Admin';
require_once __DIR__ . '/../includes/header.php';

$conn = getDbConnection();

function safe_count(mysqli $conn, string $sql): int {
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return 0;
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_row();
    $stmt->close();
    return (int)($row[0] ?? 0);
}

$statusValues = [];
$statusStmt = $conn->prepare('SELECT DISTINCT status FROM applications WHERE status IS NOT NULL AND status <> \"\" ORDER BY status ASC');
if ($statusStmt) {
    $statusStmt->execute();
    $statusResult = $statusStmt->get_result();
    while ($row = $statusResult->fetch_row()) {
        $statusValues[] = (string)$row[0];
    }
    $statusStmt->close();
}

$jobOptions = [];
$jobStmt = $conn->prepare('SELECT DISTINCT j.id, j.title FROM jobs j INNER JOIN applications a ON a.job_id = j.id ORDER BY j.title ASC');
if ($jobStmt) {
    $jobStmt->execute();
    $jobResult = $jobStmt->get_result();
    while ($row = $jobResult->fetch_assoc()) {
        $jobOptions[] = $row;
    }
    $jobStmt->close();
}

$kpis = [
    'total' => safe_count($conn, 'SELECT COUNT(*) FROM applications'),
    'pending' => 0,
    'shortlisted' => 0,
    'selected' => 0,
    'rejected' => 0,
];

foreach ($statusValues as $status) {
    $normalized = strtolower(trim($status));
    if (str_contains($normalized, 'new') || str_contains($normalized, 'applied')) {
        $kpis['pending'] += safe_count($conn, "SELECT COUNT(*) FROM applications WHERE LOWER(status) = '" . $conn->real_escape_string($normalized) . "'");
    } elseif (str_contains($normalized, 'shortlist')) {
        $kpis['shortlisted'] += safe_count($conn, "SELECT COUNT(*) FROM applications WHERE LOWER(status) = '" . $conn->real_escape_string($normalized) . "'");
    } elseif (str_contains($normalized, 'select') || str_contains($normalized, 'hire') || str_contains($normalized, 'offer')) {
        $kpis['selected'] += safe_count($conn, "SELECT COUNT(*) FROM applications WHERE LOWER(status) = '" . $conn->real_escape_string($normalized) . "'");
    } elseif (str_contains($normalized, 'reject')) {
        $kpis['rejected'] += safe_count($conn, "SELECT COUNT(*) FROM applications WHERE LOWER(status) = '" . $conn->real_escape_string($normalized) . "'");
    }
}

// Fallback if the live schema uses only one of the common statuses
if ($kpis['pending'] === 0 && in_array('New Applied', $statusValues, true)) {
    $kpis['pending'] = safe_count($conn, "SELECT COUNT(*) FROM applications WHERE status = 'New Applied'");
}
if ($kpis['shortlisted'] === 0 && in_array('Shortlisted', $statusValues, true)) {
    $kpis['shortlisted'] = safe_count($conn, "SELECT COUNT(*) FROM applications WHERE status = 'Shortlisted'");
}
if ($kpis['selected'] === 0 && in_array('Selected', $statusValues, true)) {
    $kpis['selected'] = safe_count($conn, "SELECT COUNT(*) FROM applications WHERE status = 'Selected'");
}
if ($kpis['rejected'] === 0 && in_array('Rejected', $statusValues, true)) {
    $kpis['rejected'] = safe_count($conn, "SELECT COUNT(*) FROM applications WHERE status = 'Rejected'");
}

$search = trim((string)($_GET['q'] ?? ''));
$statusFilter = trim((string)($_GET['status'] ?? ''));
$jobFilter = trim((string)($_GET['job_id'] ?? ''));
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

$where = [];
$params = [];
$types = '';

if ($search !== '') {
    $where[] = '(u.name LIKE ? OR u.email LIKE ? OR j.title LIKE ?)';
    $like = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types .= 'sss';
}

if ($statusFilter !== '') {
    $where[] = 'a.status = ?';
    $params[] = $statusFilter;
    $types .= 's';
}

if ($jobFilter !== '') {
    $jobFilterId = (int)$jobFilter;
    $where[] = 'a.job_id = ?';
    $params[] = $jobFilterId;
    $types .= 'i';
}

$whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$countSql = "SELECT COUNT(*) FROM applications a LEFT JOIN users u ON u.id = a.user_id LEFT JOIN jobs j ON j.id = a.job_id $whereSql";
$countStmt = $conn->prepare($countSql);
$totalApplications = 0;
if ($countStmt) {
    if (!empty($params)) {
        $countStmt->bind_param($types, ...$params);
    }
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $countRow = $countResult->fetch_row();
    $totalApplications = (int)($countRow[0] ?? 0);
    $countStmt->close();
}

$totalPages = max(1, (int)ceil($totalApplications / $perPage));

$applications = [];
$sql = "SELECT a.id, a.user_id, a.job_id, a.resume, a.status, a.applied_at,
        u.name AS candidate_name, u.email AS candidate_email,
        j.title AS job_title, j.location AS job_location,
        j.id AS job_id_value
        FROM applications a
        LEFT JOIN users u ON u.id = a.user_id
        LEFT JOIN jobs j ON j.id = a.job_id
        $whereSql
        ORDER BY a.applied_at DESC
        LIMIT ? OFFSET ?";

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
        $applications[] = $row;
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
    padding: 0.25rem 0 1rem;
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
    grid-template-columns: repeat(5, minmax(0, 1fr));
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
    font-size: 1.5rem;
    font-weight: 800;
    line-height: 1.2;
    margin: 0;
}

.kpi-card .icon {
    width: 42px;
    height: 42px;
    border-radius: 0.75rem;
    background: rgba(13,110,253,0.08);
    color: var(--cg-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
}

.toolbar-panel {
    padding: 1rem;
    margin-bottom: 1rem;
}

.toolbar-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.search-wrap {
    position: relative;
    flex: 1 1 250px;
    min-width: 0;
}

.search-wrap .bi {
    position: absolute;
    left: 0.8rem;
    top: 50%;
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
    padding: 0.2rem 0;
}

.table-wrap {
    width: 100%;
    overflow: auto;
}

.table {
    margin-bottom: 0;
}

.table thead th {
    background: rgba(13,110,253,0.02);
    color: var(--cg-muted);
    font-size: 0.76rem;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    padding: 0.9rem 1rem;
    border-bottom: 1px solid var(--cg-border);
}

.table tbody td {
    padding: 1rem;
    vertical-align: middle;
    border-bottom: 1px solid rgba(15, 23, 42, 0.04);
}

.table tbody tr:hover {
    background: rgba(13,110,253,0.02);
}

.applicant-cell {
    min-width: 240px;
}

.applicant-name {
    font-weight: 700;
    color: var(--cg-accent);
    line-height: 1.3;
}

.applicant-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.45rem;
    color: var(--cg-muted);
    font-size: 0.83rem;
    margin-top: 0.25rem;
}

.job-brief {
    color: var(--cg-accent);
    font-weight: 600;
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
    white-space: nowrap;
}

.badge-status.pending {
    background: rgba(13,110,253,0.09);
    color: var(--cg-primary);
    border: 1px solid rgba(13,110,253,0.12);
}

.badge-status.shortlisted {
    background: rgba(25,135,84,0.09);
    color: #198754;
    border: 1px solid rgba(25,135,84,0.12);
}

.badge-status.selected {
    background: rgba(255,193,7,0.12);
    color: #b27900;
    border: 1px solid rgba(255,193,7,0.2);
}

.badge-status.rejected {
    background: rgba(220,53,69,0.08);
    color: #b02a37;
    border: 1px solid rgba(220,53,69,0.12);
}

.badge-status.default-state {
    background: rgba(108,117,125,0.08);
    color: var(--cg-muted);
    border: 1px solid rgba(108,117,125,0.12);
}

.table-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    white-space: nowrap;
}

.table-actions .btn {
    padding: 0.45rem 0.7rem;
    font-size: 0.8rem;
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

    .result-count {
        width: 100%;
        margin-left: 0;
        text-align: left;
    }
}
</style>

<main class="container-fluid admin-root">
    <aside class="sidebar">
        <div class="brand-wrap">
            <span class="brand-mark brand-mark-sm">
                <img src="../assets/images/logo.webp" alt="Career Grow Infotech logo" width="34" height="34" loading="lazy">
            </span>
            <div>
                <div class="brand-title">Career Grow Infotech</div>
                <div class="brand-subtitle">Admin Portal</div>
            </div>
        </div>

        <nav class="sidebar-nav" aria-label="Sidebar navigation">
            <a href="dashboard.php" class="nav-link-admin"><i class="bi bi-speedometer2"></i> Dashboard</a>
            <a href="jobs.php" class="nav-link-admin"><i class="bi bi-briefcase"></i> Jobs</a>
            <a href="applicants.php" class="nav-link-admin active"><i class="bi bi-people"></i> Applicants</a>
            <a href="candidate-details.php" class="nav-link-admin"><i class="bi bi-person-badge"></i> Candidates</a>
            <a href="settings.php" class="nav-link-admin"><i class="bi bi-gear"></i> Settings</a>
        </nav>

        <div class="sidebar-footer">
            <a href="../index.php" class="nav-link-admin"><i class="bi bi-house"></i> Back to Website</a>
            <a href="../logout.php" class="nav-link-admin"><i class="bi bi-box-arrow-right"></i> Logout</a>
        </div>
    </aside>

    <section class="main-panel">
        <div class="topbar">
            <div class="topbar-title">
                <h1>Applicants</h1>
                <div class="topbar-subtitle">Manage candidate applications and recruitment workflow</div>
            </div>
            <div class="user-pill">
                <div class="avatar"><i class="bi bi-person-circle"></i></div>
                <div>
                    <div class="fw-semibold">Administrator</div>
                    <div class="text-muted small">Admin</div>
                </div>
            </div>
        </div>

        <div class="card-panel header-panel">
            <div class="page-kicker"><i class="bi bi-people"></i> Admin / Applicants</div>
            <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
                <div>
                    <h2>Applicants</h2>
                    <p>Review and track candidate applications submitted across active job posts.</p>
                </div>
                <div class="fw-semibold text-primary"><?php echo htmlspecialchars((string)$kpis['total'], ENT_QUOTES, 'UTF-8'); ?> total applications</div>
            </div>
        </div>

        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="meta">
                    <div>
                        <div class="label">Total Applications</div>
                        <p class="value"><?php echo htmlspecialchars((string)$kpis['total'], ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                    <div class="icon"><i class="bi bi-file-earmark-text"></i></div>
                </div>
            </div>

            <div class="kpi-card">
                <div class="meta">
                    <div>
                        <div class="label">Pending</div>
                        <p class="value"><?php echo htmlspecialchars((string)$kpis['pending'], ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                    <div class="icon"><i class="bi bi-hourglass-split"></i></div>
                </div>
            </div>

            <div class="kpi-card">
                <div class="meta">
                    <div>
                        <div class="label">Shortlisted</div>
                        <p class="value"><?php echo htmlspecialchars((string)$kpis['shortlisted'], ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                    <div class="icon"><i class="bi bi-clipboard-check"></i></div>
                </div>
            </div>

            <div class="kpi-card">
                <div class="meta">
                    <div>
                        <div class="label">Selected</div>
                        <p class="value"><?php echo htmlspecialchars((string)$kpis['selected'], ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                    <div class="icon"><i class="bi bi-person-check"></i></div>
                </div>
            </div>

            <div class="kpi-card">
                <div class="meta">
                    <div>
                        <div class="label">Rejected</div>
                        <p class="value"><?php echo htmlspecialchars((string)$kpis['rejected'], ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                    <div class="icon"><i class="bi bi-x-circle"></i></div>
                </div>
            </div>
        </div>

        <div class="card-panel toolbar-panel">
            <form method="get" class="toolbar-row">
                <div class="search-wrap">
                    <i class="bi bi-search"></i>
                    <input type="text" name="q" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" placeholder="Search by candidate name, email or job title">
                </div>

                <select name="status" class="form-select" aria-label="Filter by application status">
                    <option value="">All statuses</option>
                    <?php foreach ($statusValues as $statusValue): ?>
                        <option value="<?php echo htmlspecialchars($statusValue, ENT_QUOTES, 'UTF-8'); ?>" <?php if ($statusFilter === $statusValue) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($statusValue, ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="job_id" class="form-select" aria-label="Filter by job">
                    <option value="">All jobs</option>
                    <?php foreach ($jobOptions as $jobOption): ?>
                        <option value="<?php echo (int)$jobOption['id']; ?>" <?php if ((string)$jobFilter === (string)$jobOption['id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars((string)$jobOption['title'], ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="btn btn-outline-primary">Apply</button>
                <a href="applicants.php" class="btn btn-link text-decoration-none">Reset</a>

                <div class="result-count">Showing <?php echo count($applications); ?> of <?php echo $totalApplications; ?> results</div>
            </form>
        </div>

        <div class="card-panel table-panel">
            <?php if (empty($applications)): ?>
                <div class="empty-box">
                    <div class="icon"><i class="bi bi-people"></i></div>
                    <h4><?php echo ($search !== '' || $statusFilter !== '' || $jobFilter !== '') ? 'No matching applicants found' : 'No applications yet'; ?></h4>
                    <p class="mb-3"><?php echo ($search !== '' || $statusFilter !== '' || $jobFilter !== '') ? 'Adjust your filter selections and try again.' : 'Candidates have not applied to any jobs yet.'; ?></p>
                    <?php if ($search !== '' || $statusFilter !== '' || $jobFilter !== ''): ?>
                        <a href="applicants.php" class="btn btn-outline-primary">Clear Filters</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Candidate</th>
                                <th>Job</th>
                                <th>Status</th>
                                <th>Applied</th>
                                <th>Resume</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($applications as $application): ?>
                                <?php
                                    $statusLabel = trim((string)($application['status'] ?? ''));
                                    $normalizedStatus = strtolower($statusLabel);
                                    $badgeClass = 'default-state';
                                    if (str_contains($normalizedStatus, 'new') || str_contains($normalizedStatus, 'applied')) {
                                        $badgeClass = 'pending';
                                    } elseif (str_contains($normalizedStatus, 'shortlist')) {
                                        $badgeClass = 'shortlisted';
                                    } elseif (str_contains($normalizedStatus, 'select') || str_contains($normalizedStatus, 'hire') || str_contains($normalizedStatus, 'offer')) {
                                        $badgeClass = 'selected';
                                    } elseif (str_contains($normalizedStatus, 'reject')) {
                                        $badgeClass = 'rejected';
                                    }
                                ?>
                                <tr>
                                    <td class="applicant-cell">
                                        <div class="applicant-name"><?php echo htmlspecialchars((string)($application['candidate_name'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div>
                                        <div class="applicant-meta">
                                            <span><i class="bi bi-envelope"></i> <?php echo htmlspecialchars((string)($application['candidate_email'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="job-brief"><?php echo htmlspecialchars((string)($application['job_title'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div>
                                        <div class="applicant-meta">
                                            <span><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars((string)($application['job_location'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge-status <?php echo htmlspecialchars($badgeClass, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($statusLabel !== '' ? $statusLabel : 'Unknown', ENT_QUOTES, 'UTF-8'); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars((string)($application['applied_at'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td>
                                        <?php if (!empty($application['resume'])): ?>
                                            <a href="../uploads/resumes/<?php echo rawurlencode((string)$application['resume']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-secondary">View Resume</a>
                                        <?php else: ?>
                                            <span class="text-muted">Not available</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="table-actions">
                                            <a href="candidate-details.php?id=<?php echo (int)($application['user_id'] ?? 0); ?>" class="btn btn-sm btn-primary">View</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($totalPages > 1): ?>
                    <nav aria-label="Applications pagination">
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
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
