<?php
$pageTitle = 'Services - Career Grow Infotech';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<style>
    .svc-page {
        background:
            radial-gradient(circle at top left, rgba(13, 110, 253, 0.08), transparent 28%),
            linear-gradient(180deg, #f6faff 0%, #ffffff 30%, #f6faff 100%);
        overflow: hidden;
    }

    .svc-hero {
        position: relative;
        padding: 2.5rem 0 2rem;
        background: linear-gradient(135deg, rgba(13, 110, 253, 0.06), rgba(11, 31, 51, 0.02) 50%, rgba(13, 110, 253, 0.02));
        border-bottom: 1px solid rgba(13, 110, 253, 0.08);
    }

    .svc-hero::before,
    .svc-hero::after,
    .svc-cta-card::before,
    .svc-cta-card::after {
        content: "";
        position: absolute;
        pointer-events: none;
        border-radius: 50%;
        filter: blur(18px);
        opacity: 0.8;
    }

    .svc-hero::before {
        width: 220px;
        height: 220px;
        background: rgba(13, 110, 253, 0.1);
        right: 14%;
        top: -50px;
        animation: floatGlow 10s ease-in-out infinite alternate;
    }

    .svc-hero::after {
        width: 180px;
        height: 180px;
        background: rgba(79, 172, 255, 0.12);
        left: 8%;
        bottom: -30px;
        animation: floatGlow 12s ease-in-out infinite alternate-reverse;
    }

    .svc-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.42rem 0.8rem;
        border-radius: 999px;
        background: rgba(13, 110, 253, 0.08);
        border: 1px solid rgba(13, 110, 253, 0.1);
        letter-spacing: 0.12em;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--cg-primary);
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.3);
    }

    .svc-hero h1 {
        margin-top: 0.9rem;
        font-size: clamp(2.2rem, 4vw, 3.3rem);
        line-height: 1.08;
        letter-spacing: -0.05em;
        font-weight: 800;
        color: var(--cg-accent);
        max-width: 670px;
    }

    .svc-hero .lead {
        max-width: 60ch;
        color: var(--cg-muted);
        font-size: 1.03rem;
        line-height: 1.7;
        margin-top: 0.9rem;
    }

    .svc-hero .btn {
        min-width: 170px;
        padding: 0.75rem 1.15rem;
        border-radius: 0.8rem;
        font-weight: 700;
        transition: transform 0.23s ease, box-shadow 0.23s ease, border-color 0.23s ease;
    }

    .svc-hero .btn:hover,
    .svc-cta-card .btn:hover {
        transform: translateY(-2px);
    }

    .svc-hero-visual {
        position: relative;
        background: rgba(255, 255, 255, 0.88);
        border: 1px solid rgba(13, 110, 253, 0.12);
        border-radius: 1.45rem;
        box-shadow: 0 22px 48px rgba(15, 23, 42, 0.08);
        padding: 1.2rem;
        overflow: hidden;
        backdrop-filter: blur(2px);
    }

    .svc-hero-visual::before {
        content: "";
        position: absolute;
        inset: auto -18% -30% auto;
        width: 200px;
        height: 200px;
        border-radius: 50%;
        background: rgba(13, 110, 253, 0.08);
        filter: blur(12px);
    }

    .mini-panel {
        position: relative;
        z-index: 1;
        border: 1px solid rgba(13, 110, 253, 0.08);
        border-radius: 1rem;
        background: linear-gradient(180deg, rgba(255,255,255,0.9), rgba(245,247,251,0.94));
        padding: 1rem 1.05rem;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.04);
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }

    .mini-panel + .mini-panel {
        margin-top: 0.9rem;
    }

    .mini-panel:hover {
        transform: translateY(-2px);
        box-shadow: 0 16px 30px rgba(13, 110, 253, 0.08);
    }

    .svc-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.45rem 0.7rem;
        border-radius: 999px;
        background: rgba(13, 110, 253, 0.08);
        color: var(--cg-primary);
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }

    .svc-block-title {
        font-size: 1.08rem;
        font-weight: 700;
        color: var(--cg-accent);
        margin-bottom: 0.4rem;
    }

    .svc-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.8rem;
    }

    .svc-stat {
        border: 1px solid rgba(13, 110, 253, 0.1);
        border-radius: 0.85rem;
        background: linear-gradient(180deg, rgba(13,110,253,0.04), rgba(13,110,253,0.02));
        padding: 0.75rem 0.8rem;
    }

    .svc-stat strong {
        display: block;
        font-size: 1.18rem;
        color: var(--cg-primary);
        margin-bottom: 0.18rem;
    }

    .svc-section {
        position: relative;
        padding: 4rem 0;
    }

    .svc-section + .svc-section {
        border-top: 1px solid rgba(15, 23, 42, 0.05);
    }

    .svc-section.bg-white {
        background: rgba(255, 255, 255, 0.78);
    }

    .section-heading {
        margin-bottom: 1.8rem;
    }

    .section-heading h2 {
        font-size: clamp(1.9rem, 3vw, 2.45rem);
        font-weight: 800;
        line-height: 1.16;
        letter-spacing: -0.04em;
        color: var(--cg-accent);
        margin: 0;
    }

    .section-heading p {
        margin-top: 0.7rem;
        max-width: 62ch;
        color: var(--cg-muted);
        line-height: 1.7;
    }

    .svc-service-card {
        position: relative;
        border: 1px solid rgba(13, 110, 253, 0.08);
        border-radius: 1.1rem;
        background: rgba(255,255,255,0.9);
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.04);
        transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
        height: 100%;
        overflow: hidden;
    }

    .svc-service-card::before {
        content: "";
        position: absolute;
        inset: 0 auto auto 0;
        width: 100%;
        height: 3px;
        background: linear-gradient(90deg, var(--cg-primary), rgba(13, 110, 253, 0.2));
        opacity: 0;
        transition: opacity 0.25s ease;
    }

    .svc-service-card:hover {
        transform: translateY(-6px);
        border-color: rgba(13, 110, 253, 0.18);
        box-shadow: 0 20px 36px rgba(15, 23, 42, 0.08);
    }

    .svc-service-card:hover::before {
        opacity: 1;
    }

    .svc-service-card .card-body {
        display: flex;
        flex-direction: column;
        height: 100%;
        padding: 1.35rem 1.3rem 1.2rem;
    }

    .svc-icon-wrap {
        width: 52px;
        height: 52px;
        border-radius: 0.9rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, rgba(13, 110, 253, 0.12), rgba(79, 172, 255, 0.12));
        color: var(--cg-primary);
        font-size: 1.2rem;
        margin-bottom: 0.9rem;
        border: 1px solid rgba(13, 110, 253, 0.08);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .svc-service-card:hover .svc-icon-wrap {
        transform: translateY(-2px) scale(1.02);
        box-shadow: 0 12px 22px rgba(13, 110, 253, 0.12);
    }

    .svc-service-card h3 {
        margin: 0 0 0.6rem;
        font-size: 1.12rem;
        font-weight: 700;
        color: var(--cg-accent);
    }

    .svc-service-card p {
        margin: 0;
        color: var(--cg-muted);
        line-height: 1.65;
    }

    .svc-link {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        margin-top: 1rem;
        color: var(--cg-primary);
        font-weight: 700;
        text-decoration: none;
        transition: gap 0.2s ease, opacity 0.2s ease;
    }

    .svc-link:hover {
        gap: 0.6rem;
        opacity: 0.9;
    }

    .journey-panel {
        background: linear-gradient(180deg, #ffffff, #f7fbff);
        border: 1px solid rgba(13, 110, 253, 0.08);
        border-radius: 1.25rem;
        box-shadow: 0 18px 32px rgba(15, 23, 42, 0.05);
        padding: 1.4rem;
    }

    .journey-list {
        display: grid;
        gap: 0.95rem;
        margin-top: 1.3rem;
    }

    .journey-item {
        display: flex;
        align-items: flex-start;
        gap: 0.8rem;
        padding: 0.95rem 1rem;
        border-radius: 1rem;
        background: rgba(13, 110, 253, 0.02);
        border: 1px solid rgba(13, 110, 253, 0.08);
        transition: transform 0.25s ease, border-color 0.25s ease;
    }

    .journey-item:hover {
        transform: translateX(2px);
        border-color: rgba(13, 110, 253, 0.15);
    }

    .journey-number {
        width: 38px;
        height: 38px;
        border-radius: 0.85rem;
        background: linear-gradient(135deg, var(--cg-primary), var(--cg-primary-dark));
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        flex-shrink: 0;
        box-shadow: 0 12px 20px rgba(13, 110, 253, 0.18);
    }

    .journey-item h4 {
        margin: 0 0 0.3rem;
        font-size: 1rem;
        color: var(--cg-accent);
    }

    .journey-item p {
        margin: 0;
        color: var(--cg-muted);
        line-height: 1.6;
    }

    .benefit-card {
        position: relative;
        background: rgba(255,255,255,0.9);
        border: 1px solid rgba(13, 110, 253, 0.08);
        border-radius: 1rem;
        padding: 1.2rem 1.1rem;
        box-shadow: 0 12px 22px rgba(15, 23, 42, 0.03);
        height: 100%;
        transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
    }

    .benefit-card:hover {
        transform: translateY(-5px);
        border-color: rgba(13, 110, 253, 0.15);
        box-shadow: 0 18px 28px rgba(15, 23, 42, 0.05);
    }

    .benefit-card i {
        font-size: 1.35rem;
        color: var(--cg-primary);
        margin-bottom: 0.7rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: linear-gradient(135deg, rgba(13,110,253,0.09), rgba(13,110,253,0.04));
    }

    .benefit-card h3 {
        font-size: 1.04rem;
        margin: 0 0 0.55rem;
        color: var(--cg-accent);
    }

    .benefit-card p {
        margin: 0;
        color: var(--cg-muted);
        line-height: 1.62;
    }

    .svc-cta {
        padding: 0 0 4.5rem;
    }

    .svc-cta-card {
        position: relative;
        overflow: hidden;
        background: linear-gradient(135deg, var(--cg-primary) 0%, #0b1f33 100%);
        color: #fff;
        border-radius: 1.4rem;
        box-shadow: 0 20px 48px rgba(13, 110, 253, 0.18);
        padding: 1.8rem 2rem;
    }

    .svc-cta-card::before {
        width: 220px;
        height: 220px;
        background: rgba(255, 255, 255, 0.1);
        right: -30px;
        top: -30px;
        animation: floatGlow 9s ease-in-out infinite alternate;
    }

    .svc-cta-card::after {
        width: 180px;
        height: 180px;
        background: rgba(127, 182, 255, 0.12);
        left: -30px;
        bottom: -30px;
        animation: floatGlow 11s ease-in-out infinite alternate-reverse;
    }

    .svc-cta-card > * {
        position: relative;
        z-index: 1;
    }

    .svc-cta-card h2 {
        margin: 0;
        color: #fff;
        font-size: clamp(1.8rem, 2.8vw, 2.5rem);
        letter-spacing: -0.04em;
        font-weight: 800;
    }

    .svc-cta-card p {
        color: rgba(255, 255, 255, 0.86);
        margin-top: 0.7rem;
        max-width: 58ch;
        line-height: 1.6;
    }

    .svc-cta-card .btn {
        min-width: 170px;
        padding: 0.8rem 1.2rem;
        border-radius: 0.8rem;
        font-weight: 700;
    }

    .svc-cta-card .btn-light {
        color: var(--cg-primary-dark);
    }

    .reveal {
        opacity: 0;
        transform: translateY(18px);
        transition: opacity 0.55s ease, transform 0.55s ease;
    }

    .reveal.is-visible {
        opacity: 1;
        transform: translateY(0);
    }

    .reveal-delay-1 { transition-delay: 0.08s; }
    .reveal-delay-2 { transition-delay: 0.15s; }
    .reveal-delay-3 { transition-delay: 0.22s; }
    .reveal-delay-4 { transition-delay: 0.3s; }

    @keyframes floatGlow {
        0% { transform: translate3d(0, 0, 0) scale(1); }
        100% { transform: translate3d(10px, -10px, 0) scale(1.06); }
    }

    @media (max-width: 991.98px) {
        .svc-page {
            background: linear-gradient(180deg, #f6faff 0%, #ffffff 34%, #f6faff 100%);
        }

        .svc-hero {
            padding-top: 2rem;
        }

        .svc-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767.98px) {
        .svc-section,
        .svc-cta {
            padding-top: 3rem;
            padding-bottom: 3rem;
        }

        .svc-hero .btn,
        .svc-cta-card .btn {
            width: 100%;
        }

        .svc-hero h1 {
            max-width: none;
        }

        .svc-cta-card {
            padding: 1.5rem;
        }

        .journey-item {
            padding: 0.9rem 0.85rem;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        *, *::before, *::after {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
            scroll-behavior: auto !important;
        }

        .reveal {
            opacity: 1;
            transform: none;
        }
    }
</style>

<main class="svc-page flex-grow-1">
    <section class="svc-hero">
        <div class="container">
            <nav aria-label="Breadcrumb" class="mb-3">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Services</li>
                </ol>
            </nav>

            <div class="row align-items-center g-4">
                <div class="col-lg-7">
                    <div class="reveal reveal-delay-1">
                        <span class="svc-eyebrow"><i class="bi bi-briefcase-fill" aria-hidden="true"></i> Candidate services</span>
                    </div>
                    <h1 class="reveal reveal-delay-2">Career support built around the job portal experience.</h1>
                    <p class="lead reveal reveal-delay-3">Career Grow Infotech helps candidates discover current jobs, build a complete profile, upload a resume, apply for positions, and keep track of their application status from a single, straightforward platform.</p>
                    <div class="d-flex flex-wrap gap-3 mt-4 reveal reveal-delay-4">
                        <a href="jobs.php" class="btn btn-primary">Explore jobs</a>
                        <a href="register.php" class="btn btn-outline-primary">Create account</a>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="svc-hero-visual reveal reveal-delay-3">
                        <div class="mini-panel">
                            <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                                <span class="svc-pill"><i class="bi bi-person-circle" aria-hidden="true"></i> Profile</span>
                                <span class="text-muted small">Ready</span>
                            </div>
                            <div class="svc-block-title">Candidate profile</div>
                            <p class="text-muted mb-0">Skills, location, experience, and resume details can be managed in one place.</p>
                        </div>

                        <div class="mini-panel">
                            <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                                <span class="svc-pill"><i class="bi bi-search" aria-hidden="true"></i> Jobs</span>
                                <span class="text-muted small">Updated</span>
                            </div>
                            <div class="svc-grid">
                                <div class="svc-stat">
                                    <strong>Search</strong>
                                    <span class="small text-muted">By role, type, and location</span>
                                </div>
                                <div class="svc-stat">
                                    <strong>Apply</strong>
                                    <span class="small text-muted">Submit through the portal</span>
                                </div>
                            </div>
                        </div>

                        <div class="mini-panel">
                            <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                                <span class="svc-pill"><i class="bi bi-list-check" aria-hidden="true"></i> Status</span>
                                <span class="text-muted small">Live</span>
                            </div>
                            <div class="svc-block-title">Application tracking</div>
                            <p class="text-muted mb-0">Candidates can monitor their submissions and see current application status.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="svc-section">
        <div class="container">
            <div class="section-heading text-center reveal">
                <p class="svc-eyebrow mx-auto">What the platform includes</p>
                <h2>Services that match the actual portal functionality.</h2>
                <p>Every feature below is supported by the current Career Grow Infotech job portal and reflects the real candidate experience.</p>
            </div>

            <div class="row g-4">
                <div class="col-md-6 col-xl-4 reveal">
                    <div class="svc-service-card">
                        <div class="card-body">
                            <div class="svc-icon-wrap"><i class="bi bi-search" aria-hidden="true"></i></div>
                            <h3>Job Search & Discovery</h3>
                            <p>Browse active vacancies, use keyword search, and filter results by job type, category, experience, and location.</p>
                            <a href="jobs.php" class="svc-link">View current jobs <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-4 reveal">
                    <div class="svc-service-card">
                        <div class="card-body">
                            <div class="svc-icon-wrap"><i class="bi bi-person-plus" aria-hidden="true"></i></div>
                            <h3>Candidate Registration</h3>
                            <p>Create a secure personal account to access the portal and start your job search with a verified candidate profile.</p>
                            <a href="register.php" class="svc-link">Create account <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-4 reveal">
                    <div class="svc-service-card">
                        <div class="card-body">
                            <div class="svc-icon-wrap"><i class="bi bi-file-earmark-text" aria-hidden="true"></i></div>
                            <h3>Profile & Resume Management</h3>
                            <p>Update profile details, include skills and qualifications, and upload a resume that can be used for applications.</p>
                            <a href="login.php" class="svc-link">Manage profile <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-4 reveal">
                    <div class="svc-service-card">
                        <div class="card-body">
                            <div class="svc-icon-wrap"><i class="bi bi-send-check" aria-hidden="true"></i></div>
                            <h3>Easy Job Application</h3>
                            <p>Review job detail pages, submit an application, and attach a resume directly from the candidate workflow.</p>
                            <a href="jobs.php" class="svc-link">Apply now <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-4 reveal">
                    <div class="svc-service-card">
                        <div class="card-body">
                            <div class="svc-icon-wrap"><i class="bi bi-list-check" aria-hidden="true"></i></div>
                            <h3>Application Tracking</h3>
                            <p>Monitor submitted applications in the candidate dashboard and filter them by status such as New Applied, Reviewed, Accepted, and Rejected.</p>
                            <a href="login.php" class="svc-link">Track applications <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-4 reveal">
                    <div class="svc-service-card">
                        <div class="card-body">
                            <div class="svc-icon-wrap"><i class="bi bi-shield-lock" aria-hidden="true"></i></div>
                            <h3>Secure Account Access</h3>
                            <p>Candidate login and password protection keep personal details and application data accessible only to the account owner.</p>
                            <a href="login.php" class="svc-link">Login securely <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-4 offset-xl-2 reveal">
                    <div class="svc-service-card">
                        <div class="card-body">
                            <div class="svc-icon-wrap"><i class="bi bi-headset" aria-hidden="true"></i></div>
                            <h3>Contact & Support</h3>
                            <p>Use the public contact form to ask about job queries, application questions, and general support.</p>
                            <a href="contact.php" class="svc-link">Contact us <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="svc-section bg-white">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <div class="section-heading mb-0 reveal">
                        <p class="svc-eyebrow">How it works</p>
                        <h2>Candidate journey built from the actual portal flow.</h2>
                        <p>The current platform supports a simple, practical path from registration to application.</p>
                    </div>

                    <div class="journey-list">
                        <div class="journey-item reveal">
                            <div class="journey-number">1</div>
                            <div>
                                <h4>Register</h4>
                                <p>Create a candidate account with a verified email and password through the public registration screen.</p>
                            </div>
                        </div>

                        <div class="journey-item reveal">
                            <div class="journey-number">2</div>
                            <div>
                                <h4>Complete profile</h4>
                                <p>Add skills, location, experience, qualification, and resume details to strengthen your application profile.</p>
                            </div>
                        </div>

                        <div class="journey-item reveal">
                            <div class="journey-number">3</div>
                            <div>
                                <h4>Search and review jobs</h4>
                                <p>Browse current listings, filter by role and location, and open detailed job pages for more information.</p>
                            </div>
                        </div>

                        <div class="journey-item reveal">
                            <div class="journey-number">4</div>
                            <div>
                                <h4>Apply and track</h4>
                                <p>Submit an application and monitor status updates from the dashboard and My Applications section.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 reveal">
                    <div class="journey-panel">
                        <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                            <div>
                                <p class="text-uppercase text-primary fw-semibold small mb-1">Portal flow</p>
                                <h3 class="mb-0">What candidates can do</h3>
                            </div>
                            <div class="svc-icon-wrap mb-0"><i class="bi bi-people" aria-hidden="true"></i></div>
                        </div>

                        <div class="svc-grid">
                            <div class="svc-stat">
                                <strong>Register</strong>
                                <span class="small text-muted">Create account</span>
                            </div>
                            <div class="svc-stat">
                                <strong>Profile</strong>
                                <span class="small text-muted">Add resume details</span>
                            </div>
                            <div class="svc-stat">
                                <strong>Search</strong>
                                <span class="small text-muted">Filter jobs</span>
                            </div>
                            <div class="svc-stat">
                                <strong>Track</strong>
                                <span class="small text-muted">Review status</span>
                            </div>
                        </div>

                        <div class="mt-4 p-3 border rounded-4 bg-light">
                            <p class="text-muted mb-2">The platform is designed for a clear candidate workflow:</p>
                            <p class="fw-semibold mb-0">Register → Build profile → Search jobs → Apply → Track applications</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="svc-section">
        <div class="container">
            <div class="section-heading text-center reveal">
                <p class="svc-eyebrow mx-auto">Benefits</p>
                <h2>Benefits the project actually supports.</h2>
                <p>These are grounded in the current portal functionality rather than generic staffing claims.</p>
            </div>

            <div class="row g-4">
                <div class="col-md-6 col-lg-4 reveal">
                    <div class="benefit-card">
                        <i class="bi bi-compass" aria-hidden="true"></i>
                        <h3>Centralized job discovery</h3>
                        <p>All active roles are surfaced in one place for easier browsing and filtering.</p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4 reveal">
                    <div class="benefit-card">
                        <i class="bi bi-person-vcard" aria-hidden="true"></i>
                        <h3>Structured candidate profile</h3>
                        <p>Users can add critical profile data, including skills, experience, location, and qualifications.</p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4 reveal">
                    <div class="benefit-card">
                        <i class="bi bi-file-earmark-arrow-up" aria-hidden="true"></i>
                        <h3>Resume upload</h3>
                        <p>Applications can include a resume upload in supported PDF or Word formats.</p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4 reveal">
                    <div class="benefit-card">
                        <i class="bi bi-bar-chart" aria-hidden="true"></i>
                        <h3>Application history</h3>
                        <p>Submitted applications are stored and can be reviewed in a dedicated My Applications area.</p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4 reveal">
                    <div class="benefit-card">
                        <i class="bi bi-lock" aria-hidden="true"></i>
                        <h3>Secure access</h3>
                        <p>Candidate accounts are protected with login and password verification before access to profile data and applications.</p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4 reveal">
                    <div class="benefit-card">
                        <i class="bi bi-info-circle" aria-hidden="true"></i>
                        <h3>Clear job details</h3>
                        <p>Each listing can be opened to review requirements, location, job type, and application availability.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="svc-cta">
        <div class="container reveal">
            <div class="svc-cta-card">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-4">
                    <div>
                        <p class="text-uppercase fw-semibold small mb-2 text-white-50">Ready to explore?</p>
                        <h2>Ready to explore your next opportunity?</h2>
                        <p>Browse the current vacancy list and start your next career step with Career Grow Infotech.</p>
                    </div>

                    <div class="d-flex flex-wrap gap-3">
                        <a href="jobs.php" class="btn btn-light">Browse jobs</a>
                        <a href="register.php" class="btn btn-outline-light">Create account</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<script>
    (() => {
        const reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const revealItems = document.querySelectorAll('.reveal');

        if (!revealItems.length) {
            return;
        }

        if (reduceMotion) {
            revealItems.forEach((item) => item.classList.add('is-visible'));
            return;
        }

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.12,
            rootMargin: '0px 0px -30px 0px'
        });

        revealItems.forEach((item) => observer.observe(item));
    })();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
