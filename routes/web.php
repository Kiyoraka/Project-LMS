<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Teacher\TeacherController;
use App\Http\Controllers\Student\StudentController;
use Illuminate\Support\Facades\Route;

// Include admin API routes
require __DIR__.'/admin.php';

Route::get('/', function () {
    return view('welcome');
});

// Public API endpoint for site settings
Route::get('/api/site-settings', function () {
    try {
        // Try to get settings from database
        $generalSettings = App\Models\Setting::getGroup('general');
        $appearanceSettings = App\Models\Setting::getGroup('appearance');
        
        $siteName = ($generalSettings['siteName'] ?? '') ?: 'Aidel LMS';
        
        // Use public routes for logo and favicon to avoid 403 errors
        $logoPath = '';
        $faviconPath = '';
        
        $storedLogoPath = $appearanceSettings['logo_path'] ?? $generalSettings['logoPath'] ?? '';
        $storedFaviconPath = $appearanceSettings['favicon_path'] ?? $generalSettings['faviconPath'] ?? '';

        // If stored path is a static file path, use it directly
        if ($storedLogoPath && (strpos($storedLogoPath, '/assets/') === 0 || strpos($storedLogoPath, '/images/') === 0)) {
            $logoPath = $storedLogoPath;
        } elseif ($storedLogoPath && strpos($storedLogoPath, '/storage/') === 0) {
            // If it's a storage path, use the public-logo endpoint
            $logoPath = url('/public-logo');
        } else {
            $logoPath = '/assets/images/logo.svg'; // fallback
        }

        // Update favicon to use SVG favicon
        if ($storedFaviconPath && (strpos($storedFaviconPath, '/favicon') === 0)) {
            $faviconPath = '/favicon.svg'; // Use SVG favicon instead
        } elseif ($storedFaviconPath && strpos($storedFaviconPath, '/storage/') === 0) {
            $faviconPath = url('/public-favicon');
        } else {
            $faviconPath = '/favicon.svg'; // fallback to SVG
        }
        
        return response()->json([
            'general' => [
                'siteName' => $siteName,
                'logoPath' => $logoPath,
                'faviconPath' => $faviconPath
            ]
        ]);
    } catch (\Exception $e) {
        // If database fails, return safe defaults
        \Log::error('Site settings API error: ' . $e->getMessage());
        
        return response()->json([
            'general' => [
                'siteName' => 'Aidel LMS',
                'logoPath' => '/assets/images/logo.svg',
                'faviconPath' => '/favicon.ico'
            ]
        ]);
    }
});

// Public routes for serving logo and favicon (bypass any auth issues)
Route::get('/public-logo', function () {
    $logoPath = App\Models\Setting::get('general', 'logoPath');
    
    if (!$logoPath) {
        // Return default logo or 404
        abort(404, 'Logo not found');
    }
    
    // Remove /storage prefix if present and get the actual file path
    $filePath = str_replace('/storage/', '', $logoPath);
    $fullPath = storage_path('app/public/' . $filePath);
    
    if (!file_exists($fullPath)) {
        abort(404, 'Logo file not found');
    }
    
    // Get file info
    $mimeType = mime_content_type($fullPath);
    $fileSize = filesize($fullPath);
    
    // Return the file with proper headers
    return response()->file($fullPath, [
        'Content-Type' => $mimeType,
        'Content-Length' => $fileSize,
        'Cache-Control' => 'public, max-age=31536000', // Cache for 1 year
    ]);
})->name('public.logo');

Route::get('/public-favicon', function () {
    $faviconPath = App\Models\Setting::get('general', 'faviconPath');
    
    if (!$faviconPath) {
        // Return default favicon or 404
        abort(404, 'Favicon not found');
    }
    
    // Remove /storage prefix if present and get the actual file path
    $filePath = str_replace('/storage/', '', $faviconPath);
    $fullPath = storage_path('app/public/' . $filePath);
    
    if (!file_exists($fullPath)) {
        abort(404, 'Favicon file not found');
    }
    
    // Get file info
    $mimeType = mime_content_type($fullPath);
    $fileSize = filesize($fullPath);
    
    // Return the file with proper headers
    return response()->file($fullPath, [
        'Content-Type' => $mimeType,
        'Content-Length' => $fileSize,
        'Cache-Control' => 'public, max-age=31536000', // Cache for 1 year
    ]);
})->name('public.favicon');

// Public route for serving user profile images (bypass auth issues)
Route::get('/public-avatar/{userId}', function ($userId) {
    $user = App\Models\User::find($userId);
    
    if (!$user || !$user->profile_picture) {
        abort(404, 'Avatar not found');
    }
    
    $fullPath = storage_path('app/public/' . $user->profile_picture);
    
    if (!file_exists($fullPath)) {
        abort(404, 'Avatar file not found');
    }
    
    // Get file info
    $mimeType = mime_content_type($fullPath);
    $fileSize = filesize($fullPath);
    
    // Return the file with proper headers
    return response()->file($fullPath, [
        'Content-Type' => $mimeType,
        'Content-Length' => $fileSize,
        'Cache-Control' => 'public, max-age=86400', // Cache for 1 day
    ]);
})->name('public.avatar');

