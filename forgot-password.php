<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/mailer.php';

$pageTitle = 'Forgot Password - Career Grow Infotech';

$errors = [];
$email = '';
$confirmationShown = false;

// CSRF token (reusing project pattern)
if (empty($_SESSION['forgot_password_token'])) {
    $_SESSION['forgot_password_token'] = bin2hex(random_bytes(16));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = (string)($_POST['forgot_password_token'] ?? '');

    if (!hash_equals($_SESSION['forgot_password_token'] ?? '', $token)) {
        $errors[] = 'Invalid form submission.';
    }

    $email = trim((string)($_POST['email'] ?? ''));

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (empty($errors)) {
        try {
            $conn = getDbConnection();

            // Lookup user by email
            $stmt = $conn->prepare('SELECT id, name, email FROM users WHERE email = ? LIMIT 1');
            if ($stmt === false) {
                throw new RuntimeException('Database error.');
            }

            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();

            // Always show the same generic confirmation message to avoid account enumeration
            $confirmationShown = true;

            if ($user) {
                $userId = (int)$user['id'];

                // Create a secure token, store only its hash.
                $plaintextToken = bin2hex(random_bytes(32)); // 64 hex chars
                $tokenHash = hash('sha256', $plaintextToken);

                // Per-user invalidation: mark existing unused tokens as used to ensure single active token
                // This is a lightweight, per-user operation and avoids leaving multiple valid tokens active.
                $conn->begin_transaction();

                $upd = $conn->prepare('UPDATE password_resets SET used_at = NOW() WHERE user_id = ? AND used_at IS NULL');
                if ($upd) {
                    $upd->bind_param('i', $userId);
                    $upd->execute();
                    $upd->close();
                }

                $ins = $conn->prepare('INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))');
                if ($ins === false) {
                    $conn->rollback();
                    throw new RuntimeException('Failed to create password reset token.');
                }

                $ins->bind_param('is', $userId, $tokenHash);
                if (!$ins->execute()) {
                    $ins->close();
                    $conn->rollback();
                    throw new RuntimeException('Failed to create password reset token.');
                }

                $ins->close();
                $conn->commit();

                $userEmail = $user['email'] ?? $email;
                $userName = $user['name'] ?? 'User';
                $resetUrl = rtrim((string) project_config_get('APP_URL', 'http://localhost/careergrowthinfotech'), '/')
                    . '/reset-password.php?token=' . rawurlencode($plaintextToken);

                try {
                    send_password_reset_email($userEmail, $userName, $resetUrl);
                } catch (Throwable $e) {
                    error_log('Password reset email failed for ' . $userEmail . ': ' . $e->getMessage());
                }

                $conn->close();
            }
        } catch (Throwable $e) {
            if (isset($conn) && $conn->connect_errno === 0 && $conn->in_transaction) {
                $conn->rollback();
            }
            error_log('forgot-password processing error: ' . $e->getMessage());
            // Show the same generic confirmation to the user to avoid leaking details
            $confirmationShown = true;
        }
    }

    // Regenerate CSRF token after a POST to prevent replay attacks
    unset($_SESSION['forgot_password_token']);
    $_SESSION['forgot_password_token'] = bin2hex(random_bytes(16));
}

// Hide public layout similar to login page
$hidePublicLayout = true;
require_once __DIR__ . '/includes/header.php';
?>

<style>
/* Minimal auth-style form */
.auth-center{min-height:60vh;display:flex;align-items:center;justify-content:center;padding:48px}
.fp-card{width:100%;max-width:520px;background:#fff;border:1px solid var(--cg-border);border-radius:12px;padding:28px}
.back-link{display:inline-block;margin-top:12px;color:var(--cg-muted)}
</style>

<main class="auth-center">
    <div class="fp-card">
        <h3>Forgot Password?</h3>
        <p class="text-muted">Enter your registered email address and we'll send password reset instructions if an account exists.</p>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $e): ?>
                    <div><?php echo htmlspecialchars($e, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($confirmationShown): ?>
            <div class="alert alert-info">
                If an account exists with this email, a password reset link has been sent.
            </div>
            <a href="login.php" class="back-link">&larr; Back to Login</a>
        <?php else: ?>
            <form method="post" novalidate>
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="you@example.com" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>

                <input type="hidden" name="forgot_password_token" value="<?php echo htmlspecialchars($_SESSION['forgot_password_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">

                <div>
                    <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
                </div>
            </form>
            <a href="login.php" class="back-link">&larr; Back to Login</a>
        <?php endif; ?>
    </div>
</main>

<?php // Do not include public footer when $hidePublicLayout is true
?>
