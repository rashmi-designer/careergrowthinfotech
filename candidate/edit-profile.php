<?php
declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = 'Edit Profile - Career Grow Infotech';

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

$userId = (int)$_SESSION['user_id'];
$conn = getDbConnection();

// Fetch current profile
$stmt = $conn->prepare('SELECT u.id, u.name, u.email, u.phone, cp.skills, cp.location, cp.qualification, cp.experience, cp.resume FROM users u LEFT JOIN candidate_profiles cp ON u.id = cp.user_id WHERE u.id = ? LIMIT 1');
$stmt->bind_param('i', $userId);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();
$stmt->close();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $skills = trim((string)($_POST['skills'] ?? ''));
    $location = trim((string)($_POST['location'] ?? ''));
    $qualification = trim((string)($_POST['qualification'] ?? ''));
    $experience = trim((string)($_POST['experience'] ?? ''));

    if (empty($errors)) {
        // Update candidate profile
        $profileStmt = $conn->prepare('UPDATE candidate_profiles SET skills = ?, location = ?, qualification = ?, experience = ?, updated_at = NOW() WHERE user_id = ?');
        if ($profileStmt) {
            $profileStmt->bind_param('ssssi', $skills, $location, $qualification, $experience, $userId);
            if ($profileStmt->execute()) {
                $success = true;
                // Refresh profile data
                $stmt = $conn->prepare('SELECT u.name, u.email, u.phone, cp.skills, cp.location, cp.qualification, cp.experience FROM users u LEFT JOIN candidate_profiles cp ON u.id = cp.user_id WHERE u.id = ? LIMIT 1');
                $stmt->bind_param('i', $userId);
                $stmt->execute();
                $profile = $stmt->get_result()->fetch_assoc();
                $stmt->close();
            } else {
                $errors[] = 'Failed to update profile. Please try again.';
            }
            $profileStmt->close();
        }
    }
}
$conn->close();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<style>
.edit-wrapper {
    background: linear-gradient(135deg, var(--cg-primary-soft) 0%, #ffffff 100%);
    padding: 3rem 0;
    min-height: calc(100vh - 200px);
}

.edit-container {
    max-width: 700px;
    margin: 0 auto;
    padding: 0 1rem;
}

.edit-section {
    background: var(--cg-white);
    border: 1px solid var(--cg-border);
    border-radius: 1.25rem;
    padding: 2.5rem;
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
}

.edit-section h2 {
    font-weight: 700;
    margin-bottom: 0.5rem;
    color: var(--cg-text);
}

.edit-section-subtitle {
    color: var(--cg-muted);
    font-size: 0.95rem;
    margin-bottom: 2rem;
}

/* Form Styling */
.form-group {
    margin-bottom: 1.75rem;
}

.form-label {
    display: block;
    font-weight: 600;
    color: var(--cg-text);
    margin-bottom: 0.5rem;
    font-size: 0.95rem;
}

.form-label .required {
    color: #dc2626;
}

.form-label-hint {
    display: block;
    color: var(--cg-muted);
    font-size: 0.85rem;
    font-weight: 400;
    margin-top: 0.25rem;
    font-style: italic;
}

input.form-control,
select.form-control,
textarea.form-control {
    min-height: 44px;
    border: 1px solid var(--cg-border);
    border-radius: 0.75rem;
    padding: 0.75rem 1rem;
    font-size: 1rem;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

input.form-control:focus,
select.form-control:focus,
textarea.form-control:focus {
    border-color: var(--cg-primary);
    box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
    outline: none;
}

textarea.form-control {
    resize: vertical;
    min-height: 100px;
    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    line-height: 1.5;
}

/* Alerts */
.alert {
    border-radius: 0.75rem;
    padding: 1rem 1.25rem;
    margin-bottom: 1.5rem;
    border: none;
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

.alert ul {
    margin: 0;
    padding-left: 1.5rem;
}

.alert li {
    margin-bottom: 0.5rem;
}

.alert li:last-child {
    margin-bottom: 0;
}

/* Buttons */
.form-actions {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid var(--cg-border);
}

.form-actions .btn {
    padding: 0.85rem 2rem;
    font-weight: 600;
    font-size: 0.95rem;
    min-width: 140px;
    transition: all 0.2s ease;
}

.form-actions .btn-primary {
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
}

.form-actions .btn-primary:hover {
    box-shadow: 0 6px 16px rgba(13, 110, 253, 0.4);
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
    .edit-wrapper {
        padding: 2rem 0;
    }

    .edit-section {
        padding: 1.75rem;
    }

    .edit-section h2 {
        font-size: 1.5rem;
    }

    .form-actions {
        flex-direction: column;
    }

    .form-actions .btn {
        width: 100%;
        min-width: auto;
    }
}

@media (max-width: 576px) {
    .edit-container {
        padding: 0 0.75rem;
    }

    .edit-section {
        padding: 1.5rem;
    }

    .edit-section h2 {
        font-size: 1.25rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        font-size: 0.9rem;
    }

    input.form-control,
    select.form-control,
    textarea.form-control {
        min-height: 40px;
        padding: 0.65rem 0.875rem;
        font-size: 16px;
    }

    textarea.form-control {
        min-height: 80px;
    }

    .form-actions .btn {
        padding: 0.75rem 1.5rem;
        font-size: 0.9rem;
    }
}
</style>

<div class="edit-wrapper">
    <main class="edit-container py-4">
        <a href="profile.php" class="back-link">
            <i class="bi bi-arrow-left"></i> Back to Profile
        </a>

        <div class="edit-section">
            <h2>Edit Your Profile</h2>
            <p class="edit-section-subtitle">Update your professional information to help employers find the right match</p>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i>
                    <strong>Success!</strong> Your profile has been updated successfully.
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-circle"></i>
                    <strong>Error:</strong>
                    <ul>
                        <?php foreach ($errors as $e): ?>
                            <li><?php echo htmlspecialchars($e, ENT_QUOTES, 'UTF-8'); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" novalidate>
                <div class="form-group">
                    <label for="location" class="form-label">
                        <i class="bi bi-geo-alt"></i>
                        Location / City
                    </label>
                    <input
                        type="text"
                        id="location"
                        name="location"
                        class="form-control"
                        placeholder="e.g., Bangalore, India"
                        value="<?php echo htmlspecialchars($profile['location'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                    >
                    <span class="form-label-hint">Where are you based? This helps employers find local matches.</span>
                </div>

                <div class="form-group">
                    <label for="qualification" class="form-label">
                        <i class="bi bi-mortarboard"></i>
                        Education / Qualification
                    </label>
                    <input
                        type="text"
                        id="qualification"
                        name="qualification"
                        class="form-control"
                        placeholder="e.g., B.Tech in Computer Science, MBA"
                        value="<?php echo htmlspecialchars($profile['qualification'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                    >
                    <span class="form-label-hint">Your highest qualification or degree</span>
                </div>

                <div class="form-group">
                    <label for="experience" class="form-label">
                        <i class="bi bi-briefcase"></i>
                        Experience Level
                    </label>
                    <select id="experience" name="experience" class="form-control">
                        <option value="">-- Select your experience level --</option>
                        <option value="Fresher" <?php echo ($profile['experience'] === 'Fresher') ? 'selected' : ''; ?>>Fresher</option>
                        <option value="0-1" <?php echo ($profile['experience'] === '0-1') ? 'selected' : ''; ?>>0-1 years</option>
                        <option value="1-3" <?php echo ($profile['experience'] === '1-3') ? 'selected' : ''; ?>>1-3 years</option>
                        <option value="3-5" <?php echo ($profile['experience'] === '3-5') ? 'selected' : ''; ?>>3-5 years</option>
                        <option value="5-8" <?php echo ($profile['experience'] === '5-8') ? 'selected' : ''; ?>>5-8 years</option>
                        <option value="8+" <?php echo ($profile['experience'] === '8+') ? 'selected' : ''; ?>>8+ years</option>
                    </select>
                    <span class="form-label-hint">Your professional experience</span>
                </div>

                <div class="form-group">
                    <label for="skills" class="form-label">
                        <i class="bi bi-lightning-charge"></i>
                        Skills (Comma-separated)
                    </label>
                    <textarea
                        id="skills"
                        name="skills"
                        class="form-control"
                        placeholder="e.g., PHP, MySQL, JavaScript, React, Python, AWS"
                    ><?php echo htmlspecialchars($profile['skills'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                    <span class="form-label-hint">List your technical and professional skills separated by commas. This helps employers find you.</span>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i>
                        Save Changes
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
