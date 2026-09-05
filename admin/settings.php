<?php

declare(strict_types=1);
session_start();

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/admin-auth.php';
require_admin();

$pageTitle = 'Settings - Admin';
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
$passwordErrors = [];
$passwordSuccess = '';

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password_update'])) {
    $currentPassword = trim((string)($_POST['current_password'] ?? ''));
    $newPassword = trim((string)($_POST['new_password'] ?? ''));
    $confirmPassword = trim((string)($_POST['confirm_password'] ?? ''));

    if ($currentPassword === '') {
        $passwordErrors[] = 'Current password is required.';
    }
    if ($newPassword === '') {
        $passwordErrors[] = 'New password is required.';
    } elseif (strlen($newPassword) < 8) {
        $passwordErrors[] = 'New password must be at least 8 characters long.';
    }
    if ($confirmPassword === '') {
        $passwordErrors[] = 'Please confirm your new password.';
    }
    if ($newPassword !== '' && $confirmPassword !== '' && $newPassword !== $confirmPassword) {
        $passwordErrors[] = 'New password and confirmation do not match.';
    }

    if (empty($passwordErrors)) {
        $passwordCheckStmt = $conn->prepare('SELECT password FROM users WHERE id = ? AND role = ? LIMIT 1');
        if ($passwordCheckStmt) {
            $passwordCheckStmt->bind_param('is', $adminId, $adminRole);
            $passwordCheckStmt->execute();
            $passwordResult = $passwordCheckStmt->get_result();
            $existingUser = $passwordResult->fetch_assoc();
            $passwordCheckStmt->close();

            if (!$existingUser || !password_verify($currentPassword, (string)($existingUser['password'] ?? ''))) {
                $passwordErrors[] = 'Current password is incorrect.';
            }
        } else {
            $passwordErrors[] = 'Unable to verify your current password right now.';
        }
    }

    if (empty($passwordErrors)) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $passwordUpdateStmt = $conn->prepare('UPDATE users SET password = ? WHERE id = ? AND role = ? LIMIT 1');
        if ($passwordUpdateStmt) {
            $passwordUpdateStmt->bind_param('sis', $hashedPassword, $adminId, $adminRole);
            $passwordUpdated = $passwordUpdateStmt->execute();
            $passwordUpdateStmt->close();

            if ($passwordUpdated) {
                session_regenerate_id(true);
                $passwordSuccess = 'Your password was changed successfully.';
            } else {
                $passwordErrors[] = 'Unable to update your password right now.';
            }
        } else {
            $passwordErrors[] = 'Unable to update your password right now.';
        }
    }
}

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

.topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
}

.topbar-title h1 {
    font-size: clamp(1.5rem, 2vw, 2rem);
    margin: 0;
    font-weight: 700;
    letter-spacing: -0.02em;
}

.topbar-subtitle {
    font-size: 0.9rem;
    color: var(--cg-muted);
}

.user-pill {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    background: var(--cg-white);
    border: 1px solid var(--cg-border);
    border-radius: 999px;
    padding: 0.5rem 0.8rem;
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.04);
}

.user-pill .avatar {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: rgba(13,110,253,0.1);
    color: var(--cg-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
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

.settings-layout {
    display: grid;
    grid-template-columns: 280px minmax(0, 1fr);
    gap: 1rem;
}

.settings-nav {
    padding: 1rem;
}

.settings-nav .nav-title {
    font-size: 0.8rem;
    color: var(--cg-muted);
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin-bottom: 0.75rem;
}

.nav-list {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}

.nav-list a {
    display: flex;
    align-items: center;
    gap: 0.7rem;
    padding: 0.7rem 0.8rem;
    border-radius: 0.7rem;
    color: var(--cg-accent);
    font-weight: 600;
}

.nav-list a.active {
    color: var(--cg-primary);
    background: rgba(13,110,253,0.06);
}

.settings-content {
    display: grid;
    gap: 1rem;
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

    .settings-layout {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 575.98px) {
    .admin-root {
        padding: 1rem;
    }

    .topbar {
        flex-wrap: wrap;
    }

    .form-grid {
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
            <a href="settings.php" class="nav-link-admin active"><i class="bi bi-gear"></i> Settings</a>
        </nav>

        <div class="sidebar-footer">
            <a href="../index.php" class="nav-link-admin"><i class="bi bi-house"></i> Back to Website</a>
            <a href="../logout.php" class="nav-link-admin"><i class="bi bi-box-arrow-right"></i> Logout</a>
        </div>
    </aside>

    <section class="main-panel">
        <div class="topbar">
            <div class="topbar-title">
                <h1>Settings</h1>
                <div class="topbar-subtitle">Manage your administrator account preferences</div>
            </div>
            <div class="user-pill">
                <div class="avatar"><i class="bi bi-person-circle"></i></div>
                <div>
                    <div class="fw-semibold">Administrator</div>
                    <div class="text-muted small">Admin</div>
                </div>
            </div>
        </div>

        <div class="card-panel header-panel">
            <div class="page-kicker"><i class="bi bi-sliders"></i> Admin / Settings</div>
            <div>
                <h2>Settings</h2>
                <p>Manage the authenticated administrator account and security details for the Career Grow Infotech portal.</p>
            </div>
        </div>

        <div class="settings-layout">
            <aside class="card-panel settings-nav">
                <div class="nav-title">Account</div>
                <div class="nav-list">
                    <a href="#profile" class="active"><i class="bi bi-person-circle"></i> Profile</a>
                    <a href="#security"><i class="bi bi-shield-lock"></i> Security</a>
                </div>
            </aside>

            <div class="settings-content">
                <div id="profile" class="card-panel settings-card">
                    <h3>Account Information</h3>
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

                <div id="security" class="card-panel settings-card">
                    <h3>Security</h3>
                    <p>Update your password for the current administrator account.</p>

                    <?php if (!empty($passwordErrors)): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php foreach ($passwordErrors as $error): ?>
                                <div><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($passwordSuccess)): ?>
                        <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($passwordSuccess, ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php endif; ?>

                    <form method="post" novalidate>
                        <input type="hidden" name="password_update" value="1">
                        <div class="form-grid">
                            <div class="form-field full">
                                <label class="form-label" for="current-password">Current Password</label>
                                <input class="form-control" id="current-password" type="password" name="current_password" placeholder="Enter your current password" required>
                            </div>

                            <div class="form-field full">
                                <label class="form-label" for="new-password">New Password</label>
                                <input class="form-control" id="new-password" type="password" name="new_password" placeholder="Enter a new password" required>
                            </div>

                            <div class="form-field full">
                                <label class="form-label" for="confirm-password">Confirm New Password</label>
                                <input class="form-control" id="confirm-password" type="password" name="confirm_password" placeholder="Re-enter new password" required>
                            </div>
                        </div>

                        <div class="mt-3 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Update Password</button>
                            <button type="reset" class="btn btn-outline-secondary">Reset</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
