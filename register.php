<?php
declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/includes/db.php';

$pageTitle = 'Candidate Registration - Career Grow Infotech';

// Redirect already authenticated candidate
if (!empty($_SESSION['user_id']) && !empty($_SESSION['user_role']) && $_SESSION['user_role'] === 'candidate') {
    header('Location: candidate/dashboard.php');
    exit;
}

$errors = [];
$success = false;
$fields = [
    'name' => '',
    'email' => '',
    'phone' => '',
    'password' => '',
    'password_confirm' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($fields as $k => $v) {
        $fields[$k] = trim((string)($_POST[$k] ?? ''));
    }

    // Validation
    if ($fields['name'] === '') { $errors[] = 'Full name is required.'; }
    if ($fields['email'] === '') { $errors[] = 'Email address is required.'; }
    elseif (!filter_var($fields['email'], FILTER_VALIDATE_EMAIL)) { $errors[] = 'Please enter a valid email address.'; }
    if ($fields['phone'] === '') { $errors[] = 'Phone number is required.'; }
    if ($fields['password'] === '') { $errors[] = 'Password is required.'; }
    elseif (strlen($fields['password']) < 8) { $errors[] = 'Password must be at least 8 characters.'; }
    if ($fields['password_confirm'] === '') { $errors[] = 'Please confirm your password.'; }
    elseif ($fields['password'] !== $fields['password_confirm']) { $errors[] = 'Passwords do not match.'; }

    if (empty($errors)) {
        $conn = getDbConnection();

        // Check if email already exists
        $checkQuery = 'SELECT id FROM users WHERE email = ? LIMIT 1';
        $checkStmt = $conn->prepare($checkQuery);
        if ($checkStmt) {
            $checkStmt->bind_param('s', $fields['email']);
            $checkStmt->execute();
            if ($checkStmt->get_result()->fetch_assoc()) {
                $errors[] = 'This email address is already registered.';
            }
            $checkStmt->close();
        }

        if (empty($errors)) {
            // Use prepared statements for secure user insertion
            $hashedPassword = password_hash($fields['password'], PASSWORD_DEFAULT);
            $candidateRole = 'candidate';
            $status = 1;
            
            $insertStmt = $conn->prepare('INSERT INTO users (name, email, phone, password, role, status) VALUES (?, ?, ?, ?, ?, ?)');
            if ($insertStmt) {
                $insertStmt->bind_param('sssssi', $fields['name'], $fields['email'], $fields['phone'], $hashedPassword, $candidateRole, $status);
                if ($insertStmt->execute()) {
                    $userId = $conn->insert_id;
                    $insertStmt->close();

                    // Create candidate profile using prepared statement
                    $profileStmt = $conn->prepare('INSERT INTO candidate_profiles (user_id) VALUES (?)');
                    if ($profileStmt) {
                        $profileStmt->bind_param('i', $userId);
                        $profileStmt->execute();
                        $profileStmt->close();
                    }

                    $success = true;
                    // Clear fields
                    foreach ($fields as $k => &$v) { $v = ''; }
                } else {
                    $errors[] = 'Failed to create account. Please try again.';
                }
                if (!$success) {
                    $insertStmt->close();
                }
            } else {
                $errors[] = 'Failed to prepare database statement. Please try again.';
            }
        }
        $conn->close();
    }
}

