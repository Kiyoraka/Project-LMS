<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Learning Management System Prototype - Transform Your Learning Experience</title>
    <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
    /* Ensure both auth buttons are exactly the same size and style */
    .nav-auth-btn {
        min-width: 120px;
        padding: 8px 24px;
        border-radius: 24px;
        font-size: 1rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        box-sizing: border-box;
    }

    /* Login Modal Styles - Centered with Purple Gradient Overlay */
    #loginModal.modal-overlay {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100% !important;
        height: 100% !important;
        background: linear-gradient(120deg, rgba(0, 0, 0, 0.5) 0%, rgba(0, 29, 61, 0.4) 40%, rgba(3, 79, 132, 0.4) 75%, rgba(0, 180, 216, 0.3) 100%) !important;
        backdrop-filter: blur(5px) !important;
        -webkit-backdrop-filter: blur(5px) !important;
        justify-content: center !important;
        align-items: center !important;
        z-index: 9999 !important;
    }

    /* Modal visibility control */
    #loginModal[style*="display: none"] {
        display: none !important;
    }
    
    #loginModal[style*="display: flex"] {
        display: flex !important;
    }
    
    #loginModal.hidden {
        display: none !important;
        visibility: hidden !important;
        opacity: 0 !important;
    }

    #loginModal .modal-container {
        background: white !important;
        border-radius: 16px !important;
        padding: 0 !important;
        width: 90% !important;
        max-width: 400px !important;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
        animation: modalSlideIn 0.3s ease-out !important;
        position: relative !important;
    }

    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: scale(0.9) translateY(-20px);
        }
        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }

    #loginModal .modal-header {
        background: linear-gradient(120deg, #001d3d 0%, #003566 25%, #034f84 50%, #0077b6 75%, #00b4d8 100%) !important;
        color: white !important;
        padding: 20px !important;
        border-radius: 16px 16px 0 0 !important;
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
    }

    #loginModal .modal-header h3 {
        margin: 0 !important;
        font-size: 1.25rem !important;
        font-weight: 600 !important;
    }

    #loginModal .modal-close {
        background: none !important;
        border: none !important;
        color: white !important;
        font-size: 1.5rem !important;
        cursor: pointer !important;
        padding: 0 !important;
        width: 30px !important;
        height: 30px !important;
        border-radius: 50% !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        transition: background-color 0.2s !important;
        z-index: 10001 !important;
        position: relative !important;
        pointer-events: auto !important;
        outline: none !important;
    }

    #loginModal .modal-close:hover {
        background: rgba(255, 255, 255, 0.2) !important;
    }

    #loginModal .modal-body {
        padding: 24px !important;
    }

    #loginModal .form-group {
        margin-bottom: 20px !important;
    }

    #loginModal .form-group label {
        display: block !important;
        margin-bottom: 6px !important;
        font-weight: 500 !important;
        color: #374151 !important;
    }

    #loginModal .form-group input[type="email"],
    #loginModal .form-group input[type="password"],
    #registerModal .form-group input[type="text"],
    #registerModal .form-group input[type="email"],
    #registerModal .form-group input[type="password"],
    #registerModal .form-group select {
        width: 100% !important;
        padding: 12px !important;
        border: 2px solid #e5e7eb !important;
        border-radius: 8px !important;
        font-size: 1rem !important;
        transition: border-color 0.2s !important;
        box-sizing: border-box !important;
        background-color: white !important;
    }

    #loginModal .form-group input:focus,
    #registerModal .form-group input:focus,
    #registerModal .form-group select:focus {
        outline: none !important;
        border-color: #00b4d8 !important;
        box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1) !important;
    }

    #loginModal .checkbox-group {
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        margin-bottom: 24px !important;
    }

    #loginModal .checkbox-group label {
        display: flex !important;
        align-items: center !important;
        margin: 0 !important;
        font-size: 0.9rem !important;
        color: #6b7280 !important;
        cursor: pointer !important;
    }

    #loginModal .checkbox-group input[type="checkbox"] {
        margin-right: 8px !important;
        width: 16px !important;
        height: 16px !important;
        border: 2px solid #d1d5db !important;
        border-radius: 4px !important;
        cursor: pointer !important;
        accent-color: #8b5cf6 !important;
    }

    #loginModal .checkbox-group input[type="checkbox"]:checked {
        background-color: #8b5cf6 !important;
        border-color: #00b4d8 !important;
    }

    #loginModal .forgot-link {
        color: #00b4d8 !important;
        text-decoration: none !important;
        font-size: 0.9rem !important;
        font-weight: 500 !important;
        transition: all 0.2s ease !important;
        padding: 2px 4px !important;
        border-radius: 4px !important;
    }

    #loginModal .forgot-link:hover {
        text-decoration: none !important;
        background-color: rgba(139, 92, 246, 0.1) !important;
        color: #7c3aed !important;
    }

    #loginModal .modal-footer {
        padding: 0 24px 24px !important;
    }

    #loginModal .login-submit-btn {
        width: 100% !important;
        background: linear-gradient(120deg, #001d3d 0%, #034f84 50%, #0077b6 75%, #00b4d8 100%) !important;
        color: white !important;
        border: none !important;
        padding: 12px !important;
        border-radius: 8px !important;
        font-size: 1rem !important;
        font-weight: 600 !important;
        cursor: pointer !important;
        transition: opacity 0.2s !important;
        margin-bottom: 16px !important;
    }

    #loginModal .login-submit-btn:hover {
        opacity: 0.9 !important;
    }

    #loginModal .register-link {
        text-align: center !important;
        margin: 0 !important;
        font-size: 0.9rem !important;
        color: #6b7280 !important;
    }

    #loginModal .register-link a {
        color: #00b4d8 !important;
        text-decoration: none !important;
        font-weight: 600 !important;
        transition: all 0.2s ease !important;
        padding: 2px 6px !important;
        border-radius: 4px !important;
        border: 1px solid transparent !important;
    }

    #loginModal .register-link a:hover,
    #registerModal .register-link a:hover {
        text-decoration: none !important;
        background-color: rgba(139, 92, 246, 0.1) !important;
        border-color: #00b4d8 !important;
        color: #7c3aed !important;
    }

    /* Future Development Modal - Same as Login Modal */
    #futureDevModal.modal-overlay,
    #marketplaceModal.modal-overlay {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100% !important;
        height: 100% !important;
        background: linear-gradient(120deg, rgba(0, 0, 0, 0.5) 0%, rgba(0, 29, 61, 0.4) 40%, rgba(3, 79, 132, 0.4) 75%, rgba(0, 180, 216, 0.3) 100%) !important;
        backdrop-filter: blur(5px) !important;
        -webkit-backdrop-filter: blur(5px) !important;
        display: flex !important;
        justify-content: center !important;
        align-items: center !important;
        z-index: 9999 !important;
    }

    #futureDevModal .modal-container,
    #marketplaceModal .modal-container {
        background: white !important;
        border-radius: 16px !important;
        padding: 0 !important;
        width: 90% !important;
        max-width: 500px !important;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
        animation: modalSlideIn 0.3s ease-out !important;
        position: relative !important;
    }

    #futureDevModal .modal-header,
    #marketplaceModal .modal-header {
        background: linear-gradient(120deg, #001d3d 0%, #003566 25%, #034f84 50%, #0077b6 75%, #00b4d8 100%) !important;
        color: white !important;
        padding: 20px !important;
        border-radius: 16px 16px 0 0 !important;
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
    }

    #futureDevModal .modal-close,
    #marketplaceModal .modal-close {
        background: none !important;
        border: none !important;
        color: white !important;
        font-size: 1.5rem !important;
        cursor: pointer !important;
        padding: 0 !important;
        width: 30px !important;
        height: 30px !important;
        border-radius: 50% !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        transition: background-color 0.2s !important;
        z-index: 10001 !important;
        position: relative !important;
        pointer-events: auto !important;
        outline: none !important;
    }

    #futureDevModal .modal-close:hover,
    #marketplaceModal .modal-close:hover {
        background: rgba(255, 255, 255, 0.2) !important;
    }

    #futureDevModal .modal-body,
    #marketplaceModal .modal-body {
        padding: 40px 24px !important;
        text-align: center !important;
    }

    #futureDevModal .modal-footer,
    #marketplaceModal .modal-footer {
        padding: 0 24px 24px !important;
    }

    #futureDevModal .login-submit-btn,
    #marketplaceModal .login-submit-btn {
        width: 100% !important;
        background: linear-gradient(120deg, #001d3d 0%, #034f84 50%, #0077b6 75%, #00b4d8 100%) !important;
        color: white !important;
        border: none !important;
        padding: 12px !important;
        border-radius: 8px !important;
        font-size: 1rem !important;
        font-weight: 600 !important;
        cursor: pointer !important;
        transition: opacity 0.2s !important;
    }

    #futureDevModal .login-submit-btn:hover,
    #marketplaceModal .login-submit-btn:hover {
        opacity: 0.9 !important;
    }

    /* Register Modal Inherits All Login Modal Styles */
    #registerModal.modal-overlay {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100% !important;
        height: 100% !important;
        background: linear-gradient(120deg, rgba(0, 0, 0, 0.5) 0%, rgba(0, 29, 61, 0.4) 40%, rgba(3, 79, 132, 0.4) 75%, rgba(0, 180, 216, 0.3) 100%) !important;
        backdrop-filter: blur(5px) !important;
        -webkit-backdrop-filter: blur(5px) !important;
        justify-content: center !important;
        align-items: center !important;
        z-index: 9999 !important;
    }

    #registerModal .modal-container {
        background: white !important;
        border-radius: 16px !important;
        padding: 0 !important;
        width: 90% !important;
        max-width: 400px !important;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
        animation: modalSlideIn 0.3s ease-out !important;
        position: relative !important;
    }

    #registerModal .modal-header {
        background: linear-gradient(120deg, #001d3d 0%, #003566 25%, #034f84 50%, #0077b6 75%, #00b4d8 100%) !important;
        color: white !important;
        padding: 20px !important;
        border-radius: 16px 16px 0 0 !important;
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
    }

    #registerModal .modal-close {
        background: none !important;
        border: none !important;
        color: white !important;
        font-size: 1.5rem !important;
        cursor: pointer !important;
        padding: 0 !important;
        width: 30px !important;
        height: 30px !important;
        border-radius: 50% !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        transition: background-color 0.2s !important;
        z-index: 10001 !important;
        position: relative !important;
        pointer-events: auto !important;
        outline: none !important;
    }

    #registerModal .modal-close:hover {
        background: rgba(255, 255, 255, 0.2) !important;
    }

    #registerModal .modal-body {
        padding: 24px !important;
    }

    #registerModal .form-group {
        margin-bottom: 20px !important;
    }

    #registerModal .form-group label {
        display: block !important;
        margin-bottom: 6px !important;
        font-weight: 500 !important;
        color: #374151 !important;
    }

    #registerModal .modal-footer {
        padding: 0 24px 24px !important;
    }

    #registerModal .login-submit-btn {
        width: 100% !important;
        background: linear-gradient(120deg, #001d3d 0%, #034f84 50%, #0077b6 75%, #00b4d8 100%) !important;
        color: white !important;
        border: none !important;
        padding: 12px !important;
        border-radius: 8px !important;
        font-size: 1rem !important;
        font-weight: 600 !important;
        cursor: pointer !important;
        transition: opacity 0.2s !important;
        margin-bottom: 16px !important;
    }

    #registerModal .login-submit-btn:hover {
        opacity: 0.9 !important;
    }

    #registerModal .register-link {
        text-align: center !important;
        margin: 0 !important;
        font-size: 0.9rem !important;
        color: #6b7280 !important;
    }

    #registerModal .register-link a {
        color: #00b4d8 !important;
        text-decoration: none !important;
        font-weight: 600 !important;
        transition: all 0.2s ease !important;
        padding: 2px 6px !important;
        border-radius: 4px !important;
        border: 1px solid transparent !important;
    }

    .role-info {
        background: #f3f4f6 !important;
        border: 1px solid #d1d5db !important;
        border-radius: 8px !important;
        padding: 12px !important;
        margin-top: 8px !important;
        font-size: 0.9rem !important;
        color: #6b7280 !important;
    }

    .role-info.student {
        background: #ecfdf5 !important;
        border-color: #10b981 !important;
        color: #065f46 !important;
    }

    .role-info.teacher {
        background: #fef3c7 !important;
        border-color: #f59e0b !important;
        color: #92400e !important;
    }

    /* Password Strength Indicator */
    .password-strength {
        margin-top: 8px !important;
    }

    .strength-meter {
        width: 100% !important;
        height: 8px !important;
        background: #e5e7eb !important;
        border-radius: 4px !important;
        overflow: hidden !important;
        margin-bottom: 8px !important;
    }

    .strength-bar {
        height: 100% !important;
        width: 0% !important;
        transition: all 0.3s ease !important;
        border-radius: 4px !important;
    }

    .strength-bar.weak {
        background: #ef4444 !important;
        width: 25% !important;
    }

    .strength-bar.fair {
        background: #f59e0b !important;
        width: 50% !important;
    }

    .strength-bar.good {
        background: #10b981 !important;
        width: 75% !important;
    }

    .strength-bar.strong {
        background: #059669 !important;
        width: 100% !important;
    }

    .strength-text {
        font-size: 0.875rem !important;
        font-weight: 500 !important;
        margin-bottom: 4px !important;
    }

    .strength-text.weak {
        color: #ef4444 !important;
    }

    .strength-text.fair {
        color: #f59e0b !important;
    }

    .strength-text.good {
        color: #10b981 !important;
    }

    .strength-text.strong {
        color: #059669 !important;
    }

    .strength-requirements {
        font-size: 0.75rem !important;
        color: #6b7280 !important;
    }

    /* Error display styles */
    .form-error {
        background: #fef2f2 !important;
        border: 1px solid #fca5a5 !important;
        color: #dc2626 !important;
        padding: 12px !important;
        border-radius: 8px !important;
        margin-bottom: 16px !important;
        font-size: 0.875rem !important;
    }

    .form-error ul {
        margin: 0 !important;
        padding-left: 16px !important;
    }

    .form-error li {
        margin-bottom: 4px !important;
    }

    .input-error {
        border-color: #ef4444 !important;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
    }

    /* Courses Modal Styles */
    .courses-loading {
        text-align: center;
        padding: 40px;
        color: #666;
    }

    .courses-loading .spinner-border {
        width: 40px;
        height: 40px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid #8b5cf6;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-bottom: 20px;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .courses-grid {
        display: flex;
        justify-content: center;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 24px;
        padding: 10px 0;
        max-width: 1200px;
        margin: 0 auto;
        min-height: 200px;
    }

    .courses-grid .course-card {
        flex: 0 0 350px;
        max-width: 350px;
    }

    @media (max-width: 768px) {
        .courses-grid {
            flex-direction: column;
            align-items: center;
            gap: 20px;
            padding: 15px;
        }

        .courses-grid .course-card {
            flex: 0 0 auto;
            width: 100%;
            max-width: 400px;
        }
    }

    .course-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: 1px solid #e5e7eb;
    }

    .course-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .course-image {
        position: relative;
        height: 200px;
        overflow: hidden;
    }

    .course-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .course-badge {
        position: absolute;
        top: 12px;
        right: 12px;
        background: linear-gradient(135deg, #ff6b6b, #ff8e8e);
        color: white;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .popular-badge {
        background: linear-gradient(135deg, #ff6b6b, #ff8e8e);
    }

    .course-content {
        padding: 20px;
    }

    .course-meta-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
    }

    .category-tag {
        background: #f3f4f6;
        color: #6b7280;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
    }

    .level-badge {
        padding: 4px 8px;
        border-radius: 8px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .level-beginner {
        background: #dcfce7;
        color: #166534;
    }

    .level-intermediate {
        background: #fef3c7;
        color: #92400e;
    }

    .level-advanced {
        background: #fee2e2;
        color: #991b1b;
    }

    .course-title {
        font-size: 18px;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 8px;
        line-height: 1.3;
    }

    .course-description {
        color: #6b7280;
        font-size: 14px;
        line-height: 1.5;
        margin-bottom: 16px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .instructor-info {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 16px;
    }

    .instructor-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: linear-gradient(135deg, #8b5cf6, #7c3aed);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 14px;
    }

    .instructor-name {
        font-size: 14px;
        font-weight: 500;
        color: #4b5563;
    }

    .course-stats {
        display: flex;
        gap: 16px;
        margin-bottom: 16px;
    }

    .stat-item {
        display: flex;
        align-items: center;
        gap: 4px;
        color: #6b7280;
        font-size: 13px;
    }

    .stat-item i {
        color: #8b5cf6;
    }

    .course-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 16px;
        border-top: 1px solid #e5e7eb;
    }

    .course-price {
        font-size: 18px;
        font-weight: 700;
        color: #1f2937;
    }

    .course-footer .btn {
        padding: 10px 20px;
        background: linear-gradient(135deg, #8b5cf6, #7c3aed);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .course-footer .btn:hover {
        background: linear-gradient(135deg, #7c3aed, #6d28d9);
        transform: translateY(-1px);
    }

            .courses-error {
            text-align: center;
            padding: 40px;
            color: #ef4444;
        }

        /* Courses Modal Close Button Hover */
        #coursesModal .modal-close:hover {
            background: rgba(255, 255, 255, 0.2) !important;
        }

        /* Courses Modal Visibility Control */
        #coursesModal[style*="display: none"] {
            display: none !important;
        }
        
        #coursesModal[style*="display: flex"] {
            display: flex !important;
        }
        
        #coursesModal.hidden {
            display: none !important;
            visibility: hidden !important;
            opacity: 0 !important;
        }

    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <div class="logo">
                    <img id="site-logo" src="" alt="Site Logo" style="height: 40px; width: auto; display: none;" onerror="this.style.display='none'; document.querySelector('.logo i').style.display='inline-block';">
                    <i class="fas fa-graduation-cap" id="default-icon"></i>
                    <span id="site-name">LMS Prototype</span>
                </div>
                <p class="tagline">Your No.1 Learning Solution</p>
            </div>
            <div class="nav-menu">
                <a href="#" class="nav-link">HOME</a>
                <a href="#about" class="nav-link">ABOUT</a>
                <a href="#features" class="nav-link">FEATURES</a>
                <button class="login-btn nav-auth-btn" id="loginBtn">
                    <i class="fas fa-user"></i>
                    Login
                </button>
            </div>
            <div class="mobile-menu-toggle" id="mobileToggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-background">
            <div class="floating-elements">
                <div class="floating-icon book"><i class="fas fa-book"></i></div>
                <div class="floating-icon graduation"><i class="fas fa-graduation-cap"></i></div>
                <div class="floating-icon chart"><i class="fas fa-chart-line"></i></div>
                <div class="floating-icon trophy"><i class="fas fa-trophy"></i></div>
                <div class="floating-icon star"><i class="fas fa-star"></i></div>
                <div class="floating-icon bulb"><i class="fas fa-lightbulb"></i></div>
                <div class="floating-icon code"><i class="fas fa-code"></i></div>
                <div class="floating-icon rocket"><i class="fas fa-rocket"></i></div>
                <div class="floating-icon atom"><i class="fas fa-atom"></i></div>
                <div class="floating-icon brain"><i class="fas fa-brain"></i></div>
                
                <!-- Geometric shapes -->
                <div class="geometric-shape triangle"></div>
                <div class="geometric-shape circle"></div>
                <div class="geometric-shape square"></div>
                <div class="geometric-shape hexagon"></div>
                
                <!-- Particle dots -->
                <div class="particle-dot dot1"></div>
                <div class="particle-dot dot2"></div>
                <div class="particle-dot dot3"></div>
                <div class="particle-dot dot4"></div>
                <div class="particle-dot dot5"></div>
                <div class="particle-dot dot6"></div>
            </div>
        </div>
        
        <div class="container hero-content">
            <div class="hero-left">
                <div class="hero-badge">Learning</div>
                <h1 class="hero-title">
                    Next-Generation
                    Digital Learning
                    Powered by
                    <span class="highlight" id="hero-site-name">LMS Prototype</span>
                </h1>
                <p class="hero-description">
                    Revolutionize education with our cutting-edge platform that seamlessly connects educators and learners.
                    Build dynamic courses, track progress in real-time, and foster collaborative learning environments
                    that adapt to the needs of today's digital-first world.
                </p>
                <button class="cta-button" id="getStartedBtn">
                    Get Started
                    <i class="fas fa-rocket"></i>
                </button>
                
                <div class="stats">
                    <div class="stat-item">
                        <span class="stat-number">10K+</span>
                        <span class="stat-label">Active Students</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">500+</span>
                        <span class="stat-label">Expert Teachers</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">1000+</span>
                        <span class="stat-label">Courses Available</span>
                    </div>
                </div>
            </div>
            
            <div class="hero-right">
                <div class="welcome-card">
                    <div class="welcome-badge">
                        <span class="welcome-text">WELCOME</span>
                        <span class="to-text">TO</span>
                        <span class="brand-text" id="welcome-card-site-name">LMS Prototype</span>
                    </div>
                    <div class="instructor-image">
                        <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=300&h=400&fit=crop&crop=face" alt="Instructor" id="instructorImg">
                        <div class="live-indicator">
                            <span class="live-dot"></span>
                            LIVE
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about" style="padding: 80px 0; background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);">
        <div class="container">
            <div class="section-header" style="text-align: center; margin-bottom: 60px;">
                <h2 style="font-size: 2.5rem; font-weight: 700; color: #1e293b; margin-bottom: 20px;">Redefining Digital Education</h2>
                <p style="font-size: 1.1rem; color: #64748b; max-width: 600px; margin: 0 auto;">Where innovation meets education - creating immersive learning experiences that drive real results</p>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center; max-width: 1200px; margin: 0 auto;">
                <div>
                    <h3 style="font-size: 2rem; font-weight: 600; color: #0f172a; margin-bottom: 24px;">Education Reimagined for the Digital Age</h3>
                    <p style="font-size: 1rem; line-height: 1.8; color: #475569; margin-bottom: 20px;">
                        Our advanced LMS prototype breaks traditional boundaries by delivering personalized, interactive learning experiences.
                        With intelligent content delivery and adaptive learning paths, we ensure every student reaches their full potential.
                    </p>
                    <p style="font-size: 1rem; line-height: 1.8; color: #475569; margin-bottom: 30px;">
                        Built on enterprise-grade architecture with intuitive design principles, our platform empowers institutions to
                        scale their educational impact while maintaining quality and engagement across all learning modalities.
                    </p>
                    <div style="display: flex; gap: 30px;">
                        <div style="text-align: center;">
                            <div style="font-size: 2.5rem; font-weight: 700; color: #0077b6; margin-bottom: 8px;">10K+</div>
                            <div style="font-size: 0.9rem; color: #64748b;">Active Users</div>
                        </div>
                        <div style="text-align: center;">
                            <div style="font-size: 2.5rem; font-weight: 700; color: #0077b6; margin-bottom: 8px;">500+</div>
                            <div style="font-size: 0.9rem; color: #64748b;">Courses Available</div>
                        </div>
                        <div style="text-align: center;">
                            <div style="font-size: 2.5rem; font-weight: 700; color: #0077b6; margin-bottom: 8px;">98%</div>
                            <div style="font-size: 0.9rem; color: #64748b;">Satisfaction Rate</div>
                        </div>
                    </div>
                </div>

                <div style="position: relative;">
                    <div style="background: linear-gradient(135deg, #001d3d 0%, #0077b6 100%); border-radius: 20px; padding: 40px; box-shadow: 0 20px 40px rgba(0, 119, 182, 0.2);">
                        <h4 style="color: white; font-size: 1.5rem; font-weight: 600; margin-bottom: 20px;">Platform Advantages</h4>
                        <ul style="list-style: none; padding: 0; margin: 0;">
                            <li style="color: rgba(255,255,255,0.9); margin-bottom: 12px; display: flex; align-items: center;">
                                <i class="fas fa-check-circle" style="color: #48cae4; margin-right: 12px;"></i>
                                Multi-role dashboard system
                            </li>
                            <li style="color: rgba(255,255,255,0.9); margin-bottom: 12px; display: flex; align-items: center;">
                                <i class="fas fa-check-circle" style="color: #48cae4; margin-right: 12px;"></i>
                                Advanced progress tracking
                            </li>
                            <li style="color: rgba(255,255,255,0.9); margin-bottom: 12px; display: flex; align-items: center;">
                                <i class="fas fa-check-circle" style="color: #48cae4; margin-right: 12px;"></i>
                                Two-way communication system
                            </li>
                            <li style="color: rgba(255,255,255,0.9); margin-bottom: 12px; display: flex; align-items: center;">
                                <i class="fas fa-check-circle" style="color: #48cae4; margin-right: 12px;"></i>
                                Certificate generation
                            </li>
                            <li style="color: rgba(255,255,255,0.9); display: flex; align-items: center;">
                                <i class="fas fa-check-circle" style="color: #48cae4; margin-right: 12px;"></i>
                                Modern, responsive design
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features">
        <div class="container">
            <div class="section-header">
                <h2>Advanced Learning Technologies</h2>
                <p>Comprehensive tools designed to elevate educational excellence and drive student success</p>
            </div>
            
            <div class="features-grid">
                <div class="feature-card" data-aos="fade-up" style="background: linear-gradient(120deg, #001d3d 0%, #003566 25%, #034f84 50%, #0077b6 75%, #00b4d8 100%) !important; color: white !important; padding: 1.5rem !important; border-radius: 12px !important; box-shadow: 0 10px 30px rgba(0, 29, 61, 0.4) !important; border: 1px solid rgba(0, 180, 216, 0.3) !important; min-height: auto !important; display: flex !important; flex-direction: column !important; align-items: center !important; text-align: center !important; position: relative !important; z-index: 10 !important;">
                    <div class="feature-icon" style="background: rgba(255, 255, 255, 0.2) !important; color: white !important; width: 60px !important; height: 60px !important; border-radius: 50% !important; display: flex !important; align-items: center !important; justify-content: center !important; font-size: 1.6rem !important; margin-bottom: 0.875rem !important; border: 2px solid rgba(255, 255, 255, 0.3) !important; backdrop-filter: blur(10px) !important;">
                        <i class="fas fa-users-cog"></i>
                    </div>
                    <h3 style="color: white !important; margin-bottom: 0.625rem !important; font-size: 1.25rem !important; font-weight: 700 !important; text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3) !important; line-height: 1.3 !important;">Multi-Role Dashboard</h3>
                    <p style="color: rgba(255, 255, 255, 0.9) !important; line-height: 1.45 !important; text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2) !important; font-size: 0.875rem !important; margin: 0 !important;">Comprehensive dashboards for Super Admins, Teachers, and Students with role-based access control and user management.</p>
                </div>
                
                <div class="feature-card" data-aos="fade-up" data-aos-delay="100" style="background: linear-gradient(120deg, #0f0519 0%, #1a0b2e 25%, #2d1b4e 50%, #4c1d95 75%, #7c3aed 100%) !important; color: white !important; padding: 1.5rem !important; border-radius: 12px !important; box-shadow: 0 10px 30px rgba(15, 5, 25, 0.4) !important; border: 1px solid rgba(139, 92, 246, 0.3) !important; min-height: auto !important; display: flex !important; flex-direction: column !important; align-items: center !important; text-align: center !important; position: relative !important; z-index: 10 !important;">
                    <div class="feature-icon" style="background: rgba(255, 255, 255, 0.2) !important; color: white !important; width: 60px !important; height: 60px !important; border-radius: 50% !important; display: flex !important; align-items: center !important; justify-content: center !important; font-size: 1.6rem !important; margin-bottom: 0.875rem !important; border: 2px solid rgba(255, 255, 255, 0.3) !important; backdrop-filter: blur(10px) !important;">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <h3 style="color: white !important; margin-bottom: 0.625rem !important; font-size: 1.25rem !important; font-weight: 700 !important; text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3) !important; line-height: 1.3 !important;">Course Management</h3>
                    <p style="color: rgba(255, 255, 255, 0.9) !important; line-height: 1.45 !important; text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2) !important; font-size: 0.875rem !important; margin: 0 !important;">Create and organize courses with modules and lessons. Teachers can upload video content and manage course materials easily.</p>
                </div>
                
                <div class="feature-card" data-aos="fade-up" data-aos-delay="200" style="background: linear-gradient(120deg, #0f0519 0%, #1a0b2e 25%, #2d1b4e 50%, #4c1d95 75%, #7c3aed 100%) !important; color: white !important; padding: 1.5rem !important; border-radius: 12px !important; box-shadow: 0 10px 30px rgba(15, 5, 25, 0.4) !important; border: 1px solid rgba(139, 92, 246, 0.3) !important; min-height: auto !important; display: flex !important; flex-direction: column !important; align-items: center !important; text-align: center !important; position: relative !important; z-index: 10 !important;">
                    <div class="feature-icon" style="background: rgba(255, 255, 255, 0.2) !important; color: white !important; width: 60px !important; height: 60px !important; border-radius: 50% !important; display: flex !important; align-items: center !important; justify-content: center !important; font-size: 1.6rem !important; margin-bottom: 0.875rem !important; border: 2px solid rgba(255, 255, 255, 0.3) !important; backdrop-filter: blur(10px) !important;">
                        <i class="fas fa-comments"></i>
                    </div>
                    <h3 style="color: white !important; margin-bottom: 0.625rem !important; font-size: 1.25rem !important; font-weight: 700 !important; text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3) !important; line-height: 1.3 !important;">Two-Way Messaging</h3>
                    <p style="color: rgba(255, 255, 255, 0.9) !important; line-height: 1.45 !important; text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2) !important; font-size: 0.875rem !important; margin: 0 !important;">Built-in messaging system enabling seamless communication between teachers and students for better learning support.</p>
                </div>
                
                <div class="feature-card" data-aos="fade-up" data-aos-delay="300" style="background: linear-gradient(120deg, #0f0519 0%, #1a0b2e 25%, #2d1b4e 50%, #4c1d95 75%, #7c3aed 100%) !important; color: white !important; padding: 1.5rem !important; border-radius: 12px !important; box-shadow: 0 10px 30px rgba(15, 5, 25, 0.4) !important; border: 1px solid rgba(139, 92, 246, 0.3) !important; min-height: auto !important; display: flex !important; flex-direction: column !important; align-items: center !important; text-align: center !important; position: relative !important; z-index: 10 !important;">
                    <div class="feature-icon" style="background: rgba(255, 255, 255, 0.2) !important; color: white !important; width: 60px !important; height: 60px !important; border-radius: 50% !important; display: flex !important; align-items: center !important; justify-content: center !important; font-size: 1.6rem !important; margin-bottom: 0.875rem !important; border: 2px solid rgba(255, 255, 255, 0.3) !important; backdrop-filter: blur(10px) !important;">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 style="color: white !important; margin-bottom: 0.625rem !important; font-size: 1.25rem !important; font-weight: 700 !important; text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3) !important; line-height: 1.3 !important;">Progress Tracking</h3>
                    <p style="color: rgba(255, 255, 255, 0.9) !important; line-height: 1.45 !important; text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2) !important; font-size: 0.875rem !important; margin: 0 !important;">Track student progress through lessons and courses with detailed completion status and learning metrics.</p>
                </div>
                
                <div class="feature-card" data-aos="fade-up" data-aos-delay="400" style="background: linear-gradient(120deg, #0f0519 0%, #1a0b2e 25%, #2d1b4e 50%, #4c1d95 75%, #7c3aed 100%) !important; color: white !important; padding: 1.5rem !important; border-radius: 12px !important; box-shadow: 0 10px 30px rgba(15, 5, 25, 0.4) !important; border: 1px solid rgba(139, 92, 246, 0.3) !important; min-height: auto !important; display: flex !important; flex-direction: column !important; align-items: center !important; text-align: center !important; position: relative !important; z-index: 10 !important;">
                    <div class="feature-icon" style="background: rgba(255, 255, 255, 0.2) !important; color: white !important; width: 60px !important; height: 60px !important; border-radius: 50% !important; display: flex !important; align-items: center !important; justify-content: center !important; font-size: 1.6rem !important; margin-bottom: 0.875rem !important; border: 2px solid rgba(255, 255, 255, 0.3) !important; backdrop-filter: blur(10px) !important;">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <h3 style="color: white !important; margin-bottom: 0.625rem !important; font-size: 1.25rem !important; font-weight: 700 !important; text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3) !important; line-height: 1.3 !important;">Student Achievements</h3>
                    <p style="color: rgba(255, 255, 255, 0.9) !important; line-height: 1.45 !important; text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2) !important; font-size: 0.875rem !important; margin: 0 !important;">Earn certificates, badges, and rewards for completing courses and reaching learning milestones. Celebrate success with achievement tracking.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-brand">
                    <div class="logo">
                        <img id="footer-logo" src="" alt="Site Logo" style="height: 40px; width: auto; display: none;" onerror="this.style.display='none'; document.querySelector('#footer-default-icon').style.display='inline-block';">
                        <i class="fas fa-graduation-cap" id="footer-default-icon"></i>
                        <span id="footer-site-name">LMS Prototype</span>
                    </div>
                    <p>Empowering education through innovative technology</p>
                </div>
                <div class="footer-links">
                    <div class="link-group">
                        <h4>Navigation</h4>
                        <a href="#" onclick="scrollToTop()">Home</a>
                        <a href="#about">About</a>
                        <a href="#features">Features</a>
                    </div>
                    <div class="link-group">
                        <h4>Platform</h4>
                        <a href="#" onclick="document.getElementById('loginBtn').click()">Login</a>
                        <a href="#about">Learn More</a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 <span id="footer-copyright-name">LMS Prototype</span>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Login Modal -->
    <div id="loginModal" class="modal-overlay hidden" style="display: none; visibility: hidden;" onclick="closeModal(event)">
        <div class="modal-container" onclick="event.stopPropagation()">
            <div class="modal-header">
                <h3>Login to Your Account</h3>
                <button class="modal-close" onclick="closeModal()" type="button">&times;</button>
            </div>
            <form id="loginForm" method="POST" action="{{ route('login') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="modal-email">Email</label>
                        <input type="email" id="modal-email" name="email" placeholder="Enter your email" autocomplete="email" required>
                    </div>
                    <div class="form-group">
                        <label for="modal-password">Password</label>
                        <input type="password" id="modal-password" name="password" placeholder="Enter your password" autocomplete="current-password" required>
                    </div>
                    <div class="form-group checkbox-group">
                        <label for="remember-me">
                            <input type="checkbox" id="remember-me" name="remember" value="1"> Remember me
                        </label>
                        <a href="#" class="forgot-link">Forgot password?</a>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="login-submit-btn">Login</button>
                    <p class="register-link">Don't have an account? <a href="#" id="showRegisterLink">Register</a></p>
                </div>
            </form>
        </div>
    </div>

    <!-- Marketplace Development Modal -->
    <div id="marketplaceModal" class="modal-overlay hidden" style="display: none; visibility: hidden;" onclick="closeMarketplaceModal(event)">
        <div class="modal-container" onclick="event.stopPropagation()">
            <div class="modal-header">
                <h3><i class="fas fa-hammer" style="margin-right: 8px;"></i>Under Development</h3>
                <button class="modal-close" onclick="closeMarketplaceModal()" type="button">&times;</button>
            </div>
            <div class="modal-body">
                <div style="font-size: 4rem; color: #f59e0b; margin-bottom: 20px;">
                    <i class="fas fa-store"></i>
                </div>
                <h4 style="color: #374151; font-size: 1.5rem; font-weight: 600; margin-bottom: 16px;">
                    Freelancer Talent Marketplace!
                </h4>
                <p style="color: #6b7280; font-size: 1rem; line-height: 1.6; margin-bottom: 24px; max-width: 420px; margin-left: auto; margin-right: auto;">
                    Connect certified freelancers with marketing agencies for social media influencer opportunities. Get hired for live streaming, TikTok campaigns, and social media marketing projects.
                </p>
                <div style="background: linear-gradient(135deg, #fef3c7, #fed7aa); border: 1px solid #fbbf24; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
                    <h5 style="color: #92400e; font-weight: 600; margin-bottom: 12px;">
                        <i class="fas fa-users" style="margin-right: 8px;"></i>
                        Marketplace Features:
                    </h5>
                    <ul style="color: #92400e; text-align: left; margin: 0; padding-left: 20px; line-height: 1.8;">
                        <li>Certified freelancer profiles & portfolios</li>
                        <li>Marketing agency job postings</li>
                        <li>Live influencer & TikTok campaigns</li>
                        <li>Social media expertise verification</li>
                        <li>Secure hiring & payment system</li>
                    </ul>
                </div>
                <p style="color: #8b5cf6; font-weight: 600; margin-bottom: 0;">
                    <i class="fas fa-star" style="margin-right: 8px;"></i>
                    Join the future of freelance social media marketing!
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="login-submit-btn" onclick="closeMarketplaceModal()">
                    <i class="fas fa-check" style="margin-right: 8px;"></i>
                    Got It!
                </button>
            </div>
        </div>
    </div>

    <!-- Future Development Modal -->
    <div id="futureDevModal" class="modal-overlay hidden" style="display: none; visibility: hidden;" onclick="closeFutureDevModal(event)">
        <div class="modal-container" onclick="event.stopPropagation()">
            <div class="modal-header">
                <h3><i class="fas fa-rocket" style="margin-right: 8px;"></i>Coming Soon!</h3>
                <button class="modal-close" onclick="closeFutureDevModal()" type="button">&times;</button>
            </div>
            <div class="modal-body">
                <div style="font-size: 4rem; color: #10b981; margin-bottom: 20px;">
                    <i class="fas fa-gift"></i>
                </div>
                <h4 style="color: #374151; font-size: 1.5rem; font-weight: 600; margin-bottom: 16px;">
                    Currently FREE to Use!
                </h4>
                <p style="color: #6b7280; font-size: 1rem; line-height: 1.6; margin-bottom: 24px; max-width: 400px; margin-left: auto; margin-right: auto;">
                    Enjoy full access to our Learning Management System at no cost! We're planning to introduce subscription plans in the future to support ongoing development and premium features.
                </p>
                <div style="background: linear-gradient(135deg, #ecfdf5, #d1fae5); border: 1px solid #86efac; border-radius: 12px; padding: 20px; margin-bottom: 16px;">
                    <h5 style="color: #065f46; font-weight: 600; margin-bottom: 12px;">
                        <i class="fas fa-check-circle" style="margin-right: 8px;"></i>
                        Available Now - FREE:
                    </h5>
                    <ul style="color: #065f46; text-align: left; margin: 0; padding-left: 20px; line-height: 1.8;">
                        <li>Complete Learning Management System</li>
                        <li>Unlimited courses and students</li>
                        <li>Multi-role dashboards</li>
                        <li>Full feature access</li>
                    </ul>
                </div>
                <div style="background: linear-gradient(135deg, #f0f9ff, #e0f2fe); border: 1px solid #bae6fd; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
                    <h5 style="color: #0369a1; font-weight: 600; margin-bottom: 12px;">
                        <i class="fas fa-calendar-alt" style="margin-right: 8px;"></i>
                        Future Plans:
                    </h5>
                    <ul style="color: #0369a1; text-align: left; margin: 0; padding-left: 20px; line-height: 1.8;">
                        <li>Premium subscription tiers</li>
                        <li>Advanced analytics & reporting</li>
                        <li>Priority support</li>
                        <li>Enterprise integrations</li>
                    </ul>
                </div>
                <p style="color: #8b5cf6; font-weight: 600; margin-bottom: 0;">
                    <i class="fas fa-rocket" style="margin-right: 8px;"></i>
                    Start learning today - completely free!
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="login-submit-btn" onclick="closeFutureDevModal()">
                    <i class="fas fa-check" style="margin-right: 8px;"></i>
                    Got It!
                </button>
            </div>
        </div>
    </div>

    <!-- Register Modal -->
    <div id="registerModal" class="modal-overlay hidden" style="display: none; visibility: hidden; z-index: 10000;" onclick="closeRegisterModal(event)">
        <div class="modal-container" onclick="event.stopPropagation()">
            <div class="modal-header">
                <h3>Create Your Account</h3>
                <button class="modal-close" onclick="closeRegisterModal()" type="button">&times;</button>
            </div>
            <form id="registerForm" method="POST" action="{{ route('register') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="register-name">Full Name</label>
                        <input type="text" id="register-name" name="name" placeholder="Enter your full name" autocomplete="name" required>
                    </div>
                    <div class="form-group">
                        <label for="register-email">Email</label>
                        <input type="email" id="register-email" name="email" placeholder="Enter your email" autocomplete="email" required>
                    </div>
                    <div class="form-group">
                        <label for="register-password">Password</label>
                        <input type="password" id="register-password" name="password" placeholder="Enter your password" autocomplete="new-password" required>
                    </div>
                    <div class="form-group">
                        <label for="register-password-confirmation">Confirm Password</label>
                        <input type="password" id="register-password-confirmation" name="password_confirmation" placeholder="Confirm your password" autocomplete="new-password" required>
                    </div>
                    <div class="form-group">
                        <label for="register-role">I want to register as:</label>
                        <select id="register-role" name="role" required>
                            <option value="">Select Role</option>
                            <option value="student">Student</option>
                            <option value="teacher">Teacher</option>
                        </select>
                    </div>
                    <div id="role-info" class="role-info" style="display: none;"></div>
                    
                    <!-- Hidden honeypot field for bot protection -->
                    <input type="hidden" name="terms" value="1">
                    
                    <!-- Password strength indicator -->
                    <div id="password-strength" class="password-strength" style="display: none;">
                        <div class="strength-meter">
                            <div class="strength-bar" id="strength-bar"></div>
                        </div>
                        <div class="strength-text" id="strength-text">Password strength</div>
                        <div class="strength-requirements" id="strength-requirements">
                            <small>
                                • At least 8 characters<br>
                                • At least one uppercase letter<br>
                                • At least one number
                            </small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="login-submit-btn">Create Account</button>
                    <p class="register-link">Already have an account? <a href="#" id="showLoginLink">Login</a></p>
                </div>
            </form>
        </div>
    </div>

    <!-- Popular Courses Modal -->
    <div id="coursesModal" class="modal-overlay hidden" style="display: none; visibility: hidden; z-index: 10000; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(120deg, rgba(0, 0, 0, 0.5) 0%, rgba(15, 5, 25, 0.4) 40%, rgba(45, 27, 78, 0.4) 75%, rgba(139, 92, 246, 0.3) 100%); backdrop-filter: blur(5px); -webkit-backdrop-filter: blur(5px); justify-content: center; align-items: center;" onclick="closeCoursesModal(event)">
        <div class="modal-container" onclick="event.stopPropagation()" style="background: white; border-radius: 16px; padding: 0; max-width: 1200px; width: 95%; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); animation: modalSlideIn 0.3s ease-out; position: relative;">
            <div class="modal-header" style="background: linear-gradient(120deg, #0f0519 0%, #1a0b2e 25%, #2d1b4e 50%, #4c1d95 75%, #7c3aed 100%); color: white; padding: 20px; border-radius: 16px 16px 0 0; display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0; font-size: 1.25rem; font-weight: 600;">Most Popular Courses</h3>
                <button class="modal-close" onclick="closeCoursesModal()" type="button" style="background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer; padding: 0; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: background-color 0.2s; z-index: 10001; position: relative; pointer-events: auto; outline: none;">&times;</button>
            </div>
            <div class="modal-body" id="coursesModalBody" style="padding: 30px 24px;">
                <div class="courses-loading" id="coursesLoading">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p>Loading most popular courses...</p>
                </div>
                <div class="courses-grid" id="coursesGrid" style="display: none;"></div>
                <div class="courses-error" id="coursesError" style="display: none;">
                    <p>Failed to load courses. Please try again later.</p>
                </div>
            </div>
        </div>
    </div>

    <script type="module" src="{{ asset('assets/js/main.js') }}"></script>
</body>
</html>