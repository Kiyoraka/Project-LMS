<?php
// Security check - only allow access with specific token
if (!isset($_GET['token']) || $_GET['token'] !== 'check-limits-2025') {
    die('Access denied');
}

echo "<h2>PHP Upload Configuration Check</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Setting</th><th>Current Value</th><th>Recommended for Video</th></tr>";

// Check upload_max_filesize
$upload_max = ini_get('upload_max_filesize');
echo "<tr><td>upload_max_filesize</td><td style='color: " . (intval($upload_max) < 100 ? "red" : "green") . "'>{$upload_max}</td><td>500M</td></tr>";

// Check post_max_size
$post_max = ini_get('post_max_size');
echo "<tr><td>post_max_size</td><td style='color: " . (intval($post_max) < 100 ? "red" : "green") . "'>{$post_max}</td><td>500M</td></tr>";

// Check max_execution_time
$max_exec = ini_get('max_execution_time');
echo "<tr><td>max_execution_time</td><td style='color: " . (intval($max_exec) < 300 ? "orange" : "green") . "'>{$max_exec} seconds</td><td>300</td></tr>";

// Check max_input_time
$max_input = ini_get('max_input_time');
echo "<tr><td>max_input_time</td><td style='color: " . (intval($max_input) < 300 ? "orange" : "green") . "'>{$max_input} seconds</td><td>300</td></tr>";

// Check memory_limit
$memory = ini_get('memory_limit');
echo "<tr><td>memory_limit</td><td>{$memory}</td><td>512M</td></tr>";

// Check max_file_uploads
$max_files = ini_get('max_file_uploads');
echo "<tr><td>max_file_uploads</td><td>{$max_files}</td><td>20</td></tr>";

echo "</table>";

echo "<h3>Recommendations to fix on DigitalOcean:</h3>";
echo "<ol>";
echo "<li>SSH into your server</li>";
echo "<li>Edit PHP configuration: <code>sudo nano /etc/php/8.2/fpm/php.ini</code></li>";
echo "<li>Update these values:<br>";
echo "<code>upload_max_filesize = 500M<br>";
echo "post_max_size = 500M<br>";
echo "max_execution_time = 300<br>";
echo "max_input_time = 300<br>";
echo "memory_limit = 512M</code></li>";
echo "<li>Restart PHP-FPM: <code>sudo systemctl restart php8.2-fpm</code></li>";
echo "<li>For Nginx, also edit: <code>sudo nano /etc/nginx/sites-available/your-site</code><br>";
echo "Add inside server block: <code>client_max_body_size 500M;</code></li>";
echo "<li>Restart Nginx: <code>sudo systemctl restart nginx</code></li>";
echo "</ol>";

// Delete this file after checking
echo "<p style='color: red;'><strong>IMPORTANT: Delete this file after checking!</strong></p>";
?>