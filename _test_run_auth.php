<?php
declare(strict_types=1);

$logFile = __DIR__ . '/_test_output.txt';
$output = fopen($logFile, 'w');

require_once __DIR__ . '/includes/db.php';

$conn = getDbConnection();

// Create test candidate
$testEmail = 'testcandidate@example.com';
$testPassword = 'TestPass123';
$testName = 'Test Candidate';
$testPhone = '9876543210';

fwrite($output, "=== CANDIDATE AUTHENTICATION TEST ===" . PHP_EOL);
fwrite($output, "Step 1: Check if candidate exists" . PHP_EOL);

$checkStmt = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
if ($checkStmt) {
    $checkStmt->bind_param('s', $testEmail);
    $checkStmt->execute();
    $existing = $checkStmt->get_result()->fetch_assoc();
    $checkStmt->close();
    
    if ($existing) {
        fwrite($output, "FOUND: Test candidate already exists with ID=" . $existing['id'] . PHP_EOL);
        $userId = $existing['id'];
    } else {
        fwrite($output, "NOT FOUND: Creating new test candidate" . PHP_EOL);
        
        // Insert user
        $hashedPassword = password_hash($testPassword, PASSWORD_DEFAULT);
        $role = 'candidate';
        $status = 1;
        
        $stmt = $conn->prepare('INSERT INTO users (name, email, phone, password, role, status) VALUES (?,?,?,?,?,?)');
        $stmt->bind_param('ssssis', $testName, $testEmail, $testPhone, $hashedPassword, $role, $status);
        
        if ($stmt->execute()) {
            $userId = $conn->insert_id;
            fwrite($output, "SUCCESS: User created with ID=" . $userId . PHP_EOL);
            
            // Create profile
            $profileStmt = $conn->prepare('INSERT INTO candidate_profiles (user_id) VALUES (?)');
            $profileStmt->bind_param('i', $userId);
            if ($profileStmt->execute()) {
                fwrite($output, "SUCCESS: Candidate profile created" . PHP_EOL);
            } else {
                fwrite($output, "FAILED: Profile creation error" . PHP_EOL);
            }
            $profileStmt->close();
        } else {
            fwrite($output, "FAILED: User creation error: " . $stmt->error . PHP_EOL);
        }
        $stmt->close();
    }
} else {
    fwrite($output, "FAILED: Could not prepare check statement" . PHP_EOL);
}

fwrite($output, PHP_EOL . "Step 2: Test login flow" . PHP_EOL);

$loginStmt = $conn->prepare('SELECT id, name, email, password, role, status FROM users WHERE email = ? AND role = ? LIMIT 1');
if ($loginStmt) {
    $candidateRole = 'candidate';
    $loginStmt->bind_param('ss', $testEmail, $candidateRole);
    $loginStmt->execute();
    $loginUser = $loginStmt->get_result()->fetch_assoc();
    $loginStmt->close();
    
    if ($loginUser && password_verify($testPassword, $loginUser['password'])) {
        fwrite($output, "SUCCESS: Login credentials verified" . PHP_EOL);
        fwrite($output, "- Email: " . $loginUser['email'] . PHP_EOL);
        fwrite($output, "- Role: " . $loginUser['role'] . PHP_EOL);
        fwrite($output, "- Status: " . $loginUser['status'] . PHP_EOL);
        fwrite($output, "- Name: " . $loginUser['name'] . PHP_EOL);
    } else {
        fwrite($output, "FAILED: Login verification failed" . PHP_EOL);
    }
} else {
    fwrite($output, "FAILED: Could not prepare login statement" . PHP_EOL);
}

fwrite($output, PHP_EOL . "=== TEST SUMMARY ===" . PHP_EOL);
fwrite($output, "Test Email: " . $testEmail . PHP_EOL);
fwrite($output, "Test Password: " . $testPassword . PHP_EOL);
fwrite($output, "All checks completed" . PHP_EOL);

$conn->close();
fclose($output);

echo "Test output saved to _test_output.txt";
?>
