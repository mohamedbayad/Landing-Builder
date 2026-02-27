<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$session = \App\Models\RecordingSession::latest('started_at')->first();
$page = $session->pages()->first();
$events = $page->events()->orderBy('created_at', 'asc')->get();

echo "Total batches: " . $events->count() . "\n";
foreach($events as $index => $e) {
    $len = strlen($e->events_compressed);
    echo "Batch $index: ID: {$e->id}, Length $len, Created: {$e->created_at}\n";
}
