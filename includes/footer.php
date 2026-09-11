<?php
$basePath = $basePath ?? '';

// Allow pages to hide the public footer when rendering standalone auth pages.
if (!empty($hidePublicLayout)) {
    // close body/html if header was included but footer intentionally hidden
    echo '</body>\n</html>';
    return;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$candidateApplicationsHref = (isset($_SESSION['user_id'], $_SESSION['user_role']) && $_SESSION['user_role'] === 'candidate')
    ? $basePath . 'candidate/applications.php'
    : $basePath . 'login.php?next=' . rawurlencode('/candidate/applications.php');
$whatsappMessage = 'Hello Career Grow Infotech, I am interested in your job hiring services. Please share suitable job opportunities and application details.';
$whatsappHref = 'https://wa.me/919850340340?text=' . rawurlencode($whatsappMessage);

// If this is an authenticated admin page, render the admin-specific footer (UI-only)
$scriptPath = $_SERVER['SCRIPT_NAME'] ?? '';
if (strpos($scriptPath, '/admin/') !== false && !empty($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    ?>
    <style>
        .admin-footer { background: #eef4fa; border-top: 1px solid #dbe6f1; padding: 0; box-shadow: 0 -8px 24px rgba(31, 54, 88, .04); }
        .admin-footer .top-accent { height: 3px; background: linear-gradient(90deg, #2763d7, #0c8879); width:100%; display:block }
        .admin-footer .footer-inner { padding: 13px 16px; }
        .admin-footer .footer-inner .container { max-width: calc(100% - 32px) }
        .admin-footer .left-block { display:flex; gap:12px; align-items:center; }
        .admin-footer .left-block img { width:40px; height:40px; display:block; padding:2px; border:1px solid #cadbea; border-radius:8px; background:#ffffff; box-shadow:0 3px 8px rgba(22, 73, 127, .12); object-fit:contain; }
        .admin-footer .left-block .copyright { line-height:1.16; }
        .admin-footer .copyright > div:first-child { font-size:0.95rem; font-weight:650; color:var(--cg-text, #0f172a); }
        .admin-footer .copyright .small-muted { color:var(--cg-muted); font-size:0.82rem; margin-top:2px }
        .admin-footer .right-block { text-align:right }
        .admin-footer .right-block .label { font-size:0.7rem; letter-spacing:0.1em; text-transform:uppercase; color:#718198; margin-bottom:3px }
        .admin-footer .right-block .dev-link { font-weight:700; color:var(--cg-primary); text-decoration:none }
        .admin-footer .right-block .dev-link:hover{ color:#16497f; text-decoration:underline }
        html[data-theme="dark"] .admin-footer { background:#172235; border-top-color:#2c3a50; box-shadow:0 -8px 24px rgba(0,0,0,.16); }
        html[data-theme="dark"],
        html[data-theme="dark"] body,
        html[data-theme="dark"] .admin-root { background:#0f172a; }
        html[data-theme="dark"] .admin-root {
            --cg-white:#172235;
            --cg-text:#e7edf8;
            --cg-muted:#aab7ca;
            --cg-border:#2c3a50;
            color:#c7d2e2;
        }
        html[data-theme="dark"] .admin-root .main-panel h1,
        html[data-theme="dark"] .admin-root .main-panel h2,
        html[data-theme="dark"] .admin-root .main-panel h3,
        html[data-theme="dark"] .admin-root .main-panel h4,
        html[data-theme="dark"] .admin-root .main-panel h5,
        html[data-theme="dark"] .admin-root .main-panel h6 { color:#e7edf8; }
        html[data-theme="dark"] .admin-root .main-panel .topbar h1,
        html[data-theme="dark"] .admin-root .main-panel .admin-topbar .title-area h1 { color:#e7edf8; }
        html[data-theme="dark"] .admin-root .table {
            --bs-table-bg:#172235;
            --bs-table-color:#d5deeb;
            --bs-table-border-color:#2c3a50;
            --bs-table-striped-bg:#1c2a40;
            --bs-table-hover-bg:#1c2a40;
            --bs-table-hover-color:#f4f7fb;
        }
        html[data-theme="dark"] .admin-root .table tbody td { color:#d5deeb; }
        html[data-theme="dark"] .admin-root .form-control,
        html[data-theme="dark"] .admin-root .form-select { color:#e7edf8; background-color:#1d2939; border-color:#35445b; }
        html[data-theme="dark"] .admin-root .form-control::placeholder { color:#9aa9bd; }
        html[data-theme="dark"] .admin-root .main-panel > .header-panel h2,
        html[data-theme="dark"] .admin-root .main-panel > .page-intro-panel h2,
        html[data-theme="dark"] .admin-root .dashboard-hero h2 { color:#fff; }
        html[data-theme="dark"] .admin-footer .copyright > div:first-child { color:#e7edf8; }
        html[data-theme="dark"] .admin-footer .copyright .small-muted,
        html[data-theme="dark"] .admin-footer .right-block .label { color:#aab7ca; }
        html[data-theme="dark"] .admin-footer .right-block .dev-link { color:#7fb0ff; }
        html[data-theme="dark"] .admin-footer .right-block .dev-link:hover { color:#b8d4ff; }
        @media (max-width: 767.98px) {
            .admin-footer .footer-inner { padding:14px 16px; }
            .admin-footer .footer-inner .container > .d-flex { flex-direction:column; gap:12px; align-items:flex-start !important; }
            .admin-footer .right-block{text-align:left}
        }
    </style>

    <footer class="admin-footer">
        <div class="top-accent" aria-hidden="true"></div>
        <div class="footer-inner">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="left-block">
                        <img src="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>assets/images/logo.webp" alt="Career Grow Infotech logo" width="40" height="40" loading="lazy">
                        <div class="copyright">
                            <div>© 2026 Career Grow Infotech Pvt. Ltd.</div>
                            <div class="small-muted">All rights reserved.</div>
                        </div>
                    </div>

                    <div class="right-block">
                        <div class="label">Software Developed &amp; Managed By</div>
                        <div><a class="dev-link" href="https://kavyainfoweb.com/" target="_blank" rel="noopener noreferrer">Kavya Infoweb Pvt. Ltd.</a></div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>assets/js/main.js"></script>
</body>
</html>
    <?php
    return;
}
?>
<footer class="site-footer mt-auto">
    <div class="container py-5">
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="footer-brand mb-3">
                    <span class="brand-mark brand-mark-sm">
                        <img src="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>assets/images/logo.webp" alt="Career Grow Infotech logo" width="34" height="34" loading="lazy">
                    </span>
                    <span class="ms-2 fw-semibold">Career Grow Infotech Pvt. Ltd.</span>
                </div>
                <p class="footer-intro mb-0">
                    Career Grow Infotech helps professionals discover opportunities and businesses hire smarter with a reliable, modern recruitment experience.
                </p>
            </div>

            <div class="col-lg-2 col-md-6">
                <h6 class="footer-title">Quick Links</h6>
                <ul class="list-unstyled footer-links">
                    <li><a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>index.php">Home</a></li>
                    <li><a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>about.php">About</a></li>
                    <li><a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>services.php">Services</a></li>
                    <li><a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>jobs.php">Jobs</a></li>
                    <li><a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>contact.php">Contact</a></li>
                </ul>
            </div>

            <div class="col-lg-3 col-md-6">
                <h6 class="footer-title">For Job Seekers</h6>
                <ul class="list-unstyled footer-links">
                    <li><a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>jobs.php">Find Jobs</a></li>
                    <li><a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>login.php">Candidate Login</a></li>
                    <li><a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>register.php">Register</a></li>
                    <li><a href="<?php echo htmlspecialchars($candidateApplicationsHref, ENT_QUOTES, 'UTF-8'); ?>">My Applications</a></li>
                </ul>
            </div>

            <div class="col-lg-3 col-md-6">
                <h6 class="footer-title">Contact</h6>
                <ul class="list-unstyled footer-links">
                    <li>Pune</li>
                    <li>Chhatrapati Sambhajinagar</li>
                    <li><a href="tel:+919850340340">+91 98503 40340</a></li>
                    <li><a href="mailto:info@careergrowinfotech.com">info@careergrowinfotech.com</a></li>
                    <li><a href="<?php echo htmlspecialchars($whatsappHref, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">WhatsApp</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="footer-bottom border-top">
        <div class="container py-3 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
            <p class="mb-0 text-secondary">© <span id="currentYear"></span> Career Grow Infotech Pvt. Ltd.</p>
            <p class="mb-0 text-secondary">All rights reserved.</p>
        </div>
    </div>
</footer>

<button type="button" class="back-to-top" id="backToTop" aria-label="Back to top">
    <i class="bi bi-arrow-up"></i>
</button>

<?php if (basename($scriptPath) === 'contact.php'): ?>
    <a class="whatsapp-float" href="<?php echo htmlspecialchars($whatsappHref, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" aria-label="Contact us on WhatsApp" title="Contact us on WhatsApp">
        <span class="whatsapp-float-icon"><i class="bi bi-whatsapp" aria-hidden="true"></i></span>
    </a>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>assets/js/main.js"></script>
</body>
</html>
