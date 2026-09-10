<?php

declare(strict_types=1);
session_start();

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/admin-auth.php';
require_admin();

$pageTitle = 'Profile - Admin';
require_once __DIR__ . '/../includes/header.php';

$conn = getDbConnection();
$adminId = (int)($_SESSION['user_id'] ?? 0);
$adminRole = 'admin';

$admin = null;
if ($adminId > 0) {
    $adminStmt = $conn->prepare('SELECT id, name, email, phone, role, status, created_at FROM users WHERE id = ? AND role = ? LIMIT 1');
    if ($adminStmt) {
        $adminStmt->bind_param('is', $adminId, $adminRole);
        $adminStmt->execute();
        $result = $adminStmt->get_result();
        $admin = $result->fetch_assoc();
        $adminStmt->close();
    }
}

if (!$admin) {
    $conn->close();
    header('Location: login.php');
    exit;
}

$conn->close();

$statusLabel = ((int)($admin['status'] ?? 0) === 1) ? 'Active' : 'Inactive';
$roleLabel = ucfirst((string)($admin['role'] ?? 'admin'));
$createdAt = !empty($admin['created_at']) ? date('M d, Y', strtotime((string)$admin['created_at'])) : 'N/A';
?>

<style>
.admin-root {
    min-height: 100vh;
    display: flex;
    align-items: stretch;
    gap: 1.5rem;
    padding: 1.5rem;
    background: linear-gradient(180deg, rgba(13,110,253,0.02), rgba(255,255,255,0));
}

.sidebar {
    width: 260px;
    background: var(--cg-white);
    border: 1px solid var(--cg-border);
    border-radius: 1rem;
    padding: 1rem;
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.05);
    position: sticky;
    top: 1rem;
    height: fit-content;
}

.brand-wrap {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    padding: 0.25rem 0 1rem;
    border-bottom: 1px solid rgba(15, 23, 42, 0.04);
}

.sidebar-nav {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
    margin-top: 1rem;
}

.nav-link-admin {
    display: flex;
    align-items: center;
    gap: 0.7rem;
    padding: 0.7rem 0.8rem;
    border-radius: 0.65rem;
    font-weight: 600;
    color: var(--cg-accent);
    text-decoration: none;
}

.nav-link-admin:hover,
.nav-link-admin:focus {
    color: var(--cg-primary);
    background: rgba(13,110,253,0.04);
    text-decoration: none;
}

.nav-link-admin.active {
    background: rgba(13,110,253,0.07);
    color: var(--cg-primary);
}

.sidebar-footer {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid rgba(15, 23, 42, 0.04);
}

.main-panel {
    flex: 1 1 auto;
    min-width: 0;
}

.card-panel {
    background: var(--cg-white);
    border: 1px solid var(--cg-border);
    border-radius: 1rem;
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.04);
}

.header-panel {
    padding: 1.1rem 1.4rem;
    margin-bottom: 1rem;
}

.page-kicker {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--cg-primary);
    font-size: 0.78rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.header-panel h2 {
    margin: 0;
    font-size: clamp(1.6rem, 2.4vw, 2.2rem);
    letter-spacing: -0.03em;
}

.header-panel p {
    color: var(--cg-muted);
    margin-top: 0.35rem;
}

.profile-layout {
    display: grid;
    grid-template-columns: minmax(0, 1.4fr) minmax(260px, 0.8fr);
    gap: 1rem;
}

.profile-card {
    padding: 1.2rem 1.25rem;
}

.profile-card h3 {
    margin: 0 0 0.65rem;
    color: var(--cg-accent);
    font-size: 1.08rem;
}

.profile-card p {
    color: var(--cg-muted);
    margin-bottom: 1.1rem;
}

.profile-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
}

.profile-field {
    display: grid;
    gap: 0.35rem;
    padding: 0.9rem 1rem;
    border: 1px solid var(--cg-border);
    border-radius: 0.85rem;
    background: rgba(13,110,253,0.01);
}

