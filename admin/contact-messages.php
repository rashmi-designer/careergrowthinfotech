<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/admin-auth.php';
require_admin();

$pageTitle = 'Contact Messages - Admin';
require_once __DIR__ . '/../includes/header.php';

$conn = getDbConnection();

$statusColumnExists = false;
$statusCheckStmt = $conn->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'contact_messages' AND COLUMN_NAME = 'status'");
if ($statusCheckStmt) {
    $statusCheckStmt->execute();
    $statusCheckResult = $statusCheckStmt->get_result();
    $statusRow = $statusCheckResult->fetch_row();
    $statusColumnExists = ((int)($statusRow[0] ?? 0)) > 0;
    $statusCheckStmt->close();
}

$messages = [];
$sql = 'SELECT id, name, email, phone, subject, message, created_at';
if ($statusColumnExists) {
    $sql .= ', status';
}
$sql .= ' FROM contact_messages ORDER BY created_at DESC';

$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
    $stmt->close();
}

$detailId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$selectedMessage = null;

if ($detailId > 0) {
    $detailSql = 'SELECT id, name, email, phone, subject, message, created_at';
    if ($statusColumnExists) {
        $detailSql .= ', status';
    }
    $detailSql .= ' FROM contact_messages WHERE id = ?';

    $detailStmt = $conn->prepare($detailSql);
    if ($detailStmt) {
        $detailStmt->bind_param('i', $detailId);
        $detailStmt->execute();
        $detailResult = $detailStmt->get_result();
        $selectedMessage = $detailResult->fetch_assoc();
        $detailStmt->close();
    }
}

$conn->close();
?>

<style>
    .admin-root {
        min-height: 100vh;
        display: flex;
        align-items: stretch;
        gap: 1.5rem;
        padding: 1.5rem;
        background: linear-gradient(180deg, rgba(13,110,253,0.02), rgba(255,255,255,0));
    }

    .sidebar {
        width: 260px;
        background: var(--cg-white);
        border: 1px solid var(--cg-border);
        border-radius: 1rem;
        padding: 1rem;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.05);
        position: sticky;
        top: 1rem;
        height: fit-content;
    }

    .brand-wrap {
        display: flex;
        align-items: center;
        gap: 0.8rem;
        padding: 0.25rem 0 1rem;
        border-bottom: 1px solid rgba(15, 23, 42, 0.04);
    }

    .sidebar-nav {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
        margin-top: 1rem;
    }

    .nav-link-admin {
        display: flex;
        align-items: center;
        gap: 0.7rem;
        padding: 0.7rem 0.8rem;
        border-radius: 0.65rem;
        font-weight: 600;
        color: var(--cg-accent);
        text-decoration: none;
    }

    .nav-link-admin:hover,
    .nav-link-admin:focus {
        color: var(--cg-primary);
        background: rgba(13,110,253,0.04);
        text-decoration: none;
    }

    .nav-link-admin.active {
        background: rgba(13,110,253,0.07);
        color: var(--cg-primary);
    }

    .sidebar-footer {
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid rgba(15, 23, 42, 0.04);
    }

    .main-panel {
        flex: 1 1 auto;
        min-width: 0;
    }

    .topbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
        flex-wrap: wrap;
    }

    .card-panel {
        background: var(--cg-white);
        border: 1px solid var(--cg-border);
        border-radius: 1rem;
        padding: 1.25rem;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.04);
        margin-bottom: 1rem;
    }

    .page-kicker {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.78rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--cg-primary);
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .user-pill {
        display: flex;
        align-items: center;
        gap: 0.8rem;
        background: rgba(13,110,253,0.04);
        border: 1px solid rgba(13,110,253,0.06);
        border-radius: 999px;
        padding: 0.5rem 0.8rem;
    }

    .avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: rgba(13,110,253,0.08);
        color: var(--cg-primary);
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1rem;
    }

    .detail-item {
        border: 1px solid var(--cg-border);
        border-radius: 0.75rem;
        padding: 0.9rem;
        background: rgba(13,110,253,0.02);
    }

    .detail-item .label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--cg-muted);
        margin-bottom: 0.35rem;
    }

    .message-box {
        white-space: pre-wrap;
        line-height: 1.7;
        color: var(--cg-text);
        margin: 0;
    }

    .table-wrap {
        overflow-x: auto;
    }

    .table {
        margin-bottom: 0;
        width: 100%;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.3rem 0.55rem;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 700;
        background: rgba(13,110,253,0.08);
        color: var(--cg-primary);
    }

    @media (max-width: 991.98px) {
        .admin-root {
            flex-direction: column;
            padding: 1rem;
        }

        .sidebar {
            width: 100%;
            position: static;
        }
    }
</style>

