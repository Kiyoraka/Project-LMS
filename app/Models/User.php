<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'role',
        'profile_picture',
        'bio',
        'phone',
        'is_active',
        'password',
        'last_login',
        'email_verified_at',
        'youtube_url',
        'linkedin_url',
        'twitter_url',
        'facebook_url',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login' => 'datetime',
        ];
    }

    /**
     * Check if the user is a super admin.
     *
     * @return bool
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Check if the user is a teacher.
     *
     * @return bool
     */
    public function isTeacher(): bool
    {
        return $this->role === 'teacher';
    }

    /**
     * Check if the user is a student.
     *
     * @return bool
     */
    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    /**
     * Get the dashboard path based on user role.
     *
     * @return string
     */
    public function getDashboardPath(): string
    {
        if ($this->isSuperAdmin()) {
            return '/admin/dashboard';
        } elseif ($this->isTeacher()) {
            return '/teacher/dashboard';
        } else {
            return '/student/dashboard';
        }
    }

    /**
     * Get the user's course enrollments.
     */
    public function enrollments()
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    /**
     * Get the courses taught by this user (for teachers).
     */
    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    /**
     * Get the user's lesson progress records.
     */
    public function lessonProgress()
    {
        return $this->hasMany(LessonProgress::class);
    }
}
