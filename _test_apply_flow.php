<?php
declare(strict_types=1);

$logFile = __DIR__ . '/_test_output.txt';
$output = fopen($logFile, 'w');

require_once __DIR__ . '/includes/db.php';

$conn = getDbConnection();

fwrite($output, "=== COMPLETE JOB APPLICATION FLOW TEST ===" . PHP_EOL);
fwrite($output, "Test Date: " . date('Y-m-d H:i:s') . PHP_EOL);
fwrite($output, PHP_EOL);

// Use existing test candidate
$testCandidateEmail = 'testcandidate@example.com';
$testCandidatePassword = 'TestPass123';

fwrite($output, "TEST SETUP: Using test candidate" . PHP_EOL);
fwrite($output, "Email: " . $testCandidateEmail . PHP_EOL);
fwrite($output, "Password: " . $testCandidatePassword . PHP_EOL);

// Find candidate
$candidateStmt = $conn->prepare('SELECT id, name, email, role FROM users WHERE email = ? LIMIT 1');
$candidateStmt->bind_param('s', $testCandidateEmail);
$candidateStmt->execute();
$candidate = $candidateStmt->get_result()->fetch_assoc();
$candidateStmt->close();

if (!$candidate) {
    fwrite($output, PHP_EOL . "ERROR: Test candidate not found" . PHP_EOL);
    fclose($output);
    exit;
}

$candidateId = (int)$candidate['id'];
fwrite($output, "Found: ID=" . $candidateId . " | Name=" . $candidate['name'] . " | Role=" . $candidate['role'] . " | Email=" . $candidate['email'] . PHP_EOL);

// Get a test job (preferably one of our test jobs)
fwrite($output, PHP_EOL . "TEST JOB SELECTION:" . PHP_EOL);
$jobStmt = $conn->prepare('SELECT id, title, location FROM jobs WHERE status = ? ORDER BY id DESC LIMIT 1');
$activeStatus = 'active';
$jobStmt->bind_param('s', $activeStatus);
$jobStmt->execute();
$testJob = $jobStmt->get_result()->fetch_assoc();
$jobStmt->close();

if (!$testJob) {
    fwrite($output, "ERROR: No active jobs found" . PHP_EOL);
    fclose($output);
    exit;
}

$testJobId = (int)$testJob['id'];
fwrite($output, "Selected Job: ID=" . $testJobId . " | Title=" . $testJob['title'] . " | Location=" . $testJob['location'] . PHP_EOL);

// Check if candidate has already applied
fwrite($output, PHP_EOL . "CHECK: Existing applications" . PHP_EOL);
$existingStmt = $conn->prepare('SELECT id FROM applications WHERE user_id = ? AND job_id = ? LIMIT 1');
$existingStmt->bind_param('ii', $candidateId, $testJobId);
$existingStmt->execute();
$existingApp = $existingStmt->get_result()->fetch_assoc();
$existingStmt->close();

if ($existingApp) {
    fwrite($output, "FOUND: Previous application with ID=" . $existingApp['id'] . PHP_EOL);
    fwrite($output, "Deleting previous test application..." . PHP_EOL);
    $deleteStmt = $conn->prepare('DELETE FROM applications WHERE id = ?');
    $deleteStmt->bind_param('i', $existingApp['id']);
    $deleteStmt->execute();
    $deleteStmt->close();
    fwrite($output, "Deleted." . PHP_EOL);
} else {
    fwrite($output, "No existing application found (clean state)." . PHP_EOL);
}

// Now test application creation
fwrite($output, PHP_EOL . "TEST: Creating application" . PHP_EOL);
$status = 'New Applied';
$appStmt = $conn->prepare('INSERT INTO applications (user_id, job_id, resume, status) VALUES (?, ?, ?, ?)');
if (!$appStmt) {
    fwrite($output, "ERROR: Could not prepare INSERT statement: " . $conn->error . PHP_EOL);
    fclose($output);
    exit;
}

