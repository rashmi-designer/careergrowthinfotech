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
.final-cta { padding: 4rem 0; background: linear-gradient(180deg, #f8fbff, #f3f8ff); }
.final-cta .final-cta-card {
    padding: clamp(1.75rem, 4vw, 3.5rem);
    border: 1px solid #c7ddfb;
    border-radius: 1.5rem;
    background: linear-gradient(112deg, #fbfdff 0%, #edf5ff 52%, #d9eaff 100%);
    box-shadow: 0 18px 40px rgba(26, 82, 151, 0.12);
}
.final-cta .cta-eyebrow { margin-bottom: .65rem; color: #356391; font-size: .74rem; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; }
.final-cta .final-cta-card h3 { margin-bottom: .7rem; color: #102b50; font-size: clamp(1.85rem, 3vw, 2.55rem); font-weight: 750; line-height: 1.2; }
.final-cta .final-cta-card .text-soft { max-width: 59ch; color: #466687 !important; font-size: 1.05rem; line-height: 1.65; }
.final-cta .cta-actions { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: .85rem; }
.final-cta .cta-actions .btn { min-width: 142px; padding: .75rem 1.1rem; font-weight: 600; }
.final-cta .cta-actions .btn-primary { box-shadow: 0 10px 20px rgba(10, 88, 202, .2); }
.final-cta .cta-actions .btn-outline-primary { border-color: #0d6efd; color: #0a58ca; }
.final-cta .cta-actions .btn-outline-primary:hover,
.final-cta .cta-actions .btn-outline-primary:focus { background: #0d6efd; border-color: #0d6efd; color: #fff; }
@media (max-width: 991.98px) {
    .final-cta .cta-actions { justify-content: flex-start; }
}
@media (max-width: 575.98px) {
    .final-cta { padding: 2.75rem 0; }
    .final-cta .cta-actions { display: grid; grid-template-columns: 1fr; }
    .final-cta .cta-actions .btn { width: 100%; }
}

.svc-hero .breadcrumb { background: transparent; padding: 0; }

/* Services hero aligned with the contained About-page hero treatment */
.svc-hero {
    padding: 2.75rem 0 3.5rem;
    background: transparent;
    overflow: hidden;
}
.svc-hero-panel {
    background:
        radial-gradient(circle at 82% 38%, rgba(255, 255, 255, 0.42), transparent 25%),
        radial-gradient(circle at 100% 0%, rgba(13, 110, 253, 0.24), transparent 36%),
        linear-gradient(118deg, #f9fbff 0%, #edf4ff 36%, #c9e0ff 70%, #9bc7ff 100%);
    border: 1px solid rgba(13, 110, 253, 0.16);
    border-radius: 1.5rem;
    box-shadow: 0 18px 42px rgba(13, 110, 253, 0.08);
}
.svc-hero .hero-row { gap: 0; }
.svc-hero .svc-eyebrow {
    display: inline-flex;
    align-items: center;
    padding: .55rem .9rem;
    border-radius: 999px;
    background: rgba(13, 110, 253, 0.09);
    color: var(--cg-primary) !important;
    font-weight: 700;
}
.svc-hero .section-title {
    max-width: 14ch;
    font-size: clamp(2.35rem, 4vw, 4rem);
    line-height: 1.16;
    letter-spacing: -0.035em;
    color: #1f2a3d;
}
.svc-hero .breadcrumb {
    display: inline-flex;
    width: auto;
    margin-top: .15rem;
    padding: .42rem .7rem;
    border: 1px solid rgba(13, 110, 253, 0.12);
    border-radius: .65rem;
    background: rgba(255, 255, 255, 0.56);
}
.svc-hero .lead-muted {
    max-width: 56ch;
    color: #4a607a;
    font-size: 1.08rem;
}
.svc-hero-card {
    background: rgba(255, 255, 255, 0.9);
    border: 1px solid rgba(13, 110, 253, 0.1);
    border-radius: 1.1rem;
    box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
}
.svc-hero-card .talent-visual {
    grid-template-columns: minmax(0, 1fr) minmax(130px, .48fr);
    gap: .75rem;
    min-height: 0;
}
.svc-hero-card .tv-card,
.svc-hero-card .tv-small {
    box-shadow: none;
}
.svc-hero-card .tv-card { padding: 1.1rem; }
.svc-hero-card .tv-small { padding: .8rem; }
.svc-match-board { display: grid; grid-template-columns: minmax(0, 1fr) 165px; gap: .9rem; align-items: stretch; }
.svc-match-primary, .svc-match-process { border: 1px solid #dce6f4; border-radius: 1rem; background: rgba(255, 255, 255, 0.88); }
.svc-match-primary { padding: 1.3rem; }
.svc-match-process { padding: 1rem; }
.svc-match-board .tv-card { width: auto; max-width: none; box-shadow: none; }
.svc-match-board .tv-small { position: static; width: auto; box-shadow: none; }
.svc-match-board .tv-connector { display: none; }
.svc-match-process .bi-briefcase {
    width: 42px;
    height: 42px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: .8rem;
    background: #e8f1ff;
    color: #0d6efd !important;
    font-size: 1.05rem !important;
    flex: 0 0 auto;
}
.svc-match-top { display: flex; align-items: center; justify-content: space-between; gap: .75rem; margin-bottom: 1rem; }
.svc-match-label, .svc-match-status { display: inline-flex; align-items: center; gap: .4rem; font-size: .75rem; font-weight: 700; }
.svc-match-label { color: #0d6efd; }
.svc-match-status { padding: .3rem .55rem; border-radius: 999px; background: #e5f7ef; color: #15845f; }
.svc-match-primary h3 { margin-bottom: .4rem; color: #142a48; font-size: 1.25rem; font-weight: 700; }
.svc-match-primary > p { margin-bottom: 1rem; color: #58708d; font-size: .88rem; line-height: 1.55; }
.svc-match-tags { display: flex; flex-wrap: wrap; gap: .45rem; margin-bottom: 1.1rem; }
.svc-match-tags span { padding: .33rem .58rem; border-radius: .5rem; background: #eef4ff; color: #2864bd; font-size: .78rem; font-weight: 600; }
.svc-match-meter + .svc-match-meter { margin-top: .75rem; }
.svc-match-meter-label { display: flex; justify-content: space-between; gap: .75rem; margin-bottom: .35rem; color: #45617f; font-size: .77rem; }
.svc-match-meter-label strong { color: #1b63c7; }
.svc-match-track { height: .38rem; overflow: hidden; border-radius: 999px; background: #e7edf6; }
.svc-match-track span { display: block; height: 100%; border-radius: inherit; background: linear-gradient(90deg, #1976f3, #6ba9ff); }
.svc-match-process-icon { width: 44px; height: 44px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: .8rem; border-radius: .85rem; background: #e8f1ff; color: #0d6efd; font-size: 1.2rem; }
.svc-match-process h4 { margin-bottom: .8rem; color: #17355d; font-size: .98rem; font-weight: 700; }
.svc-match-process-item { display: flex; gap: .45rem; align-items: flex-start; color: #536b88; font-size: .78rem; line-height: 1.35; }
.svc-match-process-item + .svc-match-process-item { margin-top: .72rem; }
.svc-match-process-item i { color: #0d6efd; }

/* Homepage-inspired icon palette and card interactions */
.svc-intro-highlights .card,
.svc-service-card,
.process-node,
.why-cards .card {
    --svc-accent: #1267e8;
    --svc-rgb: 18, 103, 232;
    position: relative;
    overflow: hidden;
    border: 1px solid #e1e9f5;
    transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
}
.svc-intro-highlights .card::before,
.svc-service-card::before,
.process-node::before,
.why-cards .card::before {
    position: absolute;
    top: 0;
    left: 50%;
    width: 0;
    height: 4px;
    border-radius: 0 0 999px 999px;
    background: linear-gradient(90deg, var(--svc-accent), rgba(var(--svc-rgb), .55));
    content: "";
    transform: translateX(-50%);
    transition: width .22s ease;
}
.svc-intro-highlights .card:hover,
.svc-service-card:hover,
.process-node:hover,
.why-cards .card:hover {
    transform: translateY(-6px);
    border-color: rgba(var(--svc-rgb), .42);
    box-shadow: 0 22px 42px rgba(var(--svc-rgb), .16);
}
.svc-intro-highlights .card:hover::before,
.svc-service-card:hover::before,
.process-node:hover::before,
.why-cards .card:hover::before { width: 56%; }
.svc-service-badge,
.svc-service-card .icon-wrap {
    background: linear-gradient(145deg, rgba(var(--svc-rgb), .08), rgba(var(--svc-rgb), .18));
    color: var(--svc-accent);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, .8);
    transition: transform .22s ease, background .22s ease;
}
.svc-service-badge i,
.svc-service-card .icon-wrap i { color: var(--svc-accent); }
.svc-service-card:hover .icon-wrap,
.svc-intro-highlights .card:hover .svc-service-badge { transform: translateY(-3px) scale(1.03); }
.process-node .step-num {
    background: linear-gradient(145deg, rgba(var(--svc-rgb), .1), rgba(var(--svc-rgb), .2));
    color: var(--svc-accent);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, .8);
}
.svc-feature-visual {
    transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
}
.svc-feature-visual:hover {
    transform: translateY(-4px);
    border-color: rgba(18, 103, 232, .3);
    box-shadow: 0 20px 42px rgba(18, 103, 232, .1);
}
.job-seeker-visual .bi-briefcase {
    background: rgba(13, 110, 253, .1) !important;
    color: #0d6efd !important;
}
.svc-palette-1 { --svc-accent: #1267e8; --svc-rgb: 18, 103, 232; }
.svc-palette-2 { --svc-accent: #e56f16; --svc-rgb: 229, 111, 22; }
.svc-palette-3 { --svc-accent: #7456d9; --svc-rgb: 116, 86, 217; }
.svc-palette-4 { --svc-accent: #078b68; --svc-rgb: 7, 139, 104; }
.svc-intro-highlights .card.svc-palette-1,
.svc-service-card.svc-palette-1,
.process-node.svc-palette-1,
.why-cards .card.svc-palette-1 { --svc-accent: #1267e8 !important; --svc-rgb: 18, 103, 232 !important; }
.svc-intro-highlights .card.svc-palette-2,
.svc-service-card.svc-palette-2,
.process-node.svc-palette-2,
.why-cards .card.svc-palette-2 { --svc-accent: #e56f16 !important; --svc-rgb: 229, 111, 22 !important; }
.svc-intro-highlights .card.svc-palette-3,
.svc-service-card.svc-palette-3,
.process-node.svc-palette-3,
.why-cards .card.svc-palette-3 { --svc-accent: #7456d9 !important; --svc-rgb: 116, 86, 217 !important; }
.svc-intro-highlights .card.svc-palette-4,
.svc-service-card.svc-palette-4,
.process-node.svc-palette-4,
.why-cards .card.svc-palette-4 { --svc-accent: #078b68 !important; --svc-rgb: 7, 139, 104 !important; }

/* Recruitment support cards: distinct accents make each benefit easy to scan. */
.why-cards .card {
    padding: 1.35rem !important;
    border-color: rgba(var(--svc-rgb), .18);
    background: linear-gradient(145deg, rgba(var(--svc-rgb), .1), rgba(255,255,255,.98) 58%);
}
.why-cards .why-card-head { display: flex; align-items: center; gap: .8rem; margin-bottom: 1rem; }
.why-cards .why-card-icon {
    width: 44px;
    height: 44px;
    display: inline-flex;
    flex: 0 0 auto;
    align-items: center;
    justify-content: center;
    border-radius: .8rem;
    background: rgba(var(--svc-rgb), .14);
    color: var(--svc-accent);
    font-size: 1.15rem;
}
.why-cards h6 { margin: 0; color: #193457; font-size: 1rem; font-weight: 700; line-height: 1.35; }
.why-cards .text-soft { color: #59718f !important; line-height: 1.6; }

/* Recruitment process: use vertical cards so each step has room to breathe. */
.process-track {
    grid-template-columns: minmax(0, 1fr) 52px minmax(0, 1fr) 52px minmax(0, 1fr) 52px minmax(0, 1fr);
    gap: .75rem;
}
.process-node {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: .9rem;
    min-height: 208px;
    padding: 1.35rem;
    border-color: rgba(var(--svc-rgb), .2);
    background: linear-gradient(150deg, rgba(var(--svc-rgb), .1), #ffffff 55%);
}
.process-node .step-num {
    width: auto;
    min-width: 0;
    height: auto;
    min-height: 0;
    padding: .42rem .68rem;
    border-radius: 999px;
    font-size: .82rem;
    letter-spacing: .04em;
}
.process-node h5 { margin-bottom: .4rem !important; color: #193457; font-size: 1.12rem; font-weight: 700; }
.process-node .text-soft { color: #59718f !important; line-height: 1.6; }
.process-line { height: 3px; border-radius: 999px; background: linear-gradient(90deg, rgba(13,110,253,.16), rgba(13,110,253,.42), rgba(13,110,253,.16)); }
@media (max-width: 991.98px) {
    .process-node { min-height: 0; }
}

/* Employer services: a distinct, editorial-style service panel */
.employer-services .container {
    position: relative;
    padding: clamp(1.5rem, 3vw, 3rem);
    overflow: hidden;
    border: 1px solid #d9e6f6;
    border-radius: 1.5rem;
    background:
        radial-gradient(circle at 88% 14%, rgba(13, 110, 253, .12), transparent 30%),
        linear-gradient(130deg, #fbfdff 0%, #f3f8ff 55%, #e7f1ff 100%);
    box-shadow: 0 18px 44px rgba(31, 72, 122, .07);
}
.employer-services h3 {
    margin-bottom: 1rem;
    color: #132e52;
    font-size: clamp(1.8rem, 2.4vw, 2.35rem);
    font-weight: 700;
}
.employer-services h3::before {
    display: block;
    margin-bottom: .7rem;
    color: #0d6efd;
    font-size: .74rem;
    font-weight: 700;
    letter-spacing: .12em;
    text-transform: uppercase;
    content: "For employers";
}
.employer-services .col-lg-6 > p {
    max-width: 56ch;
    color: #526b88 !important;
    font-size: 1.05rem;
    line-height: 1.7;
}
.employer-services ul {
    display: grid;
    gap: .65rem;
    margin: 1.5rem 0 1.75rem;
    padding: 0;
    list-style: none;
}
.employer-services li {
    position: relative;
    padding: .72rem .85rem .72rem 2.45rem;
    border: 1px solid rgba(13, 110, 253, .1);
    border-radius: .7rem;
    background: rgba(255, 255, 255, .74);
    color: #254464;
    font-weight: 600;
}
.employer-services li::before {
    position: absolute;
    top: 50%;
    left: .8rem;
    width: 1.1rem;
    height: 1.1rem;
    border-radius: 50%;
    background: #e2efff;
    color: #0d6efd;
    content: "✓";
    font-size: .72rem;
    font-weight: 800;
    line-height: 1.1rem;
    text-align: center;
    transform: translateY(-50%);
}
.employer-services .btn-primary {
    box-shadow: 0 12px 24px rgba(13, 110, 253, .22);
}
.employer-services .svc-feature-visual {
    padding: clamp(1rem, 2.2vw, 1.5rem);
    border-color: rgba(13, 110, 253, .16);
    border-radius: 1.2rem;
    background: linear-gradient(145deg, #e1efff, #c8e0ff);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, .72);
}
.employer-services .svc-feature-visual .tv-card {
    border-color: rgba(13, 110, 253, .12);
    border-radius: 1rem;
    box-shadow: 0 15px 32px rgba(26, 77, 139, .1);
}
.employer-services .tv-card .mb-2 > .d-flex {
    padding: .55rem;
    border-radius: .7rem;
    transition: background-color .2s ease, transform .2s ease;
}
.employer-services .tv-card .mb-2 > .d-flex:hover {
    background: #f2f7ff;
    transform: translateX(3px);
}
.employer-services .candidate-row {
    display: grid !important;
    grid-template-columns: minmax(0, 1fr) auto;
    align-items: center !important;
    min-height: 74px;
}
.employer-services .candidate-row > .d-flex { min-width: 0; }
.employer-services .tv-card .mb-2 > .d-flex:nth-child(2) .avatar { background: linear-gradient(135deg, #e56f16, #c95709); }
.employer-services .tv-card .mb-2 > .d-flex:nth-child(3) .avatar { background: linear-gradient(135deg, #7456d9, #5440ad); }

/* Job seeker services: matching visual weight with a career-focused accent */
.job-seeker-services .container {
    position: relative;
    padding: clamp(1.5rem, 3vw, 3rem);
    overflow: hidden;
    border: 1px solid #dce5f5;
    border-radius: 1.5rem;
    background:
        radial-gradient(circle at 8% 16%, rgba(13, 110, 253, .1), transparent 30%),
        linear-gradient(130deg, #fbfdff 0%, #f3f8ff 55%, #e7f1ff 100%);
    box-shadow: 0 18px 44px rgba(31, 72, 122, .07);
}
.job-seeker-services .job-seeker-visual {
    border-color: rgba(13, 110, 253, .16);
    background: linear-gradient(145deg, #e1efff, #c8e0ff);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, .78);
}
.job-seeker-services .job-seeker-visual .tv-card {
    border-color: rgba(13, 110, 253, .12);
    box-shadow: 0 15px 32px rgba(26, 77, 139, .1);
}
.job-seeker-services .job-seeker-visual .avatar {
    background: linear-gradient(135deg, #1674f5, #0a58ca);
}
.job-seeker-services .job-seeker-visual .tv-card {
    display: grid;
    gap: 1rem;
    padding: clamp(1rem, 2vw, 1.5rem);
}
.job-seeker-services .job-profile-summary {
    display: flex;
    align-items: center;
    gap: .85rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e5edf8;
}
.job-seeker-services .job-profile-summary .avatar { flex: 0 0 auto; }
.job-seeker-services .job-profile-summary .fw-semibold { color: #172f55; }
.job-seeker-services .job-role-card {
    display: flex;
    align-items: center;
    gap: .85rem;
    padding: 1rem;
    border: 1px solid #cfe1fb;
    border-radius: .9rem;
    background: #f5f9ff;
}
.job-seeker-services .job-role-icon {
    width: 48px;
    height: 48px;
    display: inline-flex;
    flex: 0 0 auto;
    align-items: center;
    justify-content: center;
    border-radius: .75rem;
    background: linear-gradient(145deg, rgba(229,111,22,.1), rgba(229,111,22,.2));
    color: #e56f16;
    font-size: 1.25rem;
}
.job-seeker-services .job-role-label {
    margin-bottom: .18rem;
    color: #547096;
    font-size: .7rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
}
.job-seeker-services .job-profile-actions {
    display: grid;
    grid-template-columns: 1.2fr .9fr;
    gap: .7rem;
    padding-top: .15rem;
}
.job-seeker-services .job-profile-actions .btn {
    min-width: 0;
    padding: .7rem .75rem;
    font-weight: 600;
}
.job-seeker-services .job-seeker-copy h3 {
    margin-bottom: 1rem;
    color: #1d3155;
    font-size: clamp(1.8rem, 2.4vw, 2.35rem);
    font-weight: 700;
}
.job-seeker-services .job-seeker-copy h3::before {
    display: block;
    margin-bottom: .7rem;
    color: #0d6efd;
    font-size: .74rem;
    font-weight: 700;
    letter-spacing: .12em;
    text-transform: uppercase;
    content: "For job seekers";
}
.job-seeker-services .job-seeker-copy > p {
    max-width: 56ch;
    color: #526b88 !important;
    font-size: 1.05rem;
    line-height: 1.7;
}
.job-seeker-services .job-seeker-copy ul {
    display: grid;
    gap: .65rem;
    margin: 1.5rem 0 1.75rem;
}
.job-seeker-services .job-seeker-copy li {
    padding: .72rem .85rem .72rem 2.45rem;
    border: 1px solid rgba(13, 110, 253, .1);
    border-radius: .7rem;
    background: rgba(255, 255, 255, .76);
    color: #354967;
    font-weight: 600;
}
.job-seeker-services .job-seeker-copy li::before {
    top: 50%;
    left: .8rem;
    width: 1.1rem;
    height: 1.1rem;
    border-radius: 50%;
    background: #e2efff;
    color: #0d6efd;
    font-size: .72rem;
    line-height: 1.1rem;
    text-align: center;
    transform: translateY(-50%);
}
.job-seeker-services .job-seeker-copy li:nth-child(2)::before { background: rgba(229,111,22,.13); color: #e56f16; }
.job-seeker-services .job-seeker-copy li:nth-child(3)::before { background: rgba(116,86,217,.13); color: #7456d9; }
.job-seeker-services .job-seeker-copy li:nth-child(4)::before { background: rgba(7,139,104,.13); color: #078b68; }
.job-seeker-services .btn-outline-secondary {
    border-color: rgba(13, 110, 253, .35);
    color: #0d6efd;
}
.job-seeker-services .btn-outline-secondary:hover,
.job-seeker-services .btn-outline-secondary:focus {
    border-color: #0d6efd;
    background: #0d6efd;
    color: #ffffff;
}

@media (max-width: 575.98px) {
    .employer-services .container { padding: 1.25rem; border-radius: 1.1rem; }
    .job-seeker-services .container { padding: 1.25rem; border-radius: 1.1rem; }
    .job-seeker-services .job-profile-actions { grid-template-columns: 1fr; }
}

@media (prefers-reduced-motion: reduce) {
    .svc-intro-highlights .card,
    .svc-service-card,
    .process-node,
    .why-cards .card,
    .svc-feature-visual,
    .svc-service-badge,
    .svc-service-card .icon-wrap { transition: none; }
}

@media (max-width: 991.98px) {
    .svc-hero { padding: 2rem 0 2.5rem; }
    .svc-hero .section-title { max-width: 18ch; }
    .svc-hero-card .talent-visual { display: grid; grid-template-columns: minmax(0, 1fr) minmax(130px, .48fr); }
    .svc-match-board { grid-template-columns: minmax(0, 1fr) 160px; }
}

@media (max-width: 575.98px) {
    .svc-hero-panel { border-radius: 1.1rem; }
    .svc-hero .section-title { font-size: clamp(2.1rem, 10vw, 2.8rem); }
    .svc-hero-card .talent-visual { display: flex; flex-direction: column; }
    .svc-match-board { grid-template-columns: 1fr; }
}
</style>

<main class="flex-grow-1">

    <!-- HERO -->
    <section class="svc-hero">
        <div class="container">
            <div class="svc-hero-panel p-3 p-lg-4">
            <div class="row hero-row g-4 align-items-center p-2 p-lg-4">
                <div class="col-lg-6">
                    <p class="svc-eyebrow text-uppercase text-primary fw-semibold mb-2">OUR SERVICES</p>
                    <h1 class="section-title">Recruitment Solutions That Connect Talent With Opportunity</h1>
                    <p class="lead-muted mt-2">Career Grow Infotech provides professional recruitment and career support services designed to connect organizations with suitable talent and help candidates discover meaningful career opportunities.</p>
                    <div class="hero-actions d-flex gap-2 mt-4">
                        <a href="jobs.php" class="btn btn-primary">Explore Jobs</a>
                        <a href="contact.php" class="btn btn-outline-primary">Contact Us</a>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="svc-hero-card p-4">
                    <div class="svc-match-board">
                        <div class="tv-card svc-match-primary">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="profile">
                                    <div class="avatar">CG</div>
                                    <div>
                                        <div class="fw-semibold">Talent profile</div>
                                        <div class="text-soft small">Skills, experience and role needs</div>
                                    </div>
                                </div>
                            </div>

                            <div class="skills mt-3">
                                <span class="skill">Skills</span>
                                <span class="skill">Experience</span>
                                <span class="skill">Role fit</span>
                            </div>

                            <div class="mt-3 d-flex justify-content-between align-items-center">
                                <div class="text-muted small">Support: end-to-end</div>
                                <div class="badge bg-soft text-primary rounded-pill">Aligned</div>
                            </div>
                        </div>

                        <div class="tv-connector" aria-hidden="true"></div>

                        <div class="tv-small svc-match-process">
                            <div class="d-flex align-items-start gap-2">
                                <i class="bi bi-briefcase fs-4 text-primary"></i>
                                <div>
                                    <div class="fw-semibold">Structured support</div>
                                    <div class="text-soft small">Role discovery and candidate screening</div>
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
                    <div class="card svc-palette-1 p-3 h-100">
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
                    <div class="card svc-palette-2 p-3 h-100">
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
                    <div class="card svc-palette-3 p-3 h-100">
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

                foreach ($services as $serviceIndex => $s) {
                    ?>
                        <div class="col-lg-3 col-md-6">
                            <div class="card svc-service-card svc-palette-<?php echo ($serviceIndex % 4) + 1; ?> h-100 d-flex flex-column">
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
    <section class="py-5 svc-section employer-services">
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
                                <div class="d-flex align-items-center justify-content-between mb-2 candidate-row">
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

                                <div class="d-flex align-items-center justify-content-between mb-2 candidate-row">
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

                                <div class="d-flex align-items-center justify-content-between mb-2 candidate-row">
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
    <section class="py-5 bg-soft svc-section job-seeker-services">
        <div class="container">
            <div class="row align-items-center g-4">
                <div class="col-lg-6 order-lg-1 order-1">
                    <div class="svc-feature-visual job-seeker-visual d-flex align-items-center justify-content-center">
                        <div class="tv-card w-100 text-start">
                            <div class="job-profile-summary">
                                <div class="avatar">JS</div>
                                <div>
                                    <div class="fw-semibold">Your Profile</div>
                                    <div class="text-soft small">Showcase skills and explore roles</div>
                                </div>
                            </div>

                            <div class="job-role-card">
                                <div class="job-role-icon"><i class="bi bi-briefcase"></i></div>
                                <div>
                                    <div class="job-role-label">Suggested role</div>
                                    <div class="fw-semibold">Frontend Developer</div>
                                    <div class="text-soft small">JavaScript • React • 3+ yrs</div>
                                </div>
                            </div>

                            <div class="job-profile-actions">
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
                                <div class="process-node svc-palette-1">
                                    <div class="step-num">01</div>
                                    <div>
                                        <h5 class="mb-1">Understand</h5>
                                        <p class="text-soft mb-0">Understand employer or candidate requirements.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="d-none d-lg-block process-line" aria-hidden="true"></div>

                            <div class="process-step col-12 col-lg-auto">
                                <div class="process-node svc-palette-2">
                                    <div class="step-num">02</div>
                                    <div>
                                        <h5 class="mb-1">Identify</h5>
                                        <p class="text-soft mb-0">Identify relevant opportunities or potential talent.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="d-none d-lg-block process-line" aria-hidden="true"></div>

                            <div class="process-step col-12 col-lg-auto">
                                <div class="process-node svc-palette-3">
                                    <div class="step-num">03</div>
                                    <div>
                                        <h5 class="mb-1">Connect</h5>
                                        <p class="text-soft mb-0">Create meaningful connections between candidates and organizations.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="d-none d-lg-block process-line" aria-hidden="true"></div>

                            <div class="process-step col-12 col-lg-auto">
                                <div class="process-node svc-palette-4">
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
                    <div class="card svc-palette-1 p-3 h-100">
                        <div class="why-card-head">
                            <span class="why-card-icon"><i class="bi bi-bullseye"></i></span>
                            <h6>Requirement-Focused Approach</h6>
                        </div>
                        <p class="text-soft mb-0">We focus on core role requirements to find relevant matches.</p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="card svc-palette-2 p-3 h-100">
                        <div class="why-card-head">
                            <span class="why-card-icon"><i class="bi bi-diagram-3"></i></span>
                            <h6>Skill-Based Matching</h6>
                        </div>
                        <p class="text-soft mb-0">Connect competencies with real role needs.</p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="card svc-palette-3 p-3 h-100">
                        <div class="why-card-head">
                            <span class="why-card-icon"><i class="bi bi-person-heart"></i></span>
                            <h6>Candidate-Centered Support</h6>
                        </div>
                        <p class="text-soft mb-0">Support candidates to present their experience effectively.</p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="card svc-palette-4 p-3 h-100">
                        <div class="why-card-head">
                            <span class="why-card-icon"><i class="bi bi-clipboard-check"></i></span>
                            <h6>Professional Coordination</h6>
                        </div>
                        <p class="text-soft mb-0">Clear communication and process coordination for all parties.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FINAL CTA -->
    <section class="final-cta svc-section">
        <div class="container">
            <div class="final-cta-card">
                <div class="row align-items-center g-4">
                    <div class="col-lg-8">
                        <p class="cta-eyebrow">Let’s grow together</p>
                        <h3>Ready to Take the Next Step?</h3>
                        <p class="text-soft mb-0">Whether you are looking for the right opportunity or the right talent, connect with Career Grow Infotech today.</p>
                    </div>
                    <div class="col-lg-4">
                        <div class="cta-actions">
                            <a href="jobs.php" class="btn btn-primary">Explore Jobs</a>
                            <a href="contact.php" class="btn btn-outline-primary">Contact Us</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
