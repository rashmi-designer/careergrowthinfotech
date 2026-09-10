<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/includes/db.php';

$pageTitle = 'Reset Password - Career Grow Infotech';

function getResetRecord(mysqli $conn, string $token): ?array
{
    $token = trim($token);
    if ($token === '') {
        return null;
    }

    $tokenHash = hash('sha256', $token);

    $stmt = $conn->prepare(
        'SELECT pr.id, pr.user_id, pr.token_hash, pr.expires_at, pr.used_at, u.name, u.email, u.role, u.status
         FROM password_resets pr
         INNER JOIN users u ON u.id = pr.user_id
         WHERE pr.token_hash = ?
           AND pr.used_at IS NULL
           AND pr.expires_at > NOW()
           AND u.status = 1
         ORDER BY pr.expires_at DESC
         LIMIT 1'
    );

    if ($stmt === false) {
        return null;
    }

    $stmt->bind_param('s', $tokenHash);
    if (!$stmt->execute()) {
        $stmt->close();
        return null;
    }

    $result = $stmt->get_result();
    $record = $result->fetch_assoc();
    $stmt->close();

    return $record ?: null;
}

function is_valid_reset_password(string $password): array
{
    $errors = [];

    if ($password === '') {
        $errors[] = 'New password is required.';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }

    return $errors;
}

$errors = [];
$successMessage = '';
$token = trim((string)($_GET['token'] ?? ''));
$tokenFromPost = trim((string)($_POST['token'] ?? ''));
$resetTokenValid = false;
$resetUserRole = '';
$loginRoute = 'login.php';

if (empty($_SESSION['reset_password_token'])) {
    $_SESSION['reset_password_token'] = bin2hex(random_bytes(16));
}

$conn = getDbConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedToken = trim((string)($_POST['reset_password_token'] ?? ''));
    if (!hash_equals($_SESSION['reset_password_token'] ?? '', $submittedToken)) {
        $errors[] = 'Invalid form submission.';
    }

    $token = $tokenFromPost;
    $resetRecord = getResetRecord($conn, $token);

    if ($token === '') {
        $errors[] = 'This password reset link is invalid or has expired.';
    } elseif ($resetRecord === null) {
        $errors[] = 'This password reset link is invalid or has expired.';
    }

    if (empty($errors) && $resetRecord !== null) {
        $newPassword = trim((string)($_POST['new_password'] ?? ''));
        $confirmPassword = trim((string)($_POST['confirm_password'] ?? ''));

        $newPasswordErrors = is_valid_reset_password($newPassword);
        foreach ($newPasswordErrors as $passwordError) {
            $errors[] = $passwordError;
        }

        if ($confirmPassword === '') {
            $errors[] = 'Please confirm your new password.';
        } elseif ($newPassword !== $confirmPassword) {
            $errors[] = 'Passwords do not match.';
        }

        if (empty($errors)) {
            try {
                $conn->begin_transaction();

                $freshResetRecord = getResetRecord($conn, $token);
                if ($freshResetRecord === null) {
                    throw new RuntimeException('Reset token is invalid or expired.');
                }

                $userId = (int)$freshResetRecord['user_id'];
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

                $userUpdate = $conn->prepare('UPDATE users SET password = ? WHERE id = ? AND status = 1 LIMIT 1');
                if ($userUpdate === false) {
                    throw new RuntimeException('Unable to update password.');
                }

                $userUpdate->bind_param('si', $hashedPassword, $userId);
                if (!$userUpdate->execute()) {
                    $userUpdate->close();
                    throw new RuntimeException('Unable to update password.');
                }
                $userUpdate->close();

                $tokenUpdate = $conn->prepare('UPDATE password_resets SET used_at = NOW() WHERE id = ? AND used_at IS NULL LIMIT 1');
                if ($tokenUpdate === false) {
                    throw new RuntimeException('Unable to consume reset token.');
                }

                $resetId = (int)$freshResetRecord['id'];
                $tokenUpdate->bind_param('i', $resetId);
                if (!$tokenUpdate->execute()) {
                    $tokenUpdate->close();
                    throw new RuntimeException('Unable to consume reset token.');
                }
                $tokenUpdate->close();

                $expiredOtherTokens = $conn->prepare('UPDATE password_resets SET used_at = NOW() WHERE user_id = ? AND used_at IS NULL AND id <> ?');
                if ($expiredOtherTokens !== false) {
                    $expiredOtherTokens->bind_param('ii', $userId, $resetId);
                    $expiredOtherTokens->execute();
                    $expiredOtherTokens->close();
                }

                $conn->commit();
                $successMessage = 'Your password has been reset successfully.';
                $resetTokenValid = false;
                $loginRoute = ($freshResetRecord['role'] ?? '') === 'admin' ? 'admin/login.php' : 'login.php';
            } catch (Throwable $e) {
                if ($conn->in_transaction) {
                    $conn->rollback();
                }
                error_log('Password reset update failed: ' . $e->getMessage());
                $errors[] = 'Unable to reset your password at this time.';
            }
        }
    }

    unset($_SESSION['reset_password_token']);
    $_SESSION['reset_password_token'] = bin2hex(random_bytes(16));

    if ($successMessage !== '') {
        $token = '';
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($token !== '') {
        $resetRecord = getResetRecord($conn, $token);
        if ($resetRecord !== null) {
            $resetTokenValid = true;
            $resetUserRole = (string)($resetRecord['role'] ?? '');
            $loginRoute = $resetUserRole === 'admin' ? 'admin/login.php' : 'login.php';
        }
    }
}

