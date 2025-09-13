<?php

namespace Database\Seeders;

use App\Models\CourseEnrollment;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Seeder;

class CourseEnrollmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the test teacher and course
        $teacher = User::where('email', 'teacher@aidel.com')->first();
        $course = Course::where('user_id', $teacher->id)->first();
        
        if (!$teacher || !$course) {
            $this->command->error('Teacher or course not found. Please run CourseTestDataSeeder first.');
            return;
        }

        // Create some test students if they don't exist
        $students = [];
        
        $studentData = [
            ['name' => 'Jane Smith', 'email' => 'jane.smith@example.com'],
            ['name' => 'Robert Johnson', 'email' => 'robert.j@example.com'],
            ['name' => 'Emily Davis', 'email' => 'emily.d@example.com'],
            ['name' => 'Michael Wilson', 'email' => 'michael.w@example.com'],
            ['name' => 'Sarah Taylor', 'email' => 'sarah.t@example.com'],
        ];

        foreach ($studentData as $data) {
            $student = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => bcrypt('password'),
                    'role' => 'student',
                    'is_active' => true,
                ]
            );
            $students[] = $student;
        }

        // Create course enrollments with different progress levels
        $progressLevels = [75, 65, 20, 100, 45];
        
        foreach ($students as $index => $student) {
            CourseEnrollment::firstOrCreate(
                [
                    'user_id' => $student->id,
                    'course_id' => $course->id,
                ],
                [
                    'status' => $progressLevels[$index] === 100 ? 'completed' : 'active',
                    'progress_percentage' => $progressLevels[$index],
                    'enrolled_at' => now()->subDays(rand(1, 30)),
                    'completed_at' => $progressLevels[$index] === 100 ? now()->subDays(rand(1, 5)) : null,
                ]
            );
        }

        $this->command->info('Course enrollments created successfully!');
    }
} 