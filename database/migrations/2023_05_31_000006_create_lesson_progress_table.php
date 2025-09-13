<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lesson_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Student
            $table->foreignId('lesson_id')->constrained()->onDelete('cascade');
            $table->foreignId('course_enrollment_id')->constrained()->onDelete('cascade');
            $table->boolean('is_completed')->default(false);
            $table->integer('progress_percentage')->default(0); // For partial completion
            $table->integer('time_spent_seconds')->default(0); // Track how long student spent on lesson
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('score')->nullable(); // For quizzes or assignments
            $table->text('notes')->nullable(); // For feedback from teacher
            $table->text('student_notes')->nullable(); // For student's personal notes
            $table->timestamps();
            
            // Ensure a user can only have one progress record per lesson
            $table->unique(['user_id', 'lesson_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_progress');
    }
};