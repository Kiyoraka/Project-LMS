/**
 * Buttons Component Module
 * Handles button click events and interactions
 */

/**
 * Initialize login button functionality
 * @param {string} selector - CSS selector for login button
 */
export function initLoginButton(selector = '#loginBtn') {
    const loginBtn = document.querySelector(selector);
    if (!loginBtn) return;
    if (!document.getElementById('loginModal')) {
        createLoginModal();
    }
    loginBtn.addEventListener('click', function() {
        showModal('loginModal');
    });
}

function createLoginModal() {
    const overlay = document.createElement('div');
    overlay.id = 'modalOverlay';
    overlay.className = 'modal-overlay';
    const modal = document.createElement('div');
    modal.id = 'loginModal';
    modal.className = 'modal login-modal';
    modal.style.removeProperty('display');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    modal.innerHTML = `
        <div class="modal-header">
            <h2>Login to Your Account</h2>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <form method="POST" action="/login">
                <input type="hidden" name="_token" value="${csrfToken}">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>
                <div class="form-options">
                    <label class="checkbox-container">
                        <input type="checkbox" name="remember">
                        <span class="checkmark"></span>
                        Remember me
                    </label>
                    <a href="/forgot-password" class="forgot-password">Forgot password?</a>
                </div>
                <button type="submit" class="btn-primary btn-block">Login</button>
            </form>
            <div class="modal-footer">
                <p>Don't have an account? <a href="#" id="openSignupFromLogin">Register</a></p>
            </div>
        </div>
    `;
    document.body.appendChild(overlay);
    document.body.appendChild(modal);
    
    // Add event listeners
    const closeBtn = modal.querySelector('.modal-close');
    closeBtn.addEventListener('click', () => hideModal('loginModal'));
    overlay.addEventListener('click', () => hideModal('loginModal'));
    
    // Register link logic
    const registerLink = modal.querySelector('#openSignupFromLogin');
    if (registerLink) {
        registerLink.addEventListener('click', function(e) {
            e.preventDefault();
            hideModal('loginModal');
            showModal('signupModal');
        });
    }
}

/**
 * Initialize sign up button functionality
 * @param {string} selector - CSS selector for sign up button
 */
export function initSignupButton(selector = '#signupBtn') {
    const signupBtn = document.querySelector(selector);
    if (!signupBtn) return;
    if (!document.getElementById('signupModal')) {
        createSignupModal();
    }
    signupBtn.addEventListener('click', function() {
        showModal('signupModal');
    });
}

function createSignupModal() {
    const overlay = document.createElement('div');
    overlay.id = 'modalOverlay';
    overlay.className = 'modal-overlay';
    const modal = document.createElement('div');
    modal.id = 'signupModal';
    modal.className = 'modal login-modal';
    modal.style.removeProperty('display');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    modal.innerHTML = `
        <div class="modal-header">
            <h2>Sign Up for an Account</h2>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <form id="signupForm" method="POST" action="/register">
                <input type="hidden" name="_token" value="${csrfToken}">
                <div class="form-group">
                    <label for="signupName">Full Name</label>
                    <input type="text" id="signupName" name="name" placeholder="Enter your name" required>
                </div>
                <div class="form-group">
                    <label for="signupEmail">Email</label>
                    <input type="email" id="signupEmail" name="email" placeholder="Enter your email" required>
                </div>
                <div class="form-group">
                    <label for="signupRole">Role</label>
                    <select id="signupRole" name="role" required>
                        <option value="student">Student</option>
                        <option value="teacher">Teacher (requires admin approval)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="signupPassword">Password</label>
                    <input type="password" id="signupPassword" name="password" placeholder="Enter your password" required>
                </div>
                <div class="form-group">
                    <label for="signupPasswordConfirm">Confirm Password</label>
                    <input type="password" id="signupPasswordConfirm" name="password_confirmation" placeholder="Confirm your password" required>
                </div>
                <button type="submit" class="btn-primary btn-block">Sign Up</button>
            </form>
            <div class="modal-footer">
                <p>Already have an account? <a href="#" id="openLoginFromSignup">Login</a></p>
            </div>
        </div>
    `;
    document.body.appendChild(overlay);
    document.body.appendChild(modal);
    
    // Add event listeners
    const closeBtn = modal.querySelector('.modal-close');
    closeBtn.addEventListener('click', () => hideModal('signupModal'));
    overlay.addEventListener('click', () => hideModal('signupModal'));
    
    // Login link logic
    const loginLink = modal.querySelector('#openLoginFromSignup');
    if (loginLink) {
        loginLink.addEventListener('click', function(e) {
            e.preventDefault();
            hideModal('signupModal');
            showModal('loginModal');
        });
    }

    // AJAX registration logic
    const signupForm = modal.querySelector('#signupForm');
    if (signupForm) {
        signupForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            // Gather form data
            const formData = new FormData(signupForm);
            // Clear previous errors
            let errorDiv = modal.querySelector('.signup-error');
            if (errorDiv) errorDiv.remove();
            // Send AJAX request
            try {
                const response = await fetch('/register', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    body: formData
                });
                if (response.ok) {
                    // Success: show custom popup
                    const role = formData.get('role');
                    let message = '';
                    if (role === 'teacher') {
                        message = 'Registration successful! Your account requires admin approval. You will be notified once approved.';
                    } else {
                        message = 'Registration successful! You can now log in.';
                    }
                    hideModal('signupModal');
                    showSuccessModal(message);
                } else if (response.status === 422) {
                    // Validation error
                    const data = await response.json();
                    let errorMsg = 'Registration failed:';
                    if (data.errors) {
                        errorMsg += '<ul>' + Object.values(data.errors).map(errArr => `<li>${errArr[0]}</li>`).join('') + '</ul>';
                    }
                    showSignupError(modal, errorMsg);
                } else {
                    showSignupError(modal, 'Registration failed. Please try again.');
                }
            } catch (err) {
                showSignupError(modal, 'Registration failed. Please try again.');
            }
        });
    }
}