.profile-label {
    font-size: 0.76rem;
    color: var(--cg-muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-weight: 700;
}

.profile-value {
    font-size: 1rem;
    color: var(--cg-accent);
    font-weight: 600;
    word-break: break-word;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.35rem 0.7rem;
    border-radius: 999px;
    font-size: 0.8rem;
    font-weight: 700;
    width: fit-content;
    background: rgba(25,135,84,0.1);
    color: #198754;
}

.security-panel {
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.security-box {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    padding: 1rem;
    border: 1px solid var(--cg-border);
    border-radius: 0.9rem;
    background: rgba(13,110,253,0.02);
}

.security-box p {
    margin: 0;
}

@media (max-width: 991.98px) {
    .admin-root {
        flex-direction: column;
    }

    .sidebar {
        width: 100%;
        position: static;
    }

    .profile-layout {
        grid-template-columns: 1fr;
    }
}
</style>

<main class="container-fluid admin-root">
    <aside class="sidebar">
        <div class="brand-wrap">
            <span class="brand-mark brand-mark-sm">
                <img src="../assets/images/logo.webp" alt="Career Grow Infotech logo" width="34" height="34" loading="lazy">
            </span>
            <div>
                <div class="brand-title">Career Grow Infotech</div>
                <div class="brand-subtitle">Admin Portal</div>
            </div>
        </div>

        <nav class="sidebar-nav" aria-label="Sidebar navigation">
            <a href="dashboard.php" class="nav-link-admin"><i class="bi bi-speedometer2"></i> Dashboard</a>
            <a href="jobs.php" class="nav-link-admin"><i class="bi bi-briefcase"></i> Jobs</a>
            <a href="applicants.php" class="nav-link-admin"><i class="bi bi-people"></i> Applicants</a>
            <a href="candidate-details.php" class="nav-link-admin"><i class="bi bi-person-badge"></i> Candidates</a>
            <a href="contact-messages.php" class="nav-link-admin"><i class="bi bi-envelope-paper"></i> Contact Messages</a>
            <a href="settings.php" class="nav-link-admin"><i class="bi bi-gear"></i> Settings</a>
            <a href="profile.php" class="nav-link-admin active"><i class="bi bi-person-circle"></i> Profile</a>
        </nav>

        <div class="sidebar-footer">
            <a href="../index.php" class="nav-link-admin"><i class="bi bi-house"></i> Back to Website</a>
            <a href="../logout.php" class="nav-link-admin"><i class="bi bi-box-arrow-right"></i> Logout</a>
        </div>
    </aside>

    <section class="main-panel">
        <?php
        $pageH1 = 'Profile';
        $pageSubtitle = 'Your administrator account information';
        require_once __DIR__ . '/../includes/admin-header.php';
        ?>

        <div class="card-panel header-panel">
            <div class="page-kicker"><i class="bi bi-person-circle"></i> Admin / Profile</div>
            <div>
                <h2>Profile</h2>
                <p>Review your account details and manage your security settings.</p>
            </div>
        </div>

        <div class="profile-layout">
            <div class="card-panel profile-card">
                <h3>Profile Information</h3>
                <p>Secure account details for the current administrator profile.</p>

                <div class="profile-grid">
                    <div class="profile-field">
                        <div class="profile-label">Name</div>
                        <div class="profile-value"><?php echo htmlspecialchars((string)($admin['name'] ?? 'Not available'), ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>

                    <div class="profile-field">
                        <div class="profile-label">Registered Email</div>
                        <div class="profile-value"><?php echo htmlspecialchars((string)($admin['email'] ?? 'Not available'), ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>

                    <div class="profile-field">
                        <div class="profile-label">Phone</div>
                        <div class="profile-value"><?php echo htmlspecialchars((string)($admin['phone'] ?? 'Not available'), ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>

                    <div class="profile-field">
                        <div class="profile-label">Role</div>
                        <div class="profile-value"><?php echo htmlspecialchars($roleLabel, ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>

                    <div class="profile-field">
                        <div class="profile-label">Account Status</div>
                        <div class="profile-value"><span class="status-badge"><?php echo htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8'); ?></span></div>
                    </div>

                    <div class="profile-field">
                        <div class="profile-label">Member Since</div>
                        <div class="profile-value"><?php echo htmlspecialchars($createdAt, ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                </div>
            </div>

            <div class="card-panel profile-card security-panel">
                <h3>Security</h3>
                <p>Update your administrator password using the existing change-password workflow.</p>

                <div class="security-box">
                    <p>Use the existing password change form in the admin settings page to update your current password securely.</p>
                    <a href="settings.php#security" class="btn btn-primary">
                        <i class="bi bi-lock me-2"></i>Change Password
                    </a>
                </div>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
