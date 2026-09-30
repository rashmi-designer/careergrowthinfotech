<?php
$basePath = $basePath ?? '';
$currentPage = basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');
$isCandidateSession = !empty($_SESSION['user_id']) && (($_SESSION['user_role'] ?? '') === 'candidate');

// Allow pages to hide the public header/navbar when rendering standalone auth pages.
if (!empty($hidePublicLayout)) {
    return;
}
require_once __DIR__ . '/notifications.php';

$navItems = [
    ['label' => 'Home', 'file' => 'index.php', 'href' => $basePath . 'index.php', 'icon' => 'bi-house-door'],
    ['label' => 'About', 'file' => 'about.php', 'href' => $basePath . 'about.php', 'icon' => 'bi-info-circle'],
    ['label' => 'Services', 'file' => 'services.php', 'href' => $basePath . 'services.php', 'icon' => 'bi-grid'],
    ['label' => 'Jobs', 'file' => 'jobs.php', 'href' => $basePath . 'jobs.php', 'icon' => 'bi-briefcase'],
    ['label' => 'Contact', 'file' => 'contact.php', 'href' => $basePath . 'contact.php', 'icon' => 'bi-envelope'],
];

$candidateNotificationCount = 0;
$candidateNotifications = [];
if ($isCandidateSession) {
    $candidateNotificationCount = cg_candidate_notification_count((int)($_SESSION['user_id'] ?? 0));
    $candidateNotifications = cg_candidate_notifications((int)($_SESSION['user_id'] ?? 0), 8);
}
?>
<nav class="navbar navbar-expand-lg sticky-top" aria-label="Main navigation">
    <div class="container">
        <a class="navbar-brand" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>index.php" aria-label="Career Grow Infotech home">
            <span class="brand-mark">
                <img src="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>assets/images/logo.webp" alt="Career Grow Infotech logo" width="42" height="42" loading="lazy">
            </span>
            <span class="brand-text visually-hidden">
                <span class="brand-title">Career Grow Infotech Pvt. Ltd.</span>
            </span>
        </a>

        <div class="mobile-header-actions d-lg-none" id="mobileHeaderActions" aria-label="Account actions">
            <!-- Public theme toggle (mobile) -->
            <button id="publicThemeToggleMobile" class="btn theme-toggle d-inline-flex align-items-center justify-content-center" type="button" aria-label="Toggle site theme" title="Toggle site theme">
                <i class="bi bi-moon-fill" aria-hidden="true"></i>
            </button>
        </div>

        <button class="navbar-toggler d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse branded-mobile-drawer" id="mainNavbar">
            <div class="mobile-drawer-header d-lg-none mobile-brand-header">
                <a class="mobile-drawer-brand" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?><?php echo $isCandidateSession ? 'candidate/dashboard.php' : 'index.php'; ?>">
                    <span class="mobile-drawer-logo"><img src="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>assets/images/logo.webp" alt=""></span>
                    <span class="mobile-drawer-brand-copy"><strong>Career Grow Infotech</strong><small><?php echo $isCandidateSession ? 'Candidate Portal' : 'Career Portal'; ?></small></span>
                </a>
                <button class="mobile-nav-close" type="button" aria-label="Close navigation menu">
                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                </button>
            </div>
            <ul class="navbar-nav mx-auto align-items-lg-center">
                <?php foreach ($navItems as $item): ?>
                    <?php $isActive = ($currentPage === $item['file']); ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $isActive ? 'active' : ''; ?>" href="<?php echo htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8'); ?>" <?php echo $isActive ? 'aria-current="page"' : ''; ?>>
                            <i class="bi <?php echo htmlspecialchars($item['icon'] ?? 'bi-circle', ENT_QUOTES, 'UTF-8'); ?>" aria-hidden="true"></i>
                            <?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <style>
                .nav-notifications {
                    position: relative;
                }
                .notification-btn {
                    position: relative;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    width: 40px;
                    height: 40px;
                    border-radius: 50%;
                    border: 1px solid rgba(15, 23, 42, 0.08);
                    background: rgba(255, 255, 255, 0.8);
                    color: var(--cg-accent);
                    padding: 0;
                    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
                    transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
                }
                .notification-btn:hover,
                .notification-btn:focus,
                .notification-btn:active {
                    background: rgba(13, 110, 253, 0.08);
                    border-color: rgba(13, 110, 253, 0.18);
                    color: var(--cg-primary);
                    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.08);
                }
                .notification-btn .bi-bell {
                    font-size: 1.08rem;
                    line-height: 1;
                }
                .notification-btn::after {
                    display: none !important;
                }
                .notification-badge {
                    position: absolute;
                    top: -4px;
                    right: -3px;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    min-width: 17px;
                    height: 17px;
                    padding: 0 4px;
                    border-radius: 999px;
                    background: #ef4444;
                    color: #ffffff;
                    font-size: 0.64rem;
                    font-weight: 700;
                    line-height: 1;
                    box-shadow: 0 0 0 2px rgba(255,255,255,0.9);
                }
                .notification-dropdown-menu {
                    width: min(360px, calc(100vw - 1rem));
                    max-width: calc(100vw - 1rem);
                    max-height: min(420px, 72vh);
                    overflow: auto;
                    overflow-x: hidden;
                    padding: 0.35rem;
                    margin-top: 0.55rem;
                    border-radius: 14px;
                    background: rgba(255,255,255,0.98);
                    border: 1px solid rgba(148,163,184,0.22);
                    box-shadow: 0 18px 36px rgba(15,23,42,0.14);
                    right: 0;
                    left: auto;
                    transform-origin: top right;
                    animation: notificationDropdownSlide 0.18s ease;
                    box-sizing: border-box;
                }
                @keyframes notificationDropdownSlide {
                    from {
                        opacity: 0;
                        transform: translateY(-4px);
                    }
                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }
                .notification-header {
                    padding: 0.8rem 0.8rem 0.6rem;
                    border-bottom: 1px solid rgba(148,163,184,0.18);
                    margin-bottom: 0.25rem;
                    color: var(--cg-text);
                    font-size: 0.8rem;
                    letter-spacing: 0.02em;
                }
                .notification-mark-all-read {
                    font-size: 0.72rem;
                    color: var(--cg-primary);
                    text-decoration: none;
                    padding: 0;
                    border: 0;
                    background: transparent;
                }
                .notification-mark-all-read:hover,
                .notification-mark-all-read:focus {
                    color: var(--cg-primary-dark);
                    text-decoration: none;
                }
                .notification-item {
                    display: block;
                    width: 100%;
                    max-width: 100%;
                    min-width: 0;
                    box-sizing: border-box;
                    padding: 0.8rem 0.75rem;
                    border-radius: 10px;
                    color: var(--cg-text);
                    text-decoration: none;
                    border: 1px solid transparent;
                    white-space: normal !important;
                    overflow-wrap: anywhere;
                    word-break: break-word;
                    transition: background-color 0.2s ease, border-color 0.2s ease, opacity 0.2s ease;
                }
                .notification-item:hover,
                .notification-item:focus {
                    background: rgba(239,246,255,0.8);
                    border-color: rgba(96,165,250,0.18);
                }
                .notification-item.read {
                    opacity: 0.82;
                }
                .notification-item.unread {
                    background: rgba(239,246,255,0.7);
                }
                .notification-item-header {
                    display: flex;
                    align-items: flex-start;
                    justify-content: space-between;
                    gap: 0.5rem;
                    min-width: 0;
                    margin-bottom: 0.3rem;
                }
                .notification-title {
                    display: block;
                    flex: 1 1 auto;
                    min-width: 0;
                    max-width: 100%;
                    font-size: 0.82rem;
                    font-weight: 700;
                    color: inherit;
                    line-height: 1.4;
                    overflow-wrap: anywhere;
                    word-break: break-word;
                }
                .notification-message {
                    font-size: 0.76rem;
                    line-height: 1.5;
                    color: inherit;
                    margin-bottom: 0.35rem;
                    white-space: normal;
                    overflow-wrap: anywhere;
                    word-break: break-word;
                    max-width: 100%;
                }
                .notification-time {
                    display: block;
                    font-size: 0.7rem;
                    color: var(--cg-muted);
                    line-height: 1.4;
                    white-space: normal;
                    overflow-wrap: anywhere;
                    word-break: break-word;
                }
                .notification-dot {
                    display: inline-block;
                    width: 8px;
                    height: 8px;
                    border-radius: 50%;
                    background: var(--cg-primary);
                    flex: 0 0 auto;
                    margin-top: 0.2rem;
                    transform: translateY(2px);
                }
                .notification-empty {
                    padding: 0.9rem 0.8rem;
                    color: var(--cg-muted);
                    font-size: 0.82rem;
                }
                @media (max-width: 991.98px) {
                    .notification-btn {
                        width: 38px;
                        height: 38px;
                    }
                    .notification-dropdown-menu {
                        left: 50% !important;
                        right: auto !important;
                        transform: translateX(-50%);
                        width: min(360px, calc(100vw - 0.9rem));
                        max-width: calc(100vw - 0.9rem);
                    }
                }
                @media (max-width: 575.98px) {
                    .notification-dropdown-menu {
                        width: min(320px, calc(100vw - 0.75rem));
                        max-width: calc(100vw - 0.75rem);
                    }
                    .notification-item {
                        padding: 0.7rem 0.7rem;
                    }
                    .notification-message {
                        font-size: 0.75rem;
                    }
                    .notification-item-header {
                        gap: 0.35rem;
                    }
                }
                @media (prefers-reduced-motion: reduce) {
                    .notification-btn,
                    .notification-item,
                    .notification-dropdown-menu {
                        transition: none;
                        animation: none;
                    }
                }
                /* Profile dropdown styles */
                .nav-profile { position: relative; }
                .nav-profile .avatar { width:40px;height:40px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.06);color:var(--cg-primary);font-size:1.25rem }
                .nav-profile .profile-btn{display:inline-flex;align-items:center;gap:8px;padding:6px 10px;border-radius:999px;border:1px solid rgba(0,0,0,0.06);background:#fff}
                .nav-profile .profile-name{font-weight:600}
                .nav-profile .dropdown-menu {
                    min-width: 220px;
                    padding: 5px;
                    border-radius: 13px;
                    background: rgba(220,235,239,0.9);
                    border: 1px solid rgba(255,255,255,0.72);
                    box-shadow: 0 14px 30px rgba(15,23,42,0.14);
                    backdrop-filter: blur(12px);
                    -webkit-backdrop-filter: blur(12px);
                }
                .nav-profile .dropdown-menu > li:first-child {
                    margin: -5px -5px 4px;
                    border-radius: 13px 13px 8px 8px;
                    background: rgba(239,246,255,0.7);
                }
                .nav-profile .dropdown-menu .avatar {
                    border-radius: 10px;
                    background: #dbeafe;
                    color: #2563eb;
                }
                .nav-profile .dropdown-divider { margin: 3px 6px 4px; border-color: #cbd5e1; opacity: 0.65; }
                .nav-profile .dropdown-item {
                    border-radius: 8px;
                    color: #1f2937;
                    font-weight: 600;
                }
                .nav-profile .dropdown-item .bi { width: 1.25rem; color: #64748b; }
                .nav-profile .dropdown-item:hover,
                .nav-profile .dropdown-item:focus { background: #eef5ff; color: #1d4ed8; }
                .nav-profile .dropdown-item:hover .bi,
                .nav-profile .dropdown-item:focus .bi { color: #2563eb; }
                .nav-profile .dropdown-item.text-danger .bi { color: #dc2626; }
                .nav-profile .dropdown-item.text-danger:hover,
                .nav-profile .dropdown-item.text-danger:focus { background: #fff1f2; }
                .nav-profile .dropdown-item.text-danger:hover .bi,
                .nav-profile .dropdown-item.text-danger:focus .bi { color: #b91c1c; }

                html[data-theme="dark"] .notification-btn {
                    background: rgba(15,23,42,0.78);
                    border-color: rgba(148,163,184,0.2);
                    color: #e5e7eb;
                    box-shadow: inset 0 0 0 1px rgba(148,163,184,0.08);
                }
                html[data-theme="dark"] .notification-btn:hover,
                html[data-theme="dark"] .notification-btn:focus,
                html[data-theme="dark"] .notification-btn:active {
                    background: rgba(59,130,246,0.12);
                    border-color: rgba(96,165,250,0.38);
                    color: #dbeafe;
                }
                html[data-theme="dark"] .notification-dropdown-menu {
                    background: rgba(15,23,42,0.98);
                    border-color: rgba(148,163,184,0.2);
                    box-shadow: 0 18px 34px rgba(2, 6, 23, 0.42);
                }
                html[data-theme="dark"] .notification-header,
                html[data-theme="dark"] .notification-item,
                html[data-theme="dark"] .notification-empty {
                    color: #e5e7eb;
                }
                html[data-theme="dark"] .notification-item:hover,
                html[data-theme="dark"] .notification-item:focus {
                    background: rgba(59,130,246,0.12);
                }
                html[data-theme="dark"] .notification-item.unread {
                    background: rgba(59,130,246,0.12);
                }
                html[data-theme="dark"] .notification-item .notification-time {
                    color: #a8b5c8;
                }
                html[data-theme="dark"] .notification-mark-all-read {
                    color: #93c5fd;
                }
                html[data-theme="dark"] .nav-profile .profile-btn {
                    background: rgba(59,130,246,0.12);
                    border-color: rgba(148,163,184,0.22);
                    color: #e5e7eb;
                }
                html[data-theme="dark"] .nav-profile .profile-name,
                html[data-theme="dark"] .nav-profile .dropdown-item,
                html[data-theme="dark"] .nav-profile .dropdown-menu .fw-bold,
                html[data-theme="dark"] .nav-profile .text-muted.small {
                    color: #e5e7eb;
                }
                html[data-theme="dark"] .nav-profile .dropdown-menu {
                    background: rgba(17,24,39,0.96);
                    border-color: rgba(148,163,184,0.2);
                    box-shadow: 0 18px 34px rgba(2, 6, 23, 0.38);
                }
                html[data-theme="dark"] .nav-profile .dropdown-menu > li:first-child {
                    background: rgba(30,41,59,0.9);
                }
                html[data-theme="dark"] .nav-profile .dropdown-menu .avatar {
                    background: rgba(96,165,250,0.14);
                    color: #8ec5ff;
                }
                html[data-theme="dark"] .nav-profile .dropdown-divider { border-color: rgba(148,163,184,0.2); }
                html[data-theme="dark"] .nav-profile .dropdown-item .bi { color: #a5b4fc; }
                html[data-theme="dark"] .nav-profile .dropdown-item:hover,
                html[data-theme="dark"] .nav-profile .dropdown-item:focus {
                    background: rgba(59,130,246,0.12);
                    color: #dbeafe;
                }
                html[data-theme="dark"] .nav-profile .dropdown-item:hover .bi,
                html[data-theme="dark"] .nav-profile .dropdown-item:focus .bi { color: #dbeafe; }
                html[data-theme="dark"] .nav-profile .dropdown-item.text-danger {
                    color: #fca5a5;
                }
                html[data-theme="dark"] .nav-profile .dropdown-item.text-danger:hover,
                html[data-theme="dark"] .nav-profile .dropdown-item.text-danger:focus {
                    background: rgba(239,68,68,0.12);
                    color: #fecaca;
                }
                html[data-theme="dark"] .nav-profile .dropdown-item.text-danger .bi,
                html[data-theme="dark"] .nav-profile .dropdown-item.text-danger:hover .bi,
                html[data-theme="dark"] .nav-profile .dropdown-item.text-danger:focus .bi {
                    color: #fca5a5;
                }
            </style>

            <div class="navbar-actions d-flex align-items-center gap-2 mt-3 mt-lg-0">
                <!-- Public theme toggle (desktop) -->
                <button id="publicThemeToggle" class="btn theme-toggle btn-sm d-inline-flex align-items-center justify-content-center" type="button" aria-label="Toggle site theme" title="Toggle site theme">
                    <i class="bi bi-moon-fill" aria-hidden="true"></i>
                </button>
                <?php
                $userRole = $_SESSION['user_role'] ?? '';
                $isCandidate = !empty($_SESSION['user_id']) && $userRole === 'candidate';
                $isAdmin = !empty($_SESSION['user_id']) && $userRole === 'admin';

                if ($isCandidate):
                    $userName = trim((string)($_SESSION['user_name'] ?? $_SESSION['user_email'] ?? ''));
                ?>
                    <div class="nav-notifications dropdown">
                        <button type="button" class="btn notification-btn" id="candidateNotificationsMenu" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications" title="Notifications">
                            <i class="bi bi-bell" aria-hidden="true"></i>
                            <?php if ($candidateNotificationCount > 0): ?>
                                <span class="notification-badge" id="candidateNotificationBadge"><?php echo (int)$candidateNotificationCount; ?></span>
                            <?php else: ?>
                                <span class="notification-badge d-none" id="candidateNotificationBadge">0</span>
                            <?php endif; ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end notification-dropdown-menu" aria-labelledby="candidateNotificationsMenu">
                            <li class="notification-header d-flex justify-content-between align-items-center">
                                <span class="fw-semibold">Recent Notifications</span>
                                <?php if ($candidateNotificationCount > 0): ?>
                                    <button type="button" class="btn btn-link btn-sm p-0 notification-mark-all-read" data-mark-all-read="1">Mark all as read</button>
                                <?php endif; ?>
                            </li>
                            <?php if (!empty($candidateNotifications)): ?>
                                <?php foreach ($candidateNotifications as $notification): ?>
                                    <?php
                                    $notificationUrl = cg_candidate_notification_target_url(
                                        !empty($notification['application_id']) ? (int)$notification['application_id'] : null,
                                        !empty($notification['job_id']) ? (int)$notification['job_id'] : null,
                                        $basePath
                                    );
                                    $notificationRead = !empty($notification['is_read']);
                                    $notificationDate = trim((string)($notification['created_at'] ?? ''));
                                    ?>
                                    <li>
                                        <a class="dropdown-item notification-item <?php echo $notificationRead ? 'read' : 'unread'; ?>" href="<?php echo htmlspecialchars($notificationUrl, ENT_QUOTES, 'UTF-8'); ?>" data-notification-id="<?php echo (int)($notification['id'] ?? 0); ?>" data-read="<?php echo $notificationRead ? '1' : '0'; ?>">
                                            <div class="notification-item-header d-flex justify-content-between align-items-start gap-2">
                                                <span class="notification-title"><?php echo htmlspecialchars((string)($notification['title'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                                                <?php if (!$notificationRead): ?>
                                                    <span class="notification-dot" aria-label="Unread notification"></span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="notification-message"><?php echo htmlspecialchars((string)($notification['message'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                                            <div class="notification-time"><?php echo htmlspecialchars($notificationDate !== '' ? date('M j, Y g:i A', strtotime($notificationDate)) : 'Just now', ENT_QUOTES, 'UTF-8'); ?></div>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li><div class="notification-empty">No notifications yet.</div></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <div class="nav-profile dropdown">
                        <button class="btn profile-btn dropdown-toggle" id="accountMenu" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="avatar"><i class="bi bi-person-circle" aria-hidden="true"></i></span>
                            <span class="d-none d-sm-inline profile-name"><?php echo htmlspecialchars($userName, ENT_QUOTES, 'UTF-8'); ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="accountMenu">
                            <li class="px-3 py-2">
                                <div class="d-flex align-items-center">
                                    <div class="avatar me-2"><i class="bi bi-person-circle"></i></div>
                                    <div>
                                        <div class="fw-bold"><?php echo htmlspecialchars($userName, ENT_QUOTES, 'UTF-8'); ?></div>
                                        <div class="text-muted small">Candidate</div>
                                    </div>
                                </div>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>candidate/dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                            <li><a class="dropdown-item" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>candidate/profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>candidate/applications.php"><i class="bi bi-list-check me-2"></i>My Applications</a></li>
                            <li><a class="dropdown-item" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>candidate/change-password.php"><i class="bi bi-lock me-2"></i>Change Password</a></li>
                            <li><a class="dropdown-item text-danger" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>login.php" class="btn btn-primary btn-sm" data-mobile-header-action aria-label="Login to Career Grow Infotech">
                        <i class="bi bi-box-arrow-in-right" aria-hidden="true"></i>
                        <span class="d-none d-sm-inline ms-1">Login</span>
                    </a>
                <?php endif; ?>

                <?php if (!$isCandidate): // Do not show Admin link to candidates; visible to guests and admins ?>
                    <a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>admin/login.php" class="btn btn-primary btn-sm" data-mobile-header-action aria-label="Admin login">
                        <i class="bi bi-shield-lock" aria-hidden="true"></i>
                        <span class="d-none d-lg-inline ms-1">Admin</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="mobile-nav-backdrop" id="mobileNavBackdrop" aria-hidden="true"></div>
</nav>

<script>
    (function () {
        const nav = document.getElementById('mainNavbar');
        const toggle = document.querySelector('.navbar-toggler');
        const backdrop = document.getElementById('mobileNavBackdrop');
        const mobileActions = document.getElementById('mobileHeaderActions');
        const closeButton = nav ? nav.querySelector('.mobile-nav-close') : null;
        const profile = nav ? nav.querySelector('.nav-profile') : null;
        const headerActionLinks = nav ? nav.querySelectorAll('[data-mobile-header-action]') : [];

        const notificationControl = document.querySelector('.nav-notifications');

        function moveProfileControl() {
            if (!nav || !mobileActions) return;

            const isMobile = window.innerWidth < 992;
            const actions = nav.querySelector('.navbar-actions');
            headerActionLinks.forEach(function (action) {
                if (isMobile && !mobileActions.contains(action)) {
                    mobileActions.appendChild(action);
                } else if (!isMobile && actions && !actions.contains(action)) {
                    actions.appendChild(action);
                }
            });
            if (notificationControl && isMobile) {
                if (!mobileActions.contains(notificationControl)) {
                    mobileActions.appendChild(notificationControl);
                }
            } else if (notificationControl && actions) {
                if (!actions.contains(notificationControl)) {
                    actions.insertBefore(notificationControl, actions.firstChild);
                }
            }
            if (profile && isMobile) {
                if (!mobileActions.contains(profile)) {
                    mobileActions.appendChild(profile);
                }
            } else if (profile) {
                if (actions && !nav.contains(profile)) {
                    actions.insertBefore(profile, actions.firstChild);
                }
            }
        }

        if (mobileActions) {
            moveProfileControl();
            window.addEventListener('resize', moveProfileControl);
        }

        const notificationBadge = document.getElementById('candidateNotificationBadge');
        const markAllReadButton = document.querySelector('[data-mark-all-read]');

        function updateNotificationBadge(count) {
            const unreadCount = Number(count) || 0;
            if (!notificationBadge) return;
            if (unreadCount > 0) {
                notificationBadge.textContent = String(unreadCount);
                notificationBadge.classList.remove('d-none');
            } else {
                notificationBadge.textContent = '0';
                notificationBadge.classList.add('d-none');
            }
        }

        async function markNotificationRead(notificationId, item) {
            if (!notificationId || !item || item.dataset.read === '1') {
                return;
            }

            item.dataset.read = '1';
            item.classList.remove('unread');
            item.classList.add('read');
            const dot = item.querySelector('.notification-dot');
            if (dot) dot.remove();

            try {
                const response = await fetch('<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>includes/notifications.php?action=mark_read&notification_id=' + encodeURIComponent(String(notificationId)), {
                    method: 'GET',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!response.ok) {
                    item.dataset.read = '0';
                    item.classList.remove('read');
                    item.classList.add('unread');
                    return;
                }
                const data = await response.json();
                updateNotificationBadge(data.unread_count ?? 0);
                if (Number(data.unread_count ?? 0) <= 0 && markAllReadButton) {
                    markAllReadButton.remove();
                }
            } catch (error) {
                item.dataset.read = '0';
                item.classList.remove('read');
                item.classList.add('unread');
            }
        }

        document.querySelectorAll('.notification-item').forEach(function (item) {
            item.addEventListener('click', function (event) {
                const itemId = Number(this.dataset.notificationId || 0);
                if (!itemId || this.dataset.read === '1') {
                    return;
                }

                event.preventDefault();
                const targetUrl = this.getAttribute('href');
                markNotificationRead(itemId, this).finally(function () {
                    window.location.href = targetUrl;
                });
            });
        });

        if (markAllReadButton) {
            markAllReadButton.addEventListener('click', async function (event) {
                event.preventDefault();
                event.stopPropagation();
                try {
                    const response = await fetch('<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>includes/notifications.php?action=mark_all_read', {
                        method: 'GET',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    if (!response.ok) {
                        return;
                    }
                    const data = await response.json();
                    document.querySelectorAll('.notification-item.unread').forEach(function (item) {
                        item.dataset.read = '1';
                        item.classList.remove('unread');
                        item.classList.add('read');
                        const dot = item.querySelector('.notification-dot');
                        if (dot) dot.remove();
                    });
                    updateNotificationBadge(data.unread_count ?? 0);
                    markAllReadButton.remove();
                } catch (error) {
                    // no-op
                }
            });
        }

        if (toggle && nav && backdrop) {
            const closeDrawer = () => {
                const collapse = bootstrap.Collapse.getInstance(nav);
                if (collapse && nav.classList.contains('show')) {
                    collapse.hide();
                }
                document.body.classList.remove('mobile-nav-open');
            };

            toggle.addEventListener('click', function () {
                if (window.innerWidth >= 992) return;
                const collapse = bootstrap.Collapse.getInstance(nav) || new bootstrap.Collapse(nav, { toggle: false });
                if (nav.classList.contains('show')) {
                    collapse.hide();
                } else {
                    collapse.show();
                }
            });

            nav.addEventListener('show.bs.collapse', function () {
                if (window.innerWidth < 992) {
                    document.body.classList.add('mobile-nav-open');
                }
            });

            nav.addEventListener('hide.bs.collapse', function () {
                if (window.innerWidth < 992) {
                    document.body.classList.remove('mobile-nav-open');
                }
            });

            backdrop.addEventListener('click', closeDrawer);
            if (closeButton) closeButton.addEventListener('click', closeDrawer);
        }
    })();
</script>