if ($token !== '' && !$resetTokenValid && empty($errors) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $errors[] = 'This password reset link is invalid or has expired.';
}

$conn->close();

// Hide public layout similar to login page
$hidePublicLayout = true;
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<style>
html, body { height: 100%; }
.auth-shell { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 36px 18px; background: linear-gradient(180deg, #f8fafc 0%, #eef4ff 100%); }
.reset-card { width: 100%; max-width: 520px; background: var(--cg-white); border: 1px solid var(--cg-border); border-radius: 18px; padding: 30px; box-shadow: 0 24px 46px rgba(15, 23, 42, 0.08); }
.logo-wrap { display: inline-flex; align-items: center; gap: 10px; margin-bottom: 14px; }
.logo-wrap img { height: 38px; width: auto; }
.logo-wrap span { font-weight: 800; color: var(--cg-dark); }
.reset-card h2 { font-weight: 800; margin-bottom: 8px; }
.reset-card p { color: var(--cg-muted); }
.form-label { font-weight: 700; }
.form-control { min-height: 52px; border-radius: 12px; }
.btn-reset { min-height: 52px; border-radius: 12px; font-weight: 700; }
.action-row { display: flex; gap: 12px; flex-wrap: wrap; margin-top: 18px; }
.back-link { display: inline-block; margin-top: 10px; color: var(--cg-muted); }
@media (max-width: 576px) { .reset-card { padding: 22px; } }
</style>

<main class="auth-shell">
    <div class="reset-card" role="region" aria-label="Reset password form">
        <div class="logo-wrap">
            <img src="assets/images/logo.webp" alt="Career Grow Infotech logo">
            <span>Career Grow Infotech</span>
        </div>

        <?php if ($successMessage !== ''): ?>
            <h2>Reset Your Password</h2>
            <div class="alert alert-success"><?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?></div>
            <div class="action-row">
                <a href="<?php echo htmlspecialchars($loginRoute, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary btn-reset">Login</a>
            </div>
        <?php elseif (!empty($errors)): ?>
            <h2>Reset Your Password</h2>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <div><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endforeach; ?>
            </div>
            <div class="action-row">
                <a href="login.php" class="btn btn-outline-secondary">Back to Login</a>
                <a href="forgot-password.php" class="btn btn-outline-primary">Request New Link</a>
            </div>
        <?php elseif ($resetTokenValid): ?>
            <h2>Reset Your Password</h2>
            <p>Choose a new password for your account.</p>

            <form method="post" novalidate>
                <input type="hidden" name="reset_password_token" value="<?php echo htmlspecialchars($_SESSION['reset_password_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">

                <div class="mb-3">
                    <label for="new_password" class="form-label">New Password</label>
                    <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Enter a new password" required>
                </div>

                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Re-enter your password" required>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-reset">Reset Password</button>
                </div>
            </form>

            <a href="login.php" class="back-link">&larr; Back to Login</a>
        <?php else: ?>
            <h2>Reset Your Password</h2>
            <div class="alert alert-danger">This password reset link is invalid or has expired.</div>
            <div class="action-row">
                <a href="login.php" class="btn btn-outline-secondary">Back to Login</a>
                <a href="forgot-password.php" class="btn btn-outline-primary">Forgot Password</a>
            </div>
        <?php endif; ?>
    </div>
</main>
