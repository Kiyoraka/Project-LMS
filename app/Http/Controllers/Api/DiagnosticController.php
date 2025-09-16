<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class DiagnosticController extends Controller
{
    /**
     * Check storage configuration and debug storage issues
     */
    public function checkStorage()
    {
        $diagnostics = [];

        // Check if storage link exists
        $storageLinkPath = public_path('storage');
        $diagnostics['storage_link_exists'] = is_link($storageLinkPath);
        $diagnostics['storage_link_path'] = $storageLinkPath;

        // Check if storage link is readable
        if ($diagnostics['storage_link_exists']) {
            $diagnostics['storage_link_readable'] = is_readable($storageLinkPath);
            $diagnostics['storage_link_target'] = readlink($storageLinkPath);
        }

        // Check storage directories
        $diagnostics['storage_app_exists'] = File::exists(storage_path('app'));
        $diagnostics['storage_public_exists'] = File::exists(storage_path('app/public'));
        $diagnostics['course_thumbnails_dir_exists'] = File::exists(storage_path('app/public/course-thumbnails'));

        // Check public storage directory
        $diagnostics['public_storage_exists'] = File::exists(public_path('storage'));
        $diagnostics['public_course_thumbnails_exists'] = File::exists(public_path('storage/course-thumbnails'));

        // Get storage disk configuration
        $diagnostics['storage_disk'] = config('filesystems.default');
        $diagnostics['public_disk_url'] = config('filesystems.disks.public.url');
        $diagnostics['app_url'] = config('app.url');

        // Test creating a file
        try {
            $testContent = 'Storage test at ' . now()->toISOString();
            Storage::disk('public')->put('test-file.txt', $testContent);
            $diagnostics['test_file_created'] = true;
            $diagnostics['test_file_path'] = Storage::disk('public')->path('test-file.txt');
            $diagnostics['test_file_url'] = Storage::disk('public')->url('test-file.txt');
            $diagnostics['test_file_exists'] = Storage::disk('public')->exists('test-file.txt');

            // Clean up test file
            Storage::disk('public')->delete('test-file.txt');
        } catch (\Exception $e) {
            $diagnostics['test_file_error'] = $e->getMessage();
        }

        // Get sample course with thumbnail
        $courseWithThumbnail = Course::whereNotNull('thumbnail')->first();
        if ($courseWithThumbnail) {
            $diagnostics['sample_course'] = [
                'id' => $courseWithThumbnail->id,
                'title' => $courseWithThumbnail->title,
                'thumbnail_path' => $courseWithThumbnail->thumbnail,
                'storage_exists' => Storage::disk('public')->exists($courseWithThumbnail->thumbnail),
                'storage_url' => Storage::url($courseWithThumbnail->thumbnail),
                'storage_path' => Storage::disk('public')->path($courseWithThumbnail->thumbnail),
                'file_exists_in_storage' => File::exists(storage_path('app/public/' . $courseWithThumbnail->thumbnail)),
                'file_exists_in_public' => File::exists(public_path('storage/' . $courseWithThumbnail->thumbnail)),
            ];
        }

        // Log diagnostics for debugging
        Log::info('Storage Diagnostics', $diagnostics);

        return response()->json([
            'success' => true,
            'diagnostics' => $diagnostics,
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Fix storage link for DigitalOcean
     */
    public function fixStorageLink()
    {
        try {
            // Remove existing symlink if it exists
            $linkPath = public_path('storage');
            if (is_link($linkPath)) {
                unlink($linkPath);
                Log::info('Removed existing storage symlink');
            }

            // Create new symlink
            $targetPath = storage_path('app/public');
            symlink($targetPath, $linkPath);

            Log::info('Created new storage symlink', [
                'target' => $targetPath,
                'link' => $linkPath
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Storage link recreated successfully',
                'target' => $targetPath,
                'link' => $linkPath
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fix storage link', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fix storage link',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * List all course thumbnails
     */
    public function listThumbnails()
    {
        $thumbnails = [];

        // Get all files in course-thumbnails directory
        if (Storage::disk('public')->exists('course-thumbnails')) {
            $files = Storage::disk('public')->files('course-thumbnails');
            foreach ($files as $file) {
                $thumbnails[] = [
                    'path' => $file,
                    'url' => Storage::url($file),
                    'size' => Storage::disk('public')->size($file),
                    'last_modified' => date('Y-m-d H:i:s', Storage::disk('public')->lastModified($file)),
                    'exists_in_storage' => File::exists(storage_path('app/public/' . $file)),
                    'exists_in_public' => File::exists(public_path('storage/' . $file))
                ];
            }
        }

        return response()->json([
            'success' => true,
            'count' => count($thumbnails),
            'thumbnails' => $thumbnails
        ]);
    }
}