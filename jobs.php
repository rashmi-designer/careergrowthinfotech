<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

$pageTitle = 'Jobs - Career Grow Infotech';

$conn = getDbConnection();

$search = trim((string)($_GET['search'] ?? ''));
$jobTypeFilter = trim((string)($_GET['job_type'] ?? ''));
$locationFilter = trim((string)($_GET['location'] ?? ''));
$experienceFilter = trim((string)($_GET['experience'] ?? ''));
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 6;
$offset = ($page - 1) * $perPage;
$publicStatus = 'active';

$jobTypes = [];
$locations = [];
$experiences = [];

$metaStmt = $conn->prepare('SELECT DISTINCT job_type FROM jobs WHERE status = ? AND job_type IS NOT NULL AND job_type <> "" ORDER BY job_type ASC');
if ($metaStmt) {
    $metaStmt->bind_param('s', $publicStatus);
    $metaStmt->execute();
    $metaResult = $metaStmt->get_result();
    while ($row = $metaResult->fetch_assoc()) {
        $jobTypes[] = (string)($row['job_type'] ?? '');
    }
    $metaStmt->close();
}

$locationStmt = $conn->prepare('SELECT DISTINCT location FROM jobs WHERE status = ? AND location IS NOT NULL AND location <> "" ORDER BY location ASC');
if ($locationStmt) {
    $locationStmt->bind_param('s', $publicStatus);
    $locationStmt->execute();
    $locationResult = $locationStmt->get_result();
    while ($row = $locationResult->fetch_assoc()) {
        $locations[] = (string)($row['location'] ?? '');
    }
    $locationStmt->close();
}

$experienceStmt = $conn->prepare('SELECT DISTINCT experience_level FROM jobs WHERE status = ? AND experience_level IS NOT NULL AND experience_level <> "" ORDER BY experience_level ASC');
if ($experienceStmt) {
    $experienceStmt->bind_param('s', $publicStatus);
    $experienceStmt->execute();
    $experienceResult = $experienceStmt->get_result();
    while ($row = $experienceResult->fetch_assoc()) {
        $experiences[] = (string)($row['experience_level'] ?? '');
    }
    $experienceStmt->close();
}

$where = ['status = ?'];
$params = [$publicStatus];
$types = 's';

if ($search !== '') {
    $where[] = '(title LIKE ? OR location LIKE ? OR job_type LIKE ?)';
    $like = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types .= 'sss';
}

if ($jobTypeFilter !== '') {
    $where[] = 'job_type = ?';
    $params[] = $jobTypeFilter;
    $types .= 's';
}

if ($locationFilter !== '') {
    $where[] = 'location = ?';
    $params[] = $locationFilter;
    $types .= 's';
}

if ($experienceFilter !== '') {
    $where[] = 'experience_level = ?';
    $params[] = $experienceFilter;
    $types .= 's';
}

$whereSql = 'WHERE ' . implode(' AND ', $where);
$countSql = 'SELECT COUNT(*) FROM jobs ' . $whereSql . ' AND (last_date IS NULL OR last_date >= CURDATE())';
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
if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $perPage;
}

$jobs = [];
$sql = 'SELECT id, title, location, job_type, experience_level, salary_min, salary_max, last_date, created_at FROM jobs ' . $whereSql . ' AND (last_date IS NULL OR last_date >= CURDATE()) ORDER BY created_at DESC LIMIT ? OFFSET ?';
$stmt = $conn->prepare($sql);
if ($stmt) {
    $bindValues = $params;
    $bindValues[] = $perPage;
    $bindValues[] = $offset;
    $bindTypes = $types . 'ii';
    $stmt->bind_param($bindTypes, ...$bindValues);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $jobs[] = $row;
    }
    $stmt->close();
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

