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

$profileErrors = [];
$profileSuccess = '';

$profileValues = [
    'name' => (string)($admin['name'] ?? ''),
    'email' => (string)($admin['email'] ?? ''),
    'phone' => (string)($admin['phone'] ?? ''),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['profile_update'])) {
    $profileValues['name'] = trim((string)($_POST['name'] ?? ''));
    $profileValues['email'] = trim((string)($_POST['email'] ?? ''));
    $profileValues['phone'] = trim((string)($_POST['phone'] ?? ''));

    if ($profileValues['name'] === '') {
        $profileErrors[] = 'Name is required.';
    }
    if ($profileValues['email'] === '' || !filter_var($profileValues['email'], FILTER_VALIDATE_EMAIL)) {
        $profileErrors[] = 'A valid email address is required.';
    }
    if ($profileValues['phone'] === '') {
        $profileErrors[] = 'Phone number is required.';
    }

    if (empty($profileErrors)) {
        $duplicateStmt = $conn->prepare('SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1');
        if ($duplicateStmt) {
            $duplicateStmt->bind_param('si', $profileValues['email'], $adminId);
            $duplicateStmt->execute();
            $duplicateResult = $duplicateStmt->get_result();
            $duplicateExists = $duplicateResult->fetch_assoc();
            $duplicateStmt->close();

            if ($duplicateExists) {
                $profileErrors[] = 'This email address is already in use.';
            }
        }
    }

    if (empty($profileErrors)) {
        $updateStmt = $conn->prepare('UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ? AND role = ? LIMIT 1');
        if ($updateStmt) {
            $updateStmt->bind_param('sssis', $profileValues['name'], $profileValues['email'], $profileValues['phone'], $adminId, $adminRole);
            $updated = $updateStmt->execute();
            $updateStmt->close();

            if ($updated) {
                $_SESSION['user_name'] = $profileValues['name'];
                $_SESSION['user_email'] = $profileValues['email'];
                $profileSuccess = 'Your account details were updated successfully.';
                $admin['name'] = $profileValues['name'];
                $admin['email'] = $profileValues['email'];
                $admin['phone'] = $profileValues['phone'];
            } else {
                $profileErrors[] = 'Unable to update your account details right now.';
            }
        } else {
            $profileErrors[] = 'Unable to save your account details right now.';
        }
    }
}

$statusLabel = ((int)($admin['status'] ?? 0) === 1) ? 'Active' : 'Inactive';
$roleLabel = ucfirst((string)($admin['role'] ?? 'admin'));
$createdAt = !empty($admin['created_at']) ? date('M d, Y', strtotime((string)$admin['created_at'])) : 'N/A';

$conn->close();
?>

<style>
.admin-root {
    --dash-ink: #12213d;
    --dash-navy: #102b52;
    --dash-blue: #2563eb;
    --dash-mint: #18a66a;
    --dash-surface: #ffffff;
    --dash-bg: #f4f7fb;
    --dash-border: #e5ebf3;
    --dash-muted: #77849a;
    --cg-white: #ffffff;
    --cg-border: #e5ebf3;
    --cg-primary: #2563eb;
    --cg-accent: #18a66a;
    --cg-muted: #77849a;
    --cg-text: #12213d;
    min-height: 100vh;
    display: flex;
    align-items: stretch;
    gap: 24px;
    padding: 24px;
    background: var(--dash-bg);
    color: var(--dash-ink);
}

