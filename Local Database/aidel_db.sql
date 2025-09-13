-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.4.32-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win64
-- HeidiSQL Version:             12.5.0.6677
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for aidel_lms
CREATE DATABASE IF NOT EXISTS `aidel_lms` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;
USE `aidel_lms`;

-- Dumping structure for table aidel_lms.admin_setting
CREATE TABLE IF NOT EXISTS `admin_setting` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table aidel_lms.admin_setting: ~0 rows (approximately)

-- Dumping structure for table aidel_lms.cache
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table aidel_lms.cache: ~0 rows (approximately)

-- Dumping structure for table aidel_lms.cache_locks
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table aidel_lms.cache_locks: ~0 rows (approximately)

-- Dumping structure for table aidel_lms.courses
CREATE TABLE IF NOT EXISTS `courses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `short_description` text DEFAULT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('draft','published','archived') NOT NULL DEFAULT 'draft',
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `level` varchar(255) NOT NULL DEFAULT 'beginner',
  `duration_hours` int(11) NOT NULL DEFAULT 0,
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `courses_slug_unique` (`slug`),
  KEY `courses_user_id_foreign` (`user_id`),
  CONSTRAINT `courses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table aidel_lms.courses: ~1 rows (approximately)
INSERT INTO `courses` (`id`, `user_id`, `title`, `slug`, `description`, `short_description`, `thumbnail`, `price`, `status`, `featured`, `level`, `duration_hours`, `published_at`, `created_at`, `updated_at`) VALUES
	(1, 2, 'Test Title', 'test-title', 'Test full lorum ipsum', 'Test description', NULL, 0.00, 'published', 0, 'beginner', 0, '2025-06-09 00:05:14', '2025-06-09 00:05:14', '2025-06-09 00:05:14');

-- Dumping structure for table aidel_lms.course_enrollments
CREATE TABLE IF NOT EXISTS `course_enrollments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `course_id` bigint(20) unsigned NOT NULL,
  `status` enum('active','completed','expired','refunded') NOT NULL DEFAULT 'active',
  `enrolled_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `payment_amount` decimal(10,2) DEFAULT NULL,
  `payment_method` varchar(255) DEFAULT NULL,
  `payment_status` varchar(255) DEFAULT NULL,
  `payment_id` varchar(255) DEFAULT NULL,
  `progress_percentage` int(11) NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `course_enrollments_user_id_course_id_unique` (`user_id`,`course_id`),
  KEY `course_enrollments_course_id_foreign` (`course_id`),
  CONSTRAINT `course_enrollments_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `course_enrollments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table aidel_lms.course_enrollments: ~0 rows (approximately)

-- Dumping structure for table aidel_lms.failed_jobs
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table aidel_lms.failed_jobs: ~0 rows (approximately)

-- Dumping structure for table aidel_lms.jobs
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table aidel_lms.jobs: ~0 rows (approximately)

-- Dumping structure for table aidel_lms.job_batches
CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table aidel_lms.job_batches: ~0 rows (approximately)

-- Dumping structure for table aidel_lms.lessons
CREATE TABLE IF NOT EXISTS `lessons` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `module_id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `content_type` enum('video','text','quiz','assignment','file') NOT NULL DEFAULT 'text',
  `content` text DEFAULT NULL,
  `video_url` varchar(255) DEFAULT NULL,
  `video_protected` tinyint(1) NOT NULL DEFAULT 0,
  `video_duration` varchar(255) DEFAULT NULL,
  `position` int(11) NOT NULL DEFAULT 0,
  `duration_minutes` int(11) NOT NULL DEFAULT 0,
  `is_published` tinyint(1) NOT NULL DEFAULT 0,
  `is_free_preview` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lessons_module_id_foreign` (`module_id`),
  CONSTRAINT `lessons_module_id_foreign` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table aidel_lms.lessons: ~1 rows (approximately)
INSERT INTO `lessons` (`id`, `module_id`, `title`, `description`, `content_type`, `content`, `video_url`, `video_protected`, `video_duration`, `position`, `duration_minutes`, `is_published`, `is_free_preview`, `created_at`, `updated_at`) VALUES
	(1, 1, '1', 'chapter 1 lorum', 'video', NULL, 'https://youtu.be/ZG7_cAXTXP0', 1, NULL, 1, 5, 1, 0, '2025-06-09 00:05:14', '2025-06-09 00:05:14');

