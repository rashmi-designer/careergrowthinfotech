<?php
$basePath = $basePath ?? '';
$currentPage = basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');
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

            <div class="navbar-actions d-flex align-items-center gap-2 mt-3 mt-lg-0">
                <a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>login.php" class="btn btn-primary btn-sm" aria-label="Login to Career Grow Infotech">
                    <i class="bi bi-box-arrow-in-right" aria-hidden="true"></i>
                    <span class="d-none d-sm-inline ms-1">Login</span>
                </a>
                <a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>admin/login.php" class="btn btn-outline-secondary btn-sm admin-login-btn" aria-label="Admin login">
                    <i class="bi bi-shield-lock" aria-hidden="true"></i>
                    <span class="d-none d-lg-inline ms-1">Admin</span>
                </a>
            </div>
        </div>
    </div>
</nav>
