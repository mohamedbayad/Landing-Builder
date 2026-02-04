<?php
// Load Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Landing;

echo "--- DEBUG START ---\n";

$main = Landing::where('is_main', true)->first();

if (!$main) {
    echo "ERROR: No Main Landing found.\n";
} else {
    echo "Main Landing: {$main->name} (ID: {$main->id})\n";
    echo "Status: {$main->status}\n";
    echo "Owner ID: {$main->workspace->user_id}\n";
    
    $checkout = $main->pages()->where('slug', 'checkout')->first();
    if ($checkout) {
        echo "Checkout Page: FOUND (ID: {$checkout->id}, Type: {$checkout->type})\n";
    } else {
        echo "Checkout Page: NOT FOUND. Creating it now...\n";
        $main->pages()->create([
            'type' => 'checkout',
            'name' => 'Checkout',
            'slug' => 'checkout',
            'status' => 'draft', // Draft is fine now that I fixed the controller
            'html' => '<div class="container mx-auto px-4 py-8"><h1 class="text-3xl font-bold mb-4">Checkout</h1><p>Dynamic Checkout Form will appear here.</p></div>',
            'css' => '',
            'js' => '',
        ]);
        echo "Checkout Page: CREATED.\n";
        
        // Also Create Thank You if missing
        if (!$main->pages()->where('slug', 'thank-you')->exists()) {
             $main->pages()->create([
                'type' => 'thankyou',
                'name' => 'Thank You',
                'slug' => 'thank-you',
                'status' => 'draft',
                'html' => '<div class="container mx-auto px-4 py-8 text-center"><h1 class="text-3xl font-bold mb-4 text-green-600">Thank You!</h1></div>',
                'css' => '',
                'js' => '',
            ]);
            echo "Thank You Page: CREATED.\n";
        }
    }
}

echo "All Landings:\n";
foreach (Landing::all() as $l) {
    echo "- [{$l->id}] {$l->name} (Main: " . ($l->is_main ? 'YES' : 'NO') . ", Status: {$l->status})\n";
}

echo "--- DEBUG END ---\n";
