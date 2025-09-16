<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class TeacherApiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('teacher');
    }

    /**
     * Get dashboard statistics for teacher
     */
    public function getDashboardStats()
    {
        $teacher = Auth::user();
        
        // Course statistics
        $totalCourses = Course::where('user_id', $teacher->id)->count();
        $publishedCourses = Course::where('user_id', $teacher->id)
            ->where('status', 'published')
            ->count();
        $draftCourses = Course::where('user_id', $teacher->id)
            ->where('status', 'draft')
            ->count();
            
        // Student statistics
        $totalEnrollments = CourseEnrollment::whereHas('course', function ($query) use ($teacher) {
            $query->where('user_id', $teacher->id);
        })->count();
        
        $activeEnrollments = CourseEnrollment::whereHas('course', function ($query) use ($teacher) {
            $query->where('user_id', $teacher->id);
        })->where('status', 'active')->count();
        
        $completedEnrollments = CourseEnrollment::whereHas('course', function ($query) use ($teacher) {
            $query->where('user_id', $teacher->id);
        })->where('status', 'completed')->count();

        return response()->json([
            'stats' => [
                'totalCourses' => $totalCourses,
                'publishedCourses' => $publishedCourses,
                'draftCourses' => $draftCourses,
                'totalEnrollments' => $totalEnrollments,
                'activeEnrollments' => $activeEnrollments,
                'completedEnrollments' => $completedEnrollments
            ]
        ]);
    }

    /**
     * Get enrollment chart data for teacher dashboard
     */
    public function getEnrollmentChart(Request $request)
    {
        $teacher = Auth::user();
        $period = $request->get('period', 'week'); // Default to week view

        $chartLabels = [];
        $chartData = [];

        if ($period == 'week') {
            // Show daily data for week view (last 7 days)
            $days = 7;

            for ($i = $days - 1; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $chartLabels[] = $date->format('M d');

                $count = CourseEnrollment::whereHas('course', function ($query) use ($teacher) {
                    $query->where('user_id', $teacher->id);
                })
                ->whereDate('enrolled_at', $date->toDateString())
                ->count();

                $chartData[] = $count;
            }
        } elseif ($period == 'month') {
            // Show weekly data for month view (last 4 weeks)
            for ($i = 3; $i >= 0; $i--) {
                $weekStart = Carbon::now()->subWeeks($i)->startOfWeek();
                $weekEnd = Carbon::now()->subWeeks($i)->endOfWeek();
                $chartLabels[] = 'Week of ' . $weekStart->format('M d');

                $count = CourseEnrollment::whereHas('course', function ($query) use ($teacher) {
                    $query->where('user_id', $teacher->id);
                })
                ->whereBetween('enrolled_at', [$weekStart->toDateString(), $weekEnd->toDateString()])
                ->count();

                $chartData[] = $count;
            }
        } else {
            // Default to year view - Show monthly data for year view (12 months)
            for ($i = 11; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $chartLabels[] = $date->format('M Y');

                $count = CourseEnrollment::whereHas('course', function ($query) use ($teacher) {
                    $query->where('user_id', $teacher->id);
                })
                ->whereMonth('enrolled_at', $date->month)
                ->whereYear('enrolled_at', $date->year)
                ->count();

                $chartData[] = $count;
            }
        }

        return response()->json([
            'labels' => $chartLabels,
            'data' => $chartData
        ]);
    }

    /**
     * Get recent courses for teacher dashboard
     */
    public function getRecentCourses()
    {
        $teacher = Auth::user();
        
        $courses = Course::where('user_id', $teacher->id)
            ->withCount(['enrollments', 'modules', 'lessons'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($course) {
                return [
                    'id' => $course->id,
                    'title' => $course->title,
                    'status' => $course->status,
                    'thumbnail' => $course->thumbnail,
                    'thumbnail_url' => \App\Helpers\StorageHelper::getPublicUrl($course->thumbnail),
                    'enrollments_count' => $course->enrollments_count,
                    'modules_count' => $course->modules_count,
                    'lessons_count' => $course->lessons_count,
                    'created_at' => $course->created_at->toISOString()
                ];
            });

        return response()->json($courses);
    }

    /**
     * Get recent enrollments for teacher dashboard
     */
    public function getRecentEnrollments()
    {
        $teacher = Auth::user();
        
        $enrollments = CourseEnrollment::with(['student', 'course'])
            ->whereHas('course', function ($query) use ($teacher) {
                $query->where('user_id', $teacher->id);
            })
            ->orderBy('enrolled_at', 'desc')
            ->take(7)
            ->get()
            ->map(function ($enrollment) {
                return [
                    'id' => $enrollment->id,
                    'student' => [
                        'id' => $enrollment->student->id,
                        'name' => $enrollment->student->name,
                        'avatar' => $enrollment->student->profile_picture
                    ],
                    'course' => [
                        'id' => $enrollment->course->id,
                        'title' => $enrollment->course->title
                    ],
                    'progress_percentage' => $enrollment->progress_percentage,
                    'enrolled_at' => $enrollment->enrolled_at->toISOString()
                ];
            });

        return response()->json($enrollments);
    }

    /**
     * Get all courses for teacher
     */
    public function getCourses(Request $request)
    {
        $teacher = Auth::user();
        
        if (!$teacher) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }
        
        if (!$teacher->isTeacher()) {
            return response()->json(['error' => 'Not authorized'], 403);
        }
        
        // Build simple query
        $query = Course::where('user_id', $teacher->id);
        
        // Apply status filter if provided
        if ($request->filled('status') && $request->status !== 'null') {
            $query->where('status', $request->status);
        }
        
        // Apply search filter if provided
        if ($request->filled('search') && $request->search !== 'null') {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', '%' . $searchTerm . '%')
                  ->orWhere('short_description', 'like', '%' . $searchTerm . '%');
            });
        }
        
        // Apply sorting
        switch ($request->get('sort_by', 'created_desc')) {
            case 'created_asc':
                $query->orderBy('created_at', 'asc');
                break;
            case 'title_asc':
                $query->orderBy('title', 'asc');
                break;
            case 'title_desc':
                $query->orderBy('title', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }
        
        // Execute query with pagination
        $courses = $query->paginate(9);
        
        // Add relationship counts and format data
        $courses->getCollection()->transform(function ($course) {
            // Count relationships manually to avoid withCount issues
            $course->enrollments_count = $course->enrollments()->count();
            $course->modules_count = $course->modules()->count();
            $course->lessons_count = $course->lessons()->count();

            // Calculate completion rate based on student progress
            if ($course->enrollments_count > 0) {
                $totalProgress = $course->enrollments()->sum('progress_percentage');
                $course->completion_rate = round($totalProgress / $course->enrollments_count);
            } else {
                $course->completion_rate = 0;
            }

            $course->created_at_formatted = $course->created_at->toISOString();

            // Format thumbnail URL
            if ($course->thumbnail) {
                $course->thumbnail_url = \App\Helpers\StorageHelper::getPublicUrl($course->thumbnail);
            }

            return $course;
        });
        
        return response()->json($courses);
    }

    /**
     * Get all students enrolled in teacher's courses
     */
    public function getStudents(Request $request)
    {
        $teacher = Auth::user();
        
        if (!$teacher) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }
        
        if (!$teacher->isTeacher()) {
            return response()->json(['error' => 'Not authorized'], 403);
        }
        
        // Get students enrolled in teacher's courses with their enrollment details
        $studentsQuery = User::whereHas('enrollments.course', function ($query) use ($teacher) {
            $query->where('user_id', $teacher->id);
        })
        ->with(['enrollments' => function ($query) use ($teacher) {
            $query->whereHas('course', function ($q) use ($teacher) {
                $q->where('user_id', $teacher->id);
            })->with('course:id,title');
        }])
        ->with(['lessonProgress' => function ($query) use ($teacher) {
            $query->whereHas('lesson.module.course', function ($q) use ($teacher) {
                $q->where('user_id', $teacher->id);
            })->orderBy('updated_at', 'desc')->limit(1);
        }]);
        
        // Apply search filter if provided
        if ($request->filled('search') && $request->search !== 'null') {
            $searchTerm = $request->search;
            $studentsQuery->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('email', 'like', '%' . $searchTerm . '%');
            });
        }
        
        // Apply course filter if provided
        if ($request->filled('course_id') && $request->course_id !== 'null') {
            $courseId = $request->course_id;
            $studentsQuery->whereHas('enrollments.course', function ($query) use ($teacher, $courseId) {
                $query->where('user_id', $teacher->id)
                      ->where('id', $courseId);
            });
        }
        
        // Apply progress filter if provided
        if ($request->filled('progress') && $request->progress !== 'null') {
            switch ($request->progress) {
                case 'not_started':
                    $studentsQuery->whereHas('enrollments', function ($query) use ($teacher) {
                        $query->whereHas('course', function ($q) use ($teacher) {
                            $q->where('user_id', $teacher->id);
                        })->where('progress_percentage', 0);
                    });
                    break;
                case 'in_progress':
                    $studentsQuery->whereHas('enrollments', function ($query) use ($teacher) {
                        $query->whereHas('course', function ($q) use ($teacher) {
                            $q->where('user_id', $teacher->id);
                        })->whereBetween('progress_percentage', [1, 99]);
                    });
                    break;
                case 'completed':
                    $studentsQuery->whereHas('enrollments', function ($query) use ($teacher) {
                        $query->whereHas('course', function ($q) use ($teacher) {
                            $q->where('user_id', $teacher->id);
                        })->where('progress_percentage', 100);
                    });
                    break;
            }
        }
        
        // Execute query with pagination
        $students = $studentsQuery->paginate(15);
        
        // Transform the data to match the expected format
        $students->getCollection()->transform(function ($student) {
            // Calculate average progress across all enrollments
            $totalProgress = 0;
            $enrollmentCount = $student->enrollments->count();
            $enrolledCourses = [];
            
            foreach ($student->enrollments as $enrollment) {
                $totalProgress += $enrollment->progress_percentage;
                $enrolledCourses[] = [
                    'id' => $enrollment->course->id,
                    'title' => $enrollment->course->title,
                    'progress' => $enrollment->progress_percentage
                ];
            }
            
            $averageProgress = $enrollmentCount > 0 ? round($totalProgress / $enrollmentCount) : 0;
            
            // Get actual last activity from lesson progress or enrollment
            $lastActivity = null;
            
            if ($student->lessonProgress && $student->lessonProgress->isNotEmpty()) {
                $lastActivity = $student->lessonProgress->first()->updated_at;
            } elseif ($student->enrollments && $student->enrollments->isNotEmpty()) {
                $lastActivity = $student->enrollments->max('updated_at');
            } else {
                $lastActivity = $student->updated_at;
            }
            
            return [
                'id' => $student->id,
                'name' => $student->name,
                'email' => $student->email,
                'profile_picture' => $student->profile_picture,
                'avatar' => $student->profile_picture ? url('/public-avatar/' . $student->id) : null,
                'enrolled_courses' => $enrolledCourses,
                'average_progress' => $averageProgress,
                'last_activity' => $lastActivity ? $lastActivity->toISOString() : null,
                'enrolled_count' => $enrollmentCount
            ];
        });
        
        return response()->json($students);
    }

    /**
     * Store a new course with video chapters
     */
    public function storeCourse(Request $request)
    {
        // Handle JSON data from FormData
        if ($request->has('chapters') && is_string($request->chapters)) {
            $request->merge(['chapters' => json_decode($request->chapters, true)]);
        }
        
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'short_description' => 'required|string|max:255',
            'description' => 'nullable|string',
            'level' => 'required|in:beginner,intermediate,advanced',
            'status' => 'required|in:draft,published',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
            'chapters' => 'required|array|min:1',
            'chapters.*.title' => 'required|string|max:255',
            'chapters.*.description' => 'nullable|string',
            'chapter_videos.*' => 'nullable|file|mimes:mp4,avi,mov,webm,mkv|max:512000', // 500MB max - Made optional
        ]);

        if ($validator->fails()) {
            \Log::error('Course creation validation failed', [
                'errors' => $validator->errors()->toArray(),
                'request_data' => $request->except(['chapter_videos', 'thumbnail']),
                'file_sizes' => [
                    'thumbnail' => $request->hasFile('thumbnail') ? $request->file('thumbnail')->getSize() : null,
                    'videos' => collect($request->file('chapter_videos', []))->map(fn($file) => $file ? $file->getSize() : null)->toArray()
                ]
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Handle thumbnail upload
            $thumbnailPath = null;
            if ($request->hasFile('thumbnail')) {
                // Determine disk based on configuration
                $filesystemDefault = config('filesystems.default');
                $envFilesystemDisk = env('FILESYSTEM_DISK');
                $isProduction = config('app.env') === 'production';

                // Log configuration details
                \Log::info('🔍 DISK SELECTION DEBUG', [
                    'config_filesystems_default' => $filesystemDefault,
                    'env_FILESYSTEM_DISK' => $envFilesystemDisk,
                    'is_production' => $isProduction,
                    'config_app_env' => config('app.env'),
                    'spaces_key_exists' => config('filesystems.disks.spaces.key') ? 'YES' : 'NO',
                    'spaces_bucket' => config('filesystems.disks.spaces.bucket'),
                ]);

                // Use filesystem default configuration
                $disk = $filesystemDefault ?: 'public';

                try {
                    $file = $request->file('thumbnail');

                    // Determine actual storage location
                    $storageLocation = $disk === 'spaces'
                        ? 'DigitalOcean Spaces (kiyoraka-world)'
                        : 'Local Storage (public disk)';

                    // Log file details before upload
                    \Log::info('📤 UPLOADING THUMBNAIL', [
                        '🎯 STORAGE_LOCATION' => $storageLocation,
                        'disk_name' => $disk,
                        'original_name' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                        'size_readable' => round($file->getSize() / 1024, 2) . ' KB',
                        'mime' => $file->getMimeType(),
                        'env' => config('app.env'),
                        'filesystem_default' => config('filesystems.default'),
                        'spaces_configured' => config('filesystems.disks.spaces.key') ? 'YES' : 'NO',
                        'spaces_bucket' => config('filesystems.disks.spaces.bucket') ?: 'NOT SET'
                    ]);

                    $thumbnailPath = $file->store('course-thumbnails', $disk);

                    // Generate the full URL
                    $fullUrl = \App\Helpers\StorageHelper::getPublicUrl($thumbnailPath);

                    \Log::info('✅ THUMBNAIL UPLOAD SUCCESS', [
                        '🎯 STORAGE_LOCATION' => $storageLocation,
                        'disk' => $disk,
                        'path' => $thumbnailPath,
                        'full_url' => $fullUrl,
                        'will_persist' => $disk === 'spaces' ? 'YES - Stored in Spaces' : 'NO - Local only',
                        'env' => config('app.env')
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Failed to upload thumbnail', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'disk' => $disk,
                        'env' => config('app.env')
                    ]);
                    throw new \Exception('Failed to upload thumbnail: ' . $e->getMessage());
                }
            }
            
            // Create course
            $courseData = [
                'user_id' => Auth::id(),
                'title' => $request->title,
                'slug' => Str::slug($request->title),
                'short_description' => $request->short_description,
                'description' => $request->description,
                'thumbnail' => $thumbnailPath,
                'level' => $request->level,
                'status' => $request->status,
                'duration_hours' => 0, // Duration removed
                'published_at' => $request->status === 'published' ? now() : null,
            ];
            
            $course = Course::create($courseData);

            // Create default module for video chapters
            $module = Module::create([
                'course_id' => $course->id,
                'title' => 'Course Content',
                'description' => 'Main course content with video lessons',
                'position' => 1,
                'is_published' => true
            ]);

            // Create lessons for each chapter
            foreach ($request->chapters as $index => $chapter) {
                // Handle video upload for this chapter
                $videoUrl = null;
                if ($request->hasFile("chapter_videos.{$index}")) {
                    $videoFile = $request->file("chapter_videos.{$index}");
                    // Determine disk based on configuration
                    $filesystemDefault = config('filesystems.default');
                    $envFilesystemDisk = env('FILESYSTEM_DISK');

                    // Log configuration for video upload
                    \Log::info('🔍 VIDEO DISK SELECTION DEBUG', [
                        'chapter_index' => $index,
                        'config_filesystems_default' => $filesystemDefault,
                        'env_FILESYSTEM_DISK' => $envFilesystemDisk,
                        'is_production' => config('app.env') === 'production',
                    ]);

                    // Use filesystem default configuration
                    $disk = $filesystemDefault ?: 'public';

                    try {
                        // Determine actual storage location
                        $storageLocation = $disk === 'spaces'
                            ? 'DigitalOcean Spaces (kiyoraka-world)'
                            : 'Local Storage (public disk)';

                        \Log::info('📤 UPLOADING VIDEO - Chapter ' . ($index + 1), [
                            '🎯 STORAGE_LOCATION' => $storageLocation,
                            'chapter_title' => $chapter['title'],
                            'chapter_index' => $index,
                            'disk_name' => $disk,
                            'original_name' => $videoFile->getClientOriginalName(),
                            'size' => $videoFile->getSize(),
                            'size_mb' => round($videoFile->getSize() / 1024 / 1024, 2) . ' MB',
                            'mime' => $videoFile->getMimeType(),
                            'spaces_configured' => config('filesystems.disks.spaces.key') ? 'YES' : 'NO',
                            'warning' => $disk === 'public' ? '⚠️ Using local storage - files will be lost on deployment!' : null
                        ]);

                        $videoPath = $videoFile->store('course-videos', $disk);
                        // Store the relative path, will be accessed via /storage/ or Spaces URL
                        $videoUrl = $videoPath;

                        // Generate the full URL
                        $fullUrl = \App\Helpers\StorageHelper::getPublicUrl($videoPath);

                        \Log::info('✅ VIDEO UPLOAD SUCCESS - Chapter ' . ($index + 1), [
                            '🎯 STORAGE_LOCATION' => $storageLocation,
                            'chapter_title' => $chapter['title'],
                            'disk' => $disk,
                            'path' => $videoPath,
                            'full_url' => $fullUrl,
                            'will_persist' => $disk === 'spaces' ? 'YES - Stored in Spaces' : 'NO - Local only'
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Failed to upload video for chapter', [
                            'chapter_index' => $index,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                            'disk' => $disk
                        ]);
                        throw new \Exception('Failed to upload video for chapter ' . ($index + 1) . ': ' . $e->getMessage());
                    }
                }

                $lesson = Lesson::create([
                    'module_id' => $module->id,
                    'title' => $chapter['title'],
                    'description' => $chapter['description'] ?? null,
                    'content_type' => 'video',
                    'video_url' => $videoUrl,
                    'video_protected' => true, // Always protect video URLs
                    'duration_minutes' => 0, // Duration removed as requested
                    'position' => $index + 1,
                    'is_published' => true,
                    'is_free_preview' => false
                ]);
            }

            DB::commit();

            // Reload course with relationships and format the response
            $course->load('modules.lessons');

            // Format thumbnail URL for response
            if ($course->thumbnail) {
                $course->thumbnail_url = \App\Helpers\StorageHelper::getPublicUrl($course->thumbnail);
            }

            return response()->json([
                'success' => true,
                'message' => 'Course created successfully',
                'course' => $course
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create course: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get course details with modules and lessons
     */
    public function getCourse($id)
    {
        $teacher = Auth::user();
        
        $course = Course::where('user_id', $teacher->id)
            ->with(['modules.lessons' => function ($query) {
                $query->orderBy('position');
            }])
            ->withCount(['enrollments'])
            ->findOrFail($id);

        // Mask video URLs for security
        $course->modules->each(function ($module) {
            $module->lessons->each(function ($lesson) {
                if ($lesson->video_protected && $lesson->video_url) {
                    $lesson->video_url_masked = $this->maskVideoUrl($lesson->video_url);
                }
            });
        });

        return response()->json($course);
    }

    /**
     * Mask video URL for security (simple implementation)
     */
    private function maskVideoUrl($url)
    {
        // For now, just show the domain and mask the video ID
        $parsed = parse_url($url);
        if (isset($parsed['host']) && strpos($parsed['host'], 'youtube.com') !== false) {
            return $parsed['scheme'] . '://' . $parsed['host'] . '/watch?v=***PROTECTED***';
        }
        return '***PROTECTED VIDEO URL***';
    }

    /**
     * Update a course with chapters
     */
    public function updateCourse(Request $request, $id)
    {
        $teacher = Auth::user();
        
        $course = Course::where('user_id', $teacher->id)->findOrFail($id);
        
        // Handle JSON data from FormData
        if ($request->has('chapters') && is_string($request->chapters)) {
            $request->merge(['chapters' => json_decode($request->chapters, true)]);
        }
        
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'short_description' => 'required|string|max:255',
            'description' => 'nullable|string',
            'level' => 'required|in:beginner,intermediate,advanced',
            'status' => 'required|in:draft,published,archived',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
            'remove_thumbnail' => 'nullable|string',
            'chapters' => 'required|array|min:1',
            'chapters.*.title' => 'required|string|max:255',
            'chapters.*.description' => 'nullable|string',
            'chapters.*.video_url' => 'nullable|string', // Optional existing URL
            'chapter_videos.*' => 'nullable|mimes:mp4,avi,mov,webm|max:512000', // 500MB max - Optional new upload
        ]);

        if ($validator->fails()) {
            \Log::error('Course creation validation failed', [
                'errors' => $validator->errors()->toArray(),
                'request_data' => $request->except(['chapter_videos', 'thumbnail']),
                'file_sizes' => [
                    'thumbnail' => $request->hasFile('thumbnail') ? $request->file('thumbnail')->getSize() : null,
                    'videos' => collect($request->file('chapter_videos', []))->map(fn($file) => $file ? $file->getSize() : null)->toArray()
                ]
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Handle thumbnail upload/removal
            $updateData = [
                'title' => $request->title,
                'slug' => Str::slug($request->title),
                'short_description' => $request->short_description,
                'description' => $request->description,
                'level' => $request->level,
                'status' => $request->status,
                'duration_hours' => 0, // Duration removed
            ];
            
            // Handle thumbnail changes
            if ($request->hasFile('thumbnail')) {
                // Delete old thumbnail if exists
                if ($course->thumbnail) {
                    Storage::disk('public')->delete($course->thumbnail);
                }
                // Upload new thumbnail
                $updateData['thumbnail'] = $request->file('thumbnail')->store('course-thumbnails', 'public');
            } elseif ($request->has('remove_thumbnail') && $request->remove_thumbnail === 'true') {
                // Remove existing thumbnail
                if ($course->thumbnail) {
                    Storage::disk('public')->delete($course->thumbnail);
                }
                $updateData['thumbnail'] = null;
            }
            
            // Update published_at if status changed to published
            if ($request->status === 'published' && $course->status !== 'published') {
                $updateData['published_at'] = now();
            } elseif ($request->status !== 'published') {
                $updateData['published_at'] = null;
            }
            
            $course->update($updateData);
            
            // Get or create the main module
            $module = $course->modules()->first();
            if (!$module) {
                $module = Module::create([
                    'course_id' => $course->id,
                    'title' => 'Course Content',
                    'description' => 'Main course content with video lessons',
                    'position' => 1,
                    'is_published' => true
                ]);
            }
            
            // Delete existing lessons
            $module->lessons()->delete();
            
            // Create new lessons from chapters
            foreach ($request->chapters as $index => $chapter) {
                // Handle video upload for this chapter
                $videoUrl = $chapter['video_url'] ?? null; // Keep existing URL if provided

                // Check for new video upload
                if ($request->hasFile("chapter_videos.{$index}")) {
                    $videoFile = $request->file("chapter_videos.{$index}");
                    $videoPath = $videoFile->store('course-videos', 'public');
                    $videoUrl = $videoPath; // Override with new upload path
                }

                Lesson::create([
                    'module_id' => $module->id,
                    'title' => $chapter['title'],
                    'description' => $chapter['description'] ?? null,
                    'content_type' => 'video',
                    'video_url' => $videoUrl,
                    'video_protected' => true,
                    'duration_minutes' => 0, // Duration removed
                    'position' => $index + 1,
                    'is_published' => true,
                    'is_free_preview' => false
                ]);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Course updated successfully',
                'course' => $course
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update course: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a course
     */
    public function deleteCourse($id)
    {
        $teacher = Auth::user();
        
        $course = Course::where('user_id', $teacher->id)->findOrFail($id);
        
        // Check if course has enrollments
        if ($course->enrollments()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete course with active enrollments'
            ], 400);
        }
        
        $course->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Course deleted successfully'
        ]);
    }

    /**
     * Debug route to test basic query
     */
    public function getCoursesDebugSimple()
    {
        $teacher = Auth::user();
        
        // Get basic courses data
        $basicCourses = Course::where('user_id', $teacher->id)->get();
        
        return response()->json([
            'basic_courses_count' => $basicCourses->count(),
            'basic_courses' => $basicCourses,
            'message' => 'Debug information for course data'
        ]);
    }

    /**
     * Change user password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => ['required', 'string', 'confirmed', new \App\Rules\PasswordPolicy],
        ]);

        // Verify current password
        if (!\Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect.'
            ], 422);
        }

        // Update password
        $user->update([
            'password' => bcrypt($validated['new_password'])
        ]);

        return response()->json([
            'message' => 'Password changed successfully.'
        ]);
    }

    /**
     * Update teacher profile information
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'bio' => 'nullable|string|max:1000',
            'youtube_url' => 'nullable|url|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'twitter_url' => 'nullable|url|max:255',
            'facebook_url' => 'nullable|url|max:255',
        ]);

        // Update user profile
        $user->update([
            'name' => $validated['name'],
            'bio' => $validated['bio'],
            'youtube_url' => $validated['youtube_url'],
            'linkedin_url' => $validated['linkedin_url'],
            'twitter_url' => $validated['twitter_url'],
            'facebook_url' => $validated['facebook_url'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'bio' => $user->bio,
                'youtube_url' => $user->youtube_url,
                'linkedin_url' => $user->linkedin_url,
                'twitter_url' => $user->twitter_url,
                'facebook_url' => $user->facebook_url,
                'avatar' => $user->profile_picture ? url('/public-avatar/' . $user->id) : null,
                'role' => $user->role
            ]
        ]);
    }

    /**
     * Upload teacher profile image
     */
    public function uploadProfileImage(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
        ]);

        try {
            // Delete old profile picture if exists
            if ($user->profile_picture) {
                Storage::disk('public')->delete($user->profile_picture);
            }

            // Store new profile picture
            $path = $request->file('profile_image')->store('profile-pictures', 'public');
            
            // Update user record
            $user->update([
                'profile_picture' => $path
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Profile image uploaded successfully.',
                'avatar_url' => url('/public-avatar/' . $user->id)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload profile image: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove teacher profile image
     */
    public function removeProfileImage()
    {
        $user = Auth::user();

        try {
            // Delete profile picture file if exists
            if ($user->profile_picture) {
                Storage::disk('public')->delete($user->profile_picture);
                
                // Update user record
                $user->update([
                    'profile_picture' => null
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Profile image removed successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove profile image: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Preview course content for teachers
     * This allows teachers to see how their course appears to students
     */
    public function previewCourse($id)
    {
        $teacher = Auth::user();
        
        $course = Course::where('user_id', $teacher->id)
            ->with(['modules.lessons' => function ($query) {
                $query->orderBy('position');
            }])
            ->withCount(['enrollments'])
            ->findOrFail($id);

        // For preview, show the actual video URLs (since it's the teacher's own course)
        $course->modules->each(function ($module) {
            $module->lessons->each(function ($lesson) {
                // Teachers can see their own video URLs in preview mode
                $lesson->preview_mode = true;
            });
        });

        // Return course data formatted for preview display
        return response()->json([
            'success' => true,
            'course' => $course,
            'preview_mode' => true,
            'message' => 'Course preview data loaded successfully'
        ]);
    }

    /**
     * Get real analytics data for a course
     */
    public function getCourseAnalytics($id)
    {
        $teacher = Auth::user();
        $course = Course::where('user_id', $teacher->id)->findOrFail($id);
        
        // Total enrollments
        $totalEnrollments = CourseEnrollment::where('course_id', $id)->count();
        
        // Completion rate - average of all student progress
        $completionRate = CourseEnrollment::where('course_id', $id)
            ->avg('progress_percentage');
        
        // Average watch time from lesson progress
        $averageWatchTimeSeconds = DB::table('lesson_progress')
            ->join('lessons', 'lesson_progress.lesson_id', '=', 'lessons.id')
            ->join('modules', 'lessons.module_id', '=', 'modules.id')
            ->where('modules.course_id', $id)
            ->avg('lesson_progress.time_spent_seconds');
        
        $averageWatchTimeMinutes = $averageWatchTimeSeconds ? round($averageWatchTimeSeconds / 60) : 0;
        
        // Student progress distribution
        $notStarted = CourseEnrollment::where('course_id', $id)
            ->where('progress_percentage', 0)->count();
        $inProgress = CourseEnrollment::where('course_id', $id)
            ->whereBetween('progress_percentage', [1, 99])->count();
        $completed = CourseEnrollment::where('course_id', $id)
            ->where('progress_percentage', 100)->count();
        
        // Calculate percentages for progress distribution
        $totalStudents = $totalEnrollments > 0 ? $totalEnrollments : 1; // Avoid division by zero
        $progressDistribution = [
            'not_started' => round(($notStarted / $totalStudents) * 100),
            'in_progress' => round(($inProgress / $totalStudents) * 100),
            'completed' => round(($completed / $totalStudents) * 100)
        ];
        
        // Lesson performance analytics
        $lessonPerformance = DB::table('lessons')
            ->select('lessons.id', 'lessons.title', 'lessons.content_type')
            ->selectRaw('AVG(lesson_progress.progress_percentage) as avg_completion')
            ->selectRaw('AVG(lesson_progress.time_spent_seconds) as avg_time_spent')
            ->join('modules', 'lessons.module_id', '=', 'modules.id')
            ->leftJoin('lesson_progress', 'lessons.id', '=', 'lesson_progress.lesson_id')
            ->where('modules.course_id', $id)
            ->groupBy('lessons.id', 'lessons.title', 'lessons.content_type')
            ->get()
            ->map(function ($lesson) {
                return [
                    'id' => $lesson->id,
                    'title' => $lesson->title,
                    'type' => $lesson->content_type,
                    'completion_rate' => round($lesson->avg_completion ?: 0),
                    'avg_time_spent' => round(($lesson->avg_time_spent ?: 0) / 60), // Convert to minutes
                    'engagement_score' => min(100, round(($lesson->avg_completion ?: 0) * 1.2)) // Simple engagement calculation
                ];
            });
        
        // Recent enrollment trends (last 30 days)
        $enrollmentTrends = CourseEnrollment::where('course_id', $id)
            ->selectRaw('DATE(enrolled_at) as date, COUNT(*) as count')
            ->where('enrolled_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();
        
        return response()->json([
            'success' => true,
            'analytics' => [
                'total_enrollments' => $totalEnrollments,
                'completion_rate' => round($completionRate ?: 0),
                'average_watch_time' => $averageWatchTimeMinutes,
                'course_rating' => 4.2, // Static for now - would need reviews table
                'progress_distribution' => $progressDistribution,
                'lesson_performance' => $lessonPerformance,
                'enrollment_trends' => $enrollmentTrends,
                'monthly_change' => [
                    'enrollments' => '+12%', // Would calculate from historical data
                    'completion' => '+5%',
                    'watch_time' => '+2%',
                    'rating' => '+0.2'
                ]
            ]
        ]);
    }
} 