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

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<style>
.register-container { max-width: 600px; margin: 2rem auto; }
.register-card { background: var(--cg-white); border: 1px solid var(--cg-border); border-radius: 1rem; padding: 2rem; box-shadow: 0 12px 30px rgba(15,23,42,0.04); }
.register-card h2 { font-weight: 700; margin-bottom: 0.5rem; }
.register-subtitle { color: var(--cg-muted); margin-bottom: 1.5rem; }
.required { color: #d63384; }
input.form-control { min-height: 44px; }
.back-link { color: var(--cg-muted); text-decoration: none; }
.back-link:hover { color: var(--cg-primary); }
</style>

<main class="register-container py-5">
    <div class="register-card">
        <h2>Create Your Account</h2>
        <p class="register-subtitle">Register to explore career opportunities</p>

        <?php if ($success): ?>
            <div class="alert alert-success">
                Registration successful! <a href="login.php" class="alert-link">Go to login</a>
            </div>
        <?php elseif (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $e): ?>
                    <div><?php echo htmlspecialchars($e, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" novalidate>
            <div class="mb-3">
                <label for="name" class="form-label">Full Name <span class="required">*</span></label>
                <input type="text" id="name" name="name" class="form-control" placeholder="John Doe" value="<?php echo htmlspecialchars($fields['name'], ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email Address <span class="required">*</span></label>
                <input type="email" id="email" name="email" class="form-control" placeholder="you@example.com" value="<?php echo htmlspecialchars($fields['email'], ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>

            <div class="mb-3">
                <label for="phone" class="form-label">Phone Number <span class="required">*</span></label>
                <input type="tel" id="phone" name="phone" class="form-control" placeholder="+91 9876543210" value="<?php echo htmlspecialchars($fields['phone'], ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password <span class="required">*</span></label>
                <input type="password" id="password" name="password" class="form-control" placeholder="At least 8 characters" required>
                <small class="form-text text-muted">Password must be at least 8 characters long.</small>
            </div>

            <div class="mb-3">
                <label for="password_confirm" class="form-label">Confirm Password <span class="required">*</span></label>
                <input type="password" id="password_confirm" name="password_confirm" class="form-control" placeholder="Confirm your password" required>
            </div>

            <button type="submit" class="btn btn-primary w-100 mb-3">Create Account</button>
        </form>

        <div class="text-center">
            <p class="mb-0">Already have an account? <a href="login.php" class="text-primary">Sign in here</a></p>
        </div>

        <div class="text-center mt-3">
            <a href="index.php" class="back-link">← Back to Home</a>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
