<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Course;
use App\Models\CourseEnrollment;
use Illuminate\Database\Seeder;

class TestEnrollmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = User::where('role', 'student')->get();
        $courses = Course::where('status', 'published')->get();
        
        if ($students->isEmpty()) {
            $this->command->warn('No students found. Please create students first.');
            return;
        }
        
        if ($courses->isEmpty()) {
            $this->command->warn('No published courses found. Please create courses first.');
            return;
        }
        
        $this->command->info('Creating test enrollments...');
        
        foreach ($courses as $index => $course) {
            // Create different enrollment counts to simulate popularity
            $enrollmentCount = match($index % 5) {
                0 => rand(15, 25), // Most popular
                1 => rand(10, 20), // Very popular  
                2 => rand(8, 15),  // Popular
                3 => rand(5, 12),  // Somewhat popular
                4 => rand(2, 8),   // Less popular
            };
            
            $this->command->info("Creating {$enrollmentCount} enrollments for: {$course->title}");
            
            for ($i = 0; $i < $enrollmentCount; $i++) {
                $student = $students->random();
                
                // Check if enrollment already exists
                $existingEnrollment = CourseEnrollment::where('user_id', $student->id)
                    ->where('course_id', $course->id)
                    ->first();
                
                if (!$existingEnrollment) {
                    CourseEnrollment::create([
                        'user_id' => $student->id,
                        'course_id' => $course->id,
                        'enrolled_at' => now()->subDays(rand(1, 30)),
                        'status' => rand(1, 10) > 8 ? 'completed' : 'active',
                        'progress_percentage' => rand(0, 100),
                        'completed_at' => rand(1, 10) > 8 ? now()->subDays(rand(1, 15)) : null,
                    ]);
                }
            }
        }
        
        $this->command->info('Test enrollments created successfully!');
        
        // Show enrollment counts
        $this->command->info('Current enrollment counts:');
        $coursesWithCounts = Course::withCount('enrollments')
            ->orderBy('enrollments_count', 'desc')
            ->get();
            
        foreach ($coursesWithCounts as $course) {
            $this->command->info("- {$course->title}: {$course->enrollments_count} students");
        }
    }
}