function showSignupError(modal, message) {
    let errorDiv = modal.querySelector('.signup-error');
    if (!errorDiv) {
        errorDiv = document.createElement('div');
        errorDiv.className = 'signup-error';
        errorDiv.style.color = 'red';
        errorDiv.style.marginBottom = '16px';
        modal.querySelector('.modal-body').prepend(errorDiv);
    }
    errorDiv.innerHTML = message;
}

function showSuccessModal(message) {
    // Remove any existing success modal/overlay
    let oldModal = document.getElementById('successModal');
    let oldOverlay = document.getElementById('successModalOverlay');
    if (oldModal) oldModal.remove();
    if (oldOverlay) oldOverlay.remove();
    // Create overlay
    const overlay = document.createElement('div');
    overlay.id = 'successModalOverlay';
    overlay.className = 'modal-overlay active';
    // Create modal
    const modal = document.createElement('div');
    modal.id = 'successModal';
    modal.className = 'modal login-modal active';
    modal.innerHTML = `
        <div class="modal-header">
            <h2>Registration Successful</h2>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <p style="font-size:1.1rem; margin-bottom: 24px;">${message}</p>
            <button class="btn-primary btn-block" id="successModalLoginBtn">Go to Login</button>
        </div>
    `;
    document.body.appendChild(overlay);
    document.body.appendChild(modal);
    // Close logic
    const closeBtn = modal.querySelector('.modal-close');
    closeBtn.addEventListener('click', () => {
        modal.remove();
        overlay.remove();
    });
    overlay.addEventListener('click', () => {
        modal.remove();
        overlay.remove();
    });
    // Go to login button
    const loginBtn = modal.querySelector('#successModalLoginBtn');
    loginBtn.addEventListener('click', () => {
        modal.remove();
        overlay.remove();
        showModal('loginModal');
    });
}

// Helper functions for modal management
function showModal(modalId) {
    let modal = document.getElementById(modalId);
    const overlay = document.getElementById('modalOverlay');
    // Create modal if it doesn't exist
    if (!modal) {
        if (modalId === 'loginModal') {
            createLoginModal();
        } else if (modalId === 'signupModal') {
            createSignupModal();
        }
        modal = document.getElementById(modalId);
    }
    if (modal && overlay) {
        modal.classList.add('active');
        overlay.classList.add('active');
    }
}

function hideModal(modalId) {
    const modal = document.getElementById(modalId);
    const overlay = document.getElementById('modalOverlay');
    if (modal && overlay) {
        modal.classList.remove('active');
        overlay.classList.remove('active');
    }
}

/**
 * Initialize CTA (Call to Action) buttons
 * @param {Object} config - Configuration object with button selectors
 */
export function initCtaButtons(config = {}) {
    const selectors = {
        getStarted: '#getStartedBtn',
        startTrial: '#startTrialBtn',
        requestDemo: '#requestDemoBtn',
        ...config
    };
    // Get Started button
    const getStartedBtn = document.querySelector(selectors.getStarted);
    if (getStartedBtn) {
        getStartedBtn.addEventListener('click', function() {
            window.location.href = '/register';
        });
    }
    // Start Trial button
    const startTrialBtn = document.querySelector(selectors.startTrial);
    if (startTrialBtn) {
        startTrialBtn.addEventListener('click', function() {
            window.location.href = '/trial';
        });
    }
    // Request Demo button
    const requestDemoBtn = document.querySelector(selectors.requestDemo);
    if (requestDemoBtn) {
        requestDemoBtn.addEventListener('click', function() {
            // Open demo request modal or redirect
        });
    }
}

/**
 * Initialize button hover effects
 * @param {string} selector - CSS selector for buttons with hover effects
 */
export function initButtonHoverEffects(selector = '.animate-hover') {
    const buttons = document.querySelectorAll(selector);
    buttons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-3px)';
            this.style.boxShadow = '0 10px 20px rgba(0, 0, 0, 0.2)';
        });
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = 'none';
        });
    });
}