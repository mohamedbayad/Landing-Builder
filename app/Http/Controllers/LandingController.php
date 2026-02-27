<?php

namespace App\Http\Controllers;

use App\Models\Landing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

use App\Models\Workspace;

class LandingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $workspace = $user->workspaces()->first();

        if (!$workspace) {
            $workspace = Workspace::create([
                'user_id' => $user->id,
                'name' => 'My Workspace',
            ]);
        }

        $landings = $workspace->landings()->latest()->get();
        return view('landings.index', compact('landings'));
    }

    public function show(Landing $landing)
    {
        if ($landing->workspace->user_id != Auth::id()) {
            abort(403);
        }
        
        $landing->load('pages');
        return view('landings.show', compact('landing'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Landing $landing)
    {
        // Authorization check (simple MVP check)
        if ($landing->workspace->user_id != Auth::id()) {
            abort(403);
        }

        return view('landings.edit', compact('landing'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Landing $landing)
    {
        if ($landing->workspace->user_id != Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:landings,slug,' . $landing->id . '|not_in:dashboard,login,register,logout,password,email,profile,sanctum,api,templates,landings,app,preview',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'fb_pixel_id' => 'nullable|string',
            'ga_measurement_id' => 'nullable|string',
            'custom_head_scripts' => 'nullable|string',
            'custom_body_scripts' => 'nullable|string',
            'cart_bg_color' => 'nullable|string|max:7',
            'cart_text_color' => 'nullable|string|max:7',
            'cart_btn_color' => 'nullable|string|max:7',
            'cart_btn_text_color' => 'nullable|string|max:7',
            'cart_position' => 'nullable|string|in:bottom-right,bottom-left,top-right,top-left,bottom-bar',
            'cart_x_offset' => 'nullable|integer|min:0',
            'cart_x_offset' => 'nullable|integer|min:0',
            'cart_y_offset' => 'nullable|integer|min:0',
            'countdown_end_at' => 'nullable|date',
            'countdown_duration_minutes' => 'nullable|integer|min:1',
        ]);

        $landing->update([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'enable_cart' => $request->has('enable_cart'),
            'cart_position' => $validated['cart_position'] ?? 'bottom-right',
            'cart_x_offset' => $validated['cart_x_offset'] ?? 20,
            'cart_y_offset' => $validated['cart_y_offset'] ?? 20,
            'cart_bg_color' => $validated['cart_bg_color'] ?? '#ffffff',
            'cart_text_color' => $validated['cart_text_color'] ?? '#000000',
            'cart_btn_color' => $validated['cart_btn_color'] ?? '#3b82f6',
            'cart_btn_text_color' => $validated['cart_btn_text_color'] ?? '#ffffff',
            'countdown_enabled' => $request->has('countdown_enabled'),
            'countdown_end_at' => $validated['countdown_end_at'] ?? null,
            'countdown_duration_minutes' => $validated['countdown_duration_minutes'] ?? null,
            // If enabling duration mode for first time or re-enabling, we might want to reset started_at?
            // For now, let's say we set started_at only if it's null and we are saving a duration. 
            // Or maybe update it whenever we save settings? 
            // Better: If switching to duration mode, set started_at to now if it's not set.
            'countdown_started_at' => ($request->has('countdown_enabled') && !empty($validated['countdown_duration_minutes'])) 
                                      ? ($landing->countdown_started_at ?? now()) 
                                      : $landing->countdown_started_at,
        ]);

        $landing->settings()->updateOrCreate(
            ['landing_id' => $landing->id],
            [
                'meta_title' => $validated['meta_title'] ?? null,
                'meta_description' => $validated['meta_description'] ?? null,
                'fb_pixel_id' => $validated['fb_pixel_id'] ?? null,
                'ga_measurement_id' => $validated['ga_measurement_id'] ?? null,
                'custom_head_scripts' => $validated['custom_head_scripts'] ?? null,
                'custom_body_scripts' => $validated['custom_body_scripts'] ?? null,
                'enable_card' => $request->has('enable_card'),
                'enable_paypal' => $request->has('enable_paypal'),
                'enable_cod' => $request->has('enable_cod'),
            ]
        );

        return redirect()->route('landings.index')->with('status', 'Landing updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Landing $landing)
    {
        if ($landing->workspace->user_id != Auth::id()) {
            abort(403);
        }

        $landing->delete();

        return redirect()->route('landings.index')->with('status', 'Landing deleted successfully!');
    }

    public function setAsMain(Landing $landing)
    {
        if ($landing->workspace->user_id != Auth::id()) {
            abort(403);
        }

        // Unset any other main landing
        $landing->workspace->landings()->update(['is_main' => false]);
        
        // Set this one as main
        $landing->update(['is_main' => true]);

        return redirect()->back()->with('status', 'Landing set as main successfully!');
    }

    public function publish(Landing $landing)
    {
        if ($landing->workspace->user_id != Auth::id()) {
            abort(403);
        }

        $landing->update(['status' => 'published', 'published_at' => now()]);

        return redirect()->back()->with('status', 'Landing published successfully!');
    }

    public function syncCart(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.label' => 'required|string',
            'items.*.qty' => 'integer|min:1',
            'landing_id' => 'required|exists:landings,id',
        ]);

        $realItems = [];
        $total = 0;

        foreach ($validated['items'] as $item) {
            // Find product by label within the specific landing context if possible, 
            // or just by label globally if unique, but safer scoping by landing is better if products are unique per landing.
            // Assuming Product has 'label' and 'landing_id'
            $product = \App\Models\Product::where('landing_id', $validated['landing_id'])
                        ->where('label', $item['label']) // Falling back to label matching as requested
                        ->first();
            
            // If product found, use DB price. If not, use default or skip?
            // For now, if not found, we might skip or use a fallback price if intended for "custom" items not in DB.
            // But strict security implies only DB items.
            
            if ($product) {
                $realItems[] = [
                    'product_id' => $product->id,
                    'name' => $product->name, // or label
                    'price' => $product->price,
                    'qty' => $item['qty'] ?? 1,
                    'total' => $product->price * ($item['qty'] ?? 1)
                ];
                $total += $product->price * ($item['qty'] ?? 1);
            }
        }

        // Store in session
        session()->put('cart', [
            'items' => $realItems,
            'total' => $total,
            'landing_id' => $validated['landing_id']
        ]);

        return response()->json(['success' => true]);
    }
}
