<?php
$basePath = $basePath ?? '';
$currentPage = basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');
$isCandidateSession = !empty($_SESSION['user_id']) && (($_SESSION['user_role'] ?? '') === 'candidate');

// Allow pages to hide the public header/navbar when rendering standalone auth pages.
if (!empty($hidePublicLayout)) {
    return;
}
$navItems = [
    ['label' => 'Home', 'file' => 'index.php', 'href' => $basePath . 'index.php', 'icon' => 'bi-house-door'],
    ['label' => 'About', 'file' => 'about.php', 'href' => $basePath . 'about.php', 'icon' => 'bi-info-circle'],
    ['label' => 'Services', 'file' => 'services.php', 'href' => $basePath . 'services.php', 'icon' => 'bi-grid'],
    ['label' => 'Jobs', 'file' => 'jobs.php', 'href' => $basePath . 'jobs.php', 'icon' => 'bi-briefcase'],
    ['label' => 'Contact', 'file' => 'contact.php', 'href' => $basePath . 'contact.php', 'icon' => 'bi-envelope'],
];
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
