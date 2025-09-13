<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Middleware is already defined in routes/web.php
        // No need to apply it here
    }

    /**
     * Single entry point for the Vue.js SPA.
     * This serves as the mounting point for the Vue application.
     *
     * @return \Illuminate\View\View
     */
    public function spa(): View
    {
        return view('admin.admin');
    }

    /**
     * Get dashboard statistics for the API.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function dashboardStats()
    {
        // Get current month and previous month for comparisons
        $currentMonthStart = now()->startOfMonth();
        $previousMonthStart = now()->subMonth()->startOfMonth();
        $previousMonthEnd = now()->subMonth()->endOfMonth();

        // User statistics
        $totalUsers = User::count();
        $newUsersLastMonth = User::whereBetween('created_at', [$previousMonthStart, $previousMonthEnd])->count();
        $newUsersThisMonth = User::where('created_at', '>=', $currentMonthStart)->count();
        $userGrowth = $newUsersLastMonth > 0 
            ? round(($newUsersThisMonth - $newUsersLastMonth) / $newUsersLastMonth * 100, 1) 
            : ($newUsersThisMonth > 0 ? 100 : 0);

        // Course statistics (using default values for now since Course model might not exist yet)
        $activeCourses = 0;
        $courseGrowth = 0;
        if (class_exists('\App\Models\Course')) {
            $totalCourses = \App\Models\Course::count();
            $activeCourses = \App\Models\Course::where('status', 'published')->count();
            $newCoursesLastMonth = \App\Models\Course::whereBetween('created_at', [$previousMonthStart, $previousMonthEnd])->count();
            $newCoursesThisMonth = \App\Models\Course::where('created_at', '>=', $currentMonthStart)->count();
            $courseGrowth = $newCoursesLastMonth > 0 
                ? round(($newCoursesThisMonth - $newCoursesLastMonth) / $newCoursesLastMonth * 100, 1) 
                : ($newCoursesThisMonth > 0 ? 100 : 0);
        }

        // Enrollment and Revenue statistics (using default values for now)
        $monthlyRevenue = 0;
        $revenueGrowth = 0;
        $newEnrollments = 0;
        $enrollmentGrowth = 0;
        if (class_exists('\App\Models\CourseEnrollment')) {
            $newEnrollmentsLastMonth = \App\Models\CourseEnrollment::whereBetween('enrolled_at', [$previousMonthStart, $previousMonthEnd])->count();
            $newEnrollments = \App\Models\CourseEnrollment::where('enrolled_at', '>=', $currentMonthStart)->count();
            $enrollmentGrowth = $newEnrollmentsLastMonth > 0 
                ? round(($newEnrollments - $newEnrollmentsLastMonth) / $newEnrollmentsLastMonth * 100, 1) 
                : ($newEnrollments > 0 ? 100 : 0);
            
            $revenueLastMonth = \App\Models\CourseEnrollment::whereBetween('enrolled_at', [$previousMonthStart, $previousMonthEnd])
                ->sum('payment_amount');
            $monthlyRevenue = \App\Models\CourseEnrollment::where('enrolled_at', '>=', $currentMonthStart)
                ->sum('payment_amount');
            $revenueGrowth = $revenueLastMonth > 0 
                ? round(($monthlyRevenue - $revenueLastMonth) / $revenueLastMonth * 100, 1) 
                : ($monthlyRevenue > 0 ? 100 : 0);
        }

        // Recent users
        $recentUsers = User::orderBy('created_at', 'desc')
                           ->take(10)
                           ->get(['id', 'name', 'email', 'role', 'created_at'])
                           ->map(function($user) {
                               return [
                                   'id' => $user->id,
                                   'name' => $user->name,
                                   'email' => $user->email,
                                   'role' => $user->role,
                                   'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=random',
                                   'created_at' => $user->created_at->diffForHumans()
                               ];
                           });

        // Recent activities
        $recentActivities = collect();
        
        // Add recent user registrations
        $recentRegistrations = User::orderBy('created_at', 'desc')
            ->take(5)
            ->get(['id', 'name', 'role', 'created_at'])
            ->map(function($user) {
                return [
                    'type' => 'user',
                    'title' => "New {$user->role} account created: {$user->name}",
                    'time' => $user->created_at->diffForHumans(),
                    'timestamp' => $user->created_at
                ];
            });
        $recentActivities = $recentActivities->concat($recentRegistrations);
        
        // Sort all activities by timestamp and take the 5 most recent
        $recentActivities = $recentActivities->sortByDesc('timestamp')->take(5)->values();

        // Main statistics for dashboard cards
        $stats = [
            'totalUsers' => $totalUsers,
            'userGrowth' => $userGrowth,
            'activeCourses' => $activeCourses,
            'courseGrowth' => $courseGrowth,
            'monthlyRevenue' => $monthlyRevenue,
            'revenueGrowth' => $revenueGrowth,
            'newEnrollments' => $newEnrollments,
            'enrollmentGrowth' => $enrollmentGrowth
        ];

        return response()->json([
            'stats' => $stats,
            'recentUsers' => $recentUsers,
            'recentActivities' => $recentActivities
        ]);
    }

    /**
     * Get recent users for the API.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function recentUsers()
    {
        $recentUsers = User::select([
            'id', 'name', 'email', 'role', 'created_at', 'last_login', 'is_active'
        ])
        ->latest()
        ->take(10)
        ->get()
        ->map(function($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'created_at' => $user->created_at,
                'last_login' => $user->last_login,
                'is_active' => $user->is_active,
                'registration_date' => $user->created_at->format('M d, Y'),
                'last_login_formatted' => $user->last_login ? $user->last_login->format('M d, Y') : 'Never',
                'days_since_registration' => $user->created_at->diffInDays(now()),
                'is_recent' => $user->created_at->isAfter(now()->subDays(7)),
            ];
        });
        
        return response()->json($recentUsers);
    }

    /**
     * Get list of users for the API.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function usersList()
    {
        $users = User::paginate(10);
        return response()->json($users);
    }

    /**
     * Get a specific user for the API.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUser($id)
    {
        $user = User::findOrFail($id);
        return response()->json($user);
    }

    /**
     * Store a newly created user in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:super_admin,teacher,student',
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['password'] = bcrypt($validated['password']);
        
        $user = User::create($validated);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user
        ]);
    }

    /**
     * Update the specified user in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'role' => 'required|in:super_admin,teacher,student',
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($request->filled('password')) {
            $request->validate([
                'password' => 'string|min:8|confirmed',
            ]);
            $validated['password'] = bcrypt($request->password);
        }

        $user->update($validated);

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user
        ]);
    }

    /**
     * Remove the specified user from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroyUser($id)
    {
        $user = User::findOrFail($id);
        
        // Prevent deletion of super admin users
        if ($user->role === 'super_admin') {
            return response()->json([
                'error' => 'Cannot delete super admin users'
            ], 403);
        }
        
        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return response()->json([
                'error' => 'Cannot delete your own account'
            ], 403);
        }
        
        $userName = $user->name;
        $userEmail = $user->email;
        
        $user->delete();

        return response()->json([
            'message' => "User '{$userName}' ({$userEmail}) deleted successfully"
        ]);
    }

    /**
     * Get system settings for the API.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSettings()
    {
        // In a real app, these would come from the database
        $settings = [
            'site_name' => 'Aidel LMS',
            'site_description' => 'Advanced Learning Management System',
            'contact_email' => 'admin@aidel.com',
            'timezone' => 'UTC',
            'registration_enabled' => true,
            'default_user_role' => 'student',
        ];

        return response()->json($settings);
    }

    /**
     * Update system settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateSettings(Request $request)
    {
        // Validate settings
        $validated = $request->validate([
            'site_name' => 'required|string|max:255',
            'site_description' => 'nullable|string|max:1000',
            'contact_email' => 'required|email',
            'timezone' => 'required|string',
            'registration_enabled' => 'boolean',
            'default_user_role' => 'required|in:student,teacher',
        ]);

        // In a real app, save these to the database
        // For now, just return success
        return response()->json([
            'message' => 'Settings updated successfully',
            'settings' => $validated
        ]);
    }
}
