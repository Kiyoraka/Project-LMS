<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DebugController extends Controller
{
    public function checkEnvironment()
    {
        // Check if config is cached
        $configCached = file_exists(base_path('bootstrap/cache/config.php'));

        // Try to get raw environment values
        $rawEnvValues = [];
        if (file_exists(base_path('.env'))) {
            $envContent = file_get_contents(base_path('.env'));
            foreach (explode("\n", $envContent) as $line) {
                if (strpos($line, '=') !== false && !str_starts_with(trim($line), '#')) {
                    list($key, $value) = explode('=', $line, 2);
                    $rawEnvValues[trim($key)] = trim($value);
                }
            }
        }

        return response()->json([
            'config_status' => [
                'config_cached' => $configCached ? 'YES - Config is cached!' : 'NO - Config not cached',
                'cache_file_exists' => file_exists(base_path('bootstrap/cache/config.php')) ? 'YES' : 'NO',
                'env_file_exists' => file_exists(base_path('.env')) ? 'YES' : 'NO',
            ],
            'environment' => [
                'APP_ENV' => config('app.env'),
                'APP_URL' => config('app.url'),
                'FILESYSTEM_DISK_config' => config('filesystems.default'),
                'FILESYSTEM_DISK_env' => env('FILESYSTEM_DISK'),
                'FILESYSTEM_DISK_ENV_raw' => $_ENV['FILESYSTEM_DISK'] ?? 'not set in $_ENV',
                'FILESYSTEM_DISK_getenv' => getenv('FILESYSTEM_DISK') ?: 'not set in getenv',
                'FILESYSTEM_DISK_from_file' => $rawEnvValues['FILESYSTEM_DISK'] ?? 'not found in .env file',
            ],
            'spaces_config' => [
                'DO_SPACES_KEY' => config('filesystems.disks.spaces.key') ? 'SET' : 'NOT SET',
                'DO_SPACES_SECRET' => config('filesystems.disks.spaces.secret') ? 'SET' : 'NOT SET',
                'DO_SPACES_REGION' => config('filesystems.disks.spaces.region'),
                'DO_SPACES_BUCKET' => config('filesystems.disks.spaces.bucket'),
                'DO_SPACES_ENDPOINT' => config('filesystems.disks.spaces.endpoint'),
                'DO_SPACES_URL' => config('filesystems.disks.spaces.url'),
                'CDN_URL' => config('filesystems.disks.spaces.cdn_url'),
            ],
            'env_variables' => [
                'DO_SPACES_KEY_ENV' => env('DO_SPACES_KEY') ? 'SET' : 'NOT SET',
                'DO_SPACES_SECRET_ENV' => env('DO_SPACES_SECRET') ? 'SET' : 'NOT SET',
                'DO_SPACES_BUCKET_ENV' => env('DO_SPACES_BUCKET'),
                'DO_SPACES_REGION_ENV' => env('DO_SPACES_REGION'),
                'FILESYSTEM_DISK_direct' => env('FILESYSTEM_DISK'),
            ],
            'disk_check' => [
                'default_disk' => config('filesystems.default'),
                'spaces_disk_exists' => config('filesystems.disks.spaces') ? 'YES' : 'NO',
                'public_disk_exists' => config('filesystems.disks.public') ? 'YES' : 'NO',
                'will_use_spaces' => (config('filesystems.default') === 'spaces') ? 'YES' : 'NO',
            ],
            'runtime' => [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
                'sapi_name' => php_sapi_name(),
                'working_directory' => getcwd(),
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

    /**
     * Clear and refresh config cache - ONLY FOR TESTING
     */
    public function refreshConfig()
    {
        try {
            $results = [];

            // Check initial state
            $results['initial'] = [
                'FILESYSTEM_DISK_config' => config('filesystems.default'),
                'FILESYSTEM_DISK_env' => env('FILESYSTEM_DISK'),
                'config_cached' => file_exists(base_path('bootstrap/cache/config.php')),
            ];

            // Clear config cache
            if (file_exists(base_path('bootstrap/cache/config.php'))) {
                unlink(base_path('bootstrap/cache/config.php'));
                $results['cache_cleared'] = true;
            } else {
                $results['cache_cleared'] = false;
            }

            // Force config reload
            app('config')->set('filesystems.default', env('FILESYSTEM_DISK', 'local'));

            // Check after reload
            $results['after_reload'] = [
                'FILESYSTEM_DISK_config' => config('filesystems.default'),
                'FILESYSTEM_DISK_env' => env('FILESYSTEM_DISK'),
                'config_cached' => file_exists(base_path('bootstrap/cache/config.php')),
            ];

            return response()->json([
                'success' => true,
                'results' => $results,
                'note' => 'Config has been refreshed. This is temporary until next cache operation.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Check which disk will be used for uploads
     */
    public function checkUploadDisk()
    {
        $isProduction = config('app.env') === 'production';
        $defaultDisk = config('filesystems.default');
        $willUseSpaces = $isProduction && $defaultDisk === 'spaces';

        return response()->json([
            'decision' => [
                'is_production' => $isProduction,
                'default_disk' => $defaultDisk,
                'will_use_spaces' => $willUseSpaces,
                'disk_to_use' => $willUseSpaces ? 'spaces' : 'public',
            ],
            'test_url_generation' => [
                'test_path' => 'course-thumbnails/test.jpg',
                'spaces_url' => Storage::disk('spaces')->url('course-thumbnails/test.jpg'),
                'public_url' => Storage::disk('public')->url('course-thumbnails/test.jpg'),
                'helper_url' => \App\Helpers\StorageHelper::getPublicUrl('course-thumbnails/test.jpg'),
            ],
            'config_values' => [
                'app.env' => config('app.env'),
                'app.url' => config('app.url'),
                'filesystems.default' => config('filesystems.default'),
                'spaces.bucket' => config('filesystems.disks.spaces.bucket'),
                'spaces.region' => config('filesystems.disks.spaces.region'),
            ]
        ]);
    }
}