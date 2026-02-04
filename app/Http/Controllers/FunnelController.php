<?php

namespace App\Http\Controllers;

use App\Models\Landing;
use App\Models\Product;
use App\Models\CheckoutField;
use Illuminate\Http\Request;

class FunnelController extends Controller
{
    public function show(Landing $landing)
    {
        // Ensure user owns this landing
        if ($landing->workspace->user_id !== auth()->id()) {
            abort(403);
        }

        $pages = $landing->pages;
        $products = $landing->products; // Relationship needed in Landing model
        $checkoutFields = $landing->checkoutFields; // Relationship needed in Landing model

        // If no checkout fields configured, seed defaults for view
        if ($checkoutFields->isEmpty()) {
            $defaults = [
                ['billing_first_name', 'First Name', true, true],
                ['billing_last_name', 'Last Name', true, true],
                ['billing_email', 'Email Address', true, true],
                ['billing_phone', 'Phone Number', true, false],
                ['billing_address', 'Address', true, true],
                ['billing_city', 'City', true, true],
                ['billing_zip', 'Zip/Postal Code', true, true],
                ['billing_country', 'Country', true, true],
            ];
            
            $seeded = [];
            foreach ($defaults as $def) {
                // Not saving to DB yet, just for view, OR save them now?
                // Let's passed mocked objects or save. Saving is safer for consistency.
                // Actually, let's treat them as "default state" if missing.
                $seeded[] = new CheckoutField([
                    'field_name' => $def[0],
                    'label' => $def[1],
                    'is_enabled' => $def[2],
                    'is_required' => $def[3],
                ]);
            }
            if ($checkoutFields->isEmpty()) {
                $checkoutFields = collect($seeded);
            }
        }

        return view('landings.funnel', compact('landing', 'pages', 'products', 'checkoutFields'));
    }

    public function storeProduct(Request $request, Landing $landing)
    {
         if ($landing->workspace->user_id !== auth()->id()) abort(403);

         $validated = $request->validate([
             'name' => 'required|string',
             'price' => 'required|numeric|min:0',
             'currency' => 'required|string|size:3',
             'description' => 'nullable|string',
             'label' => 'nullable|string',
         ]);

         $product = $landing->products()->create($validated);

         return back()->with('status', 'Product added successfully.');
    }

    public function deleteProduct(Request $request, Landing $landing, Product $product)
    {
        if ($landing->workspace->user_id !== auth()->id()) abort(403);
        if ($product->landing_id !== $landing->id) abort(403);

        $product->delete();
        return back()->with('status', 'Product deleted.');
    }

    public function storeCheckoutFields(Request $request, Landing $landing)
    {
        if ($landing->workspace->user_id !== auth()->id()) abort(403);
        
        // Input: fields array [field_name => [enabled, required, label]]
        $fields = $request->input('fields', []);

        foreach ($fields as $fieldName => $data) {
            CheckoutField::updateOrCreate(
                ['landing_id' => $landing->id, 'field_name' => $fieldName],
                [
                    'label' => $data['label'] ?? '',
                    'is_enabled' => isset($data['enabled']),
                    'is_required' => isset($data['required']),
                ]
            );
        }

        return back()->with('status', 'Checkout fields saved.');
    }
}
