<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

$pageTitle = 'Jobs - Career Grow Infotech';

$conn = getDbConnection();

$search = trim((string)($_GET['search'] ?? ''));
$jobTypeFilter = trim((string)($_GET['job_type'] ?? ''));
$categoryFilter = trim((string)($_GET['category'] ?? ''));
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

if ($categoryFilter !== '') {
    $where[] = 'category = ?';
    $params[] = $categoryFilter;
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
$sql = 'SELECT id, title, company, location, job_type, experience_level, salary_min, salary_max, last_date, created_at FROM jobs ' . $whereSql . ' AND (last_date IS NULL OR last_date >= CURDATE()) ORDER BY created_at DESC LIMIT ? OFFSET ?';
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

if (!function_exists('cg_get_job_company_name')) {
    function cg_get_job_company_name(mixed $value): string
    {
        $company = trim((string)($value ?? ''));
        return $company !== '' ? $company : 'Career Grow Infotech';
    }
}

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

$hasFilters = $search !== '' || $jobTypeFilter !== '' || $locationFilter !== '' || $experienceFilter !== '' || $categoryFilter !== '';
if ($categoryFilter !== '') {
    $emptyStateTitle = 'No jobs available in this category.';
    $emptyStateText = 'There are currently no job listings in this category.';
} else {
    $emptyStateTitle = $hasFilters ? 'No matching jobs found' : 'No jobs available right now';
    $emptyStateText = $hasFilters
        ? 'Try adjusting your search or clearing the filters to explore all current opportunities.'
        : 'New openings will appear here as soon as Career Grow Infotech publishes them.';
}
?>

<style>
    .jobs-page-shell {
        padding-top: 3rem;
        padding-bottom: 4rem;
    }

    .jobs-hero {
        overflow: hidden;
        background:
            radial-gradient(circle at 82% 38%, rgba(255,255,255,.42), transparent 25%),
            radial-gradient(circle at 100% 0%, rgba(13,110,253,.24), transparent 36%),
            linear-gradient(118deg, #f9fbff 0%, #edf4ff 36%, #c9e0ff 70%, #9bc7ff 100%);
        border: 1px solid rgba(13, 110, 253, 0.16);
        border-radius: 1.5rem;
        padding: 1rem;
        box-shadow: 0 18px 42px rgba(13, 110, 253, 0.08);
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
        font-size: clamp(2.2rem, 4vw, 3.5rem);
        font-weight: 800;
        letter-spacing: -0.04em;
        color: #102b50;
        line-height: 1.12;
    }

    .jobs-hero p {
        color: #4a607a;
        margin-top: 0.75rem;
        max-width: 52ch;
        font-size: 1.08rem;
        line-height: 1.65;
    }

    .jobs-hero-card {
        padding: clamp(1.25rem, 2.5vw, 1.75rem);
        background: rgba(255,255,255,.9);
        border: 1px solid rgba(13,110,253,.1);
        border-radius: 1.1rem;
        box-shadow: 0 18px 40px rgba(15,23,42,.08);
    }

    .jobs-hero-card-title { color: #18375e; font-size: 1.05rem; font-weight: 700; }
    .jobs-hero-icon {
        width: 52px;
        height: 52px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 1rem;
        background: linear-gradient(145deg, rgba(18,103,232,.08), rgba(18,103,232,.18));
        color: #1267e8;
        font-size: 1.35rem;
    }

    .jobs-hero-stat {
        margin: 1.35rem 0 1rem;
        padding: 1rem;
        border: 1px solid #d9e7fa;
        border-radius: .85rem;
        background: #f6faff;
    }

    .jobs-hero-stat strong { display: block; color: #0d6efd; font-size: 1.85rem; line-height: 1; }
    .jobs-hero-stat span { color: #506b89; font-size: .88rem; }

    .jobs-hero-actions { display: flex; flex-wrap: wrap; gap: .75rem; margin-top: 1.5rem; }
    .jobs-hero-actions .btn { min-width: 145px; }

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
        position: relative;
        background: var(--cg-white);
        border: 1px solid var(--cg-border);
        border-radius: 1rem;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.04);
        padding: 1.5rem;
        margin-bottom: 1rem;
        transition: box-shadow .2s ease, border-color .2s ease;
    }

    .job-card::before { display: none; }
    .job-card:hover { border-color: rgba(13,110,253,.24); box-shadow: 0 16px 34px rgba(15, 23, 42, .07); }
    .job-card-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; }

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
        gap: .65rem 1.35rem;
        margin-top: .9rem;
    }

    .job-meta span {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        color: var(--cg-muted);
        font-size: .93rem;
    }

    .job-meta i {
        color: #1267e8;
        font-size: 0.95rem;
    }

    .job-meta span:nth-child(2) i { color: #e56f16; }
    .job-meta span:nth-child(3) i { color: #7456d9; }

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
        margin-top: .9rem;
        font-weight: 700;
        color: var(--cg-accent);
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .job-card .salary i {
        color: #078b68;
    }

    .job-status i { color: #e56f16; }

    .job-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.85rem;
        flex-wrap: wrap;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid rgba(15, 23, 42, 0.06);
    }

    .job-status {
        color: var(--cg-muted);
        font-size: 0.85rem;
    }

    .job-actions .btn {
        min-width: 145px;
        padding: .7rem 1.2rem;
        font-weight: 600;
    }

    /* Match the shared primary and outline CTA buttons used across the site. */
    .job-actions .btn-primary {
        background: var(--cg-primary);
        border-color: var(--cg-primary);
        color: var(--cg-white);
        box-shadow: 0 10px 20px rgba(13, 110, 253, .18);
    }

    .job-actions .btn-outline-primary {
        border-color: var(--cg-primary);
        color: var(--cg-primary);
        background: transparent;
    }

    .job-actions .btn-primary:hover,
    .job-actions .btn-primary:focus,
    .job-actions .btn-outline-primary:hover,
    .job-actions .btn-outline-primary:focus {
        background: var(--cg-primary);
        border-color: var(--cg-primary);
        color: var(--cg-white);
    }

    @media (max-width: 767.98px) {
        .job-card-header { flex-direction: column; }
        .job-meta { gap: .65rem 1rem; }
        .job-actions { align-items: stretch; }
        .job-actions > .d-flex { width: 100%; }
        .job-actions .btn { flex: 1; min-width: 0; }
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
                <div class="row g-4 align-items-center p-2 p-lg-4">
                    <div class="col-lg-7">
                        <div class="eyebrow">Career Opportunities</div>
                        <h1>Find the right role for your next step</h1>
                        <p>Explore current openings from Career Grow Infotech and discover roles that match your skills, location, and career goals.</p>
                        <div class="jobs-hero-actions">
                            <a href="#job-search" class="btn btn-primary">Explore openings</a>
                            <a href="contact.php" class="btn btn-outline-primary">Talk to our team</a>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="jobs-hero-card">
                            <div class="d-flex align-items-center justify-content-between gap-3">
                                <div>
                                    <div class="eyebrow mb-1">Your job search</div>
                                    <div class="jobs-hero-card-title">Opportunities curated for you</div>
                                </div>
                                <div class="jobs-hero-icon"><i class="bi bi-briefcase"></i></div>
                            </div>
                            <div class="jobs-hero-stat">
                                <strong><?php echo (int) $totalJobs; ?></strong>
                                <span>active <?php echo $totalJobs === 1 ? 'opportunity' : 'opportunities'; ?> available now</span>
                            </div>
                            <div class="small text-muted">Filter openings by role, location, or experience to find your best match.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="jobs-toolbar" id="job-search">
                <form method="get" class="row g-3 align-items-center">
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label visually-hidden" for="search">Search jobs</label>
                        <input id="search" type="text" name="search" class="form-control" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Search by job title or location">
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label visually-hidden" for="jobType">Job type</label>
                        <select id="jobType" name="job_type" class="form-select">
                            <option value="">Job Types</option>
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
                    <div class="col-lg-3 col-md-12 d-flex gap-2">
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
                    <div><?php echo htmlspecialchars(ucfirst((string)$publicStatus), ENT_QUOTES, 'UTF-8'); ?> roles</div>
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
                        ? (cg_format_money($salaryMin) . ($salaryMax !== null && trim((string)$salaryMax) !== '' && (float)$salaryMin > 0 ? ' - ' . cg_format_money($salaryMax) : ''))
                        : 'Salary not disclosed';
                    $cardCompany = cg_get_job_company_name($job['company'] ?? '');
                    ?>
                    <article class="job-card">
                        <div class="job-card-header">
                            <div class="flex-grow-1">
                                <span class="job-company"><?php echo htmlspecialchars($cardCompany, ENT_QUOTES, 'UTF-8'); ?></span>
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
                                <span><i class="bi bi-calendar3"></i>Last date: <?php echo htmlspecialchars(cg_format_date((string)$job['last_date']), ENT_QUOTES, 'UTF-8'); ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="salary">
                            <i class="bi bi-currency-rupee"></i>
                            <?php echo htmlspecialchars($salaryText, ENT_QUOTES, 'UTF-8'); ?>
                        </div>

                        <div class="job-actions">
                            <div class="job-status">
                                <i class="bi bi-clock-history me-1"></i>Posted <?php echo htmlspecialchars(cg_format_date((string)($job['created_at'] ?? '')), ENT_QUOTES, 'UTF-8'); ?>
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
