<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class LoginAttempt extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'ip_address',
        'type',
        'attempts',
        'last_attempt_at',
        'locked_until',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'last_attempt_at' => 'datetime',
        'locked_until' => 'datetime',
        'attempts' => 'integer',
    ];

    /**
     * Record a failed login attempt.
     *
     * @param string $email
     * @param string $ipAddress
     * @return void
     */
    public static function recordAttempt(string $email, string $ipAddress): void
    {
        $attempt = static::where('email', $email)
            ->where('ip_address', $ipAddress)
            ->where('type', 'login')
            ->first();

        if ($attempt) {
            $attempt->increment('attempts');
            $attempt->update(['last_attempt_at' => now()]);
        } else {
            static::create([
                'email' => $email,
                'ip_address' => $ipAddress,
                'type' => 'login',
                'attempts' => 1,
                'last_attempt_at' => now(),
            ]);
        }
    }

    /**
     * Check if an email/IP combination is locked out.
     *
     * @param string $email
     * @param string $ipAddress
     * @return bool
     */
    public static function isLockedOut(string $email, string $ipAddress): bool
    {
        $attempt = static::where('email', $email)
            ->where('ip_address', $ipAddress)
            ->where('type', 'login')
            ->first();

        if (!$attempt) {
            return false;
        }

        // Check if locked until timestamp has passed
        if ($attempt->locked_until && $attempt->locked_until->isFuture()) {
            return true;
        }

        return false;
    }

    /**
     * Get remaining lockout time in minutes.
     *
     * @param string $email
     * @param string $ipAddress
     * @return int
     */
    public static function getLockoutTime(string $email, string $ipAddress): int
    {
        $attempt = static::where('email', $email)
            ->where('ip_address', $ipAddress)
            ->where('type', 'login')
            ->first();

        if (!$attempt || !$attempt->locked_until) {
            return 0;
        }

        return max(0, $attempt->locked_until->diffInMinutes(now()));
    }

    /**
     * Apply lockout if max attempts reached.
     *
     * @param string $email
     * @param string $ipAddress
     * @param int $maxAttempts
     * @param int $lockoutMinutes
     * @return void
     */
    public static function applyLockoutIfNeeded(string $email, string $ipAddress, int $maxAttempts, int $lockoutMinutes): void
    {
        $attempt = static::where('email', $email)
            ->where('ip_address', $ipAddress)
            ->where('type', 'login')
            ->first();

        if ($attempt && $attempt->attempts >= $maxAttempts) {
            $attempt->update([
                'locked_until' => now()->addMinutes($lockoutMinutes)
            ]);
        }
    }

    /**
     * Clear attempts for successful login.
     *
     * @param string $email
     * @param string $ipAddress
     * @return void
     */
    public static function clearAttempts(string $email, string $ipAddress): void
    {
        static::where('email', $email)
            ->where('ip_address', $ipAddress)
            ->where('type', 'login')
            ->delete();
    }

    /**
     * Clear expired lockouts.
     *
     * @return void
     */
    public static function clearExpiredLockouts(): void
    {
        static::where('locked_until', '<', now())->delete();
    }

    /**
     * Record a registration attempt.
     *
     * @param string $email
     * @param string $ipAddress
     * @param bool $successful
     * @return void
     */
    public static function recordRegistrationAttempt(string $email, string $ipAddress, bool $successful = true): void
    {
        $attempt = static::where('email', $email)
            ->where('ip_address', $ipAddress)
            ->where('type', 'registration')
            ->first();

        if ($attempt) {
            if (!$successful) {
                $attempt->increment('attempts');
                $attempt->update(['last_attempt_at' => now()]);
            }
        } else {
            static::create([
                'email' => $email,
                'ip_address' => $ipAddress,
                'type' => 'registration',
                'attempts' => $successful ? 0 : 1,
                'last_attempt_at' => now(),
            ]);
        }
    }

    /**
     * Check if registration is rate limited.
     *
     * @param string $email
     * @param string $ipAddress
     * @return bool
     */
    public static function isRegistrationLimited(string $email, string $ipAddress): bool
    {
        $maxAttempts = Setting::get('security', 'maxRegistrationAttempts', 3);
        $lockoutTime = Setting::get('security', 'registrationLockoutTime', 60);

        $attempt = static::where('email', $email)
            ->where('ip_address', $ipAddress)
            ->where('type', 'registration')
            ->first();

        if (!$attempt) {
            return false;
        }

        // Check if locked until timestamp has passed
        if ($attempt->locked_until && $attempt->locked_until->isFuture()) {
            return true;
        }

        // Apply lockout if needed
        if ($attempt->attempts >= $maxAttempts) {
            $attempt->update([
                'locked_until' => now()->addMinutes($lockoutTime)
            ]);
            return true;
        }

        return false;
    }

    /**
     * Get remaining registration limit time.
     *
     * @param string $email
     * @param string $ipAddress
     * @return int
     */
    public static function getRegistrationLimitTime(string $email, string $ipAddress): int
    {
        $attempt = static::where('email', $email)
            ->where('ip_address', $ipAddress)
            ->where('type', 'registration')
            ->first();

        if (!$attempt || !$attempt->locked_until) {
            return 0;
        }

        return max(0, $attempt->locked_until->diffInMinutes(now()));
    }

    /**
     * Clear registration attempts.
     *
     * @param string $email
     * @param string $ipAddress
     * @return void
     */
    public static function clearRegistrationAttempts(string $email, string $ipAddress): void
    {
        static::where('email', $email)
            ->where('ip_address', $ipAddress)
            ->where('type', 'registration')
            ->delete();
    }
}
