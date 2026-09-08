<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/db.php';

$conn = getDbConnection();

// Create test candidate
$name = 'Test Candidate';
$email = 'testcandidate@example.com';
$phone = '9876543210';
$password = password_hash('TestPass123', PASSWORD_DEFAULT);
$role = 'candidate';
$status = 1;

// Check if already exists
$checkStmt = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$checkStmt->bind_param('s', $email);
$checkStmt->execute();
$existing = $checkStmt->get_result()->fetch_assoc();
$checkStmt->close();

if ($existing) {
    echo 'RESULT: TEST_CANDIDATE_EXISTS, ID=' . $existing['id'];
} else {
    // Insert user
    $stmt = $conn->prepare('INSERT INTO users (name, email, phone, password, role, status) VALUES (?,?,?,?,?,?)');
    $stmt->bind_param('ssssis', $name, $email, $phone, $password, $role, $status);
    
    if ($stmt->execute()) {
        $userId = $conn->insert_id;
        
        // Create profile
        $profileStmt = $conn->prepare('INSERT INTO candidate_profiles (user_id) VALUES (?)');
        $profileStmt->bind_param('i', $userId);
        $profileStmt->execute();
        $profileStmt->close();
        
        echo 'RESULT: TEST_CANDIDATE_CREATED, ID=' . $userId;
    } else {
        echo 'RESULT: CREATION_FAILED, ERROR=' . $conn->error;
    }
    $stmt->close();
}

$conn->close();
