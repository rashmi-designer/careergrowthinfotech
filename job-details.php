<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
    $stmt = $conn->prepare('SELECT id, title, company, description, skills_required, location, job_type, experience_level, salary_min, salary_max, openings, last_date, status, created_at FROM jobs WHERE id = ? AND status = ? AND (last_date IS NULL OR last_date >= CURDATE()) LIMIT 1');
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

if (!function_exists('cg_format_money')) {
    function cg_format_money(?string $value): string
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
}

if (!function_exists('cg_format_date')) {
    function cg_format_date(?string $value, string $fallback = 'N/A'): string
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
}

if (!function_exists('cg_get_job_company_name')) {
    function cg_get_job_company_name(mixed $value): string
    {
        $company = trim((string)($value ?? ''));
        return $company !== '' ? $company : 'Career Grow Infotech';
    }
}

if (!function_exists('cg_render_safe_text')) {
    function cg_render_safe_text(?string $value): string
    {
        return nl2br(htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8'));
    }
}
?>

<style>
    .job-details-page {
        padding-top: 2.75rem;
        padding-bottom: 4.5rem;
    }

    .job-detail-shell {
        background: var(--cg-white);
        border: 1px solid #dce7f5;
        border-radius: 1.5rem;
        box-shadow: 0 22px 48px rgba(15, 35, 70, 0.08);
        overflow: hidden;
    }

    .job-detail-header {
        position: relative;
        isolation: isolate;
        background:
            radial-gradient(circle at 88% 18%, rgba(13, 110, 253, 0.17), transparent 26%),
            linear-gradient(120deg, #fbfdff 0%, #eef5ff 55%, #deedff 100%);
        border-bottom: 1px solid #dbe7f5;
        padding: clamp(1.6rem, 3vw, 2.5rem);
    }

    .job-detail-header::after {
        position: absolute;
        z-index: -1;
        top: -5rem;
        right: 5%;
        width: 13rem;
        height: 13rem;
        border: 1px solid rgba(13, 110, 253, 0.12);
        border-radius: 50%;
        content: '';
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
        background: rgba(13, 110, 253, 0.09);
        border: 1px solid rgba(13, 110, 253, 0.1);
        border-radius: 999px;
        padding: 0.42rem 0.7rem;
        margin-bottom: 0.9rem;
    }

    .job-detail-header h1 {
        margin: 0;
        font-size: clamp(2.1rem, 4vw, 3.15rem);
        line-height: 1.15;
        letter-spacing: -0.04em;
        color: var(--cg-accent);
    }

    .job-header-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.65rem;
        margin-top: 1.25rem;
        color: #526b8e;
        font-size: 0.9rem;
    }

    .job-header-meta span {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.55rem 0.72rem;
        background: rgba(255, 255, 255, 0.78);
        border: 1px solid rgba(202, 219, 241, 0.9);
        border-radius: 0.65rem;
    }

    .job-header-meta i {
        color: var(--cg-primary);
    }

    .job-header-meta span:nth-child(2) i { color: #f08a1b; }
    .job-header-meta span:nth-child(3) i { color: #7c4de8; }
    .job-header-meta span:nth-child(4) i { color: #009a78; }

    .job-detail-body {
        padding: clamp(1.25rem, 2.5vw, 2rem);
    }

    .detail-main {
        padding-right: 0.35rem;
    }

    .detail-section {
        border: 1px solid #e0e8f2;
        background: var(--cg-white);
        border-radius: 1.05rem;
        padding: 1.4rem 1.5rem;
        margin-bottom: 1.1rem;
    }

    .detail-section h3 {
        color: var(--cg-accent);
        display: flex;
        align-items: center;
        gap: 0.7rem;
        font-size: 1.2rem;
        margin-bottom: 0.85rem;
        font-weight: 700;
    }

    .detail-section h3::before {
        width: 0.28rem;
        height: 1.3rem;
        background: linear-gradient(180deg, #1d70f7, #65a4ff);
        border-radius: 99px;
        content: '';
    }

    .detail-section p,
    .detail-section li {
        color: #526b8e;
        line-height: 1.85;
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
        border: 1px solid #e0e8f2;
        background: #fbfdff;
        border-radius: 1.05rem;
        padding: 1.4rem;
        margin-bottom: 1.1rem;
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
        padding: 0.75rem 0;
        border-bottom: 1px solid #e7edf5;
        color: #4c6485;
        font-size: 0.92rem;
    }

    .quick-list li:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .quick-list strong {
        color: var(--cg-accent);
        font-weight: 600;
    }

    .quick-list span {
        color: #526b8e;
        text-align: right;
    }

    .apply-panel {
        background: linear-gradient(145deg, #f0f6ff 0%, #fbfdff 72%);
        border-color: #cfe0fa;
    }

    .apply-panel .btn {
        width: 100%;
        min-height: 3.2rem;
        box-shadow: 0 10px 20px rgba(13, 110, 253, 0.16);
    }

    @media (min-width: 992px) {
        .job-detail-body aside {
            position: sticky;
            top: 1.25rem;
        }
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

        .detail-section,
        .sidebar-card {
            padding: 1.15rem;
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
                        <?php $jobCompany = cg_get_job_company_name($job['company'] ?? ''); ?>
                        <div class="job-company-tag"><i class="bi bi-building me-1"></i><?php echo htmlspecialchars($jobCompany, ENT_QUOTES, 'UTF-8'); ?></div>
                        <h1><?php echo htmlspecialchars((string)$job['title'], ENT_QUOTES, 'UTF-8'); ?></h1>
                        <div class="job-header-meta">
                            <span><i class="bi bi-geo-alt"></i><?php echo htmlspecialchars((string)($job['location'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                            <span><i class="bi bi-briefcase"></i><?php echo htmlspecialchars((string)($job['job_type'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                            <span><i class="bi bi-calendar3"></i>Posted <?php echo htmlspecialchars(cg_format_date((string)($job['created_at'] ?? '')), ENT_QUOTES, 'UTF-8'); ?></span>
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
                                        <p><?php echo cg_render_safe_text((string)$job['description']); ?></p>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($job['skills_required'])): ?>
                                    <div class="detail-section">
                                        <h3>Skills & Requirements</h3>
                                        <p><?php echo cg_render_safe_text((string)$job['skills_required']); ?></p>
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
                                                $salaryText = cg_format_money($minValue);
                                                if ($maxValue !== '' && $minValue !== '' && (float)$minValue > 0) {
                                                    $salaryText .= ' - ' . cg_format_money($maxValue);
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
                                            <a href="candidate/apply.php?job_id=<?php echo (int)$job['id']; ?>" class="btn btn-primary">Apply Now</a>
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
                                        <li><strong>Posted</strong><span><?php echo htmlspecialchars(cg_format_date((string)($job['created_at'] ?? '')), ENT_QUOTES, 'UTF-8'); ?></span></li>
                                        <?php if (!empty($job['last_date'])): ?>
                                            <li><strong>Last Date</strong><span><?php echo htmlspecialchars(cg_format_date((string)$job['last_date']), ENT_QUOTES, 'UTF-8'); ?></span></li>
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
