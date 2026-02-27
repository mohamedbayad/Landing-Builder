<?php

namespace App\Http\Controllers;

use App\Models\Landing;
use App\Models\LandingPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LandingPageController extends Controller
{
    public function edit(Landing $landing, LandingPage $page)
    {
        if ($landing->workspace->user_id != Auth::id()) {
            abort(403);
        }

        if ($page->landing_id != $landing->id) {
            abort(404);
        }

        // --- Logic to Render Dynamic Content for Editor ---
        // If it's a Checkout/Thank You page, we want the editor to see the REAL fields,
        // not just a placeholder. Use the same logic as PublicLandingController.
        
        $initialHtml = $page->html;
        
        // If HTML is empty or just placeholder, try to render dynamic content
        if ($page->type === 'checkout' && (empty($initialHtml) || str_contains($initialHtml, 'Dynamic Checkout Form'))) {
             $product = $landing->products()->first(); // Default to first
             $checkoutFields = $landing->checkoutFields()->where('is_enabled', true)->get();
             
             // Render the view to string
             $initialHtml = view('landings.public.checkout', [
                 'landing' => $landing, 
                 'page' => $page, 
                 'product' => $product,
                 'checkoutFields' => $checkoutFields
             ])->render();
             
             // Temporarily assign for this request (don't save to DB yet)
             $page->html = $initialHtml;
        }

        return view('editor', compact('landing', 'page'));
    }

    public function update(Request $request, Landing $landing, LandingPage $page)
    {
        if ($landing->workspace->user_id != Auth::id()) {
            abort(403);
        }
        if ($page->landing_id != $landing->id) {
            abort(404);
        }

        // We expect JSON payload
        $validated = $request->validate([
            'grapesjs_json' => 'nullable|string',
            'html' => 'nullable|string',
            'css' => 'nullable|string',
            'js' => 'nullable|string',
        ]);

        $page->update($validated);

        return response()->json(['success' => true]);
    }
}
