<?php
$basePath = $basePath ?? '';
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
                    <li><a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>candidate/applications.php">My Applications</a></li>
                </ul>
            </div>

            <div class="col-lg-3 col-md-6">
                <h6 class="footer-title">Contact</h6>
                <ul class="list-unstyled footer-links">
                    <li>Pune</li>
                    <li>Chhatrapati Sambhajinagar</li>
                    <li><a href="tel:+919850340340">+91 98503 40340</a></li>
                    <li><a href="mailto:info@careergrowinfotech.com">info@careergrowinfotech.com</a></li>
                    <li><a href="https://wa.me/919850340340" target="_blank" rel="noopener noreferrer">WhatsApp</a></li>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>assets/js/main.js"></script>
</body>
</html>
