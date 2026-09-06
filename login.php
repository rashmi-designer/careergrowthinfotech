<?php
declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/includes/db.php';

$pageTitle = 'Candidate Login - Career Grow Infotech';

// Redirect already authenticated candidate
if (!empty($_SESSION['user_id']) && !empty($_SESSION['user_role']) && $_SESSION['user_role'] === 'candidate') {
    header('Location: candidate/dashboard.php');
    exit;
}

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string)($_POST['email'] ?? ''));
    $password = trim((string)($_POST['password'] ?? ''));

    if ($email === '' || $password === '') {
        $errors[] = 'Please enter your email address and password.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    } else {
        $conn = getDbConnection();
        $stmt = $conn->prepare('SELECT id, name, email, password, role, status FROM users WHERE email = ? AND role = ? LIMIT 1');
        if ($stmt) {
            $candidateRole = 'candidate';
            $stmt->bind_param('ss', $email, $candidateRole);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();

            if (!$user) {
                $errors[] = 'Invalid email or password.';
            } else {
                // check status
                if ((int)$user['status'] !== 1) {
                    $errors[] = 'Your account is currently inactive. Please contact support.';
                } else {
                    // verify password
                    if (password_verify($password, $user['password'])) {
                        // successful login
                        session_regenerate_id(true);
                        $_SESSION['user_id'] = (int)$user['id'];
                        $_SESSION['user_name'] = $user['name'];
                        $_SESSION['user_email'] = $user['email'];
                        $_SESSION['user_role'] = $user['role'];

                        // Redirect to job application if job_id was provided
                        $redirectJobId = isset($_GET['job_id']) ? (int)$_GET['job_id'] : 0;
                        if ($redirectJobId > 0) {
                            header('Location: candidate/apply.php?job_id=' . $redirectJobId);
                        } else {
                            header('Location: candidate/dashboard.php');
                        }
                        exit;
                    } else {
                        $errors[] = 'Invalid email or password.';
                    }
                }
            }
        } else {
            // fail safe (do not expose DB errors)
            $errors[] = 'An unexpected error occurred. Please try again later.';
        }
        $conn->close();
    }
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<style>
.login-container { max-width: 500px; margin: 3rem auto; }
.login-card { background: var(--cg-white); border: 1px solid var(--cg-border); border-radius: 1rem; padding: 2rem; box-shadow: 0 12px 30px rgba(15,23,42,0.04); }
.login-card h2 { font-weight: 700; margin-bottom: 0.5rem; }
.login-subtitle { color: var(--cg-muted); margin-bottom: 1.5rem; }
.required { color: #d63384; }
input.form-control { min-height: 44px; }
.back-link { color: var(--cg-muted); text-decoration: none; }
.back-link:hover { color: var(--cg-primary); }
</style>

<main class="login-container py-5">
    <div class="login-card">
        <h2>Candidate Login</h2>
        <p class="login-subtitle">Sign in to your account</p>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $e): ?>
                    <div><?php echo htmlspecialchars($e, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" novalidate>
            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="you@example.com" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
            </div>

            <button type="submit" class="btn btn-primary w-100 mb-3">Sign In</button>
        </form>

        <div class="text-center">
            <p class="mb-0">Don't have an account? <a href="register.php" class="text-primary">Register here</a></p>
        </div>

        <div class="text-center mt-3">
            <a href="index.php" class="back-link">← Back to Home</a>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
