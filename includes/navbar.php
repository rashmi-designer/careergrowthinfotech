<?php
$basePath = $basePath ?? '';
$currentPage = basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');

// Allow pages to hide the public header/navbar when rendering standalone auth pages.
if (!empty($hidePublicLayout)) {
    return;
}
$navItems = [
    ['label' => 'Home', 'file' => 'index.php', 'href' => $basePath . 'index.php'],
    ['label' => 'About', 'file' => 'about.php', 'href' => $basePath . 'about.php'],
    ['label' => 'Services', 'file' => 'services.php', 'href' => $basePath . 'services.php'],
    ['label' => 'Jobs', 'file' => 'jobs.php', 'href' => $basePath . 'jobs.php'],
    ['label' => 'Contact', 'file' => 'contact.php', 'href' => $basePath . 'contact.php'],
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

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav mx-auto align-items-lg-center">
                <?php foreach ($navItems as $item): ?>
                    <?php $isActive = ($currentPage === $item['file']); ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $isActive ? 'active' : ''; ?>" href="<?php echo htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8'); ?>" <?php echo $isActive ? 'aria-current="page"' : ''; ?>>
                            <?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <style>
                /* Profile dropdown styles */
                .nav-profile { position: relative; }
                .nav-profile .avatar { width:40px;height:40px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.06);color:var(--bs-body-color);font-size:1.25rem }
                .nav-profile .profile-btn{display:inline-flex;align-items:center;gap:8px;padding:6px 10px;border-radius:999px;border:1px solid rgba(0,0,0,0.06);background:#fff}
                .nav-profile .profile-name{font-weight:600}
                .nav-profile .dropdown-menu{min-width:220px;border-radius:12px}
                .nav-profile .dropdown-item .bi{width:1.25rem}
            </style>

            <div class="navbar-actions d-flex align-items-center gap-2 mt-3 mt-lg-0">
                <?php
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }

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
                            <li><a class="dropdown-item text-danger" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>login.php" class="btn btn-primary btn-sm" aria-label="Login to Career Grow Infotech">
                        <i class="bi bi-box-arrow-in-right" aria-hidden="true"></i>
                        <span class="d-none d-sm-inline ms-1">Login</span>
                    </a>
                <?php endif; ?>

                <?php if (!$isCandidate): // Do not show Admin link to candidates; visible to guests and admins ?>
                    <a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>admin/login.php" class="btn btn-outline-secondary btn-sm admin-login-btn" aria-label="Admin login">
                        <i class="bi bi-shield-lock" aria-hidden="true"></i>
                        <span class="d-none d-lg-inline ms-1">Admin</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
