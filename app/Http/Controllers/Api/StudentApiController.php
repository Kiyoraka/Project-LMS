<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\LessonProgress;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class StudentApiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('student');
    }

    /**
     * Get dashboard data for student
     */
    public function getDashboardData()
    {
        $student = Auth::user();
        
        // Get student's enrollments with course details
        $enrollments = CourseEnrollment::with(['course'])
            ->where('user_id', $student->id)
            ->get();
        
        // Calculate overall statistics
        $totalEnrollments = $enrollments->count();
        $completedCourses = $enrollments->where('status', 'completed')->count();
        $inProgressCourses = $enrollments->where('status', 'active')->count();
        
        // Calculate overall progress (average of all course progress)
        $overallProgress = $totalEnrollments > 0 
            ? round($enrollments->avg('progress_percentage'))
            : 0;
        
        // Calculate learning streak based on recent lesson progress activity
        $streak = $this->calculateLearningStreak($student->id);
        
        // Get recent course enrollments for "My Courses" section
        $recentEnrollments = $enrollments->take(3)->map(function ($enrollment) {
            return [
                'id' => $enrollment->id,
                'course' => [
                    'id' => $enrollment->course->id,
                    'title' => $enrollment->course->title,
                    'thumbnail' => $enrollment->course->thumbnail ? asset('storage/' . $enrollment->course->thumbnail) : null,
                    'level' => $enrollment->course->level ?? 'Beginner'
                ],
                'status' => $enrollment->status,
                'progress_percentage' => $enrollment->progress_percentage
            ];
        });
        
        // Get recent activity (lesson progress)
        $recentActivity = LessonProgress::with(['lesson.module.course'])
            ->where('user_id', $student->id)
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($progress) {
                return [
                    'id' => $progress->id,
                    'lesson' => [
                        'id' => $progress->lesson->id,
                        'title' => $progress->lesson->title,
                        'module' => [
                            'course' => [
                                'title' => $progress->lesson->module->course->title
                            ]
                        ]
                    ],
                    'status' => $progress->is_completed ? 'completed' : 'in_progress',
                    'updated_at' => $progress->updated_at->toISOString()
                ];
            });
        
        // Note: Upcoming deadlines removed from frontend, no longer needed
        
        // Calculate dynamic achievements based on student's actual progress
        $achievements = $this->calculateAchievements($student->id, $completedCourses, $streak);
        
        return response()->json([
            'overallProgress' => $overallProgress,
            'completedCourses' => $completedCourses,
            'inProgressCourses' => $inProgressCourses,
            'streak' => $streak,
            'achievements' => $achievements,
            'enrollments' => $recentEnrollments,
            'recentActivity' => $recentActivity
        ]);
    }

    /**
     * Get student's course enrollments
     */
    public function getEnrollments()
    {
        $student = Auth::user();
        
        $enrollments = CourseEnrollment::with(['course'])
            ->where('user_id', $student->id)
            ->orderBy('enrolled_at', 'desc')
            ->get()
            ->map(function ($enrollment) {
                return [
                    'id' => $enrollment->id,
                    'course' => [
                        'id' => $enrollment->course->id,
                        'title' => $enrollment->course->title,
                        'thumbnail' => $enrollment->course->thumbnail ? asset('storage/' . $enrollment->course->thumbnail) : null,
                        'level' => $enrollment->course->level ?? 'Beginner',
                        'short_description' => $enrollment->course->short_description,
                        'duration_hours' => $enrollment->course->duration_hours ?? 0
                    ],
                    'status' => $enrollment->status,
                    'enrolled_at' => $enrollment->enrolled_at->toISOString(),
                    'completed_at' => $enrollment->completed_at ? $enrollment->completed_at->toISOString() : null,
                    'progress_percentage' => $enrollment->progress_percentage
                ];
            });
        
        return response()->json($enrollments);
    }

    /**
     * Get all available courses for browsing
     */
    public function getBrowseCourses(Request $request)
    {
        $student = Auth::user();
        
        // Get all published courses with their details
        $query = Course::where('status', 'published')
            ->withCount(['enrollments', 'modules', 'lessons'])
            ->with('teacher');
        
        // Apply search filter
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', '%' . $searchTerm . '%')
                  ->orWhere('short_description', 'like', '%' . $searchTerm . '%')
                  ->orWhere('category', 'like', '%' . $searchTerm . '%');
            });
        }
        
        // Apply category filter
        if ($request->filled('category') && $request->category !== '') {
            $query->where('category', $request->category);
        }
        
        // Apply level filter
        if ($request->filled('level') && $request->level !== '') {
            $query->where('level', $request->level);
        }
        
        // Apply sorting
        switch ($request->get('sort_by', 'newest')) {
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'popular':
                $query->orderBy('enrollments_count', 'desc');
                break;
            case 'rating':
                $query->orderBy('rating', 'desc');
                break;
            case 'duration':
                $query->orderBy('duration_hours', 'asc');
                break;
        }
        
        $courses = $query->paginate(15);
        
        // Get student's enrolled course IDs
        $enrolledCourseIds = CourseEnrollment::where('user_id', $student->id)
            ->pluck('course_id')
            ->toArray();
        
        // Format the courses data
        $courses->getCollection()->transform(function ($course) use ($enrolledCourseIds) {
            return [
                'id' => $course->id,
                'title' => $course->title,
                'short_description' => $course->short_description,
                'category' => $course->category,
                'level' => $course->level ?? 'Beginner',
                'duration_hours' => $course->duration_hours ?? 0,
                'price' => $course->price ?? 0,
                'rating' => $course->rating ?? 0,
                'students_count' => $course->enrollments_count,
                'modules_count' => $course->modules_count,
                'lessons_count' => $course->lessons_count,
                'instructor' => $course->teacher ? $course->teacher->name : 'Unknown',
                'thumbnail' => $course->thumbnail ? asset('storage/' . $course->thumbnail) : null,
                'is_featured' => $course->featured ?? false,
                'is_enrolled' => in_array($course->id, $enrolledCourseIds),
                'created_at' => $course->created_at->toISOString()
            ];
        });
        
        return response()->json($courses);
    }

    /**
     * Get featured courses (top 5 most popular by enrollment count)
     */
    public function getFeaturedCourses()
    {
        $student = Auth::user();
        
        $courses = Course::where('status', 'published')
            ->withCount(['enrollments', 'modules', 'lessons'])
            ->with('teacher')
            ->orderBy('enrollments_count', 'desc')
            ->take(5)
            ->get();
        
        // Get student's enrolled course IDs
        $enrolledCourseIds = CourseEnrollment::where('user_id', $student->id)
            ->pluck('course_id')
            ->toArray();
        
        $featuredCourses = $courses->map(function ($course) use ($enrolledCourseIds) {
            return [
                'id' => $course->id,
                'title' => $course->title,
                'short_description' => $course->short_description,
                'category' => $course->category,
                'level' => $course->level ?? 'Beginner',
                'duration_hours' => $course->duration_hours ?? 0,
                'price' => $course->price ?? 0,
                'rating' => $course->rating ?? 0,
                'students_count' => $course->enrollments_count,
                'modules_count' => $course->modules_count,
                'lessons_count' => $course->lessons_count,
                'instructor' => $course->teacher ? $course->teacher->name : 'Unknown',
                'thumbnail' => $course->thumbnail ? asset('storage/' . $course->thumbnail) : null,
                'is_featured' => true,
                'is_enrolled' => in_array($course->id, $enrolledCourseIds),
                'created_at' => $course->created_at->toISOString()
            ];
        });
        
        return response()->json($featuredCourses);
    }

    /**
     * Enroll student in a course
     */
    public function enrollInCourse(Request $request, $courseId)
    {
        $student = Auth::user();
        
        $course = Course::where('status', 'published')->findOrFail($courseId);
        
        // Check if already enrolled
        $existingEnrollment = CourseEnrollment::where('user_id', $student->id)
            ->where('course_id', $courseId)
            ->first();
        
        if ($existingEnrollment) {
            return response()->json([
                'success' => false,
                'message' => 'You are already enrolled in this course.'
            ], 400);
        }
        
        // Create enrollment
        $enrollment = CourseEnrollment::create([
            'user_id' => $student->id,
            'course_id' => $courseId,
            'enrolled_at' => now(),
            'status' => 'active',
            'progress_percentage' => 0
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Successfully enrolled in the course!',
            'enrollment' => $enrollment
        ]);
    }

    /**
     * Get student's certificates
     */
    public function getCertificates(Request $request)
    {
        $student = Auth::user();
        
        // Get completed course enrollments (these represent certificates)
        $query = CourseEnrollment::with(['course.teacher'])
            ->where('user_id', $student->id)
            ->where('status', 'completed')
            ->whereNotNull('completed_at');
        
        // Apply search filter
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->whereHas('course', function ($q) use ($searchTerm) {
                $q->where('title', 'like', '%' . $searchTerm . '%')
                  ->orWhere('category', 'like', '%' . $searchTerm . '%');
            });
        }
        
        // Apply category filter
        if ($request->filled('category') && $request->category !== '') {
            $query->whereHas('course', function ($q) use ($request) {
                $q->where('category', $request->category);
            });
        }
        
        // Apply sorting
        switch ($request->get('sort_by', 'newest')) {
            case 'newest':
                $query->orderBy('completed_at', 'desc');
                break;
            case 'oldest':
                $query->orderBy('completed_at', 'asc');
                break;
            case 'course':
                $query->join('courses', 'course_enrollments.course_id', '=', 'courses.id')
                      ->orderBy('courses.title', 'asc');
                break;
            case 'score':
                // Calculate final score based on lesson progress
                $query->orderByDesc('progress_percentage');
                break;
            default:
                $query->orderBy('completed_at', 'desc');
                break;
        }
        
        $certificates = $query->paginate(6);
        
        // Calculate final scores for each certificate
        $certificates->getCollection()->transform(function ($enrollment) {
            // Get average score from lesson progress for this enrollment
            $averageScore = LessonProgress::where('user_id', $enrollment->user_id)
                ->whereHas('lesson.module.course', function ($q) use ($enrollment) {
                    $q->where('id', $enrollment->course_id);
                })
                ->whereNotNull('score')
                ->avg('score');
            
            $finalScore = $averageScore ? round($averageScore) : $enrollment->progress_percentage;
            
            return [
                'id' => $enrollment->id,
                'course' => [
                    'id' => $enrollment->course->id,
                    'title' => $enrollment->course->title,
                    'category' => $enrollment->course->category,
                    'level' => $enrollment->course->level ?? 'Beginner',
                    'duration_hours' => $enrollment->course->duration_hours ?? 0,
                    'instructor' => $enrollment->course->teacher ? $enrollment->course->teacher->name : 'Unknown'
                ],
                'completed_at' => $enrollment->completed_at->toISOString(),
                'final_score' => $finalScore,
                'certificate_url' => "/certificates/{$enrollment->id}/download"
            ];
        });
        
        return response()->json($certificates);
    }

    /**
     * Get certificate statistics
     */
    public function getCertificateStats()
    {
        $student = Auth::user();
        
        // Get all completed enrollments for certificates
        $completedEnrollments = CourseEnrollment::with('course')
            ->where('user_id', $student->id)
            ->where('status', 'completed')
            ->whereNotNull('completed_at')
            ->get();
        
        $totalCertificates = $completedEnrollments->count();
        
        // Certificates earned this month
        $certificatesThisMonth = $completedEnrollments->filter(function ($enrollment) {
            return $enrollment->completed_at->isCurrentMonth();
        })->count();
        
        // Calculate average score across all certificates
        $averageScore = 0;
        if ($totalCertificates > 0) {
            $totalScore = 0;
            $scoreCount = 0;
            
            foreach ($completedEnrollments as $enrollment) {
                // Get average score from lesson progress
                $enrollmentScore = LessonProgress::where('user_id', $enrollment->user_id)
                    ->whereHas('lesson.module.course', function ($q) use ($enrollment) {
                        $q->where('id', $enrollment->course_id);
                    })
                    ->whereNotNull('score')
                    ->avg('score');
                
                if ($enrollmentScore) {
                    $totalScore += $enrollmentScore;
                    $scoreCount++;
                } else {
                    // Fallback to progress percentage
                    $totalScore += $enrollment->progress_percentage;
                    $scoreCount++;
                }
            }
            
            $averageScore = $scoreCount > 0 ? round($totalScore / $scoreCount) : 0;
        }
        
        // Total hours completed (sum of course durations)
        $totalHours = $completedEnrollments->sum(function ($enrollment) {
            return $enrollment->course->duration_hours ?? 0;
        });
        
        return response()->json([
            'total_certificates' => $totalCertificates,
            'certificates_this_month' => $certificatesThisMonth,
            'average_score' => $averageScore,
            'total_hours' => $totalHours
        ]);
    }

    /**
     * View certificate in HTML format
     */
    public function viewCertificate($enrollmentId)
    {
        $student = Auth::user();
        
        $enrollment = CourseEnrollment::with(['course.teacher'])
            ->where('user_id', $student->id)
            ->where('id', $enrollmentId)
            ->where('status', 'completed')
            ->whereNotNull('completed_at')
            ->firstOrFail();
        
        // Calculate final score from lesson progress
        $averageScore = LessonProgress::where('user_id', $enrollment->user_id)
            ->whereHas('lesson.module.course', function ($q) use ($enrollment) {
                $q->where('id', $enrollment->course_id);
            })
            ->whereNotNull('score')
            ->avg('score');
        
        $finalScore = $averageScore ? round($averageScore) : $enrollment->progress_percentage;
        
        return view('certificates.template', [
            'student' => $student,
            'course' => $enrollment->course,
            'enrollment' => $enrollment,
            'finalScore' => $finalScore,
            'completionDate' => $enrollment->completed_at->format('F j, Y')
        ]);
    }

    /**
     * Download certificate PDF (returns certificate URL for frontend to handle)
     */
    public function downloadCertificate($enrollmentId)
    {
        $student = Auth::user();
        
        $enrollment = CourseEnrollment::with(['course.teacher'])
            ->where('user_id', $student->id)
            ->where('id', $enrollmentId)
            ->where('status', 'completed')
            ->whereNotNull('completed_at')
            ->firstOrFail();
        
        // Return the certificate view URL for frontend to handle PDF generation
        return response()->json([
            'success' => true,
            'message' => 'Certificate ready for download',
            'certificate_url' => "/student/certificates/{$enrollmentId}/view",
            'certificate' => [
                'course_title' => $enrollment->course->title,
                'student_name' => $student->name,
                'completion_date' => $enrollment->completed_at->format('F j, Y'),
                'instructor' => $enrollment->course->teacher ? $enrollment->course->teacher->name : 'Unknown'
            ]
        ]);
    }

    /**
     * Calculate learning streak based on recent activity
     */
    private function calculateLearningStreak($studentId)
    {
        // Get recent lesson progress activity, grouped by date
        $recentActivity = LessonProgress::where('user_id', $studentId)
            ->where('updated_at', '>=', now()->subDays(30)) // Look at last 30 days
            ->orderBy('updated_at', 'desc')
            ->get()
            ->groupBy(function ($item) {
                return $item->updated_at->format('Y-m-d');
            });

        if ($recentActivity->isEmpty()) {
            return 0;
        }

        $streak = 0;
        $currentDate = now();

        // Check consecutive days of activity, starting from today
        for ($i = 0; $i < 30; $i++) {
            $dateToCheck = $currentDate->copy()->subDays($i)->format('Y-m-d');
            
            if ($recentActivity->has($dateToCheck)) {
                $streak++;
            } else {
                // If we're checking today or yesterday and no activity, continue checking
                // But if we already have a streak and hit a gap, break
                if ($streak > 0) {
                    break;
                }
            }
        }

        return $streak;
    }

    /**
     * Calculate achievements based on student progress
     * Returns achievements in the format expected by the frontend
     */
    private function calculateAchievements($studentId, $completedCourses, $streak)
    {
        $achievements = [];

        // Get additional data for achievement calculations
        $enrollments = CourseEnrollment::where('user_id', $studentId)->get();
        $totalEnrollments = $enrollments->count();
        
        // Get lesson progress data
        $lessonProgressCount = LessonProgress::where('user_id', $studentId)->count();
        $completedLessons = LessonProgress::where('user_id', $studentId)
            ->where('is_completed', true)
            ->count();

                 // Check for early bird (lessons completed before 6 AM)
         $earlyBirdExists = false;
         try {
             $earlyBirdExists = LessonProgress::where('user_id', $studentId)
                 ->whereTime('updated_at', '<', '06:00:00')
                 ->exists();
         } catch (\Exception $e) {
             // Fallback to false if query fails
         }

                 // Check for night owl (lessons completed after 11 PM) - simplified check
         $nightOwlStreak = 0;
         try {
             $nightOwlStreak = LessonProgress::where('user_id', $studentId)
                 ->whereTime('updated_at', '>', '23:00:00')
                 ->whereBetween('updated_at', [now()->subDays(7), now()])
                 ->count();
         } catch (\Exception $e) {
             // Fallback to 0 if query fails
         }

         // Check for weekend learning - simplified check
         $weekendLessons = 0;
         try {
             $weekendLessons = LessonProgress::where('user_id', $studentId)
                 ->whereRaw('WEEKDAY(updated_at) IN (5, 6)') // Saturday = 5, Sunday = 6 in WEEKDAY
                 ->count();
         } catch (\Exception $e) {
             // Fallback to 0 if query fails
         }

         // Check for speed demon (5+ lessons in one day) - simplified check
         $speedDemonAchieved = false;
         try {
             $maxLessonsInDay = LessonProgress::where('user_id', $studentId)
                 ->selectRaw('DATE(updated_at) as date, COUNT(*) as lesson_count')
                 ->groupBy('date')
                 ->orderByRaw('COUNT(*) DESC')
                 ->first();
             
             $speedDemonAchieved = $maxLessonsInDay && $maxLessonsInDay->lesson_count >= 5;
         } catch (\Exception $e) {
             // Fallback to false if query fails
         }

                 // Check for different categories - simplified check
         $categoriesCount = 0;
         try {
             $categoriesCount = CourseEnrollment::where('user_id', $studentId)
                 ->join('courses', 'course_enrollments.course_id', '=', 'courses.id')
                 ->distinct('courses.category')
                 ->count('courses.category');
         } catch (\Exception $e) {
             // Fallback to 0 if query fails
         }

        // Define when each achievement was first earned (simplified logic)
        $firstCourseDate = CourseEnrollment::where('user_id', $studentId)
            ->where('status', 'completed')
            ->orderBy('completed_at')
            ->first();

        $achievements = [
            // Course Completion Milestones
            [
                'id' => 'first_steps',
                'achieved' => $completedCourses >= 1,
                'earned_at' => $completedCourses >= 1 && $firstCourseDate ? $firstCourseDate->completed_at->toISOString() : null
            ],
            [
                'id' => 'getting_started',
                'achieved' => $completedCourses >= 5,
                'earned_at' => $completedCourses >= 5 ? $this->getAchievementDate($studentId, 5) : null
            ],
            [
                'id' => 'dedicated_learner',
                'achieved' => $completedCourses >= 10,
                'earned_at' => $completedCourses >= 10 ? $this->getAchievementDate($studentId, 10) : null
            ],
            [
                'id' => 'knowledge_collector',
                'achieved' => $completedCourses >= 25,
                'earned_at' => $completedCourses >= 25 ? $this->getAchievementDate($studentId, 25) : null
            ],
            [
                'id' => 'learning_enthusiast',
                'achieved' => $completedCourses >= 50,
                'earned_at' => $completedCourses >= 50 ? $this->getAchievementDate($studentId, 50) : null
            ],
            [
                'id' => 'century_scholar',
                'achieved' => $completedCourses >= 100,
                'earned_at' => $completedCourses >= 100 ? $this->getAchievementDate($studentId, 100) : null
            ],
            [
                'id' => 'knowledge_master',
                'achieved' => $completedCourses >= 500,
                'earned_at' => $completedCourses >= 500 ? $this->getAchievementDate($studentId, 500) : null
            ],
            [
                'id' => 'learning_legend',
                'achieved' => $completedCourses >= 1000,
                'earned_at' => $completedCourses >= 1000 ? $this->getAchievementDate($studentId, 1000) : null
            ],
            [
                'id' => 'ultimate_scholar',
                'achieved' => $completedCourses >= 10000,
                'earned_at' => $completedCourses >= 10000 ? $this->getAchievementDate($studentId, 10000) : null
            ],

            // Learning Streak Achievements
            [
                'id' => 'week_warrior',
                'achieved' => $streak >= 7,
                'earned_at' => $streak >= 7 ? now()->subDays($streak - 7)->toISOString() : null
            ],
            [
                'id' => 'monthly_champion',
                'achieved' => $streak >= 30,
                'earned_at' => $streak >= 30 ? now()->subDays($streak - 30)->toISOString() : null
            ],
            [
                'id' => 'hundred_days_hero',
                'achieved' => $streak >= 100,
                'earned_at' => $streak >= 100 ? now()->subDays($streak - 100)->toISOString() : null
            ],
            [
                'id' => 'year_long_learner',
                'achieved' => $streak >= 365,
                'earned_at' => $streak >= 365 ? now()->subDays($streak - 365)->toISOString() : null
            ],

            // Speed & Efficiency Achievements
            [
                'id' => 'speed_demon',
                'achieved' => $speedDemonAchieved,
                'earned_at' => $speedDemonAchieved && $maxLessonsInDay ? now()->toISOString() : null
            ],
            [
                'id' => 'marathon_learner',
                'achieved' => false, // This would need time tracking
                'earned_at' => null
            ],
            [
                'id' => 'quick_finisher',
                'achieved' => $this->hasQuickCompletion($studentId),
                'earned_at' => $this->hasQuickCompletion($studentId) ? $this->getQuickCompletionDate($studentId) : null
            ],

            // Perfect Performance Achievements
            [
                'id' => 'perfectionist',
                'achieved' => $this->hasPerfectStreak($studentId, 10),
                'earned_at' => $this->hasPerfectStreak($studentId, 10) ? now()->toISOString() : null
            ],
            [
                'id' => 'flawless_execution',
                'achieved' => $this->hasPerfectAttendance($studentId, 25),
                'earned_at' => $this->hasPerfectAttendance($studentId, 25) ? now()->toISOString() : null
            ],
            [
                'id' => 'excellence_pursuer',
                'achieved' => $this->hasHighAverageProgress($studentId),
                'earned_at' => $this->hasHighAverageProgress($studentId) ? now()->toISOString() : null
            ],

            // Special Timing Achievements
                         [
                 'id' => 'early_bird',
                 'achieved' => $earlyBirdExists,
                 'earned_at' => $earlyBirdExists ? $this->getEarlyBirdDate($studentId) : null
             ],
            [
                'id' => 'night_owl',
                'achieved' => $nightOwlStreak >= 7,
                'earned_at' => $nightOwlStreak >= 7 ? now()->toISOString() : null
            ],
            [
                'id' => 'weekend_warrior',
                'achieved' => $weekendLessons >= 20,
                'earned_at' => $weekendLessons >= 20 ? now()->toISOString() : null
            ],

            // Community & Engagement
            [
                'id' => 'certificate_collector',
                'achieved' => $completedCourses >= 10,
                'earned_at' => $completedCourses >= 10 ? $this->getAchievementDate($studentId, 10) : null
            ],
            [
                'id' => 'curious_explorer',
                'achieved' => $categoriesCount >= 5,
                'earned_at' => $categoriesCount >= 5 ? now()->toISOString() : null
            ],
            [
                'id' => 'learning_addict',
                'achieved' => $totalEnrollments >= 20,
                'earned_at' => $totalEnrollments >= 20 ? now()->toISOString() : null
            ]
        ];

        return $achievements;
    }

         /**
      * Get the date when a course completion milestone was achieved
      */
     private function getAchievementDate($studentId, $courseNumber)
     {
         try {
             $enrollment = CourseEnrollment::where('user_id', $studentId)
                 ->where('status', 'completed')
                 ->orderBy('completed_at')
                 ->skip($courseNumber - 1)
                 ->first();
             
             return $enrollment ? $enrollment->completed_at->toISOString() : now()->toISOString();
         } catch (\Exception $e) {
             return now()->toISOString(); // Fallback if query fails
         }
     }

         /**
      * Check if user has quick completion (course completed in under 2 hours)
      */
     private function hasQuickCompletion($studentId)
     {
         try {
             return CourseEnrollment::where('user_id', $studentId)
                 ->where('status', 'completed')
                 ->whereNotNull('completed_at')
                 ->whereRaw('TIMESTAMPDIFF(HOUR, enrolled_at, completed_at) < 2')
                 ->exists();
         } catch (\Exception $e) {
             return false; // Fallback if query fails
         }
     }

     /**
      * Get date of first quick completion
      */
     private function getQuickCompletionDate($studentId)
     {
         try {
             $enrollment = CourseEnrollment::where('user_id', $studentId)
                 ->where('status', 'completed')
                 ->whereNotNull('completed_at')
                 ->whereRaw('TIMESTAMPDIFF(HOUR, enrolled_at, completed_at) < 2')
                 ->orderBy('completed_at')
                 ->first();
             
             return $enrollment ? $enrollment->completed_at->toISOString() : now()->toISOString();
         } catch (\Exception $e) {
             return now()->toISOString(); // Fallback if query fails
         }
     }

         /**
      * Check if user has perfect completion streak
      */
     private function hasPerfectStreak($studentId, $count)
     {
         try {
             $recentCompletions = CourseEnrollment::where('user_id', $studentId)
                 ->where('status', 'completed')
                 ->where('progress_percentage', 100)
                 ->orderBy('completed_at', 'desc')
                 ->take($count)
                 ->count();
             
             return $recentCompletions >= $count;
         } catch (\Exception $e) {
             return false; // Fallback if query fails
         }
     }

     /**
      * Check if user has perfect attendance
      */
     private function hasPerfectAttendance($studentId, $count)
     {
         try {
             // This is a simplified check - in a real system you'd track attendance
             return CourseEnrollment::where('user_id', $studentId)
                 ->where('status', 'completed')
                 ->where('progress_percentage', 100)
                 ->count() >= $count;
         } catch (\Exception $e) {
             return false; // Fallback if query fails
         }
     }

     /**
      * Check if user has high average progress
      */
     private function hasHighAverageProgress($studentId)
     {
         try {
             $averageProgress = CourseEnrollment::where('user_id', $studentId)
                 ->avg('progress_percentage');
             
             return $averageProgress >= 95;
         } catch (\Exception $e) {
             return false; // Fallback if query fails
         }
     }

     /**
      * Get the date when early bird achievement was earned
      */
     private function getEarlyBirdDate($studentId)
     {
         try {
             $lesson = LessonProgress::where('user_id', $studentId)
                 ->whereTime('updated_at', '<', '06:00:00')
                 ->orderBy('updated_at')
                 ->first();
             
             return $lesson ? $lesson->updated_at->toISOString() : now()->toISOString();
         } catch (\Exception $e) {
             return now()->toISOString(); // Fallback if query fails
         }
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
     * Update student profile information
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // Update user profile
        $user->update([
            'name' => $validated['name']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->profile_picture ? url('/public-avatar/' . $user->id) : null,
                'role' => $user->role
            ]
        ]);
    }

    /**
     * Upload student profile image
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
                \Storage::disk('public')->delete($user->profile_picture);
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
     * Remove student profile image
     */
    public function removeProfileImage()
    {
        $user = Auth::user();

        try {
            // Delete profile picture file if exists
            if ($user->profile_picture) {
                \Storage::disk('public')->delete($user->profile_picture);
                
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
     * Get lesson progress for a student
     */
    public function getLessonProgress($lessonId)
    {
        $student = Auth::user();
        
        // Find enrollment for this lesson's course
        $enrollment = CourseEnrollment::whereHas('course.modules.lessons', function ($query) use ($lessonId) {
            $query->where('lessons.id', $lessonId);
        })
        ->where('user_id', $student->id)
        ->first();

        if (!$enrollment) {
            return response()->json([
                'message' => 'You are not enrolled in this course.'
            ], 403);
        }

        // Get existing progress for this lesson
        $progress = LessonProgress::where('user_id', $student->id)
            ->where('lesson_id', $lessonId)
            ->where('course_enrollment_id', $enrollment->id)
            ->first();

        if (!$progress) {
            return response()->json([
                'time_spent_seconds' => 0,
                'progress_percentage' => 0,
                'is_completed' => false,
                'lesson_duration' => 300 // Default 5 minutes
            ]);
        }

        return response()->json([
            'time_spent_seconds' => $progress->time_spent_seconds,
            'progress_percentage' => $progress->progress_percentage,
            'is_completed' => $progress->is_completed,
            'lesson_duration' => 300 // You can get this from lesson data if needed
        ]);
    }

    /**
     * Get course details for learning (enrolled students only)
     */
    public function getCourseForLearning($courseId)
    {
        $student = Auth::user();
        
        // Check if student is enrolled in this course
        $enrollment = CourseEnrollment::with(['course.modules.lessons'])
            ->where('user_id', $student->id)
            ->where('course_id', $courseId)
            ->first();

        if (!$enrollment) {
            return response()->json([
                'message' => 'You are not enrolled in this course.'
            ], 403);
        }

        // Get lesson progress for this student
        $lessonProgress = LessonProgress::where('user_id', $student->id)
            ->where('course_enrollment_id', $enrollment->id)
            ->get()
            ->keyBy('lesson_id');

        // Format course data with progress
        $course = $enrollment->course;
        $courseData = [
            'id' => $course->id,
            'title' => $course->title,
            'short_description' => $course->short_description,
            'level' => $course->level ?? 'Beginner',
            'duration_hours' => $course->duration_hours ?? 0,
            'enrollment_progress' => $enrollment->progress_percentage,
            'modules' => []
        ];

        // Add modules and lessons with progress
        foreach ($course->modules as $module) {
            $moduleData = [
                'id' => $module->id,
                'title' => $module->title,
                'description' => $module->description,
                'lessons' => []
            ];

            foreach ($module->lessons as $lesson) {
                $progress = $lessonProgress->get($lesson->id);
                
                $lessonData = [
                    'id' => $lesson->id,
                    'title' => $lesson->title,
                    'description' => $lesson->description,
                    'type' => $lesson->type ?? 'video',
                    'duration_minutes' => $lesson->duration_minutes ?? 15,
                    'video_url' => $lesson->video_url,
                    'content' => $lesson->content,
                    'completed' => $progress ? $progress->is_completed : false,
                    'progress_percentage' => $progress ? $progress->progress_percentage : 0,
                    'time_spent_seconds' => $progress ? $progress->time_spent_seconds : 0
                ];

                $moduleData['lessons'][] = $lessonData;
            }

            $courseData['modules'][] = $moduleData;
        }

        return response()->json($courseData);
    }

    /**
     * Save lesson progress (called periodically while watching)
     */
    public function saveLessonProgress(Request $request, $lessonId)
    {
        $student = Auth::user();
        
        $validated = $request->validate([
            'time_spent_seconds' => 'required|integer|min:0',
            'progress_percentage' => 'required|integer|min:0|max:100'
        ]);

        // Find enrollment for this lesson's course
        $enrollment = CourseEnrollment::whereHas('course.modules.lessons', function ($query) use ($lessonId) {
            $query->where('lessons.id', $lessonId);
        })
        ->where('user_id', $student->id)
        ->first();

        if (!$enrollment) {
            return response()->json([
                'message' => 'You are not enrolled in this course.'
            ], 403);
        }

        // Check if progress record already exists
        $existingProgress = LessonProgress::where('user_id', $student->id)
            ->where('lesson_id', $lessonId)
            ->where('course_enrollment_id', $enrollment->id)
            ->first();

        if ($existingProgress) {
            // Update existing progress
            $existingProgress->update([
                'time_spent_seconds' => $validated['time_spent_seconds'],
                'progress_percentage' => $validated['progress_percentage']
            ]);
            $progress = $existingProgress;
        } else {
            // Create new progress record
            $progress = LessonProgress::create([
                'user_id' => $student->id,
                'lesson_id' => $lessonId,
                'course_enrollment_id' => $enrollment->id,
                'time_spent_seconds' => $validated['time_spent_seconds'],
                'progress_percentage' => $validated['progress_percentage'],
                'started_at' => now()
            ]);
        }

        return response()->json([
            'success' => true,
            'progress' => $progress
        ]);
    }

    /**
     * Mark lesson as completed (called when 90%+ watched)
     */
    public function completeLesson(Request $request, $lessonId)
    {
        $student = Auth::user();
        
        $validated = $request->validate([
            'time_spent_seconds' => 'required|integer|min:0',
            'completed' => 'required|boolean'
        ]);

        // Find enrollment for this lesson's course
        $enrollment = CourseEnrollment::whereHas('course.modules.lessons', function ($query) use ($lessonId) {
            $query->where('lessons.id', $lessonId);
        })
        ->where('user_id', $student->id)
        ->first();

        if (!$enrollment) {
            return response()->json([
                'message' => 'You are not enrolled in this course.'
            ], 403);
        }

        // Check if progress record already exists
        $existingProgress = LessonProgress::where('user_id', $student->id)
            ->where('lesson_id', $lessonId)
            ->where('course_enrollment_id', $enrollment->id)
            ->first();

        if ($existingProgress) {
            // Update existing progress to completed
            $existingProgress->update([
                'is_completed' => true,
                'progress_percentage' => 100,
                'time_spent_seconds' => $validated['time_spent_seconds'],
                'completed_at' => now()
            ]);
            $progress = $existingProgress;
        } else {
            // Create new completed progress record
            $progress = LessonProgress::create([
                'user_id' => $student->id,
                'lesson_id' => $lessonId,
                'course_enrollment_id' => $enrollment->id,
                'is_completed' => true,
                'progress_percentage' => 100,
                'time_spent_seconds' => $validated['time_spent_seconds'],
                'completed_at' => now(),
                'started_at' => now()
            ]);
        }

        // Update course enrollment progress
        $this->updateCourseProgress($enrollment);

        return response()->json([
            'success' => true,
            'message' => 'Lesson completed successfully!',
            'progress' => $progress
        ]);
    }

    /**
     * Update overall course progress based on completed lessons
     */
    private function updateCourseProgress($enrollment)
    {
        // Get total lessons in this course
        $totalLessons = \App\Models\Lesson::whereHas('module', function ($query) use ($enrollment) {
            $query->where('course_id', $enrollment->course_id);
        })->count();

        if ($totalLessons === 0) {
            return;
        }

        // Get completed lessons for this student in this course
        $completedLessons = LessonProgress::where('user_id', $enrollment->user_id)
            ->where('course_enrollment_id', $enrollment->id)
            ->where('is_completed', true)
            ->count();

        // Calculate overall course progress percentage
        $overallProgress = round(($completedLessons / $totalLessons) * 100);

        // Update enrollment progress
        $enrollment->update([
            'progress_percentage' => $overallProgress,
            'status' => $overallProgress >= 100 ? 'completed' : 'active',
            'completed_at' => $overallProgress >= 100 ? now() : null
        ]);
    }
} 