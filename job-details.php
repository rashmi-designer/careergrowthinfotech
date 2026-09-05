<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

$pageTitle = 'Job Details - Career Grow Infotech';

$rawId = $_GET['id'] ?? '';
$jobId = 0;
if (is_string($rawId) || is_numeric($rawId)) {
    $jobId = filter_var($rawId, FILTER_VALIDATE_INT);
    if ($jobId === false) {
        $jobId = 0;
    }
}

$conn = getDbConnection();
$job = null;
$salaryText = 'Not disclosed';

if ($jobId > 0) {
    $publicStatus = 'active';
    $stmt = $conn->prepare('SELECT id, title, description, skills_required, location, job_type, experience_level, salary_min, salary_max, openings, last_date, status, created_at FROM jobs WHERE id = ? AND status = ? AND (last_date IS NULL OR last_date >= CURDATE()) LIMIT 1');
    if ($stmt) {
        $stmt->bind_param('is', $jobId, $publicStatus);
        $stmt->execute();
        $result = $stmt->get_result();
        $job = $result->fetch_assoc();
        $stmt->close();
    }
}

$alreadyApplied = false;
$sessionActive = session_status() === PHP_SESSION_ACTIVE;
if ($job && $sessionActive && !empty($_SESSION['user_id']) && !empty($_SESSION['user_role']) && $_SESSION['user_role'] === 'candidate') {
    $userId = (int)$_SESSION['user_id'];
    $checkStmt = $conn->prepare('SELECT 1 FROM applications WHERE user_id = ? AND job_id = ? LIMIT 1');
    if ($checkStmt) {
        $checkStmt->bind_param('ii', $userId, $jobId);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        $alreadyApplied = $checkResult->fetch_assoc() !== null;
        $checkStmt->close();
    }
}

$conn->close();

function formatMoney(?string $value): string
{
    if ($value === null || trim($value) === '') {
        return 'Not disclosed';
    }

    $amount = (float)$value;
    if ($amount <= 0) {
        return 'Not disclosed';
    }

    return '₹' . number_format($amount, 0, '.', ',');
}

function formatDate(?string $value, string $fallback = 'N/A'): string
{
    if ($value === null || trim($value) === '') {
        return $fallback;
    }

    $timestamp = strtotime((string)$value);
    if ($timestamp === false) {
        return $fallback;
    }

    return date('d M Y', $timestamp);
}

