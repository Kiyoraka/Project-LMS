<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DebugController extends Controller
{
    public function checkEnvironment()
    {
        return response()->json([
            'environment' => [
                'APP_ENV' => config('app.env'),
                'APP_URL' => config('app.url'),
                'FILESYSTEM_DISK' => config('filesystems.default'),
                'FILESYSTEM_DISK_ENV' => env('FILESYSTEM_DISK'),
                'FILESYSTEM_DISK_RAW' => $_ENV['FILESYSTEM_DISK'] ?? 'not set',
            ],
            'spaces_config' => [
                'DO_SPACES_KEY' => config('filesystems.disks.spaces.key') ? 'SET' : 'NOT SET',
                'DO_SPACES_SECRET' => config('filesystems.disks.spaces.secret') ? 'SET' : 'NOT SET',
                'DO_SPACES_REGION' => config('filesystems.disks.spaces.region'),
                'DO_SPACES_BUCKET' => config('filesystems.disks.spaces.bucket'),
                'DO_SPACES_ENDPOINT' => config('filesystems.disks.spaces.endpoint'),
                'DO_SPACES_URL' => config('filesystems.disks.spaces.url'),
            ],
            'env_variables' => [
                'DO_SPACES_KEY_ENV' => env('DO_SPACES_KEY') ? 'SET' : 'NOT SET',
                'DO_SPACES_SECRET_ENV' => env('DO_SPACES_SECRET') ? 'SET' : 'NOT SET',
                'DO_SPACES_BUCKET_ENV' => env('DO_SPACES_BUCKET'),
                'DO_SPACES_REGION_ENV' => env('DO_SPACES_REGION'),
            ],
            'disk_check' => [
                'default_disk' => config('filesystems.default'),
                'spaces_disk_exists' => config('filesystems.disks.spaces') ? 'YES' : 'NO',
                'public_disk_exists' => config('filesystems.disks.public') ? 'YES' : 'NO',
            ],
            'runtime' => [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
            ]
        ]);
    }

    public function testSpacesUpload(Request $request)
    {
        try {
            // Create a test file
            $testContent = "Test upload at " . now()->toISOString();
            $testFilename = 'test/test-' . time() . '.txt';

            // Try uploading to spaces explicitly
            $result = Storage::disk('spaces')->put($testFilename, $testContent);

            if ($result) {
                $url = Storage::disk('spaces')->url($testFilename);

                // Try to verify the file exists
                $exists = Storage::disk('spaces')->exists($testFilename);

                // Clean up
                Storage::disk('spaces')->delete($testFilename);

                return response()->json([
                    'success' => true,
                    'message' => 'Spaces upload successful',
                    'url' => $url,
                    'existed' => $exists,
                    'disk_config' => [
                        'driver' => config('filesystems.disks.spaces.driver'),
                        'bucket' => config('filesystems.disks.spaces.bucket'),
                        'region' => config('filesystems.disks.spaces.region'),
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Upload failed',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}