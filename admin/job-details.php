<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/admin-auth.php';
require_admin();

$pageTitle = 'Job Details - Admin';
require_once __DIR__ . '/../includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: jobs.php?error=invalid_job');
    exit;
}

$conn = getDbConnection();
$job = null;
$stmt = $conn->prepare('SELECT id, title, company, description, skills_required, location, job_type, experience_level, salary_min, salary_max, openings, last_date, status, created_at, updated_at FROM jobs WHERE id = ? LIMIT 1');
if ($stmt) {
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $job = $result->fetch_assoc();
    $stmt->close();
} else {
    $job = null;
}
$conn->close();

if (!$job) {
    header('Location: jobs.php?error=job_not_found');
    exit;
}

$companyDisplay = trim((string)($job['company'] ?? ''));
if ($companyDisplay === '') {
    $companyDisplay = 'Career Grow Infotech';
}

$status = strtolower((string)($job['status'] ?? 'inactive'));
$salaryMin = trim((string)($job['salary_min'] ?? ''));
$salaryMax = trim((string)($job['salary_max'] ?? ''));
$salaryDisplay = 'Not disclosed';
if ($salaryMin !== '' || $salaryMax !== '') {
    $parts = [];
    if ($salaryMin !== '') {
        $parts[] = '₹' . number_format((float)$salaryMin, 0, '.', ',');
    }
    if ($salaryMax !== '') {
        $parts[] = '₹' . number_format((float)$salaryMax, 0, '.', ',');
    }
    $salaryDisplay = $parts !== [] ? implode(' - ', $parts) : 'Not disclosed';
}

$lastDate = trim((string)($job['last_date'] ?? ''));
$skillsDisplay = trim((string)($job['skills_required'] ?? ''));
$description = trim((string)($job['description'] ?? ''));