function renderSafeText(?string $value): string
{
    return nl2br(htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8'));
}
?>

<style>
    .job-details-page {
        padding-top: 3rem;
        padding-bottom: 4rem;
    }

    .job-detail-shell {
        background: var(--cg-white);
        border: 1px solid var(--cg-border);
        border-radius: 1.25rem;
        box-shadow: 0 16px 34px rgba(15, 23, 42, 0.04);
        overflow: hidden;
    }

    .job-detail-header {
        background: rgba(13, 110, 253, 0.04);
        border-bottom: 1px solid rgba(13, 110, 253, 0.08);
        padding: 1.5rem 1.5rem 1.2rem;
    }

    .job-company-tag {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--cg-primary);
        background: rgba(13, 110, 253, 0.06);
        border-radius: 999px;
        padding: 0.42rem 0.7rem;
        margin-bottom: 0.9rem;
    }

    .job-detail-header h1 {
        margin: 0;
        font-size: clamp(2rem, 4vw, 2.8rem);
        line-height: 1.15;
        letter-spacing: -0.04em;
        color: var(--cg-accent);
    }

    .job-header-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.9rem 1.2rem;
        margin-top: 1rem;
        color: var(--cg-muted);
        font-size: 0.95rem;
    }

    .job-header-meta span {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .job-header-meta i {
        color: var(--cg-primary);
    }

    .job-detail-body {
        padding: 1.5rem;
    }

    .detail-main {
        padding-right: 0.5rem;
    }

    .detail-section {
        border: 1px solid var(--cg-border);
        background: var(--cg-white);
        border-radius: 1rem;
        padding: 1.25rem;
        margin-bottom: 1rem;
    }

    .detail-section h3 {
        color: var(--cg-accent);
        font-size: 1.15rem;
        margin-bottom: 0.75rem;
        font-weight: 700;
    }

    .detail-section p,
    .detail-section li {
        color: var(--cg-text);
        line-height: 1.8;
    }

    .detail-section ul {
        padding-left: 1.1rem;
        margin-bottom: 0;
    }

    .detail-section p:last-child,
    .detail-section li:last-child {
        margin-bottom: 0;
    }

    .sidebar-card {
        border: 1px solid var(--cg-border);
        background: var(--cg-light);
        border-radius: 1rem;
        padding: 1.25rem;
        margin-bottom: 1rem;
    }

    .sidebar-card h4 {
        margin-bottom: 1rem;
        color: var(--cg-accent);
        font-weight: 700;
    }

    .quick-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .quick-list li {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.7rem 0;
        border-bottom: 1px solid rgba(15, 23, 42, 0.06);
        color: var(--cg-text);
    }

    .quick-list li:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .quick-list strong {
        color: var(--cg-accent);
        font-weight: 600;
    }

    .apply-panel {
        background: linear-gradient(180deg, rgba(13, 110, 253, 0.03), rgba(255, 255, 255, 0));
    }

    .apply-panel .btn {
        width: 100%;
    }

    .alert-inline {
        margin-top: 0.75rem;
        border-radius: 0.85rem;
    }

    @media (max-width: 991.98px) {
        .job-detail-body {
            padding: 1rem;
        }
    }

    @media (max-width: 767.98px) {
        .job-details-page {
            padding-top: 2rem;
            padding-bottom: 3rem;
        }

        .job-detail-header,
        .job-detail-body {
            padding: 1rem;
        }
    }
</style>

<main class="container job-details-page">
    <?php if (!$job): ?>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="alert alert-light border text-center py-5">
                    <h3 class="mb-3">Job Not Found</h3>
                    <p class="text-muted mb-3">The job you requested is unavailable, has expired, or no longer matches the current public listings.</p>
                    <a href="jobs.php" class="btn btn-primary">Back to Jobs</a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="row justify-content-center">
            <div class="col-xl-10">
                <nav aria-label="breadcrumb" class="mb-3">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="jobs.php">Jobs</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars((string)$job['title'], ENT_QUOTES, 'UTF-8'); ?></li>
                    </ol>
                </nav>

                <div class="job-detail-shell">
                    <div class="job-detail-header">
                        <div class="job-company-tag"><i class="bi bi-building me-1"></i>Career Grow Infotech</div>
                        <h1><?php echo htmlspecialchars((string)$job['title'], ENT_QUOTES, 'UTF-8'); ?></h1>
                        <div class="job-header-meta">
                            <span><i class="bi bi-geo-alt"></i><?php echo htmlspecialchars((string)($job['location'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                            <span><i class="bi bi-briefcase"></i><?php echo htmlspecialchars((string)($job['job_type'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                            <span><i class="bi bi-calendar3"></i>Posted <?php echo htmlspecialchars(formatDate((string)($job['created_at'] ?? '')), ENT_QUOTES, 'UTF-8'); ?></span>
                            <?php if (!empty($job['experience_level'])): ?>
                                <span><i class="bi bi-person-workspace"></i><?php echo htmlspecialchars((string)$job['experience_level'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="job-detail-body">
                        <div class="row g-4 align-items-start">
                            <div class="col-lg-8 detail-main">
                                <?php if (!empty($job['description'])): ?>
                                    <div class="detail-section">
                                        <h3>Job Description</h3>
                                        <p><?php echo renderSafeText((string)$job['description']); ?></p>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($job['skills_required'])): ?>
                                    <div class="detail-section">
                                        <h3>Skills & Requirements</h3>
                                        <p><?php echo renderSafeText((string)$job['skills_required']); ?></p>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($job['salary_min']) || !empty($job['salary_max'])): ?>
                                    <div class="detail-section">
                                        <h3>Salary</h3>
                                        <p>
                                            <?php
                                            $minValue = isset($job['salary_min']) ? trim((string)$job['salary_min']) : '';
                                            $maxValue = isset($job['salary_max']) ? trim((string)$job['salary_max']) : '';
                                            if ($minValue !== '' || $maxValue !== '') {
                                                $salaryText = formatMoney($minValue);
                                                if ($maxValue !== '' && $minValue !== '' && (float)$minValue > 0) {
                                                    $salaryText .= ' - ' . formatMoney($maxValue);
                                                }
                                            }
                                            echo htmlspecialchars($salaryText, ENT_QUOTES, 'UTF-8');
                                            ?>
                                        </p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <aside class="col-lg-4">
                                <div class="sidebar-card apply-panel">
                                    <h4>Apply Now</h4>
                                    <?php if ($sessionActive && !empty($_SESSION['user_id']) && !empty($_SESSION['user_role']) && $_SESSION['user_role'] === 'candidate'): ?>
                                        <?php if ($alreadyApplied): ?>
                                            <div class="alert alert-light border text-success alert-inline mb-0">
                                                <strong>Already Applied</strong>
                                            </div>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-primary" disabled>Application flow is not yet implemented</button>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <a href="login.php?job_id=<?php echo (int)$job['id']; ?>" class="btn btn-primary">Login to Apply</a>
                                    <?php endif; ?>
                                </div>

                                <div class="sidebar-card">
                                    <h4>Quick Info</h4>
                                    <ul class="quick-list">
                                        <li><strong>Location</strong><span><?php echo htmlspecialchars((string)($job['location'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></span></li>
                                        <li><strong>Type</strong><span><?php echo htmlspecialchars((string)($job['job_type'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></span></li>
                                        <li><strong>Experience</strong><span><?php echo htmlspecialchars((string)($job['experience_level'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></span></li>
                                        <li><strong>Openings</strong><span><?php echo (int)($job['openings'] ?? 0); ?></span></li>
                                        <li><strong>Posted</strong><span><?php echo htmlspecialchars(formatDate((string)($job['created_at'] ?? '')), ENT_QUOTES, 'UTF-8'); ?></span></li>
                                        <?php if (!empty($job['last_date'])): ?>
                                            <li><strong>Last Date</strong><span><?php echo htmlspecialchars(formatDate((string)$job['last_date']), ENT_QUOTES, 'UTF-8'); ?></span></li>
                                        <?php endif; ?>
                                        <li><strong>Salary</strong><span><?php echo htmlspecialchars((string)($salaryText ?? 'Not disclosed'), ENT_QUOTES, 'UTF-8'); ?></span></li>
                                    </ul>
                                </div>
                            </aside>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
