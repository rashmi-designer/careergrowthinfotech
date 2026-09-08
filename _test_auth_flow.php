<?php
declare(strict_types=1);
ob_start();
session_start();

require_once __DIR__ . '/includes/db.php';

$conn = getDbConnection();

// Test 1: Create test candidate via registration logic
$testEmail = 'testcandidate' . time() . '@example.com';
$testPassword = 'TestPass123';
$testName = 'Test Candidate';
$testPhone = '9876543210';

echo "=== CANDIDATE AUTHENTICATION TEST ===" . PHP_EOL;
echo "Step 1: Create test candidate" . PHP_EOL;

$hashedPassword = password_hash($testPassword, PASSWORD_DEFAULT);
$role = 'candidate';
$status = 1;

$stmt = $conn->prepare('INSERT INTO users (name, email, phone, password, role, status) VALUES (?,?,?,?,?,?)');
if (!$stmt) {
    echo "FAILED: Could not prepare statement. Error: " . $conn->error . PHP_EOL;
    exit;
}

$stmt->bind_param('ssssis', $testName, $testEmail, $testPhone, $hashedPassword, $role, $status);
if (!$stmt->execute()) {
    echo "FAILED: Could not insert user. Error: " . $stmt->error . PHP_EOL;
    $stmt->close();
    $conn->close();
    exit;
}

$userId = $conn->insert_id;
$stmt->close();

echo "SUCCESS: User created with ID=" . $userId . PHP_EOL;
echo "Email: " . $testEmail . PHP_EOL;

// Test 2: Create candidate profile
echo PHP_EOL . "Step 2: Create candidate profile" . PHP_EOL;

$profileStmt = $conn->prepare('INSERT INTO candidate_profiles (user_id) VALUES (?)');
if (!$profileStmt) {
    echo "FAILED: Could not prepare profile statement" . PHP_EOL;
    exit;
}

$profileStmt->bind_param('i', $userId);
if (!$profileStmt->execute()) {
    echo "FAILED: Could not insert profile. Error: " . $profileStmt->error . PHP_EOL;
    $profileStmt->close();
    exit;
}
$profileStmt->close();

echo "SUCCESS: Candidate profile created" . PHP_EOL;

// Test 3: Verify login credentials
echo PHP_EOL . "Step 3: Test login verification" . PHP_EOL;

$loginStmt = $conn->prepare('SELECT id, name, email, password, role, status FROM users WHERE email = ? AND role = ? LIMIT 1');
if (!$loginStmt) {
    echo "FAILED: Could not prepare login statement" . PHP_EOL;
    exit;
}

$candidateRole = 'candidate';
$loginStmt->bind_param('ss', $testEmail, $candidateRole);
$loginStmt->execute();
$loginResult = $loginStmt->get_result();
$loginUser = $loginResult->fetch_assoc();
$loginStmt->close();

if (!$loginUser) {
    echo "FAILED: Could not find user for login" . PHP_EOL;
    exit;
}

echo "SUCCESS: User found for login" . PHP_EOL;

// Test 4: Verify password
echo PHP_EOL . "Step 4: Test password verification" . PHP_EOL;

if (password_verify($testPassword, $loginUser['password'])) {
    echo "SUCCESS: Password verification passed" . PHP_EOL;
} else {
    echo "FAILED: Password verification failed" . PHP_EOL;
    exit;
}

// Test 5: Verify role and status
echo PHP_EOL . "Step 5: Verify role and status" . PHP_EOL;

if ($loginUser['role'] === 'candidate' && (int)$loginUser['status'] === 1) {
    echo "SUCCESS: Role is 'candidate' and status is active" . PHP_EOL;
} else {
    echo "FAILED: Role or status incorrect. Role=" . $loginUser['role'] . ", Status=" . $loginUser['status'] . PHP_EOL;
    exit;
}

// Test 6: Check profile data
echo PHP_EOL . "Step 6: Verify profile data" . PHP_EOL;

$profileCheckStmt = $conn->prepare('SELECT id, user_id, skills, location FROM candidate_profiles WHERE user_id = ? LIMIT 1');
$profileCheckStmt->bind_param('i', $userId);
$profileCheckStmt->execute();
$profileData = $profileCheckStmt->get_result()->fetch_assoc();
$profileCheckStmt->close();

if ($profileData && $profileData['user_id'] == $userId) {
    echo "SUCCESS: Candidate profile verified" . PHP_EOL;
} else {
    echo "FAILED: Candidate profile not found" . PHP_EOL;
    exit;
}

// Test 7: Simulate session creation
echo PHP_EOL . "Step 7: Test session creation" . PHP_EOL;

if (session_status() === PHP_SESSION_ACTIVE) {
    $_SESSION['user_id'] = (int)$loginUser['id'];
    $_SESSION['user_name'] = $loginUser['name'];
    $_SESSION['user_email'] = $loginUser['email'];
    $_SESSION['user_role'] = $loginUser['role'];
    
    echo "SUCCESS: Session variables set" . PHP_EOL;
    echo "Session ID: " . session_id() . PHP_EOL;
} else {
    echo "WARNING: Session could not be verified as active" . PHP_EOL;
}

// Summary
echo PHP_EOL . "=== TEST SUMMARY ===" . PHP_EOL;
echo "Test Candidate Email: " . $testEmail . PHP_EOL;
echo "Test Candidate Password: " . $testPassword . PHP_EOL;
echo "Candidate ID: " . $userId . PHP_EOL;
echo PHP_EOL . "All authentication tests PASSED" . PHP_EOL;

$conn->close();
ob_end_clean();
?>
