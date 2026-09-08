<?php
declare(strict_types=1);

$logFile = __DIR__ . '/_test_output.txt';
$output = fopen($logFile, 'w');

require_once __DIR__ . '/includes/db.php';

$conn = getDbConnection();

fwrite($output, "=== DATABASE SCHEMA CHECK ===" . PHP_EOL);

// Check users table structure
fwrite($output, "SHOW CREATE TABLE users:" . PHP_EOL);
$schemaResult = $conn->query('SHOW CREATE TABLE users');
if ($schemaResult) {
    $schemaRow = $schemaResult->fetch_assoc();
    fwrite($output, $schemaRow['Create Table'] . PHP_EOL);
} else {
    fwrite($output, "ERROR: " . $conn->error . PHP_EOL);
}

fwrite($output, PHP_EOL . "=== COLUMN INFO ===" . PHP_EOL);
$columnResult = $conn->query('DESCRIBE users');
if ($columnResult) {
    fwrite($output, "Field | Type | Null | Key | Default | Extra" . PHP_EOL);
    while ($row = $columnResult->fetch_assoc()) {
        fwrite($output, $row['Field'] . " | " . $row['Type'] . " | " . $row['Null'] . " | " . $row['Key'] . " | " . $row['Default'] . " | " . $row['Extra'] . PHP_EOL);
    }
} else {
    fwrite($output, "ERROR: " . $conn->error . PHP_EOL);
}

fwrite($output, PHP_EOL . "=== TESTING DIRECT INSERT ===" . PHP_EOL);

// Try inserting using simple query (not prepared statement)
$testEmail2 = 'testcandidate2@example.com';
$testPassword2 = password_hash('TestPass123', PASSWORD_DEFAULT);

$simpleInsert = "INSERT INTO users (name, email, phone, password, role, status) VALUES ('Test User 2', '" . $conn->real_escape_string($testEmail2) . "', '1234567890', '" . $conn->real_escape_string($testPassword2) . "', 'candidate', 1)";
fwrite($output, "SQL: " . $simpleInsert . PHP_EOL);

if ($conn->query($simpleInsert)) {
    fwrite($output, "SUCCESS: Direct insert executed" . PHP_EOL);
    $simpleId = $conn->insert_id;
    
    // Check result
    $checkResult = $conn->query('SELECT id, email, role FROM users WHERE id = ' . $simpleId);
    if ($checkResult) {
        $checkRow = $checkResult->fetch_assoc();
        fwrite($output, "Inserted: id=" . $checkRow['id'] . " | email=" . $checkRow['email'] . " | role=[" . $checkRow['role'] . "]" . PHP_EOL);
    }
} else {
    fwrite($output, "ERROR: " . $conn->error . PHP_EOL);
}

$conn->close();
fclose($output);

echo "Schema check completed.";
?>
