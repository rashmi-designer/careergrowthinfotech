<?php

declare(strict_types=1);
session_start();

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/admin-auth.php';
require_admin();

$pageTitle = 'Candidate Details - Admin';
require_once __DIR__ . '/../includes/header.php';

$conn = getDbConnection();

$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$appId = isset($_GET['application_id']) ? (int)$_GET['application_id'] : 0;

$user = null;
$application = null;
$resumeFile = null;

if ($appId > 0) {
    $appStmt = $conn->prepare('SELECT a.id AS application_id, a.user_id, a.job_id, a.resume AS application_resume, a.status, a.applied_at, u.id AS user_id_value, u.name, u.email, u.phone, cp.skills, cp.location AS profile_location, cp.qualification, cp.experience, cp.resume AS profile_resume, j.title AS job_title, j.location AS job_location, j.job_type, j.status AS job_status FROM applications a LEFT JOIN users u ON u.id = a.user_id LEFT JOIN candidate_profiles cp ON cp.user_id = u.id LEFT JOIN jobs j ON j.id = a.job_id WHERE a.id = ? LIMIT 1');
    if ($appStmt) {
        $appStmt->bind_param('i', $appId);
        $appStmt->execute();
        $appResult = $appStmt->get_result();
        $application = $appResult->fetch_assoc();
        $appStmt->close();
        if ($application) {
            $userId = (int)($application['user_id_value'] ?? $application['user_id'] ?? 0);
        }
    }
}

if ($userId > 0 && empty($application)) {
    $userStmt = $conn->prepare('SELECT u.id, u.name, u.email, u.phone, cp.skills, cp.location, cp.qualification, cp.experience, cp.resume, cp.created_at FROM users u LEFT JOIN candidate_profiles cp ON cp.user_id = u.id WHERE u.id = ? AND u.role = ? LIMIT 1');
    if ($userStmt) {
        $candidateRole = 'candidate';
        $userStmt->bind_param('is', $userId, $candidateRole);
        $userStmt->execute();
        $user = $userStmt->get_result()->fetch_assoc();
        $userStmt->close();
    }

    if ($user) {
        $appStmt = $conn->prepare('SELECT a.id AS application_id, a.user_id, a.job_id, a.resume AS application_resume, a.status, a.applied_at, j.title AS job_title, j.location AS job_location, j.job_type, j.status AS job_status FROM applications a LEFT JOIN jobs j ON j.id = a.job_id WHERE a.user_id = ? ORDER BY a.applied_at DESC LIMIT 1');
        if ($appStmt) {
            $appStmt->bind_param('i', $userId);
            $appStmt->execute();
            $application = $appStmt->get_result()->fetch_assoc();
            $appStmt->close();
        }
    }
} elseif ($userId > 0 && empty($user)) {
    $userStmt = $conn->prepare('SELECT u.id, u.name, u.email, u.phone, cp.skills, cp.location, cp.qualification, cp.experience, cp.resume, cp.created_at FROM users u LEFT JOIN candidate_profiles cp ON cp.user_id = u.id WHERE u.id = ? AND u.role = ? LIMIT 1');
    if ($userStmt) {
        $candidateRole = 'candidate';
        $userStmt->bind_param('is', $userId, $candidateRole);
        $userStmt->execute();
        $user = $userStmt->get_result()->fetch_assoc();
        $userStmt->close();
    }
}

if (!empty($application)) {
    $resumeFile = !empty($application['application_resume']) ? $application['application_resume'] : (!empty($application['profile_resume']) ? $application['profile_resume'] : null);
}

if (empty($user) && $userId > 0) {
    $notFound = true;
} else {
    $notFound = false;
}

$conn->close();

function safe_resume_path(?string $fileName): ?string {
    if ($fileName === null || trim($fileName) === '') {
        return null;
    }

    $baseName = basename($fileName);
    if ($baseName === '' || $baseName !== $fileName) {
        return null;
    }

    $fullPath = __DIR__ . '/../uploads/resumes/' . $baseName;
    if (!is_file($fullPath)) {
        return null;
    }

    return '../uploads/resumes/' . rawurlencode($baseName);
}

