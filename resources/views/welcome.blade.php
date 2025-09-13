<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Aidel LMS - Learning Management System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
        box-sizing: border-box;
    }

        body {
            font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
            background-color: #ffffff;
            overflow: hidden;
        }

        .auth-container {
            display: flex;
            height: 100vh;
            width: 100%;
            position: relative;
        }

        /* Gradient Side */
        .auth-gradient {
            flex: 1;
            background: linear-gradient(120deg, #0f0519 0%, #1a0b2e 25%, #2d1b4e 50%, #4c1d95 75%, #7c3aed 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            text-align: center;
            padding: 4rem 2rem;
            position: relative;
            overflow: hidden;
            border-radius: 0;
            transform: scale(1);
            transition: all 1.5s cubic-bezier(0.4, 0.0, 0.2, 1);
        }

        .auth-gradient::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml;utf8,<svg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><g fill="%23ffffff" fill-opacity="0.1"><circle cx="30" cy="30" r="1"/></g></svg>') repeat;
            opacity: 0.3;
        }

        .welcome-content {
            position: relative;
            z-index: 2;
            transition: opacity 0.5s cubic-bezier(0.4, 0.0, 0.2, 1);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
            text-align: center;
        }

        .welcome-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
            line-height: 1.2;
            text-align: center;
            width: 100%;
        }

        .welcome-subtitle {
            font-size: 1.125rem;
            opacity: 0.9;
            margin-bottom: 2rem;
            line-height: 1.6;
            max-width: 400px;
            text-align: center;
            margin-left: auto;
            margin-right: auto;
        }

        .switch-button {
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 12px 32px;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            backdrop-filter: blur(10px);
            display: block;
            margin: 0 auto;
            align-self: center;
        }

        .switch-button:hover:not(.switching) {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
        }

        .switch-button.switching {
            pointer-events: none;
            opacity: 0.7;
        }

        /* Form Side */
        .auth-form-side {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 0;
            transform: scale(1);
            transition: all 1.5s cubic-bezier(0.4, 0.0, 0.2, 1);
        }

        /* Animation states for switching */
        .auth-gradient.switching {
            transform: scale(0.7);
            border-radius: 30px;
            margin: 20px;
        }

        .auth-form-side.switching {
            transform: scale(0.7);
            border-radius: 30px;
            margin: 20px;
        }

        /* Position states */
        /* Sign Up mode: Form RIGHT, Gradient LEFT */
        .auth-container.switch-mode .auth-gradient {
            order: 1; /* Left side */
        }

        .auth-container.switch-mode .auth-form-side {
            order: 2; /* Right side */
        }

        /* Sign In mode: Form LEFT, Gradient RIGHT */
        .auth-container:not(.switch-mode) .auth-gradient {
            order: 2; /* Right side */
        }

        .auth-container:not(.switch-mode) .auth-form-side {
            order: 1; /* Left side */
        }

        .form-container {
            width: 100%;
            max-width: 400px;
        }

        .auth-form {
            width: 100%;
        }

        .form-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .form-header h2 {
            font-size: 2rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        .form-header p {
            color: #6b7280;
            font-size: 1rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #374151;
            font-size: 0.875rem;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
            background: white;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #8b5cf6;
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
        }

        .form-group input::placeholder {
            color: #9ca3af;
        }

        .checkbox-group {
        display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .checkbox-wrapper input[type="checkbox"] {
            width: auto;
            accent-color: #8b5cf6;
        }

        .checkbox-wrapper label {
            margin: 0;
            font-size: 0.875rem;
            color: #6b7280;
            cursor: pointer;
        }

        .forgot-link {
            color: #8b5cf6;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .forgot-link:hover {
            color: #7c3aed;
            text-decoration: underline;
        }

        .submit-btn {
        width: 100%;
            background: linear-gradient(120deg, #0f0519 0%, #2d1b4e 50%, #4c1d95 75%, #7c3aed 100%);
        color: white;
            border: none;
            padding: 0.875rem 1rem;
            border-radius: 8px;
            font-size: 1rem;
        font-weight: 600;
            cursor: pointer;
            margin-bottom: 1.5rem;
        }

        .submit-btn:hover {
            box-shadow: 0 5px 15px rgba(124, 58, 237, 0.3);
        }

        .submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .form-footer {
            text-align: center;
        }

        .form-footer p {
        color: #6b7280;
            font-size: 0.875rem;
        }

        .form-footer a {
            color: #8b5cf6;
            text-decoration: none;
        font-weight: 600;
        }

        .form-footer a:hover {
            color: #7c3aed;
            text-decoration: underline;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .auth-container {
                flex-direction: column !important;
                overflow-y: auto;
            }

            .auth-gradient,
            .auth-form-side {
                order: initial !important;
            }

            .auth-gradient {
                flex: none;
                height: 40vh;
                padding: 2rem 1rem;
        display: flex;
                justify-content: center;
        align-items: center;
            }

            .welcome-content {
                width: 100%;
                max-width: 100%;
                padding: 0 1rem;
            }

            .welcome-title {
                font-size: 2rem;
                text-align: center;
                width: 100%;
            }

            .welcome-subtitle {
                font-size: 1rem;
                margin-bottom: 1.5rem;
                text-align: center;
                max-width: 90%;
                margin-left: auto;
                margin-right: auto;
            }

            .switch-button {
                margin: 0 auto;
                display: block;
            }

            .auth-form-side {
                flex: none;
                min-height: 60vh;
                padding: 2rem 1rem;
            }

            .form-header h2 {
                font-size: 1.75rem;
            }
        }

        @media (max-width: 480px) {
            .auth-gradient {
                height: 35vh;
                padding: 1.5rem 1rem;
            }

            .welcome-title {
                font-size: 1.75rem;
                text-align: center;
            }

            .welcome-subtitle {
                font-size: 0.95rem;
                max-width: 95%;
            text-align: center;
            }

            .auth-form-side {
                padding: 1.5rem 1rem;
            }

            .form-container {
                max-width: 100%;
            }
        }

        .auth-form {
            opacity: 1;
            transition: opacity 0.75s cubic-bezier(0.4, 0.0, 0.2, 1);
        }

        .auth-form.hidden {
            display: none;
        }

        .auth-form.switching {
            opacity: 0.3;
            pointer-events: none;
        }

        /* Custom Error Modal Styles */
        .error-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.6);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            padding: 20px;
            opacity: 0;
            transition: opacity 0.3s cubic-bezier(0.4, 0.0, 0.2, 1);
        }

        .error-modal-overlay.show {
            opacity: 1;
        }

        .error-modal-content {
            background: white;
            border-radius: 16px;
            max-width: 480px;
            width: 100%;
            max-height: 90vh;
            overflow: hidden;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            transform: scale(0.95);
            transition: transform 0.3s cubic-bezier(0.4, 0.0, 0.2, 1);
        }

        .error-modal-overlay.show .error-modal-content {
            transform: scale(1);
        }

        .error-modal-header {
            background: linear-gradient(135deg, #4c1d95, #7c3aed);
            color: white;
            padding: 24px;
            display: flex;
            align-items: center;
            gap: 16px;
            position: relative;
        }

        .error-icon {
            width: 48px;
            height: 48px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }

        .error-modal-header h3 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
            flex: 1;
        }

        .modal-close {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            font-size: 1.2rem;
            padding: 8px;
            border-radius: 50%;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s;
            flex-shrink: 0;
        }

        .modal-close:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .error-modal-body {
            padding: 32px 24px;
        }

        .error-modal-body p {
            margin: 0;
            color: #374151;
            font-size: 1rem;
            line-height: 1.6;
            text-align: center;
        }

        .error-modal-footer {
            padding: 20px 24px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: center;
        }

        .error-modal-btn {
            background: linear-gradient(135deg, #4c1d95, #7c3aed);
            color: white;
            border: none;
            padding: 12px 32px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            min-width: 120px;
        }

        .error-modal-btn:hover {
            background: linear-gradient(135deg, #3730a3, #6d28d9);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(124, 58, 237, 0.4);
        }

        .error-modal-btn:active {
            transform: translateY(0);
        }

        /* Responsive adjustments */
        @media (max-width: 640px) {
            .error-modal-content {
                margin: 16px;
                max-width: none;
                width: calc(100% - 32px);
            }
            
            .error-modal-header {
                padding: 20px;
                gap: 12px;
            }
            
            .error-icon {
                width: 40px;
                height: 40px;
                font-size: 18px;
            }
            
            .error-modal-header h3 {
                font-size: 1.1rem;
            }
            
            .error-modal-body {
                padding: 24px 20px;
            }
            
            .error-modal-footer {
                padding: 16px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <!-- Gradient Side -->
        <div class="auth-gradient" id="authGradient">
            <div class="welcome-content">
                <h1 class="welcome-title" id="welcomeTitle">Welcome Back, Learner!</h1>
                <p class="welcome-subtitle" id="welcomeSubtitle">
                    Continue your educational journey and access your courses, progress, and achievements
                </p>
                <button class="switch-button" id="switchButton" onclick="toggleForm()">
                    SIGN UP
                </button>
                </div>
            </div>
            
        <!-- Form Side -->
        <div class="auth-form-side" id="authFormSide">
            <div class="form-container">
                <!-- Login Form -->
                <form class="auth-form" id="loginForm" method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="form-header">
                        <h2>Sign In</h2>
                        <p>or use your email account</p>
                    </div>

                    <div class="form-group">
                        <label for="login-email">Email</label>
                        <input type="email" id="login-email" name="email" placeholder="Enter your email" required>
            </div>
            
                    <div class="form-group">
                        <label for="login-password">Password</label>
                        <input type="password" id="login-password" name="password" placeholder="Enter your password" required>
                </div>
                
                    <div class="checkbox-group">
                        <div class="checkbox-wrapper">
                            <input type="checkbox" id="remember-me" name="remember" value="1">
                            <label for="remember-me">Remember me</label>
                    </div>
                        <a href="#" class="forgot-link">Forgot password?</a>
                </div>
                
                    <button type="submit" class="submit-btn">SIGN IN</button>

                    <div class="form-footer">
                        <p>Don't have an account? <a href="#" onclick="toggleForm(); return false;">Sign Up</a></p>
                    </div>
                </form>

                <!-- Register Form -->
                <form class="auth-form hidden" id="registerForm" method="POST" action="{{ route('register') }}">
                    @csrf
                    <div class="form-header">
                        <h2>Create Account</h2>
                        <p>or use your email for registration</p>
                    </div>

                    <div class="form-group">
                        <label for="register-name">Name</label>
                        <input type="text" id="register-name" name="name" placeholder="Name" required>
    </div>

                    <div class="form-group">
                        <label for="register-email">Email</label>
                        <input type="email" id="register-email" name="email" placeholder="Email" required>
                    </div>

                    <div class="form-group">
                        <label for="register-password">Password</label>
                        <input type="password" id="register-password" name="password" placeholder="Password" required>
                    </div>

                    <div class="form-group">
                        <label for="register-password-confirmation">Confirm Password</label>
                        <input type="password" id="register-password-confirmation" name="password_confirmation" placeholder="Confirm Password" required>
                    </div>

                    <div class="form-group">
                        <label for="register-role">Register as:</label>
                        <select id="register-role" name="role" required>
                            <option value="">Select Role</option>
                            <option value="student">Student</option>
                            <option value="teacher">Teacher</option>
                        </select>
                    </div>
                    
                    <input type="hidden" name="terms" value="1">
                    
                    <button type="submit" class="submit-btn">SIGN UP</button>

                    <div class="form-footer">
                        <p>Already have an account? <a href="#" onclick="toggleForm(); return false;">Sign In</a></p>
                </div>
            </form>
            </div>
        </div>
    </div>

    <script>
                // Global variable to prevent multiple toggles during animation
        let isToggling = false;

        // Box shrinking and switching animation
        function toggleForm() {
            // Prevent multiple rapid clicks during animation
            if (isToggling) return;
            isToggling = true;
            
            const loginForm = document.getElementById('loginForm');
            const registerForm = document.getElementById('registerForm');
            const welcomeTitle = document.getElementById('welcomeTitle');
            const welcomeSubtitle = document.getElementById('welcomeSubtitle');
            const switchButton = document.getElementById('switchButton');
            const authGradient = document.getElementById('authGradient');
            const authFormSide = document.getElementById('authFormSide');
            const authContainer = document.querySelector('.auth-container');
            const welcomeContent = document.querySelector('.welcome-content');

            // Disable interactions
            switchButton.classList.add('switching');
            
            // Get current and next forms
            const isShowingLogin = !loginForm.classList.contains('hidden');
            const currentForm = isShowingLogin ? loginForm : registerForm;
            const nextForm = isShowingLogin ? registerForm : loginForm;
            
            // Phase 1: Shrink both boxes (0-0.75s) - keep current form visible
            authGradient.classList.add('switching');
            authFormSide.classList.add('switching');
            
            // Phase 2: Switch positions while small (0.75s) - still keep current form
            setTimeout(() => {
                if (isShowingLogin) {
                    // Switching to register form - form goes right, gradient goes left
                    authContainer.classList.add('switch-mode');
                } else {
                    // Switching to login form - form goes left, gradient goes right
                    authContainer.classList.remove('switch-mode');
                }
            }, 750);
            
            // Phase 3: Now switch forms and update text while boxes are small (1.25s)
            setTimeout(() => {
                if (isShowingLogin) {
                    welcomeTitle.textContent = 'Start Your Learning Journey!';
                    welcomeSubtitle.textContent = 'Join thousands of students and unlock your potential with our comprehensive Learning Management System';
                    switchButton.textContent = 'SIGN IN';
                } else {
                    welcomeTitle.textContent = 'Welcome Back, Learner!';
                    welcomeSubtitle.textContent = 'Continue your educational journey and access your courses, progress, and achievements';
                    switchButton.textContent = 'SIGN UP';
                }
                
                // Switch forms while boxes are still small
                currentForm.classList.add('hidden');
                nextForm.classList.remove('hidden');
                nextForm.classList.add('switching');
            }, 1250);
            
            // Phase 4: Grow boxes back to full size (1.75s)
            setTimeout(() => {
                authGradient.classList.remove('switching');
                authFormSide.classList.remove('switching');
                nextForm.classList.remove('switching');
            }, 1750);
            
            // Phase 5: Complete animation (2.5s)
            setTimeout(() => {
                switchButton.classList.remove('switching');
                isToggling = false;
            }, 2500);
        }

        // Handle form submissions
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('.submit-btn');
            const originalText = submitBtn.textContent;
            
            submitBtn.disabled = true;
            submitBtn.textContent = 'Signing In...';
            
            fetch('{{ route('login') }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': this.querySelector('input[name="_token"]').value,
                },
            })
            .then(response => {
                if (response.redirected) {
                    window.location.href = response.url;
                    return null;
                }
                    return response.json().catch(() => null);
            })
            .then(data => {
                if (!data) return;
                
                if (data.success && data.redirect) {
                        window.location.href = data.redirect;
                    return;
                }
                
                showErrorModal(data.message || 'Login failed. Please check your credentials.', 'Login Failed');
            })
            .catch(error => {
                console.error('Login error:', error);
                showErrorModal('An error occurred during login. Please try again.', 'Connection Error');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            });
        });
        
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('.submit-btn');
            const originalText = submitBtn.textContent;
            
            submitBtn.disabled = true;
            submitBtn.textContent = 'Creating Account...';
            
            fetch('{{ route('register') }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': this.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message || 'Registration successful!');
                    this.reset();
                        if (data.redirect) {
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 1500);
                    }
                } else {
                    let errorMsg = 'Registration failed. Please try again.';
                    if (data.errors) {
                        errorMsg = Object.values(data.errors).flat().join(' ');
                    } else if (data.message) {
                        errorMsg = data.message;
                    }
                    showErrorModal(errorMsg, 'Registration Failed');
                }
            })
            .catch(error => {
                console.error('Registration error:', error);
                showErrorModal('An error occurred during registration. Please try again.', 'Connection Error');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            });
        });

        // Handle forgot password link
        document.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('forgot-link')) {
                e.preventDefault();
                showErrorModal('Password reset feature coming soon! Please contact the administrator for password recovery.', 'Feature Not Available');
            }
        });

        // Simple page initialization
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('loginForm');
            const registerForm = document.getElementById('registerForm');
            
            // Clean initial state
            if (loginForm) loginForm.classList.remove('switching');
            if (registerForm) registerForm.classList.remove('switching');
            
            console.log('✅ Landing page ready');
    });
    
    // Custom Error Modal Functions
    function showErrorModal(message, title = 'Error') {
        const modal = document.getElementById('errorModal');
        const modalTitle = document.getElementById('errorModalTitle');
        const modalMessage = document.getElementById('errorModalMessage');
        
        modalTitle.textContent = title;
        modalMessage.textContent = message;
        modal.style.display = 'flex';
        
        // Add entrance animation
        setTimeout(() => {
            modal.classList.add('show');
        }, 10);
    }
    
    function hideErrorModal() {
        const modal = document.getElementById('errorModal');
        modal.classList.remove('show');
        
        // Wait for animation to complete before hiding
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }
    
    // Close modal when clicking overlay or close button
    document.addEventListener('click', function(e) {
        if (e.target && (e.target.id === 'errorModal' || e.target.classList.contains('modal-close'))) {
            hideErrorModal();
        }
    });
    
    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            hideErrorModal();
        }
    });
    </script>

    <!-- Custom Error Modal -->
    <div id="errorModal" class="error-modal-overlay">
        <div class="error-modal-content">
            <div class="error-modal-header">
                <div class="error-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h3 id="errorModalTitle">Error</h3>
                <button class="modal-close" type="button">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="error-modal-body">
                <p id="errorModalMessage">An error occurred</p>
            </div>
            <div class="error-modal-footer">
                <button class="error-modal-btn" onclick="hideErrorModal()">OK</button>
            </div>
        </div>
    </div>
</body>
</html>