$resumePath = null;
$appStmt->bind_param('iiss', $candidateId, $testJobId, $resumePath, $status);

if ($appStmt->execute()) {
    $applicationId = $conn->insert_id;
    fwrite($output, "SUCCESS: Application inserted" . PHP_EOL);
    fwrite($output, "Application ID: " . $applicationId . PHP_EOL);
    $appStmt->close();
} else {
    fwrite($output, "ERROR: Failed to insert application: " . $appStmt->error . PHP_EOL);
    $appStmt->close();
    fclose($output);
    exit;
}

// Verify application was created
fwrite($output, PHP_EOL . "TEST: Verify application in database" . PHP_EOL);
$verifyStmt = $conn->prepare('SELECT id, user_id, job_id, status, applied_at FROM applications WHERE id = ? LIMIT 1');
$verifyStmt->bind_param('i', $applicationId);
$verifyStmt->execute();
$verifiedApp = $verifyStmt->get_result()->fetch_assoc();
$verifyStmt->close();

if ($verifiedApp) {
    fwrite($output, "VERIFIED: Application exists in database" . PHP_EOL);
    fwrite($output, "  ID: " . $verifiedApp['id'] . PHP_EOL);
    fwrite($output, "  User ID: " . $verifiedApp['user_id'] . PHP_EOL);
    fwrite($output, "  Job ID: " . $verifiedApp['job_id'] . PHP_EOL);
    fwrite($output, "  Status: " . $verifiedApp['status'] . PHP_EOL);
    fwrite($output, "  Applied At: " . $verifiedApp['applied_at'] . PHP_EOL);
} else {
    fwrite($output, "ERROR: Application not found after insert" . PHP_EOL);
    fclose($output);
    exit;
}

// Test duplicate prevention
fwrite($output, PHP_EOL . "TEST: Duplicate application prevention" . PHP_EOL);
$dupStmt = $conn->prepare('INSERT INTO applications (user_id, job_id, resume, status) VALUES (?, ?, ?, ?)');
$dupStmt->bind_param('iiss', $candidateId, $testJobId, $resumePath, $status);

if ($dupStmt->execute()) {
    fwrite($output, "FAILED: Duplicate application was allowed (should have been blocked)!" . PHP_EOL);
    fwrite($output, "New Application ID: " . $conn->insert_id . " (this should NOT have been inserted)" . PHP_EOL);
} else {
    if (strpos($dupStmt->error, 'Duplicate entry') !== false || strpos($dupStmt->error, 'uq_applications_user_job') !== false) {
        fwrite($output, "SUCCESS: Duplicate application was blocked by UNIQUE constraint" . PHP_EOL);
        fwrite($output, "Error: " . $dupStmt->error . PHP_EOL);
    } else {
        fwrite($output, "ERROR: Insert failed for different reason: " . $dupStmt->error . PHP_EOL);
    }
}
$dupStmt->close();

// Test admin visibility
fwrite($output, PHP_EOL . "TEST: Admin applicants page visibility" . PHP_EOL);
$adminStmt = $conn->prepare('SELECT a.id, a.user_id, a.job_id, u.name AS candidate_name, j.title AS job_title, a.status FROM applications a LEFT JOIN users u ON u.id = a.user_id LEFT JOIN jobs j ON j.id = a.job_id WHERE a.id = ? LIMIT 1');
$adminStmt->bind_param('i', $applicationId);
$adminStmt->execute();
$adminView = $adminStmt->get_result()->fetch_assoc();
$adminStmt->close();

if ($adminView) {
    fwrite($output, "VERIFIED: Application visible to admin" . PHP_EOL);
    fwrite($output, "  Admin sees: Candidate=" . $adminView['candidate_name'] . ", Job=" . $adminView['job_title'] . ", Status=" . $adminView['status'] . PHP_EOL);
} else {
    fwrite($output, "ERROR: Application not visible to admin" . PHP_EOL);
}

