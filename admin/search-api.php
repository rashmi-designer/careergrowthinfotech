<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Require authentication first
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/admin-auth.php';

// Require admin authentication for search access
if (!is_admin_authenticated()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Get search query
$q = trim((string)($_GET['q'] ?? ''));

// Return empty result if query is too short
if (empty($q) || strlen($q) < 2) {
    echo json_encode(['results' => []]);
    exit;
}

// Limit per category
$limit = 5;

// Safe search term for SQL LIKE
$searchTerm = '%' . $q . '%';

$conn = getDbConnection();
$results = [
    'jobs' => [],
    'candidates' => [],
    'applications' => [],
    'messages' => []
];

// Search Jobs - search across title, company, location, job_type, experience_level
$jobsSql = "SELECT id, title, company, location FROM jobs WHERE status = 'active' AND (title LIKE ? OR COALESCE(company, '') LIKE ? OR location LIKE ? OR job_type LIKE ? OR experience_level LIKE ?) LIMIT ?";
$jobsStmt = $conn->prepare($jobsSql);
if ($jobsStmt) {
    $jobsStmt->bind_param('sssssi', $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $limit);
    $jobsStmt->execute();
    $jobsResult = $jobsStmt->get_result();
    while ($row = $jobsResult->fetch_assoc()) {
        $results['jobs'][] = [
            'id' => (int)$row['id'],
            'title' => htmlspecialchars((string)$row['title'], ENT_QUOTES, 'UTF-8'),
            'company' => htmlspecialchars((string)($row['company'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'),
            'location' => htmlspecialchars((string)$row['location'], ENT_QUOTES, 'UTF-8'),
        ];
    }
    $jobsStmt->close();
}

// Search Candidates (Users with role='candidate')
$candidatesSql = "SELECT id, name, email, phone FROM users WHERE role = 'candidate' AND (name LIKE ? OR email LIKE ?) LIMIT ?";
$candidatesStmt = $conn->prepare($candidatesSql);
if ($candidatesStmt) {
    $candidatesStmt->bind_param('ssi', $searchTerm, $searchTerm, $limit);
    $candidatesStmt->execute();
    $candidatesResult = $candidatesStmt->get_result();
    while ($row = $candidatesResult->fetch_assoc()) {
        $results['candidates'][] = [
            'id' => (int)$row['id'],
            'name' => htmlspecialchars((string)$row['name'], ENT_QUOTES, 'UTF-8'),
            'email' => htmlspecialchars((string)$row['email'], ENT_QUOTES, 'UTF-8'),
            'phone' => htmlspecialchars((string)($row['phone'] ?? ''), ENT_QUOTES, 'UTF-8'),
        ];
    }
    $candidatesStmt->close();
}

// Search Applications
$applicationsSql = "
    SELECT a.id, a.user_id, u.name AS candidate_name, u.email, j.title AS job_title, a.applied_at
    FROM applications a
    JOIN users u ON a.user_id = u.id
    JOIN jobs j ON a.job_id = j.id
    WHERE u.name LIKE ? OR u.email LIKE ? OR j.title LIKE ?
    LIMIT ?
";
$applicationsStmt = $conn->prepare($applicationsSql);
if ($applicationsStmt) {
    $applicationsStmt->bind_param('sssi', $searchTerm, $searchTerm, $searchTerm, $limit);
    $applicationsStmt->execute();
    $applicationsResult = $applicationsStmt->get_result();
    while ($row = $applicationsResult->fetch_assoc()) {
        $results['applications'][] = [
            'id' => (int)$row['id'],
            'user_id' => (int)$row['user_id'],
            'candidate_name' => htmlspecialchars((string)$row['candidate_name'], ENT_QUOTES, 'UTF-8'),
            'email' => htmlspecialchars((string)$row['email'], ENT_QUOTES, 'UTF-8'),
            'job_title' => htmlspecialchars((string)$row['job_title'], ENT_QUOTES, 'UTF-8'),
            'applied_at' => htmlspecialchars((string)$row['applied_at'], ENT_QUOTES, 'UTF-8'),
        ];
    }
    $applicationsStmt->close();
}

// Search Contact Messages
$messagesSql = "SELECT id, name, email, subject FROM contact_messages WHERE name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ? LIMIT ?";
$messagesStmt = $conn->prepare($messagesSql);
if ($messagesStmt) {
    $messagesStmt->bind_param('ssssi', $searchTerm, $searchTerm, $searchTerm, $searchTerm, $limit);
    $messagesStmt->execute();
    $messagesResult = $messagesStmt->get_result();
    while ($row = $messagesResult->fetch_assoc()) {
        $results['messages'][] = [
            'id' => (int)$row['id'],
            'name' => htmlspecialchars((string)$row['name'], ENT_QUOTES, 'UTF-8'),
            'email' => htmlspecialchars((string)$row['email'], ENT_QUOTES, 'UTF-8'),
            'subject' => htmlspecialchars((string)($row['subject'] ?? 'No subject'), ENT_QUOTES, 'UTF-8'),
        ];
    }
    $messagesStmt->close();
}

$conn->close();

// Return JSON results
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['results' => $results], JSON_UNESCAPED_SLASHES);
