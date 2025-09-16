<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verify Your Email - {{ config('app.name', 'LMS Prototype') }}</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .verify-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            padding: 40px;
            max-width: 500px;
            width: 100%;
            text-align: center;
        }

        .verify-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 30px;
            background: linear-gradient(120deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2.5rem;
        }

        .verify-title {
            font-size: 2rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 16px;
        }

        .verify-message {
            color: #6b7280;
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .email-display {
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 12px 16px;
            margin: 20px 0;
            font-weight: 500;
            color: #374151;
            word-break: break-all;
        }

        .btn-container {
            display: flex;
            flex-direction: column;
            gap: 16px;
            margin-top: 30px;
        }

        .btn {
            padding: 14px 24px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1rem;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(120deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #f9fafb;
            color: #374151;
            border: 1px solid #d1d5db;
        }

        .btn-secondary:hover {
            background: #f3f4f6;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-success {
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #86efac;
        }

        .alert-info {
            background: #f0f9ff;
            color: #0369a1;
            border: 1px solid #bae6fd;
        }

        .footer-text {
            margin-top: 30px;
            color: #9ca3af;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        .footer-text a {
            color: #667eea;
            text-decoration: none;
        }

        .footer-text a:hover {
            text-decoration: underline;
        }

        @media (max-width: 640px) {
            .verify-container {
                padding: 30px 20px;
            }

            .verify-title {
                font-size: 1.5rem;
            }

            .verify-message {
                font-size: 1rem;
            }
        }

        .loading {
            opacity: 0.7;
            pointer-events: none;
        }

        .loading .btn-text {
            display: none;
        }

        .loading .btn-spinner {
            display: inline-block;
        }

        .btn-spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid transparent;
            border-top: 2px solid currentColor;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="verify-container">
        <div class="verify-icon">
            <i class="fas fa-envelope-open"></i>
        </div>

        <h1 class="verify-title">Verify Your Email Address</h1>

        @if (session('message'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                {{ session('message') }}
            </div>
        @endif

        @if (session('status'))
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                {{ session('status') }}
            </div>
        @endif

        <p class="verify-message">
            We've sent a verification link to your email address. Please check your email and click the verification link to activate your account.
        </p>

        @auth
            <div class="email-display">
                <i class="fas fa-at"></i>
                {{ auth()->user()->email }}
            </div>
        @endauth

        <div class="btn-container">
            <form method="POST" action="{{ route('verification.send') }}" id="resendForm">
                @csrf
                <button type="submit" class="btn btn-primary" id="resendBtn">
                    <span class="btn-text">
                        <i class="fas fa-paper-plane"></i>
                        Resend Verification Email
                    </span>
                    <div class="btn-spinner"></div>
                </button>
            </form>

            <a href="{{ route('logout') }}" 
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
               class="btn btn-secondary">
                <i class="fas fa-sign-out-alt"></i>
                Use Different Account
            </a>

            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </div>

        <div class="footer-text">
            <p><strong>Didn't receive the email?</strong></p>
            <p>Check your spam folder or click "Resend Verification Email" above.</p>
            <p>Need help? <a href="mailto:support@{{ request()->getHost() }}">Contact Support</a></p>
        </div>
    </div>

    <script>
        document.getElementById('resendForm').addEventListener('submit', function() {
            const btn = document.getElementById('resendBtn');
            btn.classList.add('loading');
            
            setTimeout(() => {
                btn.classList.remove('loading');
            }, 3000);
        });

        // Auto-refresh page every 30 seconds to check if user has verified
        @auth
        setInterval(() => {
            fetch('/api/check-verification', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.verified) {
                    window.location.href = data.redirect || '/';
                }
            })
            .catch(() => {
                // Silently fail
            });
        }, 30000);
        @endauth
    </script>
</body>
</html> 