<?php
declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = 'Dashboard - Career Grow Infotech';

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

$userId = (int)$_SESSION['user_id'];
$conn = getDbConnection();

// Fetch complete user profile info with all candidate profile data
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

// Get ALL application status counts (not just recent)
$statusCounts = [
    'total' => 0,
    'New Applied' => 0,
    'Reviewed' => 0,
    'Accepted' => 0,
    'Rejected' => 0,
];

$statusStmt = $conn->prepare('SELECT status, COUNT(*) as count FROM applications WHERE user_id = ? GROUP BY status');
$statusStmt->bind_param('i', $userId);
$statusStmt->execute();
$statusResult = $statusStmt->get_result();
while ($row = $statusResult->fetch_assoc()) {
    $status = (string)($row['status'] ?? '');
    $count = (int)($row['count'] ?? 0);
    if (isset($statusCounts[$status])) {
        $statusCounts[$status] = $count;
    }
    $statusCounts['total'] += $count;
}
$statusStmt->close();

// Fetch recent applications (last 6) for display
$recentStmt = $conn->prepare('SELECT a.id, a.job_id, a.status, a.applied_at, j.title, j.location, j.job_type FROM applications a JOIN jobs j ON a.job_id = j.id WHERE a.user_id = ? ORDER BY a.applied_at DESC LIMIT 6');
$recentStmt->bind_param('i', $userId);
$recentStmt->execute();
$recentResult = $recentStmt->get_result();
$recentApps = [];
while ($row = $recentResult->fetch_assoc()) {
    $recentApps[] = $row;
}
$recentStmt->close();

// Get count of active public jobs available for browsing
$jobCountStmt = $conn->prepare('SELECT COUNT(*) as count FROM jobs WHERE status = ? AND (last_date IS NULL OR last_date >= CURDATE())');
$activeStatus = 'active';
$jobCountStmt->bind_param('s', $activeStatus);
$jobCountStmt->execute();
$jobCountResult = $jobCountStmt->get_result()->fetch_assoc();
$availableJobs = (int)($jobCountResult['count'] ?? 0);
$jobCountStmt->close();

$conn->close();
?>

<style>
:root {
    --stat-pending: #3b82f6;
    --stat-review: #f59e0b;
    --stat-accept: #10b981;
    --stat-reject: #ef4444;
}

.dashboard-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2.5rem; gap: 1.5rem; }
.dashboard-welcome { flex: 1; }
.dashboard-welcome h1 { font-size: 2rem; font-weight: 700; margin: 0 0 0.5rem 0; color: var(--cg-text); }
.dashboard-welcome p { font-size: 1.05rem; color: var(--cg-muted); margin: 0; }

.quick-actions { display: flex; gap: 0.75rem; flex-wrap: wrap; }
.quick-actions .btn { padding: 0.75rem 1.25rem; font-size: 0.95rem; font-weight: 500; }

.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-bottom: 2.5rem; }

.stat-card { background: var(--cg-white); border: 1px solid var(--cg-border); border-radius: 0.875rem; padding: 1.75rem; transition: box-shadow var(--cg-transition), transform var(--cg-transition); display: flex; flex-direction: column; }
.stat-card:hover { box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08); transform: translateY(-2px); }
.stat-card.has-data { border-left: 4px solid var(--stat-color, var(--cg-primary)); }

.stat-card .stat-value { font-size: 2.5rem; font-weight: 700; color: var(--stat-color, var(--cg-primary)); margin-bottom: 0.5rem; }
.stat-card .stat-label { font-size: 0.95rem; color: var(--cg-muted); font-weight: 500; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 0.5rem; }
.stat-card .stat-detail { font-size: 0.85rem; color: var(--cg-muted); }

.section-container { background: var(--cg-white); border: 1px solid var(--cg-border); border-radius: 0.875rem; padding: 2rem; margin-bottom: 2rem; }
.section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.75rem; }
.section-header h2 { font-size: 1.4rem; font-weight: 700; margin: 0; }
.section-header .action-link { font-size: 0.9rem; color: var(--cg-primary); font-weight: 600; text-decoration: none; }
.section-header .action-link:hover { text-decoration: underline; }