function admin_badge(string $status): string
{
    $status = strtolower($status);
    return $status === 'active' ? 'active' : 'inactive';
}
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
    .nav-link-admin:focus,
    .nav-link-admin.active {
        color: var(--cg-primary);
        background: rgba(13,110,253,0.04);
        text-decoration: none;
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
        font-size: clamp(1.5rem, 2.4vw, 2.1rem);
        letter-spacing: -0.03em;
    }
    .header-panel p {
        color: var(--cg-muted);
        margin-top: 0.35rem;
    }
    .job-detail-shell {
        padding: 1.2rem;
    }
    .job-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 1rem;
    }
    .job-header h3 {
        margin: 0;
        color: var(--cg-accent);
        font-weight: 700;
    }
    .job-company {
        color: var(--cg-primary);
        font-weight: 700;
        margin-top: 0.35rem;
    }
    .job-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.6rem 1rem;
        color: var(--cg-muted);
        font-size: 0.9rem;
        margin-top: 0.8rem;
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
    .detail-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }
    .detail-card {
        border: 1px solid var(--cg-border);
        background: rgba(13,110,253,0.01);
        border-radius: 0.9rem;
        padding: 1rem;
    }
    .detail-card h5 {
        font-size: 0.8rem;
        color: var(--cg-muted);
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }
    .detail-card strong {
        color: var(--cg-accent);
        font-size: 1rem;
    }
    .content-box {
        margin-top: 1rem;
        border: 1px solid var(--cg-border);
        border-radius: 0.9rem;
        padding: 1rem;
        background: rgba(13,110,253,0.01);
    }
    .content-box h5 {
        font-size: 0.8rem;
        color: var(--cg-muted);
        text-transform: uppercase;
        letter-spacing: 0.06em;
        margin-bottom: 0.7rem;
    }
    .content-box p,
    .content-box li {
        color: var(--cg-text);
        line-height: 1.8;
    }
    .content-box ul {
        margin-bottom: 0;
        padding-left: 1.1rem;
    }
    .action-row {
        display: flex;
        justify-content: flex-start;
        gap: 0.75rem;
        flex-wrap: wrap;
        margin-top: 1rem;
    }
    @media (max-width: 991.98px) {
        .admin-root { flex-direction: column; }
        .sidebar { width: 100%; position: static; }
    }
    @media (max-width: 767.98px) {
        .detail-grid { grid-template-columns: 1fr; }
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
            <a href="jobs.php" class="nav-link-admin active"><i class="bi bi-briefcase"></i> Jobs</a>
            <a href="applicants.php" class="nav-link-admin"><i class="bi bi-people"></i> Applicants</a>
            <a href="candidate-details.php" class="nav-link-admin"><i class="bi bi-person-badge"></i> Candidates</a>
            <a href="contact-messages.php" class="nav-link-admin"><i class="bi bi-envelope-paper"></i> Contact Messages</a>
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
                <h1>Job Details</h1>
                <div class="topbar-subtitle">Administrative view</div>
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
            <div class="page-kicker"><i class="bi bi-diagram-3"></i> Admin / Jobs / Details</div>
            <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
                <div>
                    <h2>Job Overview</h2>
                    <p>Review the full job record and manage the listing from the admin portal.</p>
                </div>
                <div class="action-row">
                    <a href="jobs.php" class="btn btn-outline-secondary">Back to Jobs</a>
                    <a href="edit-job.php?id=<?php echo (int)$job['id']; ?>" class="btn btn-primary">Edit Job</a>
                </div>
            </div>
        </div>

        <div class="card-panel job-detail-shell">
            <div class="job-header">
                <div>
                    <h3><?php echo htmlspecialchars((string)($job['title'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></h3>
                    <div class="job-company"><?php echo htmlspecialchars($companyDisplay, ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="job-meta">
                        <span><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars((string)($job['location'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></span>
                        <span><i class="bi bi-briefcase"></i> <?php echo htmlspecialchars((string)($job['job_type'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></span>
                        <span><i class="bi bi-people"></i> <?php echo htmlspecialchars((string)($job['openings'] ?? '0'), ENT_QUOTES, 'UTF-8'); ?> openings</span>
                    </div>
                </div>
                <span class="badge-status <?php echo admin_badge($status); ?>"><?php echo htmlspecialchars(ucfirst($status), ENT_QUOTES, 'UTF-8'); ?></span>
            </div>

            <div class="detail-grid">
                <div class="detail-card">
                    <h5>Location</h5>
                    <strong><?php echo htmlspecialchars((string)($job['location'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></strong>
                </div>
                <div class="detail-card">
                    <h5>Experience Level</h5>
                    <strong><?php echo htmlspecialchars((string)($job['experience_level'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></strong>
                </div>
                <div class="detail-card">
                    <h5>Salary Range</h5>
                    <strong><?php echo htmlspecialchars($salaryDisplay, ENT_QUOTES, 'UTF-8'); ?></strong>
                </div>
                <div class="detail-card">
                    <h5>Application Deadline</h5>
                    <strong><?php echo htmlspecialchars($lastDate !== '' ? date('d M Y', strtotime($lastDate)) : 'N/A', ENT_QUOTES, 'UTF-8'); ?></strong>
                </div>
                <div class="detail-card">
                    <h5>Created At</h5>
                    <strong><?php echo htmlspecialchars((string)($job['created_at'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></strong>
                </div>
                <div class="detail-card">
                    <h5>Updated At</h5>
                    <strong><?php echo htmlspecialchars((string)($job['updated_at'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></strong>
                </div>
            </div>

            <div class="content-box">
                <h5>Job Description</h5>
                <?php echo nl2br(htmlspecialchars($description !== '' ? $description : 'No description provided.', ENT_QUOTES, 'UTF-8')); ?>
            </div>

            <div class="content-box">
                <h5>Skills / Requirements</h5>
                <?php if ($skillsDisplay !== ''): ?>
                    <ul>
                        <?php foreach (preg_split('/\r\n|\n|,/', $skillsDisplay) as $skill): ?>
                            <?php $skill = trim((string)$skill); if ($skill === '') continue; ?>
                            <li><?php echo htmlspecialchars($skill, ENT_QUOTES, 'UTF-8'); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>No specific skills listed.</p>
                <?php endif; ?>
            </div>

            <div class="action-row">
                <a href="jobs.php" class="btn btn-outline-secondary">Back to Jobs</a>
                <a href="edit-job.php?id=<?php echo (int)$job['id']; ?>" class="btn btn-primary">Edit Job</a>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
