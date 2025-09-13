<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\View\View;

class StudentController extends Controller
{
    /**
     * Serve the student dashboard SPA (Single Page Application).
     *
     * @return \Illuminate\View\View
     */
    public function spa(): View
    {
        return view('student.student');
    }
    /**
     * Display the student dashboard
     */
    public function dashboard()
    {
        $user = Auth::user();
        
        // Get enrolled courses with progress
        $enrollments = CourseEnrollment::where('student_id', $user->id)
            ->with(['course', 'lessonProgress'])
            ->get();
        
        // Calculate overall progress
        $overallProgress = 0;
        $totalCourses = $enrollments->count();
        
        if ($totalCourses > 0) {
            $totalProgress = $enrollments->sum('progress_percentage');
            $overallProgress = round($totalProgress / $totalCourses);
        }
        
        // Get recent activity
        $recentActivity = LessonProgress::where('student_id', $user->id)
            ->with(['lesson.module.course'])
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();
        
        // Get upcoming deadlines
        $upcomingDeadlines = CourseEnrollment::where('student_id', $user->id)
            ->where('status', 'active')
            ->whereNotNull('course_deadline')
            ->where('course_deadline', '>', now())
            ->with('course')
            ->orderBy('course_deadline', 'asc')
            ->take(3)
            ->get();
        
        // Calculate learning streak
        $streak = $this->calculateLearningStreak($user->id);
        
        // Get achievements
        $achievements = $this->getAchievements($user->id);
        
        // Get course completion counts
        $completedCourses = $enrollments->where('status', 'completed')->count();
        $inProgressCourses = $enrollments->where('status', 'active')->count();
        
        return view('student.dashboard', compact(
            'user',
            'enrollments',
            'overallProgress',
            'recentActivity',
            'upcomingDeadlines',
            'streak',
            'achievements',
            'completedCourses',
            'inProgressCourses'
        ));
    }
    