// Hide public header/footer for the standalone register page
$hidePublicLayout = true;
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<style>
/* Premium register page styles — refined spacing, typography, and controls */
.root { --login-left-width: 45%; --card-radius: 20px; --input-height: 52px; }
:root { --login-left-width: 45%; --card-radius: 20px; --input-height: 52px; }
* , *::before, *::after { box-sizing: border-box; }
html,body{height:100dvh;margin:0;padding:0}
.auth-shell{min-height:100dvh;display:flex;align-items:stretch;height:100%;}
.auth-left{flex:0 0 var(--login-left-width);position:relative;background-size:cover;background-position:center;border-right:1px solid rgba(15,23,42,0.04);}
.auth-left::before{content:'';position:absolute;inset:0;background:linear-gradient(180deg, rgba(6,18,42,0.6), rgba(6,18,42,0.55));}
.auth-left-inner{position:relative;z-index:2;height:100%;display:flex;flex-direction:column;padding:clamp(12px,3vh,36px) clamp(14px,4vw,56px);box-sizing:border-box;color:#fff}
.logo-badge{display:inline-flex;align-items:center;gap:12px;padding:8px 14px;border-radius:999px;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.09);backdrop-filter: blur(6px);-webkit-backdrop-filter: blur(6px);box-shadow:0 6px 18px rgba(2,6,23,0.28)}
.logo-badge img{height:36px;width:auto;display:block}
.logo-pill-text{font-weight:600;font-size:1rem;color:#fff;line-height:1}
.brand-block{margin-top:18px}
.brand-title{font-weight:700;font-size:1rem;color:rgba(255,255,255,0.95)}
.brand-sub{font-size:0.85rem;color:rgba(255,255,255,0.85)}
.eyebrow{font-size:12px;letter-spacing:0.18em;text-transform:uppercase;color:rgba(255,255,255,0.86);margin:24px 0 8px;font-weight:700}
.left-head{font-size:clamp(2.2rem,4.4vw,3.2rem);line-height:1.02;font-weight:800;margin:0 0 1rem}
.left-desc{max-width:44ch;color:rgba(255,255,255,0.92);margin-bottom:1.5rem;font-size:1rem}
.features{display:flex;flex-direction:column;gap:14px;margin-top:6px}
.feature-row{display:flex;gap:14px;align-items:center}
.feature-icon{width:46px;height:46px;border-radius:50%;background:rgba(255,255,255,0.08);display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow: inset 0 -2px 6px rgba(255,255,255,0.02)}
.feature-text h6{margin:0;font-size:1rem;font-weight:700}
.feature-text p{margin:0;color:rgba(255,255,255,0.9);font-size:0.95rem}
.left-bottom{margin-top:auto;padding-top:18px;border-top:1px solid rgba(255,255,255,0.04);font-size:1.05rem;color:rgba(255,255,255,0.9)}

.auth-right{flex:1;display:flex;align-items:center;justify-content:center;padding:clamp(12px,3vh,32px);background:linear-gradient(180deg, rgba(247,249,252,1), rgba(245,247,250,1));}
.register-card{width:100%;max-width:600px;background:var(--cg-white);border:1px solid rgba(15,23,42,0.04);border-radius:var(--card-radius);padding:clamp(12px,2.5vh,24px);box-shadow:0 22px 44px rgba(15,23,42,0.06);box-sizing:border-box;max-height:calc(100dvh - 32px)}
.card-header{display:flex;flex-direction:column;align-items:flex-start;gap:6px;margin-bottom:6px}
.card-logo-wrapper{display:inline-flex;align-items:center;justify-content:center;padding:6px 8px;border-radius:10px;background:rgba(11,31,51,0.02);border:1px solid rgba(15,23,42,0.03)}
.card-logo-wrapper img{height:40px;width:auto;display:block}
.portal-name{font-weight:700;font-size:0.95rem}
.portal-sub{font-size:0.85rem;color:var(--cg-muted)}
.register-title{font-size:1.6rem;font-weight:800;margin-top:6px}
.register-subtitle{color:var(--cg-muted);margin-bottom:10px}

.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:clamp(6px,1vw,10px);margin-bottom:4px}
.form-group{margin-bottom:8px}
.form-label{font-weight:700;font-size:0.95rem;color:var(--cg-accent);margin-bottom:6px}
.form-control{height:var(--input-height);padding:0.65rem 0.9rem;border-radius:12px;border:1px solid var(--cg-border);box-shadow:none;font-size:1rem}
.form-control::placeholder{color:rgba(31,41,55,0.45)}
.form-control:focus{outline:none;border-color:rgba(13,110,253,0.22);box-shadow:0 6px 18px rgba(13,110,253,0.06)}
.form-text{color:var(--cg-muted);font-size:0.9rem;margin-top:4px}
.btn-create{height:54px;border-radius:10px;font-weight:800;box-shadow:0 10px 20px rgba(13,110,253,0.08)}
.btn-create:hover{box-shadow:0 14px 28px rgba(13,110,253,0.12)}
.divider{display:flex;align-items:center;gap:12px;margin:12px 0;color:var(--cg-muted)}
.divider::before,.divider::after{content:'';flex:1;height:1px;background:var(--cg-border)}
.login-row{text-align:center;margin-top:4px}
.back-home{display:inline-block;margin-top:8px;color:var(--cg-muted)}

/* Decorative subtle shapes on left */
.auth-left::after{content:'';position:absolute;right:-8%;bottom:-2%;width:280px;height:280px;background:radial-gradient(circle at 30% 30%, rgba(255,255,255,0.02), transparent 40%);border-radius:50%;pointer-events:none}

