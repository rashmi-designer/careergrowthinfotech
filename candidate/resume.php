<?php
declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = 'Upload Resume - Career Grow Infotech';

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

$userId = (int)$_SESSION['user_id'];
$errors = [];
$success = false;

$conn = getDbConnection();
$stmt = $conn->prepare('SELECT resume FROM candidate_profiles WHERE user_id = ? LIMIT 1');
$stmt->bind_param('i', $userId);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['resume']) || $_FILES['resume']['error'] === UPLOAD_ERR_NO_FILE) {
        $errors[] = 'Please select a file to upload.';
    } else {
        $file = $_FILES['resume'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        $allowedMimes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'File upload error. Please try again.';
        } elseif ($file['size'] > $maxSize) {
            $errors[] = 'File size must not exceed 5MB.';
        } elseif (!in_array($file['type'], $allowedMimes)) {
            $errors[] = 'Only PDF and DOC/DOCX files are allowed.';
        } else {
            $uploadDir = __DIR__ . '/../uploads/resumes/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileExt = pathinfo($file['name'], PATHINFO_EXTENSION);
            $fileName = 'resume_' . $userId . '_' . time() . '.' . $fileExt;
            $uploadPath = $uploadDir . $fileName;
            $relativePath = 'uploads/resumes/' . $fileName;

            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                // Update database
                $conn = getDbConnection();
                $updateStmt = $conn->prepare('UPDATE candidate_profiles SET resume = ?, updated_at = NOW() WHERE user_id = ?');
                if ($updateStmt) {
                    $updateStmt->bind_param('si', $relativePath, $userId);
                    if ($updateStmt->execute()) {
                        $success = true;
                        $profile['resume'] = $relativePath;
                    } else {
                        $errors[] = 'Failed to save resume. Please try again.';
                        unlink($uploadPath);
                    }
                    $updateStmt->close();
                }
                $conn->close();
            } else {
                $errors[] = 'Failed to upload file. Please try again.';
            }
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<style>
.resume-wrapper {
    background: linear-gradient(135deg, var(--cg-primary-soft) 0%, #ffffff 100%);
    padding: 3rem 0;
    min-height: calc(100vh - 200px);
}

.resume-container {
    max-width: 700px;
    margin: 0 auto;
    padding: 0 1rem;
}

.resume-section {
    background: var(--cg-white);
    border: 1px solid var(--cg-border);
    border-radius: 1.25rem;
    padding: 2.5rem;
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
}

.resume-section h2 {
    font-weight: 700;
    margin-bottom: 0.5rem;
    color: var(--cg-text);
}

.resume-section-subtitle {
    color: var(--cg-muted);
    font-size: 0.95rem;
    margin-bottom: 2rem;
}

/* Alerts */
.alert {
    border-radius: 0.75rem;
    padding: 1rem 1.25rem;
    margin-bottom: 1.5rem;
    border: none;
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
}

.alert-success {
    background-color: #ecfdf5;
    color: #065f46;
    border-left: 4px solid #10b981;
}

.alert-danger {
    background-color: #fef2f2;
    color: #7f1d1d;
    border-left: 4px solid #ef4444;
}

.alert-info {
    background-color: #eff6ff;
    color: #1e40af;
    border-left: 4px solid #3b82f6;
}

.alert i {
    font-size: 1.25rem;
    flex-shrink: 0;
    margin-top: 0.125rem;
}

.alert-content {
    flex: 1;
}

.alert-content strong {
    display: block;
    margin-bottom: 0.25rem;
}

.alert-content ul {
    margin: 0;
    padding-left: 1.5rem;
}

.alert-content li {
    margin-bottom: 0.5rem;
}

/* Current Resume Display */
.current-resume {
    background: var(--cg-light);
    border: 1px solid var(--cg-border);
    border-radius: 1rem;
    padding: 1.5rem;
    margin-bottom: 2rem;
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.current-resume-icon {
    font-size: 3rem;
    color: var(--cg-primary);
    flex-shrink: 0;
}

.current-resume-info h3 {
    margin: 0 0 0.5rem 0;
    color: var(--cg-text);
    font-weight: 600;
}

.current-resume-info p {
    margin: 0 0 1rem 0;
    color: var(--cg-muted);
    font-size: 0.9rem;
}

.current-resume-actions {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.current-resume-actions .btn {
    padding: 0.5rem 1rem;
    font-size: 0.85rem;
}

/* Upload Area */
.upload-area {
    border: 2px dashed var(--cg-border);
    border-radius: 1rem;
    padding: 3rem 1.5rem;
    text-align: center;
    background: #f9fafb;
    transition: all 0.3s ease;
    cursor: pointer;
    margin-bottom: 2rem;
}

.upload-area:hover {
    border-color: var(--cg-primary);
    background-color: #f0f9ff;
}

.upload-area.dragover {
    border-color: var(--cg-primary);
    background-color: #eff6ff;
    box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
}

.upload-icon {
    font-size: 3rem;
    color: var(--cg-primary);
    margin-bottom: 1rem;
}

.upload-text h3 {
    margin: 0 0 0.5rem 0;
    color: var(--cg-text);
    font-weight: 600;
}

.upload-text p {
    margin: 0;
    color: var(--cg-muted);
    font-size: 0.9rem;
}

.upload-text .highlight {
    color: var(--cg-primary);
    font-weight: 600;
}

/* File Input */
.file-input-wrapper {
    position: relative;
    margin-bottom: 2rem;
}

.file-input {
    display: none;
}

.file-input-label {
    display: inline-block;
    background: var(--cg-primary);
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 0.75rem;
    cursor: pointer;
    font-weight: 600;
    transition: background 0.2s ease;
}

.file-input-label:hover {
    background: var(--cg-primary-dark);
}

.file-info {
    margin-top: 1rem;
    padding: 1rem;
    background: #f0f9ff;
    border-radius: 0.75rem;
    border-left: 4px solid var(--cg-primary);
    font-size: 0.85rem;
    color: var(--cg-muted);
}

.file-info i {
    margin-right: 0.5rem;
    color: var(--cg-primary);
}

/* Form Buttons */
.form-actions {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    padding-top: 2rem;
    border-top: 1px solid var(--cg-border);
}

.form-actions .btn {
    padding: 0.85rem 2rem;
    font-weight: 600;
    font-size: 0.95rem;
    min-width: 140px;
}

/* Back Link */
.back-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--cg-primary);
    text-decoration: none;
    font-weight: 600;
    margin-bottom: 2rem;
    transition: color 0.2s ease;
}

.back-link:hover {
    color: var(--cg-primary-dark);
}

/* Responsive */
@media (max-width: 768px) {
    .resume-wrapper {
        padding: 2rem 0;
    }

    .resume-section {
        padding: 1.75rem;
    }

    .resume-section h2 {
        font-size: 1.5rem;
    }

    .current-resume {
        flex-direction: column;
        text-align: center;
    }

    .current-resume-actions {
        width: 100%;
        justify-content: center;
    }

    .form-actions {
        flex-direction: column;
    }

    .form-actions .btn {
        width: 100%;
        min-width: auto;
    }

    .upload-area {
        padding: 2rem 1rem;
    }
}

@media (max-width: 576px) {
    .resume-container {
        padding: 0 0.75rem;
    }

    .resume-section {
        padding: 1.5rem;
    }

    .resume-section h2 {
        font-size: 1.25rem;
    }

    .upload-icon {
        font-size: 2.5rem;
        margin-bottom: 0.75rem;
    }

    .current-resume-icon {
        font-size: 2.5rem;
    }

    .upload-area {
        padding: 1.5rem 1rem;
    }

    .upload-text h3 {
        margin-bottom: 0.25rem;
        font-size: 1rem;
    }

    .file-info {
        font-size: 0.8rem;
        padding: 0.75rem;
    }
}
</style>

<div class="resume-wrapper">
    <main class="resume-container py-4">
        <a href="profile.php" class="back-link">
            <i class="bi bi-arrow-left"></i> Back to Profile
        </a>

        <div class="resume-section">
            <h2>Upload Your Resume</h2>
            <p class="resume-section-subtitle">A strong resume increases your chances of getting selected by employers</p>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i>
                    <div class="alert-content">
                        <strong>Success!</strong> Your resume has been uploaded successfully.
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-circle"></i>
                    <div class="alert-content">
                        <strong>Upload Error:</strong>
                        <ul>
                            <?php foreach ($errors as $e): ?>
                                <li><?php echo htmlspecialchars($e, ENT_QUOTES, 'UTF-8'); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($profile['resume'])): ?>
                <div class="current-resume">
                    <div class="current-resume-icon">
                        <i class="bi bi-file-earmark-pdf"></i>
                    </div>
                    <div class="current-resume-info">
                        <h3>Current Resume</h3>
                        <p><?php echo htmlspecialchars(basename($profile['resume']), ENT_QUOTES, 'UTF-8'); ?></p>
                        <div class="current-resume-actions">
                            <a href="../<?php echo htmlspecialchars($profile['resume'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" class="btn btn-primary btn-sm">
                                <i class="bi bi-download"></i> Download
                            </a>
                            <a href="profile.php" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-arrow-left"></i> View Profile
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data">
                <div class="file-input-wrapper">
                    <label for="resume" class="upload-area" id="uploadArea">
                        <div class="upload-icon">
                            <i class="bi bi-cloud-upload"></i>
                        </div>
                        <div class="upload-text">
                            <h3>Drag and drop your resume here</h3>
                            <p>or click to <span class="highlight">browse</span> your computer</p>
                        </div>
                    </label>
                    <input type="file" id="resume" name="resume" class="file-input" accept=".pdf,.doc,.docx" required>
                </div>

                <div class="file-info">
                    <i class="bi bi-info-circle"></i>
                    Supported formats: <strong>PDF, DOC, DOCX</strong> | Maximum size: <strong>5MB</strong>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                        <i class="bi bi-cloud-upload"></i>
                        Upload Resume
                    </button>
                    <a href="profile.php" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i>
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('resume');
    const uploadArea = document.getElementById('uploadArea');
    const submitBtn = document.getElementById('submitBtn');

    // Update submit button state based on file selection
    fileInput.addEventListener('change', function() {
        submitBtn.disabled = !this.files || this.files.length === 0;
    });

    // Drag and drop functionality
    uploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        uploadArea.classList.add('dragover');
    });

    uploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        uploadArea.classList.remove('dragover');
    });

    uploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        uploadArea.classList.remove('dragover');

        const files = e.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files;
            submitBtn.disabled = false;
        }
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
