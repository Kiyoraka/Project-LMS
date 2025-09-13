<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Module;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Database\Seeder;

class CourseTestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the test teacher (ID 2: teacher@aidel.com)
        $teacher = User::where('email', 'teacher@aidel.com')->first();
        
        if (!$teacher) {
            $this->command->error('Test teacher not found. Please run DatabaseSeeder first.');
            return;
        }

        // Array of test courses to create
        $testCourses = [
            [
                'title' => 'Web Development Fundamentals',
                'slug' => 'web-development-fundamentals',
                'description' => 'Learn the basics of HTML, CSS, and JavaScript to build modern websites.',
                'short_description' => 'Master web development basics with HTML, CSS, and JavaScript.',
                'category' => 'Programming',
                'level' => 'beginner',
                'duration_hours' => 12,
                'price' => 0.00,
                'featured' => true,
            ],
            [
                'title' => 'Advanced JavaScript Concepts',
                'slug' => 'advanced-javascript-concepts',
                'description' => 'Deep dive into advanced JavaScript features and modern ES6+ syntax.',
                'short_description' => 'Advanced JavaScript programming with ES6+ features.',
                'category' => 'Programming',
                'level' => 'advanced',
                'duration_hours' => 15,
                'price' => 29.99,
                'featured' => false,
            ],
            [
                'title' => 'React.js for Beginners',
                'slug' => 'reactjs-for-beginners',
                'description' => 'Build interactive user interfaces with React.js library.',
                'short_description' => 'Learn React.js to create dynamic web applications.',
                'category' => 'Programming',
                'level' => 'intermediate',
                'duration_hours' => 18,
                'price' => 39.99,
                'featured' => true,
            ],
            [
                'title' => 'Digital Marketing Essentials',
                'slug' => 'digital-marketing-essentials',
                'description' => 'Comprehensive guide to online marketing strategies and tools.',
                'short_description' => 'Master digital marketing strategies and techniques.',
                'category' => 'Marketing',
                'level' => 'beginner',
                'duration_hours' => 10,
                'price' => 24.99,
                'featured' => false,
            ],
            [
                'title' => 'Graphic Design Basics',
                'slug' => 'graphic-design-basics',
                'description' => 'Learn fundamental design principles and Adobe Creative Suite.',
                'short_description' => 'Design fundamentals using professional tools.',
                'category' => 'Design',
                'level' => 'beginner',
                'duration_hours' => 14,
                'price' => 0.00,
                'featured' => true,
            ],
            [
                'title' => 'Photography Masterclass',
                'slug' => 'photography-masterclass',
                'description' => 'Professional photography techniques and post-processing.',
                'short_description' => 'Professional photography skills and editing.',
                'category' => 'Creative',
                'level' => 'intermediate',
                'duration_hours' => 20,
                'price' => 49.99,
                'featured' => false,
            ],
            [
                'title' => 'Business Analytics with Excel',
                'slug' => 'business-analytics-excel',
                'description' => 'Data analysis and visualization using Microsoft Excel.',
                'short_description' => 'Excel for business data analysis and reporting.',
                'category' => 'Business',
                'level' => 'intermediate',
                'duration_hours' => 16,
                'price' => 34.99,
                'featured' => false,
            ],
            [
                'title' => 'Mobile App Development',
                'slug' => 'mobile-app-development',
                'description' => 'Create mobile applications for iOS and Android platforms.',
                'short_description' => 'Build mobile apps for iOS and Android.',
                'category' => 'Programming',
                'level' => 'advanced',
                'duration_hours' => 25,
                'price' => 59.99,
                'featured' => true,
            ],
            [
                'title' => 'Project Management Fundamentals',
                'slug' => 'project-management-fundamentals',
                'description' => 'Essential project management skills and methodologies.',
                'short_description' => 'Learn project management best practices.',
                'category' => 'Business',
                'level' => 'beginner',
                'duration_hours' => 8,
                'price' => 19.99,
                'featured' => false,
            ],
            [
                'title' => 'Data Science with Python',
                'slug' => 'data-science-python',
                'description' => 'Data analysis, machine learning, and visualization with Python.',
                'short_description' => 'Python for data science and machine learning.',
                'category' => 'Data Science',
                'level' => 'advanced',
                'duration_hours' => 30,
                'price' => 79.99,
                'featured' => true,
            ],
            [
                'title' => 'Content Writing Mastery',
                'slug' => 'content-writing-mastery',
                'description' => 'Write compelling content for blogs, websites, and social media.',
                'short_description' => 'Master the art of persuasive content writing.',
                'category' => 'Writing',
                'level' => 'beginner',
                'duration_hours' => 12,
                'price' => 24.99,
                'featured' => false,
            ],
            [
                'title' => 'Cybersecurity Basics',
                'slug' => 'cybersecurity-basics',
                'description' => 'Essential cybersecurity concepts and best practices.',
                'short_description' => 'Learn to protect systems and data from threats.',
                'category' => 'Technology',
                'level' => 'intermediate',
                'duration_hours' => 14,
                'price' => 44.99,
                'featured' => false,
            ],
            [
                'title' => 'Social Media Marketing',
                'slug' => 'social-media-marketing',
                'description' => 'Leverage social platforms for business growth and engagement.',
                'short_description' => 'Master social media marketing strategies.',
                'category' => 'Marketing',
                'level' => 'beginner',
                'duration_hours' => 10,
                'price' => 29.99,
                'featured' => false,
            ],
            [
                'title' => 'Video Editing Pro',
                'slug' => 'video-editing-pro',
                'description' => 'Professional video editing techniques using industry tools.',
                'short_description' => 'Create professional videos with advanced editing.',
                'category' => 'Creative',
                'level' => 'intermediate',
                'duration_hours' => 18,
                'price' => 54.99,
                'featured' => false,
            ],
            [
                'title' => 'Financial Planning 101',
                'slug' => 'financial-planning-101',
                'description' => 'Personal finance management and investment strategies.',
                'short_description' => 'Take control of your personal finances.',
                'category' => 'Finance',
                'level' => 'beginner',
                'duration_hours' => 8,
                'price' => 0.00,
                'featured' => false,
            ],
            [
                'title' => 'Machine Learning Fundamentals',
                'slug' => 'machine-learning-fundamentals',
                'description' => 'Introduction to machine learning algorithms and applications.',
                'short_description' => 'Understand machine learning concepts and techniques.',
                'category' => 'Data Science',
                'level' => 'intermediate',
                'duration_hours' => 22,
                'price' => 69.99,
                'featured' => true,
            ],
            [
                'title' => 'UX/UI Design Principles',
                'slug' => 'ux-ui-design-principles',
                'description' => 'Create user-centered designs and intuitive interfaces.',
                'short_description' => 'Design better user experiences and interfaces.',
                'category' => 'Design',
                'level' => 'intermediate',
                'duration_hours' => 16,
                'price' => 42.99,
                'featured' => false,
            ],
            [
                'title' => 'Cloud Computing Essentials',
                'slug' => 'cloud-computing-essentials',
                'description' => 'Understanding cloud services and deployment strategies.',
                'short_description' => 'Learn cloud computing fundamentals and services.',
                'category' => 'Technology',
                'level' => 'intermediate',
                'duration_hours' => 20,
                'price' => 59.99,
                'featured' => false,
            ],
        ];

        $this->command->info('Creating test courses...');

        foreach ($testCourses as $courseData) {
            // Check if course already exists
            $existingCourse = Course::where('slug', $courseData['slug'])->first();
            if ($existingCourse) {
                $this->command->info("Course '{$courseData['title']}' already exists, skipping...");
                continue;
            }

            // Create course
            $course = Course::create([
                'user_id' => $teacher->id,
                'title' => $courseData['title'],
                'slug' => $courseData['slug'],
                'description' => $courseData['description'],
                'short_description' => $courseData['short_description'],
                'category' => $courseData['category'],
                'price' => $courseData['price'],
                'status' => 'published',
                'featured' => $courseData['featured'],
                'level' => $courseData['level'],
                'duration_hours' => $courseData['duration_hours'],
                'published_at' => now(),
            ]);

            // Create a module for the course
            $module = Module::create([
                'course_id' => $course->id,
                'title' => 'Main Content',
                'description' => 'Course content and lessons',
                'position' => 1,
                'is_published' => true,
            ]);

            // Create some sample lessons
            $lessons = [
                ['title' => 'Introduction', 'description' => 'Course introduction and overview'],
                ['title' => 'Getting Started', 'description' => 'Setting up and basic concepts'],
                ['title' => 'Core Concepts', 'description' => 'Essential skills and knowledge'],
                ['title' => 'Practical Applications', 'description' => 'Hands-on projects and examples'],
                ['title' => 'Advanced Topics', 'description' => 'Advanced techniques and strategies'],
                ['title' => 'Conclusion', 'description' => 'Summary and next steps'],
            ];

            foreach ($lessons as $index => $lessonData) {
                Lesson::create([
                    'module_id' => $module->id,
                    'title' => $lessonData['title'],
                    'description' => $lessonData['description'],
                    'content_type' => 'video',
                    'video_url' => 'https://example.com/video' . ($index + 1),
                    'duration_minutes' => rand(10, 30),
                    'position' => $index + 1,
                    'is_published' => true,
                    'is_free_preview' => $index === 0, // First lesson is free preview
                ]);
            }

            $this->command->info("Created course: {$course->title}");
        }

        $this->command->info('Test courses created successfully!');
    }
} 