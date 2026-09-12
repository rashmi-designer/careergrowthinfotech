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

/* Modern visual refresh: the recruitment image becomes the right-side story panel. */
.admin-login-wrap{min-height:100dvh;align-items:center;padding:28px 16px;background:radial-gradient(circle at 5% 5%,#d9f7f0 0,transparent 30%),radial-gradient(circle at 96% 92%,#dceaff 0,transparent 32%),#f6f8fc}
.admin-panel{max-width:1280px;min-height:620px;grid-template-columns:minmax(0,1fr) minmax(420px,.95fr);gap:clamp(20px,4vw,60px);padding:clamp(8px,2vw,28px)}
.admin-right{order:1}.admin-left{order:2;min-height:100%;border-radius:28px;justify-content:flex-end;isolation:isolate;box-shadow:0 24px 60px rgba(15,49,91,.2)}
.admin-left::before{z-index:-1;background:linear-gradient(145deg,rgba(14,41,83,.9) 0%,rgba(20,91,130,.6) 48%,rgba(7,130,119,.72) 100%)}
.admin-left::after{content:'';position:absolute;width:300px;height:300px;border:1px solid rgba(255,255,255,.24);border-radius:50%;right:-100px;top:-90px;box-shadow:0 0 0 55px rgba(255,255,255,.05),0 0 0 110px rgba(255,255,255,.04)}
.admin-left-inner{padding:clamp(28px,4vw,52px)}.brand-pill{padding:7px 12px;background:rgba(255,255,255,.14);backdrop-filter:blur(12px);border-color:rgba(255,255,255,.24);box-shadow:none}.brand-pill img{height:34px;width:34px;object-fit:contain;padding:3px;border-radius:10px;background:#fff}.pill-text{font-weight:700;font-size:.95rem}
.admin-eyebrow{margin-top:34px;font-weight:700;color:#9ff2dc}.admin-head{max-width:10ch;font-size:clamp(2.25rem,4vw,4rem);line-height:.98;letter-spacing:-.055em;margin:14px 0 16px}.admin-desc{max-width:39ch;font-size:1.02rem;line-height:1.65;margin:0 0 26px}.admin-features{display:grid;gap:12px;margin:0}.feature-row{align-items:center}.feature-dot{width:22px;height:22px;flex:0 0 22px;display:grid;place-items:center;margin:0;background:rgba(95,237,193,.2);color:#9ff2dc}.feature-dot::after{content:'✓';font-size:.78rem;font-weight:800}.feature-text{font-size:.92rem}
.admin-login-card{max-width:500px;padding:clamp(26px,3vw,38px);border:1px solid rgba(181,203,236,.62);border-radius:28px;background:rgba(255,255,255,.86);box-shadow:0 26px 70px rgba(30,65,112,.12);backdrop-filter:blur(14px)}.card-brand{gap:11px;margin-bottom:26px}.card-brand img{height:42px;width:42px;object-fit:contain}.card-brand .brand-title{color:#102a50;font-weight:800;line-height:1.25}.login-title{gap:14px}.login-title .icon-circle{width:52px;height:52px;flex:0 0 52px;border-radius:16px;background:linear-gradient(135deg,#e9f0ff,#e0fbf3);font-size:1.2rem;color:#1467e8}.admin-login-card h4{color:#102a50;font-size:1.65rem;letter-spacing:-.035em}.form-label.small{color:#18365e;font-size:.82rem;font-weight:800;letter-spacing:.01em}.form-control{height:54px;border:1px solid #dbe4f1;padding:.6rem .95rem;background:#fbfdff;box-shadow:none}.form-control:focus{border-color:#3d81f5;box-shadow:0 0 0 4px rgba(61,129,245,.12);background:#fff}.input-group .form-control{border-radius:12px 0 0 12px}.show-pass{min-width:54px;border-color:#dbe4f1;border-radius:0 12px 12px 0;color:#52709a}.btn-sign{height:52px;padding:0 25px;border:0;border-radius:12px;background:linear-gradient(135deg,#1769e8,#148cbd);box-shadow:0 10px 22px rgba(23,105,232,.22)}.btn-sign:hover{background:linear-gradient(135deg,#125bc9,#107aa5);transform:translateY(-1px)}.back-link{color:#55708f;font-weight:600}.back-link:hover{color:#1769e8}

@media (max-width:991.98px){.admin-login-wrap{padding:18px 12px}.admin-panel{min-height:0;grid-template-columns:1fr;gap:20px;padding:0;max-width:600px}.admin-right{order:1}.admin-left{order:2;min-height:390px}.admin-login-card{max-width:none;padding:clamp(28px,4vw,48px)}.admin-eyebrow{margin-top:22px}}
@media (max-width:575.98px){.admin-login-wrap{align-items:flex-start}.admin-login-card{padding:28px 22px;border-radius:22px}.admin-left{min-height:360px;border-radius:22px}.admin-left-inner{padding:28px}.admin-head{font-size:2.45rem}.card-brand{margin-bottom:26px}.login-title{align-items:flex-start}.login-title .icon-circle{width:46px;height:46px;flex-basis:46px}}

/* Compact desktop height: both panels share this fixed grid height. */
@media (min-width:992px){
    .admin-panel{height:500px;min-height:0}
    .admin-login-card{height:100%;padding:26px 34px}
    .admin-left-inner{padding:30px 38px}
    .admin-eyebrow{margin-top:22px}
    .admin-head{font-size:clamp(2.25rem,4vw,3.35rem);margin:10px 0 12px}
    .admin-desc{font-size:.94rem;line-height:1.5;margin-bottom:18px}
    .admin-features{gap:8px}.feature-text{font-size:.86rem}
    .card-brand{margin-bottom:16px}.card-brand img{height:38px;width:38px}
    .login-title{gap:12px}.login-title .icon-circle{width:46px;height:46px;flex-basis:46px;border-radius:14px}
    .admin-login-card h4{font-size:1.48rem}.form-control{height:48px}.show-pass{min-width:50px}.btn-sign{height:48px}
}

/* Focused blue login canvas, inspired by contemporary glass-panel sign-in screens. */
.login-brand{display:none}
@media (min-width:992px){
    .admin-login-wrap{padding:36px 16px;background:#eef5ff}
    .admin-panel{position:relative;display:block;width:min(100%,1000px);height:590px;padding:0;overflow:hidden;border-radius:30px;background:linear-gradient(135deg,#092f67 0%,#0759aa 52%,#159dcd 100%);box-shadow:0 28px 65px rgba(18,67,133,.24)}
    .admin-panel::before,.admin-panel::after{content:'';position:absolute;border-radius:50%;pointer-events:none}
    .admin-panel::before{width:440px;height:440px;left:-210px;bottom:-260px;border:56px solid rgba(115,202,255,.18)}
    .admin-panel::after{width:340px;height:340px;right:-135px;top:-170px;border:45px solid rgba(151,224,255,.15)}
    .admin-left{position:absolute;inset:0;display:block;min-height:0;border-radius:0;background:transparent;box-shadow:none}
    .admin-left::before{inset:0;background:radial-gradient(circle at 18% 22%,rgba(103,202,255,.34),transparent 19%),radial-gradient(circle at 84% 78%,rgba(31,222,201,.26),transparent 23%)}
    .admin-left::after{width:210px;height:210px;right:8%;bottom:-110px;border:28px solid rgba(255,255,255,.08);box-shadow:none}
    .admin-left-inner{display:none}
    .admin-right{position:relative;z-index:2;display:flex;width:100%;height:100%;align-items:center;justify-content:center}
    .admin-login-card{width:420px;height:auto;max-width:calc(100% - 36px);padding:32px 34px;border:1px solid rgba(198,231,255,.3);border-radius:20px;background:rgba(4,39,91,.56);box-shadow:0 18px 40px rgba(0,20,65,.3);backdrop-filter:blur(18px)}
    .login-brand{display:flex;align-items:center;justify-content:center;gap:9px;margin-bottom:23px;color:#fff;font-size:.94rem;font-weight:750}.login-brand img{width:34px;height:34px;padding:3px;border-radius:9px;background:#fff;object-fit:contain}
    .login-title{justify-content:center;margin-bottom:24px!important;text-align:center}.login-title .icon-circle{display:none}.admin-login-card h4{color:#fff;font-size:1.7rem}.small-note-muted{color:rgba(232,244,255,.76)}
    .admin-login-card .form-label.small{color:#eaf5ff;font-size:.78rem}.admin-login-card .form-control{height:46px;border-color:rgba(199,229,255,.28);border-radius:9px;background:rgba(255,255,255,.96);color:#133765}.admin-login-card .form-control:focus{border-color:#83d9ff;box-shadow:0 0 0 4px rgba(109,209,255,.2)}
    .admin-login-card .input-group .form-control{border-radius:9px 0 0 9px}.admin-login-card .show-pass{min-width:48px;border-color:rgba(199,229,255,.28);border-radius:0 9px 9px 0;background:rgba(255,255,255,.96);color:#1768a8}.admin-login-card .text-primary{color:#a8eaff!important}
    .admin-login-card .btn-sign{height:46px;border-radius:9px;background:linear-gradient(135deg,#58c6f6,#20a8df);box-shadow:0 8px 18px rgba(7,15,69,.28)}.admin-login-card .back-link{color:#d6ecff;font-size:.9rem}.admin-login-card .text-muted{color:rgba(232,244,255,.7)!important}
}

@media (min-width:992px){
    .admin-login-wrap{padding:0;background:linear-gradient(135deg,#092f67 0%,#0759aa 52%,#159dcd 100%)}
    .admin-panel{width:100%;max-width:none;height:100dvh;min-height:590px;border-radius:0;box-shadow:none}
}

@media (min-width:992px){
    .admin-login-wrap{background:radial-gradient(circle at 12% 84%,rgba(35,207,166,.24),transparent 24%),radial-gradient(circle at 88% 18%,rgba(136,92,246,.18),transparent 22%),linear-gradient(135deg,#0b3371 0%,#1266aa 48%,#0b9e90 100%)}
    .admin-panel{background:radial-gradient(circle at 12% 84%,rgba(35,207,166,.24),transparent 24%),radial-gradient(circle at 88% 18%,rgba(136,92,246,.18),transparent 22%),linear-gradient(135deg,#0b3371 0%,#1266aa 48%,#0b9e90 100%)}
    .admin-login-card{border-color:rgba(112,239,207,.34);box-shadow:0 18px 40px rgba(0,24,72,.34),0 0 0 1px rgba(139,102,245,.12)}
    .login-brand span{color:#a9ffe5}.admin-login-card .btn-sign{background:linear-gradient(135deg,#1fcfa3,#1997dc)}
    .admin-login-card .btn-sign:hover{background:linear-gradient(135deg,#18b78e,#147fc2)}
    .admin-login-card .show-pass{flex:0 0 48px;width:48px;height:46px;min-width:48px;padding:0;display:inline-flex;align-items:center;justify-content:center}
}

@media (min-width:992px){
    .admin-login-card{border-color:rgba(111,226,255,.72);background:linear-gradient(145deg,rgba(8,56,111,.68),rgba(8,87,121,.48));box-shadow:inset 0 1px 0 rgba(255,255,255,.22),0 0 0 1px rgba(20,216,218,.14),0 0 28px rgba(41,219,228,.2),0 20px 46px rgba(0,20,63,.4)}
}

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

                    <h1 class="admin-head">Manage Opportunities. <br>Build Better Careers.</h1>
                    <p class="admin-desc">Manage jobs, candidates and applications securely from one place.</p>
                </div>
            </div>

            <div class="admin-right">
                <div class="admin-login-card">
                    <div class="login-brand">
                        <img src="../assets/images/logo.webp" alt="Career Grow Infotech logo">
                        <span>Career Grow Infotech</span>
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
                            <button type="submit" class="btn btn-primary btn-sign">Sign In</button>
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
