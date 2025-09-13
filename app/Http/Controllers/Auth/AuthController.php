<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\LoginAttempt;
use App\Models\Setting;

class AuthController extends Controller
{
    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }
    
    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
        
        $email = $credentials['email'];
        $ipAddress = $request->ip();
        
        // Get security settings
        $maxAttempts = Setting::get('security', 'maxLoginAttempts', 5);
        $lockoutTime = Setting::get('security', 'lockoutTime', 30);
        
        // Check if account is locked out - DISABLED FOR DIGITAL OCEAN TESTING
        // if (LoginAttempt::isLockedOut($email, $ipAddress)) {
        //     $remainingTime = LoginAttempt::getLockoutTime($email, $ipAddress);
        //     
        //     if ($request->expectsJson() || $request->ajax()) {
        //         return response()->json([
        //             'error' => 'locked_out',
        //             'message' => "Account is locked out. Try again in {$remainingTime} minutes.",
        //             'remaining_time' => $remainingTime
        //         ], 429);
        //     }
        //     
        //     throw ValidationException::withMessages([
        //         'email' => ["Account is locked out. Try again in {$remainingTime} minutes."],
        //     ]);
        // }
        
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();
            
            // Email verification removed - users can login immediately after registration
            
            // Clear login attempts on successful login
            LoginAttempt::clearAttempts($email, $ipAddress);
            
            // Check if maintenance mode is enabled AFTER authentication
            $maintenanceMode = Setting::get('general', 'maintenanceMode', false);
            
            // Allow super admin to login even during maintenance
            if ($maintenanceMode && $user->role !== 'super_admin') {
                
                Auth::logout();
                
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'error' => 'maintenance',
                        'message' => 'System is under maintenance, please wait'
                    ], 503);
                }
                
                return back()->with('maintenance_mode', 'System is under maintenance, please wait');
            }
            
            $request->session()->regenerate();
            
            // Update last login timestamp
            $user->update(['last_login' => now()]);
            
            // Clear any previous intended URL and redirect to the appropriate dashboard
            $request->session()->forget('url.intended');
            
            $dashboardPath = $user->getDashboardPath();
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'redirect' => $dashboardPath
                ]);
            }
            
            return redirect($dashboardPath);
        }

        // Record failed login attempt - DISABLED FOR DIGITAL OCEAN TESTING
        // LoginAttempt::recordAttempt($email, $ipAddress);
        // LoginAttempt::applyLockoutIfNeeded($email, $ipAddress, $maxAttempts, $lockoutTime);
        
        // Check maintenance mode for failed login attempts too
        $maintenanceMode = Setting::get('general', 'maintenanceMode', false);
        
        if ($maintenanceMode) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'error' => 'maintenance',
                    'message' => 'System is under maintenance, please wait'
                ], 503);
            }
            
            return back()->with('maintenance_mode', 'System is under maintenance, please wait');
        }

        // Check if this failed attempt caused a lockout - DISABLED FOR DIGITAL OCEAN TESTING
        // if (LoginAttempt::isLockedOut($email, $ipAddress)) {
        //     $remainingTime = LoginAttempt::getLockoutTime($email, $ipAddress);
        //     
        //     if ($request->expectsJson() || $request->ajax()) {
        //         return response()->json([
        //             'error' => 'locked_out',
        //             'message' => "Too many failed attempts. Account locked for {$remainingTime} minutes.",
        //             'remaining_time' => $remainingTime
        //         ], 429);
        //     }
        //     
        //     throw ValidationException::withMessages([
        //         'email' => ["Too many failed attempts. Account locked for {$remainingTime} minutes."],
        //     ]);
        // }

        throw ValidationException::withMessages([
            'email' => ['The provided credentials do not match our records.'],
        ]);
    }
    
    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        // Redirect to login page instead of home page
        return redirect()->route('login')->with('status', 'You have been logged out successfully.');
    }

    /**
     * Handle registration request
     */
    public function register(Request $request)
    {
        try {
            // Check if registrations are enabled
            $registrationEnabled = Setting::get('general', 'allow_registrations', true);
            if (!$registrationEnabled) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'disabled',
                        'message' => 'Registration is currently disabled'
                    ], 403);
                }
                return back()->with('error', 'Registration is currently disabled');
            }

            // Check if maintenance mode is enabled
            $maintenanceMode = Setting::get('general', 'maintenanceMode', false);
            if ($maintenanceMode) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'maintenance',
                        'message' => 'System is under maintenance, please wait'
                    ], 503);
                }
                return back()->with('maintenance_mode', 'System is under maintenance, please wait');
            }

            // Rate limiting check
            $email = $request->input('email', '');
            $ipAddress = $request->ip();
            
            if (LoginAttempt::isRegistrationLimited($email, $ipAddress)) {
                $remainingTime = LoginAttempt::getRegistrationLimitTime($email, $ipAddress);
                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'rate_limited',
                        'message' => "Too many registration attempts. Please wait {$remainingTime} minutes before trying again.",
                        'remaining_time' => $remainingTime
                    ], 429);
                }
                return back()->with('error', "Too many registration attempts. Please wait {$remainingTime} minutes.");
            }

            // Enhanced validation with custom messages
            $validated = $request->validate([
                'name' => 'required|string|min:2|max:255|regex:/^[a-zA-Z\s]+$/',
                'email' => 'required|string|email|max:255|unique:users|regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                'password' => ['required', 'string', 'confirmed', new \App\Rules\PasswordPolicy],
                'role' => 'required|in:teacher,student',
                'terms' => 'accepted', // Honeypot and terms acceptance
            ], [
                'name.regex' => 'Name must contain only letters and spaces',
                'name.min' => 'Name must be at least 2 characters',
                'email.regex' => 'Please enter a valid email address',
                'email.unique' => 'This email address is already registered',
                'password.confirmed' => 'Password confirmation does not match',
                'role.required' => 'Please select whether you are a student or teacher',
                'role.in' => 'Invalid role selected',
                'terms.accepted' => 'You must accept the terms and conditions',
            ]);

            // Check for blocked email domains
            $blockedDomains = Setting::get('security', 'blocked_email_domains', '');
            if ($blockedDomains) {
                $domains = array_map('trim', explode(',', $blockedDomains));
                $emailDomain = substr(strrchr($validated['email'], "@"), 1);
                if (in_array($emailDomain, $domains)) {
                    if ($request->expectsJson()) {
                        return response()->json([
                            'error' => 'blocked_domain',
                            'message' => 'Registration from this email domain is not allowed'
                        ], 422);
                    }
                    return back()->with('error', 'Registration from this email domain is not allowed');
                }
            }

            // Record registration attempt
            LoginAttempt::recordRegistrationAttempt($email, $ipAddress);

            $isTeacher = $validated['role'] === 'teacher';
            $validated['is_active'] = $isTeacher ? false : true;
            $validated['password'] = bcrypt($validated['password']);
            $validated['email_verified_at'] = now(); // Auto-verify email
            
            // Remove terms from validated data before creating user
            unset($validated['terms']);

            $user = \App\Models\User::create($validated);

            // Clear registration attempts on success
            LoginAttempt::clearRegistrationAttempts($email, $ipAddress);

            if ($request->expectsJson()) {
                if ($isTeacher) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Registration successful! Your teacher account requires admin approval before you can access the platform.',
                        'redirect' => route('login'),
                        'user_type' => 'teacher',
                        'requires_verification' => false
                    ]);
                } else {
                    return response()->json([
                        'success' => true,
                        'message' => 'Registration successful! You can now log in to access the platform.',
                        'redirect' => route('login'),
                        'user_type' => 'student',
                        'requires_verification' => false
                    ]);
                }
            }

            if ($isTeacher) {
                return redirect()->route('login')->with('status', 'Registration successful! Your teacher account requires admin approval before you can access the platform.');
            } else {
                return redirect()->route('login')->with('status', 'Registration successful! You can now log in to access the platform.');
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Record failed registration attempt
            if (isset($email, $ipAddress)) {
                LoginAttempt::recordRegistrationAttempt($email, $ipAddress, false);
            }
            
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'validation',
                    'message' => 'Please correct the errors below',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Registration error: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'server',
                    'message' => 'An unexpected error occurred. Please try again.'
                ], 500);
            }
            return back()->with('error', 'An unexpected error occurred. Please try again.');
        }
    }
}