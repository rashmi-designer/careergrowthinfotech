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
require_once __DIR__ . '/../includes/header.php';
?>

<style>
/* Admin login page local styles — full-screen split layout */
html,body { height:100%; }
.admin-login-wrap { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 0; }
.admin-panel { width: 100%; max-width: 1200px; display: grid; grid-template-columns: 52% 48%; gap: 2.5rem; padding: 3.5rem; align-items: center; }
.admin-left { background: linear-gradient(180deg, rgba(13,110,253,0.04), rgba(13,110,253,0.01)); border-radius: 1rem; padding: 2.5rem; display:flex; flex-direction:column; gap:1.5rem; justify-content:center; }
.admin-left .brand { display:flex; gap:1rem; align-items:center; }
.admin-left h1 { font-size: clamp(1.6rem, 2.8vw, 2.4rem); margin:0; font-weight:800; }
.admin-left p.lead { color:var(--cg-muted); max-width:56ch; line-height:1.6; }
.visual-box { margin-top:1rem; display:flex; gap:1rem; align-items:flex-start; }
.visual-card { background:var(--cg-white); border:1px solid var(--cg-border); border-radius:.75rem; padding:1rem; box-shadow: 0 14px 36px rgba(15,23,42,0.06); width:100%; }
.visual-row { display:flex; gap:.75rem; align-items:center; }
.visual-avatar { width:44px; height:44px; border-radius:8px; background: linear-gradient(135deg,var(--cg-primary),var(--cg-primary-dark)); color:#fff; display:inline-flex; align-items:center; justify-content:center; font-weight:700; }

/* stat tiles inside visual */
.stat-tiles { display:flex; gap: .75rem; margin-bottom: .8rem; }
.stat-tile { flex:1 1 0; background: linear-gradient(180deg, rgba(13,110,253,0.03), rgba(13,110,253,0.01)); border:1px solid rgba(13,110,253,0.06); border-radius:.6rem; padding:.65rem; text-align:left; }
.stat-tile .num { font-weight:800; color:var(--cg-accent); font-size:1.25rem; }
.stat-tile .label { color:var(--cg-muted); font-size:.85rem; }

.match-row { margin-top:.6rem; border-top:1px dashed rgba(15,23,42,0.04); padding-top:.6rem; }
.match-row .match-item { display:flex; gap:.6rem; align-items:center; }
.match-row .match-item .role { font-weight:700; }
.match-row .badge-suit { background: rgba(13,110,253,0.08); color:var(--cg-primary); border-radius:.5rem; padding:.18rem .5rem; font-size:.8rem; }

/* subtle entrance animations */
.fade-in { animation: fadeInUp .6s ease both; }
@keyframes fadeInUp { from { opacity:0; transform: translateY(8px);} to { opacity:1; transform:none; } }
@media (prefers-reduced-motion: reduce) { .fade-in { animation: none; } }

.admin-right { display:flex; align-items:center; justify-content:center; }
.admin-login-card { background: var(--cg-white); border:1px solid var(--cg-border); border-radius: 1rem; padding: 2.25rem; box-shadow: 0 22px 48px rgba(15,23,42,0.08); width:100%; }
.admin-login-card h4 { font-weight:700; }
.admin-login-card p.small-note { color:var(--cg-muted); margin-bottom:1rem; }
.form-label.small { font-weight:700; }
.input-group .form-control { min-height:48px; }
.show-pass { cursor:pointer; border-left:0; }
.back-link { color:var(--cg-muted); }

@media (max-width: 991.98px) {
    .admin-panel { grid-template-columns: 1fr; padding: 1.5rem; gap: 1.25rem; }
    .admin-left { padding: 1.5rem; }
    .admin-login-card { padding: 1.25rem; }
}
</style>

<main class="admin-login-wrap">
    <div class="container">
        <div class="admin-panel">
            <div class="admin-left">
                <div class="brand">
                    <span class="brand-mark-sm"><img src="../assets/images/logo.webp" alt="Career Grow Infotech logo" width="48" height="48"></span>
                    <div>
                        <div class="brand-title">Career Grow Infotech</div>
                        <div class="brand-subtitle small-note">Administrator Workspace</div>
                    </div>
                </div>

                <h1>Welcome to Admin Portal</h1>
                <p class="lead">Manage jobs, candidates and recruitment activities from one secure workspace.</p>

                <div class="visual-box">
                    <div class="visual-card">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <div class="fw-semibold">Talent Overview</div>
                                <div class="text-soft small">UI illustration — not real data</div>
                            </div>
                            <i class="bi bi-graph-up-arrow text-primary fs-4"></i>
                        </div>

                        <div class="stat-tiles">
                            <div class="stat-tile">
                                <div class="num">24</div>
                                <div class="label">Candidates</div>
                            </div>
                            <div class="stat-tile">
                                <div class="num">12</div>
                                <div class="label">Open Jobs</div>
                            </div>
                            <div class="stat-tile">
                                <div class="num">48</div>
                                <div class="label">Applications</div>
                            </div>
                        </div>

                        <div class="match-row">
                            <div class="match-item">
                                <div class="visual-avatar">AD</div>
                                <div>
                                    <div class="role">Candidate Profile — PHP Developer</div>
                                    <div class="text-soft small">Skills: PHP · MySQL · JavaScript</div>
                                </div>
                                <div class="ms-auto"><span class="badge-suit">Suitable Match</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="admin-right">
                <div class="admin-login-card">
                    <h4 class="mb-1">ADMIN PORTAL</h4>
                    <p class="small-note mb-3">Administrator Sign In</p>

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
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="../index.php" class="back-link">&larr; Back to Website</a>
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
