<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class StorageHelper
{
    /**
     * Get the proper public URL for a storage file.
     * This handles both local development and DigitalOcean production environments.
     *
     * @param string|null $path The storage path (e.g., 'course-thumbnails/image.png')
     * @return string|null The public URL or null if path is empty
     */
    public static function getPublicUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        $isProduction = config('app.env') === 'production';
        $defaultDisk = config('filesystems.default');
        $shouldUseSpaces = $isProduction && $defaultDisk === 'spaces';

        // Log the URL generation for debugging
        Log::info('🔍 StorageHelper::getPublicUrl DEBUG', [
            'input_path' => $path,
            'app_env' => config('app.env'),
            'is_production' => $isProduction,
            'app_url' => config('app.url'),
            'filesystem_disk_config' => $defaultDisk,
            'filesystem_disk_env' => env('FILESYSTEM_DISK'),
            'spaces_configured' => config('filesystems.disks.spaces.key') ? 'YES' : 'NO',
            'spaces_bucket' => config('filesystems.disks.spaces.bucket'),
            'should_use_spaces' => $shouldUseSpaces ? 'YES' : 'NO',
            'decision' => $shouldUseSpaces ? 'WILL USE SPACES' : 'WILL USE LOCAL/PUBLIC'
        ]);

        // For production with DigitalOcean Spaces
        if ($shouldUseSpaces) {
            // Build the Spaces URL manually to ensure it's correct
            // Don't rely on Storage::disk('spaces')->url() as it may not use the CDN URL
            $spacesUrl = config('filesystems.disks.spaces.url', 'https://kiyoraka-world.sgp1.digitaloceanspaces.com');
            $spacesUrl = rtrim($spacesUrl, '/');
            $url = $spacesUrl . '/' . ltrim($path, '/');

            Log::info('✅ Generated Spaces URL', [
                'url' => $url,
                'path' => $path,
                'method' => 'manual_spaces_url',
                'spaces_base_url' => $spacesUrl
            ]);

            return $url;
        }

        // For production on DigitalOcean without Spaces
        if (config('app.env') === 'production') {
            // Check if the file exists
            if (!Storage::disk('public')->exists($path)) {
                Log::warning('File not found in storage', [
                    'path' => $path,
                    'full_path' => Storage::disk('public')->path($path)
                ]);
            }

            // For DigitalOcean, we need to use the APP_URL directly
            $appUrl = rtrim(config('app.url'), '/');
            $url = $appUrl . '/storage/' . ltrim($path, '/');

            Log::debug('Generated production URL', [
                'url' => $url,
                'method' => 'direct_concatenation'
            ]);

            return $url;
        }

        // For local development, use the standard Storage::url method
        $url = Storage::url($path);

        Log::debug('Generated local URL', [
            'url' => $url,
            'method' => 'Storage::url'
        ]);

        return $url;
    }

    /**
     * Check if a storage file exists and is accessible
     *
     * @param string|null $path
     * @return bool
     */
    public static function fileExists(?string $path): bool
    {
        if (empty($path)) {
            return false;
        }

        $isProduction = config('app.env') === 'production';
        $defaultDisk = config('filesystems.default');
        $shouldUseSpaces = $isProduction && $defaultDisk === 'spaces';

        // Check in Spaces if in production with Spaces configured
        if ($shouldUseSpaces) {
            return Storage::disk('spaces')->exists($path);
        }

        return Storage::disk('public')->exists($path);
    }

    /**
     * Get file size in a human-readable format
     *
     * @param string|null $path
     * @return string|null
     */
    public static function getFileSize(?string $path): ?string
    {
        if (empty($path) || !self::fileExists($path)) {
            return null;
        }

        $isProduction = config('app.env') === 'production';
        $defaultDisk = config('filesystems.default');
        $shouldUseSpaces = $isProduction && $defaultDisk === 'spaces';

        // Get size from appropriate disk
        $disk = $shouldUseSpaces ? 'spaces' : 'public';
        $bytes = Storage::disk($disk)->size($path);

        $units = ['B', 'KB', 'MB', 'GB'];
        $factor = floor((strlen($bytes) - 1) / 3);

        return sprintf("%.2f", $bytes / pow(1024, $factor)) . ' ' . $units[$factor];
    }
}