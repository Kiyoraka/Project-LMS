<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CourseEnrollment;
use App\Models\User;
use App\Models\Course;
use Carbon\Carbon;

class CompletedEnrollmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get a student user (user with role 'student')
        $student = User::where('role', 'student')->first();
        
        if (!$student) {
            $this->command->info('No student user found. Please create a student user first.');
            return;
        }

        // Get some courses to complete
        $courses = Course::limit(3)->get();
        
        if ($courses->isEmpty()) {
            $this->command->info('No courses found. Please create some courses first.');
            return;
        }

        $completionDates = [
            Carbon::now()->subMonths(2)->setDay(15), // 2 months ago
            Carbon::now()->subMonth()->setDay(22),   // 1 month ago  
            Carbon::now()->subWeeks(2)->setDay(10),  // 2 weeks ago
        ];

        $progressPercentages = [92, 88, 95]; // High completion scores
        
        foreach ($courses as $index => $course) {
            // Check if enrollment already exists
            $enrollment = CourseEnrollment::where('user_id', $student->id)
                ->where('course_id', $course->id)
                ->first();
            
            if (!$enrollment) {
                // Create new enrollment
                $enrollment = CourseEnrollment::create([
                    'user_id' => $student->id,
                    'course_id' => $course->id,
                    'enrolled_at' => $completionDates[$index]->copy()->subWeeks(4), // Enrolled 4 weeks before completion
                    'status' => 'completed',
                    'progress_percentage' => $progressPercentages[$index] ?? 90,
                    'completed_at' => $completionDates[$index] ?? Carbon::now()->subWeeks(1),
                ]);
                
                $this->command->info("Created completed enrollment for student '{$student->name}' in course '{$course->title}'");
            } else {
                // Update existing enrollment to completed
                $enrollment->update([
                    'status' => 'completed',
                    'progress_percentage' => $progressPercentages[$index] ?? 90,
                    'completed_at' => $completionDates[$index] ?? Carbon::now()->subWeeks(1),
                ]);
                
                $this->command->info("Updated enrollment for student '{$student->name}' in course '{$course->title}' to completed");
            }
        }
        
        $this->command->info('Completed enrollment seeder finished successfully!');
    }
}
