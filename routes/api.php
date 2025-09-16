<?php

use App\Http\Controllers\Api\AdminApiController;
use App\Http\Controllers\Api\DiagnosticController;
use App\Http\Controllers\Api\DebugController;
use App\Http\Controllers\Api\TeacherApiController;
use App\Http\Controllers\Api\TeacherMessageApiController;
use App\Http\Controllers\Api\StudentApiController;
use App\Http\Controllers\Api\StudentMessageApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// User endpoint that works with web sessions (for the dashboard)
Route::middleware(['web', 'auth'])->get('/user', function (Request $request) {
    $user = $request->user();
    
    // Return user data with public avatar URL
    return response()->json([
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'role' => $user->role,
        'bio' => $user->bio,
        'phone' => $user->phone,
        'is_active' => $user->is_active,
        'profile_picture' => $user->profile_picture, // Keep original for compatibility
        'avatar' => $user->profile_picture ? url('/public-avatar/' . $user->id) : null,
        'youtube_url' => $user->youtube_url,
        'linkedin_url' => $user->linkedin_url,
        'twitter_url' => $user->twitter_url,
        'facebook_url' => $user->facebook_url,
        'created_at' => $user->created_at,
        'updated_at' => $user->updated_at,
    ]);
});

// Public settings endpoint (no authentication required)
Route::get('/site-settings', function () {
    // Get basic site settings that are safe to expose publicly
    $settings = \App\Models\Setting::getAllSettings();
    $general = $settings['general'] ?? [];
    
    // Use isset and null checks instead of empty() for string values
    $siteName = (isset($general['siteName']) && $general['siteName'] !== null && $general['siteName'] !== '') 
        ? $general['siteName'] 
        : 'LMS Prototype';
    
    // For logo and favicon, use public routes instead of direct storage paths
    $logoPath = '';
    $faviconPath = '';
    
    if (isset($general['logoPath']) && $general['logoPath'] !== null && $general['logoPath'] !== '') {
        $logoPath = url('/public-logo');
    } else {
        $logoPath = '/assets/images/logo.svg'; // fallback
    }
    
    if (isset($general['faviconPath']) && $general['faviconPath'] !== null && $general['faviconPath'] !== '') {
        $faviconPath = url('/public-favicon');
    } else {
        $faviconPath = '/favicon.ico'; // fallback
    }
    
    return response()->json([
        'general' => [
            'siteName' => $siteName,
            'logoPath' => $logoPath,
            'faviconPath' => $faviconPath
        ]
    ]);
});


// Sanctum user endpoint (for API authentication)
Route::middleware('auth:sanctum')->get('/user-sanctum', function (Request $request) {
    return $request->user();
});

// Test route without authentication (temporary for debugging)
Route::get('/admin/test', function() {
    return response()->json(['message' => 'API is accessible', 'time' => now()]);
});

// Test maintenance mode functionality (can be removed in production)
Route::get('/test/maintenance', function() {
    $maintenanceMode = \App\Models\Setting::get('general', 'maintenanceMode', false);
    return response()->json([
        'maintenance_mode' => $maintenanceMode,
        'current_time' => now(),
        'message' => $maintenanceMode ? 'System is under maintenance' : 'System is operational'
    ]);
});

// Public API routes (no authentication required)
Route::get('/popular-courses', [App\Http\Controllers\Api\HomeApiController::class, 'getPopularCourses']);

