<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';

$pageTitle = 'Contact Us - Career Grow Infotech';

$errors = [];
$successMessage = '';
$formValues = [
    'name' => '',
    'email' => '',
    'phone' => '',
    'subject' => '',
    'message' => '',
];

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['contact_form_token'])) {
    $_SESSION['contact_form_token'] = bin2hex(random_bytes(16));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = (string)($_POST['contact_form_token'] ?? '');

    if (!hash_equals($_SESSION['contact_form_token'] ?? '', $token)) {
        $errors[] = 'Invalid form submission.';
    }

    foreach ($formValues as $key => $value) {
        $formValues[$key] = trim((string)($_POST[$key] ?? ''));
    }

    if ($formValues['name'] === '') {
        $errors[] = 'Full name is required.';
    }

    if ($formValues['email'] === '') {
        $errors[] = 'Email address is required.';
    } elseif (!filter_var($formValues['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if ($formValues['phone'] !== '' && !preg_match('/^[0-9+()\-\s]{7,20}$/', $formValues['phone'])) {
        $errors[] = 'Please enter a valid phone number.';
    }

    if ($formValues['subject'] === '') {
        $errors[] = 'Subject is required.';
    }

    if ($formValues['message'] === '') {
        $errors[] = 'Message is required.';
    } elseif (mb_strlen($formValues['message']) < 10) {
        $errors[] = 'Message should be at least 10 characters long.';
    }

    if (empty($errors)) {
        try {
            $connection = getDbConnection();
            $stmt = $connection->prepare('INSERT INTO contact_messages (name, email, phone, subject, message, is_read) VALUES (?, ?, ?, ?, ?, 0)');

            if ($stmt === false) {
                throw new RuntimeException('Unable to prepare contact message submission.');
            }

            $phone = $formValues['phone'] !== '' ? $formValues['phone'] : '';
            $stmt->bind_param('sssss', $formValues['name'], $formValues['email'], $phone, $formValues['subject'], $formValues['message']);

            if (!$stmt->execute()) {
                throw new RuntimeException('Failed to save your message. Please try again.');
            }

            $stmt->close();
            $connection->close();

            $successMessage = 'Your message has been sent successfully. Our team will get back to you soon.';
            $formValues = ['name' => '', 'email' => '', 'phone' => '', 'subject' => '', 'message' => ''];
            unset($_SESSION['contact_form_token']);
            $_SESSION['contact_form_token'] = bin2hex(random_bytes(16));
        } catch (Throwable $e) {
            $errors[] = $e->getMessage();
        }
    }
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<style>
    .contact-hero {
        overflow: hidden;
        background:
            radial-gradient(circle at 82% 38%, rgba(255, 255, 255, 0.42), transparent 25%),
            radial-gradient(circle at 100% 0%, rgba(13, 110, 253, 0.24), transparent 36%),
            linear-gradient(118deg, #f9fbff 0%, #edf4ff 36%, #c9e0ff 70%, #9bc7ff 100%);
        border: 1px solid rgba(13, 110, 253, 0.16);
        border-radius: 1.5rem;
        box-shadow: 0 18px 42px rgba(13, 110, 253, 0.08);
    }

    .contact-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.55rem 0.9rem;
        border-radius: 999px;
        background: rgba(13, 110, 253, 0.08);
        color: var(--cg-primary);
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }

    .contact-hero h1 { color: #102b50; line-height: 1.12; }
    .contact-hero-copy > p { max-width: 55ch; color: #4a607a !important; line-height: 1.65; }
    .contact-hero-card {
        padding: clamp(1.25rem, 2.5vw, 1.75rem);
        background: rgba(255, 255, 255, 0.9);
        border: 1px solid rgba(13, 110, 253, 0.1);
        border-radius: 1.1rem;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
    }
    .contact-hero-card h3 { color: #18375e; font-size: 1.25rem; font-weight: 700; }
    .contact-hero-card-icon {
        width: 52px;
        height: 52px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 1rem;
        background: linear-gradient(145deg, rgba(18,103,232,.08), rgba(18,103,232,.18));
        color: #1267e8;
        font-size: 1.35rem;
    }
    .contact-hero-points { display: grid; gap: .7rem; margin: 1.25rem 0 0; padding: 0; list-style: none; }
    .contact-hero-points li { display: flex; align-items: center; gap: .65rem; color: #4b6685; font-size: .92rem; }
    .contact-hero-points i { color: #1267e8; font-size: 1rem; }
    .contact-hero-points li:nth-child(2) i { color: #e56f16; }
    .contact-hero-points li:nth-child(3) i { color: #7456d9; }

    .contact-card,
    .contact-form-card,
    .reason-card,
    .faq-item {
        background: var(--cg-white);
        border: 1px solid var(--cg-border);
        border-radius: 1rem;
        box-shadow: var(--cg-shadow-soft);
    }

    .contact-card {
        padding: 1.5rem;
        height: 100%;
        transition: transform var(--cg-transition), box-shadow var(--cg-transition);
    }

    .contact-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--cg-shadow);
    }

    .contact-icon {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(13, 110, 253, 0.08);
        color: var(--cg-primary);
        font-size: 1.4rem;
        margin-bottom: 1rem;
    }

    .contact-info-grid > div:nth-child(1) .contact-icon {
        background: linear-gradient(145deg, rgba(18,103,232,.08), rgba(18,103,232,.18));
        color: #1267e8;
    }
    .contact-info-grid > div:nth-child(2) .contact-icon {
        background: linear-gradient(145deg, rgba(229,111,22,.08), rgba(229,111,22,.18));
        color: #e56f16;
    }
    .contact-info-grid > div:nth-child(3) .contact-icon {
        background: linear-gradient(145deg, rgba(116,86,217,.08), rgba(116,86,217,.18));
        color: #7456d9;
    }

    .form-shell {
        padding: 2rem;
        border: 1px solid var(--cg-border);
        border-radius: 1.2rem;
        background: var(--cg-white);
        box-shadow: var(--cg-shadow-soft);
    }

    .reason-card {
        padding: 1.5rem;
        height: 100%;
        overflow: hidden;
    }

    .reason-visual {
        position: relative;
        height: 145px;
        margin: -0.25rem -0.25rem 1.25rem;
        overflow: hidden;
        border-radius: 0.8rem;
        background: var(--cg-light);
    }

    .reason-visual img {
        width: 100%;
        height: 100%;
        display: block;
        object-fit: cover;
        object-position: center;
    }

    .reason-visual::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg, rgba(8, 24, 42, 0.68), rgba(8, 24, 42, 0.08));
        pointer-events: none;
    }

    .reason-visual-label {
        position: absolute;
        right: 1rem;
        bottom: 0.85rem;
        left: 1rem;
        z-index: 1;
        color: var(--cg-white);
        font-size: 0.9rem;
        font-weight: 700;
    }

    .reason-next {
        display: grid;
        gap: 0.7rem;
        margin-top: 1.4rem;
        padding-top: 1.15rem;
        border-top: 1px solid var(--cg-border);
    }

    .reason-next-item {
        display: flex;
        gap: 0.7rem;
        align-items: flex-start;
    }

    .reason-next-item i {
        flex: 0 0 auto;
        color: var(--cg-primary);
        margin-top: 0.15rem;
    }

    .reason-list {
        list-style: none;
        padding-left: 0;
        margin: 0;
        display: grid;
        gap: 0.9rem;
    }

    .reason-list li {
        display: flex;
        gap: 0.75rem;
        align-items: flex-start;
        color: var(--cg-text);
    }

    .reason-list li i {
        color: var(--cg-primary);
        font-size: 1.1rem;
        margin-top: 0.2rem;
    }

    .faq-item {
        position: relative;
        overflow: hidden;
        padding: 1.35rem;
        height: 100%;
        width: 100%;
        border-color: rgba(var(--faq-rgb), .18);
        background: linear-gradient(145deg, rgba(var(--faq-rgb), .08), #ffffff 58%);
        transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
    }

    .faq-grid > [class*="col-"] {
        --faq-accent: #1267e8;
        --faq-rgb: 18, 103, 232;
        display: flex;
    }

    .faq-grid > [class*="col-"]:nth-child(2) { --faq-accent: #e56f16; --faq-rgb: 229, 111, 22; }
    .faq-grid > [class*="col-"]:nth-child(3) { --faq-accent: #7456d9; --faq-rgb: 116, 86, 217; }
    .faq-grid > [class*="col-"]:nth-child(4) { --faq-accent: #078b68; --faq-rgb: 7, 139, 104; }
    .faq-item:hover { transform: translateY(-4px); border-color: rgba(var(--faq-rgb), .4); box-shadow: 0 18px 34px rgba(var(--faq-rgb), .12); }
    .faq-question { display: flex; align-items: center; gap: .75rem; margin-bottom: .8rem; }
    .faq-question h6 { margin: 0; color: #193457; font-size: 1.05rem; line-height: 1.35; }
    .faq-marker {
        width: 40px;
        height: 40px;
        display: inline-flex;
        flex: 0 0 auto;
        align-items: center;
        justify-content: center;
        border-radius: .75rem;
        background: linear-gradient(145deg, rgba(var(--faq-rgb), .1), rgba(var(--faq-rgb), .2));
        color: var(--faq-accent);
        font-size: 1.05rem;
    }
    .faq-item p { color: #58708d !important; line-height: 1.6; }

    .contact-cta {
        background: linear-gradient(112deg, #fbfdff 0%, #edf5ff 52%, #d9eaff 100%);
        border: 1px solid #c7ddfb;
        border-radius: 1.5rem;
        box-shadow: 0 18px 40px rgba(26, 82, 151, 0.12);
    }

    .contact-cta .text-white-50 { color: #466687 !important; }
    .contact-cta h2 { color: #102b50; }
    .contact-cta .btn-light {
        background: #0d6efd;
        border-color: #0d6efd;
        color: #fff;
        transition: background-color .2s ease, border-color .2s ease, color .2s ease, box-shadow .2s ease, transform .2s ease;
    }
    .contact-cta .btn-light:hover,
    .contact-cta .btn-light:focus {
        background: #0a58ca;
        border-color: #0a58ca;
        color: #fff;
        box-shadow: 0 10px 20px rgba(10, 88, 202, .24);
        transform: translateY(-2px);
    }
    .contact-cta .btn-outline-light { border-color: #0d6efd; color: #0a58ca; }
    .contact-cta .btn-outline-light:hover,
    .contact-cta .btn-outline-light:focus { background: #0d6efd; border-color: #0d6efd; color: #fff; }

    /* Ensure outline-style CTA on Contact page has #0d6efd background by default
       Only changing the background-color as requested; text and border colors left unchanged. */
    .contact-cta .btn-outline-light {
        background-color: #0d6efd !important;
    }

    .contact-cta .text-white-50 { color: #466687 !important; }
    .contact-cta h2 { color: #102b50; }
    .contact-cta .btn-light {
        background: #0d6efd;
        border-color: #0d6efd;
        color: #fff;
        transition: background-color .2s ease, border-color .2s ease, color .2s ease, box-shadow .2s ease, transform .2s ease;
    }
    .contact-cta .btn-light:hover,
    .contact-cta .btn-light:focus {
        background: #0a58ca;
        border-color: #0a58ca;
        color: #fff;
        box-shadow: 0 10px 20px rgba(10, 88, 202, .24);
        transform: translateY(-2px);
    }
    .contact-cta .btn-outline-light { border-color: #0d6efd; color: #0a58ca; }
    .contact-cta .btn-outline-light:hover,
    .contact-cta .btn-outline-light:focus { background: #0d6efd; border-color: #0d6efd; color: #fff; }

    .breadcrumb {
        margin-bottom: 1rem;
        font-size: 0.92rem;
    }

    @media (max-width: 767.98px) {
        .form-shell {
            padding: 1.2rem;
        }
    }
</style>

<main>
    <section class="py-5">
        <div class="container">
            <div class="contact-hero p-3 p-lg-4">
                <div class="row g-4 align-items-center p-2 p-lg-4">
                    <div class="col-lg-7 contact-hero-copy">
                        <span class="contact-badge"><i class="bi bi-envelope-paper" aria-hidden="true"></i> Contact</span>
                        <h1 class="display-5 fw-bold mt-3 mb-3">Get in Touch With Us</h1>
                        <p class="text-muted fs-5 mb-0">
                            Whether you are looking for a new opportunity, need support with your application, or want to discuss recruitment requirements, Career Grow Infotech is here to help.
                        </p>
                    </div>
                    <div class="col-lg-5">
                        <div class="contact-hero-card">
                            <div class="d-flex align-items-center justify-content-between gap-3">
                                <div>
                                    <p class="text-uppercase text-primary fw-semibold small mb-1">We’re here to help</p>
                                    <h3 class="mb-0">Start the conversation</h3>
                                </div>
                                <div class="contact-hero-card-icon"><i class="bi bi-chat-dots"></i></div>
                            </div>
                            <ul class="contact-hero-points">
                                <li><i class="bi bi-check-circle-fill"></i>Explore career opportunities</li>
                                <li><i class="bi bi-check-circle-fill"></i>Get application support</li>
                                <li><i class="bi bi-check-circle-fill"></i>Discuss recruitment needs</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="pb-5">
        <div class="container">
            <div class="row g-4 contact-info-grid">
                <div class="col-md-6 col-lg-4">
                    <div class="contact-card">
                        <div class="contact-icon"><i class="bi bi-envelope-fill" aria-hidden="true"></i></div>
                        <h4 class="fw-semibold mb-2">Email</h4>
                        <p class="text-muted mb-0"><a href="mailto:info@careergrowinfotech.com" class="text-decoration-none">info@careergrowinfotech.com</a></p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="contact-card">
                        <div class="contact-icon"><i class="bi bi-telephone-fill" aria-hidden="true"></i></div>
                        <h4 class="fw-semibold mb-2">Phone</h4>
                        <p class="text-muted mb-0"><a href="tel:9850340340" class="text-decoration-none">+91 98503 40340</a></p>
                    </div>
                </div>

                <div class="col-md-12 col-lg-4">
                    <div class="contact-card">
                        <div class="contact-icon"><i class="bi bi-geo-alt-fill" aria-hidden="true"></i></div>
                        <h4 class="fw-semibold mb-2">Our locations</h4>
                        <ul class="list-unstyled text-muted mb-0">
                            <li class="mb-2">Pune</li>
                            <li class="mb-2">Chhatrapati Sambhajinagar</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 bg-white">
        <div class="container">
            <div class="row g-4 justify-content-center">
                <div class="col-lg-7">
                    <div class="form-shell">
                        <p class="text-uppercase text-primary fw-semibold small mb-2">Send us a message</p>
                        <h2 class="fw-bold mb-4">We would love to hear from you</h2>

                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php foreach ($errors as $error): ?>
                                    <div><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($successMessage !== ''): ?>
                            <div class="alert alert-success" role="alert" aria-live="polite">
                                <?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                        <?php endif; ?>

                        <form method="post" action="contact.php" id="contactForm" novalidate>
                            <input type="hidden" name="contact_form_token" value="<?php echo htmlspecialchars($_SESSION['contact_form_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            <div id="contactValidationSummary" class="alert alert-danger d-none" role="alert" aria-live="polite"></div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">Full Name</label>
                                    <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($formValues['name'], ENT_QUOTES, 'UTF-8'); ?>" minlength="2" pattern="[A-Za-z][A-Za-z .'-]{1,99}" title="Enter a name using letters, spaces, apostrophes, periods, or hyphens." required>
                                </div>

                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($formValues['email'], ENT_QUOTES, 'UTF-8'); ?>" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($formValues['phone'], ENT_QUOTES, 'UTF-8'); ?>" pattern="[0-9()+ -]{7,20}" title="Enter a valid phone number using 7 to 20 digits and standard phone characters.">
                                </div>

                                <div class="col-md-6">
                                    <label for="subject" class="form-label">Subject</label>
                                    <input type="text" id="subject" name="subject" class="form-control" value="<?php echo htmlspecialchars($formValues['subject'], ENT_QUOTES, 'UTF-8'); ?>" minlength="3" pattern=".*\S.*" title="Enter a subject with at least 3 characters." required>
                                </div>

                                <div class="col-12">
                                    <label for="message" class="form-label">Message</label>
                                    <textarea id="message" name="message" class="form-control" rows="6" minlength="10" pattern=".*\S.*" title="Enter a message with at least 10 characters." required><?php echo htmlspecialchars($formValues['message'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                                </div>

                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">Submit Message</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="reason-card h-100">
                        <div class="reason-visual">
                            <img src="assets/images/contactUs.jpg" alt="Contact and career support workspace" loading="lazy">
                            <div class="reason-visual-label">Let’s find the right next step.</div>
                        </div>
                        <p class="text-uppercase text-primary fw-semibold small mb-2">Why contact us</p>
                        <h3 class="fw-bold mb-3">We can help with</h3>
                        <p class="text-muted mb-4">Tell us what you need, and our team will guide you toward the right career or recruitment support.</p>
                        <ul class="reason-list">
                            <li><i class="bi bi-check-circle-fill" aria-hidden="true"></i><span>Job-related queries and career guidance.</span></li>
                            <li><i class="bi bi-check-circle-fill" aria-hidden="true"></i><span>Application-related questions and follow-up support.</span></li>
                            <li><i class="bi bi-check-circle-fill" aria-hidden="true"></i><span>Employer and recruitment requirements.</span></li>
                            <li><i class="bi bi-check-circle-fill" aria-hidden="true"></i><span>General website assistance and support queries.</span></li>
                        </ul>
                        <div class="reason-next">
                            <div class="small text-uppercase text-primary fw-semibold">What happens next</div>
                            <div class="reason-next-item"><i class="bi bi-chat-dots-fill" aria-hidden="true"></i><span class="small text-muted">We review your message and understand your needs.</span></div>
                            <div class="reason-next-item"><i class="bi bi-person-check-fill" aria-hidden="true"></i><span class="small text-muted">Our team responds with the most relevant next steps.</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <div class="text-center mb-4">
                <p class="text-uppercase text-primary fw-semibold small mb-2">Helpful information</p>
                <h2 class="fw-bold mb-0">Frequently Asked Questions</h2>
            </div>

            <div class="row g-3 faq-grid">
                <div class="col-lg-6">
                    <div class="faq-item">
                        <div class="faq-question"><span class="faq-marker"><i class="bi bi-send"></i></span><h6>How do I apply for a job?</h6></div>
                        <p class="text-muted mb-0">Browse the current opportunities on the jobs page and submit your application through the available role listing.</p>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="faq-item">
                        <div class="faq-question"><span class="faq-marker"><i class="bi bi-chat-dots"></i></span><h6>Can I contact the team about my application?</h6></div>
                        <p class="text-muted mb-0">Yes. Use the form above to send your application-related question or message.</p>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="faq-item">
                        <div class="faq-question"><span class="faq-marker"><i class="bi bi-people"></i></span><h6>Do you support employers and recruitment teams?</h6></div>
                        <p class="text-muted mb-0">Yes. You can contact us to discuss hiring and recruitment requirements.</p>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="faq-item">
                        <div class="faq-question"><span class="faq-marker"><i class="bi bi-question-circle"></i></span><h6>What if I need general website help?</h6></div>
                        <p class="text-muted mb-0">Use the contact form and mention the issue or assistance needed, and our team will review it.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 pb-6">
        <div class="container">
            <div class="contact-cta text-white p-4 p-lg-5">
                <div class="row align-items-center g-4">
                    <div class="col-lg-8">
                        <p class="text-uppercase fw-semibold small mb-2 text-white-50">Career opportunities</p>
                        <h2 class="fw-bold mb-2">Looking for your next opportunity?</h2>
                        <p class="text-white-50 mb-0">Explore current roles and take the next confident step in your career journey.</p>
                    </div>
                    <div class="col-lg-4 text-lg-end">
                        <div class="d-flex flex-wrap justify-content-lg-end gap-3">
                            <a href="jobs.php" class="btn btn-light">Explore Jobs</a>
                            <a href="register.php" class="btn btn-outline-light">Create Account</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<script>
(function () {
    var formShell = document.querySelector('.form-shell');
    var reasonCard = document.querySelector('.reason-card');
    var desktopQuery = window.matchMedia('(min-width: 992px)');

    if (!formShell || !reasonCard) {
        return;
    }

    function matchPanelHeight() {
        if (desktopQuery.matches) {
            reasonCard.style.setProperty('height', formShell.getBoundingClientRect().height + 'px', 'important');
        } else {
            reasonCard.style.removeProperty('height');
        }
    }

    matchPanelHeight();
    window.addEventListener('resize', matchPanelHeight);
    if (window.ResizeObserver) {
        new ResizeObserver(matchPanelHeight).observe(formShell);
    }
})();
</script>

<script>
document.getElementById('contactForm').addEventListener('submit', function (event) {
    var form = this;
    var summary = document.getElementById('contactValidationSummary');
    var fields = [
        { input: form.elements.name, label: 'Full Name', valid: function (value) { return /^[A-Za-z][A-Za-z .\'-]{1,99}$/.test(value); } },
        { input: form.elements.email, label: 'Email Address', valid: function (value) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value); } },
        { input: form.elements.phone, label: 'Phone Number', valid: function (value) { return value === '' || /^[0-9()+ -]{7,20}$/.test(value); } },
        { input: form.elements.subject, label: 'Subject', valid: function (value) { return value.length >= 3 && /\S/.test(value); } },
        { input: form.elements.message, label: 'Message', valid: function (value) { return value.length >= 10 && /\S/.test(value); } }
    ];
    var invalidFields = [];

    fields.forEach(function (field) {
        var value = field.input.value.trim();
        field.input.classList.remove('is-invalid');
        field.input.removeAttribute('aria-invalid');
        if ((field.input.required && value === '') || (value !== '' && !field.valid(value))) {
            invalidFields.push(field);
            field.input.classList.add('is-invalid');
            field.input.setAttribute('aria-invalid', 'true');
        }
    });

    if (invalidFields.length === 0) {
        summary.classList.add('d-none');
        return;
    }

    event.preventDefault();
    summary.textContent = 'Please correct: ' + invalidFields.map(function (field) {
        return field.label;
    }).join(', ') + '.';
    summary.classList.remove('d-none');
    invalidFields[0].input.focus();
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
