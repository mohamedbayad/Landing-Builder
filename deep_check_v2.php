<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Get the latest landing
$landing = App\Models\Landing::latest()->first();
$page = $landing->pages()->where('type', 'index')->first();

if ($landing && $page) {
    echo "=== Landing ID: " . $landing->id . " ===" . PHP_EOL;
    echo "Name: " . $landing->name . " (page ID: " . $page->id . ")" . PHP_EOL;
    
    $head = $landing->settings->custom_head_scripts ?? '';
    echo PHP_EOL . "--- Head Scripts ---" . PHP_EOL;
    echo $head . PHP_EOL;
    
    $js = $page->js ?? '';
    echo PHP_EOL . "--- Body JS ---" . PHP_EOL;
    echo $js . PHP_EOL;

    // Check files on disk mentioned in scripts
    preg_match_all('/src=["\']([^"\']+)["\']/', $head . $js, $matches);
    echo PHP_EOL . "--- File Existence Check ---" . PHP_EOL;
    foreach ($matches[1] as $url) {
        $path = str_replace('/storage/', '', $url);
        $fullPath = storage_path('app/public/' . $path);
        echo (File::exists($fullPath) ? "[OK] " : "[MISSING] ") . $url . " (" . $fullPath . ")" . PHP_EOL;
    }
} else {
    echo "No landing found." . PHP_EOL;
}