.sidebar {
    width: 248px;
    min-height: calc(100vh - 48px);
    height: auto;
    align-self: flex-start;
    position: sticky;
    top: 24px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    padding: 18px 14px;
    border: 1px solid rgba(255,255,255,.14);
    border-radius: 20px;
    background: linear-gradient(155deg,#102c54 0%,#16497f 56%,#0c8879 130%);
    box-shadow: 0 18px 42px rgba(20,57,102,.22);
}

.sidebar-branding,
.brand-wrap,
.brand {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 4px 8px 10px;
    border-bottom: 1px solid rgba(255,255,255,.18);
}

.brand-mark-sm {
    width: 42px;
    height: 42px;
    padding: 4px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    background: rgba(255,255,255,.96);
}

.brand-mark-sm img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.brand-title {
    color: #fff;
    font-size: .82rem;
    font-weight: 800;
    letter-spacing: -.02em;
    line-height: 1.2;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.brand-subtitle,
.text-soft,
.sidebar .text-soft,
.brand-subtitle.small,
.text-soft.small {
    color: rgba(232,244,255,.72) !important;
    font-size: .7rem;
    line-height: 1.2;
    margin-top: 2px;
}

.sidebar-branding {
    flex-shrink: 0;
}

.sidebar-navigation {
    flex: 1 1 auto;
    display: flex;
    flex-direction: column;
    overflow-y: auto;
    overflow-x: hidden;
    margin-top: 10px;
    -ms-overflow-style: none;
    scrollbar-width: none;
}

.sidebar-navigation::-webkit-scrollbar {
    width: 0;
    height: 0;
}

.sidebar-nav {
    display: grid;
    gap: 4px;
    margin-top: 0 !important;
}

.nav-link-admin {
    position: relative;
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 11px 12px;
    border-radius: 11px;
    color: rgba(235,246,255,.8);
    font-size: .92rem;
    font-weight: 650;
    text-decoration: none;
    transition: background .2s ease,color .2s ease,transform .2s ease;
}

.nav-link-admin i {
    width: 20px;
    font-size: 1.08rem;
    text-align: center;
}

.nav-link-admin:hover,
.nav-link-admin:focus {
    color: #fff;
    background: rgba(255,255,255,.12);
    transform: translateX(2px);
    text-decoration: none;
}

.nav-link-admin.active {
    color: #fff;
    background: rgba(255,255,255,.18);
    box-shadow: inset 0 1px 0 rgba(255,255,255,.12);
}

.nav-link-admin.active::before {
    content: "";
    position: absolute;
    left: 0;
    width: 4px;
    height: 24px;
    border-radius: 0 5px 5px 0;
    background: #54e2a6;
}

.sidebar-footer {
    margin-top: auto;
    padding-top: 14px;
    border-top: 1px solid rgba(255,255,255,.18);
}

.sidebar-footer .nav-link-admin:last-child {
    color: #ffd0cd;
}

.sidebar-footer .nav-link-admin:last-child:hover,
.sidebar-footer .nav-link-admin:last-child:focus {
    color: #fff;
    background: rgba(220,38,38,.2);
}

.main-panel {
    flex: 1 1 auto;
    min-width: 0;
    padding: 2px 4px 24px;
}

.card-panel {
    background: var(--dash-surface);
    border: 1px solid var(--dash-border);
    border-radius: 18px;
    box-shadow: 0 9px 26px rgba(31, 54, 88, 0.05);
}

.header-panel {
    position: relative;
    overflow: hidden;
    padding: 28px 32px;
    margin-bottom: 22px;
    border: 1px solid rgba(255,255,255,0.12);
    border-radius: 20px;
    background: linear-gradient(118deg, #102c54 0%, #16497f 56%, #0c8879 130%);
    box-shadow: 0 18px 34px rgba(20,57,102,0.18);
    color: #ffffff;
}

.header-panel::before,
.header-panel::after {
    content: "";
    position: absolute;
    border: 1px solid rgba(255,255,255,0.14);
    border-radius: 50%;
    pointer-events: none;
}

.header-panel::before {
    width: 230px;
    height: 230px;
    right: 55px;
    top: -70px;
}

.header-panel::after {
    width: 145px;
    height: 145px;
    right: 98px;
    top: -28px;
}

.page-kicker {
    position: relative;
    z-index: 1;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: #c7ece4;
    font-size: 0.73rem;
    text-transform: uppercase;
    letter-spacing: 0.13em;
    font-weight: 800;
    margin-bottom: 11px;
}

.page-kicker::before {
    content: "";
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #47dda1;
    box-shadow: 0 0 0 5px rgba(71,221,161,0.15);
}

.header-panel h2 {
    position: relative;
    z-index: 1;
    margin: 0;
    color: #ffffff;
    font-size: clamp(1.45rem, 2.6vw, 2.15rem);
    letter-spacing: -0.045em;
    line-height: 1.2;
}

.header-panel p {
    position: relative;
    z-index: 1;
    margin-top: 8px;
    color: rgba(235,247,255,0.82);
    margin-bottom: 0;
    max-width: 620px;
}

.profile-layout {
    display: grid;
    grid-template-columns: minmax(0, 1.5fr);
    gap: 16px;
}

.profile-card,
.settings-card {
    padding: 20px 21px;
}

.profile-card h3,
.settings-card h3 {
    margin: 0 0 10px;
    color: var(--dash-blue);
    font-size: 1rem;
    font-weight: 800;
    letter-spacing: -0.02em;
}

.profile-card p,
.settings-card p {
    margin-bottom: 16px;
    color: var(--dash-muted);
}

.profile-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
}

.profile-field {
    display: grid;
    gap: 0.35rem;
    padding: 16px;
    border: 1px solid var(--dash-border);
    border-radius: 14px;
    background: var(--dash-surface);
    box-shadow: 0 7px 20px rgba(31, 54, 88, 0.03);
}

.profile-label {
    font-size: 0.71rem;
    font-weight: 800;
    color: var(--dash-muted);
    text-transform: uppercase;
    letter-spacing: 0.06em;
}

.profile-value {
    font-size: 1rem;
    color: var(--dash-ink);
    font-weight: 700;
    word-break: break-word;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 5px 9px;
    border-radius: 999px;
    font-size: 0.72rem;
    font-weight: 800;
    width: fit-content;
    background: #e7f8ef;
    color: #087e55;
}

.settings-card {
    margin-top: 16px;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
}

.form-field {
    display: grid;
    gap: 8px;
}

.form-field.full {
    grid-column: 1 / -1;
}

.form-label {
    margin: 0;
    font-size: 0.8rem;
    color: var(--dash-muted);
    text-transform: uppercase;
    letter-spacing: 0.04em;
    font-weight: 700;
}

.form-control,
.form-select {
    min-height: 46px;
    border: 1px solid var(--dash-border);
    border-radius: 12px;
    padding: 0.7rem 0.9rem;
    background: #ffffff;
    color: var(--dash-ink);
    transition: box-shadow 0.18s ease, border-color 0.18s ease;
}

.form-control:focus,
.form-select:focus {
    outline: none;
    border-color: var(--dash-blue);
    box-shadow: 0 8px 20px rgba(37,99,235,0.08);
}

.btn-primary {
    background: var(--dash-blue);
    border-color: var(--dash-blue);
    border-radius: 10px;
    font-weight: 700;
    padding: 0.7rem 1.1rem;
    box-shadow: 0 12px 28px rgba(37,99,235,0.18);
}

.btn-primary:hover,
.btn-primary:focus,
.btn-primary:active {
    background: #075bd3 !important;
    border-color: #075bd3 !important;
    box-shadow: 0 16px 32px rgba(37,99,235,0.28) !important;
}

.btn-outline-secondary {
    border-radius: 10px;
    border-color: #dfe8f2;
    background: #ffffff;
    color: var(--dash-ink);
    font-weight: 700;
    padding: 0.7rem 1.1rem;
}

.btn-outline-secondary:hover,
.btn-outline-secondary:focus,
.btn-outline-secondary:active {
    background: #f4f7fb !important;
    border-color: #dfe8f2 !important;
    color: var(--dash-ink) !important;
}

.alert {
    border-radius: 12px;
    padding: 0.85rem 1rem;
    margin-bottom: 1rem;
}

.alert-danger {
    background: #fff1f2;
    border-color: #fecdd3;
    color: #b91c1c;
}

.alert-success {
    background: #ecfdf5;
    border-color: #a7f3d0;
    color: #065f46;
}

/* Admin Profile Dark Mode Support */
html[data-theme="dark"] .admin-root {
    --dash-ink: #e5e7eb;
    --dash-navy: #1f2937;
    --dash-blue: #60a5fa;
    --dash-mint: #10b981;
    --dash-surface: #1f2937;
    --dash-bg: #111827;
    --dash-border: #374151;
    --dash-muted: #9ca3af;
    --cg-white: #1f2937;
    --cg-border: #374151;
    --cg-primary: #60a5fa;
    --cg-accent: #10b981;
    --cg-muted: #9ca3af;
    --cg-text: #e5e7eb;
}

html[data-theme="dark"] .admin-root {
    background: var(--dash-bg);
    color: var(--dash-ink);
}

html[data-theme="dark"] .card-panel {
    background: var(--dash-surface);
    border-color: var(--dash-border);
}

html[data-theme="dark"] .form-control,
html[data-theme="dark"] .form-select {
    background: #0f172a;
    color: var(--dash-ink);
    border-color: var(--dash-border);
}

html[data-theme="dark"] .form-control::placeholder,
html[data-theme="dark"] .form-select::placeholder {
    color: var(--dash-muted);
}

html[data-theme="dark"] .form-control:focus,
html[data-theme="dark"] .form-select:focus {
    border-color: var(--dash-blue);
    box-shadow: 0 8px 20px rgba(96, 165, 250, 0.12);
}

html[data-theme="dark"] .btn-primary {
    background: var(--dash-blue);
    border-color: var(--dash-blue);
    box-shadow: 0 12px 28px rgba(96, 165, 250, 0.15);
}

html[data-theme="dark"] .btn-primary:hover,
html[data-theme="dark"] .btn-primary:focus,
html[data-theme="dark"] .btn-primary:active {
    background: #3b82f6 !important;
    border-color: #3b82f6 !important;
    box-shadow: 0 16px 32px rgba(59, 130, 246, 0.28) !important;
}

html[data-theme="dark"] .btn-outline-secondary {
    border-color: rgba(96, 165, 250, 0.2);
    background: rgba(96, 165, 250, 0.08);
    color: var(--dash-ink);
}

html[data-theme="dark"] .btn-outline-secondary:hover,
html[data-theme="dark"] .btn-outline-secondary:focus,
html[data-theme="dark"] .btn-outline-secondary:active {
    background: rgba(96, 165, 250, 0.16) !important;
    border-color: rgba(96, 165, 250, 0.3) !important;
    color: var(--dash-ink) !important;
}

html[data-theme="dark"] .alert-danger {
    background: rgba(220, 38, 38, 0.15);
    border-color: rgba(220, 38, 38, 0.3);
    color: #fca5a5;
}

html[data-theme="dark"] .alert-success {
    background: rgba(16, 185, 129, 0.15);
    border-color: rgba(16, 185, 129, 0.3);
    color: #86efac;
}

html[data-theme="dark"] .status-badge {
    background: rgba(16, 185, 129, 0.2);
    color: #86efac;
}

@media (max-width: 991.98px) {
    .admin-root {
        flex-direction: column;
        padding: 16px;
        gap: 16px;
    }

    .sidebar {
        width: 100%;
        position: static;
    }

    .profile-grid,
    .form-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 767.98px) {
    .main-panel {
        padding: 0;
    }

    .header-panel {
        padding: 22px 20px;
    }

    .profile-card,
    .settings-card {
        padding: 18px 16px;
    }
}
</style>

<main class="container-fluid admin-root">
    <aside class="sidebar">
        <div class="sidebar-branding brand">
            <a class="brand-mark-sm" href="dashboard.php" aria-label="Admin dashboard"><img src="../assets/images/logo.webp" alt="logo" width="40" height="40"></a>
            <div>
                <div class="brand-title">Career Grow Infotech</div>
                <div class="text-soft small">Admin Portal</div>
            </div>
        </div>

        <div class="sidebar-navigation">
            <nav class="mt-3 sidebar-nav" aria-label="Sidebar navigation">
                <a href="dashboard.php" class="nav-link-admin"><i class="bi bi-speedometer2"></i> Dashboard</a>
                <a href="jobs.php" class="nav-link-admin"><i class="bi bi-briefcase"></i> Jobs</a>
                <a href="applicants.php" class="nav-link-admin"><i class="bi bi-people"></i> Applicants</a>
                <a href="candidate-details.php" class="nav-link-admin"><i class="bi bi-person-badge"></i> Candidates</a>
                <a href="contact-messages.php" class="nav-link-admin"><i class="bi bi-envelope-paper"></i> Contact Messages</a>
                <a href="settings.php" class="nav-link-admin"><i class="bi bi-gear"></i> Settings</a>
                <a href="profile.php" class="nav-link-admin active"><i class="bi bi-person-circle"></i> Profile</a>
            </nav>

            <div class="mt-auto pt-3 sidebar-footer">
                <a href="../index.php" class="d-block nav-link-admin"><i class="bi bi-house"></i> Back to Homepage</a>
                <a href="../logout.php" class="d-block nav-link-admin"><i class="bi bi-box-arrow-right"></i> Logout</a>
            </div>
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
                <p>Review and update your account and personal information.</p>
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
        </div>

        <div class="card-panel settings-card" style="margin-top: 1rem;">
            <h3>Edit Account Information</h3>
            <p>Update the details for the current administrator account.</p>

            <?php if (!empty($profileErrors)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php foreach ($profileErrors as $error): ?>
                        <div><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($profileSuccess)): ?>
                <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($profileSuccess, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <form method="post" novalidate>
                <input type="hidden" name="profile_update" value="1">
                <div class="form-grid">
                    <div class="form-field">
                        <label class="form-label" for="profile-name">Full Name</label>
                        <input class="form-control" id="profile-name" type="text" name="name" value="<?php echo htmlspecialchars($profileValues['name'], ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>

                    <div class="form-field">
                        <label class="form-label" for="profile-email">Email Address</label>
                        <input class="form-control" id="profile-email" type="email" name="email" value="<?php echo htmlspecialchars($profileValues['email'], ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>

                    <div class="form-field full">
                        <label class="form-label" for="profile-phone">Phone</label>
                        <input class="form-control" id="profile-phone" type="tel" name="phone" value="<?php echo htmlspecialchars($profileValues['phone'], ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                </div>

                <div class="mt-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <a href="dashboard.php" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
