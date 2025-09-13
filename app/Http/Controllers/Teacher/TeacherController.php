<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Module;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TeacherController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('teacher');
    }

    /**
     * Serve the teacher dashboard SPA (Single Page Application).
     *
     * @return \Illuminate\View\View
     */
    public function spa(): View
    {
        return view('teacher.teacher');
    }

    /**
     * Show the teacher dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function dashboard(): View
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
        
        // Recent enrollments
        $recentEnrollments = CourseEnrollment::with(['student', 'course'])
            ->whereHas('course', function ($query) use ($teacher) {
                $query->where('user_id', $teacher->id);
            })
            ->orderBy('enrolled_at', 'desc')
            ->take(5)
            ->get();
            
        // Recent courses
        $recentCourses = Course::where('user_id', $teacher->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
            
        // Enrollment chart data (monthly for the past year)
        $enrollmentsByMonth = CourseEnrollment::whereHas('course', function ($query) use ($teacher) {
            $query->where('user_id', $teacher->id);
        })
        ->select(DB::raw('MONTH(enrolled_at) as month'), DB::raw('YEAR(enrolled_at) as year'), DB::raw('COUNT(*) as count'))
        ->whereRaw('enrolled_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)')
        ->groupBy('year', 'month')
        ->orderBy('year')
        ->orderBy('month')
        ->get();
        
        // Format for chart.js
        $chartLabels = [];
        $chartData = [];
        
        foreach ($enrollmentsByMonth as $record) {
            $date = \DateTime::createFromFormat('!m', $record->month);
            $monthName = $date->format('M');
            $chartLabels[] = $monthName . ' ' . $record->year;
            $chartData[] = $record->count;
        }

        return view('teacher.dashboard', [
            'totalCourses' => $totalCourses,
            'publishedCourses' => $publishedCourses,
            'draftCourses' => $draftCourses,
            'totalEnrollments' => $totalEnrollments,
            'activeEnrollments' => $activeEnrollments,
            'completedEnrollments' => $completedEnrollments,
            'recentEnrollments' => $recentEnrollments,
            'recentCourses' => $recentCourses,
            'chartLabels' => $chartLabels,
            'chartData' => $chartData,
        ]);
    }

    /**
     * Display a listing of the courses.
     *
     * @return \Illuminate\View\View
     */
    public function courses(): View
    {
        $teacher = Auth::user();
        $courses = Course::where('user_id', $teacher->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return view('teacher.courses.index', compact('courses'));
    }

    /**
     * Show the form for creating a new course.
     *
     * @return \Illuminate\View\View
     */
    public function createCourse(): View
    {
        return view('teacher.courses.create');
    }

    /**
     * Store a newly created course in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeCourse(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'short_description' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'level' => 'required|in:beginner,intermediate,advanced',
            'thumbnail' => 'nullable|image|max:2048', // 2MB max
            'duration_hours' => 'nullable|integer|min:0',
            'status' => 'required|in:draft,published',
            'featured' => 'nullable|boolean',
        ]);
        
        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = $request->file('thumbnail')->store('course-thumbnails', 'public');
            $validated['thumbnail'] = $thumbnailPath;
        }
        
        // Set published_at if status is published
        if ($validated['status'] === 'published') {
            $validated['published_at'] = now();
        }
        
        // Add teacher ID
        $validated['user_id'] = Auth::id();
        
        // Create slug from title
        $validated['slug'] = Str::slug($validated['title']);
        
        $course = Course::create($validated);
        
        return redirect()->route('teacher.courses.show', $course->id)
            ->with('success', 'Course created successfully.');
    }

    /**
     * Display the specified course.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function showCourse($id): View
    {
        $teacher = Auth::user();
        $course = Course::where('user_id', $teacher->id)
            ->with(['modules.lessons'])
            ->findOrFail($id);
            
        // Get enrollment statistics
        $enrollmentStats = [
            'total' => $course->enrollments()->count(),
            'active' => $course->enrollments()->where('status', 'active')->count(),
            'completed' => $course->enrollments()->where('status', 'completed')->count(),
        ];
        
        // Get student list
        $students = $course->students()
            ->withPivot(['status', 'progress_percentage'])
            ->paginate(10);
            
        return view('teacher.courses.show', [
            'course' => $course,
            'enrollmentStats' => $enrollmentStats,
            'students' => $students,
        ]);
    }

    /**
     * Show the form for editing the specified course.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function editCourse($id): View
    {
        $teacher = Auth::user();
        $course = Course::where('user_id', $teacher->id)->findOrFail($id);
        
        return view('teacher.courses.edit', compact('course'));
    }

    /**
     * Update the specified course in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateCourse(Request $request, $id)
    {
        $teacher = Auth::user();
        $course = Course::where('user_id', $teacher->id)->findOrFail($id);
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'short_description' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'level' => 'required|in:beginner,intermediate,advanced',
            'thumbnail' => 'nullable|image|max:2048', // 2MB max
            'duration_hours' => 'nullable|integer|min:0',
            'status' => 'required|in:draft,published,archived',
            'featured' => 'nullable|boolean',
        ]);
        
        // Set featured to false if not present
        $validated['featured'] = $request->has('featured');
        
        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            // Delete old thumbnail if exists
            if ($course->thumbnail) {
                Storage::disk('public')->delete($course->thumbnail);
            }
            
            $thumbnailPath = $request->file('thumbnail')->store('course-thumbnails', 'public');
            $validated['thumbnail'] = $thumbnailPath;
        }
        
        // Update published_at if status changed to published
        if ($validated['status'] === 'published' && $course->status !== 'published') {
            $validated['published_at'] = now();
        }
        
        // Update slug if title changed
        if ($validated['title'] !== $course->title) {
            $validated['slug'] = Str::slug($validated['title']);
        }
        
        $course->update($validated);
        
        return redirect()->route('teacher.courses.show', $course->id)
            ->with('success', 'Course updated successfully.');
    }

    /**
     * Remove the specified course from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyCourse($id)
    {
        $teacher = Auth::user();
        $course = Course::where('user_id', $teacher->id)->findOrFail($id);
        
        // Delete thumbnail if exists
        if ($course->thumbnail) {
            Storage::disk('public')->delete($course->thumbnail);
        }
        
        $course->delete();
        
        return redirect()->route('teacher.courses.index')
            ->with('success', 'Course deleted successfully.');
    }

    /**
     * Show the form for creating a new module for a course.
     *
     * @param  int  $courseId
     * @return \Illuminate\View\View
     */
    public function createModule($courseId): View
    {
        $teacher = Auth::user();
        $course = Course::where('user_id', $teacher->id)->findOrFail($courseId);
        
        return view('teacher.modules.create', compact('course'));
    }

    /**
     * Store a newly created module in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $courseId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeModule(Request $request, $courseId)
    {
        $teacher = Auth::user();
        $course = Course::where('user_id', $teacher->id)->findOrFail($courseId);
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_published' => 'nullable|boolean',
        ]);
        
        // Set position to be the last in the course
        $lastPosition = $course->modules()->max('position') ?? 0;
        $validated['position'] = $lastPosition + 1;
        
        // Set is_published to false if not present
        $validated['is_published'] = $request->has('is_published');
        
        $module = $course->modules()->create($validated);
        
        return redirect()->route('teacher.courses.show', $course->id)
            ->with('success', 'Module created successfully.');
    }

    /**
     * Show the form for editing the specified module.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function editModule($id): View
    {
        $teacher = Auth::user();
        $module = Module::with('course')->findOrFail($id);
        
        // Check if the module belongs to a course owned by the teacher
        if ($module->course->user_id !== $teacher->id) {
            abort(403, 'Unauthorized action.');
        }
        
        return view('teacher.modules.edit', compact('module'));
    }

    /**
     * Update the specified module in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateModule(Request $request, $id)
    {
        $teacher = Auth::user();
        $module = Module::with('course')->findOrFail($id);
        
        // Check if the module belongs to a course owned by the teacher
        if ($module->course->user_id !== $teacher->id) {
            abort(403, 'Unauthorized action.');
        }
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_published' => 'nullable|boolean',
        ]);
        
        // Set is_published to false if not present
        $validated['is_published'] = $request->has('is_published');
        
        $module->update($validated);
        
        return redirect()->route('teacher.courses.show', $module->course_id)
            ->with('success', 'Module updated successfully.');
    }

    /**
     * Remove the specified module from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyModule($id)
    {
        $teacher = Auth::user();
        $module = Module::with('course')->findOrFail($id);
        
        // Check if the module belongs to a course owned by the teacher
        if ($module->course->user_id !== $teacher->id) {
            abort(403, 'Unauthorized action.');
        }
        
        $courseId = $module->course_id;
        $module->delete();
        
        return redirect()->route('teacher.courses.show', $courseId)
            ->with('success', 'Module deleted successfully.');
    }

    /**
     * Show the form for creating a new lesson for a module.
     *
     * @param  int  $moduleId
     * @return \Illuminate\View\View
     */
    public function createLesson($moduleId): View
    {
        $teacher = Auth::user();
        $module = Module::with('course')->findOrFail($moduleId);
        
        // Check if the module belongs to a course owned by the teacher
        if ($module->course->user_id !== $teacher->id) {
            abort(403, 'Unauthorized action.');
        }
        
        return view('teacher.lessons.create', compact('module'));
    }

    /**
     * Store a newly created lesson in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $moduleId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeLesson(Request $request, $moduleId)
    {
        $teacher = Auth::user();
        $module = Module::with('course')->findOrFail($moduleId);
        
        // Check if the module belongs to a course owned by the teacher
        if ($module->course->user_id !== $teacher->id) {
            abort(403, 'Unauthorized action.');
        }
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content_type' => 'required|in:video,text,quiz,assignment,file',
            'content' => 'nullable|string',
            'video_url' => 'nullable|string|max:255',
            'video_protected' => 'nullable|boolean',
            'video_duration' => 'nullable|string',
            'duration_minutes' => 'nullable|integer|min:0',
            'is_published' => 'nullable|boolean',
            'is_free_preview' => 'nullable|boolean',
        ]);
        
        // Set position to be the last in the module
        $lastPosition = $module->lessons()->max('position') ?? 0;
        $validated['position'] = $lastPosition + 1;
        
        // Set boolean fields
        $validated['is_published'] = $request->has('is_published');
        $validated['is_free_preview'] = $request->has('is_free_preview');
        $validated['video_protected'] = $request->has('video_protected');
        
        $lesson = $module->lessons()->create($validated);
        
        return redirect()->route('teacher.courses.show', $module->course_id)
            ->with('success', 'Lesson created successfully.');
    }

    /**
     * Show the form for editing the specified lesson.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function editLesson($id): View
    {
        $teacher = Auth::user();
        $lesson = Lesson::with(['module.course'])->findOrFail($id);
        
        // Check if the lesson belongs to a module in a course owned by the teacher
        if ($lesson->module->course->user_id !== $teacher->id) {
            abort(403, 'Unauthorized action.');
        }
        
        return view('teacher.lessons.edit', compact('lesson'));
    }

    /**
     * Update the specified lesson in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateLesson(Request $request, $id)
    {
        $teacher = Auth::user();
        $lesson = Lesson::with(['module.course'])->findOrFail($id);
        
        // Check if the lesson belongs to a module in a course owned by the teacher
        if ($lesson->module->course->user_id !== $teacher->id) {
            abort(403, 'Unauthorized action.');
        }
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content_type' => 'required|in:video,text,quiz,assignment,file',
            'content' => 'nullable|string',
            'video_url' => 'nullable|string|max:255',
            'video_protected' => 'nullable|boolean',
            'video_duration' => 'nullable|string',
            'duration_minutes' => 'nullable|integer|min:0',
            'is_published' => 'nullable|boolean',
            'is_free_preview' => 'nullable|boolean',
        ]);
        
        // Set boolean fields
        $validated['is_published'] = $request->has('is_published');
        $validated['is_free_preview'] = $request->has('is_free_preview');
        $validated['video_protected'] = $request->has('video_protected');
        
        $lesson->update($validated);
        
        return redirect()->route('teacher.courses.show', $lesson->module->course_id)
            ->with('success', 'Lesson updated successfully.');
    }

    /**
     * Remove the specified lesson from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyLesson($id)
    {
        $teacher = Auth::user();
        $lesson = Lesson::with(['module.course'])->findOrFail($id);
        
        // Check if the lesson belongs to a module in a course owned by the teacher
        if ($lesson->module->course->user_id !== $teacher->id) {
            abort(403, 'Unauthorized action.');
        }
        
        $courseId = $lesson->module->course_id;
        $lesson->delete();
        
        return redirect()->route('teacher.courses.show', $courseId)
            ->with('success', 'Lesson deleted successfully.');
    }

    /**
     * Display the students enrolled in the teacher's courses.
     *
     * @return \Illuminate\View\View
     */
    public function students(): View
    {
        $teacher = Auth::user();
        
        // Get all students enrolled in the teacher's courses
        $students = User::whereHas('enrollments.course', function ($query) use ($teacher) {
            $query->where('user_id', $teacher->id);
        })
        ->withCount(['enrollments' => function ($query) use ($teacher) {
            $query->whereHas('course', function ($q) use ($teacher) {
                $q->where('user_id', $teacher->id);
            });
        }])
        ->paginate(15);
        
        return view('teacher.students.index', compact('students'));
    }

    /**
     * Display a specific student's details and progress.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function showStudent($id): View
    {
        $teacher = Auth::user();
        $student = User::findOrFail($id);
        
        // Check if the student is enrolled in any of the teacher's courses
        $enrollmentExists = CourseEnrollment::whereHas('course', function ($query) use ($teacher) {
            $query->where('user_id', $teacher->id);
        })
        ->where('user_id', $student->id)
        ->exists();
        
        if (!$enrollmentExists) {
            abort(403, 'Unauthorized action. This student is not enrolled in any of your courses.');
        }
        
        // Get the student's enrollments in the teacher's courses
        $enrollments = CourseEnrollment::with('course')
            ->whereHas('course', function ($query) use ($teacher) {
                $query->where('user_id', $teacher->id);
            })
            ->where('user_id', $student->id)
            ->get();
            
        return view('teacher.students.show', [
            'student' => $student,
            'enrollments' => $enrollments,
        ]);
    }

    /**
     * Display a specific enrollment's progress details.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function showEnrollment($id): View
    {
        $teacher = Auth::user();
        $enrollment = CourseEnrollment::with(['course', 'student', 'lessonProgress.lesson'])
            ->findOrFail($id);
            
        // Check if the enrollment is for a course owned by the teacher
        if ($enrollment->course->user_id !== $teacher->id) {
            abort(403, 'Unauthorized action.');
        }
        
        // Get modules and lessons with progress
        $modules = Module::with(['lessons' => function ($query) use ($enrollment) {
            $query->with(['progress' => function ($q) use ($enrollment) {
                $q->where('user_id', $enrollment->user_id);
            }]);
        }])
        ->where('course_id', $enrollment->course_id)
        ->orderBy('position')
        ->get();
        
        return view('teacher.students.enrollment', [
            'enrollment' => $enrollment,
            'modules' => $modules,
        ]);
    }

    /**
     * Add notes to a student's enrollment.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addEnrollmentNotes(Request $request, $id)
    {
        $teacher = Auth::user();
        $enrollment = CourseEnrollment::with('course')->findOrFail($id);
        
        // Check if the enrollment is for a course owned by the teacher
        if ($enrollment->course->user_id !== $teacher->id) {
            abort(403, 'Unauthorized action.');
        }
        
        $validated = $request->validate([
            'notes' => 'required|string',
        ]);
        
        $enrollment->update(['notes' => $validated['notes']]);
        
        return redirect()->back()->with('success', 'Notes added successfully.');
    }

    /**
     * Add notes to a student's lesson progress.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addLessonProgressNotes(Request $request, $id)
    {
        $teacher = Auth::user();
        $progress = LessonProgress::with(['lesson.module.course'])->findOrFail($id);
        
        // Check if the progress is for a lesson in a course owned by the teacher
        if ($progress->lesson->module->course->user_id !== $teacher->id) {
            abort(403, 'Unauthorized action.');
        }
        
        $validated = $request->validate([
            'notes' => 'required|string',
        ]);
        
        $progress->update(['notes' => $validated['notes']]);
        
        return redirect()->back()->with('success', 'Notes added successfully.');
    }
}