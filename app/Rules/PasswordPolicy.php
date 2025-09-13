<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\Setting;

class PasswordPolicy implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Get password policy settings
        $requireUppercase = Setting::get('security', 'passwordPolicy.requireUppercase', true);
        $requireNumber = Setting::get('security', 'passwordPolicy.requireNumber', true);
        $requireSpecialChar = Setting::get('security', 'passwordPolicy.requireSpecialChar', false);
        $minLength = Setting::get('security', 'passwordPolicy.minLength', 8);

        // Convert string booleans to actual booleans if needed
        $requireUppercase = $this->toBool($requireUppercase);
        $requireNumber = $this->toBool($requireNumber);
        $requireSpecialChar = $this->toBool($requireSpecialChar);

        $errors = [];

        // Check minimum length
        if (strlen($value) < $minLength) {
            $errors[] = "at least {$minLength} characters";
        }

        // Check for uppercase letter
        if ($requireUppercase && !preg_match('/[A-Z]/', $value)) {
            $errors[] = "at least one uppercase letter";
        }

        // Check for number
        if ($requireNumber && !preg_match('/[0-9]/', $value)) {
            $errors[] = "at least one number";
        }

        // Check for special character
        if ($requireSpecialChar && !preg_match('/[^A-Za-z0-9]/', $value)) {
            $errors[] = "at least one special character";
        }

        // If there are errors, fail with a descriptive message
        if (!empty($errors)) {
            $message = "Password must contain " . implode(', ', $errors) . ".";
            $fail($message);
        }
    }

    /**
     * Convert various boolean representations to actual boolean.
     *
     * @param mixed $value
     * @return bool
     */
    private function toBool($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        
        if (is_string($value)) {
            return in_array(strtolower($value), ['true', '1', 'yes', 'on']);
        }
        
        if (is_numeric($value)) {
            return (bool) $value;
        }
        
        return false;
    }
}
