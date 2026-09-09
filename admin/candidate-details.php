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

// Handle admin status update POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $postedAppId = isset($_POST['application_id']) ? (int)$_POST['application_id'] : 0;
    $postedStatus = isset($_POST['status']) ? trim((string)$_POST['status']) : '';

    // Define allowed statuses based on existing project usage
    $allowedStatuses = [
        'New Applied',
        'Reviewed',
        'Shortlisted',
        'Accepted',
        'Rejected',
    ];

    if ($postedAppId > 0 && in_array($postedStatus, $allowedStatuses, true)) {
        $updateStmt = $conn->prepare('UPDATE applications SET status = ? WHERE id = ? LIMIT 1');
        if ($updateStmt) {
            $updateStmt->bind_param('si', $postedStatus, $postedAppId);
            $updateStmt->execute();
            $updateStmt->close();
        } else {
            error_log('candidate-details status update prepare failed: ' . $conn->error);
        }
    }

    // Redirect back to avoid form resubmission and to show updated value
    $redirectUrl = 'candidate-details.php?id=' . urlencode((string)$userId);
    // Prefer redirecting to the application that was updated
    if ($postedAppId > 0) {
        $redirectUrl .= '&application_id=' . urlencode((string)$postedAppId);
    } elseif ($appId > 0) {
        $redirectUrl .= '&application_id=' . urlencode((string)$appId);
    }
    header('Location: ' . $redirectUrl);
    exit;
}

$user = null;
$application = null;
$resumeFile = null;
$candidateList = [];
$candidateApplications = [];
$showCandidateList = ($userId === 0 && $appId === 0);

if ($showCandidateList) {
    $candidatesStmt = $conn->prepare('SELECT u.id, u.name, u.email, u.phone, cp.location, cp.qualification, cp.experience, COUNT(a.id) AS application_count FROM users u LEFT JOIN candidate_profiles cp ON cp.user_id = u.id LEFT JOIN applications a ON a.user_id = u.id WHERE u.role = ? GROUP BY u.id, u.name, u.email, u.phone, cp.location, cp.qualification, cp.experience ORDER BY u.name ASC');
    if ($candidatesStmt) {
        $candidateRole = 'candidate';
        $candidatesStmt->bind_param('s', $candidateRole);
        $candidatesStmt->execute();
        $candidateListResult = $candidatesStmt->get_result();
        while ($row = $candidateListResult->fetch_assoc()) {
            $candidateList[] = $row;
        }
        $candidatesStmt->close();
    }
}

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

if ($userId > 0 && empty($user)) {
    $userStmt = $conn->prepare('SELECT u.id, u.name, u.email, u.phone, cp.skills, cp.location, cp.qualification, cp.experience, cp.resume, cp.created_at FROM users u LEFT JOIN candidate_profiles cp ON cp.user_id = u.id WHERE u.id = ? AND u.role = ? LIMIT 1');
    if ($userStmt) {
        $candidateRole = 'candidate';
        $userStmt->bind_param('is', $userId, $candidateRole);
        $userStmt->execute();
        $user = $userStmt->get_result()->fetch_assoc();
        $userStmt->close();
    }
}

