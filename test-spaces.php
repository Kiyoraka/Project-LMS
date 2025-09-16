<?php

// Test DigitalOcean Spaces connectivity
// Run this with: php test-spaces.php

require_once 'vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

// Your Spaces credentials
$key = 'DO00NTTP726H8VRGZ68G';
$secret = 'R4NZOtvfv0zim6Tt2wZOoHqV4ChIGrpdYScXnxBNsDw';
$region = 'sgp1';
$bucketName = 'kiyoraka-world';
$endpoint = 'https://sgp1.digitaloceanspaces.com';

echo "Testing DigitalOcean Spaces connectivity...\n";
echo "========================================\n";

try {
    // Create S3 client
    $client = new S3Client([
        'version' => 'latest',
        'region' => $region,
        'endpoint' => $endpoint,
        'credentials' => [
            'key' => $key,
            'secret' => $secret,
        ],
        'use_path_style_endpoint' => false,
    ]);

    echo "✓ S3 Client created successfully\n";

    // Test 1: List buckets
    echo "\nTest 1: Listing buckets...\n";
    $result = $client->listBuckets();
    foreach ($result['Buckets'] as $bucket) {
        echo "  - " . $bucket['Name'] . "\n";
    }
    echo "✓ Can list buckets\n";

    // Test 2: Check if bucket exists
    echo "\nTest 2: Checking if bucket '$bucketName' exists...\n";
    $exists = $client->doesBucketExist($bucketName);
    if ($exists) {
        echo "✓ Bucket exists\n";
    } else {
        echo "✗ Bucket does not exist\n";
        exit(1);
    }

    // Test 3: Upload a test file
    echo "\nTest 3: Uploading test file...\n";
    $testContent = "This is a test file uploaded at " . date('Y-m-d H:i:s');
    $testKey = 'test/test-upload-' . time() . '.txt';

    $result = $client->putObject([
        'Bucket' => $bucketName,
        'Key' => $testKey,
        'Body' => $testContent,
        'ACL' => 'public-read',
        'ContentType' => 'text/plain',
    ]);

    echo "✓ Test file uploaded successfully\n";
    echo "  File URL: " . $result['ObjectURL'] . "\n";

    // Test 4: List files in bucket
    echo "\nTest 4: Listing files in bucket...\n";
    $objects = $client->listObjects([
        'Bucket' => $bucketName,
        'MaxKeys' => 10
    ]);

    if (isset($objects['Contents'])) {
        foreach ($objects['Contents'] as $object) {
            echo "  - " . $object['Key'] . " (" . $object['Size'] . " bytes)\n";
        }
    }

    // Test 5: Delete test file
    echo "\nTest 5: Cleaning up test file...\n";
    $client->deleteObject([
        'Bucket' => $bucketName,
        'Key' => $testKey
    ]);
    echo "✓ Test file deleted\n";

    echo "\n========================================\n";
    echo "All tests passed! Spaces is configured correctly.\n";

} catch (AwsException $e) {
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    echo "Error code: " . $e->getAwsErrorCode() . "\n";
    exit(1);
} catch (\Exception $e) {
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}