<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;

class HomeApiController extends Controller
{
    /**
     * Get popular courses (top 3 by enrollment count)
     * This endpoint doesn't require authentication as it's for the homepage
     */
    public function getPopularCourses()
    {
        $courses = Course::where('status', 'published')
            ->withCount(['enrollments', 'modules', 'lessons'])
            ->with('teacher')
            ->orderBy('enrollments_count', 'desc')
            ->take(3)
            ->get();

        $popularCourses = $courses->map(function ($course) {
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
                'created_at' => $course->created_at->toISOString()
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $popularCourses,
            'message' => 'Popular courses retrieved successfully'
        ]);
    }
} 