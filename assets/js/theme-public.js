// Public/Candidate theme toggle handler
// Uses localStorage key 'site-theme' with values 'light' or 'dark'
(function(){
    'use strict';
    var STORAGE_KEY = 'site-theme';
    var toggleBtn = null;
    var toggleBtnMobile = null;

    function getTheme(){
        try { return localStorage.getItem(STORAGE_KEY) || 'light'; } catch(e){ return 'light'; }
    }
    function applyTheme(t){
        try {
            if (t === 'dark') {
                document.documentElement.setAttribute('data-theme','dark');
            } else {
                document.documentElement.removeAttribute('data-theme');
            }
        } catch(e){}
    }
    function updateButtons(t){
        var icon = t === 'dark' ? 'bi-sun-fill' : 'bi-moon-fill';
        var label = t === 'dark' ? 'Switch to light mode' : 'Switch to dark mode';
        if (toggleBtn) {
            var i = toggleBtn.querySelector('i');
            if (i) { i.className = 'bi ' + icon; }
            toggleBtn.setAttribute('title', label);
            toggleBtn.setAttribute('aria-label', label);
        }
        if (toggleBtnMobile) {
            var im = toggleBtnMobile.querySelector('i');
            if (im) { im.className = 'bi ' + icon; }
            toggleBtnMobile.setAttribute('title', label);
            toggleBtnMobile.setAttribute('aria-label', label);
        }
    }
    function toggleTheme(){
        var current = getTheme();
        var next = current === 'dark' ? 'light' : 'dark';
        try { localStorage.setItem(STORAGE_KEY, next); } catch(e){}
        applyTheme(next);
        updateButtons(next);
    }

    document.addEventListener('DOMContentLoaded', function(){
        toggleBtn = document.getElementById('publicThemeToggle');
        toggleBtnMobile = document.getElementById('publicThemeToggleMobile');
        var theme = getTheme();
        applyTheme(theme);
        updateButtons(theme);
        if (toggleBtn) toggleBtn.addEventListener('click', toggleTheme, {passive:false});
        if (toggleBtnMobile) toggleBtnMobile.addEventListener('click', function(e){
            toggleTheme();
            // Close mobile navbar if open to show effect
            var nav = document.getElementById('mainNavbar');
            if (nav && nav.classList.contains('show')) {
                var collapse = bootstrap.Collapse.getInstance(nav) || new bootstrap.Collapse(nav, { toggle: false });
                collapse.hide();
            }
        }, {passive:false});
    });
})();
