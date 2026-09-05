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
    $kpis['candidates'] = safe_count($conn, 'SELECT COUNT(*) FROM candidate_profiles');
    $kpis['applications'] = safe_count($conn, 'SELECT COUNT(*) FROM applications');
    $kpis['messages'] = safe_count($conn, 'SELECT COUNT(*) FROM contact_messages');
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
/* Dashboard local styles */
.admin-root { min-height:100vh; display:flex; gap:1.5rem; padding:2rem; background: linear-gradient(180deg, rgba(13,110,253,0.02), rgba(255,255,255,0)); }
.sidebar { width:260px; background:var(--cg-white); border:1px solid var(--cg-border); border-radius:.75rem; padding:1rem; box-shadow: 0 12px 30px rgba(15,23,42,0.04); height:calc(100vh - 4rem); position:sticky; top:1rem; }
.sidebar .brand { display:flex; gap:.75rem; align-items:center; padding: .5rem 0 .75rem 0; }
.nav-link-admin { display:flex; align-items:center; gap:.75rem; padding:.55rem .6rem; border-radius:.6rem; color:var(--cg-accent); font-weight:600; }
.nav-link-admin.active, .nav-link-admin:hover { background: rgba(13,110,253,0.04); color:var(--cg-primary); text-decoration:none; }
.topbar { background: transparent; margin-bottom:1rem; display:flex; align-items:center; justify-content:space-between; gap:1rem; }
.kpi-grid { display:grid; grid-template-columns: repeat(4,1fr); gap:1rem; margin-bottom:1rem; }
.kpi { background:var(--cg-white); border:1px solid var(--cg-border); border-radius:.75rem; padding:1rem; box-shadow: 0 10px 30px rgba(15,23,42,0.04); }
.kpi .num { font-size:1.6rem; font-weight:800; }
.card { border-radius:.75rem; }
.table-responsive { overflow:auto; }
@media (max-width: 991.98px) { .sidebar { position:static; width:100%; height:auto; } .kpi-grid { grid-template-columns: repeat(2,1fr); } }
@media (max-width: 575.98px) { .kpi-grid { grid-template-columns: 1fr; } }
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
            <a href="settings.php" class="nav-link-admin"><i class="bi bi-gear"></i> Settings</a>
        </nav>

        <div class="mt-auto pt-3">
            <a href="../index.php" class="d-block nav-link-admin"><i class="bi bi-house"></i> Back to Website</a>
            <a href="../logout.php" class="d-block nav-link-admin"><i class="bi bi-box-arrow-right"></i> Logout</a>
        </div>
    </aside>

    <section style="flex:1 1 auto;">
        <div class="topbar">
            <div>
                <h3 class="mb-0">Dashboard</h3>
                <div class="text-soft small">Overview of your recruitment activity</div>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="text-end">
                    <div class="fw-semibold">Administrator</div>
                    <div class="text-soft small">Admin</div>
                </div>
                <div class="brand-mark-sm"><i class="bi bi-person-circle fs-2 text-primary"></i></div>
            </div>
        </div>

        <div class="welcome card mb-3 p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-1">Welcome back, Administrator</h4>
                    <p class="text-soft mb-0">Here's an overview of your recruitment platform.</p>
                </div>
                <div class="text-primary">
                    <i class="bi bi-graph-up-arrow fs-3"></i>
                </div>
            </div>
        </div>

        <div class="kpi-grid mb-3">
            <div class="kpi">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-soft small">Total Jobs</div>
                        <div class="num"><?php echo htmlspecialchars((string)$kpis['jobs'], ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                    <i class="bi bi-briefcase fs-2 text-primary"></i>
                </div>
            </div>

            <div class="kpi">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-soft small">Total Candidates</div>
                        <div class="num"><?php echo htmlspecialchars((string)$kpis['candidates'], ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                    <i class="bi bi-people fs-2 text-primary"></i>
                </div>
            </div>

            <div class="kpi">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-soft small">Total Applications</div>
                        <div class="num"><?php echo htmlspecialchars((string)$kpis['applications'], ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                    <i class="bi bi-file-earmark-person fs-2 text-primary"></i>
                </div>
            </div>

            <div class="kpi">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-soft small">Contact Messages</div>
                        <div class="num"><?php echo htmlspecialchars((string)$kpis['messages'], ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                    <i class="bi bi-envelope fs-2 text-primary"></i>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-7">
                <div class="card p-3">
                    <h5 class="mb-3">Recent Jobs</h5>
                    <div class="table-responsive">
                        <?php if (count($recentJobs) === 0): ?>
                            <div class="text-muted">No jobs have been added yet. <a href="add-job.php">Add New Job</a></div>
                        <?php else: ?>
                            <table class="table table-borderless align-middle mb-0">
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
                                        <td><?php echo htmlspecialchars($job['title'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($job['location'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($job['status'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($job['created_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <a href="../job-details.php?id=<?php echo (int)$job['id']; ?>" class="btn btn-sm btn-outline-secondary">View</a>
                                            <a href="edit-job.php?id=<?php echo (int)$job['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
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
                <div class="card p-3 mb-3">
                    <h5 class="mb-3">Recent Applications</h5>
                    <?php if (count($recentApps) === 0): ?>
                        <div class="text-muted">No applications yet.</div>
                    <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($recentApps as $app): ?>
                                <li class="list-group-item d-flex align-items-center justify-content-between">
                                    <div>
                                        <div class="fw-semibold"><?php echo htmlspecialchars($app['candidate'], ENT_QUOTES, 'UTF-8'); ?></div>
                                        <div class="text-soft small"><?php echo htmlspecialchars($app['job_title'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    </div>
                                    <div class="text-end">
                                        <div class="small text-soft"><?php echo htmlspecialchars($app['applied_at'], ENT_QUOTES, 'UTF-8'); ?></div>
                                        <div class="mt-1"><a href="candidate-details.php?id=<?php echo (int)$app['user_id']; ?>" class="btn btn-sm btn-outline-secondary">View</a></div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>

                <div class="card p-3">
                    <h5 class="mb-3">Recruitment Activity</h5>
                    <?php if (empty($appStatus)): ?>
                        <div class="text-muted">No application activity yet.</div>
                    <?php else: ?>
                        <ul class="list-unstyled mb-0">
                            <?php foreach ($appStatus as $status => $cnt): ?>
                                <li class="d-flex justify-content-between py-1"><span><?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?></span><strong><?php echo (int)$cnt; ?></strong></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="mt-3">
            <h5>Quick Actions</h5>
            <div class="row g-2 mt-2">
                <div class="col-md-3 col-6">
                    <a href="add-job.php" class="btn btn-light w-100 text-start p-3 border rounded">
                        <div class="fw-semibold">Add New Job</div>
                        <div class="small text-soft">Create a new job posting</div>
                    </a>
                </div>
                <div class="col-md-3 col-6">
                    <a href="jobs.php" class="btn btn-light w-100 text-start p-3 border rounded">
                        <div class="fw-semibold">View Jobs</div>
                        <div class="small text-soft">Manage job listings</div>
                    </a>
                </div>
                <div class="col-md-3 col-6">
                    <a href="applicants.php" class="btn btn-light w-100 text-start p-3 border rounded">
                        <div class="fw-semibold">View Applicants</div>
                        <div class="small text-soft">Review applications</div>
                    </a>
                </div>
                <div class="col-md-3 col-6">
                    <a href="candidate-details.php" class="btn btn-light w-100 text-start p-3 border rounded">
                        <div class="fw-semibold">Candidate Management</div>
                        <div class="small text-soft">Manage candidate profiles</div>
                    </a>
                </div>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>
<?php require_once __DIR__ . '/../includes/navbar.php'; ?>

<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h1 class="mb-3">Admin Dashboard</h1>
            <p class="text-muted">This is a placeholder admin dashboard page. Business logic is not implemented yet.</p>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
