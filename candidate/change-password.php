<?php
declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = 'Change Password - Career Grow Infotech';

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

$userId = (int)$_SESSION['user_id'];
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = trim((string)($_POST['current_password'] ?? ''));
    $newPassword = trim((string)($_POST['new_password'] ?? ''));
    $confirmPassword = trim((string)($_POST['confirm_password'] ?? ''));

    // Validation
    if ($currentPassword === '') { $errors[] = 'Current password is required.'; }
    if ($newPassword === '') { $errors[] = 'New password is required.'; }
    elseif (strlen($newPassword) < 8) { $errors[] = 'Password must be at least 8 characters.'; }
    if ($confirmPassword === '') { $errors[] = 'Please confirm your new password.'; }
    elseif ($newPassword !== $confirmPassword) { $errors[] = 'New passwords do not match.'; }

    if (empty($errors)) {
        $conn = getDbConnection();

        // Get current password hash
        $stmt = $conn->prepare('SELECT password FROM users WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$user || !password_verify($currentPassword, $user['password'])) {
            $errors[] = 'Current password is incorrect.';
        } else {
            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $updateStmt = $conn->prepare('UPDATE users SET password = ? WHERE id = ?');
            if ($updateStmt) {
                $updateStmt->bind_param('si', $hashedPassword, $userId);
                if ($updateStmt->execute()) {
                    $success = true;
                } else {
                    $errors[] = 'Failed to update password. Please try again.';
                }
                $updateStmt->close();
            }
        }
        $conn->close();
    }
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<style>
.password-container {
    width: min(100% - 2rem, 660px);
    margin: clamp(1.5rem, 4vw, 3.5rem) auto;
}

.password-section {
    background: var(--cg-white);
    border: 1px solid var(--cg-border);
    border-radius: 1.5rem;
    overflow: hidden;
    box-shadow: 0 24px 64px rgba(16,44,84,0.1), 0 4px 12px rgba(16,44,84,0.04);
}

.password-heading {
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 2rem 2.25rem;
    background: linear-gradient(118deg, #102c54 0%, #16497f 56%, #0c8879 130%);
    border-bottom: 3px solid #54d6b0;
}

.password-heading::after {
    content: "";
    position: absolute;
    width: 190px;
    height: 190px;
    top: -110px;
    right: -40px;
    border: 1px solid rgba(255,255,255,0.16);
    border-radius: 50%;
    box-shadow: 0 0 0 28px rgba(255,255,255,0.04);
    pointer-events: none;
}

.password-heading > i {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 52px;
    height: 52px;
    border-radius: 16px;
    background: rgba(255,255,255,0.12);
    border: 1px solid rgba(255,255,255,0.22);
    color: #bdffe5;
    font-size: 1.5rem;
}

.password-heading h2 {
    position: relative;
    margin: 0;
    color: #fff;
    font-size: clamp(1.4rem, 3.5vw, 1.8rem);
    font-weight: 700;
    letter-spacing: -0.035em;
    line-height: 1.25;
}

.password-body { padding: 2rem 2.25rem 2.25rem; }
.password-body form > .mb-3 { margin-bottom: 1.4rem !important; }
.password-body .form-label { margin-bottom: 0.55rem; font-size: 0.9rem; font-weight: 650; color: var(--cg-accent); }
.password-body .form-control {
    min-height: 52px;
    padding: 0.85rem 1rem;
    border-color: #dbe3ed;
    border-radius: 0.75rem;
    background: #f8fafc;
    font-size: 0.9rem;
    transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
}
.password-body .form-control::placeholder { color: #738198; opacity: 1; }
.password-body .form-control:focus { background: var(--cg-white); border-color: #287eaa; box-shadow: 0 0 0 4px rgba(40,126,170,0.12); }
.password-body .form-text { display: block; margin-top: 0.55rem; font-size: 0.8rem; line-height: 1.5; }
.password-actions { padding-top: 1.25rem; margin-top: 1.75rem; border-top: 1px solid var(--cg-border); }
.password-actions .btn { min-height: 48px; display: inline-flex; align-items: center; justify-content: center; border-radius: 0.75rem; padding: 0.75rem 1.25rem; font-size: 0.9rem; font-weight: 650; }
.password-actions .btn-primary { flex: 1; background: #16497f; border-color: #16497f; box-shadow: 0 6px 14px rgba(22,73,127,0.16); }
.password-actions .btn-primary:hover { background: #102c54; border-color: #102c54; }
.password-actions .btn-outline-secondary { color: #52647b; border-color: #d5deea; background: #fff; }
.password-actions .btn-outline-secondary:hover { color: #16497f; border-color: #a6bfd8; background: #f1f6fc; }
.password-actions .btn:focus-visible { outline: 3px solid #287eaa; outline-offset: 3px; }
.password-body .alert { border-radius: 0.75rem; font-size: 0.9rem; }

@media (max-width: 575.98px) {
    .password-heading { padding: 1.5rem; gap: 0.8rem; }
    .password-heading > i { flex-basis: 44px; height: 44px; border-radius: 12px; }
    .password-body { padding: 1.5rem; }
    .password-actions { flex-direction: column; }
}

@media (prefers-reduced-motion: reduce) {
    .password-body .form-control { transition: none; }
}
</style>

<main class="password-container">
    <div class="password-section">
        <div class="password-heading">
            <i class="bi bi-shield-lock" aria-hidden="true"></i>
            <h2>Change Your Password</h2>
        </div>
        <div class="password-body">

        <?php if ($success): ?>
            <div class="alert alert-success">Password changed successfully!</div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $e): ?>
                    <div><?php echo htmlspecialchars($e, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" novalidate>
            <div class="mb-3">
                <label for="current_password" class="form-label">Current Password</label>
                <input type="password" id="current_password" name="current_password" class="form-control" placeholder="Enter your current password" required>
            </div>

            <div class="mb-3">
                <label for="new_password" class="form-label">New Password</label>
                <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Enter a new password (minimum 8 characters)" required>
                <small class="form-text text-muted">Password must be at least 8 characters long.</small>
            </div>

            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Confirm your new password" required>
            </div>

            <div class="d-flex gap-2 password-actions">
                <button type="submit" class="btn btn-primary">Change Password</button>
                <a href="dashboard.php" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
