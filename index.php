<?php
$pageTitle = 'Where Talent Meets Opportunities - Career Grow Infotech';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main>
    <section id="heroSlider" class="position-relative">
        <style>
            /* Inline slider styles (kept in index.php per task restrictions) */
            #heroSlider { overflow: hidden; }
            .slider { position: relative; width: 100%; height: 600px; min-height: 420px; }
            @media (max-width: 991.98px) { .slider { height: 540px; } }
            @media (max-width: 767.98px) { .slider { height: 520px; min-height: 420px; } }
            .slide { position: absolute; inset: 0; background-size: cover; background-position: center; opacity: 0; transition: opacity 900ms ease; display: flex; align-items: center; }
            .slide.active { opacity: 1; z-index: 2; }
            .slide::before { content: ''; position: absolute; inset: 0; background: rgba(9,20,34,0.45); transition: transform 5s ease; }
            .slide .container { position: relative; z-index: 3; }
            .slide-content { max-width: 54%; color: var(--cg-white); }
            @media (max-width: 991.98px) { .slide-content { max-width: 90%; } }
            .eyebrow { display: inline-block; background: rgba(255,255,255,0.08); padding: .35rem .7rem; border-radius: .5rem; color: rgba(255,255,255,0.95); font-weight:700; letter-spacing: .04em; }
            .slide h2 { font-size: clamp(1.6rem, 3.2vw, 2.6rem); margin-top: 1rem; margin-bottom: .8rem; color: #fff; }
            .slide p.lead { color: rgba(255,255,255,0.9); margin-bottom: 1rem; }
            .slide .btn { margin-right: .5rem; }
            .slider-nav { position: absolute; left: 0; right: 0; bottom: 1.25rem; display:flex; justify-content:center; gap:.5rem; z-index:6 }
            .dot { width:10px; height:10px; border-radius:50%; background: rgba(255,255,255,0.35); cursor:pointer; transition: background .2s; }
            .dot.active { background: var(--cg-white); }
            .slider-arrows { position:absolute; top:50%; transform:translateY(-50%); left:1rem; right:1rem; display:flex; justify-content:space-between; z-index:6 }
            .slider-arrow { background: rgba(0,0,0,0.35); color: #fff; border: none; width:44px; height:44px; border-radius: .5rem; display:inline-flex; align-items:center; justify-content:center; cursor:pointer; }
            /* No logos inside hero per requirements; keep hero image area clean */
            /* Text animation states */
            .animate { opacity:0; transform: translateY(8px); transition: opacity .6s ease, transform .6s ease; }
            .animate.show { opacity:1; transform: none; }
            /* subtle zoom on active slide background */
            .slide-bg-zoom { transform: scale(1); transition: transform 8s ease; }
            .slide.active .slide-bg-zoom { transform: scale(1.05); }
            @media (prefers-reduced-motion: reduce) {
                .slide-bg-zoom, .slide { transition: none !important; }
                .animate { transition: none !important; }
            }
            /* spacing between hero and next sections */
            .hero-bottom-spacing { padding-bottom: 2.5rem; }
        </style>

        <div class="slider" role="region" aria-label="Homepage hero slider">
            <div class="slide" data-index="0" style="background: linear-gradient(120deg, rgba(6,34,72,0.7), rgba(10,64,103,0.6)), linear-gradient(180deg, rgba(3,7,18,0.25), rgba(3,7,18,0.25));">
                <div class="slide-bg-zoom" style="position:absolute;inset:0;background-image:linear-gradient(0deg, rgba(13,110,253,0.15), rgba(13,110,253,0.05));"></div>
                <div class="container">
                    <div class="slide-content">
                        <span class="eyebrow animate" data-anim-delay="200">BUILD YOUR CAREER</span>
                        <h2 class="animate" data-anim-delay="400">Find the Right Opportunity for Your Future</h2>
                        <p class="lead animate" data-anim-delay="600">Discover career opportunities that match your skills, experience and ambitions.</p>
                        <div class="animate" data-anim-delay="800">
                            <a href="jobs.php" class="btn btn-primary btn-lg">Explore Jobs</a>
                            <a href="register.php" class="btn btn-outline-light btn-lg">Register Now</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="slide" data-index="1" style="background: linear-gradient(120deg, rgba(27,43,92,0.7), rgba(8,39,76,0.6));">
                <div class="slide-bg-zoom" style="position:absolute;inset:0;background-image:linear-gradient(0deg, rgba(2,132,199,0.12), rgba(2,132,199,0.04));"></div>
                <div class="container">
                    <div class="slide-content">
                        <span class="eyebrow animate" data-anim-delay="200">GROW WITH OPPORTUNITY</span>
                        <h2 class="animate" data-anim-delay="400">Turn Your Skills Into Your Next Career Move</h2>
                        <p class="lead animate" data-anim-delay="600">Connect with opportunities where your skills can make a real difference.</p>
                        <div class="animate" data-anim-delay="800">
                            <a href="jobs.php" class="btn btn-primary btn-lg">Explore Jobs</a>
                            <a href="about.php" class="btn btn-outline-light btn-lg">Learn More</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="slide" data-index="2" style="background: linear-gradient(120deg, rgba(33,25,62,0.7), rgba(70,18,90,0.6));">
                <div class="slide-bg-zoom" style="position:absolute;inset:0;background-image:linear-gradient(0deg, rgba(99,102,241,0.10), rgba(99,102,241,0.02));"></div>
                <div class="container">
                    <div class="slide-content">
                        <span class="eyebrow animate" data-anim-delay="200">FIND THE RIGHT TALENT</span>
                        <h2 class="animate" data-anim-delay="400">Helping Businesses Connect With Skilled Professionals</h2>
                        <p class="lead animate" data-anim-delay="600">Career Grow Infotech helps organizations connect with capable professionals for the right opportunities.</p>
                        <div class="animate" data-anim-delay="800">
                            <a href="contact.php" class="btn btn-primary btn-lg">Contact Us</a>
                            <a href="services.php" class="btn btn-outline-light btn-lg">Our Services</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="slider-arrows">
                <button class="slider-arrow" id="prevSlide" aria-label="Previous slide"><i class="bi bi-chevron-left"></i></button>
                <button class="slider-arrow" id="nextSlide" aria-label="Next slide"><i class="bi bi-chevron-right"></i></button>
            </div>

            <div class="slider-nav" id="sliderDots" role="tablist" aria-label="Slide indicators"></div>
        </div>

        <!-- Floating Search removed from hero; search section placed below to avoid overlap -->

        <script>
            (function(){
                const slides = Array.from(document.querySelectorAll('#heroSlider .slide'));
                const reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                const dotsContainer = document.getElementById('sliderDots');
                let current = 0;
                let interval = null;
                const delay = 5500;

                function createDots(){
                    slides.forEach((s,i)=>{
                        const d = document.createElement('button');
                        d.className='dot';
                        d.type='button';
                        d.setAttribute('aria-label','Go to slide '+(i+1));
                        d.addEventListener('click', ()=> goTo(i));
                        dotsContainer.appendChild(d);
                    });
                }

                function updateDots(){
                    Array.from(dotsContainer.children).forEach((d,idx)=> d.classList.toggle('active', idx===current));
                }

                function resetAnim(slide){
                    const items = Array.from(slide.querySelectorAll('.animate'));
                    items.forEach(el=> el.classList.remove('show'));
                    // trigger reflow
                    void slide.offsetWidth;
                    if (reduceMotion) {
                        // reveal without animation
                        items.forEach(el=> el.classList.add('show'));
                        return;
                    }
                    items.forEach((el)=>{
                        const d = parseInt(el.getAttribute('data-anim-delay')||0,10);
                        setTimeout(()=> el.classList.add('show'), d);
                    });
                }

                function show(index){
                    slides.forEach((s,i)=>{
                        s.classList.toggle('active', i===index);
                    });
                    resetAnim(slides[index]);
                    updateDots();
                }

                function next(){ current = (current+1) % slides.length; show(current); }
                function prev(){ current = (current-1+slides.length) % slides.length; show(current); }
                function goTo(i){ current = i; show(current); restart(); }

                function start(){ if (!reduceMotion) interval = setInterval(next, delay); }
                function stop(){ if (interval) { clearInterval(interval); interval = null; } }
                function restart(){ stop(); start(); }

                // initialize
                createDots();
                show(0);
                start();

                // arrows
                document.getElementById('nextSlide').addEventListener('click', ()=>{ next(); restart(); });
                document.getElementById('prevSlide').addEventListener('click', ()=>{ prev(); restart(); });

                // pause on hover
                const sliderEl = document.querySelector('#heroSlider .slider');
                sliderEl.addEventListener('mouseenter', stop);
                sliderEl.addEventListener('mouseleave', start);

                // touch support (basic)
                let touchStartX=0; let touchEndX=0;
                sliderEl.addEventListener('touchstart', (e)=>{ touchStartX = e.changedTouches[0].screenX; stop(); });
                sliderEl.addEventListener('touchend', (e)=>{ touchEndX = e.changedTouches[0].screenX; if(touchEndX+30 < touchStartX) next(); if(touchEndX-30 > touchStartX) prev(); start(); });

                // accessibility: keyboard
                document.addEventListener('keydown', (e)=>{
                    if(e.key === 'ArrowLeft') { prev(); restart(); }
                    if(e.key === 'ArrowRight') { next(); restart(); }
                });
            })();
        </script>
    </section>

    <!-- Job Search Section -->
    <section class="search-section py-5" aria-label="Job search">
        <div class="container">
            <div class="search-header text-center mb-4 reveal">
                <p class="text-uppercase text-primary fw-semibold mb-2">FIND YOUR NEXT OPPORTUNITY</p>
                <h2 class="fw-bold mb-3">Start Your Job Search</h2>
                <p class="text-muted mb-0 mx-auto">Search for opportunities that match your skills, experience and career goals.</p>
            </div>

            <div class="search-card card rounded-soft shadow-sm border-0" id="searchCard">
                <div class="card-body p-3 p-md-4">
                    <form id="homeSearchForm" method="get" action="jobs.php" class="search-form">
                        <div class="row g-3 align-items-end">
                            <div class="col-lg-5">
                                <label for="keyword" class="form-label small mb-2 fw-semibold">Keyword</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search" aria-hidden="true"></i></span>
                                    <input id="keyword" name="keyword" type="search" class="form-control" placeholder="Job title, skills or keywords" aria-label="Job title, skills or keywords">
                                </div>
                            </div>

                            <div class="col-lg-3">
                                <label for="location" class="form-label small mb-2 fw-semibold">Location</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-geo-alt" aria-hidden="true"></i></span>
                                    <input id="location" name="location" type="text" class="form-control" placeholder="City or location" aria-label="City or location">
                                </div>
                            </div>

                            <div class="col-lg-2 d-grid">
                                <label for="submitSearch" class="form-label small mb-2 fw-semibold visually-hidden">Find Jobs</label>
                                <button id="submitSearch" type="submit" class="btn btn-primary btn-lg w-100">
                                    <i class="bi bi-search me-2" aria-hidden="true"></i>Find Jobs
                                </button>
                            </div>
                        </div>

                        <div class="row g-3 mt-1">
                            <div class="col-md-4">
                                <label for="job_type" class="form-label small mb-2 fw-semibold">Job Type</label>
                                <select id="job_type" name="job_type" class="form-select">
                                    <option value="">Select job type</option>
                                    <option value="Full Time">Full Time</option>
                                    <option value="Part Time">Part Time</option>
                                    <option value="Contract">Contract</option>
                                    <option value="Internship">Internship</option>
                                    <option value="Remote">Remote</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="salary" class="form-label small mb-2 fw-semibold">Monthly Salary</label>
                                <select id="salary" name="salary" class="form-select">
                                    <option value="">Select salary range</option>
                                    <option value="below_10">Below ₹10K</option>
                                    <option value="10_20">₹10K - ₹20K</option>
                                    <option value="20_30">₹20K - ₹30K</option>
                                    <option value="30_50">₹30K - ₹50K</option>
                                    <option value="50_plus">₹50K+</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="experience" class="form-label small mb-2 fw-semibold">Years of Experience</label>
                                <select id="experience" name="experience" class="form-select">
                                    <option value="">Select experience</option>
                                    <option value="fresher">Fresher</option>
                                    <option value="0-1">0-1 Years</option>
                                    <option value="1-3">1-3 Years</option>
                                    <option value="3-5">3-5 Years</option>
                                    <option value="5-8">5-8 Years</option>
                                    <option value="8_plus">8+ Years</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            (function(){
                const form = document.getElementById('homeSearchForm');
                if (!form) return;

                form.addEventListener('submit', function(){
                    const inputs = Array.from(form.elements).filter(el => el.name);
                    inputs.forEach(el => {
                        if ((el.tagName === 'SELECT' || el.type === 'text' || el.type === 'search') && !el.value) {
                            el.disabled = true;
                        }
                    });
                });

                const card = document.getElementById('searchCard');
                if (card) {
                    setTimeout(() => card.classList.add('visible'), 120);
                }
            })();
        </script>
    </section>

    <!-- How It Works -->
    <section class="how-it-works py-5">
        <div class="container">
            <div class="text-center mb-4">
                <p class="text-uppercase text-primary fw-semibold mb-2">HOW IT WORKS</p>
                <h2 class="fw-bold mb-2">Your Career Journey Starts Here</h2>
                <p class="text-muted mb-0">Follow a simple process to discover opportunities and move forward with confidence.</p>
            </div>

            <div class="how-steps d-flex flex-column flex-lg-row align-items-stretch gap-3 mt-4">
                <!-- Step 1 -->
                <div class="how-step card rounded-soft p-3 reveal" data-index="0">
                    <div class="d-flex align-items-start gap-3">
                        <div>
                            <div class="how-number">01</div>
                        </div>
                        <div>
                            <div class="how-icon bg-soft rounded-circle d-inline-flex align-items-center justify-content-center mb-2">
                                <i class="bi bi-person-plus text-primary" aria-hidden="true"></i>
                            </div>
                            <h5 class="mb-1"><a href="register.php">Create Your Profile</a></h5>
                            <p class="small text-muted mb-0">Register and build your professional profile with your skills, qualifications and career details.</p>
                        </div>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="how-step card rounded-soft p-3 reveal" data-index="1">
                    <div class="d-flex align-items-start gap-3">
                        <div>
                            <div class="how-number">02</div>
                        </div>
                        <div>
                            <div class="how-icon bg-soft rounded-circle d-inline-flex align-items-center justify-content-center mb-2">
                                <i class="bi bi-search text-primary" aria-hidden="true"></i>
                            </div>
                            <h5 class="mb-1"><a href="jobs.php">Explore Opportunities</a></h5>
                            <p class="small text-muted mb-0">Browse available job opportunities and use relevant search options to find suitable roles.</p>
                        </div>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="how-step card rounded-soft p-3 reveal" data-index="2">
                    <div class="d-flex align-items-start gap-3">
                        <div>
                            <div class="how-number">03</div>
                        </div>
                        <div>
                            <div class="how-icon bg-soft rounded-circle d-inline-flex align-items-center justify-content-center mb-2">
                                <i class="bi bi-file-earmark-person text-primary" aria-hidden="true"></i>
                            </div>
                            <h5 class="mb-1"><a href="jobs.php">Apply for Jobs</a></h5>
                            <p class="small text-muted mb-0">Review job details and submit your application for opportunities that match your profile.</p>
                        </div>
                    </div>
                </div>

                <!-- Step 4 -->
                <div class="how-step card rounded-soft p-3 reveal" data-index="3">
                    <div class="d-flex align-items-start gap-3">
                        <div>
                            <div class="how-number">04</div>
                        </div>
                        <div>
                            <div class="how-icon bg-soft rounded-circle d-inline-flex align-items-center justify-content-center mb-2">
                                <i class="bi bi-emoji-smile-upside-down text-primary" aria-hidden="true"></i>
                            </div>
                            <h5 class="mb-1">Move Forward</h5>
                            <p class="small text-muted mb-0">Stay connected with your applications and take the next step toward your career goals.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            // reveal steps with stagger; respect reduced motion
            (function(){
                const reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                const steps = Array.from(document.querySelectorAll('.how-steps .reveal'));
                if (reduce) { steps.forEach(s=> s.classList.add('show')); return; }
                const obs = new IntersectionObserver((entries, o)=>{
                    entries.forEach(entry=>{
                        if (entry.isIntersecting) {
                            const idx = parseInt(entry.target.getAttribute('data-index')||0,10);
                            setTimeout(()=> entry.target.classList.add('show'), idx * 90);
                            o.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.12 });
                steps.forEach(s=> obs.observe(s));
            })();
        </script>
    </section>

    <!-- Job Categories -->
    <section class="categories-section py-5">
        <div class="container">
            <div class="text-center mb-4">
                <p class="text-uppercase text-primary fw-semibold mb-2">EXPLORE OPPORTUNITIES</p>
                <h2 class="fw-bold mb-2">Explore Jobs by Category</h2>
                <p class="text-muted mb-0">Discover career opportunities across a range of professional fields and find roles that match your skills and interests.</p>
            </div>

            <div class="row g-4 mt-3">
                <!-- Category 1 -->
                <div class="col-12 col-md-6 col-lg-3">
                    <a href="jobs.php?category=IT%20%26%20Software" class="text-decoration-none">
                        <div class="card category-card h-100 p-3 reveal" data-index="0">
                            <div class="d-flex align-items-start gap-3">
                                <div class="cat-icon bg-soft rounded p-2 d-flex align-items-center justify-content-center">
                                    <i class="bi bi-laptop text-primary" aria-hidden="true" style="font-size:1.25rem"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1">IT &amp; Software</h5>
                                    <p class="small text-muted mb-2">Explore software, web, development and technology-related opportunities.</p>
                                    <span class="small fw-semibold text-primary">View Jobs <i class="bi bi-arrow-right ms-1"></i></span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Category 2 -->
                <div class="col-12 col-md-6 col-lg-3">
                    <a href="jobs.php?category=Sales%20%26%20Marketing" class="text-decoration-none">
                        <div class="card category-card h-100 p-3 reveal" data-index="1">
                            <div class="d-flex align-items-start gap-3">
                                <div class="cat-icon bg-soft rounded p-2 d-flex align-items-center justify-content-center">
                                    <i class="bi bi-graph-up text-primary" aria-hidden="true" style="font-size:1.25rem"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1">Sales &amp; Marketing</h5>
                                    <p class="small text-muted mb-2">Discover opportunities in sales, digital marketing, business development and marketing.</p>
                                    <span class="small fw-semibold text-primary">View Jobs <i class="bi bi-arrow-right ms-1"></i></span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Category 3 -->
                <div class="col-12 col-md-6 col-lg-3">
                    <a href="jobs.php?category=Human%20Resources" class="text-decoration-none">
                        <div class="card category-card h-100 p-3 reveal" data-index="2">
                            <div class="d-flex align-items-start gap-3">
                                <div class="cat-icon bg-soft rounded p-2 d-flex align-items-center justify-content-center">
                                    <i class="bi bi-people text-primary" aria-hidden="true" style="font-size:1.25rem"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1">Human Resources</h5>
                                    <p class="small text-muted mb-2">Explore HR, recruitment, talent acquisition and people-focused roles.</p>
                                    <span class="small fw-semibold text-primary">View Jobs <i class="bi bi-arrow-right ms-1"></i></span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Category 4 -->
                <div class="col-12 col-md-6 col-lg-3">
                    <a href="jobs.php?category=Finance%20%26%20Accounting" class="text-decoration-none">
                        <div class="card category-card h-100 p-3 reveal" data-index="3">
                            <div class="d-flex align-items-start gap-3">
                                <div class="cat-icon bg-soft rounded p-2 d-flex align-items-center justify-content-center">
                                    <i class="bi bi-currency-exchange text-primary" aria-hidden="true" style="font-size:1.25rem"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1">Finance &amp; Accounting</h5>
                                    <p class="small text-muted mb-2">Find opportunities in accounting, finance, banking and related functions.</p>
                                    <span class="small fw-semibold text-primary">View Jobs <i class="bi bi-arrow-right ms-1"></i></span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Category 5 -->
                <div class="col-12 col-md-6 col-lg-3">
                    <a href="jobs.php?category=Engineering" class="text-decoration-none">
                        <div class="card category-card h-100 p-3 reveal" data-index="4">
                            <div class="d-flex align-items-start gap-3">
                                <div class="cat-icon bg-soft rounded p-2 d-flex align-items-center justify-content-center">
                                    <i class="bi bi-hammer text-primary" aria-hidden="true" style="font-size:1.25rem"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1">Engineering</h5>
                                    <p class="small text-muted mb-2">Explore technical and engineering opportunities across different industries.</p>
                                    <span class="small fw-semibold text-primary">View Jobs <i class="bi bi-arrow-right ms-1"></i></span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Category 6 -->
                <div class="col-12 col-md-6 col-lg-3">
                    <a href="jobs.php?category=Customer%20Support" class="text-decoration-none">
                        <div class="card category-card h-100 p-3 reveal" data-index="5">
                            <div class="d-flex align-items-start gap-3">
                                <div class="cat-icon bg-soft rounded p-2 d-flex align-items-center justify-content-center">
                                    <i class="bi bi-headset text-primary" aria-hidden="true" style="font-size:1.25rem"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1">Customer Support</h5>
                                    <p class="small text-muted mb-2">Discover customer service, support and client relationship opportunities.</p>
                                    <span class="small fw-semibold text-primary">View Jobs <i class="bi bi-arrow-right ms-1"></i></span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Category 7 -->
                <div class="col-12 col-md-6 col-lg-3">
                    <a href="jobs.php?category=Operations%20%26%20Management" class="text-decoration-none">
                        <div class="card category-card h-100 p-3 reveal" data-index="6">
                            <div class="d-flex align-items-start gap-3">
                                <div class="cat-icon bg-soft rounded p-2 d-flex align-items-center justify-content-center">
                                    <i class="bi bi-kanban text-primary" aria-hidden="true" style="font-size:1.25rem"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1">Operations &amp; Management</h5>
                                    <p class="small text-muted mb-2">Explore operations, administration, coordination and management roles.</p>
                                    <span class="small fw-semibold text-primary">View Jobs <i class="bi bi-arrow-right ms-1"></i></span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Category 8 -->
                <div class="col-12 col-md-6 col-lg-3">
                    <a href="jobs.php?category=Other%20Opportunities" class="text-decoration-none">
                        <div class="card category-card h-100 p-3 reveal" data-index="7">
                            <div class="d-flex align-items-start gap-3">
                                <div class="cat-icon bg-soft rounded p-2 d-flex align-items-center justify-content-center">
                                    <i class="bi bi-briefcase text-primary" aria-hidden="true" style="font-size:1.25rem"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1">Other Opportunities</h5>
                                    <p class="small text-muted mb-2">Explore additional career opportunities across diverse professional fields.</p>
                                    <span class="small fw-semibold text-primary">View Jobs <i class="bi bi-arrow-right ms-1"></i></span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <script>
            // Reveal with stagger for category cards; respect reduced motion
            (function(){
                const reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                const cards = Array.from(document.querySelectorAll('.categories-section .reveal'));
                if (reduce) { cards.forEach(c=> c.classList.add('show')); return; }
                const obs = new IntersectionObserver((entries, o)=>{
                    entries.forEach(entry=>{
                        if (entry.isIntersecting) {
                            const index = parseInt(entry.target.getAttribute('data-index')||0,10);
                            setTimeout(()=> entry.target.classList.add('show'), index * 80);
                            o.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.12 });
                cards.forEach(c=> obs.observe(c));
            })();
        </script>
    </section>
    <!-- Latest Opportunities -->
    <section class="latest-jobs py-5">
        <div class="container">
            <div class="d-flex align-items-start justify-content-between mb-3">
                <div>
                    <p class="text-uppercase text-primary fw-semibold mb-2">LATEST OPPORTUNITIES</p>
                    <h2 class="fw-bold mb-0">Explore Our Latest Job Opportunities</h2>
                    <p class="text-muted mt-2 mb-0">Find relevant career opportunities and take the next step toward your professional goals.</p>
                </div>
                <div class="ms-3 align-self-center">
                    <a href="jobs.php" class="btn btn-outline-primary">View All Jobs</a>
                </div>
            </div>

            <div class="row g-4 mt-3">
                <?php
                $latestJobs = [];
                try {
                    require_once __DIR__ . '/includes/db.php';
                    $conn = getDbConnection();
                    $status = 'active';
                    $limit = 6;
                    $stmt = $conn->prepare("SELECT id, title, location, job_type, experience_level, salary_min, salary_max, last_date, created_at FROM jobs WHERE status = ? AND (last_date IS NULL OR last_date >= CURDATE()) ORDER BY created_at DESC LIMIT ?");
                    if ($stmt) {
                        $stmt->bind_param('si', $status, $limit);
                        $stmt->execute();

                        if (method_exists($stmt, 'get_result')) {
                            $res = $stmt->get_result();
                            while ($row = $res->fetch_assoc()) { $latestJobs[] = $row; }
                        } else {
                            $stmt->store_result();
                            $stmt->bind_result($id, $title, $location, $job_type, $experience_level, $salary_min, $salary_max, $last_date, $created_at);
                            while ($stmt->fetch()) {
                                $latestJobs[] = compact('id','title','location','job_type','experience_level','salary_min','salary_max','last_date','created_at');
                            }
                        }
                        $stmt->close();
                    } else {
                        error_log('Prepare failed (latest jobs): ' . $conn->error);
                    }
                    $conn->close();
                } catch (Exception $e) {
                    error_log('DB error (latest jobs): ' . $e->getMessage());
                    $latestJobs = [];
                }

                if (count($latestJobs) === 0): ?>
                    <div class="col-12">
                        <div class="card border-0 rounded-soft p-4 text-center">
                            <h5 class="mb-2">New Opportunities Coming Soon</h5>
                            <p class="text-muted mb-3">Current openings will appear here as soon as new opportunities are available.</p>
                            <a href="jobs.php" class="btn btn-primary">Browse Jobs</a>
                        </div>
                    </div>
                <?php else:
                    foreach ($latestJobs as $job): ?>
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="card job-card h-100 border-0 rounded-soft p-3 reveal">
                                <div class="d-flex flex-column h-100">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="mb-1"><a href="job-details.php?id=<?php echo (int)$job['id']; ?>"><?php echo htmlspecialchars($job['title'], ENT_QUOTES, 'UTF-8'); ?></a></h5>
                                        <span class="badge bg-soft text-primary rounded-pill small"><?php echo htmlspecialchars($job['job_type'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
                                    </div>

                                    <div class="text-muted small mb-2">
                                        <i class="bi bi-geo-alt me-1"></i><?php echo htmlspecialchars($job['location'] ?? '', ENT_QUOTES, 'UTF-8'); ?> • <?php echo htmlspecialchars($job['experience_level'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                    </div>

                                    <?php
                                    $min = isset($job['salary_min']) ? (float)$job['salary_min'] : 0;
                                    $max = isset($job['salary_max']) ? (float)$job['salary_max'] : 0;
                                    if ($min > 0 && $max > 0): ?>
                                        <div class="mb-2">Salary: ₹<?php echo htmlspecialchars(number_format($min), ENT_QUOTES, 'UTF-8'); ?> - ₹<?php echo htmlspecialchars(number_format($max), ENT_QUOTES, 'UTF-8'); ?> / month</div>
                                    <?php elseif ($min > 0): ?>
                                        <div class="mb-2">Salary: ₹<?php echo htmlspecialchars(number_format($min), ENT_QUOTES, 'UTF-8'); ?> / month</div>
                                    <?php endif; ?>

                                    <?php if (!empty($job['last_date'])): ?>
                                        <div class="small text-muted mb-3">Apply by: <?php echo htmlspecialchars(date('d M Y', strtotime($job['last_date'])), ENT_QUOTES, 'UTF-8'); ?></div>
                                    <?php endif; ?>

                                    <div class="mt-auto text-end">
                                        <a href="job-details.php?id=<?php echo (int)$job['id']; ?>" class="btn btn-sm btn-outline-primary">View Details</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach;
                endif; ?>
            </div>
        </div>
    </section>

    <!-- Introduction / Trust Section -->
    <section class="intro-trust py-5">
        <div class="container">
            <div class="row g-4 align-items-center">
                <div class="col-lg-6">
                    <div class="intro-content reveal">
                        <p class="text-uppercase text-primary fw-semibold small mb-2">ABOUT CAREER GROW INFOTECH</p>
                        <h2 class="fw-bold mb-3">Connecting Talent With the Right Opportunities</h2>
                        <p class="text-muted mb-3">Career Grow Infotech Pvt. Ltd. is focused on helping job seekers discover meaningful career opportunities while supporting businesses in finding the right talent for their growing teams.</p>
                        <p class="text-muted mb-3">Our approach combines career guidance, recruitment support and a strong understanding of employer requirements to create better connections between professionals and organizations.</p>
                        <div class="d-flex gap-3 mt-3">
                            <a href="jobs.php" class="btn btn-primary">Explore Opportunities</a>
                            <a href="about.php" class="btn btn-outline-primary">Learn More About Us</a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="trust-card card rounded-soft border shadow-sm p-4 reveal">
                        <div class="d-flex align-items-center mb-3 gap-3">
                            <div class="trust-icon bg-soft rounded-circle d-flex align-items-center justify-content-center" aria-hidden="true">
                                <i class="bi bi-briefcase-fill" style="font-size:1.6rem;color:var(--cg-primary)"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-semibold">Professional Recruitment Support</h5>
                                <p class="small text-muted mb-0">Candidate-focused career support and employer recruitment assistance.</p>
                            </div>
                        </div>

                        <div class="row g-2 mt-3">
                            <div class="col-12">
                                <div class="trust-feature d-flex gap-3 p-3 rounded-sm border align-items-start">
                                    <i class="bi bi-search fs-4 text-primary" aria-hidden="true"></i>
                                    <div>
                                        <h6 class="mb-1">Career Opportunities</h6>
                                        <p class="small text-muted mb-0">Discover roles aligned with your skills and goals.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="trust-feature d-flex gap-3 p-3 rounded-sm border align-items-start">
                                    <i class="bi bi-people fs-4 text-primary" aria-hidden="true"></i>
                                    <div>
                                        <h6 class="mb-1">Recruitment Support</h6>
                                        <p class="small text-muted mb-0">Helping organizations connect with suitable professionals.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="trust-feature d-flex gap-3 p-3 rounded-sm border align-items-start">
                                    <i class="bi bi-person-workspace fs-4 text-primary" aria-hidden="true"></i>
                                    <div>
                                        <h6 class="mb-1">Professional Guidance</h6>
                                        <p class="small text-muted mb-0">A structured approach to career and recruitment needs.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="trust-feature d-flex gap-3 p-3 rounded-sm border align-items-start">
                                    <i class="bi bi-globe fs-4 text-primary" aria-hidden="true"></i>
                                    <div>
                                        <h6 class="mb-1">Growing Network</h6>
                                        <p class="small text-muted mb-0">Connecting candidates and employers across India.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 text-end">
                            <img src="assets/images/logo.webp" alt="Career Grow Infotech logo" style="height:34px;opacity:0.9">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
            (function(){
                // reveal on scroll with IntersectionObserver, respect reduced motion
                const reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                if (reduce) { document.querySelectorAll('.reveal').forEach(el=> el.classList.add('show')); return; }
                const obs = new IntersectionObserver((entries, o)=>{
                    entries.forEach(e=>{
                        if (e.isIntersecting) {
                            e.target.classList.add('show');
                            o.unobserve(e.target);
                        }
                    });
                }, { threshold: 0.12 });
                document.querySelectorAll('.reveal').forEach(el=> obs.observe(el));
            })();
        </script>
    </section>

    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <p class="text-uppercase text-primary fw-semibold mb-2">Why Choose Us</p>
                <h2 class="fw-bold mb-3">We make hiring and job search simple</h2>
            </div>

            <div class="row g-4">
                <div class="col-md-3">
                    <div class="card h-100 border-0 rounded-soft p-3">
                        <div class="card-body p-3 text-center">
                            <i class="bi bi-award" style="font-size:1.6rem;color:var(--cg-primary)"></i>
                            <h5 class="mt-3 mb-2">Right Opportunities</h5>
                            <p class="text-muted small mb-0">Curated listings to match candidate skills and employer needs.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card h-100 border-0 rounded-soft p-3">
                        <div class="card-body p-3 text-center">
                            <i class="bi bi-people" style="font-size:1.6rem;color:var(--cg-primary)"></i>
                            <h5 class="mt-3 mb-2">Skilled Talent</h5>
                            <p class="text-muted small mb-0">Access to qualified candidates backed by clear profiles.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card h-100 border-0 rounded-soft p-3">
                        <div class="card-body p-3 text-center">
                            <i class="bi bi-life-preserver" style="font-size:1.6rem;color:var(--cg-primary)"></i>
                            <h5 class="mt-3 mb-2">Candidate Support</h5>
                            <p class="text-muted small mb-0">Guidance for profile building and application tracking.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card h-100 border-0 rounded-soft p-3">
                        <div class="card-body p-3 text-center">
                            <i class="bi bi-graph-up" style="font-size:1.6rem;color:var(--cg-primary)"></i>
                            <h5 class="mt-3 mb-2">Career Growth</h5>
                            <p class="text-muted small mb-0">Opportunities that support long-term professional development.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Job Seeker + Employer CTA -->
    <section class="cta-dual-section py-5">
        <div class="container">
            <div class="text-center mb-4">
                <p class="text-uppercase text-primary fw-semibold mb-2">GET STARTED</p>
                <h2 class="fw-bold mb-2">Ready for Your Next Opportunity?</h2>
                <p class="text-muted mb-0">Whether you are looking for your next career opportunity or searching for the right talent, Career Grow Infotech is here to help.</p>
            </div>

            <div class="row g-3 mt-4">
                <div class="col-md-6">
                    <div class="cta-panel card h-100 rounded-soft p-4 reveal" data-index="0">
                        <div class="d-flex align-items-start gap-3 h-100">
                            <div class="cta-icon me-2 d-flex align-items-center justify-content-center bg-soft rounded-circle" aria-hidden="true">
                                <i class="bi bi-person-badge text-primary" style="font-size:1.6rem"></i>
                            </div>
                            <div class="flex-fill">
                                <div class="small text-uppercase text-muted">FOR JOB SEEKERS</div>
                                <h4 class="fw-bold mb-2">Find Your Next Career Opportunity</h4>
                                <p class="text-muted">Explore available opportunities, discover roles that match your skills and take the next step in your professional journey.</p>
                                <div class="mt-3">
                                    <a href="jobs.php" class="btn btn-primary me-2">Explore Jobs</a>
                                    <a href="register.php" class="btn btn-outline-primary">Create Your Profile</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="cta-panel card h-100 rounded-soft p-4 reveal" data-index="1">
                        <div class="d-flex align-items-start gap-3 h-100">
                            <div class="cta-icon me-2 d-flex align-items-center justify-content-center bg-soft rounded-circle" aria-hidden="true">
                                <i class="bi bi-building text-primary" style="font-size:1.6rem"></i>
                            </div>
                            <div class="flex-fill">
                                <div class="small text-uppercase text-muted">FOR EMPLOYERS</div>
                                <h4 class="fw-bold mb-2">Find the Right Talent for Your Team</h4>
                                <p class="text-muted">Connect with professionals and get recruitment support for your organization's hiring requirements.</p>
                                <div class="mt-3">
                                    <a href="contact.php" class="btn btn-primary me-2">Contact Us</a>
                                    <a href="services.php" class="btn btn-outline-primary">Explore Services</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
            // reveal panels with small stagger; respect reduced motion
            (function(){
                const reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                const panels = Array.from(document.querySelectorAll('.cta-dual-section .reveal'));
                if (reduce) { panels.forEach(p=> p.classList.add('show')); return; }
                panels.forEach((p,i)=> setTimeout(()=> p.classList.add('show'), i * 120));
            })();
        </script>
    </section>

    <!-- Recruitment Services -->
    <section class="recruitment-services py-5">
        <div class="container">
            <div class="text-center mb-4">
                <p class="text-uppercase text-primary fw-semibold mb-2">OUR RECRUITMENT SERVICES</p>
                <h2 class="fw-bold mb-2">Recruitment Support Built Around Your Hiring Needs</h2>
                <p class="text-muted mb-0">From understanding hiring requirements to connecting organizations with suitable professionals, our recruitment approach is focused on creating better talent connections.</p>
            </div>

            <div class="row g-4 mt-3">
                <div class="col-md-6 col-lg-4">
                    <div class="card service-card h-100 p-3 reveal" data-index="0">
                        <div class="d-flex gap-3">
                            <div class="service-icon bg-soft rounded d-flex align-items-center justify-content-center">
                                <i class="bi bi-people-fill text-primary" aria-hidden="true" style="font-size:1.25rem"></i>
                            </div>
                            <div>
                                <h5 class="mb-1">Recruitment Assistance</h5>
                                <p class="small text-muted mb-2">Support organizations in identifying and connecting with professionals for their hiring requirements.</p>
                                <a href="contact.php" class="small fw-semibold text-primary">Get in Touch <i class="bi bi-arrow-right ms-1"></i></a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="card service-card h-100 p-3 reveal" data-index="1">
                        <div class="d-flex gap-3">
                            <div class="service-icon bg-soft rounded d-flex align-items-center justify-content-center">
                                <i class="bi bi-search text-primary" aria-hidden="true" style="font-size:1.25rem"></i>
                            </div>
                            <div>
                                <h5 class="mb-1">Talent Sourcing</h5>
                                <p class="small text-muted mb-2">Help identify potential candidates based on relevant skills, experience and role requirements.</p>
                                <a href="contact.php" class="small fw-semibold text-primary">Get in Touch <i class="bi bi-arrow-right ms-1"></i></a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="card service-card h-100 p-3 reveal" data-index="2">
                        <div class="d-flex gap-3">
                            <div class="service-icon bg-soft rounded d-flex align-items-center justify-content-center">
                                <i class="bi bi-card-checklist text-primary" aria-hidden="true" style="font-size:1.25rem"></i>
                            </div>
                            <div>
                                <h5 class="mb-1">Candidate Screening</h5>
                                <p class="small text-muted mb-2">Review candidate profiles against relevant requirements to support a more focused recruitment process.</p>
                                <a href="contact.php" class="small fw-semibold text-primary">Get in Touch <i class="bi bi-arrow-right ms-1"></i></a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="card service-card h-100 p-3 reveal" data-index="3">
                        <div class="d-flex gap-3">
                            <div class="service-icon bg-soft rounded d-flex align-items-center justify-content-center">
                                <i class="bi bi-tools text-primary" aria-hidden="true" style="font-size:1.25rem"></i>
                            </div>
                            <div>
                                <h5 class="mb-1">Skill-Based Matching</h5>
                                <p class="small text-muted mb-2">Connect candidate capabilities and experience with suitable professional opportunities.</p>
                                <a href="contact.php" class="small fw-semibold text-primary">Get in Touch <i class="bi bi-arrow-right ms-1"></i></a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="card service-card h-100 p-3 reveal" data-index="4">
                        <div class="d-flex gap-3">
                            <div class="service-icon bg-soft rounded d-flex align-items-center justify-content-center">
                                <i class="bi bi-people text-primary" aria-hidden="true" style="font-size:1.25rem"></i>
                            </div>
                            <div>
                                <h5 class="mb-1">Hiring Support</h5>
                                <p class="small text-muted mb-2">Provide structured recruitment support throughout the candidate and employer interaction process.</p>
                                <a href="contact.php" class="small fw-semibold text-primary">Get in Touch <i class="bi bi-arrow-right ms-1"></i></a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="card service-card h-100 p-3 reveal" data-index="5">
                        <div class="d-flex gap-3">
                            <div class="service-icon bg-soft rounded d-flex align-items-center justify-content-center">
                                <i class="bi bi-people-fill text-primary" aria-hidden="true" style="font-size:1.25rem"></i>
                            </div>
                            <div>
                                <h5 class="mb-1">Workforce Requirements</h5>
                                <p class="small text-muted mb-2">Understand organizational hiring needs and support the search for suitable professionals.</p>
                                <a href="contact.php" class="small fw-semibold text-primary">Get in Touch <i class="bi bi-arrow-right ms-1"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            // reveal services with stagger; respect reduced motion
            (function(){
                const reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                const cards = Array.from(document.querySelectorAll('.recruitment-services .reveal'));
                if (reduce) { cards.forEach(c=> c.classList.add('show')); return; }
                const obs = new IntersectionObserver((entries, o)=>{
                    entries.forEach(entry=>{
                        if (entry.isIntersecting) {
                            const idx = parseInt(entry.target.getAttribute('data-index')||0,10);
                            setTimeout(()=> entry.target.classList.add('show'), idx * 80);
                            o.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.12 });
                cards.forEach(c=> obs.observe(c));
            })();
        </script>
    </section>

    <!-- Career Growth -->
    <section class="career-growth py-5">
        <div class="container">
            <div class="row g-4 align-items-center">
                <div class="col-lg-5">
                    <div class="career-growth-visual card border-0 rounded-soft p-4 reveal" data-index="0">
                        <div class="d-flex align-items-center mb-3 gap-3">
                            <div class="career-growth-icon bg-soft rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-graph-up-arrow text-primary" aria-hidden="true"></i>
                            </div>
                            <div>
                                <p class="text-uppercase text-primary fw-semibold small mb-0">BUILD YOUR CAREER</p>
                            </div>
                        </div>

                        <div class="career-quote-box p-3 rounded-soft mb-3">
                            <p class="mb-0 fw-medium">"Your next career opportunity can start with the right search."</p>
                        </div>

                        <div class="career-growth-badge d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill bg-soft text-primary fw-semibold">
                            <i class="bi bi-stars" aria-hidden="true"></i>
                            <span>Career Growth</span>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="career-heading reveal" data-index="1">
                        <p class="text-uppercase text-primary fw-semibold mb-2">BUILD YOUR CAREER</p>
                        <h2 class="fw-bold mb-2">Take the Next Step in Your Career</h2>
                        <p class="text-muted mb-0">Your skills, experience and goals can open the door to new opportunities. Explore relevant roles and keep moving forward in your professional journey.</p>
                    </div>

                    <div class="career-points mt-4">
                        <div class="career-point d-flex align-items-start gap-3 p-3 rounded-soft reveal" data-index="2">
                            <div class="career-point-icon bg-soft rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-lightbulb text-primary" aria-hidden="true"></i>
                            </div>
                            <div>
                                <h5 class="mb-1">Discover Your Strengths</h5>
                                <p class="small text-muted mb-0">Understand your skills, qualifications and experience to identify suitable career directions.</p>
                            </div>
                        </div>

                        <div class="career-point d-flex align-items-start gap-3 p-3 rounded-soft reveal" data-index="3">
                            <div class="career-point-icon bg-soft rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-search-heart text-primary" aria-hidden="true"></i>
                            </div>
                            <div>
                                <h5 class="mb-1">Explore Relevant Roles</h5>
                                <p class="small text-muted mb-0">Search opportunities based on your preferred role, location, experience and career interests.</p>
                            </div>
                        </div>

                        <div class="career-point d-flex align-items-start gap-3 p-3 rounded-soft reveal" data-index="4">
                            <div class="career-point-icon bg-soft rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-person-check text-primary" aria-hidden="true"></i>
                            </div>
                            <div>
                                <h5 class="mb-1">Keep Your Profile Updated</h5>
                                <p class="small text-muted mb-0">Maintain accurate professional information so your profile represents your current capabilities.</p>
                            </div>
                        </div>

                        <div class="career-point d-flex align-items-start gap-3 p-3 rounded-soft reveal" data-index="5">
                            <div class="career-point-icon bg-soft rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-arrow-up-right-circle text-primary" aria-hidden="true"></i>
                            </div>
                            <div>
                                <h5 class="mb-1">Keep Growing</h5>
                                <p class="small text-muted mb-0">Continue developing your skills and exploring opportunities that support your long-term career goals.</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 d-flex flex-wrap gap-3">
                        <a href="jobs.php" class="btn btn-primary">Explore Jobs</a>
                        <a href="register.php" class="btn btn-outline-primary">Create Your Profile</a>
                    </div>
                </div>
            </div>
        </div>

        <script>
            (function(){
                const reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                const items = Array.from(document.querySelectorAll('.career-growth .reveal'));
                if (reduce) { items.forEach(el => el.classList.add('show')); return; }
                const obs = new IntersectionObserver((entries, observer) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            const idx = parseInt(entry.target.getAttribute('data-index') || '0', 10);
                            setTimeout(() => entry.target.classList.add('show'), idx * 80);
                            observer.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.12 });
                items.forEach(el => obs.observe(el));
            })();
        </script>
    </section>

    <!-- Locations -->
    <section class="locations-section py-5">
        <div class="container">
            <div class="text-center mb-4">
                <p class="text-uppercase text-primary fw-semibold mb-2">OUR LOCATIONS</p>
                <h2 class="fw-bold mb-2">Connecting Opportunities Across India</h2>
                <p class="text-muted mb-0">Career Grow Infotech Pvt. Ltd. supports career and recruitment needs with a professional presence in Pune and Chhatrapati Sambhajinagar.</p>
            </div>

            <div class="row g-4 mt-3">
                <div class="col-md-6">
                    <div class="location-card card h-100 p-4 reveal" data-index="0">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="location-icon bg-soft rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-geo-alt-fill text-primary" aria-hidden="true"></i>
                            </div>
                            <div>
                                <h4 class="mb-1">Pune</h4>
                                <p class="small text-muted mb-0">Career &amp; Recruitment Support</p>
                            </div>
                        </div>
                        <p class="text-muted mb-0">Connect with Career Grow Infotech for professional career opportunities and recruitment-related requirements.</p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="location-card card h-100 p-4 reveal" data-index="1">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="location-icon bg-soft rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-geo-alt-fill text-primary" aria-hidden="true"></i>
                            </div>
                            <div>
                                <h4 class="mb-1">Chhatrapati Sambhajinagar</h4>
                                <p class="small text-muted mb-0">Career &amp; Recruitment Support</p>
                            </div>
                        </div>
                        <p class="text-muted mb-0">Connect with Career Grow Infotech for professional career opportunities and recruitment-related requirements.</p>
                    </div>
                </div>
            </div>

            <div class="contact-strip card rounded-soft border shadow-sm mt-4 p-3 reveal" data-index="2">
                <div class="row g-3 align-items-center text-center text-md-start">
                    <div class="col-md-4">
                        <div class="d-flex align-items-center justify-content-center justify-content-md-start gap-2">
                            <i class="bi bi-telephone text-primary"></i>
                            <span><a href="tel:+919850340340" class="text-decoration-none">+91 98503 40340</a></span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center justify-content-center justify-content-md-start gap-2">
                            <i class="bi bi-envelope text-primary"></i>
                            <span><a href="mailto:info@careergrowinfotech.com" class="text-decoration-none">info@careergrowinfotech.com</a></span>
                        </div>
                    </div>
                    <div class="col-md-4 text-center text-md-end">
                        <div class="d-flex justify-content-center justify-content-md-end gap-2 flex-wrap">
                            <a href="contact.php" class="btn btn-primary btn-sm">Contact Us</a>
                            <a href="jobs.php" class="btn btn-outline-primary btn-sm">Explore Jobs</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            (function(){
                const reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                const items = Array.from(document.querySelectorAll('.locations-section .reveal'));
                if (reduce) { items.forEach(el => el.classList.add('show')); return; }
                const obs = new IntersectionObserver((entries, observer) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            const idx = parseInt(entry.target.getAttribute('data-index') || '0', 10);
                            setTimeout(() => entry.target.classList.add('show'), idx * 80);
                            observer.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.12 });
                items.forEach(el => obs.observe(el));
            })();
        </script>
    </section>

    <section class="final-cta-section py-5">
        <div class="container">
            <div class="final-cta-card card border-0 reveal text-center" data-index="0">
                <div class="final-cta-inner">
                    <p class="text-uppercase text-primary fw-semibold mb-3">START YOUR NEXT CHAPTER</p>
                    <h2 class="fw-bold mb-3">Your Next Opportunity Starts With the Right Step</h2>
                    <p class="final-cta-copy mx-auto mb-4">Explore career opportunities, build your professional profile or connect with us for recruitment support.</p>

                    <div class="d-flex flex-column flex-sm-row justify-content-center align-items-center gap-3">
                        <a href="jobs.php" class="btn btn-primary btn-lg final-cta-primary">Explore Jobs</a>
                        <a href="contact.php" class="btn btn-outline-primary btn-lg final-cta-secondary">Contact Us</a>
                    </div>

                    <p class="final-cta-note mt-4 mb-0">Career opportunities for professionals. Recruitment support for organizations.</p>
                </div>
            </div>
        </div>

        <script>
            (function(){
                const reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                const items = Array.from(document.querySelectorAll('.final-cta-section .reveal'));
                if (reduce) { items.forEach(el => el.classList.add('show')); return; }
                const obs = new IntersectionObserver((entries, observer) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            const idx = parseInt(entry.target.getAttribute('data-index') || '0', 10);
                            setTimeout(() => entry.target.classList.add('show'), idx * 80);
                            observer.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.12 });
                items.forEach(el => obs.observe(el));
            })();
        </script>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
