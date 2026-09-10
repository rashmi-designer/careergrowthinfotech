<?php
// Admin header include (UI-only). Expects $pageH1 and $pageSubtitle set in the including page.
$adminName = htmlspecialchars($_SESSION['admin_name'] ?? $_SESSION['user_name'] ?? 'Administrator', ENT_QUOTES, 'UTF-8');
?>
<style>
/* Admin Dark Mode CSS Variables */
html[data-theme="dark"] {
    --cg-primary: #0d6efd;
    --cg-accent: #e5e7eb;
    --cg-text: #e5e7eb;
    --cg-muted: #9ca3af;
    --cg-light: #1f2937;
    --cg-border: #374151;
    --cg-white: #111827;
    --cg-bg: #0f172a;
    --cg-shadow: 0 14px 30px rgba(0, 0, 0, 0.3);
    --cg-shadow-soft: 0 10px 22px rgba(0, 0, 0, 0.2);
}

/* Admin Header UI */
.admin-topbar { display:flex; align-items:center; justify-content:space-between; gap:1rem; padding:0.6rem 1rem; background:transparent; border-bottom:1px solid rgba(15,23,42,0.04); flex-wrap:wrap; transition: border-color 0.25s ease; }
html[data-theme="dark"] .admin-topbar { border-bottom-color: rgba(255,255,255,0.1); }

