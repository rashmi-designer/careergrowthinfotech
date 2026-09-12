<?php
declare(strict_types=1);
session_start();

// Require DB and admin-auth (placeholder)
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/admin-auth.php';
require_admin();

$pageTitle = 'Admin Dashboard - Career Grow Infotech';
require_once __DIR__ . '/../includes/header.php';

// Fetch KPI counts safely using prepared statements
$conn = getDbConnection();

$kpis = [
    'jobs' => 0,
    'candidates' => 0,
    'applications' => 0,
    'messages' => 0,
];

function safe_count(mysqli $conn, string $sql): int {
    $stmt = $conn->prepare($sql);
    if (!$stmt) return 0;
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_row();
    $stmt->close();
    return (int)($row[0] ?? 0);
}

try {
    $kpis['jobs'] = safe_count($conn, 'SELECT COUNT(*) FROM jobs');
    $kpis['candidates'] = safe_count($conn, "SELECT COUNT(*) FROM users WHERE role = 'candidate'");
    $kpis['applications'] = safe_count($conn, 'SELECT COUNT(*) FROM applications');
    $kpis['messages'] = safe_count($conn, 'SELECT COUNT(*) FROM contact_messages WHERE is_read = 0');
} catch (Throwable $e) {
    // keep zeros on error, do not expose DB errors
}

// Recent jobs
$recentJobs = [];
$stmt = $conn->prepare('SELECT id, title, location, status, created_at FROM jobs ORDER BY created_at DESC LIMIT 5');
if ($stmt) {
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) { $recentJobs[] = $r; }
    $stmt->close();
}

// Recent applications
$recentApps = [];
$stmt = $conn->prepare('SELECT a.id, a.applied_at, a.status, u.name AS candidate, j.title AS job_title, a.user_id, a.job_id FROM applications a JOIN users u ON a.user_id = u.id JOIN jobs j ON a.job_id = j.id ORDER BY a.applied_at DESC LIMIT 5');
if ($stmt) {
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) { $recentApps[] = $r; }
    $stmt->close();
}

// Application status distribution
$appStatus = [];
$stmt = $conn->prepare('SELECT status, COUNT(*) AS cnt FROM applications GROUP BY status');
if ($stmt) {
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) { $appStatus[$r['status']] = (int)$r['cnt']; }
    $stmt->close();
}

$conn->close();
?>

