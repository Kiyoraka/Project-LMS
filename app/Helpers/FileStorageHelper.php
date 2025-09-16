<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;

class FileStorageHelper
{
    /**
     * Get the appropriate storage disk based on environment
     */
    public static function getDisk()
    {
        if (config('app.env') === 'production' && config('filesystems.disks.spaces')) {
            return 'spaces';
        }
        return 'public';
    }

    /**
     * Store a file and return its path
     */
    public static function storeFile($file, $directory)
    {
        $disk = self::getDisk();
        $path = $file->store($directory, $disk);

        return $path;
    }

    /**
     * Get the public URL for a stored file
     */
    public static function getFileUrl($path)
    {
        if (!$path) {
            return null;
        }

        $disk = self::getDisk();

        if ($disk === 'spaces') {
            // For DigitalOcean Spaces, return the full URL
            return Storage::disk('spaces')->url($path);
        }

        // For local storage, use the existing helper
        return StorageHelper::getPublicUrl($path);
    }

    /**
     * Delete a file from storage
     */
    public static function deleteFile($path)
    {
        if (!$path) {
            return;
        }

        $disk = self::getDisk();
        Storage::disk($disk)->delete($path);
    }

    /**
     * Check if we're using cloud storage
     */
    public static function isUsingCloudStorage()
    {
        return self::getDisk() === 'spaces';
    }
}