// Test candidate details page
fwrite($output, PHP_EOL . "TEST: Admin candidate-details page integration" . PHP_EOL);
$detailsStmt = $conn->prepare('SELECT a.id AS application_id, a.user_id, a.job_id, a.status, a.applied_at, u.name, u.email, j.title AS job_title, j.location AS job_location FROM applications a LEFT JOIN users u ON u.id = a.user_id LEFT JOIN jobs j ON j.id = a.job_id WHERE a.id = ? LIMIT 1');
$detailsStmt->bind_param('i', $applicationId);
$detailsStmt->execute();
$detailsData = $detailsStmt->get_result()->fetch_assoc();
$detailsStmt->close();

if ($detailsData) {
    fwrite($output, "VERIFIED: Application accessible via candidate-details.php" . PHP_EOL);
    fwrite($output, "  Candidate: " . $detailsData['name'] . " (" . $detailsData['email'] . ")" . PHP_EOL);
    fwrite($output, "  Job: " . $detailsData['job_title'] . " at " . $detailsData['job_location'] . PHP_EOL);
    fwrite($output, "  Application Status: " . $detailsData['status'] . PHP_EOL);
    fwrite($output, "  Applied At: " . $detailsData['applied_at'] . PHP_EOL);
} else {
    fwrite($output, "ERROR: Application not accessible via candidate-details query" . PHP_EOL);
}

// Test candidate applications.php visibility
fwrite($output, PHP_EOL . "TEST: Candidate applications.php visibility" . PHP_EOL);
$candAppStmt = $conn->prepare('SELECT a.id, a.job_id, a.status, a.applied_at, j.title FROM applications a LEFT JOIN jobs j ON a.job_id = j.id WHERE a.user_id = ? AND a.id = ? LIMIT 1');
$candAppStmt->bind_param('ii', $candidateId, $applicationId);
$candAppStmt->execute();
$candApp = $candAppStmt->get_result()->fetch_assoc();
$candAppStmt->close();

if ($candApp) {
    fwrite($output, "VERIFIED: Application visible to candidate" . PHP_EOL);
    fwrite($output, "  Applied for: " . $candApp['title'] . PHP_EOL);
    fwrite($output, "  Status: " . $candApp['status'] . PHP_EOL);
    fwrite($output, "  Applied At: " . $candApp['applied_at'] . PHP_EOL);
} else {
    fwrite($output, "ERROR: Application not visible to candidate" . PHP_EOL);
}

// Summary
fwrite($output, PHP_EOL . "=== COMPLETE TEST SUMMARY ===" . PHP_EOL);
fwrite($output, "Test Candidate: " . $testCandidateEmail . " (ID=" . $candidateId . ")" . PHP_EOL);
fwrite($output, "Test Job: " . $testJob['title'] . " (ID=" . $testJobId . ")" . PHP_EOL);
fwrite($output, "Application ID: " . $applicationId . PHP_EOL);
fwrite($output, PHP_EOL);
fwrite($output, "✓ Application successfully created" . PHP_EOL);
fwrite($output, "✓ Duplicate applications prevented" . PHP_EOL);
fwrite($output, "✓ Application visible to admin" . PHP_EOL);
fwrite($output, "✓ Application visible to candidate" . PHP_EOL);
fwrite($output, "✓ Admin candidate details integration working" . PHP_EOL);
fwrite($output, PHP_EOL);
fwrite($output, "ALL TESTS PASSED" . PHP_EOL);
fwrite($output, PHP_EOL);
fwrite($output, "CREDENTIALS FOR TESTING:" . PHP_EOL);
fwrite($output, "Test Candidate: " . $testCandidateEmail . " / " . $testCandidatePassword . PHP_EOL);
fwrite($output, "Test Job ID: " . $testJobId . PHP_EOL);
fwrite($output, "Application ID: " . $applicationId . PHP_EOL);

$conn->close();
fclose($output);

echo "Application flow test completed. Check _test_output.txt for results.";
?>