<main class="container-fluid admin-root">
    <aside class="sidebar">
        <div class="brand-wrap">
            <span class="brand-mark brand-mark-sm">
                <img src="../assets/images/logo.webp" alt="Career Grow Infotech logo" width="34" height="34" loading="lazy">
            </span>
            <div>
                <div class="brand-title">Career Grow Infotech</div>
                <div class="brand-subtitle">Admin Portal</div>
            </div>
        </div>

        <nav class="sidebar-nav" aria-label="Sidebar navigation">
            <a href="dashboard.php" class="nav-link-admin"><i class="bi bi-speedometer2"></i> Dashboard</a>
            <a href="jobs.php" class="nav-link-admin"><i class="bi bi-briefcase"></i> Jobs</a>
            <a href="applicants.php" class="nav-link-admin"><i class="bi bi-people"></i> Applicants</a>
            <a href="candidate-details.php" class="nav-link-admin"><i class="bi bi-person-badge"></i> Candidates</a>
            <a href="contact-messages.php" class="nav-link-admin active"><i class="bi bi-envelope-paper"></i> Contact Messages</a>
            <a href="settings.php" class="nav-link-admin"><i class="bi bi-gear"></i> Settings</a>
        </nav>

        <div class="sidebar-footer">
            <a href="../index.php" class="nav-link-admin"><i class="bi bi-house"></i> Back to Website</a>
            <a href="../logout.php" class="nav-link-admin"><i class="bi bi-box-arrow-right"></i> Logout</a>
        </div>
    </aside>

    <section class="main-panel">
        <div class="topbar">
            <div class="topbar-title">
                <h1>Contact Messages</h1>
                <div class="topbar-subtitle">Review inbound inquiries and recruitment-related messages</div>
            </div>
            <div class="user-pill">
                <div class="avatar"><i class="bi bi-person-circle"></i></div>
                <div>
                    <div class="fw-semibold">Administrator</div>
                    <div class="text-muted small">Admin</div>
                </div>
            </div>
        </div>

        <div class="card-panel">
            <div class="page-kicker"><i class="bi bi-envelope-paper"></i> Admin / Contact Messages</div>
            <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
                <div>
                    <h2 class="mb-1">Inbound Messages</h2>
                    <p class="mb-0 text-muted">Messages submitted through the public contact form are listed here.</p>
                </div>
                <div class="fw-semibold text-primary"><?php echo htmlspecialchars((string)count($messages), ENT_QUOTES, 'UTF-8'); ?> total</div>
            </div>
        </div>

        <?php if ($selectedMessage): ?>
            <div class="card-panel">
                <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-3">
                    <div>
                        <div class="page-kicker"><i class="bi bi-envelope-open"></i> Message Details</div>
                        <h3 class="mb-0">Message #<?php echo (int)($selectedMessage['id'] ?? 0); ?></h3>
                    </div>
                    <a href="contact-messages.php" class="btn btn-outline-secondary">Back to Messages</a>
                </div>

                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="label">Name</div>
                        <div class="fw-semibold"><?php echo htmlspecialchars((string)($selectedMessage['name'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>

                    <div class="detail-item">
                        <div class="label">Email</div>
                        <div class="fw-semibold"><?php echo htmlspecialchars((string)($selectedMessage['email'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>

                    <div class="detail-item">
                        <div class="label">Phone</div>
                        <div class="fw-semibold"><?php echo htmlspecialchars((string)($selectedMessage['phone'] ?? 'Not provided'), ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>

                    <div class="detail-item">
                        <div class="label">Subject</div>
                        <div class="fw-semibold"><?php echo htmlspecialchars((string)($selectedMessage['subject'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>

                    <div class="detail-item">
                        <div class="label">Date / Time</div>
                        <div class="fw-semibold"><?php echo htmlspecialchars((string)($selectedMessage['created_at'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>

                    <?php if ($statusColumnExists): ?>
                        <div class="detail-item">
                            <div class="label">Status</div>
                            <span class="status-badge"><?php echo htmlspecialchars((string)($selectedMessage['status'] ?? 'Pending'), ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="detail-item mt-3">
                    <div class="label">Message</div>
                    <p class="message-box"><?php echo nl2br(htmlspecialchars((string)($selectedMessage['message'] ?? ''), ENT_QUOTES, 'UTF-8')); ?></p>
                </div>
            </div>
        <?php endif; ?>

        <div class="card-panel">
            <?php if (empty($messages)): ?>
                <div class="text-center py-4">
                    <div class="mb-2 fs-1 text-primary"><i class="bi bi-envelope"></i></div>
                    <h4>No contact messages yet</h4>
                    <p class="text-muted mb-0">Messages submitted through the public contact form will appear here.</p>
                </div>
            <?php else: ?>
                <div class="table-wrap">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Subject</th>
                                <th>Message</th>
                                <th>Date / Time</th>
                                <?php if ($statusColumnExists): ?><th>Status</th><?php endif; ?>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($messages as $message): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars((string)($message['name'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars((string)($message['email'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars((string)($message['phone'] ?? 'Not provided'), ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars((string)($message['subject'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars(mb_substr((string)($message['message'] ?? ''), 0, 90, 'UTF-8') . (mb_strlen((string)($message['message'] ?? ''), 'UTF-8') > 90 ? '...' : ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars((string)($message['created_at'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                    <?php if ($statusColumnExists): ?>
                                        <td><span class="status-badge"><?php echo htmlspecialchars((string)($message['status'] ?? 'Pending'), ENT_QUOTES, 'UTF-8'); ?></span></td>
                                    <?php endif; ?>
                                    <td class="text-end">
                                        <a href="contact-messages.php?id=<?php echo (int)($message['id'] ?? 0); ?>" class="btn btn-sm btn-primary">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>