    /**
     * Display available courses catalog
     */
    public function browseCourses(Request $request)
    {
        $user = Auth::user();
        
        $query = Course::where('status', 'published');
        
        // Apply category filter
        if ($request->has('category') && $request->category) {
            $query->where('category_id', $request->category);
        }
        
        // Apply level filter
        if ($request->has('level') && $request->level) {
            $query->where('level', $request->level);
        }
        
        // Apply price filter
        if ($request->has('price')) {
            if ($request->price === 'free') {
                $query->where('price', 0);
            } elseif ($request->price === 'paid') {
                $query->where('price', '>', 0);
            }
        }
        
        // Apply search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('short_description', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // Apply sorting
        if ($request->has('sort')) {
            switch($request->sort) {
                case 'newest':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'price_low':
                    $query->orderBy('price', 'asc');
                    break;
                case 'price_high':
                    $query->orderBy('price', 'desc');
                    break;
                case 'popular':
                    $query->withCount('enrollments')->orderBy('enrollments_count', 'desc');
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }
        
        $courses = $query->with(['category', 'teacher'])->paginate(12);
        
        // Get enrolled course IDs for the current user
        $enrolledCourseIds = CourseEnrollment::where('student_id', $user->id)
            ->pluck('course_id')
            ->toArray();
        
        $categories = DB::table('categories')->get();
        
        return view('student.courses.browse', compact('courses', 'enrolledCourseIds', 'categories'));
    }
    
    /**
     * Display course details
     */
    public function courseDetails($id)
    {
        $course = Course::with(['category', 'teacher', 'modules', 'reviews'])
            ->withCount('enrollments')
            ->findOrFail($id);
        
        $isEnrolled = CourseEnrollment::where('student_id', Auth::id())
            ->where('course_id', $id)
            ->exists();
        
        // Calculate course ratings
        $avgRating = $course->reviews->avg('rating') ?? 0;
        $totalReviews = $course->reviews->count();
        
        // Get similar courses
        $similarCourses = Course::where('id', '!=', $id)
            ->where(function($query) use ($course) {
                $query->where('category_id', $course->category_id)
                      ->orWhere('level', $course->level);
            })
            ->where('status', 'published')
            ->take(4)
            ->get();
        
        return view('student.courses.details', compact(
            'course',
            'isEnrolled',
            'avgRating',
            'totalReviews',
            'similarCourses'
        ));
    }
    
    /**
     * Enroll in a course
     */
    public function enrollCourse($id)
    {
        $course = Course::findOrFail($id);
        $user = Auth::user();
        
        // Check if already enrolled
        $existingEnrollment = CourseEnrollment::where('student_id', $user->id)
            ->where('course_id', $course->id)
            ->first();
        
        if ($existingEnrollment) {
            return redirect()->route('student.courses.learn', $course->id)
                ->with('info', 'You are already enrolled in this course.');
        }
        
        // Check if course requires payment
        if ($course->price > 0) {
            // Redirect to payment page
            return redirect()->route('student.courses.checkout', $course->id);
        }
        
        // Free course enrollment
        $enrollment = new CourseEnrollment();
        $enrollment->student_id = $user->id;
        $enrollment->course_id = $course->id;
        $enrollment->status = 'active';
        $enrollment->enrolled_at = now();
        $enrollment->progress_percentage = 0;
        $enrollment->save();
        
        return redirect()->route('student.courses.learn', $course->id)
            ->with('success', 'You have successfully enrolled in this course!');
    }
    
    /**
     * Display enrolled courses
     */
    public function enrolledCourses()
    {
        $user = Auth::user();
        
        $activeEnrollments = CourseEnrollment::where('student_id', $user->id)
            ->where('status', 'active')
            ->with(['course'])
            ->get();
        
        $completedEnrollments = CourseEnrollment::where('student_id', $user->id)
            ->where('status', 'completed')
            ->with(['course'])
            ->get();
        
        return view('student.courses.enrolled', compact('activeEnrollments', 'completedEnrollments'));
    }
    
    /**
     * Display course learning interface
     */
    public function learnCourse($id)
    {
        $course = Course::with(['modules.lessons'])->findOrFail($id);
        $user = Auth::user();
        
        // Check if enrolled
        $enrollment = CourseEnrollment::where('student_id', $user->id)
            ->where('course_id', $course->id)
            ->firstOrFail();
        
        // Get lesson progress
        $lessonProgress = LessonProgress::where('student_id', $user->id)
            ->whereIn('lesson_id', $course->lessons->pluck('id'))
            ->get()
            ->keyBy('lesson_id');
        
        // Determine current lesson (last accessed or first)
        $currentLesson = null;
        $lastAccessed = $lessonProgress->sortByDesc('last_accessed_at')->first();
        
        if ($lastAccessed) {
            $currentLesson = Lesson::find($lastAccessed->lesson_id);
        } else {
            // Get first lesson
            $firstModule = $course->modules->sortBy('order')->first();
            if ($firstModule) {
                $currentLesson = $firstModule->lessons->sortBy('order')->first();
            }
        }
        
        return view('student.courses.learn', compact(
            'course',
            'enrollment',
            'lessonProgress',
            'currentLesson'
        ));
    }
    
    /**
     * Display lesson content
     */
    public function viewLesson($courseId, $lessonId)
    {
        $course = Course::findOrFail($courseId);
        $lesson = Lesson::with('module')->findOrFail($lessonId);
        $user = Auth::user();
        
        // Check if enrolled
        $enrollment = CourseEnrollment::where('student_id', $user->id)
            ->where('course_id', $course->id)
            ->firstOrFail();
        
        // Check if lesson belongs to course
        $moduleIds = $course->modules->pluck('id')->toArray();
        if (!in_array($lesson->module_id, $moduleIds)) {
            abort(404);
        }
        
        // Record lesson progress
        $progress = LessonProgress::firstOrNew([
            'student_id' => $user->id,
            'lesson_id' => $lesson->id
        ]);
        
        $progress->last_accessed_at = now();
        if (!$progress->started_at) {
            $progress->started_at = now();
        }
        $progress->save();
        
        // Get next and previous lessons
        $currentModule = $lesson->module;
        $modules = $course->modules->sortBy('order');
        
        $nextLesson = null;
        $prevLesson = null;
        $foundCurrent = false;
        
        foreach ($modules as $module) {
            $lessons = $module->lessons->sortBy('order');
            foreach ($lessons as $l) {
                if ($foundCurrent) {
                    $nextLesson = $l;
                    break 2; // Found next, exit both loops
                }
                
                if ($l->id == $lesson->id) {
                    $foundCurrent = true;
                } else {
                    $prevLesson = $l;
                }
            }
        }
        
        // Get lesson progress for all lessons in the course
        $lessonProgress = LessonProgress::where('student_id', $user->id)
            ->whereIn('lesson_id', $course->lessons->pluck('id'))
            ->get()
            ->keyBy('lesson_id');
        
        return view('student.courses.lesson', compact(
            'course',
            'lesson',
            'enrollment',
            'progress',
            'nextLesson',
            'prevLesson',
            'modules',
            'lessonProgress'
        ));
    }
    
    /**
     * Mark lesson as completed
     */
    public function completeLesson(Request $request, $courseId, $lessonId)
    {
        $user = Auth::user();
        $lesson = Lesson::findOrFail($lessonId);
        
        // Update lesson progress
        $progress = LessonProgress::where('student_id', $user->id)
            ->where('lesson_id', $lessonId)
            ->firstOrFail();
        
        $progress->completed_at = now();
        $progress->status = 'completed';
        
        // If this is a quiz/assessment, save the score
        if ($request->has('score')) {
            $progress->score = $request->score;
        }
        
        $progress->save();
        
        // Update course progress percentage
        $this->updateCourseProgress($user->id, $courseId);
        
        // Check if course is now completed
        $this->checkCourseCompletion($user->id, $courseId);
        
        return response()->json([
            'success' => true,
            'message' => 'Lesson marked as completed!'
        ]);
    }
    
    /**
     * Generate course completion certificate
     */
    public function generateCertificate($enrollmentId)
    {
        $enrollment = CourseEnrollment::with(['course', 'student'])
            ->where('student_id', Auth::id())
            ->findOrFail($enrollmentId);
        
        // Check if course is completed
        if ($enrollment->status !== 'completed') {
            return redirect()->back()->with('error', 'You must complete the course to get a certificate.');
        }
        
        $data = [
            'student' => $enrollment->student,
            'course' => $enrollment->course,
            'enrollment' => $enrollment,
            'date' => $enrollment->completed_at->format('F d, Y')
        ];
        
        $pdf = PDF::loadView('student.certificates.template', $data);
        
        return $pdf->download('certificate-'.$enrollment->course->slug.'.pdf');
    }
    
    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'bio' => 'nullable|string|max:500',
            'profile_picture' => 'nullable|image|max:2048',
            'phone' => 'nullable|string|max:20',
        ]);
        
        $user->name = $validated['name'];
        $user->bio = $validated['bio'];
        $user->phone = $validated['phone'];
        
        if ($request->hasFile('profile_picture')) {
            // Delete old picture if exists
            if ($user->profile_picture) {
                Storage::delete('public/'.$user->profile_picture);
            }
            
            $path = $request->file('profile_picture')->store('profile_pictures', 'public');
            $user->profile_picture = $path;
        }
        
        $user->save();
        
        return redirect()->route('student.profile')->with('success', 'Profile updated successfully.');
    }
    
    /**
     * Display user profile
     */
    public function showProfile()
    {
        $user = Auth::user();
        
        $enrollments = CourseEnrollment::where('student_id', $user->id)
            ->with(['course'])
            ->get();
        
        $completedCourses = $enrollments->where('status', 'completed')->count();
        $inProgressCourses = $enrollments->where('status', 'active')->count();
        
        // Get certificates
        $certificates = CourseEnrollment::where('student_id', $user->id)
            ->where('status', 'completed')
            ->with(['course'])
            ->get();
        
        // Get achievements
        $achievements = $this->getAchievements($user->id);
        
        return view('student.profile', compact(
            'user',
            'enrollments',
            'completedCourses',
            'inProgressCourses',
            'certificates',
            'achievements'
        ));
    }
    
    /**
     * Calculate learning streak
     */
    private function calculateLearningStreak($userId)
    {
        $today = Carbon::today();
        $currentStreak = 0;
        $lastActivity = null;
        
        // Get all student activity sorted by date in descending order
        $activities = LessonProgress::where('student_id', $userId)
            ->whereNotNull('last_accessed_at')
            ->orderBy('last_accessed_at', 'desc')
            ->get()
            ->map(function($item) {
                return Carbon::parse($item->last_accessed_at)->startOfDay();
            })
            ->unique()
            ->values();
        
        // No activity found
        if ($activities->isEmpty()) {
            return 0;
        }
        
        // Check if user was active today
        $lastActivityDate = $activities->first();
        if ($lastActivityDate->lt($today)) {
            // Last activity was before today
            if ($lastActivityDate->lt($today->copy()->subDay())) {
                // Last activity was before yesterday, streak is broken
                return 0;
            }
        }
        
        // Calculate streak
        $checkDate = $today;
        foreach ($activities as $activityDate) {
            if ($activityDate->eq($checkDate) || $activityDate->eq($checkDate->copy()->subDay())) {
                $currentStreak++;
                $checkDate = $activityDate->copy()->subDay();
            } else {
                break;
            }
        }
        
        return $currentStreak;
    }
    
    /**
     * Get user achievements
     */
    private function getAchievements($userId)
    {
        $achievements = [];
        
        // Course completion achievements
        $completedCourses = CourseEnrollment::where('student_id', $userId)
            ->where('status', 'completed')
            ->count();
        
        if ($completedCourses >= 1) {
            $achievements[] = [
                'title' => 'First Course Completed',
                'description' => 'You completed your first course!',
                'icon' => 'bi-award',
                'color' => 'success'
            ];
        }
        
        if ($completedCourses >= 5) {
            $achievements[] = [
                'title' => 'Knowledge Seeker',
                'description' => 'You completed 5 courses!',
                'icon' => 'bi-book',
                'color' => 'primary'
            ];
        }
        
        if ($completedCourses >= 10) {
            $achievements[] = [
                'title' => 'Learning Master',
                'description' => 'You completed 10 courses!',
                'icon' => 'bi-mortarboard',
                'color' => 'warning'
            ];
        }
        
        // Perfect score achievements
        $perfectScores = LessonProgress::where('student_id', $userId)
            ->where('score', 100)
            ->count();
        
        if ($perfectScores >= 3) {
            $achievements[] = [
                'title' => 'Perfect Student',
                'description' => 'You got a perfect score on 3 assessments!',
                'icon' => 'bi-patch-check',
                'color' => 'info'
            ];
        }
        
        // Streak achievements
        $streak = $this->calculateLearningStreak($userId);
        
        if ($streak >= 3) {
            $achievements[] = [
                'title' => 'Consistent Learner',
                'description' => 'You maintained a 3-day learning streak!',
                'icon' => 'bi-calendar-check',
                'color' => 'primary'
            ];
        }
        
        if ($streak >= 7) {
            $achievements[] = [
                'title' => 'Weekly Warrior',
                'description' => 'You maintained a 7-day learning streak!',
                'icon' => 'bi-calendar-week',
                'color' => 'danger'
            ];
        }
        
        if ($streak >= 30) {
            $achievements[] = [
                'title' => 'Monthly Master',
                'description' => 'You maintained a 30-day learning streak!',
                'icon' => 'bi-calendar-month',
                'color' => 'warning'
            ];
        }
        
        return $achievements;
    }
    
    /**
     * Update course progress percentage
     */
    private function updateCourseProgress($userId, $courseId)
    {
        $course = Course::with('lessons')->findOrFail($courseId);
        $totalLessons = $course->lessons->count();
        
        if ($totalLessons == 0) {
            return;
        }
        
        $completedLessons = LessonProgress::where('student_id', $userId)
            ->whereIn('lesson_id', $course->lessons->pluck('id'))
            ->where('status', 'completed')
            ->count();
        
        $progressPercentage = round(($completedLessons / $totalLessons) * 100);
        
        CourseEnrollment::where('student_id', $userId)
            ->where('course_id', $courseId)
            ->update(['progress_percentage' => $progressPercentage]);
    }
    
    /**
     * Check if course is completed
     */
    private function checkCourseCompletion($userId, $courseId)
    {
        $enrollment = CourseEnrollment::where('student_id', $userId)
            ->where('course_id', $courseId)
            ->first();
        
        if ($enrollment && $enrollment->progress_percentage >= 100) {
            $enrollment->status = 'completed';
            $enrollment->completed_at = now();
            $enrollment->save();
            
            // TODO: Generate certificate automatically if needed
            
            return true;
        }
        
        return false;
    }
}