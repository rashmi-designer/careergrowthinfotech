<?php
declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = 'Apply for Job - Career Grow Infotech';

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

$userId = (int)$_SESSION['user_id'];
$jobId = isset($_GET['job_id']) ? (int)$_GET['job_id'] : 0;

// Validate job_id
if ($jobId <= 0) {
    echo '<div class="container py-5"><div class="alert alert-danger">Invalid job ID.</div></div>';
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

$conn = getDbConnection();
$job = null;
$errors = [];
$success = false;
$applicationId = 0;

// Fetch job details (only active jobs available)
$publicStatus = 'active';
$jobStmt = $conn->prepare('SELECT id, title, description, skills_required, location, job_type, experience_level, salary_min, salary_max, openings, last_date, status, created_at FROM jobs WHERE id = ? AND status = ? AND (last_date IS NULL OR last_date >= CURDATE()) LIMIT 1');
if ($jobStmt) {
    $jobStmt->bind_param('is', $jobId, $publicStatus);
    $jobStmt->execute();
    $job = $jobStmt->get_result()->fetch_assoc();
    $jobStmt->close();
}

// If job not found, show error
if (!$job) {
    echo '<div class="container py-5"><div class="alert alert-danger">The job you are trying to apply for is not available.</div></div>';
    $conn->close();
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

// Check if candidate has already applied to this job
$alreadyApplied = false;
$checkStmt = $conn->prepare('SELECT 1 FROM applications WHERE user_id = ? AND job_id = ? LIMIT 1');
if ($checkStmt) {
    $checkStmt->bind_param('ii', $userId, $jobId);
    $checkStmt->execute();
    $alreadyApplied = $checkStmt->get_result()->fetch_assoc() !== null;
    $checkStmt->close();
}

// Fetch candidate profile for resume
$profile = null;
$profileStmt = $conn->prepare('SELECT u.name, u.email, u.phone, cp.skills, cp.location, cp.qualification, cp.experience, cp.resume FROM users u LEFT JOIN candidate_profiles cp ON u.id = cp.user_id WHERE u.id = ? LIMIT 1');
if ($profileStmt) {
    $profileStmt->bind_param('i', $userId);
    $profileStmt->execute();
    $profile = $profileStmt->get_result()->fetch_assoc();
    $profileStmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$alreadyApplied) {
    // Validate form input
    $resume = isset($_FILES['resume']) ? $_FILES['resume'] : null;
    $hasResume = $resume && $resume['error'] === UPLOAD_ERR_OK;
    $resumePath = null;

    // Check for duplicate application one more time (security)
    $finalCheckStmt = $conn->prepare('SELECT 1 FROM applications WHERE user_id = ? AND job_id = ? LIMIT 1');
    if ($finalCheckStmt) {
        $finalCheckStmt->bind_param('ii', $userId, $jobId);
        $finalCheckStmt->execute();
        if ($finalCheckStmt->get_result()->fetch_assoc() !== null) {
            $errors[] = 'You have already applied for this job.';
        }
        $finalCheckStmt->close();
    }

    // Process resume if provided
    if ($hasResume && empty($errors)) {
        $maxSize = 5 * 1024 * 1024; // 5MB
        $allowedMimes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];

        if ($resume['size'] > $maxSize) {
            $errors[] = 'Resume file size must not exceed 5MB.';
        } elseif (!in_array($resume['type'], $allowedMimes, true)) {
            $errors[] = 'Only PDF and DOC/DOCX files are allowed for resume.';
        } else {
            $uploadDir = __DIR__ . '/../uploads/resumes/';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0755, true);
            }

            $fileExt = strtolower(pathinfo($resume['name'], PATHINFO_EXTENSION));
            $fileName = 'resume_' . $userId . '_' . time() . '.' . $fileExt;
            $uploadPath = $uploadDir . $fileName;

            if (move_uploaded_file($resume['tmp_name'], $uploadPath)) {
                $resumePath = 'uploads/resumes/' . $fileName;
            } else {
                $errors[] = 'Failed to upload resume. Please try again.';
            }
        }
    }

    // Insert application if no errors
    if (empty($errors)) {
        $appStmt = $conn->prepare('INSERT INTO applications (user_id, job_id, resume, status) VALUES (?, ?, ?, ?)');
        if ($appStmt) {
            $status = 'New Applied';
            $appStmt->bind_param('iiss', $userId, $jobId, $resumePath, $status);
            if ($appStmt->execute()) {
                $applicationId = $conn->insert_id;
                $success = true;
            } else {
                // Check if it's a duplicate application error
                if (strpos($appStmt->error, 'Duplicate entry') !== false || strpos($appStmt->error, 'uq_applications_user_job') !== false) {
                    $errors[] = 'You have already applied for this job.';
                } else {
                    $errors[] = 'Failed to submit application. Please try again.';
                }
            }
            $appStmt->close();
        } else {
            $errors[] = 'Database error. Please try again later.';
        }
    }
}

$conn->close();

