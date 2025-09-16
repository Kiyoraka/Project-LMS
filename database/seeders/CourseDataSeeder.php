<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\Module;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CourseDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create a teacher user
        $teacher = User::where('role', 'teacher')->first();

        if (!$teacher) {
            $teacher = User::where('email', 'teacher@gmail.com')->first();
        }

        if (!$teacher) {
            echo "No teacher found. Please ensure you have a teacher user in the database.\n";
            return;
        }

        // Course data with different creation dates for growth chart
        $courses = [
            [
                'title' => 'Introduction to Web Development',
                'short_description' => 'Learn the basics of HTML, CSS, and JavaScript',
                'description' => 'This comprehensive course covers the fundamentals of web development, including HTML5, CSS3, and modern JavaScript.',
                'level' => 'beginner',
                'status' => 'published',
                'price' => 299,
                'created_at' => Carbon::now()->subMonths(3),
                'published_at' => Carbon::now()->subMonths(3),
            ],
            [
                'title' => 'Advanced PHP Programming',
                'short_description' => 'Master PHP and build dynamic web applications',
                'description' => 'Deep dive into PHP programming with Laravel framework, design patterns, and best practices.',
                'level' => 'advanced',
                'status' => 'published',
                'price' => 499,
                'created_at' => Carbon::now()->subMonths(2),
                'published_at' => Carbon::now()->subMonths(2),
            ],
            [
                'title' => 'Database Design and SQL',
                'short_description' => 'Learn to design and query databases effectively',
                'description' => 'Master database design principles, SQL queries, and optimization techniques.',
                'level' => 'intermediate',
                'status' => 'published',
                'price' => 399,
                'created_at' => Carbon::now()->subMonth(),
                'published_at' => Carbon::now()->subMonth(),
            ],
            [
                'title' => 'Vue.js 3 Complete Guide',
                'short_description' => 'Build modern single-page applications with Vue.js',
                'description' => 'Learn Vue.js 3 from scratch and build reactive web applications.',
                'level' => 'intermediate',
                'status' => 'published',
                'price' => 449,
                'created_at' => Carbon::now()->subWeeks(3),
                'published_at' => Carbon::now()->subWeeks(3),
            ],
            [
                'title' => 'Laravel API Development',
                'short_description' => 'Build RESTful APIs with Laravel',
                'description' => 'Learn to build secure and scalable RESTful APIs using Laravel.',
                'level' => 'advanced',
                'status' => 'published',
                'price' => 599,
                'created_at' => Carbon::now()->subWeeks(2),
                'published_at' => Carbon::now()->subWeeks(2),
            ],
            [
                'title' => 'React Fundamentals',
                'short_description' => 'Get started with React.js',
                'description' => 'Learn the fundamentals of React.js and build interactive user interfaces.',
                'level' => 'beginner',
                'status' => 'published',
                'price' => 349,
                'created_at' => Carbon::now()->subWeek(),
                'published_at' => Carbon::now()->subWeek(),
            ],
            [
                'title' => 'Mobile App Development with React Native',
                'short_description' => 'Build cross-platform mobile apps',
                'description' => 'Create native mobile applications for iOS and Android using React Native.',
                'level' => 'intermediate',
                'status' => 'published',
                'price' => 549,
                'created_at' => Carbon::now()->subDays(5),
                'published_at' => Carbon::now()->subDays(5),
            ],
            [
                'title' => 'DevOps Essentials',
                'short_description' => 'Learn CI/CD and deployment strategies',
                'description' => 'Master DevOps practices including Docker, CI/CD pipelines, and cloud deployment.',
                'level' => 'advanced',
                'status' => 'published',
                'price' => 699,
                'created_at' => Carbon::now()->subDays(3),
                'published_at' => Carbon::now()->subDays(3),
            ],
            [
                'title' => 'UI/UX Design Principles',
                'short_description' => 'Design beautiful and user-friendly interfaces',
                'description' => 'Learn the principles of good UI/UX design and create stunning interfaces.',
                'level' => 'beginner',
                'status' => 'published',
                'price' => 399,
                'created_at' => Carbon::now()->subDays(2),
                'published_at' => Carbon::now()->subDays(2),
            ],
            [
                'title' => 'Python for Data Science',
                'short_description' => 'Analyze data with Python',
                'description' => 'Learn Python programming for data analysis, visualization, and machine learning.',
                'level' => 'intermediate',
                'status' => 'draft',
                'price' => 499,
                'created_at' => Carbon::now()->subDay(),
                'published_at' => null,
            ],
        ];

        foreach ($courses as $courseData) {
            // Create course
            $course = Course::create([
                'user_id' => $teacher->id,
                'title' => $courseData['title'],
                'slug' => Str::slug($courseData['title']),
                'short_description' => $courseData['short_description'],
                'description' => $courseData['description'],
                'level' => $courseData['level'],
                'status' => $courseData['status'],
                'price' => $courseData['price'],
                'duration_hours' => rand(10, 50),
                'created_at' => $courseData['created_at'],
                'updated_at' => $courseData['created_at'],
                'published_at' => $courseData['published_at'],
            ]);

            // Create a module for each course
            $module = Module::create([
                'course_id' => $course->id,
                'title' => 'Module 1: Introduction',
                'description' => 'Getting started with ' . $course->title,
                'position' => 1,
                'is_published' => true,
            ]);

            // Create 3-5 lessons for each module
            $lessonCount = rand(3, 5);
            for ($i = 1; $i <= $lessonCount; $i++) {
                Lesson::create([
                    'module_id' => $module->id,
                    'title' => "Lesson $i: " . fake()->sentence(3),
                    'description' => fake()->paragraph(),
                    'content_type' => 'video',
                    'video_url' => 'https://example.com/video' . $i . '.mp4',
                    'duration_minutes' => rand(10, 45),
                    'position' => $i,
                    'is_published' => true,
                    'is_free_preview' => $i === 1, // First lesson is free preview
                ]);
            }

            echo "Created course: {$course->title}\n";
        }

        echo "\nCourse data seeding completed successfully!\n";
        echo "Total courses created: " . count($courses) . "\n";
    }
}