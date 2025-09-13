<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminApiController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('super_admin');
    }

    /**
     * Get comprehensive dashboard statistics and data.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDashboardStats(): JsonResponse
    {
        \Log::info('getDashboardStats called');
        
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
            : 100;

        // Course statistics
        $totalCourses = \App\Models\Course::count();
        $activeCourses = \App\Models\Course::where('status', 'published')->count();
        $newCoursesLastMonth = \App\Models\Course::whereBetween('created_at', [$previousMonthStart, $previousMonthEnd])->count();
        $newCoursesThisMonth = \App\Models\Course::where('created_at', '>=', $currentMonthStart)->count();
        $courseGrowth = $newCoursesLastMonth > 0 
            ? round(($newCoursesThisMonth - $newCoursesLastMonth) / $newCoursesLastMonth * 100, 1) 
            : 100;

        // Enrollment statistics
        $totalEnrollments = \App\Models\CourseEnrollment::count();
        $newEnrollmentsLastMonth = \App\Models\CourseEnrollment::whereBetween('enrolled_at', [$previousMonthStart, $previousMonthEnd])->count();
        $newEnrollmentsThisMonth = \App\Models\CourseEnrollment::where('enrolled_at', '>=', $currentMonthStart)->count();
        $enrollmentGrowth = $newEnrollmentsLastMonth > 0 
            ? round(($newEnrollmentsThisMonth - $newEnrollmentsLastMonth) / $newEnrollmentsLastMonth * 100, 1) 
            : 100;

        // Revenue statistics
        $totalRevenue = \App\Models\CourseEnrollment::sum('payment_amount');
        $revenueLastMonth = \App\Models\CourseEnrollment::whereBetween('enrolled_at', [$previousMonthStart, $previousMonthEnd])
            ->sum('payment_amount');
        $revenueThisMonth = \App\Models\CourseEnrollment::where('enrolled_at', '>=', $currentMonthStart)
            ->sum('payment_amount');
        $revenueGrowth = $revenueLastMonth > 0 
            ? round(($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth * 100, 1) 
            : 100;

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

        // Recent activities (a combination of recent user registrations, course creations, and enrollments)
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
        
        // Add recent course creations
        $recentCourses = \App\Models\Course::orderBy('created_at', 'desc')
            ->take(5)
            ->get(['id', 'title', 'created_at'])
            ->map(function($course) {
                return [
                    'type' => 'course',
                    'title' => "New course published: {$course->title}",
                    'time' => $course->created_at->diffForHumans(),
                    'timestamp' => $course->created_at
                ];
            });
        $recentActivities = $recentActivities->concat($recentCourses);
        
        // Add recent enrollments
        $recentEnrollments = \App\Models\CourseEnrollment::with(['user', 'course'])
            ->orderBy('enrolled_at', 'desc')
            ->take(5)
            ->get()
            ->map(function($enrollment) {
                return [
                    'type' => 'enrollment',
                    'title' => "{$enrollment->user->name} enrolled in {$enrollment->course->title}",
                    'time' => \Carbon\Carbon::parse($enrollment->enrolled_at)->diffForHumans(),
                    'timestamp' => $enrollment->enrolled_at
                ];
            });
        $recentActivities = $recentActivities->concat($recentEnrollments);
        
        // Add recent payments
        $recentPayments = \App\Models\CourseEnrollment::whereNotNull('payment_amount')
            ->whereNotNull('payment_id')
            ->with(['user', 'course'])
            ->orderBy('enrolled_at', 'desc')
            ->take(5)
            ->get()
            ->map(function($enrollment) {
                return [
                    'type' => 'payment',
                    'title' => "Payment received: \${$enrollment->payment_amount} for {$enrollment->course->title}",
                    'time' => \Carbon\Carbon::parse($enrollment->enrolled_at)->diffForHumans(),
                    'timestamp' => $enrollment->enrolled_at
                ];
            });
        $recentActivities = $recentActivities->concat($recentPayments);
        
        // Sort all activities by timestamp and take the 5 most recent
        $recentActivities = $recentActivities->sortByDesc('timestamp')->take(5)->values();

        // Teacher statistics
        $totalTeachers = User::where('role', 'teacher')->count();
        $newTeachersLastMonth = User::where('role', 'teacher')
            ->whereBetween('created_at', [$previousMonthStart, $previousMonthEnd])->count();
        $newTeachersThisMonth = User::where('role', 'teacher')
            ->where('created_at', '>=', $currentMonthStart)->count();
        $teacherGrowth = $newTeachersLastMonth > 0
            ? round(($newTeachersThisMonth - $newTeachersLastMonth) / $newTeachersLastMonth * 100, 1)
            : 100;

        // Main statistics for dashboard cards
        $mainStats = [
            'totalUsers' => $totalUsers,
            'userGrowth' => $userGrowth,
            'activeCourses' => $activeCourses,
            'courseGrowth' => $courseGrowth,
            'totalTeachers' => $totalTeachers,
            'teacherGrowth' => $teacherGrowth,
            'newEnrollments' => $newEnrollmentsThisMonth,
            'enrollmentGrowth' => $enrollmentGrowth
        ];

        $response = [
            'stats' => $mainStats,
            'detailedStats' => [
                'users' => [
                    'total' => $totalUsers,
                    'active' => User::where('is_active', true)->count(),
                    'inactive' => User::where('is_active', false)->count(),
                    'teachers' => User::where('role', 'teacher')->count(),
                    'students' => User::where('role', 'student')->count(),
                    'admins' => User::where('role', 'super_admin')->count(),
                ],
                'courses' => [
                    'total' => $totalCourses,
                    'published' => \App\Models\Course::where('status', 'published')->count(),
                    'draft' => \App\Models\Course::where('status', 'draft')->count(),
                    'archived' => \App\Models\Course::where('status', 'archived')->count(),
                ],
                'enrollments' => [
                    'total' => $totalEnrollments,
                    'active' => \App\Models\CourseEnrollment::where('status', 'active')->count(),
                    'completed' => \App\Models\CourseEnrollment::where('status', 'completed')->count(),
                    'revenue' => [
                        'total' => $totalRevenue,
                        'thisMonth' => $revenueThisMonth,
                        'lastMonth' => $revenueLastMonth
                    ]
                ]
            ],
            'recentUsers' => $recentUsers,
            'recentActivities' => $recentActivities
        ];
        
        \Log::info('getDashboardStats response:', $response);
        
        return response()->json($response);
    }

    /**
     * Get user growth chart data.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserGrowthChart(Request $request): JsonResponse
    {
        $period = $request->input('period', 'month');
        $result = [];
        $labels = [];
        
        switch ($period) {
            case 'week':
                // Last 7 days
                $startDate = now()->subDays(6)->startOfDay();
                for ($i = 0; $i < 7; $i++) {
                    $date = clone $startDate;
                    $date->addDays($i);
                    $labels[] = $date->format('D');
                    $result[] = User::whereDate('created_at', $date->toDateString())->count();
                }
                break;
                
            case 'month':
                // Last 30 days grouped by week
                $startDate = now()->subDays(29)->startOfDay();
                for ($i = 0; $i < 4; $i++) {
                    $weekStart = clone $startDate;
                    $weekStart->addDays($i * 7);
                    $weekEnd = clone $weekStart;
                    $weekEnd->addDays(6);
                    $labels[] = $weekStart->format('M d') . ' - ' . $weekEnd->format('M d');
                    $result[] = User::whereBetween('created_at', [$weekStart, $weekEnd])->count();
                }
                break;
                
            case 'year':
                // Last 12 months
                $startDate = now()->subMonths(11)->startOfMonth();
                for ($i = 0; $i < 12; $i++) {
                    $date = clone $startDate;
                    $date->addMonths($i);
                    $labels[] = $date->format('M');
                    $nextMonth = clone $date;
                    $nextMonth->addMonth();
                    $result[] = User::whereBetween('created_at', [$date, $nextMonth->subSecond()])->count();
                }
                break;
                
            default:
                return response()->json([
                    'error' => 'Invalid period. Use week, month, or year.'
                ], 400);
        }
        
        return response()->json([
            'labels' => $labels,
            'data' => $result
        ]);
    }

    /**
     * Get revenue chart data.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRevenueChart(Request $request): JsonResponse
    {
        $period = $request->input('period', 'month');
        $result = [];
        $labels = [];
        
        switch ($period) {
            case 'week':
                // Last 7 days
                $startDate = now()->subDays(6)->startOfDay();
                for ($i = 0; $i < 7; $i++) {
                    $date = clone $startDate;
                    $date->addDays($i);
                    $labels[] = $date->format('D');
                    $result[] = \App\Models\CourseEnrollment::whereDate('enrolled_at', $date->toDateString())
                        ->sum('payment_amount');
                }
                break;
                
            case 'month':
                // Last 30 days grouped by week
                $startDate = now()->subDays(29)->startOfDay();
                for ($i = 0; $i < 4; $i++) {
                    $weekStart = clone $startDate;
                    $weekStart->addDays($i * 7);
                    $weekEnd = clone $weekStart;
                    $weekEnd->addDays(6);
                    $labels[] = $weekStart->format('M d') . ' - ' . $weekEnd->format('M d');
                    $result[] = \App\Models\CourseEnrollment::whereBetween('enrolled_at', [$weekStart, $weekEnd])
                        ->sum('payment_amount');
                }
                break;
                
            case 'year':
                // Last 12 months
                $startDate = now()->subMonths(11)->startOfMonth();
                for ($i = 0; $i < 12; $i++) {
                    $date = clone $startDate;
                    $date->addMonths($i);
                    $labels[] = $date->format('M');
                    $nextMonth = clone $date;
                    $nextMonth->addMonth();
                    $result[] = \App\Models\CourseEnrollment::whereBetween('enrolled_at', [$date, $nextMonth->subSecond()])
                        ->sum('payment_amount');
                }
                break;
                
            default:
                return response()->json([
                    'error' => 'Invalid period. Use week, month, or year.'
                ], 400);
        }
        
        return response()->json([
            'labels' => $labels,
            'data' => $result
        ]);
    }

    /**
     * Get course growth chart data.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCourseGrowthChart(Request $request): JsonResponse
    {
        $period = $request->input('period', 'month');
        $result = [];
        $labels = [];

        switch ($period) {
            case 'week':
                // Last 7 days
                $startDate = now()->subDays(6)->startOfDay();
                for ($i = 0; $i < 7; $i++) {
                    $date = clone $startDate;
                    $date->addDays($i);
                    $labels[] = $date->format('D');
                    $result[] = \App\Models\Course::whereDate('created_at', $date->toDateString())->count();
                }
                break;

            case 'month':
                // Last 30 days grouped by week
                $startDate = now()->subDays(29)->startOfDay();
                for ($i = 0; $i < 4; $i++) {
                    $weekStart = clone $startDate;
                    $weekStart->addDays($i * 7);
                    $weekEnd = clone $weekStart;
                    $weekEnd->addDays(6);
                    $labels[] = $weekStart->format('M d') . ' - ' . $weekEnd->format('M d');
                    $result[] = \App\Models\Course::whereBetween('created_at', [$weekStart, $weekEnd])->count();
                }
                break;

            case 'year':
                // Last 12 months
                $startDate = now()->subMonths(11)->startOfMonth();
                for ($i = 0; $i < 12; $i++) {
                    $date = clone $startDate;
                    $date->addMonths($i);
                    $labels[] = $date->format('M');
                    $nextMonth = clone $date;
                    $nextMonth->addMonth();
                    $result[] = \App\Models\Course::whereBetween('created_at', [$date, $nextMonth->subSecond()])->count();
                }
                break;

            default:
                return response()->json([
                    'error' => 'Invalid period. Use week, month, or year.'
                ], 400);
        }

        return response()->json([
            'labels' => $labels,
            'data' => $result
        ]);
    }

    /**
     * Get all users with pagination.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUsers(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search', '');
        $role = $request->input('role', '');
        
        $query = User::query();
        
        // Apply search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        // Apply role filter
        if ($role) {
            $query->where('role', $role);
        }
        
        $users = $query->paginate($perPage);
        
        return response()->json($users);
    }

    /**
     * Get a specific user.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUser($id): JsonResponse
    {
        $user = User::findOrFail($id);
        return response()->json($user);
    }

    /**
     * Create a new user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createUser(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'string', new \App\Rules\PasswordPolicy],
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
        ], 201);
    }

    /**
     * Update an existing user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateUser(Request $request, $id): JsonResponse
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
                'password' => ['string', new \App\Rules\PasswordPolicy],
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
     * Delete a user.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteUser($id): JsonResponse
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully'
        ]);
    }

    /**
     * Get all system settings grouped by category.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSettings(): JsonResponse
    {
        // Fetch all settings from database using our Setting model
        $settings = \App\Models\Setting::getAllSettings();
        
        // If no settings exist yet in the database, return empty structure
        if (empty($settings)) {
            $settings = [
                'general' => [],
                'appearance' => [],
                'email' => [],
                'payment' => [],
                'security' => []
            ];
        }

        return response()->json($settings);
    }
    
    /**
     * Get settings for a specific group.
     *
     * @param string $group
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSettingsByGroup(string $group): JsonResponse
    {
        $settings = \App\Models\Setting::getGroup($group);
        
        return response()->json($settings);
    }

    /**
     * Update system settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateSettings(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'settings' => 'required|array',
        ]);
        
        // Update settings in database
        \App\Models\Setting::updateSettings($validated['settings']);

        return response()->json([
            'message' => 'Settings updated successfully',
            'settings' => \App\Models\Setting::getAllSettings()
        ]);
    }
    
    /**
     * Update settings for a specific group.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $group
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateSettingsByGroup(Request $request, string $group): JsonResponse
    {
        $validated = $request->validate([
            'settings' => 'required|array',
        ]);
        
        // Update each setting in the group
        foreach ($validated['settings'] as $key => $value) {
            \App\Models\Setting::set($group, $key, $value);
        }

        return response()->json([
            'message' => 'Settings updated successfully',
            'settings' => \App\Models\Setting::getGroup($group)
        ]);
    }

    /**
     * Upload logo file.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadLogo(Request $request): JsonResponse
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:5120', // Max 5MB
        ]);

        try {
            $file = $request->file('logo');
            $fileName = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
            
            // Store the file in public/uploads/logos directory
            $path = $file->storeAs('uploads/logos', $fileName, 'public');
            
            // Get the public URL
            $logoPath = '/storage/' . $path;
            
            // Update the setting in database
            \App\Models\Setting::set('general', 'logoPath', $logoPath);
            
            return response()->json([
                'message' => 'Logo uploaded successfully',
                'logoPath' => $logoPath
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to upload logo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload favicon file.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadFavicon(Request $request): JsonResponse
    {
        $request->validate([
            'favicon' => 'required|image|mimes:ico,png,jpg,gif,svg|max:1024', // Max 1MB
        ]);

        try {
            $file = $request->file('favicon');
            $fileName = 'favicon_' . time() . '.' . $file->getClientOriginalExtension();
            
            // Store the file in public/uploads/favicons directory
            $path = $file->storeAs('uploads/favicons', $fileName, 'public');
            
            // Get the public URL
            $faviconPath = '/storage/' . $path;
            
            // Update the setting in database
            \App\Models\Setting::set('general', 'faviconPath', $faviconPath);
            
            return response()->json([
                'message' => 'Favicon uploaded successfully',
                'faviconPath' => $faviconPath
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to upload favicon',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove logo file.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeLogo(): JsonResponse
    {
        try {
            // Get current logo path
            $currentLogo = \App\Models\Setting::get('general', 'logoPath');
            
            // Delete the file if it exists
            if ($currentLogo && file_exists(public_path('storage' . str_replace('/storage', '', $currentLogo)))) {
                unlink(public_path('storage' . str_replace('/storage', '', $currentLogo)));
            }
            
            // Remove the setting from database
            \App\Models\Setting::set('general', 'logoPath', '');
            
            return response()->json([
                'message' => 'Logo removed successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to remove logo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove favicon file.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeFavicon(): JsonResponse
    {
        try {
            // Get current favicon path
            $currentFavicon = \App\Models\Setting::get('general', 'faviconPath');
            
            // Delete the file if it exists
            if ($currentFavicon && file_exists(public_path('storage' . str_replace('/storage', '', $currentFavicon)))) {
                unlink(public_path('storage' . str_replace('/storage', '', $currentFavicon)));
            }
            
            // Remove the setting from database
            \App\Models\Setting::set('general', 'faviconPath', '');
            
            return response()->json([
                'message' => 'Favicon removed successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to remove favicon',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current admin profile data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'bio' => $user->bio,
                'phone' => $user->phone,
                'profile_picture' => $user->profile_picture,
                'avatar' => $user->profile_picture ? url('/public-avatar/' . $user->id) : null,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ]
        ]);
    }

    /**
     * Update admin profile information.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'bio' => 'nullable|string|max:1000',
            'phone' => 'nullable|string|max:20'
        ]);

        try {
            // Update the user
            $user->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'bio' => $user->bio,
                    'phone' => $user->phone,
                    'profile_picture' => $user->profile_picture,
                    'avatar' => $user->profile_picture ? url('/public-avatar/' . $user->id) : null,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
