<?php
/**
 * Test what URLs Spaces is generating
 * Access at /test-spaces-url.php
 */

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\Storage;
use App\Helpers\StorageHelper;

$testPath = 'course-thumbnails/test-image.jpg';

$results = [
    'environment' => [
        'APP_ENV' => config('app.env'),
        'FILESYSTEM_DISK' => config('filesystems.default'),
    ],
    'spaces_config' => [
        'bucket' => config('filesystems.disks.spaces.bucket'),
        'region' => config('filesystems.disks.spaces.region'),
        'endpoint' => config('filesystems.disks.spaces.endpoint'),
        'url' => config('filesystems.disks.spaces.url'),
        'url_from_env' => env('DO_SPACES_URL'),
    ],
    'url_generation_test' => [
        'test_path' => $testPath,
        'spaces_disk_url' => Storage::disk('spaces')->url($testPath),
        'public_disk_url' => Storage::disk('public')->url($testPath),
        'storage_helper_url' => StorageHelper::getPublicUrl($testPath),
    ],
    'actual_file_test' => []
];

// Test with an actual file upload
try {
    $testContent = 'Test file at ' . now();
    $uploadPath = 'test/url-test-' . time() . '.txt';

    // Upload to Spaces
    $uploaded = Storage::disk('spaces')->put($uploadPath, $testContent, 'public');

    if ($uploaded) {
        $url = Storage::disk('spaces')->url($uploadPath);
        $exists = Storage::disk('spaces')->exists($uploadPath);

        $results['actual_file_test'] = [
            'uploaded' => true,
            'path' => $uploadPath,
            'url' => $url,
            'exists' => $exists,
            'expected_format' => 'https://kiyoraka-world.sgp1.digitaloceanspaces.com/' . $uploadPath,
        ];

        // Clean up
        Storage::disk('spaces')->delete($uploadPath);
    }
} catch (Exception $e) {
    $results['actual_file_test'] = [
        'error' => $e->getMessage()
    ];
}

header('Content-Type: application/json');
echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);