$hasFilters = $search !== '' || $jobTypeFilter !== '' || $locationFilter !== '' || $experienceFilter !== '';
$emptyStateTitle = $hasFilters ? 'No matching jobs found' : 'No jobs available right now';
$emptyStateText = $hasFilters
    ? 'Try adjusting your search or clearing the filters to explore all current opportunities.'
    : 'New openings will appear here as soon as Career Grow Infotech publishes them.';
?>

<style>
    .jobs-page-shell {
        padding-top: 3rem;
        padding-bottom: 4rem;
    }

    .jobs-hero {
        background: rgba(13, 110, 253, 0.04);
        border: 1px solid rgba(13, 110, 253, 0.1);
        border-radius: 1.25rem;
        padding: 1.5rem 1.25rem;
        margin-bottom: 1.5rem;
    }

    .jobs-hero .eyebrow {
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: var(--cg-primary);
        font-size: 0.72rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .jobs-hero h1 {
        margin: 0;
        font-size: clamp(2rem, 4vw, 3rem);
        font-weight: 800;
        letter-spacing: -0.04em;
        color: var(--cg-accent);
    }

    .jobs-hero p {
        color: var(--cg-muted);
        margin-top: 0.75rem;
        max-width: 42rem;
    }

    .jobs-toolbar {
        background: var(--cg-white);
        border: 1px solid var(--cg-border);
        border-radius: 1rem;
        padding: 1rem;
        box-shadow: 0 10px 25px rgba(15, 23, 42, 0.03);
        margin-bottom: 1.5rem;
    }

    .jobs-toolbar .form-control,
    .jobs-toolbar .form-select {
        min-height: 48px;
        border-radius: 0.8rem;
        border-color: rgba(13, 110, 253, 0.12);
    }

    .job-card {
        background: var(--cg-white);
        border: 1px solid var(--cg-border);
        border-radius: 1rem;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.04);
        padding: 1.25rem;
        margin-bottom: 1rem;
    }

    .job-company {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--cg-primary);
        background: rgba(13, 110, 253, 0.06);
        padding: 0.38rem 0.6rem;
        border-radius: 999px;
        margin-bottom: 0.75rem;
    }

    .job-card h3 {
        margin: 0;
        font-size: clamp(1.2rem, 2vw, 1.65rem);
        line-height: 1.3;
        letter-spacing: -0.02em;
    }

    .job-card h3 a {
        color: var(--cg-accent);
    }

    .job-card h3 a:hover,
    .job-card h3 a:focus {
        color: var(--cg-primary);
    }

    .job-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.9rem 1.2rem;
        margin-top: 1rem;
        color: var(--cg-muted);
        font-size: 0.93rem;
    }

    .job-meta span {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
    }

    .job-meta i {
        color: var(--cg-primary);
        font-size: 0.95rem;
    }

    .job-card .badge {
        border-radius: 999px;
        padding: 0.48rem 0.8rem;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .job-card .badge-soft {
        background: rgba(13, 110, 253, 0.08);
        color: var(--cg-primary);
    }

    .job-card .salary {
        margin-top: 1rem;
        font-weight: 700;
        color: var(--cg-accent);
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .job-card .salary i {
        color: var(--cg-primary);
    }

    .job-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.85rem;
        flex-wrap: wrap;
        margin-top: 1.2rem;
        padding-top: 1rem;
        border-top: 1px solid rgba(15, 23, 42, 0.06);
    }

    .job-status {
        color: var(--cg-muted);
        font-size: 0.85rem;
    }

    .job-actions .btn {
        min-width: 150px;
    }

    .empty-state {
        background: var(--cg-white);
        border: 1px solid var(--cg-border);
        border-radius: 1rem;
        padding: 2rem 1.5rem;
        text-align: center;
        box-shadow: 0 10px 22px rgba(15, 23, 42, 0.03);
    }

    .empty-state h3 {
        margin-bottom: 0.5rem;
        color: var(--cg-accent);
        font-weight: 700;
    }

    .empty-state p {
        color: var(--cg-muted);
        margin-bottom: 1rem;
    }

    .page-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
        color: var(--cg-muted);
        font-size: 0.92rem;
    }

    .pagination {
        justify-content: center;
        margin-top: 1.5rem;
    }

    .pagination .page-link {
        color: var(--cg-accent);
        border-color: rgba(13, 110, 253, 0.12);
        border-radius: 0.75rem;
        margin: 0 0.25rem;
        padding: 0.6rem 0.9rem;
    }

    .pagination .page-item.active .page-link {
        background-color: var(--cg-primary);
        border-color: var(--cg-primary);
        color: var(--cg-white);
    }

    @media (max-width: 767.98px) {
        .jobs-page-shell {
            padding-top: 2rem;
            padding-bottom: 3rem;
        }

        .job-actions {
            align-items: stretch;
        }

        .job-actions .btn {
            flex: 1 1 100%;
        }

        .page-toolbar {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>

<main class="container jobs-page-shell">
    <div class="row justify-content-center">
        <div class="col-xl-10">
            <div class="jobs-hero">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3">
                    <div>
                        <div class="eyebrow">Career Opportunities</div>
                        <h1>Find the right role for your next step</h1>
                    </div>
                    <div class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill">
                        <i class="bi bi-briefcase me-2"></i>Career Grow Infotech
                    </div>
                </div>
                <p>Explore current openings from Career Grow Infotech and discover roles that match your skills, location, and career goals.</p>
            </div>

            <div class="jobs-toolbar">
                <form method="get" class="row g-3 align-items-center">
                    <div class="col-lg-4 col-md-6">
                        <label class="form-label visually-hidden" for="search">Search jobs</label>
                        <input id="search" type="text" name="search" class="form-control" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Search by job title or location">
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label visually-hidden" for="jobType">Job type</label>
                        <select id="jobType" name="job_type" class="form-select">
                            <option value="">All job types</option>
                            <?php foreach ($jobTypes as $type): ?>
                                <option value="<?php echo htmlspecialchars((string)$type, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $jobTypeFilter === (string)$type ? 'selected' : ''; ?>><?php echo htmlspecialchars((string)$type, ENT_QUOTES, 'UTF-8'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label visually-hidden" for="location">Location</label>
                        <select id="location" name="location" class="form-select">
                            <option value="">All locations</option>
                            <?php foreach ($locations as $location): ?>
                                <option value="<?php echo htmlspecialchars((string)$location, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $locationFilter === (string)$location ? 'selected' : ''; ?>><?php echo htmlspecialchars((string)$location, ENT_QUOTES, 'UTF-8'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label visually-hidden" for="experience">Experience</label>
                        <select id="experience" name="experience" class="form-select">
                            <option value="">All experience</option>
                            <?php foreach ($experiences as $experience): ?>
                                <option value="<?php echo htmlspecialchars((string)$experience, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $experienceFilter === (string)$experience ? 'selected' : ''; ?>><?php echo htmlspecialchars((string)$experience, ENT_QUOTES, 'UTF-8'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-fill">Search</button>
                        <a href="jobs.php" class="btn btn-outline-secondary">Clear</a>
                    </div>
                </form>
            </div>

            <?php if ($totalJobs === 0): ?>
                <div class="empty-state">
                    <h3><?php echo htmlspecialchars($emptyStateTitle, ENT_QUOTES, 'UTF-8'); ?></h3>
                    <p><?php echo htmlspecialchars($emptyStateText, ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php if ($hasFilters): ?>
                        <a href="jobs.php" class="btn btn-primary">Clear Filters</a>
                    <?php else: ?>
                        <a href="contact.php" class="btn btn-primary">Contact Us</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="page-toolbar">
                    <div>Showing <?php echo (int)count($jobs); ?> of <?php echo (int)$totalJobs; ?> active opportunities</div>
                    <div><?php echo htmlspecialchars((string)$publicStatus, ENT_QUOTES, 'UTF-8'); ?> roles</div>
                </div>

                <?php foreach ($jobs as $job): ?>
                    <?php
                    $jobId = (int)($job['id'] ?? 0);
                    $jobTitle = (string)($job['title'] ?? '');
                    $jobLocation = (string)($job['location'] ?? '');
                    $jobType = (string)($job['job_type'] ?? '');
                    $experience = (string)($job['experience_level'] ?? '');
                    $salaryMin = isset($job['salary_min']) && $job['salary_min'] !== null ? (string)$job['salary_min'] : null;
                    $salaryMax = isset($job['salary_max']) && $job['salary_max'] !== null ? (string)$job['salary_max'] : null;
                    $salaryText = $salaryMin !== null || $salaryMax !== null
                        ? (formatMoney($salaryMin) . ($salaryMax !== null && trim((string)$salaryMax) !== '' && (float)$salaryMin > 0 ? ' - ' . formatMoney($salaryMax) : ''))
                        : 'Salary not disclosed';
                    ?>
                    <article class="job-card">
                        <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
                            <div class="flex-grow-1">
                                <span class="job-company">Career Grow Infotech</span>
                                <h3><a href="job-details.php?id=<?php echo $jobId; ?>"><?php echo htmlspecialchars($jobTitle, ENT_QUOTES, 'UTF-8'); ?></a></h3>
                            </div>
                            <?php if ($jobType !== ''): ?>
                                <span class="badge badge-soft"><?php echo htmlspecialchars($jobType, ENT_QUOTES, 'UTF-8'); ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="job-meta">
                            <span><i class="bi bi-geo-alt"></i><?php echo htmlspecialchars($jobLocation, ENT_QUOTES, 'UTF-8'); ?></span>
                            <?php if ($experience !== ''): ?>
                                <span><i class="bi bi-person-workspace"></i><?php echo htmlspecialchars($experience, ENT_QUOTES, 'UTF-8'); ?></span>
                            <?php endif; ?>
                            <?php if (!empty($job['last_date'])): ?>
                                <span><i class="bi bi-calendar3"></i>Last date: <?php echo htmlspecialchars(formatDate((string)$job['last_date']), ENT_QUOTES, 'UTF-8'); ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="salary">
                            <i class="bi bi-currency-rupee"></i>
                            <?php echo htmlspecialchars($salaryText, ENT_QUOTES, 'UTF-8'); ?>
                        </div>

                        <div class="job-actions">
                            <div class="job-status">
                                <i class="bi bi-clock-history me-1"></i>Posted <?php echo htmlspecialchars(formatDate((string)($job['created_at'] ?? '')), ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="job-details.php?id=<?php echo $jobId; ?>" class="btn btn-outline-primary">View Details</a>
                                <a href="job-details.php?id=<?php echo $jobId; ?>" class="btn btn-primary">Apply Now</a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>

                <?php if ($totalPages > 1): ?>
                    <nav aria-label="Job pages">
                        <ul class="pagination">
                            <?php
                            $prevPage = max(1, $page - 1);
                            $nextPage = min($totalPages, $page + 1);
                            $queryString = http_build_query([
                                'search' => $search,
                                'job_type' => $jobTypeFilter,
                                'location' => $locationFilter,
                                'experience' => $experienceFilter,
                            ]);
                            ?>
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="jobs.php?page=<?php echo $prevPage; ?><?php echo $queryString !== '' ? '&' . $queryString : ''; ?>">Previous</a>
                                </li>
                            <?php endif; ?>

                            <?php for ($pageNumber = 1; $pageNumber <= $totalPages; $pageNumber++): ?>
                                <li class="page-item <?php echo $pageNumber === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="jobs.php?page=<?php echo $pageNumber; ?><?php echo $queryString !== '' ? '&' . $queryString : ''; ?>"><?php echo (int)$pageNumber; ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="jobs.php?page=<?php echo $nextPage; ?><?php echo $queryString !== '' ? '&' . $queryString : ''; ?>">Next</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
