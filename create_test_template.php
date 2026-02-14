<?php
/**
 * Test Script: Create a template ZIP with the specified structure
 * 
 * Structure:
 * /index.html
 * /assets/css/style.css
 * /assets/js/index.js
 * /media/imgs/example.jpeg
 */

// Create temporary directory
$tempDir = sys_get_temp_dir() . '/test_template_' . time();
mkdir($tempDir, 0755, true);

// Create directory structure
mkdir($tempDir . '/assets/css', 0755, true);
mkdir($tempDir . '/assets/js', 0755, true);
mkdir($tempDir . '/media/imgs', 0755, true);

// Create index.html with links to CSS, JS, and images
$indexHtml = <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Landing Page</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Test Landing Page</h1>
        <p>This is a test landing page with proper structure.</p>
        <img src="/media/imgs/example.jpeg" alt="Example Image">
        
        <div class="bg-test" style="background-image: url(/media/imgs/example.jpeg);">
            Background image test
        </div>
    </div>
    
    <script src="/assets/js/index.js"></script>
</body>
</html>
HTML;

file_put_contents($tempDir . '/index.html', $indexHtml);

// Create style.css
$css = <<<'CSS'
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Inter', sans-serif;
    line-height: 1.6;
    color: #333;
    background-color: #f5f5f5;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
}

h1 {
    font-size: 2.5rem;
    margin-bottom: 1rem;
    color: #2563eb;
}

img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    margin: 1rem 0;
}

.bg-test {
    width: 300px;
    height: 200px;
    background-size: cover;
    background-position: center;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    text-shadow: 0 2px 4px rgba(0,0,0,0.5);
}
CSS;

file_put_contents($tempDir . '/assets/css/style.css', $css);

// Create index.js
$js = <<<'JS'
console.log('Test Landing Page Loaded');

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded');
    
    const heading = document.querySelector('h1');
    if (heading) {
        heading.style.transition = 'color 0.3s';
        heading.addEventListener('mouseenter', function() {
            this.style.color = '#1d4ed8';
        });
        heading.addEventListener('mouseleave', function() {
            this.style.color = '#2563eb';
        });
    }
});
JS;

file_put_contents($tempDir . '/assets/js/index.js', $js);

// Create a simple test image (1x1 pixel JPEG)
$imageData = base64_decode('/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/2wBDAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwCwAA8A/9k=');
file_put_contents($tempDir . '/media/imgs/example.jpeg', $imageData);

// Create screenshot.png for template preview
file_put_contents($tempDir . '/screenshot.png', $imageData); // Using same image as placeholder

// Create ZIP file
$zipPath = __DIR__ . '/test_template.zip';
$zip = new ZipArchive();

if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    die("Cannot create ZIP file\n");
}

// Add files to ZIP
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($tempDir),
    RecursiveIteratorIterator::LEAVES_ONLY
);

foreach ($files as $file) {
    if (!$file->isDir()) {
        $filePath = $file->getRealPath();
        $relativePath = substr($filePath, strlen($tempDir) + 1);
        $zip->addFile($filePath, $relativePath);
    }
}

$zip->close();

// Clean up temp directory
function deleteDirectory($dir) {
    if (!file_exists($dir)) return;
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        $path = $dir . '/' . $file;
        is_dir($path) ? deleteDirectory($path) : unlink($path);
    }
    rmdir($dir);
}

deleteDirectory($tempDir);

echo "✅ ZIP created successfully: test_template.zip\n\n";
echo "Structure:\n";
echo "  /index.html\n";
echo "  /assets/css/style.css\n";
echo "  /assets/js/index.js\n";
echo "  /media/imgs/example.jpeg\n";
echo "  /screenshot.png\n\n";
echo "Expected behavior after import:\n";
echo "  ✅ <link rel='stylesheet' href='/storage/landings/{uuid}/assets/css/style.css'> → HEAD\n";
echo "  ✅ Google Fonts → HEAD\n";
echo "  ✅ <img src='/storage/landings/{uuid}/media/imgs/example.jpeg'> → BODY\n";
echo "  ✅ <script src='/storage/landings/{uuid}/assets/js/index.js'> → BODY\n";
echo "  ✅ background-image: url('/storage/landings/{uuid}/media/imgs/example.jpeg') → BODY\n";
