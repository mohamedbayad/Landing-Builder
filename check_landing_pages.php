<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Landing;

$landing = Landing::latest()->first();

if (!$landing) {
    die("No landing found\n");
}

echo "Landing: {$landing->name}\n";
echo "UUID: {$landing->uuid}\n";
echo "Pages count: " . $landing->pages->count() . "\n\n";

if ($landing->pages->count() > 0) {
    echo "Pages:\n";
    foreach ($landing->pages as $page) {
        echo "  - {$page->type}: {$page->slug} (ID: {$page->id})\n";
    }
} else {
    echo "⚠️ NO PAGES FOUND!\n";
    echo "\nThis means pages were not created during import.\n";
    echo "Check for errors in the import process.\n";
}
