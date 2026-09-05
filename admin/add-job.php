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
    'description' => '',
    'skills_required' => '',
    'location' => '',
    'job_type' => '',
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
    if ($fields['location'] === '') { $errors[] = 'Location is required.'; }
    if ($fields['job_type'] === '') { $errors[] = 'Job type is required.'; }
    if ($fields['experience_level'] === '') { $errors[] = 'Experience level is required.'; }
    if ($fields['openings'] === '' || !ctype_digit($fields['openings'])) { $errors[] = 'Vacancies must be a number.'; }
    if ($fields['status'] === '') { $errors[] = 'Status is required.'; }

    if (empty($errors)) {
        $conn = getDbConnection();

        $sql = "INSERT INTO jobs (title, description, skills_required, location, job_type, experience_level, salary_min, salary_max, openings, last_date, status) VALUES (?,?,?,?,?,?,NULLIF(?,''),NULLIF(?,''),?,?,?)";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            // 11 placeholders total: 8 strings, 1 integer, 2 strings
            $types = 'ssssssssiss';
            $bindParams = [
                $fields['title'],
                $fields['description'],
                $fields['skills_required'],
                $fields['location'],
                $fields['job_type'],
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
            $errors[] = 'Failed to prepare database statement.';
            $conn->close();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';

// helper for echoing values
function val(array $fields, string $key): string { return htmlspecialchars($fields[$key] ?? '', ENT_QUOTES, 'UTF-8'); }
?>

<style>
/* Add Job form styles */
.admin-form { max-width:900px; margin: 1.5rem auto; }
.section { background: var(--cg-white); border:1px solid var(--cg-border); padding:1rem; border-radius:.6rem; box-shadow: 0 10px 30px rgba(15,23,42,0.04); }
.section + .section { margin-top:1rem; }
.required { color: #d63384; }
@media (max-width:767.98px) { .admin-form { padding: .5rem; } }
</style>

<main class="container admin-form">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-0">Add New Job</h2>
            <div class="text-soft small">Create a new job posting</div>
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
            <h5>Basic Information</h5>
            <div class="row g-3 mt-2">
                <div class="col-md-8">
                    <label class="form-label">Job Title <span class="required">*</span></label>
                    <input name="title" value="<?php echo val($fields,'title'); ?>" class="form-control" required>
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
            <h5>Compensation</h5>
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
            <h5>Details</h5>
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

        <div class="d-flex justify-content-between align-items-center mt-3">
            <a href="jobs.php" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Job</button>
        </div>
    </form>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
