(function () {
    'use strict';

    const navbar = document.querySelector('.navbar');
    const menuToggle = document.getElementById('menuToggle');
    const mobileNavigation = document.getElementById('mobileNavigation');
    const backToTop = document.getElementById('backToTop');
    const adminMenuToggle = document.querySelector('.admin-mobile-toggle');
    const adminNavigation = document.getElementById('adminNavigation');

    function closeMobileNavigation() {
        if (!menuToggle || !mobileNavigation) return;
        menuToggle.classList.remove('open');
        mobileNavigation.classList.remove('open');
        menuToggle.setAttribute('aria-expanded', 'false');
        menuToggle.setAttribute('aria-label', 'Open navigation menu');
    }

    if (menuToggle && mobileNavigation) {
        menuToggle.addEventListener('click', function () {
            const isOpen = menuToggle.getAttribute('aria-expanded') === 'true';
            menuToggle.classList.toggle('open', !isOpen);
            mobileNavigation.classList.toggle('open', !isOpen);
            menuToggle.setAttribute('aria-expanded', String(!isOpen));
            menuToggle.setAttribute('aria-label', isOpen ? 'Open navigation menu' : 'Close navigation menu');
        });

        mobileNavigation.addEventListener('click', function (event) {
            if (event.target.closest('a')) closeMobileNavigation();
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') closeMobileNavigation();
        });

        window.addEventListener('resize', function () {
            if (window.innerWidth > 991) closeMobileNavigation();
        });
    }

    function updateScrollControls() {
        const isScrolled = window.scrollY > 24;
        if (navbar) navbar.classList.toggle('scrolled', isScrolled);
        if (backToTop) backToTop.classList.toggle('visible', window.scrollY > 400);
    }

    window.addEventListener('scroll', updateScrollControls, { passive: true });
    updateScrollControls();

    if (backToTop) {
        backToTop.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    if (adminMenuToggle && adminNavigation) {
        function setAdminNavigationOpen(isOpen) {
            adminNavigation.classList.toggle('open', isOpen);
            adminMenuToggle.setAttribute('aria-expanded', String(isOpen));
            adminMenuToggle.setAttribute('aria-label', isOpen ? 'Close admin navigation' : 'Open admin navigation');
        }

        adminMenuToggle.addEventListener('click', function () {
            setAdminNavigationOpen(adminMenuToggle.getAttribute('aria-expanded') !== 'true');
        });
        adminNavigation.addEventListener('click', function (event) {
            if (event.target.closest('a')) setAdminNavigationOpen(false);
        });
        document.addEventListener('click', function (event) {
            if (
                adminNavigation.classList.contains('open')
                && !adminNavigation.contains(event.target)
                && !adminMenuToggle.contains(event.target)
            ) {
                setAdminNavigationOpen(false);
            }
        });
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') setAdminNavigationOpen(false);
        });
        window.addEventListener('resize', function () {
            if (window.innerWidth > 991) setAdminNavigationOpen(false);
        });
    }
})();