function formatMoney(?string $value): string {
    if ($value === null || trim($value) === '') {
        return 'Not disclosed';
    }
    $amount = (float)$value;
    if ($amount <= 0) {
        return 'Not disclosed';
    }
    return '₹' . number_format($amount, 0, '.', ',');
}

function formatDate(?string $value, string $fallback = 'N/A'): string {
    if ($value === null || trim($value) === '') {
        return $fallback;
    }
    $timestamp = strtotime((string)$value);
    if ($timestamp === false) {
        return $fallback;
    }
    return date('d M Y', $timestamp);
}
?>

<style>
.apply-page { padding-top: 2rem; padding-bottom: 4rem; }
.apply-container { max-width: 900px; margin: 0 auto; }
.apply-header { background: var(--cg-white); border: 1px solid var(--cg-border); border-radius: 1rem; padding: 2rem; margin-bottom: 2rem; }
.apply-header h2 { font-weight: 700; margin-bottom: 1rem; }
.job-summary { display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; }
.job-info p { margin-bottom: 0.5rem; color: var(--cg-text); line-height: 1.5; }
.job-info strong { color: var(--cg-accent); font-weight: 600; }
.job-meta { background: var(--cg-light); border-radius: 0.5rem; padding: 1.25rem; }
.job-meta h4 { color: var(--cg-accent); font-weight: 700; margin-bottom: 1rem; margin-top: 0; }
.meta-item { display: flex; justify-content: space-between; gap: 1rem; padding: 0.5rem 0; border-bottom: 1px solid rgba(15,23,42,0.08); }
.meta-item:last-child { border-bottom: none; }
.meta-label { color: var(--cg-muted); font-size: 0.9rem; }
.meta-value { font-weight: 600; color: var(--cg-accent); }
.apply-form { background: var(--cg-white); border: 1px solid var(--cg-border); border-radius: 1rem; padding: 2rem; }
.apply-form h3 { font-weight: 700; color: var(--cg-accent); margin-bottom: 1.5rem; }
.form-section { margin-bottom: 2rem; }
.form-section h4 { font-size: 1rem; font-weight: 600; color: var(--cg-accent); margin-bottom: 1rem; }
.form-group { margin-bottom: 1.25rem; }
.form-group label { display: block; font-weight: 500; margin-bottom: 0.5rem; color: var(--cg-text); }
.form-group input, .form-group select { min-height: 44px; }
.form-static { background: var(--cg-light); padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem; }
.form-static p { margin: 0; color: var(--cg-text); }
.file-input-label { display: inline-block; padding: 0.75rem 1rem; background: var(--cg-primary); color: white; border-radius: 0.35rem; cursor: pointer; font-weight: 500; }
.file-input-label:hover { background: #0b5ed7; }
#resume { display: none; }
.file-name { color: var(--cg-muted); font-size: 0.9rem; margin-top: 0.5rem; }
.form-actions { display: flex; gap: 1rem; justify-content: space-between; margin-top: 2rem; }
.form-actions .btn { min-width: 150px; }
.success-container { max-width: 600px; margin: 3rem auto; text-align: center; }
.success-icon { font-size: 3.5rem; color: #28a745; margin-bottom: 1rem; }
.success-message { background: var(--cg-white); border: 1px solid var(--cg-border); border-radius: 1rem; padding: 2rem; margin-bottom: 2rem; }
.success-message h2 { color: #28a745; margin-bottom: 1rem; }
.application-details { background: var(--cg-light); padding: 1.5rem; border-radius: 0.5rem; margin-bottom: 1.5rem; text-align: left; }
.detail-row { display: flex; justify-content: space-between; gap: 1rem; padding: 0.75rem 0; border-bottom: 1px solid rgba(15,23,42,0.08); }
.detail-row:last-child { border-bottom: none; }
.detail-label { color: var(--cg-muted); font-weight: 500; }
.detail-value { color: var(--cg-accent); font-weight: 600; }
@media (max-width: 768px) {
    .job-summary { grid-template-columns: 1fr; }
    .apply-header, .apply-form { padding: 1.25rem; }
    .form-actions { flex-direction: column; }
    .form-actions .btn { width: 100%; }
}
</style>

<main class="apply-page">
    <div class="apply-container">
        <?php if ($success): ?>
            <!-- Success State -->
            <div class="success-container">
                <div class="success-icon">✓</div>
                <div class="success-message">
                    <h2>Application Submitted Successfully!</h2>
                    <p class="text-muted">Your application has been received by the Career Grow Infotech recruitment team.</p>
                    
                    <div class="application-details">
                        <div class="detail-row">
                            <span class="detail-label">Position Applied:</span>
                            <span class="detail-value"><?php echo htmlspecialchars((string)$job['title'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Location:</span>
                            <span class="detail-value"><?php echo htmlspecialchars((string)($job['location'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Application Date:</span>
                            <span class="detail-value"><?php echo date('d M Y, h:i A'); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Application ID:</span>
                            <span class="detail-value">#<?php echo $applicationId; ?></span>
                        </div>
                    </div>

                    <div class="text-muted mb-3">
                        <p>We will review your application and contact you soon if you match our requirements.</p>
                    </div>

                    <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                        <a href="applications.php" class="btn btn-primary">View My Applications</a>
                        <a href="../jobs.php" class="btn btn-outline-primary">Browse More Jobs</a>
                    </div>
                </div>
            </div>

        <?php elseif ($alreadyApplied): ?>
            <!-- Already Applied State -->
            <div class="apply-header">
                <div class="alert alert-warning mb-0">
                    <h4 class="alert-heading">Already Applied</h4>
                    <p class="mb-0">You have already submitted an application for the <strong><?php echo htmlspecialchars((string)$job['title'], ENT_QUOTES, 'UTF-8'); ?></strong> position. You can view your application status in your <a href="applications.php">applications</a>.</p>
                </div>
            </div>
            <div class="text-center mt-4">
                <a href="../jobs.php" class="btn btn-outline-primary">Browse Other Jobs</a>
                <a href="applications.php" class="btn btn-primary">View My Applications</a>
            </div>

        <?php else: ?>
            <!-- Application Form -->
            <div class="apply-header">
                <h2>Apply for Position</h2>
                <div class="job-summary">
                    <div class="job-info">
                        <h3 style="margin-top: 0; margin-bottom: 1rem; color: var(--cg-accent);"><?php echo htmlspecialchars((string)$job['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p><strong>Location:</strong> <?php echo htmlspecialchars((string)($job['location'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><strong>Job Type:</strong> <?php echo htmlspecialchars((string)($job['job_type'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><strong>Experience Level:</strong> <?php echo htmlspecialchars((string)($job['experience_level'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><strong>Salary:</strong> <?php 
                            $salaryText = 'Not disclosed';
                            if (!empty($job['salary_min']) || !empty($job['salary_max'])) {
                                if (!empty($job['salary_min']) && !empty($job['salary_max'])) {
                                    $salaryText = formatMoney($job['salary_min']) . ' - ' . formatMoney($job['salary_max']);
                                } elseif (!empty($job['salary_min'])) {
                                    $salaryText = formatMoney($job['salary_min']);
                                } elseif (!empty($job['salary_max'])) {
                                    $salaryText = 'Up to ' . formatMoney($job['salary_max']);
                                }
                            }
                            echo htmlspecialchars($salaryText, ENT_QUOTES, 'UTF-8');
                        ?></p>
                    </div>
                    <div class="job-meta">
                        <h4>Quick Info</h4>
                        <div class="meta-item">
                            <span class="meta-label">Posted:</span>
                            <span class="meta-value"><?php echo htmlspecialchars(formatDate((string)($job['created_at'] ?? '')), ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Openings:</span>
                            <span class="meta-value"><?php echo (int)($job['openings'] ?? 0); ?></span>
                        </div>
                        <?php if (!empty($job['last_date'])): ?>
                            <div class="meta-item">
                                <span class="meta-label">Last Date:</span>
                                <span class="meta-value"><?php echo htmlspecialchars(formatDate((string)$job['last_date']), ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger" style="margin-bottom: 2rem;">
                    <strong>Error:</strong>
                    <?php foreach ($errors as $e): ?>
                        <div><?php echo htmlspecialchars($e, ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="apply-form">
                <h3>Your Application</h3>
                <form method="post" enctype="multipart/form-data" novalidate>
                    <!-- Candidate Information Section -->
                    <div class="form-section">
                        <h4>Your Information</h4>
                        <div class="form-static">
                            <p><strong>Name:</strong> <?php echo htmlspecialchars((string)($profile['name'] ?? 'Not set'), ENT_QUOTES, 'UTF-8'); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars((string)($profile['email'] ?? 'Not set'), ENT_QUOTES, 'UTF-8'); ?></p>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars((string)($profile['phone'] ?? 'Not set'), ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                        <div style="text-align: right;">
                            <a href="profile.php" class="btn btn-sm btn-outline-secondary">Update Profile</a>
                        </div>
                    </div>

                    <!-- Resume Section -->
                    <div class="form-section">
                        <h4>Resume <span style="color: #d63384;">*</span></h4>
                        <div class="form-group">
                            <p class="text-muted" style="margin-bottom: 1rem; font-size: 0.9rem;">Upload your resume in PDF or Word format (max 5MB)</p>
                            <input type="file" id="resume" name="resume" accept=".pdf,.doc,.docx" required>
                            <label for="resume" class="file-input-label">Select Resume File</label>
                            <div class="file-name"><span id="fileName">No file selected</span></div>
                        </div>
                        <?php if (!empty($profile['resume'])): ?>
                            <div class="alert alert-info" style="margin-top: 1rem;">
                                <small><strong>Profile Resume:</strong> You have a resume on file. You can upload a different one for this application, or leave blank to use your profile resume.</small>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <a href="../job-details.php?id=<?php echo (int)$job['id']; ?>" class="btn btn-outline-secondary">Back to Job Details</a>
                        <button type="submit" class="btn btn-primary">Submit Application</button>
                    </div>
                </form>
            </div>

        <?php endif; ?>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('resume');
    const fileNameSpan = document.getElementById('fileName');
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            fileNameSpan.textContent = this.files.length > 0 ? this.files[0].name : 'No file selected';
        });
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
