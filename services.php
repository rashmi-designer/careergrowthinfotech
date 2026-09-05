<?php
$pageTitle = 'Services - Career Grow Infotech';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<style>
/* Page-specific styles (kept local to services.php) */
.svc-hero {
    padding: 5rem 0 3.5rem;
    background: linear-gradient(180deg, rgba(13,110,253,0.04), rgba(13,110,253,0.01));
    position: relative;
    overflow: visible;
}
.svc-hero .hero-decor { position: absolute; right: -6%; top: -6%; width: 380px; height: 380px; opacity: .06; transform: rotate(18deg); }
.svc-eyebrow { letter-spacing: .12em; font-size: .78rem; }
.svc-intro-highlights .card { border: 1px solid var(--cg-border); border-radius: .85rem; box-shadow: 0 8px 30px rgba(15,23,42,0.04); }
.svc-service-badge { width:44px; height:44px; border-radius:8px; background: linear-gradient(135deg,var(--cg-primary),var(--cg-primary-dark)); color:#fff; display:inline-flex; align-items:center; justify-content:center; font-weight:700; }
.svc-service-card { border:1px solid var(--cg-border); border-radius:.9rem; transition: transform .22s ease, box-shadow .22s ease; background:var(--cg-white); }
.svc-service-card:hover { transform: translateY(-6px); box-shadow: 0 22px 56px rgba(15,23,42,0.06); }
.svc-service-card .card-body { min-height: 150px; display:flex; flex-direction:column; }
.svc-service-card .card-footer { background:transparent; border-top:0; }
.svc-feature-visual { background: linear-gradient(180deg, rgba(13,110,253,0.02), rgba(13,110,253,0.00)); border:1px solid var(--cg-border); border-radius:.9rem; padding:1.25rem; }
.process-steps { position:relative; }
.process-track { display:flex; gap:1rem; align-items:stretch; }
.process-step { flex:1 1 0; background:var(--cg-white); border:1px solid var(--cg-border); border-radius:.85rem; padding:1.1rem; box-shadow: 0 10px 28px rgba(15,23,42,0.04); }
.process-step .step-num { width:40px; height:40px; border-radius:8px; background:var(--cg-primary); color:#fff; display:inline-flex; align-items:center; justify-content:center; font-weight:700; margin-right:.8rem; }
@media (max-width: 991.98px) {
    .svc-hero { padding:3rem 0 2rem; }
    .svc-hero .hero-decor { display:none; }
    .process-track { flex-direction:column; }
}
.why-cards .card { border:1px solid var(--cg-border); border-radius:.85rem; box-shadow: 0 10px 28px rgba(15,23,42,0.04); }
.final-cta { padding:2.25rem 0; background: linear-gradient(180deg, rgba(13,110,253,0.04), rgba(13,110,253,0.01)); }
.lead-muted { color:var(--cg-muted); }
.svc-hero .hero-row { align-items: center; gap: 2rem; }
.svc-hero .section-title { font-size: clamp(1.8rem, 3.6vw, 2.6rem); font-weight:800; }
.svc-hero .lead-muted { color:var(--cg-muted); max-width: 58ch; }
.svc-hero .hero-actions .btn { min-width: 160px; }

/* Talent matching visual */
.talent-visual { position: relative; display: flex; align-items: center; justify-content: center; }
.tv-card { width: 360px; max-width: 100%; background: var(--cg-white); border: 1px solid var(--cg-border); border-radius: 1rem; box-shadow: 0 18px 40px rgba(15,23,42,0.06); padding: 1rem; }
.tv-card .profile { display:flex; gap:.75rem; align-items:center; }
.tv-card .avatar { width:56px; height:56px; border-radius:12px; background: linear-gradient(135deg,var(--cg-primary),var(--cg-primary-dark)); color:#fff; display:inline-flex; align-items:center; justify-content:center; font-weight:700; }
.tv-card .skills { margin-top:.6rem; display:flex; gap:.4rem; flex-wrap:wrap; }
.tv-card .skill { background: rgba(13,110,253,0.06); color:var(--cg-primary); padding:.28rem .55rem; border-radius:.5rem; font-size:.85rem; }
.tv-small { position: absolute; right: -28px; top: 12%; width:180px; background:var(--cg-white); border:1px solid var(--cg-border); border-radius:.75rem; padding:.6rem; box-shadow: 0 10px 30px rgba(15,23,42,0.06); }
.tv-connector { position:absolute; left: 42%; top: 40%; width: 120px; height:2px; background: linear-gradient(90deg, rgba(13,110,253,0.12), rgba(13,110,253,0.45)); transform: rotate(8deg); }
.talent-visual { display:grid; grid-template-columns:minmax(320px, 360px) 190px; gap:1rem; justify-content:center; align-items:center; min-height:280px; }
.talent-visual .tv-card { width:100%; position:relative; z-index:1; }
.talent-visual .tv-small { position:static; width:100%; padding:1rem; z-index:1; }
.talent-visual .tv-connector { display:none; }
.talent-visual .bi-briefcase,
.svc-feature-visual .bi-briefcase {
    width: 46px;
    height: 46px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 14px;
    background: rgba(13,110,253,0.08);
    color: var(--cg-primary) !important;
    font-size: 1.15rem !important;
    flex: 0 0 auto;
    line-height: 1;
}
.talent-visual .bi-briefcase { width: 42px; height: 42px; border-radius: 12px; font-size: 1.05rem !important; }
.job-seeker-visual { padding:1.5rem; background:linear-gradient(145deg, rgba(13,110,253,0.06), rgba(255,255,255,0.72)); }
.job-seeker-visual .tv-card { max-width:none; padding:1.5rem; border-radius:1.25rem; box-shadow:0 18px 45px rgba(15,23,42,0.08); }
.job-seeker-visual .tv-card > .d-flex:first-child { padding-bottom:1.25rem; border-bottom:1px solid rgba(15,23,42,0.08); }
.job-seeker-visual .tv-card > .d-flex:nth-child(2) { margin:1.25rem 0 !important; padding:1rem; border:1px solid rgba(13,110,253,0.1); border-radius:.9rem; background:rgba(13,110,253,0.04); }
.job-seeker-visual .tv-card > .d-flex:last-child { justify-content:flex-end; flex-wrap:wrap; gap:.75rem !important; }
.job-seeker-visual .tv-card > .d-flex:last-child .btn { min-width:150px; }
.job-seeker-visual .bi-briefcase { width:48px; height:48px; border-radius:14px; background:var(--cg-white); box-shadow:0 6px 16px rgba(13,110,253,0.08); }
.job-seeker-copy ul { list-style:none; padding-left:0; }
.job-seeker-copy li { position:relative; padding-left:1.5rem; margin-bottom:.65rem; }
.job-seeker-copy li::before { content:'\2713'; position:absolute; left:0; top:.1rem; color:var(--cg-primary); font-weight:700; }

.svc-intro-highlights .feature { display:flex; gap:1rem; align-items:flex-start; padding:1.05rem; border-radius:.75rem; background:var(--cg-white); border:1px solid rgba(15,23,42,0.04); box-shadow: 0 8px 24px rgba(15,23,42,0.04); }
.svc-service-badge {
    width: 52px;
    height: 52px;
    border-radius: 16px;
    background: linear-gradient(135deg,var(--cg-primary),var(--cg-primary-dark));
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    font-size: 1.1rem;
    box-shadow: 0 10px 22px rgba(13,110,253,0.14);
}
.svc-service-badge i { line-height: 1; }

/* Services grid: 4 columns desktop */
.svc-service-card {
    border: 1px solid var(--cg-border);
    border-radius: 1rem;
    transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
    background: var(--cg-white);
    display: flex;
    flex-direction: column;
    min-height: 220px;
}
.svc-service-card .icon-wrap {
    width: 56px;
    height: 56px;
    border-radius: 16px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, rgba(13,110,253,0.12), rgba(13,110,253,0.08));
    color: var(--cg-primary);
    font-size: 1.25rem;
    flex: 0 0 auto;
    line-height: 1;
    box-shadow: 0 10px 20px rgba(13,110,253,0.08);
}
.svc-service-card .icon-wrap i { line-height: 1; }
.svc-service-card h5 { font-size:1.05rem; margin-bottom:.45rem; }
.svc-service-card p { color:var(--cg-muted); }
.svc-service-card:hover { transform: translateY(-6px); box-shadow: 0 28px 60px rgba(15,23,42,0.08); border-color: rgba(13,110,253,0.12); }
.svc-service-card:hover .icon-wrap { transform: translateY(-3px); }
.svc-service-card .card-body { flex:1 1 auto; }
.svc-service-card .card-footer { background:transparent; border-top:0; }

/* Additional visual polish: spacing, headings, numbers */
.svc-section { padding-top: 4.5rem; padding-bottom: 4.5rem; }
@media (min-width: 1200px) { .svc-section { padding-top: 6rem; padding-bottom: 5rem; } }
@media (max-width: 991.98px) { .svc-section { padding-top: 3.5rem; padding-bottom: 3rem; } }
.svc-service-card { padding: 1.35rem; }
.svc-service-footer { margin-top: 1rem; }
.svc-service-footer a { color: var(--cg-primary); font-weight:600; text-decoration: none; }
.svc-service-footer a:hover { text-decoration: underline; }
.svc-service-meta {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 56px;
    gap: .9rem;
    align-items: start;
    width: 100%;
}
.svc-service-meta > div:first-child {
    min-width: 0;
    overflow-wrap: normal;
    word-break: normal;
}
.svc-service-card .icon-wrap { justify-self: end; align-self: start; margin-left: 0; }
.svc-service-card h5 { line-height: 1.25; }
.svc-service-card p { line-height: 1.65; }
.section-title { letter-spacing: -0.01em; }
.lead-muted { font-size: 1rem; line-height: 1.7; }

@media (max-width: 991.98px) {
    .svc-hero { padding:3rem 0 2rem; }
    .talent-visual { display:flex; flex-direction:column; align-items:stretch; min-height:0; }
    .talent-visual .tv-card,
    .talent-visual .tv-small { max-width:420px; margin-left:auto; margin-right:auto; }
}

@media (max-width: 575.98px) {
    .svc-service-meta {
        gap: .75rem;
        grid-template-columns: minmax(0, 1fr) 48px;
    }

    .svc-service-card .icon-wrap,
    .svc-service-badge {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        font-size: 1.05rem;
    }
}

@media (max-width: 399.98px) {
    .svc-service-meta {
        grid-template-columns: minmax(0, 1fr) 48px;
    }

    .svc-service-card .icon-wrap {
        grid-column: 2;
        grid-row: 1;
    }
}

.process-steps { padding-top:.5rem; }
.process-track { display:grid !important; grid-template-columns:minmax(0, 1fr) 80px minmax(0, 1fr) 80px minmax(0, 1fr) 80px minmax(0, 1fr); gap:1rem; align-items:stretch !important; }
.process-step { width:100%; min-width:0; display:flex; align-self:stretch; background:transparent; border:0; padding:0; }
.process-node { width:100%; height:100%; background:var(--cg-white); border:1px solid var(--cg-border); border-radius:12px; padding:1.25rem; box-shadow: 0 12px 30px rgba(15,23,42,0.04); display:flex; gap:1rem; align-items:flex-start; min-height:170px; }
.process-node > div:last-child { min-width:0; }
.process-step:first-child .process-node h5 { font-size:1.18rem; }
.process-node .step-num { width:52px; height:52px; min-width:52px; min-height:52px; flex:0 0 52px; aspect-ratio:1 / 1; border-radius:50%; background:var(--cg-primary); color:#fff; display:inline-flex; align-items:center; justify-content:center; font-weight:800; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, 'Roboto Mono', monospace; line-height:1; }
.process-line { width:100%; height:2px; align-self:center; background: linear-gradient(90deg, rgba(13,110,253,0.12), rgba(13,110,253,0.28)); }

@media (max-width: 991.98px) {
    .process-track { display:flex !important; flex-direction:column; align-items:stretch !important; }
    .process-step { display:block; }
    .process-node { height:auto; }
}

.why-cards .card { border:0; background:var(--cg-white); border-radius:12px; padding:1.2rem; box-shadow: 0 12px 36px rgba(15,23,42,0.04); }
.final-cta { padding:2.25rem 0; }
.final-cta .final-cta-card { background: linear-gradient(90deg, var(--cg-primary), var(--cg-primary-dark)); color: #fff; border:0; box-shadow: 0 20px 60px rgba(13,110,253,0.18); }
.final-cta .final-cta-card .text-soft { color: rgba(255,255,255,0.88) !important; }
.final-cta .final-cta-card .btn-primary { background:#fff; border-color:#fff; color:var(--cg-primary-dark); }
.final-cta .final-cta-card .btn-primary:hover,
.final-cta .final-cta-card .btn-primary:focus { background:var(--cg-primary); border-color:var(--cg-primary); color:#fff; }

.svc-hero .breadcrumb { background: transparent; padding: 0; }
</style>

<main class="flex-grow-1">

    <!-- HERO -->
    <section class="svc-hero">
        <div class="container">
            <div class="row hero-row">
                <div class="col-lg-6">
                    <p class="svc-eyebrow text-uppercase text-primary fw-semibold mb-2">OUR SERVICES</p>
                    <h1 class="section-title">Recruitment Solutions That Connect Talent With Opportunity</h1>
                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb mb-0 bg-transparent">
                            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Services</li>
                        </ol>
                    </nav>
                    <p class="lead-muted mt-2">Career Grow Infotech provides professional recruitment and career support services designed to connect organizations with suitable talent and help candidates discover meaningful career opportunities.</p>
                    <div class="hero-actions d-flex gap-2 mt-4">
                        <a href="jobs.php" class="btn btn-primary">Explore Jobs</a>
                        <a href="contact.php" class="btn btn-outline-primary">Contact Us</a>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="talent-visual">
                        <div class="tv-card">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="profile">
                                    <div class="avatar">RK</div>
                                    <div>
                                        <div class="fw-semibold">Rashmi K.</div>
                                        <div class="text-soft small">Marketing Executive • 3+ yrs</div>
                                    </div>
                                </div>
                                <div class="text-end small text-soft">Profile</div>
                            </div>

                            <div class="skills mt-3">
                                <span class="skill">Marketing</span>
                                <span class="skill">SEO</span>
                                <span class="skill">Communication</span>
                            </div>

                            <div class="mt-3 d-flex justify-content-between align-items-center">
                                <div class="text-muted small">Experience: 3+ years</div>
                                <div class="badge bg-soft text-primary rounded-pill">Matched</div>
                            </div>
                        </div>

                        <div class="tv-connector" aria-hidden="true"></div>

                        <div class="tv-small">
                            <div class="d-flex align-items-start gap-2">
                                <i class="bi bi-briefcase fs-4 text-primary"></i>
                                <div>
                                    <div class="fw-semibold">Suitable Opportunity</div>
                                    <div class="text-soft small">Marketing Executive - Pune</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- INTRO -->
    <section class="py-5 svc-section">
        <div class="container">
            <div class="row align-items-center mb-4">
                <div class="col-lg-6">
                    <h2 class="section-title">How We Help</h2>
                    <p class="lead-muted">Career Grow Infotech supports both organizations and job seekers through a structured recruitment approach focused on skills, requirements, opportunities and professional connections.</p>
                </div>
            </div>

            <div class="row svc-intro-highlights g-3">
                <div class="col-md-4">
                    <div class="card p-3 h-100">
                        <div class="d-flex align-items-start gap-3">
                            <div class="svc-service-badge"><i class="bi bi-building"></i></div>
                            <div>
                                <h5 class="mb-1">For Employers</h5>
                                <p class="text-soft mb-0">Structured recruitment support to help organizations find and engage suitable talent.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card p-3 h-100">
                        <div class="d-flex align-items-start gap-3">
                            <div class="svc-service-badge"><i class="bi bi-person"></i></div>
                            <div>
                                <h5 class="mb-1">For Job Seekers</h5>
                                <p class="text-soft mb-0">Career guidance and practical support to help candidates present their skills effectively.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card p-3 h-100">
                        <div class="d-flex align-items-start gap-3">
                            <div class="svc-service-badge"><i class="bi bi-gear"></i></div>
                            <div>
                                <h5 class="mb-1">Recruitment Support</h5>
                                <p class="text-soft mb-0">End-to-end coordination and support through the recruitment journey.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- MAIN SERVICES -->
    <section class="py-4 bg-soft svc-section">
        <div class="container">
            <div class="row mb-3">
                <div class="col-lg-8">
                    <h2 class="section-title">Our Recruitment Services</h2>
                    <p class="text-soft">Practical recruitment support for employers and meaningful career assistance for job seekers.</p>
                </div>
            </div>

            <div class="row g-4">
                <?php
                $services = [
                    ["title"=>"Recruitment Assistance","icon"=>"people-fill","desc"=>"Support organizations in identifying and connecting with professionals based on their hiring requirements."],
                    ["title"=>"Talent Sourcing","icon"=>"search","desc"=>"Identify potential candidates based on relevant skills, experience and role requirements."],
                    ["title"=>"Candidate Screening","icon"=>"file-earmark-text","desc"=>"Review candidate profiles against relevant requirements to support a focused recruitment process."],
                    ["title"=>"Skill-Based Matching","icon"=>"person-bounding-box","desc"=>"Connect candidate skills and experience with suitable professional opportunities."],
                    ["title"=>"Hiring Support","icon"=>"person-check-fill","desc"=>"Provide structured recruitment support throughout the candidate and employer interaction process."],
                    ["title"=>"Career Support","icon"=>"briefcase","desc"=>"Help job seekers explore opportunities aligned with their skills, qualifications, experience and career goals."],
                    ["title"=>"Job Opportunity Guidance","icon"=>"compass","desc"=>"Help candidates understand available opportunities and identify roles that match their career interests."],
                    ["title"=>"Recruitment Coordination","icon"=>"inboxes","desc"=>"Support communication and coordination between suitable candidates and organizations during the recruitment journey."]
                ];

                foreach ($services as $s) {
                    ?>
                        <div class="col-lg-3 col-md-6">
                            <div class="card svc-service-card h-100 d-flex flex-column">
                                <div class="card-body d-flex flex-column">
                                    <div class="svc-service-meta mb-3">
                                        <div>
                                            <h5 class="mb-0"><?php echo htmlspecialchars($s['title'],ENT_QUOTES,'UTF-8'); ?></h5>
                                            <p class="text-soft mb-0 small mt-1"><?php echo htmlspecialchars($s['desc'],ENT_QUOTES,'UTF-8'); ?></p>
                                        </div>
                                        <div class="icon-wrap"><i class="bi bi-<?php echo htmlspecialchars($s['icon'],ENT_QUOTES,'UTF-8'); ?>"></i></div>
                                    </div>
                                    <div class="mt-auto svc-service-footer">
                                        <a href="contact.php">Get Started &raquo;</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php
                }
                ?>
            </div>
        </div>
    </section>

    <!-- EMPLOYER SERVICES -->
    <section class="py-5 svc-section">
        <div class="container">
            <div class="row align-items-center g-4">
                <div class="col-lg-6">
                    <h3>Solutions for Employers</h3>
                    <p class="text-soft">Finding suitable professionals can be challenging. Our recruitment support helps organizations identify relevant talent based on their requirements.</p>
                    <ul class="mt-3">
                        <li>Understand Hiring Requirements</li>
                        <li>Source Relevant Candidates</li>
                        <li>Support Candidate Screening</li>
                        <li>Assist Recruitment Coordination</li>
                    </ul>
                    <a href="contact.php" class="btn btn-primary mt-3">Discuss Your Hiring Needs</a>
                </div>

                <div class="col-lg-6">
                    <div class="svc-feature-visual h-100 d-flex align-items-center justify-content-center">
                        <div class="tv-card w-100">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="fw-semibold">Talent Search</div>
                                <div class="text-soft small">Filtered: Marketing, Pune</div>
                            </div>

                            <div class="mb-2">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar">AD</div>
                                        <div>
                                            <div class="fw-semibold">Aditya D.</div>
                                            <div class="text-soft small">PHP Developer • 4 yrs</div>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div class="small text-soft">Skills</div>
                                        <div class="mt-1 small"><span class="skill">PHP</span> <span class="skill">MySQL</span></div>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar">SM</div>
                                        <div>
                                            <div class="fw-semibold">Sneha M.</div>
                                            <div class="text-soft small">Marketing Executive • 3 yrs</div>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div class="small text-soft">Skills</div>
                                        <div class="mt-1 small"><span class="skill">SEO</span> <span class="skill">Content</span></div>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar">PN</div>
                                        <div>
                                            <div class="fw-semibold">Priya N.</div>
                                            <div class="text-soft small">UI/UX • 5 yrs</div>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div class="small text-soft">Skills</div>
                                        <div class="mt-1 small"><span class="skill">Figma</span> <span class="skill">UX</span></div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <a href="contact.php" class="small">Discuss candidates &raquo;</a>
                                <div class="badge bg-soft text-primary rounded-pill">3 Matched</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- JOB SEEKER SERVICES -->
    <section class="py-5 bg-soft svc-section">
        <div class="container">
            <div class="row align-items-center g-4">
                <div class="col-lg-6 order-lg-1 order-1">
                    <div class="svc-feature-visual job-seeker-visual d-flex align-items-center justify-content-center">
                        <div class="tv-card w-100 text-start">
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <div class="avatar">JS</div>
                                <div>
                                    <div class="fw-semibold">Your Profile</div>
                                    <div class="text-soft small">Showcase skills and explore roles</div>
                                </div>
                            </div>

                            <div class="d-flex gap-2 align-items-center mb-3">
                                <i class="bi bi-briefcase fs-3 text-primary"></i>
                                <div>
                                    <div class="fw-semibold">Frontend Developer</div>
                                    <div class="text-soft small">JavaScript • React • 3+ yrs</div>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <a href="jobs.php" class="btn btn-primary">View Matching Roles</a>
                                <a href="register.php" class="btn btn-outline-secondary">Update Profile</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 order-lg-2 order-2 job-seeker-copy">
                    <h3>Support for Job Seekers</h3>
                    <p class="text-soft">Explore opportunities that match your skills, qualifications, experience and career goals.</p>
                    <ul class="mt-3">
                        <li>Explore Job Opportunities</li>
                        <li>Build Your Professional Profile</li>
                        <li>Highlight Your Skills</li>
                        <li>Move Toward Your Career Goals</li>
                    </ul>
                    <div class="d-flex gap-2 mt-3">
                        <a href="jobs.php" class="btn btn-primary">Explore Jobs</a>
                        <a href="register.php" class="btn btn-outline-secondary">Create Your Profile</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- RECRUITMENT PROCESS -->
    <section class="py-5 svc-section">
        <div class="container">
            <div class="row mb-3">
                <div class="col-12">
                    <h3 class="section-title">Our Recruitment Approach</h3>
                    <p class="text-soft">A practical, staged approach that helps connect the right people with the right roles.</p>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="process-steps">
                        <div class="process-track d-flex align-items-center flex-column flex-lg-row">
                            <div class="process-step col-12 col-lg-auto">
                                <div class="process-node">
                                    <div class="step-num">01</div>
                                    <div>
                                        <h5 class="mb-1">Understand</h5>
                                        <p class="text-soft mb-0">Understand employer or candidate requirements.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="d-none d-lg-block process-line" aria-hidden="true"></div>

                            <div class="process-step col-12 col-lg-auto">
                                <div class="process-node">
                                    <div class="step-num">02</div>
                                    <div>
                                        <h5 class="mb-1">Identify</h5>
                                        <p class="text-soft mb-0">Identify relevant opportunities or potential talent.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="d-none d-lg-block process-line" aria-hidden="true"></div>

                            <div class="process-step col-12 col-lg-auto">
                                <div class="process-node">
                                    <div class="step-num">03</div>
                                    <div>
                                        <h5 class="mb-1">Connect</h5>
                                        <p class="text-soft mb-0">Create meaningful connections between candidates and organizations.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="d-none d-lg-block process-line" aria-hidden="true"></div>

                            <div class="process-step col-12 col-lg-auto">
                                <div class="process-node">
                                    <div class="step-num">04</div>
                                    <div>
                                        <h5 class="mb-1">Support</h5>
                                        <p class="text-soft mb-0">Provide structured support through the recruitment journey.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- WHY WORK WITH US -->
    <section class="py-5 bg-soft svc-section">
        <div class="container">
            <div class="row mb-3">
                <div class="col-lg-8">
                    <h3 class="section-title">Why Choose Our Recruitment Support?</h3>
                    <p class="text-soft">A practical, people-centred approach delivered with professional coordination.</p>
                </div>
            </div>

            <div class="row g-4 why-cards">
                <div class="col-md-6 col-lg-3">
                    <div class="card p-3 h-100">
                        <h6 class="mb-2">Requirement-Focused Approach</h6>
                        <p class="text-soft mb-0">We focus on core role requirements to find relevant matches.</p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="card p-3 h-100">
                        <h6 class="mb-2">Skill-Based Matching</h6>
                        <p class="text-soft mb-0">Connect competencies with real role needs.</p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="card p-3 h-100">
                        <h6 class="mb-2">Candidate-Centered Support</h6>
                        <p class="text-soft mb-0">Support candidates to present their experience effectively.</p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="card p-3 h-100">
                        <h6 class="mb-2">Professional Coordination</h6>
                        <p class="text-soft mb-0">Clear communication and process coordination for all parties.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FINAL CTA -->
    <section class="final-cta svc-section">
        <div class="container">
            <div class="final-cta-card p-4 rounded">
                <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                    <div>
                        <h3 class="mb-1">Ready to Take the Next Step?</h3>
                        <p class="text-soft mb-0">Whether you are looking for the right opportunity or the right talent, connect with Career Grow Infotech today.</p>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="jobs.php" class="btn btn-primary">Explore Jobs</a>
                        <a href="contact.php" class="btn btn-outline-primary">Contact Us</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
