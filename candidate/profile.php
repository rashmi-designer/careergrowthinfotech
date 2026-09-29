<?php
declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = 'My Profile - Career Grow Infotech';

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

$userId = (int)$_SESSION['user_id'];
$conn = getDbConnection();

// Fetch complete profile with all candidate information
$stmt = $conn->prepare('SELECT u.id, u.name, u.email, u.phone, cp.skills, cp.location, cp.qualification, cp.experience, cp.resume FROM users u LEFT JOIN candidate_profiles cp ON u.id = cp.user_id WHERE u.id = ? LIMIT 1');
$stmt->bind_param('i', $userId);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Calculate profile completion percentage based on actual data
$profileFields = [
    !empty($profile['name']),
    !empty($profile['email']),
    !empty($profile['phone']),
    !empty($profile['location']),
    !empty($profile['experience']),
    !empty($profile['qualification']),
    !empty($profile['skills']),
    !empty($profile['resume']),
];
$completedFields = count(array_filter($profileFields));
$totalFields = count($profileFields);
$profileCompletion = ceil(($completedFields / $totalFields) * 100);

$conn->close();
?>

<style>
.profile-wrapper {
    background: linear-gradient(135deg, #eef4fb 0%, #f8fafc 65%, #f0f9f7 100%);
    padding: 3rem 0;
    min-height: calc(100vh - 200px);
}

.profile-container {
    max-width: 900px;
    margin: 0 auto;
    padding: 0 1rem;
}

/* Header Section */
.profile-header {
    background: linear-gradient(118deg, #102c54 0%, #16497f 56%, #0c8879 130%);
    border: 1px solid rgba(255,255,255,0.18);
    border-radius: 1.25rem;
    padding: 2.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 16px 36px rgba(20,57,102,0.16);
}

.profile-header-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 2rem;
    gap: 2rem;
}

.profile-header-title {
    flex: 1;
    min-width: 0;
    overflow-wrap: anywhere;
}

.profile-header-title h1 {
    font-size: 2rem;
    font-weight: 700;
    margin: 0 0 0.5rem 0;
    color: #fff;
    letter-spacing: -0.035em;
}

.profile-header-subtitle {
    color: #d2e5f5;
    font-size: 1rem;
    margin: 0;
}

.profile-actions {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.profile-actions .btn {
    white-space: nowrap;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
}

.profile-header .btn-primary {
    background: #fff;
    border-color: #fff;
    color: #16497f;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.profile-header .btn-primary:hover {
    background: #e9f4fc;
    border-color: #e9f4fc;
    color: #102c54;
}

.profile-header .btn-outline-primary {
    color: #fff;
    border-color: rgba(255,255,255,0.45);
    background: rgba(255,255,255,0.08);
}

.profile-header .btn-outline-primary:hover {
    color: #fff;
    border-color: #fff;
    background: rgba(255,255,255,0.18);
}

.profile-wrapper .btn { border-radius: 0.7rem; }
.profile-wrapper .btn:focus-visible { outline: 3px solid #279cb2; outline-offset: 3px; }
.profile-header .btn:focus-visible { outline-color: #a7f3d0; }

/* Profile Completion Bar */
.completion-section {
    padding-top: 2rem;
    border-top: 1px solid rgba(255,255,255,0.2);
    margin-top: 2rem;
}

.completion-label {
    font-size: 0.9rem;
    color: #d2e5f5;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.75rem;
    font-weight: 600;
}

.completion-bar {
    height: 10px;
    background: rgba(255,255,255,0.18);
    border-radius: 5px;
    overflow: hidden;
    margin-bottom: 0.75rem;
}

.completion-fill {
    height: 100%;
    background: linear-gradient(90deg, #62c8f5, #54e2a6);
    border-radius: 5px;
    transition: width 0.4s ease;
}

.completion-percent {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.95rem;
}

.completion-percent-value {
    font-weight: 700;
    color: #b9f8df;
    font-size: 1.25rem;
}

.completion-fields {
    color: #d2e5f5;
    font-size: 0.9rem;
}

/* Profile Sections */
.profile-section {
    background: var(--cg-white);
    border: 1px solid var(--cg-border);
    border-radius: 1.25rem;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 6px 20px rgba(20,57,102,0.045);
    transition: box-shadow 0.3s ease;
}

.profile-section:hover {
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.08);
}

.profile-section-title {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 1.3rem;
    font-weight: 700;
    margin-bottom: 1.75rem;
    color: var(--cg-text);
    padding-bottom: 1rem;
    border-bottom: 1px solid #e8eef5;
    letter-spacing: -0.02em;
}

.profile-section-title i {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 40px;
    height: 40px;
    border-radius: 12px;
    background: #edf5fc;
    font-size: 1.2rem;
    color: #21628f;
}

/* Information Grid */
.info-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.info-grid.full {
    grid-template-columns: 1fr;
}

.info-item {
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.info-grid .info-item {
    padding: 1rem 1.1rem;
    background: #f8fafc;
    border: 1px solid #edf1f6;
    border-radius: 0.8rem;
}

.profile-section > .info-grid:last-child { margin-bottom: 0; }

.info-label {
    font-size: 0.8rem;
    color: var(--cg-muted);
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin-bottom: 0.5rem;
    font-weight: 600;
}

.info-value {
    overflow-wrap: anywhere;
    font-size: 1.05rem;
    color: var(--cg-text);
    font-weight: 500;
}

.info-value.empty {
    color: var(--cg-muted);
    font-style: italic;
}

.info-value a {
    color: var(--cg-primary);
    font-weight: 600;
    text-decoration: none;
    transition: color 0.2s ease;
}

.info-value a:hover {
    color: var(--cg-primary-dark);
    text-decoration: underline;
}

/* Skills Section */
.skills-list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.skill-tag {
    display: inline-block;
    background: linear-gradient(135deg, var(--cg-primary-soft), #edf5ff);
    color: var(--cg-primary);
    padding: 0.5rem 1rem;
    border-radius: 2rem;
    font-size: 0.9rem;
    font-weight: 500;
    border: 1px solid #bfdbfe;
}

/* Resume Section */
.resume-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.25rem;
    background: #f3f8fc;
    border-radius: 0.75rem;
    border-left: 4px solid var(--cg-primary);
}

.resume-icon {
    font-size: 2rem;
    color: var(--cg-primary);
    flex-shrink: 0;
}

.resume-info { min-width: 0; overflow-wrap: anywhere; }

.resume-info h3 {
    margin: 0 0 0.25rem 0;
    font-weight: 600;
    font-size: 1rem;
}

.resume-info p {
    margin: 0;
    color: var(--cg-muted);
    font-size: 0.85rem;
}

.resume-item.empty {
    border-left-color: #d4d8dd;
    background: #f9fafb;
}

.resume-item.empty .resume-icon {
    color: #9ca3af;
}

/* Empty State */
.empty-field {
    padding: 1rem;
    background: var(--cg-light);
    border-radius: 0.5rem;
    text-align: center;
    border: 1px dashed var(--cg-border);
}

.empty-field p {
    color: var(--cg-muted);
    margin: 0;
    font-size: 0.9rem;
}

/* Action Buttons */
.profile-actions-footer {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
    justify-content: center;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid #dce5ef;
}

.profile-actions-footer .btn {
    padding: 0.85rem 1.75rem;
    font-weight: 600;
    font-size: 0.95rem;
    min-width: 150px;
}

html[data-theme="dark"] {
    --profile-shell: #0f172a;
    --profile-panel: #111827;
    --profile-panel-alt: rgba(31, 41, 55, 0.9);
    --profile-line: rgba(148, 163, 184, 0.22);
    --profile-soft: rgba(148, 163, 184, 0.1);
}

html[data-theme="dark"] body {
    background: var(--profile-shell);
    color: var(--cg-text);
}

html[data-theme="dark"] .profile-wrapper {
    background: linear-gradient(135deg, rgba(15, 23, 42, 0.96) 0%, rgba(17, 24, 39, 1) 58%, rgba(12, 21, 36, 1) 100%);
}

html[data-theme="dark"] .profile-header {
    background: linear-gradient(118deg, rgba(16, 44, 84, 0.98) 0%, rgba(22, 73, 127, 0.98) 56%, rgba(8, 72, 62, 0.96) 130%);
    border-color: rgba(148, 163, 184, 0.22);
    box-shadow: 0 16px 36px rgba(2, 6, 23, 0.42);
}

html[data-theme="dark"] .profile-header-title h1,
html[data-theme="dark"] .profile-header-subtitle,
html[data-theme="dark"] .completion-label,
html[data-theme="dark"] .completion-fields,
html[data-theme="dark"] .completion-percent-value,
html[data-theme="dark"] .profile-section-title,
html[data-theme="dark"] .info-label,
html[data-theme="dark"] .info-value,
html[data-theme="dark"] .skill-tag,
html[data-theme="dark"] .resume-info h3,
html[data-theme="dark"] .empty-field p,
html[data-theme="dark"] .resume-info p,
html[data-theme="dark"] .profile-actions-footer a,
html[data-theme="dark"] .profile-header .btn-outline-primary {
    color: var(--cg-text);
}

html[data-theme="dark"] .profile-header-subtitle,
html[data-theme="dark"] .completion-label,
html[data-theme="dark"] .completion-fields,
html[data-theme="dark"] .info-label,
html[data-theme="dark"] .info-value.empty,
html[data-theme="dark"] .resume-info p,
html[data-theme="dark"] .empty-field p,
html[data-theme="dark"] .profile-header .profile-header-subtitle {
    color: var(--cg-muted);
}

html[data-theme="dark"] .profile-section {
    background: var(--profile-panel);
    border-color: var(--cg-border);
    box-shadow: 0 10px 24px rgba(2, 6, 23, 0.38);
}

html[data-theme="dark"] .profile-section-title {
    border-bottom-color: var(--profile-line);
}

html[data-theme="dark"] .profile-section-title i,
html[data-theme="dark"] .resume-icon {
    color: #8ec5ff;
    background: rgba(96, 165, 250, 0.12);
}

html[data-theme="dark"] .info-grid .info-item {
    background: rgba(148, 163, 184, 0.06);
    border-color: var(--profile-line);
}

html[data-theme="dark"] .info-value a,
html[data-theme="dark"] .empty-field a,
html[data-theme="dark"] .profile-actions a,
html[data-theme="dark"] .profile-actions-footer a {
    color: var(--cg-primary);
}

html[data-theme="dark"] .info-value a:hover,
html[data-theme="dark"] .empty-field a:hover,
html[data-theme="dark"] .profile-actions a:hover,
html[data-theme="dark"] .profile-actions-footer a:hover {
    color: #8ec5ff;
}

html[data-theme="dark"] .skill-tag {
    background: rgba(59, 130, 246, 0.16);
    border-color: rgba(96, 165, 250, 0.32);
    color: #dbeafe;
}

html[data-theme="dark"] .resume-item,
html[data-theme="dark"] .empty-field,
html[data-theme="dark"] .resume-item.empty {
    background: rgba(15, 23, 42, 0.7);
    border-color: var(--profile-line);
}

html[data-theme="dark"] .resume-item {
    border-left-color: rgba(96, 165, 250, 0.9);
}

html[data-theme="dark"] .resume-item.empty {
    background: rgba(31, 41, 55, 0.8);
    border-left-color: rgba(148, 163, 184, 0.8);
}

html[data-theme="dark"] .resume-info p {
    color: var(--cg-muted);
}

html[data-theme="dark"] .profile-actions-footer {
    border-top-color: var(--profile-line);
}

html[data-theme="dark"] .profile-header .btn-primary,
html[data-theme="dark"] .profile-actions-footer .btn-primary,
html[data-theme="dark"] .profile-actions .btn-primary,
html[data-theme="dark"] .resume-item a.btn.btn-primary,
html[data-theme="dark"] .resume-item.empty a.btn.btn-primary {
    background: var(--cg-primary);
    border-color: var(--cg-primary);
    color: #ffffff;
}

html[data-theme="dark"] .profile-header .btn-primary:hover,
html[data-theme="dark"] .profile-actions-footer .btn-primary:hover,
html[data-theme="dark"] .profile-actions .btn-primary:hover,
html[data-theme="dark"] .resume-item a.btn.btn-primary:hover,
html[data-theme="dark"] .resume-item.empty a.btn.btn-primary:hover,
html[data-theme="dark"] .profile-header .btn-primary:focus,
html[data-theme="dark"] .profile-actions-footer .btn-primary:focus,
html[data-theme="dark"] .profile-actions .btn-primary:focus,
html[data-theme="dark"] .resume-item a.btn.btn-primary:focus,
html[data-theme="dark"] .resume-item.empty a.btn.btn-primary:focus {
    background: var(--cg-primary-dark);
    border-color: var(--cg-primary-dark);
    color: #ffffff;
}

html[data-theme="dark"] .profile-header .btn-outline-primary,
html[data-theme="dark"] .profile-actions-footer .btn-outline-secondary,
html[data-theme="dark"] .profile-actions .btn-outline-secondary,
html[data-theme="dark"] .resume-item a.btn.btn-outline-secondary,
html[data-theme="dark"] .profile-actions-footer a.btn.btn-outline-secondary[href="dashboard.php"] {
    background: rgba(15, 23, 42, 0.8);
    border-color: rgba(148, 163, 184, 0.38);
    color: var(--cg-text);
}

html[data-theme="dark"] .profile-header .btn-outline-primary:hover,
html[data-theme="dark"] .profile-actions-footer .btn-outline-secondary:hover,
html[data-theme="dark"] .profile-actions .btn-outline-secondary:hover,
html[data-theme="dark"] .resume-item a.btn.btn-outline-secondary:hover,
html[data-theme="dark"] .profile-actions-footer a.btn.btn-outline-secondary[href="dashboard.php"]:hover,
html[data-theme="dark"] .profile-header .btn-outline-primary:focus,
html[data-theme="dark"] .profile-actions-footer .btn-outline-secondary:focus,
html[data-theme="dark"] .profile-actions .btn-outline-secondary:focus,
html[data-theme="dark"] .resume-item a.btn.btn-outline-secondary:focus,
html[data-theme="dark"] .profile-actions-footer a.btn.btn-outline-secondary[href="dashboard.php"]:focus {
    background: rgba(30, 41, 59, 0.95);
    border-color: rgba(96, 165, 250, 0.4);
    color: #e5e7eb;
}

html[data-theme="dark"] .resume-item .btn.btn-primary,
html[data-theme="dark"] .resume-item.empty .btn.btn-primary {
    color: #ffffff;
}

html[data-theme="dark"] .profile-wrapper .btn:focus-visible {
    outline-color: rgba(96, 165, 250, 0.9);
}

/* Responsive Design */
@media (max-width: 768px) {
    .profile-header {
        padding: 1.75rem;
    }

    .profile-header-top {
        flex-direction: column;
    }

    .profile-header-title h1 {
        font-size: 1.75rem;
    }

    .profile-actions {
        width: 100%;
    }

    .profile-actions .btn {
        flex: 1;
        min-width: 120px;
    }

    .info-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }

    .completion-section {
        margin-top: 1.5rem;
        padding-top: 1.5rem;
    }

    .profile-section {
        padding: 1.5rem;
    }

    .profile-section-title {
        font-size: 1.1rem;
        margin-bottom: 1.25rem;
    }

    .profile-actions-footer {
        flex-direction: column;
    }

    .profile-actions-footer .btn {
        width: 100%;
        min-width: auto;
    }
}

@media (max-width: 576px) {
    .profile-wrapper {
        padding: 2rem 0;
    }

    .profile-container {
        padding: 0 0.75rem;
    }

    .profile-header {
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .profile-header-title h1 {
        font-size: 1.5rem;
    }

    .profile-section {
        padding: 1.25rem;
        margin-bottom: 1.5rem;
    }

    .info-grid {
        gap: 0.75rem;
    }

    .info-label {
        font-size: 0.75rem;
    }

    .info-value {
        font-size: 1rem;
    }

    .skill-tag {
        font-size: 0.85rem;
        padding: 0.4rem 0.8rem;
    }

    .resume-item {
        padding: 1rem;
    }

    .resume-icon {
        font-size: 1.5rem;
    }

    .resume-info h3 {
        font-size: 0.9rem;
    }

    .profile-actions-footer .btn {
        padding: 0.75rem 1.5rem;
        font-size: 0.9rem;
    }
}

/* Page-scoped fixes for specific action buttons to ensure text visibility */
.profile-header .profile-actions a.btn.btn-outline-primary[href="resume.php"] {
    color: #ffffff !important;
}
.profile-header .profile-actions a.btn.btn-outline-primary[href="resume.php"]:hover,
.profile-header .profile-actions a.btn.btn-outline-primary[href="resume.php"]:focus,
.profile-header .profile-actions a.btn.btn-outline-primary[href="resume.php"]:active {
    color: #ffffff !important;
}

.profile-actions-footer a.btn.btn-outline-secondary[href="change-password.php"] {
    background-color: #0d6efd !important;
    border-color: #0d6efd !important;
    color: #ffffff !important;
}
.profile-actions-footer a.btn.btn-outline-secondary[href="change-password.php"]:hover,
.profile-actions-footer a.btn.btn-outline-secondary[href="change-password.php"]:focus,
.profile-actions-footer a.btn.btn-outline-secondary[href="change-password.php"]:active {
    background-color: #0d6efd !important;
    border-color: #0d6efd !important;
    color: #ffffff !important;
}

.profile-actions-footer a.btn.btn-outline-secondary[href="dashboard.php"] {
    background-color: #0d6efd !important;
    border-color: #0d6efd !important;
    color: #ffffff !important;
}
.profile-actions-footer a.btn.btn-outline-secondary[href="dashboard.php"]:hover,
.profile-actions-footer a.btn.btn-outline-secondary[href="dashboard.php"]:focus,
.profile-actions-footer a.btn.btn-outline-secondary[href="dashboard.php"]:active {
    background-color: #0d6efd !important;
    border-color: #0d6efd !important;
    color: #ffffff !important;
}
</style>

<div class="profile-wrapper">
    <main class="profile-container py-4">
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="profile-header-top">
                <div class="profile-header-title">
                    <h1><?php echo htmlspecialchars($profile['name'] ?? 'Candidate', ENT_QUOTES, 'UTF-8'); ?></h1>
                    <p class="profile-header-subtitle">
                        <i class="bi bi-envelope"></i>
                        <?php echo htmlspecialchars($profile['email'] ?? 'Email not provided', ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                </div>
                <div class="profile-actions">
                    <a href="edit-profile.php" class="btn btn-primary">
                        <i class="bi bi-pencil-square"></i> Edit Profile
                    </a>
                    <a href="resume.php" class="btn btn-outline-primary">
                        <i class="bi bi-file-earmark-pdf"></i> <?php echo !empty($profile['resume']) ? 'Update Resume' : 'Upload Resume'; ?>
                    </a>
                </div>
            </div>

            <!-- Profile Completion -->
            <div class="completion-section">
                <div class="completion-label">Profile Completion</div>
                <div class="completion-bar">
                    <div class="completion-fill" style="width: <?php echo $profileCompletion; ?>%;"></div>
                </div>
                <div class="completion-percent">
                    <span class="completion-fields">
                        <?php echo $completedFields; ?>/<?php echo $totalFields; ?> fields completed
                    </span>
                    <span class="completion-percent-value"><?php echo $profileCompletion; ?>%</span>
                </div>
            </div>
        </div>

        <!-- Basic Information Section -->
        <div class="profile-section">
            <div class="profile-section-title">
                <i class="bi bi-person-circle"></i>
                Basic Information
            </div>

            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Full Name</span>
                    <span class="info-value <?php echo empty($profile['name']) ? 'empty' : ''; ?>">
                        <?php echo !empty($profile['name']) ? htmlspecialchars($profile['name'], ENT_QUOTES, 'UTF-8') : 'Not provided'; ?>
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Email Address</span>
                    <span class="info-value">
                        <?php echo htmlspecialchars($profile['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Phone Number</span>
                    <span class="info-value <?php echo empty($profile['phone']) ? 'empty' : ''; ?>">
                        <?php echo !empty($profile['phone']) ? htmlspecialchars($profile['phone'], ENT_QUOTES, 'UTF-8') : 'Not provided'; ?>
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Location</span>
                    <span class="info-value <?php echo empty($profile['location']) ? 'empty' : ''; ?>">
                        <?php echo !empty($profile['location']) ? htmlspecialchars($profile['location'], ENT_QUOTES, 'UTF-8') : 'Not specified'; ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Professional Information Section -->
        <div class="profile-section">
            <div class="profile-section-title">
                <i class="bi bi-briefcase"></i>
                Professional Information
            </div>

            <div class="info-grid full">
                <div class="info-item">
                    <span class="info-label">Experience Level</span>
                    <span class="info-value <?php echo empty($profile['experience']) ? 'empty' : ''; ?>">
                        <?php echo !empty($profile['experience']) ? htmlspecialchars($profile['experience'], ENT_QUOTES, 'UTF-8') : 'Not mentioned'; ?>
                    </span>
                </div>
            </div>

            <div class="info-grid full">
                <div class="info-item">
                    <span class="info-label">Qualification / Education</span>
                    <span class="info-value <?php echo empty($profile['qualification']) ? 'empty' : ''; ?>">
                        <?php echo !empty($profile['qualification']) ? htmlspecialchars($profile['qualification'], ENT_QUOTES, 'UTF-8') : 'Not added'; ?>
                    </span>
                </div>
            </div>

            <div class="info-item">
                <span class="info-label">Skills</span>
                <?php if (!empty($profile['skills'])): ?>
                    <div class="skills-list">
                        <?php
                        $skills = array_map('trim', explode(',', $profile['skills']));
                        foreach ($skills as $skill) {
                            if (!empty($skill)) {
                                echo '<span class="skill-tag">' . htmlspecialchars($skill, ENT_QUOTES, 'UTF-8') . '</span>';
                            }
                        }
                        ?>
                    </div>
                <?php else: ?>
                    <div class="empty-field">
                        <p>No skills added yet. <a href="edit-profile.php#skills">Add skills to your profile</a></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Resume Section -->
        <div class="profile-section">
            <div class="profile-section-title">
                <i class="bi bi-file-earmark-text"></i>
                Resume
            </div>

            <?php if (!empty($profile['resume'])): ?>
                <div class="resume-item">
                    <div class="resume-icon">
                        <i class="bi bi-file-earmark-pdf"></i>
                    </div>
                    <div class="resume-info">
                        <h3>Resume Uploaded</h3>
                        <p><?php echo htmlspecialchars(basename($profile['resume']), ENT_QUOTES, 'UTF-8'); ?></p>
                        <div style="margin-top: 0.75rem;">
                            <a href="../<?php echo htmlspecialchars($profile['resume'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" class="btn btn-sm btn-primary">
                                <i class="bi bi-download"></i> Download
                            </a>
                            <a href="resume.php" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-arrow-repeat"></i> Update
                            </a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="resume-item empty">
                    <div class="resume-icon">
                        <i class="bi bi-file-earmark-pdf"></i>
                    </div>
                    <div class="resume-info">
                        <h3>No Resume Uploaded</h3>
                        <p>Upload your resume to increase your chances of getting selected by employers.</p>
                        <div style="margin-top: 0.75rem;">
                            <a href="resume.php" class="btn btn-sm btn-primary">
                                <i class="bi bi-cloud-upload"></i> Upload Resume
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div style="margin-top: 1rem; padding: 1rem; background: #f0f9ff; border-left: 4px solid var(--cg-primary); border-radius: 0.5rem;">
                <p style="margin: 0; color: var(--cg-muted); font-size: 0.9rem;">
                    <i class="bi bi-info-circle"></i>
                    <strong>Supported formats:</strong> PDF, DOC, DOCX (Max 5MB)
                </p>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="profile-actions-footer">
            <a href="edit-profile.php" class="btn btn-primary">
                <i class="bi bi-pencil-square"></i> Edit Profile
            </a>
            <a href="change-password.php" class="btn btn-outline-secondary">
                <i class="bi bi-lock"></i> Change Password
            </a>
            <a href="dashboard.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
