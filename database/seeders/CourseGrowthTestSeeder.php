<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class CourseGrowthTestSeeder extends Seeder
{
    /**
     * Run the database seeds to test course growth chart functionality.
     */
    public function run(): void
    {
        // Get the test teacher
        $teacher = User::where('role', 'teacher')->first();

        if (!$teacher) {
            $this->command->error('Test teacher not found. Please run DatabaseSeeder first.');
            return;
        }

        $this->command->info('Creating courses with distributed creation dates for chart testing...');

        // Array of test courses with specific creation dates
        $testCourses = [
            // Courses from 3 months ago
            [
                'title' => 'Chart Test Course 1',
                'slug' => 'chart-test-course-1',
                'description' => 'Test course for chart functionality - 3 months ago',
                'short_description' => 'Chart test course from 3 months ago',
                'category' => 'Test',
                'created_at' => Carbon::now()->subMonths(3)->subDays(2),
            ],
            [
                'title' => 'Chart Test Course 2',
                'slug' => 'chart-test-course-2',
                'description' => 'Test course for chart functionality - 3 months ago',
                'short_description' => 'Chart test course from 3 months ago',
                'category' => 'Test',
                'created_at' => Carbon::now()->subMonths(3)->subDays(1),
            ],

            // Courses from 2 months ago
            [
                'title' => 'Chart Test Course 3',
                'slug' => 'chart-test-course-3',
                'description' => 'Test course for chart functionality - 2 months ago',
                'short_description' => 'Chart test course from 2 months ago',
                'category' => 'Test',
                'created_at' => Carbon::now()->subMonths(2)->subDays(5),
            ],
            [
                'title' => 'Chart Test Course 4',
                'slug' => 'chart-test-course-4',
                'description' => 'Test course for chart functionality - 2 months ago',
                'short_description' => 'Chart test course from 2 months ago',
                'category' => 'Test',
                'created_at' => Carbon::now()->subMonths(2)->subDays(3),
            ],
            [
                'title' => 'Chart Test Course 5',
                'slug' => 'chart-test-course-5',
                'description' => 'Test course for chart functionality - 2 months ago',
                'short_description' => 'Chart test course from 2 months ago',
                'category' => 'Test',
                'created_at' => Carbon::now()->subMonths(2)->subDays(1),
            ],

            // Courses from 1 month ago
            [
                'title' => 'Chart Test Course 6',
                'slug' => 'chart-test-course-6',
                'description' => 'Test course for chart functionality - 1 month ago',
                'short_description' => 'Chart test course from 1 month ago',
                'category' => 'Test',
                'created_at' => Carbon::now()->subMonths(1)->subDays(10),
            ],

            // Courses from this month
            [
                'title' => 'Chart Test Course 7',
                'slug' => 'chart-test-course-7',
                'description' => 'Test course for chart functionality - this month',
                'short_description' => 'Chart test course from this month',
                'category' => 'Test',
                'created_at' => Carbon::now()->subDays(15),
            ],
            [
                'title' => 'Chart Test Course 8',
                'slug' => 'chart-test-course-8',
                'description' => 'Test course for chart functionality - this month',
                'short_description' => 'Chart test course from this month',
                'category' => 'Test',
                'created_at' => Carbon::now()->subDays(10),
            ],
            [
                'title' => 'Chart Test Course 9',
                'slug' => 'chart-test-course-9',
                'description' => 'Test course for chart functionality - this month',
                'short_description' => 'Chart test course from this month',
                'category' => 'Test',
                'created_at' => Carbon::now()->subDays(5),
            ],

            // Courses from this week
            [
                'title' => 'Chart Test Course 10',
                'slug' => 'chart-test-course-10',
                'description' => 'Test course for chart functionality - this week',
                'short_description' => 'Chart test course from this week',
                'category' => 'Test',
                'created_at' => Carbon::now()->subDays(3),
            ],
            [
                'title' => 'Chart Test Course 11',
                'slug' => 'chart-test-course-11',
                'description' => 'Test course for chart functionality - this week',
                'short_description' => 'Chart test course from this week',
                'category' => 'Test',
                'created_at' => Carbon::now()->subDays(1),
            ],
        ];

        foreach ($testCourses as $courseData) {
            // Check if course already exists
            $existingCourse = Course::where('slug', $courseData['slug'])->first();
            if ($existingCourse) {
                $this->command->info("Course '{$courseData['title']}' already exists, skipping...");
                continue;
            }

            // Create course with specific creation date
            $course = new Course([
                'user_id' => $teacher->id,
                'title' => $courseData['title'],
                'slug' => $courseData['slug'],
                'description' => $courseData['description'],
                'short_description' => $courseData['short_description'],
                'category' => $courseData['category'],
                'price' => 0.00,
                'status' => 'published',
                'featured' => false,
                'level' => 'beginner',
                'duration_hours' => 5,
                'published_at' => $courseData['created_at'],
            ]);

            // Set the created_at timestamp explicitly
            $course->created_at = $courseData['created_at'];
            $course->updated_at = $courseData['created_at'];
            $course->save();

            $this->command->info("Created course: {$course->title} (Created: {$course->created_at->format('Y-m-d')})");
        }

        $this->command->info('Course growth test data created successfully!');

        // Show summary
        $this->command->info("\n--- Course Distribution Summary ---");
        $this->command->info('3 months ago: 2 courses');
        $this->command->info('2 months ago: 3 courses');
        $this->command->info('1 month ago: 1 course');
        $this->command->info('This month: 3 courses');
        $this->command->info('This week: 2 courses');
        $this->command->info('Total test courses: 11');
    }
}