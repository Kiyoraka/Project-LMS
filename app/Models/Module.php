<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'course_id',
        'title',
        'description',
        'position',
        'is_published',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_published' => 'boolean',
    ];

    /**
     * Get the course that owns the module.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the lessons for the module.
     */
    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('position');
    }

    /**
     * Get the published lessons for the module.
     */
    public function publishedLessons()
    {
        return $this->lessons()->where('is_published', true);
    }

    /**
     * Get the count of lessons in this module.
     */
    public function getLessonCountAttribute(): int
    {
        return $this->lessons()->count();
    }

    /**
     * Get the count of published lessons in this module.
     */
    public function getPublishedLessonCountAttribute(): int
    {
        return $this->publishedLessons()->count();
    }

    /**
     * Check if the module has any lessons.
     */
    public function hasLessons(): bool
    {
        return $this->lessons()->exists();
    }

    /**
     * Calculate the total duration of the module in minutes.
     */
    public function getTotalDurationAttribute(): int
    {
        return $this->lessons()->sum('duration_minutes');
    }

    /**
     * Calculate the completion percentage for a specific student.
     */
    public function getCompletionPercentage(User $student): int
    {
        $lessonCount = $this->lesson_count;
        
        if ($lessonCount === 0) {
            return 0;
        }
        
        $completedLessons = $this->lessons()
            ->whereHas('progress', function ($query) use ($student) {
                $query->where('user_id', $student->id)
                      ->where('is_completed', true);
            })
            ->count();
            
        return $lessonCount > 0 ? round(($completedLessons / $lessonCount) * 100) : 0;
    }
}