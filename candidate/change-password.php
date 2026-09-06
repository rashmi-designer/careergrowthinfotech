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
.password-container { max-width: 600px; margin: 2rem auto; }
.password-section { background: var(--cg-white); border: 1px solid var(--cg-border); border-radius: 1rem; padding: 2rem; }
.password-section h2 { font-weight: 700; margin-bottom: 1.5rem; }
input.form-control { min-height: 44px; }
</style>

<main class="password-container py-5">
    <div class="password-section">
        <h2>Change Your Password</h2>

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

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Change Password</button>
                <a href="dashboard.php" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
