/**
 * Main JavaScript Entry Point
 * Imports and initializes all modular components
 */

// Import utilities
import { 
    initFloatingElements, 
    initParallaxEffect, 
    initScrollReveal,
    initFeatureCardsEffect,
    initInstructorImageRotation,
    initPageLoadAnimation
} from './utils/animation.js';

// Import components
import { 
    initNavbarScrollEffect, 
    initMobileMenu, 
    initSmoothScrolling 
} from './components/navigation.js';
import { 
    initLoginButton, 
    initSignupButton, 
    initCtaButtons, 
    initButtonHoverEffects 
} from './components/buttons.js';

// Initialize everything when the DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize navigation
    initNavbarScrollEffect();
    initMobileMenu();
    initSmoothScrolling();
    
    // Initialize buttons
    initLoginButton();
    initSignupButton();
    initCtaButtons();
    initButtonHoverEffects();
    
    // Initialize animations
    initFloatingElements();
    initParallaxEffect();
    initScrollReveal('.feature-card');
    initFeatureCardsEffect();
    
    // Initialize instructor image rotation
    const instructorImages = [
        'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=300&h=400&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1494790108755-2616b2c2e7e3?w=300&h=400&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=300&h=400&fit=crop&crop=face'
    ];
    initInstructorImageRotation('#instructorImg', instructorImages, 5000);
    
    // Initialize page load animation
    initPageLoadAnimation();
    
    console.log('Aidel LMS - Landing page initialized successfully! 🚀');
});

// Optional: Add global error handling (logging only)
window.addEventListener('error', function(e) {
    console.error('Global error:', e.message);
});