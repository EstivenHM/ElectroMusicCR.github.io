(() => {
    const navToggle = document.querySelector('[data-nav-toggle]');
    const siteNav = document.querySelector('[data-site-nav]');

    if (!navToggle || !siteNav) return;

    const toggleNav = (forceState) => {
        const isOpen = typeof forceState === 'boolean'
            ? forceState
            : !siteNav.classList.contains('is-open');

        siteNav.classList.toggle('is-open', isOpen);
        navToggle.setAttribute('aria-expanded', String(isOpen));
        navToggle.setAttribute('aria-label', isOpen ? 'Cerrar menú de navegación' : 'Abrir menú de navegación');
    };

    navToggle.addEventListener('click', (event) => {
        event.stopPropagation();
        toggleNav();
    });

    siteNav.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => {
            if (siteNav.classList.contains('is-open')) {
                toggleNav(false);
            }
        });
    });

    document.addEventListener('click', (event) => {
        if (!siteNav.contains(event.target) && !navToggle.contains(event.target)) {
            if (siteNav.classList.contains('is-open')) {
                toggleNav(false);
            }
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && siteNav.classList.contains('is-open')) {
            toggleNav(false);
            navToggle.focus();
        }
    });
})();