function format_status_badge(string $status): string {
    $status = trim($status);
    if ($status === '') {
        return 'Unknown';
    }

    $lower = strtolower($status);
    if (str_contains($lower, 'new') || str_contains($lower, 'applied')) {
        return 'pending';
    }
    if (str_contains($lower, 'shortlist')) {
        return 'shortlisted';
    }
    if (str_contains($lower, 'select') || str_contains($lower, 'hire') || str_contains($lower, 'offer')) {
        return 'selected';
    }
    if (str_contains($lower, 'reject')) {
        return 'rejected';
    }
    return 'default-state';
}

$profileResumeLink = safe_resume_path($resumeFile ?? null);
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
    padding: 1.1rem 1.4rem;
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

.profile-card {
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 1rem;
    padding: 1.15rem 1.25rem;
    margin-bottom: 1rem;
}

.profile-avatar {
    width: 72px;
    height: 72px;
    border-radius: 50%;
    background: linear-gradient(135deg, rgba(13,110,253,0.12), rgba(13,110,253,0.04));
    color: var(--cg-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    border: 1px solid rgba(13,110,253,0.12);
}

.profile-name {
    font-size: clamp(1.3rem, 2vw, 1.8rem);
    font-weight: 800;
    margin: 0;
    color: var(--cg-accent);
}

.profile-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.7rem 1rem;
    color: var(--cg-muted);
    font-size: 0.9rem;
    margin-top: 0.35rem;
}

.summary-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
    margin-bottom: 1rem;
}

.summary-box {
    padding: 1rem 1.1rem;
    background: var(--cg-white);
    border: 1px solid var(--cg-border);
    border-radius: 1rem;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
}

