<?php

namespace Database\Seeders;

use App\Models\LessonProgress;
use App\Models\Lesson;
use App\Models\User;
use App\Models\CourseEnrollment;
use Illuminate\Database\Seeder;

class LessonProgressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get students who have enrollments
        $studentsWithEnrollments = User::where('role', 'student')
            ->whereHas('enrollments')
            ->with(['enrollments.course.modules.lessons'])
            ->get();

        if ($studentsWithEnrollments->isEmpty()) {
            $this->command->info('No students with enrollments found.');
            return;
        }

        foreach ($studentsWithEnrollments as $student) {
            foreach ($student->enrollments as $enrollment) {
                if ($enrollment->course && $enrollment->course->modules->isNotEmpty()) {
                    foreach ($enrollment->course->modules as $module) {
                        if ($module->lessons->isNotEmpty()) {
                            // Create progress for first 2-3 lessons in each module
                            $lessonsToProgress = $module->lessons->take(rand(2, 3));
                            
                            foreach ($lessonsToProgress as $lesson) {
                                $isCompleted = rand(1, 100) > 30; // 70% chance of completion
                                $progressPercentage = $isCompleted ? 100 : rand(25, 95);
                                
                                LessonProgress::updateOrCreate(
                                    [
                                        'user_id' => $student->id,
                                        'lesson_id' => $lesson->id,
                                    ],
                                    [
                                        'course_enrollment_id' => $enrollment->id,
                                        'is_completed' => $isCompleted,
                                        'progress_percentage' => $progressPercentage,
                                        'time_spent_seconds' => rand(300, 1800), // 5-30 minutes
                                        'started_at' => now()->subDays(rand(0, 7))->subHours(rand(0, 23)),
                                        'completed_at' => $isCompleted ? now()->subDays(rand(0, 5))->subHours(rand(0, 23)) : null,
                                        'score' => $isCompleted ? rand(70, 100) : null,
                                        'updated_at' => now()->subDays(rand(0, 3))->subHours(rand(0, 23))
                                    ]
                                );
                            }
                        }
                    }
                }
            }
        }

        $this->command->info('Lesson progress data seeded successfully!');
    }
} 