.profile-completion { display: flex; align-items: center; gap: 1rem; }
.completion-info { flex: 1; }
.completion-label { font-size: 0.9rem; color: var(--cg-muted); margin-bottom: 0.5rem; }
.completion-bar { height: 8px; background: var(--cg-border); border-radius: 4px; overflow: hidden; }
.completion-fill { height: 100%; background: linear-gradient(90deg, var(--cg-primary), #00a8e8); border-radius: 4px; transition: width 0.3s ease; }
.completion-percent { font-weight: 700; color: var(--cg-primary); min-width: 40px; text-align: right; }

.profile-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem; }
.profile-field { display: flex; flex-direction: column; }
.profile-label { font-size: 0.8rem; color: var(--cg-muted); text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 0.4rem; font-weight: 600; }
.profile-value { font-size: 1rem; color: var(--cg-text); font-weight: 500; }
.profile-value.empty { color: var(--cg-muted); font-style: italic; }

.profile-actions { display: flex; gap: 0.75rem; margin-top: 1rem; }

.app-list { display: flex; flex-direction: column; gap: 0.75rem; }
.app-item { display: flex; justify-content: space-between; align-items: flex-start; padding: 1.25rem; background: var(--cg-light); border-radius: 0.625rem; transition: background var(--cg-transition); }
.app-item:hover { background: #f0f2f7; }
.app-item-content { flex: 1; }
.app-item-title { font-size: 1.05rem; font-weight: 600; color: var(--cg-text); margin-bottom: 0.4rem; }
.app-item-title a { color: var(--cg-primary); text-decoration: none; }
.app-item-title a:hover { text-decoration: underline; }
.app-item-meta { font-size: 0.85rem; color: var(--cg-muted); }
.app-item-meta span { margin-right: 1rem; }
.app-item-status { display: flex; align-items: center; }

.status-badge { display: inline-block; padding: 0.5rem 0.875rem; border-radius: 0.375rem; font-size: 0.8rem; font-weight: 600; text-transform: capitalize; white-space: nowrap; }
.status-badge.new { background-color: #dbeafe; color: #1e40af; }
.status-badge.new-applied { background-color: #dbeafe; color: #1e40af; }
.status-badge.reviewed { background-color: #fed7aa; color: #b45309; }
.status-badge.accepted { background-color: #dcfce7; color: #166534; }
.status-badge.rejected { background-color: #fee2e2; color: #991b1b; }

.empty-state { text-align: center; padding: 3rem 1.5rem; }
.empty-state-icon { font-size: 3.5rem; margin-bottom: 1rem; opacity: 0.5; }
.empty-state-title { font-size: 1.3rem; font-weight: 700; margin-bottom: 0.5rem; }
.empty-state-text { color: var(--cg-muted); margin-bottom: 1.5rem; line-height: 1.6; }
.empty-state-action { display: inline-block; }

.browse-section { background: linear-gradient(135deg, var(--cg-primary-soft) 0%, #edf5ff 100%); border: 1px solid #bfdbfe; border-radius: 0.875rem; padding: 2rem; text-align: center; }
.browse-section h3 { font-size: 1.2rem; font-weight: 700; margin-bottom: 0.75rem; }
.browse-section p { color: var(--cg-muted); margin-bottom: 1.5rem; }

@media (max-width: 768px) {
    .dashboard-header { flex-direction: column-reverse; margin-bottom: 2rem; }
    .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 1rem; }
    .stat-card { padding: 1.25rem; }
    .stat-card .stat-value { font-size: 2rem; }
    .profile-grid { grid-template-columns: 1fr; gap: 1rem; }
    .quick-actions { flex-direction: column; }
    .quick-actions .btn { width: 100%; }
    .section-header { flex-direction: column; align-items: flex-start; gap: 0.75rem; }
    .app-item { flex-direction: column; gap: 1rem; }
    .app-item-meta { display: flex; flex-direction: column; gap: 0.25rem; }
    .app-item-meta span { margin-right: 0; }
}

@media (max-width: 576px) {
    .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 0.75rem; }
    .stat-card { padding: 1rem; }
    .stat-card .stat-value { font-size: 1.75rem; }
    .stat-card .stat-label { font-size: 0.8rem; }
    .section-container { padding: 1.25rem; }
    .dashboard-welcome h1 { font-size: 1.5rem; }
}
</style>

<main class="container py-5">
    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <div class="dashboard-welcome">
            <h1>Welcome, <?php echo htmlspecialchars($profile['name'] ?? 'Candidate', ENT_QUOTES, 'UTF-8'); ?>! 👋</h1>
            <p>Track your applications and manage your career journey</p>
        </div>
        <div class="quick-actions">
            <a href="../jobs.php" class="btn btn-primary">
                <i class="bi bi-briefcase"></i> Browse Jobs
            </a>
            <a href="applications.php" class="btn btn-outline-primary">
                <i class="bi bi-file-text"></i> My Apps
            </a>
        </div>
    </div>

    <!-- Stats Cards Grid -->
    <div class="stats-grid">
        <!-- Total Applications -->
        <div class="stat-card <?php echo $statusCounts['total'] > 0 ? 'has-data' : ''; ?>" style="--stat-color: var(--cg-primary);">
            <div class="stat-value"><?php echo $statusCounts['total']; ?></div>
            <div class="stat-label">Total Applications</div>
            <div class="stat-detail">Overall submissions</div>
        </div>

        <!-- New Applied -->
        <div class="stat-card <?php echo $statusCounts['New Applied'] > 0 ? 'has-data' : ''; ?>" style="--stat-color: var(--stat-pending);">
            <div class="stat-value"><?php echo $statusCounts['New Applied']; ?></div>
            <div class="stat-label">New Applied</div>
            <div class="stat-detail">Awaiting review</div>
        </div>

        <!-- Under Review -->
        <div class="stat-card <?php echo $statusCounts['Reviewed'] > 0 ? 'has-data' : ''; ?>" style="--stat-color: var(--stat-review);">
            <div class="stat-value"><?php echo $statusCounts['Reviewed']; ?></div>
            <div class="stat-label">Under Review</div>
            <div class="stat-detail">Being evaluated</div>
        </div>

        <!-- Accepted -->
        <div class="stat-card <?php echo $statusCounts['Accepted'] > 0 ? 'has-data' : ''; ?>" style="--stat-color: var(--stat-accept);">
            <div class="stat-value"><?php echo $statusCounts['Accepted']; ?></div>
            <div class="stat-label">Accepted</div>
            <div class="stat-detail">Great progress!</div>
        </div>

        <!-- Rejected -->
        <div class="stat-card <?php echo $statusCounts['Rejected'] > 0 ? 'has-data' : ''; ?>" style="--stat-color: var(--stat-reject);">
            <div class="stat-value"><?php echo $statusCounts['Rejected']; ?></div>
            <div class="stat-label">Rejected</div>
            <div class="stat-detail">Applications declined</div>
        </div>

        <!-- Jobs Available -->
        <div class="stat-card" style="--stat-color: #8b5cf6;">
            <div class="stat-value"><?php echo $availableJobs; ?></div>
            <div class="stat-label">Opportunities</div>
            <div class="stat-detail">Active positions</div>
        </div>
    </div>

    <!-- Profile Section -->
    <div class="section-container">
        <div class="section-header">
            <h2>Your Profile</h2>
            <a href="edit-profile.php" class="action-link">
                <i class="bi bi-pencil"></i> Edit Profile
            </a>
        </div>

        <!-- Profile Completion -->
        <div class="profile-completion" style="margin-bottom: 2rem; padding-bottom: 2rem; border-bottom: 1px solid var(--cg-border);">
            <div class="completion-info">
                <div class="completion-label">Profile Completion</div>
                <div class="completion-bar">
                    <div class="completion-fill" style="width: <?php echo $profileCompletion; ?>%;"></div>
                </div>
            </div>
            <div class="completion-percent"><?php echo $profileCompletion; ?>%</div>
        </div>

        <!-- Profile Information Grid -->
        <div class="profile-grid">
            <div class="profile-field">
                <span class="profile-label">Full Name</span>
                <span class="profile-value"><?php echo htmlspecialchars($profile['name'] ?? 'Not set', ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
            <div class="profile-field">
                <span class="profile-label">Email Address</span>
                <span class="profile-value"><?php echo htmlspecialchars($profile['email'] ?? 'Not set', ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
            <div class="profile-field">
                <span class="profile-label">Phone Number</span>
                <span class="profile-value <?php echo empty($profile['phone']) ? 'empty' : ''; ?>"><?php echo !empty($profile['phone']) ? htmlspecialchars($profile['phone'], ENT_QUOTES, 'UTF-8') : 'Not provided'; ?></span>
            </div>
            <div class="profile-field">
                <span class="profile-label">Location</span>
                <span class="profile-value <?php echo empty($profile['location']) ? 'empty' : ''; ?>"><?php echo !empty($profile['location']) ? htmlspecialchars($profile['location'], ENT_QUOTES, 'UTF-8') : 'Not specified'; ?></span>
            </div>
            <div class="profile-field">
                <span class="profile-label">Experience</span>
                <span class="profile-value <?php echo empty($profile['experience']) ? 'empty' : ''; ?>"><?php echo !empty($profile['experience']) ? htmlspecialchars($profile['experience'], ENT_QUOTES, 'UTF-8') : 'Not mentioned'; ?></span>
            </div>
            <div class="profile-field">
                <span class="profile-label">Qualification</span>
                <span class="profile-value <?php echo empty($profile['qualification']) ? 'empty' : ''; ?>"><?php echo !empty($profile['qualification']) ? htmlspecialchars($profile['qualification'], ENT_QUOTES, 'UTF-8') : 'Not added'; ?></span>
            </div>
        </div>

        <!-- Resume Status -->
        <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--cg-border);">
            <div class="profile-label">Resume</div>
            <div style="margin-top: 0.5rem;">
                <?php if (!empty($profile['resume'])): ?>
                    <span class="badge bg-success">
                        <i class="bi bi-check-circle"></i> Uploaded
                    </span>
                    <span style="color: var(--cg-muted); font-size: 0.85rem;">
                        <?php echo htmlspecialchars(basename($profile['resume']), ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                <?php else: ?>
                    <span class="badge bg-warning">
                        <i class="bi bi-exclamation-circle"></i> Not uploaded
                    </span>
                    <p style="color: var(--cg-muted); font-size: 0.9rem; margin-top: 0.5rem;">
                        <strong>Tip:</strong> Uploading a resume increases your chances of getting selected.
                    </p>
                    <a href="resume.php" class="btn btn-sm btn-primary">
                        <i class="bi bi-upload"></i> Upload Resume
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Profile Actions -->
        <div class="profile-actions">
            <a href="edit-profile.php" class="btn btn-primary btn-sm">
                <i class="bi bi-pencil"></i> Edit Profile
            </a>
            <a href="change-password.php" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-lock"></i> Change Password
            </a>
        </div>
    </div>

    <!-- Recent Applications Section -->
    <div class="section-container">
        <div class="section-header">
            <h2>Recent Applications</h2>
            <?php if ($statusCounts['total'] > 6): ?>
                <a href="applications.php" class="action-link">
                    View All <?php echo $statusCounts['total']; ?> Applications →
                </a>
            <?php endif; ?>
        </div>

        <?php if (!empty($recentApps)): ?>
            <div class="app-list">
                <?php foreach ($recentApps as $app): ?>
                    <div class="app-item">
                        <div class="app-item-content">
                            <div class="app-item-title">
                                <a href="../job-details.php?id=<?php echo (int)$app['job_id']; ?>">
                                    <?php echo htmlspecialchars($app['title'], ENT_QUOTES, 'UTF-8'); ?>
                                </a>
                            </div>
                            <div class="app-item-meta">
                                <span>
                                    <i class="bi bi-geo-alt"></i>
                                    <?php echo htmlspecialchars($app['location'] ?? 'Location not specified', ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                                <span>
                                    <i class="bi bi-briefcase"></i>
                                    <?php echo htmlspecialchars($app['job_type'] ?? 'Job type not specified', ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                                <span>
                                    <i class="bi bi-calendar"></i>
                                    <?php echo date('d M Y', strtotime($app['applied_at'])); ?>
                                </span>
                            </div>
                        </div>
                        <div class="app-item-status">
                            <span class="status-badge <?php echo strtolower(str_replace(' ', '-', $app['status'])); ?>">
                                <?php echo htmlspecialchars($app['status'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($statusCounts['total'] > 6): ?>
                <div style="text-align: center; margin-top: 1.5rem;">
                    <a href="applications.php" class="btn btn-outline-primary">
                        View All Applications
                    </a>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="bi bi-inbox"></i>
                </div>
                <div class="empty-state-title">No Applications Yet</div>
                <div class="empty-state-text">
                    Start your journey by exploring job opportunities and submitting applications.
                </div>
                <div class="empty-state-action">
                    <a href="../jobs.php" class="btn btn-primary">
                        <i class="bi bi-search"></i> Browse Jobs
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Job Opportunities Discovery Section -->
    <?php if ($availableJobs > 0): ?>
        <div class="section-container">
            <div class="section-header">
                <h2>Career Opportunities</h2>
            </div>
            <div class="browse-section">
                <h3>More Opportunities Available</h3>
                <p>
                    We have
                    <strong>
                        <?php echo $statusCounts['total'] > 0 
                            ? ($availableJobs - $statusCounts['total'] > 0 
                                ? ($availableJobs - $statusCounts['total']) . ' unapplied' 
                                : 'all ' . $availableJobs) 
                            : $availableJobs; ?>
                    </strong>
                    open positions looking for qualified candidates like you.
                </p>
                <a href="../jobs.php" class="btn btn-primary btn-lg">
                    <i class="bi bi-briefcase"></i> Browse All Job Opportunities
                </a>
            </div>
        </div>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
