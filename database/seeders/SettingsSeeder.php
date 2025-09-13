<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // General settings
        Setting::set('general', 'siteName', 'Learning Management System Prototype', 'The name of the site');
        Setting::set('general', 'site_description', 'Advanced Learning Management System', 'The site description');
        Setting::set('general', 'timezone', 'UTC', 'Default timezone for the system');
        Setting::set('general', 'dateFormat', 'MM/DD/YYYY', 'Default date format for the system');
        Setting::set('general', 'language', 'en', 'Default language for the system');
        Setting::set('general', 'maintenanceMode', false, 'Whether the system is in maintenance mode');
        Setting::set('general', 'logoPath', '', 'Path to the uploaded site logo');
        Setting::set('general', 'faviconPath', '', 'Path to the uploaded favicon');
        Setting::set('general', 'allow_registrations', true, 'Allow new user registrations');
        Setting::set('general', 'default_role', 'student', 'Default role for new registrations');
        Setting::set('general', 'admin_email', 'admin@demo.com', 'System administrator email');
        
        // Appearance settings
        Setting::set('appearance', 'theme', 'default', 'The active theme');
        Setting::set('appearance', 'primary_color', '#3b82f6', 'Primary brand color');
        Setting::set('appearance', 'secondary_color', '#1d4ed8', 'Secondary brand color');
        Setting::set('appearance', 'logo_path', '/assets/images/logo.svg', 'Path to the site logo');
        Setting::set('appearance', 'favicon_path', '/favicon.ico', 'Path to the favicon');
        Setting::set('appearance', 'show_footer', true, 'Whether to show the footer');
        
        // Email settings
        Setting::set('email', 'from_address', 'noreply@demo.com', 'Email address for outgoing emails');
        Setting::set('email', 'from_name', 'LMS Prototype', 'Name for outgoing emails');
        Setting::set('email', 'notification_new_user', true, 'Send notification on new user registration');
        Setting::set('email', 'notification_new_course', true, 'Send notification on new course creation');
        Setting::set('email', 'notification_course_completion', true, 'Send notification on course completion');
        Setting::set('email', 'notification_payment', true, 'Send notification on payment receipt');
        
        // Payment settings
        Setting::set('payment', 'currency', 'USD', 'Default currency for payments');
        Setting::set('payment', 'stripe_enabled', true, 'Whether Stripe payments are enabled');
        Setting::set('payment', 'stripe_test_mode', true, 'Whether Stripe is in test mode');
        Setting::set('payment', 'stripe_key', 'pk_test_placeholder', 'Stripe publishable key (placeholder)');
        Setting::set('payment', 'stripe_secret', 'sk_test_placeholder', 'Stripe secret key (placeholder)');
        Setting::set('payment', 'paypal_enabled', true, 'Whether PayPal payments are enabled');
        Setting::set('payment', 'paypal_test_mode', true, 'Whether PayPal is in test mode');
        Setting::set('payment', 'paypal_client_id', 'test_client_id_placeholder', 'PayPal client ID (placeholder)');
        Setting::set('payment', 'paypal_secret', 'test_secret_placeholder', 'PayPal secret (placeholder)');
        
        // Security settings
        Setting::set('security', 'passwordPolicy.requireUppercase', true, 'Require uppercase letters in passwords');
        Setting::set('security', 'passwordPolicy.requireNumber', true, 'Require numbers in passwords');
        Setting::set('security', 'passwordPolicy.requireSpecialChar', false, 'Require special characters in passwords');
        Setting::set('security', 'passwordPolicy.minLength', 8, 'Minimum password length');
        Setting::set('security', 'maxLoginAttempts', 5, 'Maximum login attempts before lockout');
        Setting::set('security', 'lockoutTime', 30, 'Lockout time in minutes after max attempts');
        
        // Registration Security Settings
        Setting::set('security', 'maxRegistrationAttempts', 3, 'Maximum registration attempts before lockout');
        Setting::set('security', 'registrationLockoutTime', 60, 'Registration lockout time in minutes');
        Setting::set('security', 'blocked_email_domains', '', 'Comma-separated list of blocked email domains');
        
        // Keep existing security settings for backwards compatibility
        Setting::set('security', 'max_file_size', 50, 'Maximum file size in MB');
        Setting::set('security', 'allowed_file_types', json_encode(['pdf', 'doc', 'docx', 'jpg', 'png', 'mp4']), 'Allowed file upload types');
        Setting::set('security', 'require_email_verification', true, 'Whether email verification is required');
    }
}
