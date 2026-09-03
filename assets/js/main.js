document.addEventListener('DOMContentLoaded', function () {
    const currentYearElement = document.getElementById('currentYear');
    if (currentYearElement) {
        currentYearElement.textContent = new Date().getFullYear();
    }

    const navbarToggler = document.querySelector('.navbar-toggler');
    const navbarCollapse = document.querySelector('.navbar-collapse');
    if (navbarToggler && navbarCollapse) {
        navbarCollapse.addEventListener('show.bs.collapse', function () {
            navbarToggler.setAttribute('aria-expanded', 'true');
        });

        navbarCollapse.addEventListener('hide.bs.collapse', function () {
            navbarToggler.setAttribute('aria-expanded', 'false');
        });
    }

    const currentPage = window.location.pathname.split('/').pop() || 'index.php';
    document.querySelectorAll('.nav-link').forEach(function (link) {
        const href = link.getAttribute('href');
        if (!href) {
            return;
        }

        const normalizedHref = href.split('/').pop();
        const isCurrentPage = normalizedHref === currentPage;

        link.classList.toggle('active', isCurrentPage);
        if (isCurrentPage) {
            link.setAttribute('aria-current', 'page');
        } else {
            link.removeAttribute('aria-current');
        }
    });

    const backToTopButton = document.getElementById('backToTop');
    if (backToTopButton) {
        const toggleBackToTop = function () {
            if (window.scrollY > 220) {
                backToTopButton.classList.add('visible');
            } else {
                backToTopButton.classList.remove('visible');
            }
        };

        toggleBackToTop();
        window.addEventListener('scroll', toggleBackToTop, { passive: true });
        backToTopButton.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    const handleNavbarScrollState = function () {
        document.body.classList.toggle('scrolled', window.scrollY > 20);
    };

    handleNavbarScrollState();
    window.addEventListener('scroll', handleNavbarScrollState, { passive: true });
});
