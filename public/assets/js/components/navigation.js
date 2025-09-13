/**
 * Navigation Component Module
 * Handles navigation functionality including mobile menu and scroll effects
 */

/**
 * Initialize navigation scroll effect
 * @param {string} selector - CSS selector for navbar
 */
export function initNavbarScrollEffect(selector = '.navbar') {
    const navbar = document.querySelector(selector);
    if (!navbar) return;
    
    window.addEventListener('scroll', function() {
        if (window.scrollY > 100) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });
}

/**
 * Initialize mobile menu toggle
 * @param {string} toggleSelector - CSS selector for mobile toggle button
 * @param {string} menuSelector - CSS selector for nav menu
 */
export function initMobileMenu(toggleSelector = '#mobileToggle', menuSelector = '.nav-menu') {
    const mobileToggle = document.querySelector(toggleSelector);
    const navMenu = document.querySelector(menuSelector);
    
    if (!mobileToggle || !navMenu) return;
    
    // Toggle mobile menu
    mobileToggle.addEventListener('click', function() {
        navMenu.classList.toggle('active');
        mobileToggle.classList.toggle('active');
    });

    // Close mobile menu when clicking on a nav link
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', function() {
            navMenu.classList.remove('active');
            mobileToggle.classList.remove('active');
        });
    });

    // Close mobile menu when clicking outside
    document.addEventListener('click', function(e) {
        if (!mobileToggle.contains(e.target) && !navMenu.contains(e.target)) {
            navMenu.classList.remove('active');
            mobileToggle.classList.remove('active');
        }
    });
}

/**
 * Initialize smooth scrolling for navigation links
 * @param {string} selector - CSS selector for navigation links
 */
export function initSmoothScrolling(selector = '.nav-link') {
    document.querySelectorAll(selector).forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            
            if (href.startsWith('#')) {
                e.preventDefault();
                const target = document.querySelector(href);
                
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });
}