-- Dumping structure for table aidel_lms.lesson_progress
CREATE TABLE IF NOT EXISTS `lesson_progress` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `lesson_id` bigint(20) unsigned NOT NULL,
  `course_enrollment_id` bigint(20) unsigned NOT NULL,
  `is_completed` tinyint(1) NOT NULL DEFAULT 0,
  `progress_percentage` int(11) NOT NULL DEFAULT 0,
  `time_spent_seconds` int(11) NOT NULL DEFAULT 0,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `score` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `student_notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lesson_progress_user_id_lesson_id_unique` (`user_id`,`lesson_id`),
  KEY `lesson_progress_lesson_id_foreign` (`lesson_id`),
  KEY `lesson_progress_course_enrollment_id_foreign` (`course_enrollment_id`),
  CONSTRAINT `lesson_progress_course_enrollment_id_foreign` FOREIGN KEY (`course_enrollment_id`) REFERENCES `course_enrollments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lesson_progress_lesson_id_foreign` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lesson_progress_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table aidel_lms.lesson_progress: ~0 rows (approximately)

-- Dumping structure for table aidel_lms.migrations
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table aidel_lms.migrations: ~12 rows (approximately)
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
	(1, '0001_01_01_000000_create_users_table', 1),
	(2, '0001_01_01_000001_create_cache_table', 1),
	(3, '0001_01_01_000002_create_jobs_table', 1),
	(4, '2023_05_31_000001_add_role_to_users_table', 1),
	(5, '2023_05_31_000002_create_courses_table', 1),
	(6, '2023_05_31_000003_create_modules_table', 1),
	(7, '2023_05_31_000004_create_lessons_table', 1),
	(8, '2023_05_31_000005_create_course_enrollments_table', 1),
	(9, '2023_05_31_000006_create_lesson_progress_table', 1),
	(10, '2025_05_31_165755_create_settings_table', 1),
	(11, '2025_06_06_110040_create_admin_setting_table', 1),
	(12, '2025_06_06_110057_create_user_settings_table', 1);

-- Dumping structure for table aidel_lms.modules
CREATE TABLE IF NOT EXISTS `modules` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `course_id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `position` int(11) NOT NULL DEFAULT 0,
  `is_published` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `modules_course_id_foreign` (`course_id`),
  CONSTRAINT `modules_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table aidel_lms.modules: ~1 rows (approximately)
INSERT INTO `modules` (`id`, `course_id`, `title`, `description`, `position`, `is_published`, `created_at`, `updated_at`) VALUES
	(1, 1, 'Course Videos', 'Video lessons for Test Title', 1, 1, '2025-06-09 00:05:14', '2025-06-09 00:05:14');

-- Dumping structure for table aidel_lms.password_reset_tokens
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table aidel_lms.password_reset_tokens: ~0 rows (approximately)

