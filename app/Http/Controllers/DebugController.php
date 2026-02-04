<?php

namespace App\Http\Controllers;

use App\Models\Landing;
use App\Models\LandingPage;
use Illuminate\Support\Facades\DB;

class DebugController extends Controller
{
    public function index()
    {
        $landings = Landing::with('pages')->get();
        
        echo "<h1>Debug Info</h1>";
        echo "User ID: " . (auth()->id() ?? 'Guest') . "<br>";
        
        foreach ($landings as $l) {
            echo "<hr>";
            echo "Landing ID: {$l->id} | Name: {$l->name} | Main: " . ($l->is_main ? 'YES' : 'NO') . " | Status: {$l->status} | User: {$l->workspace->user_id}<br>";
            echo "Pages:<br>";
            foreach ($l->pages as $p) {
                echo " - [{$p->id}] {$p->name} (Slug: {$p->slug}, Type: {$p->type})<br>";
            }
        }
        
        echo "<hr><h3>Main Landing Logic Check</h3>";
        $main = Landing::where('is_main', true)->first();
        if ($main) {
            echo "Found Main: {$main->name} (ID: {$main->id})<br>";
            $checkout = $main->pages()->where('slug', 'checkout')->first();
            echo "Checkout Page: " . ($checkout ? "FOUND (ID: {$checkout->id})" : "NOT FOUND") . "<br>";
            
            // Visibility
            $visible = false;
            if ($main->status === 'published') {
                $visible = true;
            } else {
                 if (request()->user() && request()->user()->id === $main->workspace->user_id) {
                     $visible = true;
                     echo "Visible because Owner.<br>";
                 } else {
                     echo "Hidden (Draft & Not Owner/Guest).<br>";
                 }
            }
            echo "Final Visibility: " . ($visible ? "VISIBLE" : "404") . "<br>";
        } else {
            echo "NO MAIN LANDING FOUND.<br>";
        }
    }
}