// Database Test Route (remove after testing)
Route::get('/test-db', function () {
    try {
        // Test database connection
        $dbConnection = \DB::connection()->getPDO();
        
        // Test if tables exist
        $tables = [];
        $tables['users'] = \Schema::hasTable('users');
        $tables['settings'] = \Schema::hasTable('settings');
        $tables['courses'] = \Schema::hasTable('courses');
        $tables['login_attempts'] = \Schema::hasTable('login_attempts');
        
        // Count records if tables exist
        $counts = [];
        if ($tables['users']) {
            $counts['users'] = \App\Models\User::count();
        }
        if ($tables['settings']) {
            $counts['settings'] = \App\Models\Setting::count();
        }
        
        return response()->json([
            'database_connection' => 'OK',
            'tables' => $tables,
            'record_counts' => $counts,
            'app_env' => config('app.env'),
            'app_url' => config('app.url')
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'database_connection' => 'FAILED',
            'error' => $e->getMessage(),
            'app_env' => config('app.env'),
            'app_url' => config('app.url')
        ], 500);
    }
});

// Authentication Routes
Route::get('/login', function() {
    return redirect('/')->with('show_login_modal', true);
})->name('login');

// Simple login test endpoint (remove after testing)
Route::post('/test-login', function(\Illuminate\Http\Request $request) {
    try {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);
        
        // Find user
        $user = \App\Models\User::where('email', $credentials['email'])->first();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
                'debug_info' => [
                    'email_searched' => $credentials['email'],
                    'total_users' => \App\Models\User::count()
                ]
            ]);
        }
        
        // Check password
        if (!\Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password incorrect',
                'debug_info' => [
                    'user_found' => true,
                    'user_role' => $user->role,
                    'email_verified' => $user->hasVerifiedEmail()
                ]
            ]);
        }
        
        // Check email verification
        if (!$user->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => 'Email not verified',
                'debug_info' => [
                    'user_found' => true,
                    'password_correct' => true,
                    'email_verified_at' => $user->email_verified_at
                ]
            ]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Login would succeed',
            'debug_info' => [
                'user_id' => $user->id,
                'user_role' => $user->role,
                'dashboard_path' => $user->getDashboardPath()
            ]
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Login test failed',
            'error' => $e->getMessage()
        ], 500);
    }
});

Route::post('/login', [App\Http\Controllers\Auth\AuthController::class, 'login']);
Route::post('/logout', [App\Http\Controllers\Auth\AuthController::class, 'logout'])->name('logout');
Route::post('/register', [App\Http\Controllers\Auth\AuthController::class, 'register'])->name('register');

// Email Verification Routes (Laravel 11 built-in)
use Illuminate\Foundation\Auth\EmailVerificationRequest;

// Email verification handler
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    
    $user = $request->user();
    return redirect($user->getDashboardPath())->with('verified', true);
})->middleware(['auth', 'signed'])->name('verification.verify');

// Custom email verification notice page
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

// Resend email verification
Route::post('/email/verification-notification', function (Illuminate\Http\Request $request) {
    $request->user()->sendEmailVerificationNotification();

    return back()->with('message', 'Verification link sent!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

// Check verification status API
Route::get('/api/check-verification', function (Illuminate\Http\Request $request) {
    if (!auth()->check()) {
        return response()->json(['verified' => false], 401);
    }
    
    $user = auth()->user();
    if ($user->hasVerifiedEmail()) {
        return response()->json([
            'verified' => true,
            'redirect' => $user->getDashboardPath()
        ]);
    }
    
    return response()->json(['verified' => false]);
})->middleware('auth');

// Admin Routes - Protected by super_admin middleware
Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', 'super_admin'])->group(function () {
    // SPA entry point - Vue.js app will handle all routes internally
    Route::get('/{any?}', [AdminController::class, 'spa'])->where('any', '.*')->name('spa');
});

// Teacher Routes - Protected by teacher middleware
Route::prefix('teacher')->name('teacher.')->middleware(['auth', 'verified', 'teacher', 'maintenance'])->group(function () {
    // SPA entry point - Vue.js app will handle all routes internally
    Route::get('/{any?}', [TeacherController::class, 'spa'])->where('any', '.*')->name('spa');
});

// Student Routes - Protected by student middleware
Route::prefix('student')->name('student.')->middleware(['auth', 'verified', 'student', 'maintenance'])->group(function () {
    // SPA entry point - Vue.js app will handle all routes internally
    Route::get('/{any?}', [StudentController::class, 'spa'])->where('any', '.*')->name('spa');
    
    // Keep original routes for backward compatibility and API endpoints
    // Dashboard
    Route::get('/dashboard', [StudentController::class, 'dashboard'])->name('dashboard');
    
    // Course Browsing and Enrollment
    Route::get('/courses/browse', [StudentController::class, 'browseCourses'])->name('courses.browse');
    Route::get('/courses/enrolled', [StudentController::class, 'enrolledCourses'])->name('courses.enrolled');
    Route::get('/courses/{id}/details', [StudentController::class, 'courseDetails'])->name('courses.details');
    Route::post('/courses/{id}/enroll', [StudentController::class, 'enrollCourse'])->name('courses.enroll');
    
    // Learning Interface
    Route::get('/courses/{id}/learn', [StudentController::class, 'learnCourse'])->name('courses.learn');
    Route::get('/courses/{courseId}/lessons/{lessonId}', [StudentController::class, 'viewLesson'])->name('courses.lesson');
    Route::post('/courses/{courseId}/lessons/{lessonId}/complete', [StudentController::class, 'completeLesson'])->name('lessons.complete');
    
    // Certificates
    Route::get('/certificates/{enrollmentId}', [StudentController::class, 'generateCertificate'])->name('certificates.generate');
    
    // Profile Management
    Route::get('/profile', [StudentController::class, 'showProfile'])->name('profile');
    Route::put('/profile', [StudentController::class, 'updateProfile'])->name('profile.update');
});