-- Dumping structure for table aidel_lms.sessions
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table aidel_lms.sessions: ~1 rows (approximately)
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
	('qDrdkq4KRkTUnH6Y9u0tXxF1ivKAx2GbvoSUQdlp', 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiZllSOXhWcHZ3MU9vMEFaUktjRU9rN0oyQzQzb0txQUQ3UEVTTTFFeCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NjA6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hcGkvdGVhY2hlci9lbnJvbGxtZW50LWNoYXJ0P3BlcmlvZD0xMiI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjI7fQ==', 1749459801);

-- Dumping structure for table aidel_lms.settings
CREATE TABLE IF NOT EXISTS `settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `group` varchar(255) NOT NULL COMMENT 'Settings category (general, appearance, email, etc.)',
  `key` varchar(255) NOT NULL COMMENT 'Setting identifier',
  `value` text DEFAULT NULL COMMENT 'Setting value',
  `value_type` varchar(255) NOT NULL DEFAULT 'string' COMMENT 'Type of value (string, boolean, integer, json, etc.)',
  `description` varchar(255) DEFAULT NULL COMMENT 'Setting description',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_group_key_unique` (`group`,`key`),
  KEY `settings_group_index` (`group`),
  KEY `settings_key_index` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table aidel_lms.settings: ~34 rows (approximately)
INSERT INTO `settings` (`id`, `group`, `key`, `value`, `value_type`, `description`, `created_at`, `updated_at`) VALUES
	(1, 'general', 'site_name', 'Aidel LMS', 'string', 'The name of the site', '2025-06-07 06:08:45', '2025-06-07 06:08:45'),
	(2, 'general', 'site_description', 'Advanced Learning Management System', 'string', 'The site description', '2025-06-07 06:08:45', '2025-06-07 06:08:45'),
	(3, 'general', 'allow_registrations', '1', 'boolean', 'Whether to allow new user registrations', '2025-06-07 06:08:45', '2025-06-07 06:08:45'),
	(4, 'general', 'default_role', 'student', 'string', 'Default role for new registrations', '2025-06-07 06:08:45', '2025-06-07 06:08:45'),
	(5, 'general', 'admin_email', 'admin@aidel.com', 'string', 'System administrator email', '2025-06-07 06:08:45', '2025-06-07 06:08:45'),
	(6, 'appearance', 'theme', 'default', 'string', 'The active theme', '2025-06-07 06:08:45', '2025-06-07 06:08:45'),
	(7, 'appearance', 'primary_color', '#7c3aed', 'string', 'Primary brand color', '2025-06-07 06:08:45', '2025-06-07 06:08:45'),
	(8, 'appearance', 'secondary_color', '#6366f1', 'string', 'Secondary brand color', '2025-06-07 06:08:45', '2025-06-07 06:08:45'),
	(9, 'appearance', 'logo_path', '/assets/images/logo.svg', 'string', 'Path to the site logo', '2025-06-07 06:08:45', '2025-06-07 06:08:45'),
	(10, 'appearance', 'favicon_path', '/favicon.ico', 'string', 'Path to the favicon', '2025-06-07 06:08:45', '2025-06-07 06:08:45'),
	(11, 'appearance', 'show_footer', '1', 'boolean', 'Whether to show the footer', '2025-06-07 06:08:45', '2025-06-07 06:08:45'),
	(12, 'email', 'from_address', 'noreply@aidel.com', 'string', 'Email address for outgoing emails', '2025-06-07 06:08:45', '2025-06-07 06:08:45'),
	(13, 'email', 'from_name', 'Aidel LMS', 'string', 'Name for outgoing emails', '2025-06-07 06:08:45', '2025-06-07 06:08:45'),
	(14, 'email', 'notification_new_user', '1', 'boolean', 'Send notification on new user registration', '2025-06-07 06:08:45', '2025-06-07 06:08:45'),
	(15, 'email', 'notification_new_course', '1', 'boolean', 'Send notification on new course creation', '2025-06-07 06:08:45', '2025-06-07 06:08:45'),
	(16, 'email', 'notification_course_completion', '1', 'boolean', 'Send notification on course completion', '2025-06-07 06:08:45', '2025-06-07 06:08:45'),
	(17, 'email', 'notification_payment', '1', 'boolean', 'Send notification on payment receipt', '2025-06-07 06:08:45', '2025-06-07 06:08:45'),
	(18, 'payment', 'currency', 'USD', 'string', 'Default currency for payments', '2025-06-07 06:08:45', '2025-06-07 06:08:45'),
	(19, 'payment', 'stripe_enabled', '1', 'boolean', 'Whether Stripe payments are enabled', '2025-06-07 06:08:45', '2025-06-07 06:08:45'),
	(20, 'payment', 'stripe_test_mode', '1', 'boolean', 'Whether Stripe is in test mode', '2025-06-07 06:08:45', '2025-06-07 06:08:45'),
	(21, 'payment', 'stripe_key', 'pk_test_placeholder', 'string', 'Stripe publishable key (placeholder)', '2025-06-07 06:08:45', '2025-06-07 06:08:45'),
	(22, 'payment', 'stripe_secret', 'sk_test_placeholder', 'string', 'Stripe secret key (placeholder)', '2025-06-07 06:08:45', '2025-06-07 06:08:45'),
	(23, 'payment', 'paypal_enabled', '1', 'boolean', 'Whether PayPal payments are enabled', '2025-06-07 06:08:45', '2025-06-07 06:08:45'),
	(24, 'payment', 'paypal_test_mode', '1', 'boolean', 'Whether PayPal is in test mode', '2025-06-07 06:08:45', '2025-06-07 06:08:45'),
	(25, 'payment', 'paypal_client_id', 'test_client_id_placeholder', 'string', 'PayPal client ID (placeholder)', '2025-06-07 06:08:45', '2025-06-07 06:08:45'),
	(26, 'payment', 'paypal_secret', 'test_secret_placeholder', 'string', 'PayPal secret (placeholder)', '2025-06-07 06:08:45', '2025-06-07 06:08:45'),
	(27, 'security', 'max_file_size', '50', 'integer', 'Maximum file size in MB', '2025-06-07 06:08:45', '2025-06-07 06:08:45'),
	(28, 'security', 'allowed_file_types', '["pdf","doc","docx","jpg","png","mp4"]', 'string', 'Allowed file upload types', '2025-06-07 06:08:45', '2025-06-07 06:08:45'),
	(29, 'security', 'require_email_verification', '1', 'boolean', 'Whether email verification is required', '2025-06-07 06:08:45', '2025-06-07 06:08:45'),
	(30, 'security', 'password_min_length', '8', 'integer', 'Minimum password length', '2025-06-07 06:08:45', '2025-06-07 06:08:45'),
	(31, 'security', 'password_requires_mixed_case', '1', 'boolean', 'Whether password requires mixed case', '2025-06-07 06:08:45', '2025-06-07 06:08:45'),
	(32, 'security', 'password_requires_number', '1', 'boolean', 'Whether password requires a number', '2025-06-07 06:08:45', '2025-06-07 06:08:45'),
	(33, 'security', 'password_requires_symbol', '1', 'boolean', 'Whether password requires a symbol', '2025-06-07 06:08:45', '2025-06-07 06:08:45'),
	(34, 'security', 'login_throttling', '1', 'boolean', 'Whether to enable login throttling', '2025-06-07 06:08:45', '2025-06-07 06:08:45');

-- Dumping structure for table aidel_lms.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'student',
  `profile_picture` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table aidel_lms.users: ~6 rows (approximately)
INSERT INTO `users` (`id`, `name`, `email`, `role`, `profile_picture`, `bio`, `phone`, `is_active`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
	(1, 'Super Admin', 'admin@aidel.com', 'super_admin', NULL, NULL, NULL, 1, NULL, '$2y$12$J7Cdm0VYG3d8wtwPquoEd.fnlxVjqukt0.q6d1fN8/SAVYOsmrywS', NULL, '2025-06-07 06:08:45', '2025-06-07 06:08:45'),
	(2, 'Test Teacher', 'teacher@aidel.com', 'teacher', NULL, NULL, NULL, 1, NULL, '$2y$12$jSdIlHLE7W234Gp44lmtoeasj8QXUG4GG5HyG.WTye1heguAL6S.W', NULL, '2025-06-07 06:08:45', '2025-06-07 06:08:45'),
	(3, 'Test Student', 'student@aidel.com', 'student', NULL, NULL, NULL, 1, NULL, '$2y$12$e4T99pZPVOX76QQOKcd6SOlbioFEji1Gpq/Rjk1aFWgRwKZ9mQ2b6', NULL, '2025-06-07 06:08:45', '2025-06-07 06:08:45'),
	(4, 'Super Admin', 'admin@example.com', 'super_admin', NULL, NULL, NULL, 1, NULL, '$2y$12$4ZSC2XhzFnjoS4jlSmFKruWMSbfSLxQgN1dCcgQVcWWoVOfiVN5u6', NULL, '2025-06-07 06:09:11', '2025-06-07 06:09:11'),
	(5, 'Test Teacher', 'teacher@example.com', 'teacher', NULL, NULL, NULL, 1, NULL, '$2y$12$Cwnx1DF7hO5fN6CwWrxace3fPwmYfueNh7g/SYDbB40LnUmOjLgpO', NULL, '2025-06-07 06:09:11', '2025-06-07 06:09:11'),
	(6, 'Test Student', 'student@example.com', 'student', NULL, NULL, NULL, 1, NULL, '$2y$12$AncqWuujMPSmZIOzkK1NgOElqtzWnLAdxDU9Cc.CvLVkdAAyBA8hi', NULL, '2025-06-07 06:09:11', '2025-06-07 06:09:11');

-- Dumping structure for table aidel_lms.user_settings
CREATE TABLE IF NOT EXISTS `user_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table aidel_lms.user_settings: ~0 rows (approximately)

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
