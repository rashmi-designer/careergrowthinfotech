<?php
declare(strict_types=1);

$logFile = __DIR__ . '/_test_output.txt';
$output = fopen($logFile, 'w');

require_once __DIR__ . '/includes/db.php';

$conn = getDbConnection();

fwrite($output, "=== DEBUGGING TEST USER ===" . PHP_EOL);

// Check what's in the database
$debugStmt = $conn->prepare('SELECT id, name, email, role, status FROM users ORDER BY id DESC LIMIT 5');
$debugStmt->execute();
$results = $debugStmt->get_result();

fwrite($output, "Recent users in database:" . PHP_EOL);
while ($row = $results->fetch_assoc()) {
    fwrite($output, "ID=" . $row['id'] . " | Email=" . $row['email'] . " | Role=" . $row['role'] . " | Status=" . $row['status'] . PHP_EOL);
}
$debugStmt->close();

fwrite($output, PHP_EOL . "=== TESTING SPECIFIC QUERY ===" . PHP_EOL);

$testEmail = 'testcandidate@example.com';
$testRole = 'candidate';

fwrite($output, "Query: SELECT * FROM users WHERE email = ? AND role = ?" . PHP_EOL);
fwrite($output, "Params: email=" . $testEmail . ", role=" . $testRole . PHP_EOL);

$testStmt = $conn->prepare('SELECT id, name, email, role, status FROM users WHERE email = ? AND role = ?');
if (!$testStmt) {
    fwrite($output, "ERROR: " . $conn->error . PHP_EOL);
} else {
    $testStmt->bind_param('ss', $testEmail, $testRole);
    if (!$testStmt->execute()) {
        fwrite($output, "ERROR: " . $testStmt->error . PHP_EOL);
    } else {
        $testResult = $testStmt->get_result();
        $testRow = $testResult->fetch_assoc();
        
        if ($testRow) {
            fwrite($output, "SUCCESS: Found user" . PHP_EOL);
            fwrite($output, "ID=" . $testRow['id'] . " | Email=" . $testRow['email'] . " | Role=" . $testRow['role'] . PHP_EOL);
        } else {
            fwrite($output, "NOT FOUND: No user matched the criteria" . PHP_EOL);
            
            fwrite($output, PHP_EOL . "Trying without role filter:" . PHP_EOL);
            $emailStmt = $conn->prepare('SELECT id, name, email, role, status FROM users WHERE email = ?');
            $emailStmt->bind_param('s', $testEmail);
            $emailStmt->execute();
            $emailResult = $emailStmt->get_result();
            $emailRow = $emailResult->fetch_assoc();
            
            if ($emailRow) {
                fwrite($output, "Found user by email: ID=" . $emailRow['id'] . " | Role=" . $emailRow['role'] . " (role type: " . gettype($emailRow['role']) . ")" . PHP_EOL);
            } else {
                fwrite($output, "User not found even by email alone" . PHP_EOL);
            }
            $emailStmt->close();
        }
    }
    $testStmt->close();
}

$conn->close();
fclose($output);

echo "Debug check completed.";
?>
