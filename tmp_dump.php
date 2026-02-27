<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$session = \App\Models\RecordingSession::latest('started_at')->first();
if (!$session) { echo "No session\n"; exit; }
$page = $session->pages()->first();
if (!$page) { echo "No page\n"; exit; }
$event = $page->events()->first();
if (!$event) { echo "No event\n"; exit; }

$data = $event->events_compressed;
echo "Length: " . strlen($data) . "\n";
echo "Prefix: " . substr($data, 0, 50) . "\n";
file_put_contents('storage/logs/latest_event.txt', $data);
