<?php
/**
 * Simple configuration checker for DigitalOcean deployment
 * Access this file at /check-config.php
 */

// Check if we're in production environment
$isProduction = isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'production';

// Configuration values
$config = [
    'Environment Check' => [
        'APP_ENV' => $_ENV['APP_ENV'] ?? 'NOT SET',
        'APP_URL' => $_ENV['APP_URL'] ?? 'NOT SET',
        'Is Production' => $isProduction ? 'YES' : 'NO',
    ],
    'Filesystem Configuration' => [
        'FILESYSTEM_DISK' => $_ENV['FILESYSTEM_DISK'] ?? 'NOT SET',
        'FILESYSTEM_DISK (getenv)' => getenv('FILESYSTEM_DISK') ?: 'NOT SET',
    ],
    'Spaces Configuration' => [
        'DO_SPACES_KEY' => isset($_ENV['DO_SPACES_KEY']) ? 'SET (hidden)' : 'NOT SET',
        'DO_SPACES_SECRET' => isset($_ENV['DO_SPACES_SECRET']) ? 'SET (hidden)' : 'NOT SET',
        'DO_SPACES_REGION' => $_ENV['DO_SPACES_REGION'] ?? 'NOT SET',
        'DO_SPACES_BUCKET' => $_ENV['DO_SPACES_BUCKET'] ?? 'NOT SET',
        'DO_SPACES_ENDPOINT' => $_ENV['DO_SPACES_ENDPOINT'] ?? 'NOT SET',
        'DO_SPACES_URL' => $_ENV['DO_SPACES_URL'] ?? 'NOT SET',
    ],
    'Laravel Cache Status' => [
        'Config Cache' => file_exists('../bootstrap/cache/config.php') ? 'CACHED' : 'NOT CACHED',
        'Route Cache' => file_exists('../bootstrap/cache/routes-v7.php') ? 'CACHED' : 'NOT CACHED',
    ],
    'Server Info' => [
        'PHP Version' => PHP_VERSION,
        'Server Software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
        'Document Root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
    ]
];

// Display as JSON for easy reading
header('Content-Type: application/json');
echo json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);