<?php
declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/includes/db.php';

$pageTitle = 'Candidate Login - Career Grow Infotech';

// Redirect already authenticated candidate
if (!empty($_SESSION['user_id']) && !empty($_SESSION['user_role']) && $_SESSION['user_role'] === 'candidate') {
    // If a safe next target was provided, redirect there; otherwise go to dashboard.
    $nextParam = isset($_GET['next']) ? (string)$_GET['next'] : '';
    if ($nextParam !== '') {
        $decodedNext = rawurldecode($nextParam);
        $isLocal = (parse_url($decodedNext, PHP_URL_SCHEME) === null) && (parse_url($decodedNext, PHP_URL_HOST) === null) && (strpos($decodedNext, '//') !== 0) && str_starts_with($decodedNext, '/');
        if ($isLocal) {
            header('Location: ' . $decodedNext);
            exit;
        }
    }
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

                        // Prefer a validated 'next' return target if provided (prevents open redirects)
                        $nextParam = isset($_GET['next']) ? (string)$_GET['next'] : '';
                        $redirected = false;
                        if ($nextParam !== '') {
                            $decodedNext = rawurldecode($nextParam);
                            $isLocal = (parse_url($decodedNext, PHP_URL_SCHEME) === null) && (parse_url($decodedNext, PHP_URL_HOST) === null) && (strpos($decodedNext, '//') !== 0) && str_starts_with($decodedNext, '/');
                            if ($isLocal) {
                                header('Location: ' . $decodedNext);
                                $redirected = true;
                            }
                        }

                        if (!$redirected) {
                            // Fallback: support legacy job_id param to redirect directly to apply flow
                            $redirectJobId = isset($_GET['job_id']) ? (int)$_GET['job_id'] : 0;
                            if ($redirectJobId > 0) {
                                header('Location: candidate/apply.php?job_id=' . $redirectJobId);
                            } else {
                                header('Location: candidate/dashboard.php');
                            }
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

// Hide global public header / navbar / footer for this standalone auth page
$hidePublicLayout = true;
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<style>
/* Full viewport split layout */
:root { --login-left-width: 46%; }
html,body{height:100%;}
.auth-shell{min-height:100vh;display:flex;align-items:stretch;}
.auth-left{flex:0 0 var(--login-left-width);position:relative;background-size:cover;background-position:center;}
.auth-left::before{content:'';position:absolute;inset:0;background:linear-gradient(180deg, rgba(5,23,55,0.55), rgba(5,23,55,0.55));}
.auth-left-inner{position:relative;z-index:2;height:100%;display:flex;flex-direction:column;padding:48px 48px;box-sizing:border-box;color:#fff}
.logo-badge{display:inline-flex;align-items:center;gap:12px;padding:8px 14px;border-radius:999px;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.09);backdrop-filter: blur(6px);-webkit-backdrop-filter: blur(6px);box-shadow:0 6px 18px rgba(2,6,23,0.28);}
.logo-badge img{height:36px;width:auto;display:block}
.logo-pill-text{font-weight:600;font-size:1rem;color:#fff;line-height:1}
.eyebrow{font-size:12px;letter-spacing:0.18em;text-transform:uppercase;color:rgba(255,255,255,0.85);margin-bottom:1rem;font-weight:700}
.left-head{font-size:clamp(2rem,4.2vw,3rem);line-height:1.02;font-weight:800;margin:0 0 1rem}
.left-desc{max-width:36ch;color:rgba(255,255,255,0.9);margin-bottom:1.25rem}
.features{display:flex;flex-direction:column;gap:14px;margin-top:18px}
.feature-row{display:flex;gap:12px;align-items:center}
.feature-icon{width:48px;height:48px;border-radius:50%;background:rgba(255,255,255,0.1);display:inline-flex;align-items:center;justify-content:center;flex-shrink:0}
.feature-text h6{margin:0;font-size:1rem;font-weight:700}
.feature-text p{margin:0;color:rgba(255,255,255,0.9);font-size:0.95rem}
.left-bottom{margin-top:auto;padding-top:18px;border-top:1px solid rgba(255,255,255,0.06);font-size:1.05rem;color:rgba(255,255,255,0.9)}

.auth-right{flex:1;display:flex;align-items:center;justify-content:center;padding:48px;background:#f7f9fc}
.login-card{width:100%;max-width:480px;background:var(--cg-white);border:1px solid var(--cg-border);border-radius:18px;padding:34px;box-shadow:0 18px 40px rgba(15,23,42,0.06)}
.card-header{display:flex;flex-direction:column;align-items:start;gap:8px;margin-bottom:16px}
.card-logo-wrapper{display:inline-flex;align-items:center;justify-content:center;padding:6px 8px;border-radius:10px;background:rgba(11,31,51,0.02);border:1px solid rgba(15,23,42,0.03)}
.card-logo-wrapper img{height:40px;width:auto;display:block}
.welcome{font-size:1.6rem;font-weight:800;margin:6px 0}
.subtitle{color:var(--cg-muted);margin-bottom:6px}
.form-label{font-weight:700}
.form-control{height:56px;padding:0.9rem;border-radius:12px}
.form-group{margin-bottom:14px}
.forgot-row{display:flex;justify-content:flex-end;margin-top:-6px;margin-bottom:8px}
.forgot-row a{color:var(--cg-primary);text-decoration:none;font-weight:700}
.btn-login{height:56px;border-radius:12px;font-weight:800}
.divider{display:flex;align-items:center;gap:12px;margin:18px 0;color:var(--cg-muted)}
.divider::before,.divider::after{content:'';flex:1;height:1px;background:var(--cg-border)}
.register-row{text-align:center}
.back-home{display:inline-block;margin-top:12px;color:var(--cg-muted)}

@media (max-width: 992px){
  :root{--login-left-width:40%;}
}
@media (max-width: 768px){
  .auth-left{display:none}
  .auth-right{padding:28px}
  .auth-shell{min-height:100dvh}
}

/* Candidate login: a compact glass form over the career image. */
@media (min-width:769px){
  .auth-shell{position:relative;min-height:100dvh;display:block;isolation:isolate;background:#0b4279}
  .auth-left{position:absolute;top:0;right:0;bottom:-64px;left:0;display:block;min-height:100%;background-position:center;background-size:cover}
  .auth-left::before{background:linear-gradient(125deg,rgba(8,39,90,.93),rgba(13,101,153,.78) 55%,rgba(9,153,139,.68)),radial-gradient(circle at 85% 16%,rgba(130,89,239,.32),transparent 26%)}
  .auth-left::after{content:'';position:absolute;width:360px;height:360px;right:-130px;top:-140px;border:44px solid rgba(255,255,255,.13);border-radius:50%;box-shadow:0 0 0 48px rgba(255,255,255,.06)}
  .auth-left-inner{display:none}
  .auth-right{position:relative;z-index:1;box-sizing:border-box;min-height:100dvh;display:flex;align-items:center;justify-content:center;padding:28px;background:transparent}
  .login-card{width:min(100%,430px);max-width:430px;padding:30px 32px;border:1px solid rgba(212,244,255,.46);border-radius:22px;background:rgba(255,255,255,.9);box-shadow:0 24px 55px rgba(0,26,70,.3);backdrop-filter:blur(16px)}
  .card-header{align-items:center;gap:7px;margin-bottom:20px;text-align:center}.card-logo-wrapper{gap:9px;padding:5px 9px;border-radius:10px;background:rgba(17,99,173,.07);border-color:rgba(17,99,173,.08)}.card-logo-wrapper img{height:32px}.card-logo-wrapper span{color:#143b6b;font-size:.85rem;font-weight:800}
  .welcome{margin:5px 0 0;color:#113b70;font-size:1.62rem;letter-spacing:-.035em}.subtitle{margin:0;color:#60728b;font-size:.96rem}.form-label{color:#193b67;font-size:.83rem}.form-control{height:48px;padding:.7rem .85rem;border:1px solid #d7e4f3;border-radius:10px;background:#fbfdff}.form-control:focus{border-color:#30b99d;box-shadow:0 0 0 4px rgba(48,185,157,.13)}.form-group{margin-bottom:12px}.forgot-row{margin-top:-2px;margin-bottom:14px}.forgot-row a{color:#078e82;font-size:.9rem}.btn-login{height:48px;border:0;border-radius:10px;background:linear-gradient(135deg,#1b75d0,#14a99a);box-shadow:0 10px 20px rgba(13,119,169,.22)}.btn-login:hover{background:linear-gradient(135deg,#1463b5,#0c9183)}.divider{margin:15px 0;font-size:.9rem}.register-row{font-size:.92rem}.back-home{margin-top:9px;font-size:.9rem}
}

/* Compact dark-glass treatment over the image background. */
@media (min-width:769px){
  .login-card{position:relative;width:min(100%,380px);max-width:380px;padding:24px 26px;overflow:hidden;border:1px solid rgba(111,226,255,.72);border-radius:24px;background:linear-gradient(145deg,rgba(8,56,111,.66),rgba(8,87,121,.45));box-shadow:inset 0 1px 0 rgba(255,255,255,.22),0 0 0 1px rgba(20,216,218,.14),0 20px 46px rgba(0,20,63,.4);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px)}
  .login-card::before{content:'';position:absolute;inset:0;pointer-events:none;background:linear-gradient(115deg,rgba(255,255,255,.16),transparent 28%)}
  .login-card>*{position:relative;z-index:1}
  .card-header{gap:5px;margin-bottom:16px}.card-logo-wrapper{padding:4px 8px;background:rgba(255,255,255,.12);border-color:rgba(181,243,255,.32)}.card-logo-wrapper span{color:#d7fffa;font-size:.78rem}.card-logo-wrapper img{height:28px}
  .welcome{color:#fff;font-size:1.42rem;margin:3px 0 0}.subtitle{color:rgba(229,246,255,.8);font-size:.88rem}.form-label{color:#e9f8ff;font-size:.76rem}.form-control{height:44px;padding:.6rem .75rem;border-color:rgba(198,238,255,.4);border-radius:9px;background:rgba(255,255,255,.12);color:#fff}.form-control::placeholder{color:rgba(231,247,255,.66)}.form-control:focus{border-color:#72f0df;background:rgba(255,255,255,.17);box-shadow:0 0 0 3px rgba(78,231,216,.18);color:#fff}.form-group{margin-bottom:10px}.forgot-row{margin-top:0;margin-bottom:11px}.forgot-row a{color:#9bfff0;font-size:.82rem}.btn-login{height:44px;border-radius:9px;background:linear-gradient(135deg,#20cba6,#168dd8);box-shadow:0 9px 18px rgba(0,28,85,.32)}.divider{color:rgba(231,247,255,.7);margin:13px 0}.divider::before,.divider::after{background:rgba(211,243,255,.24)}.register-row{color:#eefaff;font-size:.84rem}.register-row a[style]{color:#9bfff0!important}.back-home{margin-top:8px;color:rgba(231,247,255,.8);font-size:.82rem}
}
</style>

<main class="auth-shell">
    <section class="auth-left" style="background-image:url('assets/images/buildcareer1.jpg')">
        <div class="auth-left-inner">
            <div>
                <a href="index.php" aria-label="Career Grow Infotech home" class="logo-badge"><img src="assets/images/logo.webp" alt="Career Grow Infotech logo"><span class="logo-pill-text">Career Grow Infotech</span></a>
            </div>
            <div style="margin-top:18px">
                <div class="eyebrow">YOUR CAREER PARTNER</div>
                <h1 class="left-head">Find the Right<br>Opportunity.<br>Build Your Career.</h1>
                <p class="left-desc">Explore meaningful job opportunities and take the next step with a team dedicated to growing careers in the right direction.</p>

                <div class="features">
                    <div class="feature-row">
                        <div class="feature-icon"><i class="bi bi-search" style="font-size:1.25rem;color:#fff"></i></div>
                        <div class="feature-text"><h6>Explore Jobs</h6><p>Find your perfect role</p></div>
                    </div>

                    <div class="feature-row">
                        <div class="feature-icon"><i class="bi bi-people" style="font-size:1.25rem;color:#fff"></i></div>
                        <div class="feature-text"><h6>Connect with Companies</h6><p>Work with top employers</p></div>
                    </div>

                    <div class="feature-row">
                        <div class="feature-icon"><i class="bi bi-arrow-up-right" style="font-size:1.25rem;color:#fff"></i></div>
                        <div class="feature-text"><h6>Grow Your Career</h6><p>Turn your aspirations into success</p></div>
                    </div>
                </div>
            </div>

            <div class="left-bottom">A better career<br>starts here...</div>
        </div>
    </section>

    <section class="auth-right">
        <div class="login-card" role="region" aria-label="Candidate login form">
            <div class="card-header">
                <div class="card-logo-wrapper"><img src="assets/images/logo.webp" alt="Career Grow Infotech logo"><span>Career Grow Infotech</span></div>
                <div class="welcome">Welcome Back</div>
                <div class="subtitle">Login to continue to your account.</div>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $e): ?>
                        <div><?php echo htmlspecialchars($e, ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="post" novalidate>
                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="you@example.com" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
                </div>

                <div class="forgot-row">
                    <a href="forgot-password.php">Forgot Password?</a>
                </div>

                <div>
                    <button type="submit" class="btn btn-primary w-100 btn-login">Login</button>
                </div>
            </form>

            <div class="divider">or</div>

            <div class="register-row">
                <p class="mb-0">Don't have an account? <a href="register.php" style="color:var(--cg-primary);font-weight:700">Register now!</a></p>
                <a href="index.php" class="back-home">← Back to Home</a>
            </div>
        </div>
    </section>
</main>

<?php // Do not include includes/footer.php because $hidePublicLayout prevents its rendering
?>