@media (max-width: 1100px){
    :root{--login-left-width:42%;}
    .auth-left-inner{padding:clamp(16px,3.5vw,48px)}
}
@media (max-height: 880px) {
    /* Reduce vertical spacing for shorter viewports (e.g. 768px tall) */
    .auth-left-inner{padding-top:14px;padding-bottom:14px}
    .auth-right{padding:12px}
    .register-card{padding:12px;max-height:calc(100dvh - 24px)}
    .left-head{font-size:clamp(1.6rem,3.2vw,2.4rem)}
    .left-desc{font-size:0.95rem}
    .form-control{height:48px}
    .form-grid{gap:8px}
    .btn-create{height:50px}
    .card-header{gap:4px}
    .register-title{font-size:1.45rem;margin-top:4px}
}
@media (max-height: 800px) {
    /* Aggressive compaction for short laptop viewports */
    .auth-right{align-items:flex-start;padding-top:14px;padding-bottom:14px}
    .register-card{padding:10px;max-height:calc(100dvh - 20px)}
    .card-header{gap:4px}
    .portal-name{font-size:0.9rem}
    .portal-sub{font-size:0.82rem}
    .register-title{font-size:1.36rem}
    .register-subtitle{margin-bottom:10px}
    .form-grid{gap:8px}
    .form-control{height:46px}
    .form-label{margin-bottom:4px}
    .left-bottom{padding-top:10px}
    .logo-badge img{height:32px}
}
@media (max-width: 768px){
    .auth-left{display:none}
    .auth-right{padding:28px}
    .auth-shell{min-height:100dvh}
    .form-grid{grid-template-columns:1fr}
}
</style>

<main class="auth-shell">
    <section class="auth-left" style="background-image:url('assets/images/buildcareer1.jpg')">
        <div class="auth-left-inner">
            <div class="brand-line">
                <div class="logo-badge"><a href="index.php" aria-label="Career Grow Infotech home"><img src="assets/images/logo.webp" alt="Career Grow Infotech logo"><span class="logo-pill-text">Career Grow Infotech</span></a></div>
                <div>
                    <div class="brand-title">Career Grow Infotech</div>
                    <div class="brand-sub">Candidate Portal</div>
                </div>
            </div>

            <div class="eyebrow">START YOUR JOURNEY</div>
            <h1 class="left-head">Create Your Account.<br>Start Growing Your Career.</h1>
            <p class="left-desc">Create your Career Grow Infotech account and explore opportunities that match your career goals.</p>

            <div class="features">
                <div class="feature-row">
                    <div class="feature-icon"><i class="bi bi-check-lg" style="font-size:1.1rem;color:#fff"></i></div>
                    <div class="feature-text"><h6>Discover relevant opportunities</h6></div>
                </div>

                <div class="feature-row">
                    <div class="feature-icon"><i class="bi bi-check-lg" style="font-size:1.1rem;color:#fff"></i></div>
                    <div class="feature-text"><h6>Manage your applications</h6></div>
                </div>

                <div class="feature-row">
                    <div class="feature-icon"><i class="bi bi-check-lg" style="font-size:1.1rem;color:#fff"></i></div>
                    <div class="feature-text"><h6>Build your professional profile</h6></div>
                </div>
            </div>

            <div class="left-bottom">A better career<br>starts here...</div>
        </div>
    </section>

    <section class="auth-right">
        <div class="register-card" role="region" aria-label="Candidate registration form">
            <div class="card-header">
                <div class="card-logo-wrapper"><img src="assets/images/logo.webp" alt="Career Grow Infotech logo"></div>
                <div class="portal-name">Career Grow Infotech</div>
                <div class="portal-sub">Candidate Portal</div>
            </div>

            <div class="register-title">Create Your Account</div>
            <div class="register-subtitle">Join Career Grow Infotech and take the next step in your career.</div>

            <?php if ($success): ?>
                <div class="alert alert-success">Registration successful! <a href="login.php" class="alert-link">Go to login</a></div>
            <?php elseif (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $e): ?>
                        <div><?php echo htmlspecialchars($e, ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="post" novalidate>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="name" class="form-label">Full Name <span class="required">*</span></label>
                        <input type="text" id="name" name="name" class="form-control" placeholder="John Doe" value="<?php echo htmlspecialchars($fields['name'], ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Email Address <span class="required">*</span></label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="you@example.com" value="<?php echo htmlspecialchars($fields['email'], ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="phone" class="form-label">Phone Number <span class="required">*</span></label>
                        <input type="tel" id="phone" name="phone" class="form-control" placeholder="+91 9876543210" value="<?php echo htmlspecialchars($fields['phone'], ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password <span class="required">*</span></label>
                        <input type="password" id="password" name="password" class="form-control" placeholder="At least 8 characters" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password_confirm" class="form-label">Confirm Password <span class="required">*</span></label>
                    <input type="password" id="password_confirm" name="password_confirm" class="form-control" placeholder="Confirm your password" required>
                </div>

                <button type="submit" class="btn btn-primary w-100 btn-create">Create Account</button>
            </form>

            <div class="divider">or</div>

            <div class="login-row">
                <p class="mb-0">Already have an account? <a href="login.php" style="color:var(--cg-primary);font-weight:700">Login</a></p>
                <a href="index.php" class="back-home">← Back to Home</a>
            </div>
        </div>
    </section>
</main>

<?php // Do not include includes/footer.php because $hidePublicLayout prevents its rendering
?>