<style>
/* Dashboard-only presentation layer. Queries, routes and controls remain unchanged. */
.admin-root { --dash-ink:#12213d; --dash-navy:#102b52; --dash-blue:#2563eb; --dash-mint:#18a66a; --dash-surface:#ffffff; min-height:100vh; display:flex; gap:24px; padding:24px; background:#f4f7fb; color:var(--dash-ink); }
.admin-root .sidebar { width:248px; min-height:calc(100vh - 48px); height:auto; align-self:flex-start; position:sticky; top:24px; display:flex; flex-direction:column; padding:18px 14px; border:1px solid rgba(255,255,255,.14); border-radius:20px; background:linear-gradient(155deg,#102c54 0%,#16497f 56%,#0c8879 130%); box-shadow:0 18px 42px rgba(20,57,102,.22); }
.sidebar .brand { display:flex; gap:11px; align-items:center; padding:4px 8px 18px; border-bottom:1px solid rgba(255,255,255,.18); }
.brand-mark-sm { width:42px; height:42px; padding:4px; display:inline-flex; align-items:center; justify-content:center; border-radius:12px; background:rgba(255,255,255,.96); }
.brand-mark-sm img { max-width:100%; max-height:100%; object-fit:contain; }
.brand-title { color:#fff; font-size:.92rem; font-weight:800; letter-spacing:-.02em; }
.sidebar .text-soft { color:rgba(232,244,255,.72) !important; }
.sidebar nav { display:grid; gap:4px; margin-top:20px !important; }
.nav-link-admin { position:relative; display:flex; align-items:center; gap:12px; padding:11px 12px; border-radius:11px; color:rgba(235,246,255,.8); font-size:.92rem; font-weight:650; text-decoration:none; transition:background .2s ease,color .2s ease,transform .2s ease; }
.nav-link-admin i { width:20px; font-size:1.08rem; text-align:center; }
.nav-link-admin:hover { color:#fff; background:rgba(255,255,255,.12); transform:translateX(2px); }
.nav-link-admin.active { color:#fff; background:rgba(255,255,255,.18); box-shadow:inset 0 1px 0 rgba(255,255,255,.12); }
.nav-link-admin.active::before { content:""; position:absolute; left:0; width:4px; height:24px; border-radius:0 5px 5px 0; background:#54e2a6; }
.sidebar .mt-auto { margin-top:20px !important; padding-top:14px !important; border-top:1px solid rgba(255,255,255,.18); }
.sidebar .mt-auto .nav-link-admin:last-child { color:#ffd0cd; }
.sidebar .mt-auto .nav-link-admin:last-child:hover { color:#fff; background:rgba(220,38,38,.2); }
.dashboard-content { min-width:0; flex:1 1 auto; padding:2px 4px 24px; }
.dashboard-content .admin-topbar { min-height:73px; padding:4px 0 20px; margin-bottom:8px; border-bottom:0; }
.dashboard-content .admin-topbar .title-area h1 { color:var(--dash-ink); font-size:1.65rem; letter-spacing:-.04em; }
.dashboard-content .admin-topbar .subtitle { margin-top:3px; color:#77849a; }
.dashboard-content .header-search { border-color:#e0e8f2; border-radius:12px; background:#fff; }
.dashboard-content .header-clock, .dashboard-content .dark-toggle { border-color:#e0e8f2; background:#fff; box-shadow:0 5px 16px rgba(29,59,101,.04); }
.dashboard-content .profile-control { border-radius:12px; border-color:#e0e8f2; box-shadow:0 5px 16px rgba(29,59,101,.04); }
.dashboard-content .profile-control .avatar { color:#fff; background:linear-gradient(135deg,#1e40af,#3b82f6); }
.dashboard-hero { position:relative; overflow:hidden; display:flex; align-items:center; justify-content:space-between; gap:24px; min-height:170px; padding:28px 32px; margin-bottom:22px; border-radius:20px; color:#fff; background:linear-gradient(118deg,#102c54 0%,#16497f 56%,#0c8879 130%); box-shadow:0 18px 34px rgba(20,57,102,.18); }
.dashboard-hero::before, .dashboard-hero::after { content:""; position:absolute; border:1px solid rgba(255,255,255,.14); border-radius:50%; pointer-events:none; }
.dashboard-hero::before { width:230px; height:230px; right:55px; top:-70px; }
.dashboard-hero::after { width:145px; height:145px; right:98px; top:-28px; }
.hero-content, .hero-actions { position:relative; z-index:1; }
.hero-kicker { display:flex; align-items:center; gap:8px; margin-bottom:7px; color:#c7ece4; font-size:.73rem; font-weight:800; letter-spacing:.13em; text-transform:uppercase; }
.hero-kicker::before { content:""; width:8px; height:8px; border-radius:50%; background:#47dda1; box-shadow:0 0 0 5px rgba(71,221,161,.15); }
.dashboard-hero h2 { margin:0; font-size:clamp(1.45rem,2.6vw,2.15rem); font-weight:800; letter-spacing:-.045em; }
.dashboard-hero p { max-width:610px; margin:8px 0 0; color:rgba(235,247,255,.82); font-size:.95rem; }
.hero-actions { display:flex; flex-wrap:wrap; gap:10px; min-width:240px; justify-content:flex-end; }
.hero-actions .btn { padding:9px 14px; border-radius:10px; border-color:rgba(255,255,255,.28); color:#fff; background:rgba(255,255,255,.1); font-size:.88rem; font-weight:700; }
.hero-actions .btn:hover { color:#12345a; border-color:#fff; background:#fff; }
.kpi-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:16px; margin-bottom:22px; }
.kpi { min-height:128px; padding:19px; border:1px solid #e5ebf3; border-radius:16px; background:#fff; box-shadow:0 9px 25px rgba(31,54,88,.05); transition:transform .2s ease,box-shadow .2s ease; }
.kpi:hover { transform:translateY(-3px); box-shadow:0 16px 28px rgba(31,54,88,.09); }
.kpi-top { display:flex; align-items:center; justify-content:space-between; gap:12px; }
.kpi-label { color:#748197; font-size:.79rem; font-weight:750; letter-spacing:.04em; text-transform:uppercase; }
.kpi .num { margin-top:10px; color:var(--dash-ink); font-size:1.85rem; font-weight:800; letter-spacing:-.05em; line-height:1; }
.kpi-icon { width:43px; height:43px; display:inline-flex; align-items:center; justify-content:center; border-radius:12px; font-size:1.18rem; }
.kpi-jobs .kpi-icon { color:#2763d7; background:#eaf1ff; }.kpi-candidates .kpi-icon { color:#08976b; background:#e7f8f0; }.kpi-applications .kpi-icon { color:#8856d8; background:#f2ebff; }.kpi-messages .kpi-icon { color:#d88020; background:#fff3df; }
.dashboard-panel { border:1px solid #e4ebf4; border-radius:17px; background:#fff; box-shadow:0 9px 26px rgba(31,54,88,.05); }
.panel-head { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:20px 21px 14px; }
.panel-head h5 { margin:0; color:var(--dash-ink); font-size:1rem; font-weight:800; letter-spacing:-.02em; }
.panel-link { color:#2563eb; font-size:.83rem; font-weight:700; text-decoration:none; }.panel-link:hover { color:#1748af; text-decoration:underline; }
.jobs-table { margin:0; }.jobs-table thead th { padding:12px 21px; border-bottom:1px solid #edf1f5; color:#77849a; font-size:.71rem; font-weight:800; letter-spacing:.06em; text-transform:uppercase; white-space:nowrap; }.jobs-table tbody td { padding:15px 21px; border-color:#edf1f5; color:#536178; font-size:.88rem; }.jobs-table tbody tr:hover { background:#f8fbff; }.job-title { display:block; color:var(--dash-ink); font-weight:750; }.job-location { display:flex; align-items:center; gap:5px; margin-top:3px; color:#8793a5; font-size:.79rem; }
.status-badge { display:inline-flex; padding:5px 9px; border-radius:999px; font-size:.72rem; font-weight:800; text-transform:capitalize; }.status-active { color:#087e55; background:#e7f8ef; }.status-inactive { color:#c66b14; background:#fff4e5; }
.jobs-table th:last-child, .jobs-table td:last-child { min-width:84px; white-space:nowrap; }.job-actions { display:inline-flex; align-items:center; gap:4px; white-space:nowrap; }.table-action { width:32px; height:32px; display:inline-flex; align-items:center; justify-content:center; padding:0; border-radius:9px; }
.empty-state { padding:24px 21px 28px; color:#77849a; }.empty-state a { color:#2563eb; font-weight:700; }
.application-list { margin:0; padding:0 12px 10px; }.application-list .list-group-item { padding:13px 8px; border-color:#edf1f5; background:transparent; }.app-avatar { flex:0 0 40px; width:40px; height:40px; display:inline-flex; align-items:center; justify-content:center; border-radius:12px; color:#2563eb; background:#ebf2ff; }.applicant-name { color:var(--dash-ink); font-size:.89rem; font-weight:750; }.applicant-role { max-width:170px; overflow:hidden; color:#7b8799; font-size:.78rem; text-overflow:ellipsis; white-space:nowrap; }.app-date { color:#8a96a7; font-size:.72rem; }.app-view { color:#2563eb; font-size:.79rem; font-weight:750; text-decoration:none; }.app-view:hover { text-decoration:underline; }
.activity-panel { margin-top:16px; }.activity-list { padding:0 21px 16px; }.activity-list li { padding:9px 0; border-bottom:1px solid #edf1f5; color:#66758b; font-size:.88rem; }.activity-list li:last-child { border-bottom:0; }.activity-list strong { min-width:28px; padding:3px 8px; border-radius:8px; color:#2563eb; background:#edf3ff; text-align:center; font-size:.76rem; }
.quick-actions { margin-top:22px; }.section-heading { margin:0 0 13px; color:var(--dash-ink); font-size:1rem; font-weight:800; }.quick-action { height:100%; display:flex; align-items:center; gap:13px; padding:16px; border:1px solid #e5ebf3; border-radius:14px; color:var(--dash-ink); background:#fff; box-shadow:0 7px 20px rgba(31,54,88,.04); text-decoration:none; transition:transform .2s ease,box-shadow .2s ease,border-color .2s ease; }.quick-action:hover { border-color:#bdd3fa; color:var(--dash-ink); box-shadow:0 13px 24px rgba(31,54,88,.08); transform:translateY(-2px); }.quick-action-icon { width:40px; height:40px; display:inline-flex; flex:0 0 40px; align-items:center; justify-content:center; border-radius:11px; color:#2563eb; background:#eaf2ff; font-size:1.1rem; }.quick-action:nth-child(2) .quick-action-icon { color:#078b64; background:#e7f8f0; }.quick-action:nth-child(3) .quick-action-icon { color:#8a58d7; background:#f2ebff; }.quick-action:nth-child(4) .quick-action-icon { color:#ce7b22; background:#fff3df; }.quick-action-title { display:block; font-size:.87rem; font-weight:800; }.quick-action-copy { display:block; margin-top:2px; color:#7c899b; font-size:.76rem; }
html[data-theme="dark"] .admin-root { --dash-ink:#e7edf8; --dash-surface:#172235; background:#0f172a; }.admin-root .sidebar, html[data-theme="dark"] .dashboard-panel, html[data-theme="dark"] .kpi, html[data-theme="dark"] .quick-action { background:var(--dash-surface); border-color:#2c3a50; }html[data-theme="dark"] .admin-root .sidebar { border-color:rgba(255,255,255,.32); }.sidebar .brand, .sidebar .mt-auto, html[data-theme="dark"] .jobs-table thead th, html[data-theme="dark"] .jobs-table tbody td, html[data-theme="dark"] .application-list .list-group-item, html[data-theme="dark"] .activity-list li { border-color:#2c3a50; }html[data-theme="dark"] .brand-title, html[data-theme="dark"] .dashboard-content .admin-topbar .title-area h1, html[data-theme="dark"] .kpi .num, html[data-theme="dark"] .panel-head h5, html[data-theme="dark"] .job-title, html[data-theme="dark"] .applicant-name, html[data-theme="dark"] .section-heading, html[data-theme="dark"] .quick-action { color:var(--dash-ink); }html[data-theme="dark"] .nav-link-admin { color:#aab7ca; }html[data-theme="dark"] .nav-link-admin.active { color:#9cc1ff; background:#1e3a61; }html[data-theme="dark"] .dashboard-content .header-search, html[data-theme="dark"] .dashboard-content .header-clock, html[data-theme="dark"] .dashboard-content .dark-toggle, html[data-theme="dark"] .dashboard-content .profile-control { background:#172235; border-color:#e0e8f2; }html[data-theme="dark"] .jobs-table { --bs-table-bg:#172235; --bs-table-color:#b9c6d9; --bs-table-border-color:#2c3a50; }html[data-theme="dark"] .jobs-table tbody tr:hover { background:#1c2a40; }
.admin-root .sidebar { background:linear-gradient(155deg,#102c54 0%,#16497f 56%,#0c8879 130%) !important; }
@media (max-width:1199.98px) { .admin-root { padding:16px; gap:0; }.admin-root .sidebar { display:none; }.dashboard-content { padding:2px 0 20px; }.kpi-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } }
@media (max-width:767.98px) { .admin-root { padding:14px; }.dashboard-content .admin-topbar { align-items:flex-start; }.dashboard-hero { align-items:flex-start; flex-direction:column; padding:24px; }.hero-actions { justify-content:flex-start; }.dashboard-hero::before, .dashboard-hero::after { display:none; }.panel-head { padding:17px; }.jobs-table thead th, .jobs-table tbody td { padding-left:16px; padding-right:16px; }.kpi-grid { gap:12px; }.kpi { min-height:112px; padding:16px; } }
@media (max-width:575.98px) { .kpi-grid { grid-template-columns:1fr; }.dashboard-content .admin-topbar .utils { width:100%; justify-content:space-between; }.dashboard-content .profile-control { margin-left:auto; }.hero-actions .btn { flex:1 1 auto; }.application-list .list-group-item { align-items:flex-start !important; }.application-list .text-end { text-align:left !important; } }
</style>

<main class="container-fluid admin-root">
    <aside class="sidebar">
        <div class="brand">
            <span class="brand-mark-sm"><img src="../assets/images/logo.webp" alt="logo" width="40" height="40"></span>
            <div>
                <div class="brand-title">Career Grow Infotech</div>
                <div class="text-soft small">Admin Portal</div>
            </div>
        </div>

        <nav class="mt-3">
            <a href="dashboard.php" class="nav-link-admin active"><i class="bi bi-speedometer2"></i> Dashboard</a>
            <a href="jobs.php" class="nav-link-admin"><i class="bi bi-briefcase"></i> Jobs</a>
            <a href="applicants.php" class="nav-link-admin"><i class="bi bi-people"></i> Applicants</a>
            <a href="candidate-details.php" class="nav-link-admin"><i class="bi bi-person-badge"></i> Candidates</a>
            <a href="contact-messages.php" class="nav-link-admin"><i class="bi bi-envelope-paper"></i> Contact Messages</a>
            <a href="settings.php" class="nav-link-admin"><i class="bi bi-gear"></i> Settings</a>
        </nav>

        <div class="mt-auto pt-3">
            <a href="../index.php" class="d-block nav-link-admin"><i class="bi bi-house"></i> Back to Homepage</a>
            <a href="../logout.php" class="d-block nav-link-admin"><i class="bi bi-box-arrow-right"></i> Logout</a>
        </div>
    </aside>

    <section class="dashboard-content">
        <?php
        $pageH1 = 'Dashboard';
        $pageSubtitle = 'Overview of your recruitment activity';
        require_once __DIR__ . '/../includes/admin-header.php';
        ?>

        <section class="dashboard-hero" aria-label="Dashboard overview">
            <div class="hero-content">
                <div class="hero-kicker">Recruitment command center</div>
                <h2>Welcome back, <?php echo $adminName; ?>.</h2>
                <p>Keep your hiring pipeline moving with a clear view of jobs, applicants and candidate activity.</p>
            </div>
            <div class="hero-actions">
                <a href="add-job.php" class="btn"><i class="bi bi-plus-lg me-1"></i> Post a job</a>
                <a href="applicants.php" class="btn"><i class="bi bi-people me-1"></i> Review applicants</a>
            </div>
        </section>

        <div class="kpi-grid">
            <article class="kpi kpi-jobs"><div class="kpi-top"><span class="kpi-label">Total Jobs</span><span class="kpi-icon"><i class="bi bi-briefcase"></i></span></div><div class="num"><?php echo htmlspecialchars((string)$kpis['jobs'], ENT_QUOTES, 'UTF-8'); ?></div></article>
            <article class="kpi kpi-candidates"><div class="kpi-top"><span class="kpi-label">Candidates</span><span class="kpi-icon"><i class="bi bi-people"></i></span></div><div class="num"><?php echo htmlspecialchars((string)$kpis['candidates'], ENT_QUOTES, 'UTF-8'); ?></div></article>
            <article class="kpi kpi-applications"><div class="kpi-top"><span class="kpi-label">Applications</span><span class="kpi-icon"><i class="bi bi-file-earmark-person"></i></span></div><div class="num"><?php echo htmlspecialchars((string)$kpis['applications'], ENT_QUOTES, 'UTF-8'); ?></div></article>
            <article class="kpi kpi-messages"><div class="kpi-top"><span class="kpi-label">Unread messages</span><span class="kpi-icon"><i class="bi bi-envelope"></i></span></div><div class="num"><?php echo htmlspecialchars((string)$kpis['messages'], ENT_QUOTES, 'UTF-8'); ?></div></article>
        </div>

        <div class="row g-3">
            <div class="col-lg-7">
                    <div class="dashboard-panel">
                        <div class="panel-head"><h5>Recent job postings</h5><a class="panel-link" href="jobs.php">View all <i class="bi bi-arrow-right"></i></a></div>
                        <div class="table-responsive">
                            <?php if (count($recentJobs) === 0): ?>
                                <div class="empty-state">No jobs have been added yet. <a href="add-job.php">Add a new job</a></div>
                            <?php else: ?>
                                <table class="table jobs-table align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Job Title</th>
                                            <th>Location</th>
                                            <th>Status</th>
                                            <th>Posted</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($recentJobs as $job): ?>
                                        <tr>
                                            <td><span class="job-title"><?php echo htmlspecialchars($job['title'], ENT_QUOTES, 'UTF-8'); ?></span><span class="job-location"><i class="bi bi-geo-alt"></i><?php echo htmlspecialchars($job['location'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                            <td><?php echo htmlspecialchars($job['location'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td>
                                                <?php $status = strtolower((string)$job['status']); ?>
                                                <span class="status-badge <?php echo $status === 'active' ? 'status-active' : ($status === 'inactive' ? 'status-inactive' : ''); ?>"><?php echo htmlspecialchars($job['status'], ENT_QUOTES, 'UTF-8'); ?></span>
                                            </td>
                                            <td><?php echo htmlspecialchars($job['created_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td>
                                                <span class="job-actions">
                                                    <a href="../job-details.php?id=<?php echo (int)$job['id']; ?>" class="btn btn-sm btn-outline-primary table-action" aria-label="View <?php echo htmlspecialchars($job['title'], ENT_QUOTES, 'UTF-8'); ?>"><i class="bi bi-eye"></i></a>
                                                    <a href="edit-job.php?id=<?php echo (int)$job['id']; ?>" class="btn btn-sm btn-primary table-action" aria-label="Edit <?php echo htmlspecialchars($job['title'], ENT_QUOTES, 'UTF-8'); ?>"><i class="bi bi-pencil"></i></a>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>
            </div>

            <div class="col-lg-5">
                <div class="dashboard-panel">
                    <div class="panel-head"><h5>Recent applications</h5><a class="panel-link" href="applicants.php">View all <i class="bi bi-arrow-right"></i></a></div>
                    <?php if (count($recentApps) === 0): ?>
                        <div class="empty-state">No applications yet.</div>
                    <?php else: ?>
                        <ul class="list-group list-group-flush application-list">
                            <?php foreach ($recentApps as $app): ?>
                                <li class="list-group-item d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="app-avatar"><i class="bi bi-person-fill"></i></div>
                                        <div>
                                            <div class="applicant-name"><?php echo htmlspecialchars($app['candidate'], ENT_QUOTES, 'UTF-8'); ?></div>
                                            <div class="applicant-role"><?php echo htmlspecialchars($app['job_title'], ENT_QUOTES, 'UTF-8'); ?></div>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div class="app-date"><?php echo htmlspecialchars($app['applied_at'], ENT_QUOTES, 'UTF-8'); ?></div>
                                        <div class="mt-1"><a href="candidate-details.php?id=<?php echo (int)$app['user_id']; ?>" class="app-view">View profile <i class="bi bi-arrow-up-right"></i></a></div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>

                <div class="dashboard-panel activity-panel">
                    <div class="panel-head"><h5>Recruitment activity</h5></div>
                    <?php if (empty($appStatus)): ?>
                        <div class="empty-state">No application activity yet.</div>
                    <?php else: ?>
                        <ul class="list-unstyled mb-0 activity-list">
                            <?php foreach ($appStatus as $status => $cnt): ?>
                                <li class="d-flex justify-content-between py-1"><span><?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?></span><strong><?php echo (int)$cnt; ?></strong></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <section class="quick-actions">
            <h5 class="section-heading">Quick actions</h5>
            <div class="row g-3">
                <div class="col-md-3 col-6">
                    <a href="add-job.php" class="quick-action">
                        <span class="quick-action-icon"><i class="bi bi-plus-lg"></i></span><span><span class="quick-action-title">Add new job</span><span class="quick-action-copy">Create a new job posting</span></span>
                    </a>
                </div>
                <div class="col-md-3 col-6">
                    <a href="jobs.php" class="quick-action">
                        <span class="quick-action-icon"><i class="bi bi-briefcase"></i></span><span><span class="quick-action-title">Manage jobs</span><span class="quick-action-copy">Review every open listing</span></span>
                    </a>
                </div>
                <div class="col-md-3 col-6">
                    <a href="applicants.php" class="quick-action">
                        <span class="quick-action-icon"><i class="bi bi-people"></i></span><span><span class="quick-action-title">Review applicants</span><span class="quick-action-copy">See the latest applications</span></span>
                    </a>
                </div>
                <div class="col-md-3 col-6">
                    <a href="candidate-details.php" class="quick-action">
                        <span class="quick-action-icon"><i class="bi bi-person-badge"></i></span><span><span class="quick-action-title">Candidates</span><span class="quick-action-copy">Manage candidate profiles</span></span>
                    </a>
                </div>
            </div>
        </section>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
