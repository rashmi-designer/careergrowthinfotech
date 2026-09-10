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
        .admin-footer { background: #ffffff; border-top: 0; padding: 0; }
        .admin-footer .top-accent { height: 2px; background: var(--cg-primary, #0d6efd); width:100%; display:block }
        .admin-footer .footer-inner { padding: 10px 16px; }
        .admin-footer .footer-inner .container { max-width: calc(100% - 32px) }
        .admin-footer .left-block { display:flex; gap:10px; align-items:center; }
        .admin-footer .left-block img { width:40px;height:auto; display:block }
        .admin-footer .left-block .copyright { line-height:1.05; }
        .admin-footer .copyright .main { font-size:0.95rem; font-weight:600; color:var(--cg-text, #0f172a); }
        .admin-footer .copyright .small-muted { color:var(--cg-muted); font-size:0.85rem }
        .admin-footer .right-block { text-align:right }
        .admin-footer .right-block .label { font-size:0.72rem; letter-spacing:0.08em; text-transform:uppercase; color:var(--cg-muted); margin-bottom:2px }
        .admin-footer .right-block .dev-link { font-weight:600; color:var(--cg-primary); text-decoration:none }
        .admin-footer .right-block .dev-link:hover{ text-decoration:underline }
        @media (max-width: 767.98px) {
            .admin-footer .footer-inner{display:flex;flex-direction:column;gap:8px;align-items:flex-start}
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

<a class="whatsapp-float" href="<?php echo htmlspecialchars($whatsappHref, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" aria-label="Contact us on WhatsApp" title="Contact us on WhatsApp">
    <span class="whatsapp-float-icon"><i class="bi bi-whatsapp" aria-hidden="true"></i></span>
</a>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>assets/js/main.js"></script>
</body>
</html>
