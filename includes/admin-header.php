<?php
// Admin header include (UI-only). Expects $pageH1 and $pageSubtitle set in the including page.
$adminName = htmlspecialchars($_SESSION['admin_name'] ?? $_SESSION['user_name'] ?? 'Administrator', ENT_QUOTES, 'UTF-8');
?>
<!-- Admin layout: fixed sidebar with normal document scrolling -->
<style>
    /* Layout wrapper for admin pages. This wrapper owns the left offset
       so the right-area (header, main content, footer) aligns naturally
       to the right of the fixed sidebar without per-footer hacks. */
    .admin-layout {
        /* define the sidebar width and a consistent gap to separate sidebar from content */
        --admin-sidebar-width: 248px;
        --admin-sidebar-gap: 24px; /* desktop gap; responsive fallbacks below */        --admin-mobile-gutter: 16px;        box-sizing: border-box;
        min-height: 100vh;
        /* Create right-side content inset so children (main + footer)
           naturally flow to the right of the fixed sidebar. */
        padding-left: calc(var(--admin-sidebar-width) + var(--admin-sidebar-gap));
    }

    /* Keep existing admin-root pages functioning but avoid double-left offsets
       when the global .admin-layout wrapper is present. */
    .admin-layout .admin-root { padding-left: 0 !important; }

    /* Fixed left sidebar that never scrolls away from the viewport top */
    .admin-root .sidebar {
        position:fixed;
        left:0;
        top:0;
        bottom:0;
        width: var(--admin-sidebar-width);
        height:100vh;
        display:flex;
        flex-direction:column;
        overflow:hidden;
        -webkit-overflow-scrolling: touch;
        box-shadow: 0 6px 20px rgba(16,24,40,0.06);
        z-index: 1030;
        box-sizing: border-box;
    }
    .admin-root .sidebar-branding {
        flex-shrink:0;
        display:flex;
        align-items:center;
        gap:10px;
        padding:18px 14px 10px;
        border-bottom:1px solid rgba(255,255,255,.12);
    }
    .admin-root .sidebar-branding .brand-copy {
        min-width:0;
        display:flex;
        flex-direction:column;
        justify-content:center;
        line-height:1.15;
    }
    .admin-root .sidebar-branding .brand-title {
        white-space:nowrap;
        overflow:hidden;
        text-overflow:ellipsis;
        color:#fff;
        font-size:0.82rem;
        font-weight:800;
        letter-spacing:-.02em;
        line-height:1.2;
    }
    .admin-root .sidebar-branding .text-soft {
        margin-top:2px;
        font-size:0.7rem;
        line-height:1.2;
    }
    .admin-root .sidebar-navigation {
        flex:1 1 auto;
        display:flex;
        flex-direction:column;
        overflow-y:auto;
        overflow-x:hidden;
        padding:10px 14px 8px;
        -ms-overflow-style: none; /* IE and Edge */
        scrollbar-width: none; /* Firefox */
    }
    .admin-root .sidebar-navigation::-webkit-scrollbar { width: 0; height: 0; }

    /* Right-side area (header + main content). Pages render inside this area.
       Ensure the right area occupies the remaining viewport width and stacks vertically. */
    .admin-root > *:not(.sidebar) { margin-left:0; }
    .admin-root .dashboard-content,
    .admin-root .main-panel,
    .admin-root section.dashboard-content { box-sizing:border-box; }

     /* Let the page grow with its content so the footer follows every section.
        The browser owns page scrolling; only the sidebar scrolls independently. */
     .admin-root .dashboard-content,
     .admin-root .main-panel {
          height:auto;
          overflow:visible;
     }

     /* Admin header should scroll naturally with the right-side content.
         Only the sidebar remains fixed. Do NOT use fixed or sticky for the header. */
     .admin-root .admin-topbar,
     .admin-root .dashboard-content .admin-topbar { position: static; top: auto; z-index: auto; background:inherit; }

     /* Footer will live inside the admin-layout wrapper and therefore
         inherits the left inset. Keep the footer full-width of the
         right-side area by using 100% width within the wrapper. */
     .admin-footer { width: 100%; box-sizing: border-box; }

    /* Responsive: on smaller screens fall back to a single-column stacked layout */
    @media (max-width: 1199.98px) {
        .admin-layout {
            width: 100%;
            max-width: 100%;
            padding-left: 0;
            padding-right: 0;
            overflow-x: hidden;
        }
        .admin-layout .admin-root,
        .admin-root {
            width: 100%;
            max-width: 100%;
            margin-left: 0;
            margin-right: 0;
            padding-left: var(--admin-mobile-gutter, 16px) !important;
            padding-right: var(--admin-mobile-gutter, 16px) !important;
            box-sizing: border-box;
            overflow-x: hidden;
        }
        .admin-root .sidebar {
            position: fixed;
            left: 0;
            top: 12px;
            bottom: auto;
            z-index: 1045;
            width: min(82vw, 290px);
            max-width: 290px;
            height: auto;
            max-height: calc(100vh - 24px);
            min-height: 0;
            box-shadow: 0 18px 42px rgba(15, 23, 42, 0.26);
            display: flex;
            transform: translateX(-105%);
            opacity: 0;
            visibility: hidden;
            transition: transform 0.25s ease, opacity 0.25s ease, visibility 0.25s ease;
        }
        .admin-root .sidebar-navigation {
            flex: 0 0 auto;
            max-height: calc(100vh - 150px);
            overflow-y: auto;
            overflow-x: hidden;
            padding-bottom: 10px;
        }
        .admin-sidebar-open .admin-root .sidebar {
            transform: translateX(0);
            opacity: 1;
            visibility: visible;
        }
        .admin-root .dashboard-content,
        .admin-root .main-panel {
            width: 100%;
            max-width: 100%;
            height: auto !important;
            min-height: 0;
            overflow: visible !important;
            overflow-x: hidden;
        }
        .admin-footer { margin-left:0; width:100%; max-width:100%; }
    }

    @media (max-width: 767.98px) {
        .admin-layout,
        .admin-root {
            overflow-x: hidden;
        }
        .admin-layout .admin-root,
        .admin-root {
            padding-left: var(--admin-mobile-gutter, 16px) !important;
            padding-right: var(--admin-mobile-gutter, 16px) !important;
            margin-left: 0;
            margin-right: 0;
        }
        .admin-root .dashboard-content,
        .admin-root .main-panel {
            height: auto !important;
            min-height: 0;
            overflow: visible !important;
            overflow-x: hidden;
        }
    }
