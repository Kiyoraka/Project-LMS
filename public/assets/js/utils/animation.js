/**
 * Animation Utility Module
 * Handles various animations throughout the site
 */

/**
 * Initialize floating elements animation enhancement
 * @param {string} selector - CSS selector for floating elements
 */
export function initFloatingElements(selector = '.floating-icon') {
    const floatingElements = document.querySelectorAll(selector);
    
    floatingElements.forEach((element, index) => {
        element.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.2) rotate(15deg)';
            this.style.color = '#f093fb';
        });
        
        element.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1) rotate(0deg)';
            this.style.color = 'white';
        });
    });
}

/**
 * Initialize parallax effect for hero section
 * @param {string} selector - CSS selector for parallax elements
 */
export function initParallaxEffect(selector = '.floating-icon') {
    const parallaxElements = document.querySelectorAll(selector);
    
    window.addEventListener('scroll', function() {
        const scrolled = window.pageYOffset;
        
        parallaxElements.forEach((element, index) => {
            const speed = 0.5 + (index * 0.1);
            element.style.transform = `translateY(${scrolled * speed}px) rotate(${scrolled * 0.1}deg)`;
        });
    });
}

/**
 * Initialize scroll reveal animations
 * @param {string} selector - CSS selector for elements to animate
 */
export function initScrollReveal(selector = '.animate-on-scroll') {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, observerOptions);

    // Observe elements for animation
    const elements = document.querySelectorAll(selector);
    elements.forEach(element => {
        observer.observe(element);
    });
    
    return observer;
}

/**
 * Initialize feature cards hover effect
 * @param {string} cardSelector - CSS selector for feature cards
 * @param {string} iconSelector - CSS selector for feature icons
 */
export function initFeatureCardsEffect(cardSelector = '.feature-card', iconSelector = '.feature-icon') {
    const featureCards = document.querySelectorAll(cardSelector);
    
    featureCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.querySelector(iconSelector).style.transform = 'scale(1.1) rotate(5deg)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.querySelector(iconSelector).style.transform = 'scale(1) rotate(0deg)';
        });
    });
}

/**
 * Initialize instructor image rotation
 * @param {string} selector - CSS selector for instructor image
 * @param {Array} images - Array of image URLs
 * @param {number} interval - Rotation interval in milliseconds
 */
export function initInstructorImageRotation(selector = '#instructorImg', images = [], interval = 5000) {
    const instructorImg = document.getElementById(selector.replace('#', ''));
    if (!instructorImg || images.length === 0) return;
    
    let currentImageIndex = 0;
    
    setInterval(() => {
        currentImageIndex = (currentImageIndex + 1) % images.length;
        instructorImg.style.opacity = '0.7';
        
        setTimeout(() => {
            instructorImg.src = images[currentImageIndex];
            instructorImg.style.opacity = '1';
        }, 300);
    }, interval);
}

/**
 * Add page loading animation
 */
export function initPageLoadAnimation() {
    window.addEventListener('load', function() {
        document.body.style.opacity = '0';
        document.body.style.transition = 'opacity 0.5s ease';
        
        setTimeout(() => {
            document.body.style.opacity = '1';
        }, 100);
    });
}