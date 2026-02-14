<?php
/**
 * Test Script: Process Mitsoki HTML with TemplateZipProcessorService
 * This simulates the exact import flow to debug the issue
 */

require __DIR__ . '/vendor/autoload.php';

use App\Services\TemplateZipProcessorService;

// Initialize Laravel app
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing TemplateZipProcessorService with Mitsoki ZIP ===\n\n";

$processor = new TemplateZipProcessorService();

$htmlPath = 'C:\Users\DELL\Desktop\web app\Mitsoki_extracted\Mitsoki\index.html';
$baseUrl = "/storage/landings/test-uuid/";

echo "Processing HTML: $htmlPath\n";
echo "Base URL: $baseUrl\n\n";

if (!file_exists($htmlPath)) {
    die("ERROR: HTML file not found!\n");
}

$parsed = $processor->processHtml($htmlPath, $baseUrl);

echo "=== RESULTS ===\n\n";

echo "📄 TITLE:\n";
echo $parsed['title'] ?: '(empty)';
echo "\n\n";

echo "📦 CUSTOM HEAD (for layout header):\n";
echo "Length: " . strlen($parsed['custom_head']) . " characters\n";
echo "---\n";
echo substr($parsed['custom_head'], 0, 500) . (strlen($parsed['custom_head']) > 500 ? '...' : '');
echo "\n\n";

echo "🎨 CSS (inline styles):\n";
echo "Length: " . strlen($parsed['css']) . " characters\n";
echo ($parsed['css'] ? 'Has CSS' : '(empty)');
echo "\n\n";

echo "📝 BODY HTML (first 1000 chars):\n";
echo "Length: " . strlen($parsed['body_html']) . " characters\n";
echo "---\n";
echo substr($parsed['body_html'], 0, 1000) . '...';
echo "\n\n";

// Check for specific patterns
echo "=== VALIDATION CHECKS ===\n\n";

// Check if Tailwind CDN was removed
if (str_contains($parsed['custom_head'], 'cdn.tailwindcss.com')) {
    echo "❌ FAIL: Tailwind CDN still present in custom_head!\n";
} else {
    echo "✅ PASS: Tailwind CDN removed from custom_head\n";
}

if (str_contains($parsed['body_html'], 'cdn.tailwindcss.com')) {
    echo "❌ FAIL: Tailwind CDN still present in body!\n";
} else {
    echo "✅ PASS: Tailwind CDN not in body\n";
}

// Check if stylesheet was extracted
if (str_contains($parsed['custom_head'], '/storage/landings/test-uuid/assets/css/style.css')) {
    echo "✅ PASS: Stylesheet extracted and rewritten correctly\n";
} else {
    echo "❌ FAIL: Stylesheet not found in custom_head or not rewritten!\n";
}

// Check if Google Fonts were extracted
if (str_contains($parsed['custom_head'], 'fonts.googleapis.com')) {
    echo "✅ PASS: Google Fonts extracted to custom_head\n";
} else {
    echo "❌ FAIL: Google Fonts not in custom_head!\n";
}

// Check if JS script stayed in body
if (str_contains($parsed['body_html'], '/storage/landings/test-uuid/assets/js/index.js')) {
    echo "✅ PASS: JavaScript script in body with rewritten path\n";
} else {
    echo "❌ FAIL: JavaScript script not found in body or path not rewritten!\n";
}

// Check if images were rewritten
if (str_contains($parsed['body_html'], '/storage/landings/test-uuid/assets/media/imgs/')) {
    echo "✅ PASS: Image paths rewritten correctly\n";
} else {
    echo "❌ FAIL: Image paths not rewritten!\n";
}

echo "\n=== END TEST ===\n";