</style>
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
.admin-topbar { display:grid; grid-template-columns:minmax(0, 1fr) auto; align-items:center; column-gap:1rem; padding:0.6rem 1rem; background:transparent; border-bottom:1px solid rgba(15,23,42,0.04); transition: border-color 0.25s ease; }
html[data-theme="dark"] .admin-topbar { border-bottom-color: rgba(255,255,255,0.1); }

.admin-topbar .title-area { display:flex; flex-direction:column; min-width:0; }
.admin-topbar .title-area h1 { margin:0; font-size:1.15rem; font-weight:700; color: var(--cg-text); transition: color 0.25s ease; }
.admin-topbar .title-area .subtitle { font-size:0.9rem; color:var(--cg-muted); transition: color 0.25s ease; }
.admin-topbar .utils { display:flex; align-items:center; justify-self:end; gap:0.75rem; flex-wrap:nowrap; }
.mobile-header-row { display:none; }
.mobile-branding { display:none; }
.admin-sidebar-close-button { display:none; }
.header-search { width:260px; max-width:38vw; height:42px; display:inline-flex; align-items:center; gap:8px; padding:6px 10px; background:var(--cg-white); border:1px solid var(--cg-border); border-radius:10px; transition: background 0.25s ease, border-color 0.25s ease, color 0.25s ease; }
.header-search input { border:0; outline:0; width:100%; font-size:0.95rem; background: transparent; color: var(--cg-text); transition: color 0.25s ease; }
.header-search input::placeholder { color: var(--cg-muted); }
.header-search i { color: var(--cg-muted); transition: color 0.25s ease; }
.header-clock { min-height:42px; padding:3px 10px; border-radius:8px; background:var(--cg-white); border:1px solid var(--cg-border); font-size:0.9rem; line-height:1.15; color:var(--cg-muted); display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center; transition: background 0.25s ease, border-color 0.25s ease, color 0.25s ease; }
.header-clock .date, .header-clock .time { line-height:1.15; }
.header-clock .time { font-weight:700; color:var(--cg-text); transition: color 0.25s ease; }
.dark-toggle { width:42px; height:42px; border-radius:10px; display:inline-flex; align-items:center; justify-content:center; border:1px solid var(--cg-border); background:var(--cg-white); cursor:pointer; transition: background 0.25s ease, border-color 0.25s ease, color 0.25s ease; color: var(--cg-muted); }
.dark-toggle:hover { background: rgba(13,110,253,0.1); border-color: var(--cg-primary); color: var(--cg-primary); }
.profile-control { min-height:42px; display:inline-flex; align-items:center; gap:8px; padding:4px 10px; border-radius:999px; border:1px solid var(--cg-border); background:var(--cg-white); transition: background 0.25s ease, border-color 0.25s ease, color 0.25s ease; color: var(--cg-text); }
.profile-control .avatar { width:32px; height:32px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; background:rgba(13,110,253,0.08); color:var(--cg-primary); }
.admin-mobile-menu-button {
    display: none;
    width: 40px;
    height: 40px;
    padding: 0;
    border: 1px solid var(--cg-border);
    border-radius: 12px;
    background: var(--cg-white);
    color: var(--cg-text);
    box-shadow: 0 6px 18px rgba(15, 23, 42, 0.08);
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.2s ease, border-color 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
}
.admin-mobile-menu-button:hover,
.admin-mobile-menu-button:focus-visible {
    background: rgba(13, 110, 253, 0.08);
    border-color: var(--cg-primary);
    color: var(--cg-primary);
    box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.12);
    outline: none;
}
.admin-mobile-menu-button i { font-size: 1.45rem; line-height: 1; }
.dropdown-menu-admin { min-width:220px; border-radius:12px; background: var(--cg-white); border: 1px solid var(--cg-border); transition: background 0.25s ease, border-color 0.25s ease; }
.dropdown-menu-admin { min-width:204px; padding:5px; border-radius:13px; background:rgba(255,255,255,.78); border-color:rgba(255,255,255,.72); box-shadow:0 14px 30px rgba(15,23,42,.14); backdrop-filter:blur(12px); -webkit-backdrop-filter:blur(12px); }
.dropdown-menu-admin.dropdown-menu-end { right:0; left:auto; }
.dropdown-menu-admin > li:first-child { margin:-5px -5px 4px; padding:8px 11px 8px !important; border-radius:13px 13px 8px 8px; background:rgba(239,246,255,.7); }
.dropdown-menu-admin > li:first-child .avatar { width:34px; height:34px; display:inline-flex; align-items:center; justify-content:center; border-radius:10px; background:#dbeafe; color:#2563eb; font-size:1rem; }
.dropdown-menu-admin > li:first-child .fw-bold { color:var(--cg-text); font-size:.9rem; letter-spacing:-.01em; }
.dropdown-menu-admin > li:first-child .text-muted { color:var(--cg-muted) !important; font-size:.72rem; }
.dropdown-menu-admin .dropdown-divider { margin:3px 6px 4px; border-color:var(--cg-border); opacity:.65; }
.dropdown-menu-admin .dropdown-item { display:flex; align-items:center; min-height:32px; padding:6px 9px; border-radius:8px; color:var(--cg-text); font-size:.84rem; font-weight:600; transition:background 0.2s ease, color 0.2s ease, transform 0.2s ease; }
.dropdown-menu-admin .dropdown-item .bi { width:18px; margin-right:9px !important; color:#64748b; font-size:.92rem; text-align:center; transition:color 0.2s ease; }
.dropdown-menu-admin .dropdown-item:hover,
.dropdown-menu-admin .dropdown-item:focus { background:#eef5ff; color:#1d4ed8; transform:translateX(2px); }
.dropdown-menu-admin .dropdown-item:hover .bi,
.dropdown-menu-admin .dropdown-item:focus .bi { color:#2563eb; }
.dropdown-menu-admin .dropdown-item.text-danger { color: #dc2626; }
.dropdown-menu-admin .dropdown-item.text-danger .bi { color:#dc2626; }
.dropdown-menu-admin .dropdown-item.text-danger:hover,
.dropdown-menu-admin .dropdown-item.text-danger:focus { background:#fff1f2; color:#b91c1c; }
.dropdown-menu-admin .dropdown-item.text-danger:hover .bi,
.dropdown-menu-admin .dropdown-item.text-danger:focus .bi { color:#b91c1c; }
html[data-theme="dark"] .dropdown-menu-admin { background:rgba(17,24,39,.82); border-color:rgba(148,163,184,.3); box-shadow:0 16px 34px rgba(0,0,0,.38); }
html[data-theme="dark"] .dropdown-menu-admin > li:first-child { background:rgba(30,58,95,.72); }
html[data-theme="dark"] .dropdown-menu-admin .dropdown-item:hover,
html[data-theme="dark"] .dropdown-menu-admin .dropdown-item:focus { background:#1e3a61; color:#bfdbfe; }
html[data-theme="dark"] .dropdown-menu-admin .dropdown-item .bi { color:#94a3b8; }
html[data-theme="dark"] .dropdown-menu-admin .dropdown-item:hover .bi,
html[data-theme="dark"] .dropdown-menu-admin .dropdown-item:focus .bi { color:#93c5fd; }
html[data-theme="dark"] .dropdown-menu-admin .dropdown-item.text-danger:hover,
html[data-theme="dark"] .dropdown-menu-admin .dropdown-item.text-danger:focus { background:#451a1a; color:#fca5a5; }

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
@media (max-width: 991.98px){
    .admin-topbar{display:flex; flex-wrap:wrap; row-gap:0.75rem;}
    .admin-topbar .utils{width:100%; justify-content:flex-end;}
}
@media (max-width: 767.98px){
    .admin-topbar {
        display: flex;
        flex-direction: column;
        align-items: stretch;
        gap: 0.7rem;
        min-height: 0;
        padding: 0.55rem 0.75rem 0.7rem;
        border-bottom: 1px solid rgba(15, 23, 42, 0.06);
    }
    .admin-topbar .title-area {
        width: 100%;
        min-width: 0;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 0.15rem;
    }
    .mobile-header-row {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto auto;
        align-items: center;
        gap: 0.6rem;
        width: 100%;
    }
    .admin-mobile-menu-button {
        display: inline-flex;
        flex-shrink: 0;
        grid-column: 3;
        grid-row: 1;
    }
    .mobile-branding {
        grid-column: 1;
        grid-row: 1;
    }
    .mobile-header-row > .utils {
        grid-column: 2;
        grid-row: 1;
    }
    .admin-sidebar-close-button {
        display: inline-flex;
        position: absolute;
        top: 14px;
        right: 14px;
        width: 40px;
        height: 40px;
        border: 1px solid rgba(255,255,255,0.22);
        border-radius: 12px;
        background: rgba(15, 23, 42, 0.18);
        color: #ffffff;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        z-index: 2;
        transition: background-color 0.2s ease, border-color 0.2s ease, transform 0.2s ease;
    }
    .admin-sidebar-close-button:hover,
    .admin-sidebar-close-button:focus-visible,
    .admin-sidebar-close-button:active {
        background: rgba(255,255,255,0.12);
        border-color: rgba(255,255,255,0.42);
        transform: translateY(-1px);
        outline: none;
    }
    .admin-sidebar-close-button i {
        font-size: 1.2rem;
        line-height: 1;
    }
    .mobile-branding {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        min-width: 0;
        overflow: hidden;
    }
    .mobile-brand-mark {
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        background: rgba(13, 110, 253, 0.08);
        overflow: hidden;
        flex-shrink: 0;
    }
    .mobile-brand-mark img {
        width: 29px;
        height: 29px;
        object-fit: contain;
    }
    .mobile-brand-name {
        display: inline-block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-size: 0.84rem;
        font-weight: 750;
        color: var(--cg-text);
    }
    .admin-topbar .title-area h1 {
        font-size: 1.08rem;
        line-height: 1.2;
        white-space: normal;
        overflow-wrap: anywhere;
        margin: 0;
    }
    .admin-topbar .title-area .subtitle {
        display: block;
        font-size: 0.74rem;
        line-height: 1.4;
        color: var(--cg-muted);
    }
    .admin-topbar .utils {
        width: auto;
        justify-content: flex-end;
        gap: 0.45rem;
        flex-wrap: nowrap;
        flex-shrink: 0;
    }
    .desktop-utils {
        display: none !important;
    }
    .header-search{display:none}
    .header-clock{display:none}
    .dark-toggle {
        width:36px;
        height:36px;
        flex-shrink:0;
    }
    .profile-control {
        min-height:36px;
        padding:4px 8px;
        flex-shrink:0;
    }
    .profile-control .fw-semibold {
        display: none !important;
    }
}
@media (max-width: 425.98px){
    .admin-topbar {
        gap: 0.5rem;
        padding: 0.5rem 0.5rem 0.6rem;
    }
    .mobile-brand-mark {
        width: 32px;
        height: 32px;
    }
    .mobile-brand-mark img {
        width: 26px;
        height: 26px;
    }
    .mobile-brand-name {
        font-size: 0.76rem;
    }
    .admin-topbar .title-area h1 {
        font-size: 0.98rem;
    }
    .admin-topbar .title-area .subtitle {
        font-size: 0.7rem;
    }
    .admin-topbar .utils {
        gap: 0.35rem;
    }
    .profile-control {
        padding:4px 6px;
    }
    .profile-control .avatar {
        width:28px;
        height:28px;
    }
}
</style>

<div id="adminSidebarOverlay" class="admin-sidebar-overlay" aria-hidden="true"></div>

<header class="admin-topbar">
    <div class="mobile-header-row">
        <button type="button" class="admin-mobile-menu-button" id="adminMobileMenuToggle" aria-label="Open admin menu" aria-expanded="false">
            <i class="bi bi-list" aria-hidden="true"></i>
        </button>

        <div class="mobile-branding" aria-label="Career Grow Infotech branding">
            <span class="mobile-brand-mark"><img src="<?php echo htmlspecialchars($basePath ?? '../', ENT_QUOTES, 'UTF-8'); ?>assets/images/logo.webp" alt="Career Grow Infotech logo"></span>
            <span class="mobile-brand-name">Career Grow Infotech</span>
        </div>

        <div class="utils">
            <button class="dark-toggle" id="darkToggleMobile" aria-label="Toggle theme"><i class="bi bi-moon-fill"></i></button>

            <div class="dropdown">
                <button class="btn profile-control dropdown-toggle" id="adminProfileMenuMobile" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="avatar"><i class="bi bi-person-circle"></i></span>
                    <span class="d-none d-sm-inline fw-semibold"><?php echo $adminName; ?></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-admin" aria-labelledby="adminProfileMenuMobile">
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
                    <li><a class="dropdown-item" href="<?php echo htmlspecialchars($basePath ?? '', ENT_QUOTES, 'UTF-8'); ?>admin/dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                    <li><a class="dropdown-item" href="<?php echo htmlspecialchars($basePath ?? '', ENT_QUOTES, 'UTF-8'); ?>admin/profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
                    <li><a class="dropdown-item" href="<?php echo htmlspecialchars($basePath ?? '', ENT_QUOTES, 'UTF-8'); ?>admin/settings.php"><i class="bi bi-gear me-2"></i>Settings</a></li>
                    <li><a class="dropdown-item text-danger" href="<?php echo htmlspecialchars($basePath ?? '', ENT_QUOTES, 'UTF-8'); ?>logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="title-area">
        <h1><?php echo $pageH1 ?? htmlspecialchars($pageTitle ?? '', ENT_QUOTES, 'UTF-8'); ?></h1>
        <?php if (!empty($pageSubtitle)): ?><div class="subtitle"><?php echo htmlspecialchars($pageSubtitle, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
    </div>

    <div class="utils desktop-utils">
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
                <li><a class="dropdown-item" href="<?php echo htmlspecialchars($basePath ?? '', ENT_QUOTES, 'UTF-8'); ?>admin/dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
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

    const darkToggles = document.querySelectorAll('.dark-toggle');
    darkToggles.forEach((toggle) => {
        toggle.innerHTML = theme === 'dark' ? '<i class="bi bi-sun-fill"></i>' : '<i class="bi bi-moon-fill"></i>';
    });

    if (theme === 'dark') {
        document.documentElement.setAttribute('data-theme', 'dark');
    } else {
        document.documentElement.removeAttribute('data-theme');
    }
}

function toggleAdminSidebar(forceOpen) {
    const body = document.body;
    const sidebarOpen = typeof forceOpen === 'boolean' ? forceOpen : !body.classList.contains('admin-sidebar-open');
    body.classList.toggle('admin-sidebar-open', sidebarOpen);
    document.body.style.overflow = sidebarOpen ? 'hidden' : '';
    const overlay = document.getElementById('adminSidebarOverlay');
    const menuToggle = document.getElementById('adminMobileMenuToggle');

    if (overlay) {
        overlay.classList.toggle('is-visible', sidebarOpen);
        overlay.setAttribute('aria-hidden', sidebarOpen ? 'false' : 'true');
    }

    if (menuToggle) {
        menuToggle.setAttribute('aria-expanded', sidebarOpen ? 'true' : 'false');
        menuToggle.setAttribute('title', sidebarOpen ? 'Close admin menu' : 'Open admin menu');
    }
}

const adminMobileMenuToggle = document.getElementById('adminMobileMenuToggle');
const adminSidebarOverlay = document.getElementById('adminSidebarOverlay');

function ensureSidebarCloseButton() {
    const sidebars = document.querySelectorAll('.sidebar');

    sidebars.forEach(function (sidebar) {
        if (sidebar.querySelector('.admin-sidebar-close-button')) {
            return;
        }

        const closeButton = document.createElement('button');
        closeButton.type = 'button';
        closeButton.className = 'admin-sidebar-close-button';
        closeButton.setAttribute('aria-label', 'Close admin menu');
        closeButton.innerHTML = '<i class="bi bi-x-lg" aria-hidden="true"></i>';
        closeButton.addEventListener('click', function () {
            toggleAdminSidebar(false);
        });

        if (sidebar.firstChild) {
            sidebar.insertBefore(closeButton, sidebar.firstChild);
        } else {
            sidebar.appendChild(closeButton);
        }
    });
}

if (adminMobileMenuToggle) {
    adminMobileMenuToggle.addEventListener('click', function () {
        toggleAdminSidebar();
    });
}

if (adminSidebarOverlay) {
    adminSidebarOverlay.addEventListener('click', function () {
        toggleAdminSidebar(false);
    });
}

ensureSidebarCloseButton();

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        toggleAdminSidebar(false);
    }
});

window.addEventListener('resize', function () {
    if (window.innerWidth > 1199.98) {
        toggleAdminSidebar(false);
    }
});

// Apply saved theme on page load
setAdminTheme(getAdminTheme());

// Dark mode toggle handler
document.querySelectorAll('.dark-toggle').forEach((toggle) => {
    toggle.addEventListener('click', function(){
        const currentTheme = getAdminTheme();
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        setAdminTheme(newTheme);
    });
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
