<?php
/**
 * Test which disk is being used for uploads
 * Access at /test-upload-disk.php
 */

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Get configurations
$defaultDisk = config('filesystems.default');
$isProduction = config('app.env') === 'production';
$spacesConfigured = config('filesystems.disks.spaces.key') ? true : false;

// Test what disk will actually be used
$testPath = 'test-' . time() . '.txt';
$testContent = 'Test upload at ' . now();

$results = [
    'configuration' => [
        'app_env' => config('app.env'),
        'filesystem_default' => $defaultDisk,
        'spaces_configured' => $spacesConfigured,
        'will_use_spaces' => ($isProduction && $defaultDisk === 'spaces'),
    ],
    'environment_variables' => [
        'FILESYSTEM_DISK' => env('FILESYSTEM_DISK'),
        'DO_SPACES_BUCKET' => env('DO_SPACES_BUCKET'),
    ],
    'upload_test' => []
];

// Test default disk
try {
    $uploaded = Storage::put($testPath, $testContent);
    if ($uploaded) {
        $url = Storage::url($testPath);
        $exists = Storage::exists($testPath);
        Storage::delete($testPath);

        $results['upload_test']['default_disk'] = [
            'success' => true,
            'disk_used' => $defaultDisk,
            'url' => $url,
            'file_existed' => $exists,
        ];
    }
} catch (Exception $e) {
    $results['upload_test']['default_disk'] = [
        'success' => false,
        'error' => $e->getMessage(),
    ];
}

// Test spaces disk explicitly
if ($spacesConfigured) {
    try {
        $uploaded = Storage::disk('spaces')->put($testPath, $testContent);
        if ($uploaded) {
            $url = Storage::disk('spaces')->url($testPath);
            $exists = Storage::disk('spaces')->exists($testPath);
            Storage::disk('spaces')->delete($testPath);

            $results['upload_test']['spaces_disk'] = [
                'success' => true,
                'url' => $url,
                'file_existed' => $exists,
            ];
        }
    } catch (Exception $e) {
        $results['upload_test']['spaces_disk'] = [
            'success' => false,
            'error' => $e->getMessage(),
        ];
    }
}

// Test public disk
try {
    $uploaded = Storage::disk('public')->put($testPath, $testContent);
    if ($uploaded) {
        $url = Storage::disk('public')->url($testPath);
        $exists = Storage::disk('public')->exists($testPath);
        Storage::disk('public')->delete($testPath);

        $results['upload_test']['public_disk'] = [
            'success' => true,
            'url' => $url,
            'file_existed' => $exists,
        ];
    }
} catch (Exception $e) {
    $results['upload_test']['public_disk'] = [
        'success' => false,
        'error' => $e->getMessage(),
    ];
}

header('Content-Type: application/json');
echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);