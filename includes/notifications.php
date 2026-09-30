<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function cg_notification_user_id(): int
{
    if (empty($_SESSION['user_id']) || (($_SESSION['user_role'] ?? '') !== 'candidate')) {
        return 0;
    }

    return (int)$_SESSION['user_id'];
}

function cg_candidate_notification_count(int $userId): int
{
    $userId = (int)$userId;
    if ($userId <= 0) {
        return 0;
    }

    $conn = getDbConnection();
    $stmt = $conn->prepare('SELECT COUNT(*) AS unread_count FROM notifications WHERE user_id = ? AND is_read = 0');
    if (!$stmt) {
        $conn->close();
        return 0;
    }

    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = 0;
    if ($result) {
        $row = $result->fetch_assoc();
        $count = (int)($row['unread_count'] ?? 0);
    }
    $stmt->close();
    $conn->close();

    return $count;
}

function cg_candidate_notifications(int $userId, int $limit = 10): array
{
    $userId = (int)$userId;
    if ($userId <= 0) {
        return [];
    }

    $limit = max(1, (int)$limit);
    $conn = getDbConnection();
    $stmt = $conn->prepare('SELECT id, user_id, application_id, job_id, title, message, is_read, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC, id DESC LIMIT ?');
    if (!$stmt) {
        $conn->close();
        return [];
    }

    $stmt->bind_param('ii', $userId, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $notifications = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $notifications[] = $row;
        }
    }
    $stmt->close();
    $conn->close();

    return $notifications;
}

function cg_candidate_notification_target_url(?int $applicationId, ?int $jobId, string $basePath = ''): string
{
    $basePath = rtrim($basePath, '/');
    $basePrefix = $basePath === '' ? '' : $basePath . '/';

    if ($applicationId !== null && (int)$applicationId > 0) {
        return $basePrefix . 'candidate/applications.php';
    }

    if ($jobId !== null && (int)$jobId > 0) {
        return $basePrefix . 'job-details.php?id=' . urlencode((string)$jobId);
    }

    return $basePrefix . 'candidate/dashboard.php';
}

function cg_create_candidate_notification(int $userId, ?int $applicationId, ?int $jobId, string $title, string $message): bool
{
    $userId = (int)$userId;
    $title = trim($title);
    $message = trim($message);
    if ($userId <= 0 || $title === '' || $message === '') {
        return false;
    }

    $conn = getDbConnection();
    $existsStmt = $conn->prepare('SELECT id FROM notifications WHERE user_id = ? AND application_id = ? AND title = ? AND message = ? LIMIT 1');
    if (!$existsStmt) {
        $conn->close();
        return false;
    }

    $appId = $applicationId !== null ? (int)$applicationId : 0;
    $existsStmt->bind_param('iiss', $userId, $appId, $title, $message);
    $existsStmt->execute();
    $existing = $existsStmt->get_result()->fetch_assoc();
    $existsStmt->close();

    if ($existing) {
        $conn->close();
        return true;
    }

    $insertStmt = $conn->prepare('INSERT INTO notifications (user_id, application_id, job_id, title, message, is_read) VALUES (?, ?, ?, ?, ?, 0)');
    if (!$insertStmt) {
        $conn->close();
        return false;
    }

    $jobIdValue = $jobId !== null ? (int)$jobId : 0;
    $insertStmt->bind_param('iiiss', $userId, $appId, $jobIdValue, $title, $message);
    $success = $insertStmt->execute();
    $insertStmt->close();
    $conn->close();

    return $success;
}

function cg_mark_notification_read_for_candidate(int $userId, int $notificationId): bool
{
    $userId = (int)$userId;
    $notificationId = (int)$notificationId;
    if ($userId <= 0 || $notificationId <= 0) {
        return false;
    }

    $conn = getDbConnection();
    $stmt = $conn->prepare('UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ? AND is_read = 0 LIMIT 1');
    if (!$stmt) {
        $conn->close();
        return false;
    }

    $stmt->bind_param('ii', $notificationId, $userId);
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();

    return $success;
}

function cg_mark_all_notifications_read_for_candidate(int $userId): int
{
    $userId = (int)$userId;
    if ($userId <= 0) {
        return 0;
    }

    $conn = getDbConnection();
    $stmt = $conn->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0');
    if (!$stmt) {
        $conn->close();
        return 0;
    }

    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
    $conn->close();

    return (int)$affected;
}

if (basename($_SERVER['SCRIPT_NAME'] ?? '') === 'notifications.php') {
    header('Content-Type: application/json; charset=utf-8');

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $requestUserId = cg_notification_user_id();
    if ($requestUserId <= 0) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    $action = $_REQUEST['action'] ?? '';
    $notificationId = isset($_REQUEST['notification_id']) ? (int)$_REQUEST['notification_id'] : 0;

    if ($action === 'mark_read') {
        $success = cg_mark_notification_read_for_candidate($requestUserId, $notificationId);
        echo json_encode([
            'success' => $success,
            'unread_count' => cg_candidate_notification_count($requestUserId),
        ]);
        exit;
    }

    if ($action === 'mark_all_read') {
        $updated = cg_mark_all_notifications_read_for_candidate($requestUserId);
        echo json_encode([
            'success' => $updated >= 0,
            'updated_count' => $updated,
            'unread_count' => cg_candidate_notification_count($requestUserId),
        ]);
        exit;
    }

    echo json_encode([
        'success' => false,
        'message' => 'Invalid action',
        'unread_count' => cg_candidate_notification_count($requestUserId),
    ]);
    exit;
}