.admin-topbar .title-area { display:flex; flex-direction:column; }
.admin-topbar .title-area h1 { margin:0; font-size:1.15rem; font-weight:700; color: var(--cg-text); transition: color 0.25s ease; }
.admin-topbar .title-area .subtitle { font-size:0.9rem; color:var(--cg-muted); transition: color 0.25s ease; }
.admin-topbar .utils { display:flex; align-items:center; gap:0.75rem }
.header-search { width:260px; max-width:38vw; height:42px; display:inline-flex; align-items:center; gap:8px; padding:6px 10px; background:var(--cg-white); border:1px solid var(--cg-border); border-radius:10px; transition: background 0.25s ease, border-color 0.25s ease, color 0.25s ease; }
.header-search input { border:0; outline:0; width:100%; font-size:0.95rem; background: transparent; color: var(--cg-text); transition: color 0.25s ease; }
.header-search input::placeholder { color: var(--cg-muted); }
.header-search i { color: var(--cg-muted); transition: color 0.25s ease; }
.header-clock { padding:6px 10px; border-radius:8px; background:var(--cg-white); border:1px solid var(--cg-border); font-size:0.9rem; color:var(--cg-muted); display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center; transition: background 0.25s ease, border-color 0.25s ease, color 0.25s ease; }
.header-clock .time { font-weight:700; color:var(--cg-text); transition: color 0.25s ease; }
.dark-toggle { width:42px; height:42px; border-radius:10px; display:inline-flex; align-items:center; justify-content:center; border:1px solid var(--cg-border); background:var(--cg-white); cursor:pointer; transition: background 0.25s ease, border-color 0.25s ease, color 0.25s ease; color: var(--cg-muted); }
.dark-toggle:hover { background: rgba(13,110,253,0.1); border-color: var(--cg-primary); color: var(--cg-primary); }
.profile-control { display:inline-flex; align-items:center; gap:8px; padding:6px 10px; border-radius:999px; border:1px solid var(--cg-border); background:var(--cg-white); transition: background 0.25s ease, border-color 0.25s ease, color 0.25s ease; color: var(--cg-text); }
.profile-control .avatar { width:38px; height:38px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; background:rgba(13,110,253,0.08); color:var(--cg-primary); }
.dropdown-menu-admin { min-width:220px; border-radius:12px; background: var(--cg-white); border: 1px solid var(--cg-border); transition: background 0.25s ease, border-color 0.25s ease; }
.dropdown-menu-admin .dropdown-item { color: var(--cg-text); transition: background 0.25s ease, color 0.25s ease; }
.dropdown-menu-admin .dropdown-item:hover { background: rgba(13,110,253,0.1); color: var(--cg-primary); }
.dropdown-menu-admin .dropdown-item.text-danger { color: #dc2626; }
.dropdown-menu-admin .dropdown-item.text-danger:hover { background: rgba(220,38,38,0.1); color: #ef4444; }
.dropdown-menu-admin .dropdown-divider { border-color: var(--cg-border); }

.search-dropdown { position:absolute; top:100%; left:0; right:0; background:var(--cg-white); border:1px solid var(--cg-border); border-radius:10px; box-shadow:0 12px 30px rgba(15,23,42,0.1); max-height:400px; overflow-y:auto; z-index:1000; display:none; margin-top:8px; min-width:320px; transition: background 0.25s ease, border-color 0.25s ease; }
html[data-theme="dark"] .search-dropdown { box-shadow: 0 12px 30px rgba(0,0,0,0.3); }
.search-dropdown.active { display:block; }
.search-dropdown-group { padding:0.75rem 0; border-bottom:1px solid rgba(15,23,42,0.04); transition: border-color 0.25s ease; }
html[data-theme="dark"] .search-dropdown-group { border-bottom-color: rgba(255,255,255,0.1); }
.search-dropdown-group:last-child { border-bottom:none; }
.search-dropdown-heading { padding:0.5rem 1rem; font-size:0.75rem; font-weight:700; text-transform:uppercase; color:var(--cg-muted); letter-spacing:0.05em; transition: color 0.25s ease; }
.search-result-item { padding:0.75rem 1rem; color:var(--cg-text); cursor:pointer; text-decoration:none; display:flex; align-items:center; justify-content:space-between; transition: background 0.25s ease, color 0.25s ease; }
.search-result-item:hover { background:rgba(13,110,253,0.04); }
html[data-theme="dark"] .search-result-item:hover { background: rgba(13,110,253,0.15); }
.search-result-main { font-weight:600; }
.search-result-meta { font-size:0.85rem; color:var(--cg-muted); transition: color 0.25s ease; }
.search-no-results { padding:1.5rem; text-align:center; color:var(--cg-muted); font-size:0.9rem; transition: color 0.25s ease; }
.search-container { position:relative; }
@media (max-width: 768px){ .header-search{display:none} .header-clock{display:none} }
</style>

<header class="admin-topbar">
    <div class="title-area">
        <h1><?php echo $pageH1 ?? htmlspecialchars($pageTitle ?? '', ENT_QUOTES, 'UTF-8'); ?></h1>
        <?php if (!empty($pageSubtitle)): ?><div class="subtitle"><?php echo htmlspecialchars($pageSubtitle, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
    </div>

    <div class="utils">
        <div class="search-container">
            <div class="header-search" role="search" aria-label="Admin search">
                <i class="bi bi-search" aria-hidden="true"></i>
                <input type="search" id="adminSearchInput" placeholder="Search" aria-label="Search">
            </div>
            <div id="searchDropdown" class="search-dropdown"></div>
        </div>

        <div class="header-clock" title="Current date and time">
            <div class="date" id="headerDate"></div>
            <div class="time" id="headerTime"></div>
        </div>

        <button class="dark-toggle" id="darkToggle" aria-label="Toggle theme"><i class="bi bi-moon-fill"></i></button>

        <div class="dropdown">
            <button class="btn profile-control dropdown-toggle" id="adminProfileMenu" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="avatar"><i class="bi bi-person-circle"></i></span>
                <span class="d-none d-sm-inline fw-semibold"><?php echo $adminName; ?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-admin" aria-labelledby="adminProfileMenu">
                <li class="px-3 py-2">
                    <div class="d-flex align-items-center">
                        <div class="avatar me-2"><i class="bi bi-person-circle"></i></div>
                        <div>
                            <div class="fw-bold"><?php echo $adminName; ?></div>
                            <div class="text-muted small">Admin</div>
                        </div>
                    </div>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="<?php echo htmlspecialchars($basePath ?? '', ENT_QUOTES, 'UTF-8'); ?>admin/profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
                <li><a class="dropdown-item" href="<?php echo htmlspecialchars($basePath ?? '', ENT_QUOTES, 'UTF-8'); ?>admin/settings.php"><i class="bi bi-gear me-2"></i>Settings</a></li>
                <li><a class="dropdown-item text-danger" href="<?php echo htmlspecialchars($basePath ?? '', ENT_QUOTES, 'UTF-8'); ?>logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
            </ul>
        </div>
    </div>
</header>

<script>
// Admin Theme Management
function getAdminTheme() {
    try {
        return localStorage.getItem('admin-theme') || 'light';
    } catch (e) {
        return 'light';
    }
}

function setAdminTheme(theme) {
    try {
        localStorage.setItem('admin-theme', theme);
    } catch (e) {
        // localStorage may be unavailable; theme still works for current session
    }
    
    if (theme === 'dark') {
        document.documentElement.setAttribute('data-theme', 'dark');
        document.getElementById('darkToggle').innerHTML = '<i class="bi bi-sun-fill"></i>';
    } else {
        document.documentElement.removeAttribute('data-theme');
        document.getElementById('darkToggle').innerHTML = '<i class="bi bi-moon-fill"></i>';
    }
}

// Apply saved theme on page load
setAdminTheme(getAdminTheme());

// Dark mode toggle handler
document.getElementById('darkToggle').addEventListener('click', function(){
    const currentTheme = getAdminTheme();
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    setAdminTheme(newTheme);
});

// Admin Header Search
const adminSearchInput = document.getElementById('adminSearchInput');
const searchDropdown = document.getElementById('searchDropdown');
let searchTimeout;

function closeSearchDropdown() {
    searchDropdown.classList.remove('active');
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function performSearch(query) {
    if (!query || query.trim().length < 2) {
        closeSearchDropdown();
        return;
    }

    // Fetch search results from API
    fetch('./search-api.php?q=' + encodeURIComponent(query.trim()))
        .then(res => res.json())
        .then(data => {
            if (!data.results || (Object.keys(data.results).every(key => data.results[key].length === 0))) {
                searchDropdown.innerHTML = '<div class="search-no-results">No results found. Try a different keyword.</div>';
                searchDropdown.classList.add('active');
                return;
            }

            let html = '';

            // Jobs
            if (data.results.jobs && data.results.jobs.length > 0) {
                html += '<div class="search-dropdown-group">';
                html += '<div class="search-dropdown-heading">Jobs</div>';
                data.results.jobs.forEach(job => {
                    html += `<a href="./job-details.php?id=${job.id}" class="search-result-item">
                        <div>
                            <div class="search-result-main">${job.title}</div>
                            <div class="search-result-meta">${job.company} · ${job.location}</div>
                        </div>
                    </a>`;
                });
                html += '</div>';
            }

            // Candidates
            if (data.results.candidates && data.results.candidates.length > 0) {
                html += '<div class="search-dropdown-group">';
                html += '<div class="search-dropdown-heading">Candidates</div>';
                data.results.candidates.forEach(candidate => {
                    html += `<a href="./candidate-details.php?id=${candidate.id}" class="search-result-item">
                        <div>
                            <div class="search-result-main">${candidate.name}</div>
                            <div class="search-result-meta">${candidate.email}</div>
                        </div>
                    </a>`;
                });
                html += '</div>';
            }

            // Applications
            if (data.results.applications && data.results.applications.length > 0) {
                html += '<div class="search-dropdown-group">';
                html += '<div class="search-dropdown-heading">Applications</div>';
                data.results.applications.forEach(app => {
                    html += `<a href="./candidate-details.php?id=${app.user_id}&application_id=${app.id}" class="search-result-item">
                        <div>
                            <div class="search-result-main">${app.candidate_name}</div>
                            <div class="search-result-meta">${app.job_title}</div>
                        </div>
                    </a>`;
                });
                html += '</div>';
            }

            // Contact Messages
            if (data.results.messages && data.results.messages.length > 0) {
                html += '<div class="search-dropdown-group">';
                html += '<div class="search-dropdown-heading">Contact Messages</div>';
                data.results.messages.forEach(msg => {
                    html += `<a href="./contact-messages.php?id=${msg.id}" class="search-result-item">
                        <div>
                            <div class="search-result-main">${msg.name}</div>
                            <div class="search-result-meta">${msg.subject}</div>
                        </div>
                    </a>`;
                });
                html += '</div>';
            }

            searchDropdown.innerHTML = html;
            searchDropdown.classList.add('active');
        })
        .catch(err => {
            searchDropdown.innerHTML = '<div class="search-no-results">Unable to load search results.</div>';
            searchDropdown.classList.add('active');
            console.error('Search error:', err);
        });
}

// Debounced search handler
adminSearchInput.addEventListener('input', (e) => {
    clearTimeout(searchTimeout);
    const query = e.target.value;
    
    if (!query || query.trim().length < 2) {
        closeSearchDropdown();
        return;
    }

    searchTimeout = setTimeout(() => {
        performSearch(query);
    }, 300);
});

// Close dropdown on outside click
document.addEventListener('click', (e) => {
    if (!searchDropdown.contains(e.target) && !adminSearchInput.contains(e.target)) {
        closeSearchDropdown();
    }
});

// Close dropdown on ESC
adminSearchInput.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeSearchDropdown();
    }
});

// Minimal clock UI (client-side only)
function updateAdminClock(){
    const d = new Date();
    const optsDate = { day:'2-digit', month:'short', year:'numeric' };
    const optsTime = { hour:'numeric', minute:'2-digit', hour12:true };
    document.getElementById('headerDate').textContent = d.toLocaleDateString(undefined, optsDate);
    document.getElementById('headerTime').textContent = d.toLocaleTimeString(undefined, optsTime);
}
updateAdminClock(); setInterval(updateAdminClock, 60000);
</script>
