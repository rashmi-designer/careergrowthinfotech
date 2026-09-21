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

.settings-card {
    padding: 1.15rem 1.2rem;
}

.settings-card h3 {
    margin: 0 0 0.65rem;
    color: var(--cg-accent);
    font-size: 1.08rem;
}

.settings-card p {
    color: var(--cg-muted);
    margin-bottom: 1rem;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
}

.form-field {
    display: grid;
    gap: 0.4rem;
}

.form-field.full {
    grid-column: 1 / -1;
}

.form-label {
    font-size: 0.82rem;
    color: var(--cg-muted);
    text-transform: uppercase;
    letter-spacing: 0.04em;
    font-weight: 700;
}

.form-control,
.form-select {
    min-height: 44px;
}

.alert {
    margin-bottom: 1rem;
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
                <a href="dashboard.php" aria-label="Admin dashboard"><img src="../assets/images/logo.webp" alt="Career Grow Infotech logo" width="34" height="34" loading="lazy"></a>
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