if ($userId > 0 && $appId === 0 && $user) {
    $appsStmt = $conn->prepare('SELECT a.id AS application_id, a.user_id, a.job_id, a.resume AS application_resume, a.status, a.applied_at, j.title AS job_title, j.company AS job_company, j.location AS job_location, j.job_type, j.status AS job_status FROM applications a LEFT JOIN jobs j ON j.id = a.job_id WHERE a.user_id = ? ORDER BY a.applied_at DESC');
    if ($appsStmt) {
        $appsStmt->bind_param('i', $userId);
        $appsStmt->execute();
        $appsResult = $appsStmt->get_result();
        while ($row = $appsResult->fetch_assoc()) {
            $candidateApplications[] = $row;
        }
        $appsStmt->close();
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
    if ($fileName === null) {
        return null;
    }

    $rawPath = trim((string)$fileName);
    if ($rawPath === '') {
        return null;
    }

    $normalized = str_replace('\\', '/', $rawPath);
    if (strpos($normalized, '..') !== false) {
        return null;
    }

    $relativePath = $normalized;
    if (preg_match('#^uploads/resumes/#', $relativePath) !== 1) {
        $relativePath = 'uploads/resumes/' . basename($relativePath);
    }

    $fullPath = __DIR__ . '/../' . $relativePath;
    if (!is_file($fullPath)) {
        return null;
    }

    return '../' . $relativePath;
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
$candidateProfileResumeLink = safe_resume_path((string)($user['resume'] ?? ''));
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
            <a href="applicants.php" class="nav-link-admin"><i class="bi bi-people"></i> Applicants</a>
            <a href="candidate-details.php" class="nav-link-admin active"><i class="bi bi-person-badge"></i> Candidates</a>
            <a href="contact-messages.php" class="nav-link-admin"><i class="bi bi-envelope-paper"></i> Contact Messages</a>
            <a href="settings.php" class="nav-link-admin"><i class="bi bi-gear"></i> Settings</a>
        </nav>

        <div class="sidebar-footer">
            <a href="../index.php" class="nav-link-admin"><i class="bi bi-house"></i> Back to Website</a>
            <a href="../logout.php" class="nav-link-admin"><i class="bi bi-box-arrow-right"></i> Logout</a>
        </div>
    </aside>

    <section class="main-panel">
        <?php
        $pageH1 = 'Candidate Details';
        $pageSubtitle = 'Application and profile overview';
        require_once __DIR__ . '/../includes/admin-header.php';
        ?>

        <?php if ($showCandidateList): ?>
            <div class="card-panel header-panel">
                <div class="page-kicker"><i class="bi bi-people"></i> Admin / Candidates</div>
                <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
                    <div>
                        <h2>Candidate Directory</h2>
                        <p>Review registered candidate profiles and their application activity.</p>
                    </div>
                    <a href="applicants.php" class="btn btn-outline-secondary">View Applicants</a>
                </div>
            </div>

            <div class="card-panel" style="padding: 1rem;">
                <?php if (empty($candidateList)): ?>
                    <div class="empty-box">
                        <div class="icon"><i class="bi bi-people"></i></div>
                        <h4>No candidates found</h4>
                        <p class="mb-3">No candidate accounts are currently registered in the system.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Candidate</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Location</th>
                                    <th>Applications</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($candidateList as $candidate): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-semibold"><?php echo htmlspecialchars((string)($candidate['name'] ?? 'Candidate'), ENT_QUOTES, 'UTF-8'); ?></div>
                                            <div class="text-muted small"><?php echo htmlspecialchars((string)($candidate['qualification'] ?? 'Profile pending'), ENT_QUOTES, 'UTF-8'); ?></div>
                                        </td>
                                        <td><?php echo htmlspecialchars((string)($candidate['email'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string)($candidate['phone'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string)($candidate['location'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo (int)($candidate['application_count'] ?? 0); ?></td>
                                        <td>
                                            <a href="candidate-details.php?id=<?php echo (int)($candidate['id'] ?? 0); ?>" class="btn btn-sm btn-primary">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        <?php elseif ($notFound || empty($user)): ?>
            <div class="card-panel header-panel">
                <div class="page-kicker"><i class="bi bi-person-x"></i> Admin / Candidates / Candidate Details</div>
                <div class="empty-box">
                    <div class="icon"><i class="bi bi-person-x"></i></div>
                    <h4>Candidate not found</h4>
                    <p class="mb-3">The requested candidate could not be found.</p>
                    <a href="candidate-details.php" class="btn btn-primary">Back to Candidates</a>
                </div>
            </div>
        <?php else: ?>
            <?php if ($appId > 0): ?>
                <div class="card-panel header-panel">
                    <div class="page-kicker"><i class="bi bi-person-lines-fill"></i> Admin / Applicants / Candidate Details</div>
                    <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
                        <div>
                            <h2>Candidate Details</h2>
                            <p>Professional overview of the selected application.</p>
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
                            <div style="display:flex;gap:0.75rem;align-items:center;flex-wrap:wrap;">
                                <span class="badge-status <?php echo htmlspecialchars($badgeType, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($statusValue !== '' ? $statusValue : 'Unknown', ENT_QUOTES, 'UTF-8'); ?></span>

                                <form method="post" style="display:inline-flex;gap:0.5rem;align-items:center;">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="application_id" value="<?php echo (int)($application['application_id'] ?? $appId); ?>">
                                    <select name="status" class="form-select form-select-sm" style="min-width:170px;">
                                        <?php
                                        $allowedStatuses = ['New Applied','Reviewed','Shortlisted','Accepted','Rejected'];
                                        foreach ($allowedStatuses as $opt):
                                            $sel = ($opt === $statusValue) ? 'selected' : '';
                                        ?>
                                            <option value="<?php echo htmlspecialchars($opt, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $sel; ?>><?php echo htmlspecialchars($opt, ENT_QUOTES, 'UTF-8'); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-primary">Update</button>
                                </form>
                            </div>
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
            <?php else: ?>
                <div class="card-panel header-panel">
                    <div class="page-kicker"><i class="bi bi-person-lines-fill"></i> Admin / Candidates / Candidate Details</div>
                    <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
                        <div>
                            <h2>Candidate Details</h2>
                            <p>Profile overview and complete application history.</p>
                        </div>
                        <a href="candidate-details.php" class="btn btn-outline-secondary">Back to Candidates</a>
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
                        <label>Total Applications</label>
                        <h4><?php echo count($candidateApplications); ?></h4>
                    </div>
                    <div class="summary-box">
                        <label>Profile Status</label>
                        <h4><?php echo !empty($user['resume']) ? 'Resume Available' : 'Resume Not Uploaded'; ?></h4>
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
                </div>

                <div class="info-grid">
                    <div class="info-card" style="grid-column: 1 / -1;">
                        <h3>Resume</h3>
                        <?php if ($candidateProfileResumeLink): ?>
                            <div class="resume-box">
                                <p class="mb-3">Candidate resume is available for review.</p>
                                <a href="<?php echo htmlspecialchars($candidateProfileResumeLink, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-primary">View Resume</a>
                            </div>
                        <?php else: ?>
                            <div class="resume-box text-muted">Resume not available.</div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card-panel header-panel" style="margin-top: 1rem;">
                    <div class="page-kicker"><i class="bi bi-journal-text"></i> Admin / Candidates / Application History</div>
                    <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
                        <div>
                            <h2>Applications</h2>
                            <p>All applications submitted by this candidate.</p>
                        </div>
                    </div>
                </div>

                <?php if (empty($candidateApplications)): ?>
                    <div class="card-panel empty-box">
                        <div class="icon"><i class="bi bi-file-earmark-text"></i></div>
                        <h4>No applications found</h4>
                        <p class="mb-3">This candidate has not applied to any jobs yet.</p>
                    </div>
                <?php else: ?>
                    <div class="card-panel" style="padding: 1rem;">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Job Title</th>
                                        <th>Company</th>
                                        <th>Location</th>
                                        <th>Applied</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($candidateApplications as $candidateApplication): ?>
                                        <?php
                                            $candidateAppStatus = trim((string)($candidateApplication['status'] ?? ''));
                                            $candidateAppBadge = format_status_badge($candidateAppStatus);
                                            $jobCompany = trim((string)($candidateApplication['job_company'] ?? ''));
                                            $displayCompany = $jobCompany !== '' ? $jobCompany : 'Career Grow Infotech';
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars((string)($candidateApplication['job_title'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars($displayCompany, ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars((string)($candidateApplication['job_location'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars((string)($candidateApplication['applied_at'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><span class="badge-status <?php echo htmlspecialchars($candidateAppBadge, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($candidateAppStatus !== '' ? $candidateAppStatus : 'Unknown', ENT_QUOTES, 'UTF-8'); ?></span></td>
                                            <td>
                                                <a href="candidate-details.php?id=<?php echo (int)$userId; ?>&application_id=<?php echo (int)($candidateApplication['application_id'] ?? 0); ?>" class="btn btn-sm btn-primary">View Application</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
