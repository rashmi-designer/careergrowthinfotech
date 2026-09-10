<?php
declare(strict_types=1);
session_start();

$pageTitle = 'Admin Login - Career Grow Infotech';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/admin-auth.php';

// Redirect already authenticated admin
if (!empty($_SESSION['user_id']) && !empty($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    header('Location: dashboard.php');
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
        $stmt = $conn->prepare('SELECT id, name, email, password, role, status FROM users WHERE email = ? LIMIT 1');
        if ($stmt) {
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();

            if (!$user) {
                $errors[] = 'Invalid email or password.';
            } else {
                // check role and status
                if (($user['role'] ?? '') !== 'admin') {
                    $errors[] = 'Invalid email or password.';
                } elseif ((int)$user['status'] !== 1) {
                    $errors[] = 'Your account is currently inactive. Please contact the administrator.';
                } else {
                    // verify password
                    if (password_verify($password, $user['password'])) {
                        // successful login
                        session_regenerate_id(true);
                        $_SESSION['user_id'] = (int)$user['id'];
                        $_SESSION['user_name'] = $user['name'];
                        $_SESSION['user_email'] = $user['email'];
                        $_SESSION['user_role'] = $user['role'];

                        header('Location: dashboard.php');
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
?>

<?php
// Include header after processing so redirects work; do not include public navbar for admin login
// Hide public footer/header for this standalone admin login page
$hidePublicLayout = true;
require_once __DIR__ . '/../includes/header.php';
?>

<style>
/* Admin login page local styles — premium split layout */
*,*::before,*::after{box-sizing:border-box}
html,body{height:100dvh;margin:0}
.admin-login-wrap{min-height:100dvh;display:flex;align-items:stretch;justify-content:center;background:var(--cg-bg,#f6f8fb)}
.admin-panel{width:100%;max-width:1200px;display:grid;grid-template-columns:46% 54%;gap:2rem;padding:2.25rem;align-items:stretch}
.admin-left{position:relative;border-radius:1rem;overflow:hidden;background-image:url('../assets/images/buildcareer1.jpg');background-size:cover;background-position:center;display:flex;flex-direction:column;justify-content:center;color:#fff}
.admin-left::before{content:'';position:absolute;inset:0;background:linear-gradient(180deg, rgba(6,18,42,0.55), rgba(6,18,42,0.55));}
.admin-left-inner{position:relative;z-index:2;padding:clamp(20px,4vw,48px)}
.brand-pill{display:inline-flex;align-items:center;gap:12px;padding:8px 14px;border-radius:999px;background:rgba(255,255,255,0.06);backdrop-filter:blur(6px);border:1px solid rgba(255,255,255,0.08);box-shadow:0 6px 18px rgba(2,6,23,0.28)}
.brand-pill img{height:36px;width:auto;display:block}
.pill-text{font-weight:600;font-size:1rem;color:#fff}
.admin-eyebrow{margin-top:12px;font-size:.75rem;letter-spacing:.12em;text-transform:uppercase;color:rgba(255,255,255,0.9)}
.admin-head{font-size:clamp(1.8rem,3.6vw,2.6rem);line-height:1.02;margin:12px 0 8px;font-weight:800}
.admin-desc{color:rgba(255,255,255,0.9);max-width:48ch;margin-bottom:1rem}
.admin-features{display:flex;flex-direction:column;gap:10px;margin-top:8px}
.feature-row{display:flex;gap:10px;align-items:flex-start}
.feature-dot{width:10px;height:10px;border-radius:50%;background:rgba(255,255,255,0.12);margin-top:6px}
.feature-text{color:rgba(255,255,255,0.95);font-weight:600}

.admin-right{display:flex;align-items:center;justify-content:center}
.admin-login-card{background:var(--cg-white);border:1px solid var(--cg-border);border-radius:18px;padding:2rem;box-shadow:0 28px 60px rgba(15,23,42,0.08);width:100%;max-width:520px}
.card-brand{display:flex;align-items:center;gap:12px;margin-bottom:8px}
.card-brand img{height:38px;width:auto}
.card-brand .brand-title{font-weight:700}
.login-title{display:flex;align-items:center;gap:12px}
.login-title .icon-circle{width:48px;height:48px;border-radius:50%;background:linear-gradient(180deg, rgba(13,110,253,0.08), rgba(13,110,253,0.03));display:inline-flex;align-items:center;justify-content:center;font-size:1.25rem;color:var(--cg-primary)}
.admin-login-card h4{margin:0;font-weight:800}
.admin-login-card p.small-note{color:var(--cg-muted);margin:0 0 1rem}
.form-label.small{font-weight:700}
.form-control{height:52px;border-radius:12px;padding:.6rem 0.9rem}
.btn-sign{height:54px;border-radius:10px;font-weight:800}
.small-note-muted{color:var(--cg-muted)}
.back-link{color:var(--cg-muted);text-decoration:none}

@media (max-width:991.98px){.admin-panel{grid-template-columns:1fr;padding:1rem;gap:1rem}.admin-left{min-height:320px}.admin-login-card{padding:1.25rem}}

</style>

<main class="admin-login-wrap">
    <div class="container">
        <div class="admin-panel">
            <div class="admin-left">
                <div class="admin-left-inner">
                    <div class="brand-pill">
                        <img src="../assets/images/logo.webp" alt="Career Grow Infotech logo">
                        <span class="pill-text">Career Grow Infotech</span>
                    </div>

                    <div class="admin-eyebrow">ADMIN PORTAL</div>
                    <h1 class="admin-head">Manage Opportunities. <br>Build Better Careers.</h1>
                    <p class="admin-desc">Securely manage jobs, applications and candidate relationships from your administration portal.</p>

                    <div class="admin-features">
                        <div class="feature-row"><div class="feature-dot"></div><div class="feature-text">Secure access for administrators</div></div>
                        <div class="feature-row"><div class="feature-dot"></div><div class="feature-text">Manage jobs, candidates and applications</div></div>
                        <div class="feature-row"><div class="feature-dot"></div><div class="feature-text">Fast and reliable administration tools</div></div>
                    </div>
                </div>
            </div>

            <div class="admin-right">
                <div class="admin-login-card">
                    <div class="card-brand">
                        <img src="../assets/images/logo.webp" alt="Career Grow Infotech logo">
                        <div class="brand-title">Career Grow Infotech <div class="small text-muted">Admin Portal</div></div>
                    </div>

                    <div class="login-title mb-2">
                        <div class="icon-circle"><i class="bi bi-shield-lock-fill"></i></div>
                        <div>
                            <h4 class="mb-0">Admin Login</h4>
                            <div class="small-note-muted">Sign in to access the administration portal.</div>
                        </div>
                    </div>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo htmlspecialchars(implode(' ', $errors), ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php endif; ?>

                    <div class="mb-3 small text-muted"><i class="bi bi-shield-lock-fill me-1"></i> Secure Administrator Access</div>

                    <form method="post" novalidate class="fade-in">
                        <div class="mb-3">
                            <label for="email" class="form-label small">Email Address</label>
                            <input type="email" id="email" name="email" class="form-control" placeholder="admin@example.com" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label small">Password</label>
                            <div class="input-group">
                                <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
                                <button type="button" class="btn btn-outline-secondary show-pass" id="togglePass" aria-label="Show password"><i class="bi bi-eye"></i></button>
                            </div>
                            <div class="d-flex justify-content-end mt-2">
                                <a href="../forgot-password.php" class="small text-primary text-decoration-none fw-bold">Forgot Password?</a>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="../index.php" class="back-link">&larr; Back to Homepage</a>
                            <button type="submit" class="btn btn-primary">Sign In</button>
                        </div>
                    </form>
                    <div class="text-center text-muted mt-3 small"><i class="bi bi-lock-fill me-1"></i> Authorized administrators only</div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
document.getElementById('togglePass').addEventListener('click', function(){
    var p = document.getElementById('password');
    var icon = this.querySelector('i');
    if (p.type === 'password') { p.type = 'text'; icon.classList.remove('bi-eye'); icon.classList.add('bi-eye-slash'); }
    else { p.type = 'password'; icon.classList.remove('bi-eye-slash'); icon.classList.add('bi-eye'); }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