// Admin API Routes - Protected by auth and super_admin middleware
Route::prefix('admin')->name('api.admin.')->middleware(['web', 'auth', 'super_admin'])->group(function () {
    // Dashboard
    Route::get('/dashboard-stats', [AdminApiController::class, 'getDashboardStats'])->name('dashboard.stats');
    Route::get('/user-growth-chart', [AdminApiController::class, 'getUserGrowthChart'])->name('user.growth.chart');
    // Alias for user-growth-chart (to handle abbreviated form)
    Route::get('/user-chart', [AdminApiController::class, 'getUserGrowthChart']);
    
    Route::get('/revenue-chart', [AdminApiController::class, 'getRevenueChart'])->name('revenue.chart');
    // Alias for revenue-chart (to handle abbreviated form)
    Route::get('/reve-chart', [AdminApiController::class, 'getRevenueChart']);
    
    // User Management
    Route::get('/users', [AdminApiController::class, 'getUsers'])->name('users.index');
    Route::get('/users/{id}', [AdminApiController::class, 'getUser'])->name('users.show');
    Route::post('/users', [AdminApiController::class, 'createUser'])->name('users.store');
    Route::put('/users/{id}', [AdminApiController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{id}', [AdminApiController::class, 'deleteUser'])->name('users.destroy');
    
    // Settings
    Route::get('/settings', [AdminApiController::class, 'getSettings'])->name('settings.index');
    Route::post('/settings', [AdminApiController::class, 'updateSettings'])->name('settings.update');

    // Profile Management
    Route::get('/profile', [AdminApiController::class, 'getProfile'])->name('profile.show');
    Route::put('/profile', [AdminApiController::class, 'updateProfile'])->name('profile.update');
    Route::post('/change-password', [AdminApiController::class, 'changePassword'])->name('change.password');
    Route::post('/verify-password', [AdminApiController::class, 'verifyPassword'])->name('verify.password');

    // File Uploads
    Route::post('/upload-logo', [AdminApiController::class, 'uploadLogo'])->name('upload.logo');
    Route::post('/upload-favicon', [AdminApiController::class, 'uploadFavicon'])->name('upload.favicon');
    Route::delete('/remove-logo', [AdminApiController::class, 'removeLogo'])->name('remove.logo');
    Route::delete('/remove-favicon', [AdminApiController::class, 'removeFavicon'])->name('remove.favicon');
});

// Teacher API Routes - Protected by auth and teacher middleware
Route::prefix('teacher')->name('api.teacher.')->middleware(['web', 'auth', 'teacher', 'maintenance'])->group(function () {
    // Debug route - simple course listing
    Route::get('/courses-debug', function () {
        $teacher = Auth::user();
        \Log::info('🔍 Debug route - teacher info', [
            'teacher_id' => $teacher->id,
            'teacher_email' => $teacher->email,
            'teacher_role' => $teacher->role
        ]);
        
        $allCourses = \App\Models\Course::all();
        $teacherCourses = \App\Models\Course::where('user_id', $teacher->id)->get();
        
        \Log::info('🔍 Debug route - course data', [
            'all_courses_count' => $allCourses->count(),
            'teacher_courses_count' => $teacherCourses->count(),
            'all_courses' => $allCourses->toArray(),
            'teacher_courses' => $teacherCourses->toArray()
        ]);
        
        return response()->json([
            'teacher' => [
                'id' => $teacher->id,
                'email' => $teacher->email,
                'role' => $teacher->role
            ],
            'all_courses' => $allCourses,
            'teacher_courses' => $teacherCourses
        ]);
    });
    
    // Debug route - detailed query testing
    Route::get('/courses-debug-simple', [TeacherApiController::class, 'getCoursesDebugSimple']);
    
    // Dashboard
    Route::get('/dashboard-stats', [TeacherApiController::class, 'getDashboardStats'])->name('dashboard.stats');
    Route::get('/enrollment-chart', [TeacherApiController::class, 'getEnrollmentChart'])->name('enrollment.chart');
    Route::get('/recent-courses', [TeacherApiController::class, 'getRecentCourses'])->name('recent.courses');
    Route::get('/recent-enrollments', [TeacherApiController::class, 'getRecentEnrollments'])->name('recent.enrollments');
    
    // Course Management
    Route::get('/courses', [TeacherApiController::class, 'getCourses'])->name('courses.index');
    Route::post('/courses', [TeacherApiController::class, 'storeCourse'])->name('courses.store');
    Route::get('/courses/{id}', [TeacherApiController::class, 'getCourse'])->name('courses.show');
    Route::put('/courses/{id}', [TeacherApiController::class, 'updateCourse'])->name('courses.update');
    Route::delete('/courses/{id}', [TeacherApiController::class, 'deleteCourse'])->name('courses.destroy');
    Route::get('/courses/{id}/analytics', [TeacherApiController::class, 'getCourseAnalytics'])->name('courses.analytics');
    Route::get('/courses/{id}/preview', [TeacherApiController::class, 'previewCourse'])->name('courses.preview');
    
    // Student Management
    Route::get('/students', [TeacherApiController::class, 'getStudents'])->name('students.index');
    
    // Messaging
    Route::get('/message-stats', [TeacherMessageApiController::class, 'getMessageStats'])->name('messages.stats');
    Route::get('/message-students', [TeacherMessageApiController::class, 'getStudents'])->name('messages.students');
    Route::get('/message-courses', [TeacherMessageApiController::class, 'getCourses'])->name('messages.courses');
    Route::get('/courses/{courseId}/students', [TeacherMessageApiController::class, 'getCourseStudents'])->name('messages.course.students');
    Route::post('/send-message', [TeacherMessageApiController::class, 'sendMessage'])->name('messages.send');
    Route::post('/send-broadcast', [TeacherMessageApiController::class, 'sendBroadcastMessage'])->name('messages.broadcast');
    Route::get('/sent-messages', [TeacherMessageApiController::class, 'getSentMessages'])->name('messages.sent');
    
    // Received Messages (Inbox)
    Route::get('/received-messages', [TeacherMessageApiController::class, 'getReceivedMessages'])->name('messages.received');
    Route::get('/received-messages/{id}', [TeacherMessageApiController::class, 'getReceivedMessage'])->name('messages.received.show');
    Route::post('/received-messages/{id}/mark-as-read', [TeacherMessageApiController::class, 'markReceivedAsRead'])->name('messages.received.mark.read');
    Route::post('/received-messages/{id}/archive', [TeacherMessageApiController::class, 'archiveReceived'])->name('messages.received.archive');
    Route::post('/messages/{messageId}/reply', [TeacherMessageApiController::class, 'replyToMessage'])->name('messages.reply');
    Route::delete('/messages/{id}', [TeacherMessageApiController::class, 'deleteMessage'])->name('messages.delete');
    
    // Profile & Password Management
    Route::post('/change-password', [TeacherApiController::class, 'changePassword'])->name('change.password');
    Route::put('/profile', [TeacherApiController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/upload-image', [TeacherApiController::class, 'uploadProfileImage'])->name('profile.upload.image');
    Route::delete('/profile/remove-image', [TeacherApiController::class, 'removeProfileImage'])->name('profile.remove.image');
});

// Student API Routes - Protected by auth and student middleware
Route::prefix('student')->name('api.student.')->middleware(['web', 'auth', 'student', 'maintenance'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [StudentApiController::class, 'getDashboardData'])->name('dashboard.data');
    
    // Course Enrollments
    Route::get('/enrollments', [StudentApiController::class, 'getEnrollments'])->name('enrollments.index');
    
    // Course Learning (for enrolled students)
    Route::get('/courses/{course}/learn', [StudentApiController::class, 'getCourseForLearning'])->name('courses.learn');
    
    // Browse Courses
    Route::get('/courses', [StudentApiController::class, 'getBrowseCourses'])->name('courses.browse');
    Route::get('/featured-courses', [StudentApiController::class, 'getFeaturedCourses'])->name('courses.featured');
    Route::post('/courses/{course}/enroll', [StudentApiController::class, 'enrollInCourse'])->name('courses.enroll');
    
    // Certificates
    Route::get('/certificates', [StudentApiController::class, 'getCertificates'])->name('certificates.index');
    Route::get('/certificate-stats', [StudentApiController::class, 'getCertificateStats'])->name('certificates.stats');
    Route::get('/certificates/{enrollment}/view', [StudentApiController::class, 'viewCertificate'])->name('certificates.view');
    Route::get('/certificates/{enrollment}/download', [StudentApiController::class, 'downloadCertificate'])->name('certificates.download');
    
    // Lesson Progress Tracking (Simple Video Analytics)
    Route::get('/lessons/{lesson}/progress', [StudentApiController::class, 'getLessonProgress'])->name('lessons.progress.show');
    Route::post('/lessons/{lesson}/progress', [StudentApiController::class, 'saveLessonProgress'])->name('lessons.progress.save');
    Route::post('/lessons/{lesson}/complete', [StudentApiController::class, 'completeLesson'])->name('lessons.complete');
    
    // Messages
    Route::get('/test-debug', [StudentMessageApiController::class, 'testEndpoint'])->name('test.debug');
    Route::get('/message-stats', [StudentMessageApiController::class, 'getMessageStats'])->name('messages.stats');
    Route::get('/messages', [StudentMessageApiController::class, 'getMessages'])->name('messages.index');
    Route::get('/sent-messages', [StudentMessageApiController::class, 'getSentMessages'])->name('messages.sent');
    Route::get('/messages/{id}', [StudentMessageApiController::class, 'getMessage'])->name('messages.show');
    Route::post('/messages/{id}/mark-as-read', [StudentMessageApiController::class, 'markAsRead'])->name('messages.mark.read');
    Route::delete('/messages/{id}', [StudentMessageApiController::class, 'destroy'])->name('messages.destroy');
    Route::get('/messages/{id}/view-url', [StudentMessageApiController::class, 'getMessageViewUrl'])->name('messages.view.url');
    
    // Send Messages to Teachers
    Route::get('/available-teachers', [StudentMessageApiController::class, 'getAvailableTeachers'])->name('messages.teachers');
    Route::get('/enrolled-courses', [StudentMessageApiController::class, 'getEnrolledCourses'])->name('messages.courses');
    Route::post('/send-message', [StudentMessageApiController::class, 'sendMessage'])->name('messages.send');
    Route::post('/messages/{messageId}/reply', [StudentMessageApiController::class, 'replyToMessage'])->name('messages.reply');
    
    // Profile & Password Management
    Route::post('/change-password', [StudentApiController::class, 'changePassword'])->name('change.password');
    Route::put('/profile', [StudentApiController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/upload-image', [StudentApiController::class, 'uploadProfileImage'])->name('profile.upload.image');
    Route::delete('/profile/remove-image', [StudentApiController::class, 'removeProfileImage'])->name('profile.remove.image');
});

// Diagnostic routes (for debugging storage issues)
Route::prefix('diagnostics')->group(function () {
    Route::get('/storage-check', [DiagnosticController::class, 'checkStorage']);
    Route::post('/fix-storage', [DiagnosticController::class, 'fixStorageLink']);
    Route::get('/thumbnails', [DiagnosticController::class, 'listThumbnails']);
});

// Debug routes for environment and spaces testing
Route::prefix('debug')->group(function () {
    Route::get('/environment', [DebugController::class, 'checkEnvironment']);
    Route::get('/test-spaces', [DebugController::class, 'testSpacesUpload']);
});
