<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lesson extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'module_id',
        'title',
        'description',
        'content_type',
        'content',
        'video_url',
        'video_protected',
        'video_duration',
        'position',
        'duration_minutes',
        'is_published',
        'is_free_preview',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_published' => 'boolean',
        'is_free_preview' => 'boolean',
        'video_protected' => 'boolean',
    ];

    /**
     * Get the module that owns the lesson.
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    /**
     * Get the course through the module.
     */
    public function course()
    {
        return $this->module->course;
    }

    /**
     * Get the progress records for this lesson.
     */
    public function progress(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }

    /**
     * Get progress for a specific student.
     */
    public function getProgressForStudent(User $student)
    {
        return $this->progress()->where('user_id', $student->id)->first();
    }

    /**
     * Check if a student has completed this lesson.
     */
    public function isCompletedByStudent(User $student): bool
    {
        $progress = $this->getProgressForStudent($student);
        return $progress ? $progress->is_completed : false;
    }

    /**
     * Check if the lesson is accessible to a specific student.
     */
    public function isAccessibleToStudent(User $student): bool
    {
        // Free preview lessons are accessible to everyone
        if ($this->is_free_preview) {
            return true;
        }

        // Check if the student is enrolled in the course
        $course = $this->module->course;
        $enrollment = $course->enrollments()->where('user_id', $student->id)
                            ->where('status', 'active')
                            ->exists();
        
        return $enrollment;
    }

    /**
     * Format the video duration for display.
     */
    public function getFormattedDurationAttribute(): string
    {
        if (!$this->video_duration) {
            return 'N/A';
        }

        return $this->video_duration;
    }

    /**
     * Generate a secure URL for protected videos.
     */
    public function getSecureVideoUrl(): string
    {
        if (!$this->video_protected || !$this->video_url) {
            return $this->video_url ?? '';
        }

        // This would be replaced with actual secure URL generation logic
        // For example, signed URLs with expiration, domain restrictions, etc.
        return $this->video_url;
    }

    /**
     * Count the number of students who have completed this lesson.
     */
    public function getCompletionCountAttribute(): int
    {
        return $this->progress()->where('is_completed', true)->count();
    }

    /**
     * Calculate the completion rate as a percentage.
     */
    public function getCompletionRateAttribute(): int
    {
        $course = $this->module->course;
        $enrollmentCount = $course->enrollments()->count();
        
        if ($enrollmentCount === 0) {
            return 0;
        }
        
        return round(($this->completion_count / $enrollmentCount) * 100);
    }
}