<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Landing;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index()
    {
        // Fetch orders associated with landings that belong to the user's workspaces
        $orders = Order::whereHas('landing.workspace', function($query) {
            $query->where('user_id', Auth::id());
        })
        ->with('landing')
        ->latest()
        ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'landing_id' => 'required|exists:landings,id',
            'product_id' => 'required', // Relaxed validation as we rely on cart, but keep for legacy or lead link
            'payment_method' => 'required|in:card,paypal,cod',
            // Flexible validation for dynamic checkout fields
            'first_name' => 'nullable|string',
            'last_name'  => 'nullable|string',
            'email'      => 'nullable|email',
            'address'    => 'nullable|string',
            'phone'      => 'nullable|string',
            'city'       => 'nullable|string',
            'zip'        => 'nullable|string',
            'country'    => 'nullable|string',
        ]);

        $landing = Landing::find($validated['landing_id']);
        
        // 1. Retrieve Cart
        $cart = session('cart', []);

        // 2. Validate Cart
        if (empty($cart) || empty($cart['items']) || !is_array($cart['items'])) {
            return redirect()->back()->with('error', 'Your cart is empty. Please add items before checking out.');
        }

        // 3. Calculate Total (Server-Side Recalculation)
        $grandTotal = 0;
        $orderItemsPayload = [];

        foreach ($cart['items'] as $item) {
            $qty = intval($item['qty'] ?? 1);
            if ($qty < 1) $qty = 1;
            
            $price = floatval($item['price']);
            
            // The Math: Price * Quantity
            $lineTotal = $price * $qty;
            $grandTotal += $lineTotal;

            // Prepare JSON data
            $orderItemsPayload[] = [
                'product_id' => $item['product_id'] ?? $item['id'] ?? null,
                'name' => $item['name'] ?? $item['title'] ?? 'Product',
                'price' => $price,
                'qty' => $qty,
                'subtotal' => $lineTotal
            ];
        }

        // 4. Create Order
        // For product_id column, we use the first item's ID or the passed one as a fallback reference
        $mainProductId = $validated['product_id'] ?? $orderItemsPayload[0]['product_id'];
        $product = Product::find($mainProductId);
        
        $order = \App\Models\Order::create([
            'landing_id' => $landing->id,
            'product_id' => $mainProductId, 
            'customer_name' => ($request->first_name ?? '') . ' ' . ($request->last_name ?? ''),
            'customer_email' => $request->email,
            'amount' => $grandTotal, // <--- EXPLICITLY CALCULATED
            'currency' => $product->currency ?? 'USD',
            'status' => 'pending', 
            'payment_provider' => $validated['payment_method'],
            'transaction_id' => 'sim_'.uniqid(), 
            'order_items' => $orderItemsPayload,
        ]);

        // 5. Create Unified Lead (Linked to Order)
        $order->lead()->create([
            'landing_id' => $landing->id,
            'email' => $order->customer_email,
            'status' => 'new',
            'payment_provider' => $order->payment_provider,
            'amount' => $order->amount,
            'currency' => $order->currency,
            'product_id' => $order->product_id,
            'transaction_id' => $order->transaction_id,
            'data' => $request->except(['_token', 'password', 'payment_method', 'landing_id', 'product_id']),
            'ip_address' => $request->ip(),
            'order_items' => $orderItemsPayload, // Save copy to lead if needed, or rely on order relation
        ]);

        // 6. Clear Session
        session()->forget('cart');

        // Find Thank You Page
        $thankYouPage = $landing->pages()->where('type', 'thankyou')->first();
        
        if ($thankYouPage) {
            if ($landing->is_main && $landing->status === 'published') {
                return redirect('/' . $thankYouPage->slug);
            } else {
                 return redirect()->route('landings.preview', [$landing, $thankYouPage]); 
            }
        }

        return redirect()->back()->with('success', 'Order created successfully!');
    }

    public function update(Request $request, Order $order)
    {
        // Ensure user owns the order (via landing -> workspace)
        if ($order->landing->workspace->user_id != Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,paid,failed,refunded,shipped,completed',
        ]);

        $order->update(['status' => $validated['status']]);

        // Sync with Lead
        if ($order->lead) {
            // Map Order Status to Lead Status if necessary, or just use same
            // leads: new, contacted, qualified, paid, failed, refunded
            $leadStatus = $validated['status'];
            if ($leadStatus === 'pending') $leadStatus = 'new';
            if ($leadStatus === 'completed' || $leadStatus === 'shipped') $leadStatus = 'paid'; // Assumed

            $order->lead->update(['status' => $leadStatus]);
        }

        return redirect()->back()->with('success', 'Order status updated.');
    }
}
