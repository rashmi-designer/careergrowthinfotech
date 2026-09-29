<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/admin-auth.php';
require_admin();

$pageTitle = 'Add New Job - Admin';

$errors = [];
$success = false;

// initialize fields
$fields = [
    'title' => '',
    'company' => '',
    'description' => '',
    'skills_required' => '',
    'location' => '',
    'job_type' => '',
    'category' => '',
    'experience_level' => '',
    'salary_min' => '',
    'salary_max' => '',
    'openings' => '1',
    'last_date' => '',
    'status' => 'active'
];

// CSRF token to avoid duplicate submissions
if (empty($_SESSION['add_job_token'])) {
    $_SESSION['add_job_token'] = bin2hex(random_bytes(16));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // simple CSRF
    $token = $_POST['add_job_token'] ?? '';
    if (!hash_equals($_SESSION['add_job_token'] ?? '', (string)$token)) {
        $errors[] = 'Invalid form submission.';
    }

    // collect and trim
    foreach ($fields as $k => $v) {
        $fields[$k] = trim((string)($_POST[$k] ?? ''));
    }

    // validation
    if ($fields['title'] === '') { $errors[] = 'Job title is required.'; }
    if ($fields['company'] === '') { $errors[] = 'Company name is required.'; }
    if ($fields['location'] === '') { $errors[] = 'Location is required.'; }
    if ($fields['job_type'] === '') { $errors[] = 'Job type is required.'; }
    if ($fields['category'] === '') { $errors[] = 'Category is required.'; }
    if ($fields['experience_level'] === '') { $errors[] = 'Experience level is required.'; }
    if ($fields['openings'] === '' || !ctype_digit($fields['openings'])) { $errors[] = 'Vacancies must be a number.'; }
    if ($fields['status'] === '') { $errors[] = 'Status is required.'; }

    if (empty($errors)) {
        $conn = getDbConnection();

        $sql = "INSERT INTO jobs (title, company, description, skills_required, location, job_type, category, experience_level, salary_min, salary_max, openings, last_date, status) VALUES (?,?,?,?,?,?,?, ?,NULLIF(?,''),NULLIF(?,''),?,?,?)";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            // Columns: title, company, description, skills_required, location, job_type, experience_level, salary_min, salary_max, openings, last_date, status
            $types = 'ssssssssssiss';
            $bindParams = [
                $fields['title'],
                $fields['company'],
                $fields['description'],
                $fields['skills_required'],
                $fields['location'],
                $fields['job_type'],
                $fields['category'],
                $fields['experience_level'],
                $fields['salary_min'],
                $fields['salary_max'],
                (int)$fields['openings'],
                $fields['last_date'],
                $fields['status']
            ];

            $stmt->bind_param($types, ...$bindParams);

            $ok = $stmt->execute();
            if ($ok) {
                $stmt->close();
                $conn->close();
                $_SESSION['job_status_message'] = 'Job Added Successfully. The new job has been created successfully.';
                $_SESSION['job_status_type'] = 'success';
                // regenerate token to prevent resubmission
                unset($_SESSION['add_job_token']);
                header('Location: jobs.php');
                exit;
            } else {
                $errors[] = 'Failed to save job. Please try again.';
                $stmt->close();
                $conn->close();
            }
        } else {
            $dbErr = $conn->error ?? 'unknown error';
            error_log('Add Job prepare failed: ' . $dbErr);
            $errors[] = 'Failed to prepare database statement: ' . htmlspecialchars($dbErr, ENT_QUOTES, 'UTF-8');
            $conn->close();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';

// helper for echoing values
function val(array $fields, string $key): string { return htmlspecialchars($fields[$key] ?? '', ENT_QUOTES, 'UTF-8'); }
?>

<style>
.add-job-page { display:flex; gap:24px; padding:24px; background:var(--cg-bg, #f4f7fb); min-height:100vh; }
.add-job-page .main-panel { flex:1; min-width:0; padding:0 4px 24px; }
.add-job-page .sidebar { width:248px; flex:0 0 248px; padding:18px 14px; border:1px solid rgba(255,255,255,.14); border-radius:20px; background:linear-gradient(155deg,#102c54 0%,#16497f 56%,#0c8879 130%); color:#fff; }
.add-job-page .brand-wrap { display:flex; align-items:center; gap:10px; }
.add-job-page .brand-wrap > div { min-width:0; }
.add-job-page .brand-mark-sm { flex:0 0 42px; width:42px; height:42px; padding:4px; border-radius:12px; background:#fff; }
.add-job-page .brand-title { color:#fff; font-size:.82rem; font-weight:800; }
.add-job-page .brand-subtitle { color:rgba(232,244,255,.72); font-size:.7rem; }
.add-job-page .sidebar-nav { display:grid; gap:4px; margin-top:10px !important; }
.add-job-page .nav-link-admin { position:relative; display:flex; align-items:center; gap:12px; padding:11px 12px; border-radius:11px; color:rgba(235,246,255,.8); font-size:.92rem; font-weight:650; text-decoration:none; transition:background .2s ease,color .2s ease,transform .2s ease; }
.add-job-page .nav-link-admin i { width:20px; font-size:1.08rem; text-align:center; }
.add-job-page .nav-link-admin:hover, .add-job-page .nav-link-admin:focus { color:#fff; background:rgba(255,255,255,.12); transform:translateX(2px); }
.add-job-page .nav-link-admin.active { color:#fff; background:rgba(255,255,255,.18); box-shadow:inset 0 1px 0 rgba(255,255,255,.12); }
.add-job-page .nav-link-admin.active::before { content:""; position:absolute; left:0; width:4px; height:24px; border-radius:0 5px 5px 0; background:#54e2a6; }
.add-job-page .sidebar-footer { margin-top:20px; padding-top:14px; border-top:1px solid #2c3a50; }
.add-job-page .sidebar-footer .nav-link-admin { display:block; }
.add-job-page .sidebar .brand-wrap { border-bottom-color:#2c3a50; }
.add-job-page .sidebar-footer .nav-link-admin:last-child { color:#ffd0cd; }
.add-job-page .sidebar-footer .nav-link-admin:last-child:hover { color:#fff; background:rgba(220,38,38,.2); }
.add-job-page .admin-topbar { margin-bottom:20px; }
.add-job-page .job-form-hero { display:flex; justify-content:space-between; align-items:center; gap:20px; flex-wrap:wrap; padding:26px 28px; margin-bottom:24px; border-radius:18px; background:linear-gradient(118deg,#102c54 0%,#16497f 56%,#0c8879 130%); box-shadow:0 12px 28px rgba(20,57,102,.14); }
.add-job-page .job-form-hero h2 { color:#fff; font-size:clamp(1.5rem,2.5vw,2rem); font-weight:750; letter-spacing:-.035em; }
.add-job-page .job-form-hero .text-soft { color:#d2e5f5; margin-top:6px; }
.add-job-page .job-form-hero .btn { color:#fff; background:rgba(255,255,255,.1); border-color:rgba(255,255,255,.35); }
.add-job-page .job-form-hero .btn:hover { color:#102c54; background:#fff; }
.add-job-page .section { background:var(--cg-white); border:1px solid var(--cg-border); padding:24px; border-radius:16px; box-shadow:0 6px 22px rgba(15,23,42,.04); }
.add-job-page .section + .section { margin-top:20px; }
.add-job-page .section h5 { display:flex; align-items:center; gap:10px; padding-bottom:16px; margin:0; border-bottom:1px solid var(--cg-border); color:var(--cg-text); font-size:1.05rem; font-weight:700; }
.add-job-page .section h5 i { display:inline-flex; align-items:center; justify-content:center; width:36px; height:36px; border-radius:10px; background:rgba(13,110,253,.08); color:var(--cg-primary); }
.add-job-page .form-label { font-size:.88rem; font-weight:600; margin-bottom:8px; }
.add-job-page .form-control, .add-job-page .form-select { min-height:46px; border-radius:10px; font-size:.92rem; }
.add-job-page .form-control:focus, .add-job-page .form-select:focus { border-color:#287eaa; box-shadow:0 0 0 3px rgba(40,126,170,.12); }
.add-job-page .required { color:#d63384; }
.add-job-page .btn { border-radius:10px; font-weight:600; }
.add-job-page .job-form-actions { padding:18px 24px; border:1px solid var(--cg-border); border-radius:14px; background:var(--cg-white); }
@media (min-width:768px) {
    .add-job-page .section:first-of-type .row > div:first-child { width:100%; }
    .add-job-page .section:first-of-type .row > .col-md-2,
    .add-job-page .section:first-of-type .row > .col-md-3 { width:33.333333%; }
    .add-job-page .section:nth-of-type(2) .row > div { width:50%; }
}
@media (max-width:767.98px) {
    .add-job-page { padding:12px; }
    .add-job-page .main-panel { padding:0 0 20px; }
    .add-job-page .job-form-hero { padding:20px; }
    .add-job-page .section { padding:18px; }
    .add-job-page .job-form-actions { padding:16px; gap:12px; }
}
</style>

<main class="container-fluid admin-root add-job-page">
    <aside class="sidebar">
        <div class="sidebar-branding brand-wrap">
            <span class="brand-mark brand-mark-sm">
                <a href="dashboard.php" aria-label="Admin dashboard"><img src="../assets/images/logo.webp" alt="Career Grow Infotech logo" width="34" height="34" loading="lazy"></a>
            </span>
            <div>
                <div class="brand-title">Career Grow Infotech</div>
                <div class="brand-subtitle">Admin Portal</div>
            </div>
        </div>

        <div class="sidebar-navigation">
            <nav class="sidebar-nav" aria-label="Sidebar navigation">
                <a href="dashboard.php" class="nav-link-admin"><i class="bi bi-speedometer2"></i> Dashboard</a>
                <a href="jobs.php" class="nav-link-admin active"><i class="bi bi-briefcase"></i> Jobs</a>
                <a href="applicants.php" class="nav-link-admin"><i class="bi bi-people"></i> Applicants</a>
                <a href="candidate-details.php" class="nav-link-admin"><i class="bi bi-person-badge"></i> Candidates</a>
                <a href="contact-messages.php" class="nav-link-admin"><i class="bi bi-envelope-paper"></i> Contact Messages</a>
                <a href="settings.php" class="nav-link-admin"><i class="bi bi-gear"></i> Settings</a>
            </nav>

            <div class="sidebar-footer">
                <a href="../index.php" class="nav-link-admin"><i class="bi bi-house"></i> Back to Homepage</a>
                <a href="../logout.php" class="nav-link-admin"><i class="bi bi-box-arrow-right"></i> Logout</a>
            </div>
        </div>
    </aside>
    <section class="main-panel">
    <?php
    $pageH1 = 'Add New Job';
    require_once __DIR__ . '/../includes/admin-header.php';
    ?>
    <div class="job-form-hero">
        <div>
            <h2 class="mb-0">Create New Job</h2>
            <div class="text-soft small">Publish a new job posting</div>
        </div>
        <div>
            <a href="jobs.php" class="btn btn-outline-secondary">Back to Jobs</a>
        </div>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $e) { echo '<div>' . htmlspecialchars($e, ENT_QUOTES, 'UTF-8') . '</div>'; } ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="add_job_token" value="<?php echo htmlspecialchars($_SESSION['add_job_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">

        <div class="section">
            <h5><i class="bi bi-briefcase" aria-hidden="true"></i>Basic Information</h5>
            <div class="row g-3 mt-2">
                <div class="col-md-8">
                    <label class="form-label">Job Title <span class="required">*</span></label>
                    <input name="title" value="<?php echo val($fields,'title'); ?>" class="form-control" required>
                </div>

                <div class="col-md-8">
                    <label class="form-label">Company Name <span class="required">*</span></label>
                    <input name="company" value="<?php echo val($fields,'company'); ?>" class="form-control" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Location <span class="required">*</span></label>
                    <input name="location" value="<?php echo val($fields,'location'); ?>" class="form-control" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Job Type <span class="required">*</span></label>
                    <select name="job_type" class="form-select" required>
                        <option value="">Select job type</option>
                        <?php $types = ['Full Time','Part Time','Contract','Internship','Remote']; foreach($types as $t): ?>
                            <option value="<?php echo htmlspecialchars($t,ENT_QUOTES,'UTF-8'); ?>" <?php if ($fields['job_type']===$t) echo 'selected'; ?>><?php echo htmlspecialchars($t,ENT_QUOTES,'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Category <span class="required">*</span></label>
                    <select name="category" class="form-select" required>
                        <option value="">Select category</option>
                        <?php $cats = ['IT & Software','Sales & Marketing','Human Resources','Finance & Accounting','Engineering','Customer Support','Operations & Management','Other Opportunities']; foreach($cats as $c): ?>
                            <option value="<?php echo htmlspecialchars($c,ENT_QUOTES,'UTF-8'); ?>" <?php if ($fields['category']===$c) echo 'selected'; ?>><?php echo htmlspecialchars($c,ENT_QUOTES,'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Experience Level <span class="required">*</span></label>
                    <select name="experience_level" class="form-select" required>
                        <option value="">Select experience</option>
                        <?php $exps = ['Fresher','0-1','1-3','3-5','5-8','8+']; foreach($exps as $e): ?>
                            <option value="<?php echo htmlspecialchars($e,ENT_QUOTES,'UTF-8'); ?>" <?php if ($fields['experience_level']===$e) echo 'selected'; ?>><?php echo htmlspecialchars($e,ENT_QUOTES,'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Vacancies <span class="required">*</span></label>
                    <input name="openings" value="<?php echo val($fields,'openings'); ?>" class="form-control" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Last Date</label>
                    <input type="date" name="last_date" value="<?php echo val($fields,'last_date'); ?>" class="form-control">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Status <span class="required">*</span></label>
                    <select name="status" class="form-select" required>
                        <option value="active" <?php if ($fields['status']==='active') echo 'selected'; ?>>Active</option>
                        <option value="inactive" <?php if ($fields['status']==='inactive') echo 'selected'; ?>>Inactive</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="section">
            <h5><i class="bi bi-cash-stack" aria-hidden="true"></i>Compensation</h5>
            <div class="row g-3 mt-2">
                <div class="col-md-3">
                    <label class="form-label">Salary Min</label>
                    <input name="salary_min" value="<?php echo val($fields,'salary_min'); ?>" class="form-control" placeholder="e.g. 15000">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Salary Max</label>
                    <input name="salary_max" value="<?php echo val($fields,'salary_max'); ?>" class="form-control" placeholder="e.g. 30000">
                </div>
            </div>
        </div>

        <div class="section">
            <h5><i class="bi bi-file-earmark-text" aria-hidden="true"></i>Details</h5>
            <div class="row g-3 mt-2">
                <div class="col-12">
                    <label class="form-label">Job Description</label>
                    <textarea name="description" class="form-control" rows="6"><?php echo val($fields,'description'); ?></textarea>
                </div>

                <div class="col-12">
                    <label class="form-label">Skills / Qualifications</label>
                    <input name="skills_required" value="<?php echo val($fields,'skills_required'); ?>" class="form-control" placeholder="Comma separated skills">
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end align-items-center gap-3 flex-wrap mt-3 job-form-actions">
            <a href="jobs.php" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Job</button>
        </div>
    </form>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