.summary-box label {
    display: block;
    margin-bottom: 0.35rem;
    color: var(--cg-muted);
    font-size: 0.78rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.summary-box h4 {
    margin: 0;
    color: var(--cg-accent);
    font-size: 1.1rem;
}

.badge-status {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.5rem 0.7rem;
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

.info-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.info-card {
    padding: 1rem 1.1rem;
    border: 1px solid var(--cg-border);
    border-radius: 1rem;
    background: var(--cg-white);
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
}

.info-card h3 {
    font-size: 1rem;
    margin: 0 0 1rem;
    color: var(--cg-accent);
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.detail-list {
    display: grid;
    gap: 0.75rem;
}

.detail-item {
    display: grid;
    gap: 0.15rem;
}

.detail-item label {
    font-size: 0.76rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--cg-muted);
}

.detail-item div {
    color: var(--cg-accent);
    font-weight: 600;
}

.resume-box {
    border: 1px dashed rgba(13,110,253,0.22);
    border-radius: 1rem;
    padding: 1rem;
    background: rgba(13,110,253,0.02);
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

@media (max-width: 991.98px) {
    .admin-root {
        flex-direction: column;
    }

    .sidebar {
        width: 100%;
        position: static;
    }

    .summary-grid,
    .info-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 575.98px) {
    .admin-root {
        padding: 1rem;
    }

    .topbar {
        flex-wrap: wrap;
    }

    .profile-card {
        grid-template-columns: 1fr;
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
                <h1>Candidate Details</h1>
                <div class="topbar-subtitle">Application and profile overview</div>
            </div>
            <div class="user-pill">
                <div class="avatar"><i class="bi bi-person-circle"></i></div>
                <div>
                    <div class="fw-semibold">Administrator</div>
                    <div class="text-muted small">Admin</div>
                </div>
            </div>
        </div>

        <?php if ($notFound || empty($user)): ?>
            <div class="card-panel header-panel">
                <div class="page-kicker"><i class="bi bi-person-x"></i> Admin / Applicants / Candidate Details</div>
                <div class="empty-box">
                    <div class="icon"><i class="bi bi-person-x"></i></div>
                    <h4>Candidate not found</h4>
                    <p class="mb-3">The requested candidate or application could not be found.</p>
                    <a href="applicants.php" class="btn btn-primary">Back to Applicants</a>
                </div>
            </div>
        <?php else: ?>
            <div class="card-panel header-panel">
                <div class="page-kicker"><i class="bi bi-person-lines-fill"></i> Admin / Applicants / Candidate Details</div>
                <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
                    <div>
                        <h2>Candidate Details</h2>
                        <p>Professional overview of the candidate and their application.</p>
                    </div>
                    <a href="applicants.php" class="btn btn-outline-secondary">Back to Applicants</a>
                </div>
            </div>

            <div class="card-panel profile-card">
                <div class="profile-avatar"><?php echo htmlspecialchars(strtoupper(substr((string)($user['name'] ?? 'C'), 0, 1)), ENT_QUOTES, 'UTF-8'); ?></div>
                <div>
                    <h3 class="profile-name"><?php echo htmlspecialchars((string)($user['name'] ?? 'Candidate'), ENT_QUOTES, 'UTF-8'); ?></h3>
                    <div class="profile-meta">
                        <?php if (!empty($user['email'])): ?><span><i class="bi bi-envelope"></i> <?php echo htmlspecialchars((string)$user['email'], ENT_QUOTES, 'UTF-8'); ?></span><?php endif; ?>
                        <?php if (!empty($user['phone'])): ?><span><i class="bi bi-telephone"></i> <?php echo htmlspecialchars((string)$user['phone'], ENT_QUOTES, 'UTF-8'); ?></span><?php endif; ?>
                        <?php if (!empty($user['location'])): ?><span><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars((string)$user['location'], ENT_QUOTES, 'UTF-8'); ?></span><?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="summary-grid">
                <div class="summary-box">
                    <label>Applied Job</label>
                    <h4><?php echo htmlspecialchars((string)($application['job_title'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></h4>
                </div>
                <div class="summary-box">
                    <label>Application Status</label>
                    <div>
                        <?php $statusValue = trim((string)($application['status'] ?? '')); ?>
                        <?php $badgeType = format_status_badge($statusValue); ?>
                        <span class="badge-status <?php echo htmlspecialchars($badgeType, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($statusValue !== '' ? $statusValue : 'Unknown', ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                </div>
            </div>

            <div class="info-grid">
                <div class="info-card">
                    <h3>Personal Information</h3>
                    <div class="detail-list">
                        <div class="detail-item">
                            <label>Full Name</label>
                            <div><?php echo htmlspecialchars((string)($user['name'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                        <div class="detail-item">
                            <label>Email</label>
                            <div><?php echo htmlspecialchars((string)($user['email'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                        <div class="detail-item">
                            <label>Phone</label>
                            <div><?php echo htmlspecialchars((string)($user['phone'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                        <div class="detail-item">
                            <label>Location</label>
                            <div><?php echo htmlspecialchars((string)($user['location'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                    </div>
                </div>

                <div class="info-card">
                    <h3>Application Details</h3>
                    <div class="detail-list">
                        <div class="detail-item">
                            <label>Applied Job</label>
                            <div><?php echo htmlspecialchars((string)($application['job_title'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                        <div class="detail-item">
                            <label>Job Location</label>
                            <div><?php echo htmlspecialchars((string)($application['job_location'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                        <div class="detail-item">
                            <label>Applied Date</label>
                            <div><?php echo htmlspecialchars((string)($application['applied_at'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                        <div class="detail-item">
                            <label>Current Status</label>
                            <div><?php echo htmlspecialchars((string)($application['status'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="info-grid">
                <div class="info-card">
                    <h3>Professional Information</h3>
                    <div class="detail-list">
                        <div class="detail-item">
                            <label>Qualification</label>
                            <div><?php echo htmlspecialchars((string)($user['qualification'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                        <div class="detail-item">
                            <label>Experience</label>
                            <div><?php echo htmlspecialchars((string)($user['experience'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                        <div class="detail-item">
                            <label>Skills</label>
                            <div><?php echo htmlspecialchars((string)($user['skills'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                    </div>
                </div>

                <div class="info-card">
                    <h3>Resume</h3>
                    <?php if ($profileResumeLink): ?>
                        <div class="resume-box">
                            <p class="mb-3">Candidate resume is available for review.</p>
                            <a href="<?php echo htmlspecialchars($profileResumeLink, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-primary">View Resume</a>
                        </div>
                    <?php else: ?>
                        <div class="resume-box text-muted">Resume not available.</div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
