<?php require_once __DIR__ . '/includes/header.php'; ?>
<?php require_once __DIR__ . '/includes/navbar.php'; ?>

<style>
    .about-hero {
        background: linear-gradient(135deg, rgba(13, 110, 253, 0.08), rgba(11, 31, 51, 0.02));
        border: 1px solid rgba(13, 110, 253, 0.08);
        border-radius: 1.5rem;
        overflow: hidden;
    }

    .about-badge {
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

    .about-hero-card {
        background: rgba(255, 255, 255, 0.9);
        border: 1px solid rgba(13, 110, 253, 0.08);
        border-radius: 1.1rem;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
    }

    .about-value {
        border-radius: 1rem;
        border: 1px solid var(--cg-border);
        background: var(--cg-white);
        box-shadow: var(--cg-shadow-soft);
        height: 100%;
    }

    .about-timeline {
        position: relative;
        border-left: 2px solid rgba(13, 110, 253, 0.18);
        padding-left: 1.5rem;
    }

    .about-timeline-item {
        position: relative;
        padding-bottom: 1.5rem;
    }

    .about-timeline-item:last-child {
        padding-bottom: 0;
    }

    .about-timeline-item::before {
        content: "";
        position: absolute;
        left: -1.95rem;
        top: 0.3rem;
        width: 0.9rem;
        height: 0.9rem;
        border-radius: 50%;
        background: var(--cg-primary);
        box-shadow: 0 0 0 5px rgba(13, 110, 253, 0.12);
    }

    .about-cta-banner {
        background: linear-gradient(135deg, var(--cg-primary) 0%, var(--cg-accent) 100%);
        border-radius: 1.5rem;
        box-shadow: 0 18px 40px rgba(11, 31, 51, 0.18);
    }

</style>

<main>
    <section class="py-5 py-lg-6">
        <div class="container">
            <div class="about-hero p-3 p-lg-4">
                <div class="row g-4 align-items-center p-2 p-lg-4">
                    <div class="col-lg-7">
                        <div class="p-3 p-lg-4">
                            <span class="about-badge"><i class="bi bi-stars" aria-hidden="true"></i> About us</span>
                            <h1 class="display-5 fw-bold mt-3 mb-3">Helping talent and employers grow together.</h1>
                            <p class="text-muted fs-5 mb-4">Career Grow Infotech Pvt. Ltd. is a forward-focused recruitment and career support brand helping professionals discover the right opportunities and helping organizations hire with confidence.</p>
                            <div class="d-flex flex-wrap gap-3">
                                <a href="jobs.php" class="btn btn-primary">Explore jobs</a>
                                <a href="contact.php" class="btn btn-outline-primary">Talk to our team</a>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="about-hero-card p-4">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <div>
                                    <p class="text-uppercase text-primary fw-semibold small mb-1">Our mission</p>
                                    <h3 class="mb-0 fw-bold">Career clarity. Better hiring.</h3>
                                </div>
                                <div class="bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                                    <i class="bi bi-graph-up-arrow text-primary fs-4" aria-hidden="true"></i>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="d-flex align-items-center gap-3 border rounded-4 p-3">
                                        <div class="bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                                            <i class="bi bi-briefcase-fill text-primary" aria-hidden="true"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1 fw-semibold">Opportunity matching</h6>
                                            <small class="text-muted">Aligning skills, goals and role requirements.</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="d-flex align-items-center gap-3 border rounded-4 p-3">
                                        <div class="bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                                            <i class="bi bi-people-fill text-primary" aria-hidden="true"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1 fw-semibold">Talent-first approach</h6>
                                            <small class="text-muted">Supporting both job seekers and employers.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <div class="row g-4 align-items-center">
                <div class="col-lg-6">
                    <p class="text-uppercase text-primary fw-semibold small mb-2">Who we are</p>
                    <h2 class="fw-bold mb-3">A recruitment partner built around trust, clarity and growth.</h2>
                    <p class="text-muted mb-3">We believe the right opportunity is not just about the job description — it is about fit, long-term potential, and a stronger future for both the candidate and the employer.</p>
                    <p class="text-muted mb-4">From identifying strong talent to connecting businesses with capable professionals, our process is shaped by market understanding, personalized guidance and a commitment to meaningful placement outcomes.</p>
                    <div class="d-flex flex-wrap gap-3">
                        <div class="d-flex align-items-center gap-2 text-dark fw-semibold">
                            <i class="bi bi-check-circle-fill text-primary" aria-hidden="true"></i>
                            Candidate-focused support
                        </div>
                        <div class="d-flex align-items-center gap-2 text-dark fw-semibold">
                            <i class="bi bi-check-circle-fill text-primary" aria-hidden="true"></i>
                            Employer-ready hiring approach
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="about-value p-4 h-100">
                                <div class="bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 52px; height: 52px;">
                                    <i class="bi bi-lightbulb text-primary fs-4" aria-hidden="true"></i>
                                </div>
                                <h5 class="fw-semibold mb-2">Insight-driven</h5>
                                <p class="text-muted small">We work with a practical understanding of industry needs and candidate expectations.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="about-value p-4 h-100">
                                <div class="bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 52px; height: 52px;">
                                    <i class="bi bi-shield-check text-primary fs-4" aria-hidden="true"></i>
                                </div>
                                <h5 class="fw-semibold mb-2">Reliable support</h5>
                                <p class="text-muted small">Transparent, consistent assistance throughout the hiring and job-search journey.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="about-value p-4 h-100">
                                <div class="bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 52px; height: 52px;">
                                    <i class="bi bi-people text-primary fs-4" aria-hidden="true"></i>
                                </div>
                                <h5 class="fw-semibold mb-2">People-first</h5>
                                <p class="text-muted small">We invest in human connections and long-term career growth, not just quick placements.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="about-value p-4 h-100">
                                <div class="bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 52px; height: 52px;">
                                    <i class="bi bi-globe2 text-primary fs-4" aria-hidden="true"></i>
                                </div>
                                <h5 class="fw-semibold mb-2">Growth mindset</h5>
                                <p class="text-muted small">We continuously adapt to changing business needs, candidate goals and market trends.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 pb-6">
        <div class="container">
            <div class="about-cta-banner text-white p-4 p-lg-5">
                <div class="row align-items-center g-4">
                    <div class="col-lg-8">
                        <p class="text-uppercase fw-semibold small mb-2 text-white-50">Let’s grow together</p>
                        <h2 class="fw-bold mb-2">Your next opportunity or your next great hire starts here.</h2>
                        <p class="text-white-50 mb-0">Whether you are searching for a role or hiring for your team, Career Grow Infotech helps make the next step clearer.</p>
                    </div>
                    <div class="col-lg-4 text-lg-end">
                        <div class="d-flex flex-wrap justify-content-lg-end gap-3">
                            <a href="jobs.php" class="btn btn-light">View jobs</a>
                            <a href="contact.php" class="btn btn-outline-light">Contact us</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
