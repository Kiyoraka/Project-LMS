<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonProgress extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'lesson_id',
        'course_enrollment_id',
        'is_completed',
        'progress_percentage',
        'time_spent_seconds',
        'started_at',
        'completed_at',
        'score',
        'notes',
        'student_notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_completed' => 'boolean',
        'progress_percentage' => 'integer',
        'time_spent_seconds' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'score' => 'integer',
    ];

    /**
     * Get the student that owns the progress record.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the lesson that this progress record belongs to.
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    /**
     * Get the course enrollment that this progress record belongs to.
     */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(CourseEnrollment::class, 'course_enrollment_id');
    }

    /**
     * Mark the lesson as completed.
     */
    public function markAsCompleted(): self
    {
        if (!$this->is_completed) {
            $this->update([
                'is_completed' => true,
                'progress_percentage' => 100,
                'completed_at' => now(),
            ]);

            // Update the enrollment progress
            $this->enrollment->updateProgress();
        }

        return $this;
    }

    /**
     * Update progress percentage.
     */
    public function updateProgress(int $percentage): self
    {
        $this->update([
            'progress_percentage' => min(100, max(0, $percentage)),
        ]);

        if ($percentage >= 100) {
            $this->markAsCompleted();
        }

        return $this;
    }

    /**
     * Record time spent on the lesson.
     */
    public function addTimeSpent(int $seconds): self
    {
        $this->update([
            'time_spent_seconds' => $this->time_spent_seconds + max(0, $seconds),
        ]);

        return $this;
    }

    /**
     * Set score for the lesson (for quizzes and assignments).
     */
    public function setScore(int $score): self
    {
        $this->update([
            'score' => min(100, max(0, $score)),
        ]);

        return $this;
    }

    /**
     * Add teacher notes to the progress record.
     */
    public function addTeacherNotes(string $notes): self
    {
        $this->update([
            'notes' => $notes,
        ]);

        return $this;
    }

    /**
     * Get formatted time spent.
     */
    public function getFormattedTimeSpentAttribute(): string
    {
        $seconds = $this->time_spent_seconds;
        
        if ($seconds < 60) {
            return "$seconds seconds";
        }
        
        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;
        
        if ($minutes < 60) {
            return "$minutes min $remainingSeconds sec";
        }
        
        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;
        
        return "$hours hr $remainingMinutes min";
    }
}