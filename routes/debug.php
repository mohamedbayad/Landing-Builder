<?php

use Illuminate\Support\Facades\Route;
use App\Models\Landing;

Route::get('/debug-checkout', function () {
    $mainLanding = Landing::where('is_main', true)->first();
    if (!$mainLanding) return 'No Main Landing Found';
    
    echo "Main Landing: {$mainLanding->name} (ID: {$mainLanding->id})<br>";
    echo "Status: {$mainLanding->status}<br>";
    echo "Owner ID: {$mainLanding->workspace->user_id}<br>";
    echo "Current User: " . (auth()->id() ?? 'Guest') . "<br>";
    
    $page = $mainLanding->pages()->where('slug', 'checkout')->first();
    if (!$page) return 'Checkout Page NOT Found';
    
    echo "Checkout Page Found: {$page->name} (ID: {$page->id})";
});
