<?php
declare(strict_types=1);

$logFile = __DIR__ . '/_test_output.txt';
$output = fopen($logFile, 'w');

require_once __DIR__ . '/includes/db.php';

$conn = getDbConnection();

fwrite($output, "=== FINAL TEST CANDIDATE SETUP ===" . PHP_EOL);

// Delete existing test candidate
$testEmail = 'testcandidate@example.com';
$conn->query('DELETE FROM candidate_profiles WHERE user_id IN (SELECT id FROM users WHERE email = "' . $conn->real_escape_string($testEmail) . '")');
$conn->query('DELETE FROM users WHERE email = "' . $conn->real_escape_string($testEmail) . '"');

fwrite($output, "Cleaned old test candidate" . PHP_EOL);

// Insert test candidate using direct query (avoids ENUM prepared statement issue)
$testName = 'Test Candidate';
$testPhone = '9876543210';
$testPassword = password_hash('TestPass123', PASSWORD_DEFAULT);

fwrite($output, PHP_EOL . "Creating test candidate via direct query..." . PHP_EOL);

$escapedName = $conn->real_escape_string($testName);
$escapedEmail = $conn->real_escape_string($testEmail);
$escapedPhone = $conn->real_escape_string($testPhone);
$escapedPassword = $conn->real_escape_string($testPassword);

$insertQuery = "INSERT INTO users (name, email, phone, password, role, status) VALUES ('" . $escapedName . "', '" . $escapedEmail . "', '" . $escapedPhone . "', '" . $escapedPassword . "', 'candidate', 1)";

if ($conn->query($insertQuery)) {
    $userId = $conn->insert_id;
    fwrite($output, "SUCCESS: User created with ID=" . $userId . PHP_EOL);
    
    // Verify insert
    $verify = $conn->query('SELECT id, email, role, status FROM users WHERE id = ' . $userId);
    $verifyRow = $verify->fetch_assoc();
    fwrite($output, "Verified: id=" . $verifyRow['id'] . " | email=" . $verifyRow['email'] . " | role=" . $verifyRow['role'] . " | status=" . $verifyRow['status'] . PHP_EOL);
    
    // Create profile
    $profileQuery = 'INSERT INTO candidate_profiles (user_id) VALUES (' . $userId . ')';
    if ($conn->query($profileQuery)) {
        fwrite($output, "SUCCESS: Candidate profile created" . PHP_EOL);
    } else {
        fwrite($output, "ERROR: Profile creation failed - " . $conn->error . PHP_EOL);
    }
} else {
    fwrite($output, "ERROR: Insert failed - " . $conn->error . PHP_EOL);
    $conn->close();
    fclose($output);
    exit;
}

// TEST LOGIN
fwrite($output, PHP_EOL . "=== TESTING LOGIN ===" . PHP_EOL);

$loginStmt = $conn->prepare('SELECT id, name, email, password, role, status FROM users WHERE email = ? AND role = ? LIMIT 1');
$loginRole = 'candidate';
$loginStmt->bind_param('ss', $testEmail, $loginRole);
$loginStmt->execute();
$loginUser = $loginStmt->get_result()->fetch_assoc();
$loginStmt->close();

if ($loginUser) {
    fwrite($output, "SUCCESS: User found in database" . PHP_EOL);
    
    if (password_verify('TestPass123', $loginUser['password'])) {
        fwrite($output, "SUCCESS: Password verification passed" . PHP_EOL);
        fwrite($output, PHP_EOL . "=== TEST CANDIDATE LOGIN CREDENTIALS ===" . PHP_EOL);
        fwrite($output, "Email: " . $testEmail . PHP_EOL);
        fwrite($output, "Password: TestPass123" . PHP_EOL);
        fwrite($output, "User ID: " . $loginUser['id'] . PHP_EOL);
        fwrite($output, "User Name: " . $loginUser['name'] . PHP_EOL);
        fwrite($output, "Role: " . $loginUser['role'] . PHP_EOL);
        fwrite($output, PHP_EOL . "READY FOR CANDIDATE FLOW TESTING" . PHP_EOL);
    } else {
        fwrite($output, "ERROR: Password verification failed" . PHP_EOL);
    }
} else {
    fwrite($output, "ERROR: User not found in login query" . PHP_EOL);
}

// Check all users in database
fwrite($output, PHP_EOL . "=== ALL USERS IN DATABASE ===" . PHP_EOL);
$allUsers = $conn->query('SELECT id, email, role, status FROM users ORDER BY id DESC');
while ($row = $allUsers->fetch_assoc()) {
    fwrite($output, "ID=" . $row['id'] . " | email=" . $row['email'] . " | role=" . $row['role'] . " | status=" . $row['status'] . PHP_EOL);
}

$conn->close();
fclose($output);

echo "Setup completed. Check _test_output.txt";
?>
