<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Lead;
use App\Models\Landing;
use App\Models\Product;
use Carbon\Carbon;

class LeadsController extends Controller
{
    // Admin: List Leads with Filters, Search, Sorting, and Analytics
    public function index(Request $request)
    {
        $workspace = Auth::user()->workspaces()->first();

        if (!$workspace) {
            return view('leads.index', [
                'leads' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15),
                'analytics' => $this->getEmptyAnalytics(),
                'statuses' => $this->getStatuses(),
            ]);
        }

        $landingIds = $workspace->landings()->pluck('id');

        // Base query
        $query = Lead::with(['landing'])
            ->whereIn('landing_id', $landingIds);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%")
                    ->orWhere('transaction_id', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDir = $request->get('dir', 'desc');
        
        if (in_array($sortField, ['created_at', 'amount'])) {
            $query->orderBy($sortField, $sortDir === 'asc' ? 'asc' : 'desc');
        } else {
            $query->latest();
        }

        // Per-page pagination
        $perPage = $request->get('per_page', 15);
        if (!in_array($perPage, [10, 20, 50])) {
            $perPage = 15;
        }

        $leads = $query->paginate($perPage)->withQueryString();

        // Analytics
        $analytics = $this->calculateAnalytics($landingIds);

        return view('leads.index', [
            'leads' => $leads,
            'analytics' => $analytics,
            'statuses' => $this->getStatuses(),
        ]);
    }

    // Calculate analytics for the dashboard
    private function calculateAnalytics($landingIds)
    {
        $baseQuery = Lead::whereIn('landing_id', $landingIds);

        // Total Revenue (paid, shipped, completed orders)
        $totalRevenue = (clone $baseQuery)
            ->whereIn('status', ['paid', 'shipped', 'completed'])
            ->sum('amount');

        // Pending Orders (new, pending, contacted)
        $pendingOrders = (clone $baseQuery)
            ->whereIn('status', ['new', 'pending', 'contacted'])
            ->count();

        // Conversion Rate
        $totalOrders = (clone $baseQuery)->count();
        $successfulOrders = (clone $baseQuery)
            ->whereIn('status', ['paid', 'shipped', 'completed'])
            ->count();
        
        $conversionRate = $totalOrders > 0 
            ? round(($successfulOrders / $totalOrders) * 100, 1) 
            : 0;

        // Today's orders
        $todayOrders = (clone $baseQuery)
            ->whereDate('created_at', Carbon::today())
            ->count();

        return [
            'total_revenue' => $totalRevenue,
            'pending_orders' => $pendingOrders,
            'conversion_rate' => $conversionRate,
            'today_orders' => $todayOrders,
            'total_orders' => $totalOrders,
        ];
    }

    private function getEmptyAnalytics()
    {
        return [
            'total_revenue' => 0,
            'pending_orders' => 0,
            'conversion_rate' => 0,
            'today_orders' => 0,
            'total_orders' => 0,
        ];
    }

    private function getStatuses()
    {
        return [
            'new' => ['label' => 'New', 'color' => 'blue'],
            'pending' => ['label' => 'Pending', 'color' => 'yellow'],
            'contacted' => ['label' => 'Contacted', 'color' => 'purple'],
            'qualified' => ['label' => 'Qualified', 'color' => 'cyan'],
            'paid' => ['label' => 'Paid', 'color' => 'green'],
            'shipped' => ['label' => 'Shipped', 'color' => 'indigo'],
            'completed' => ['label' => 'Completed', 'color' => 'emerald'],
            'failed' => ['label' => 'Failed', 'color' => 'red'],
            'refunded' => ['label' => 'Refunded', 'color' => 'orange'],
        ];
    }

    // Admin: Update Status
    public function update(Request $request, Lead $lead)
    {
        if ($lead->landing->workspace->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'sometimes|in:new,contacted,qualified,pending,paid,shipped,completed,failed,refunded',
            'amount' => 'sometimes|numeric|min:0',
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:50',
            'email' => 'sometimes|email|max:255',
            'address' => 'sometimes|string|max:500',
            'city' => 'sometimes|string|max:100',
            'zip' => 'sometimes|string|max:20',
            'country' => 'sometimes|string|max:100',
        ]);

        $lead->update($validated);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'lead' => $lead]);
        }

        return redirect()->back()->with('success', 'Order updated successfully.');
    }

    // Admin: Get lead details (AJAX)
    public function show(Lead $lead)
    {
        if ($lead->landing->workspace->user_id !== Auth::id()) {
            abort(403);
        }

        $lead->load('landing');

        return response()->json([
            'id' => $lead->id,
            'first_name' => $lead->first_name,
            'last_name' => $lead->last_name,
            'email' => $lead->email,
            'phone' => $lead->phone,
            'address' => $lead->address,
            'city' => $lead->city,
            'zip' => $lead->zip,
            'country' => $lead->country,
            'amount' => $lead->amount,
            'currency' => $lead->currency,
            'status' => $lead->status,
            'payment_provider' => $lead->payment_provider,
            'transaction_id' => $lead->transaction_id,
            'order_items' => $lead->order_items,
            'landing_name' => $lead->landing->name ?? 'N/A',
            'created_at' => $lead->created_at->format('M d, Y H:i'),
            'data' => $lead->data,
        ]);
    }
    
    // Admin: Bulk Update Status
    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer',
            'status' => 'required|in:new,contacted,qualified,pending,paid,shipped,completed,failed,refunded',
        ]);

        $workspace = Auth::user()->workspaces()->first();
        $landingIds = $workspace->landings()->pluck('id');

        $updated = Lead::whereIn('id', $validated['ids'])
            ->whereIn('landing_id', $landingIds)
            ->update(['status' => $validated['status']]);

        return response()->json([
            'success' => true,
            'updated' => $updated,
            'message' => "{$updated} orders updated to {$validated['status']}."
        ]);
    }

    // Admin: Bulk Delete
    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer',
        ]);

        $workspace = Auth::user()->workspaces()->first();
        $landingIds = $workspace->landings()->pluck('id');

        $deleted = Lead::whereIn('id', $validated['ids'])
            ->whereIn('landing_id', $landingIds)
            ->delete();

        return response()->json([
            'success' => true,
            'deleted' => $deleted,
            'message' => "{$deleted} orders deleted."
        ]);
    }

    // Admin: Export CSV
    public function export(Request $request)
    {
        $workspace = Auth::user()->workspaces()->first();

        if (!$workspace) {
            abort(404);
        }

        $landingIds = $workspace->landings()->pluck('id');

        $query = Lead::with(['landing'])
            ->whereIn('landing_id', $landingIds);

        // Apply same filters as index
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $leads = $query->latest()->get();

        $filename = 'orders_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($leads) {
            $file = fopen('php://output', 'w');

            // CSV Header
            fputcsv($file, [
                'Order ID',
                'Date',
                'Customer Name',
                'Email',
                'Phone',
                'Address',
                'City',
                'ZIP',
                'Country',
                'Amount',
                'Currency',
                'Status',
                'Payment Method',
                'Landing Page',
            ]);

            // CSV Data
            foreach ($leads as $lead) {
                fputcsv($file, [
                    $lead->id,
                    $lead->created_at->format('Y-m-d H:i:s'),
                    trim($lead->first_name . ' ' . $lead->last_name),
                    $lead->email,
                    $lead->phone,
                    $lead->address,
                    $lead->city,
                    $lead->zip,
                    $lead->country,
                    $lead->amount,
                    $lead->currency,
                    ucfirst($lead->status),
                    $lead->payment_provider,
                    $lead->landing->name ?? 'N/A',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
    
    // Admin: Delete Lead
    public function destroy(Lead $lead)
    {
        if ($lead->landing->workspace->user_id !== Auth::id()) {
            abort(403);
        }
        $lead->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Order deleted.');
    }

    // Public: Process Checkout (Creates a "Sales Lead")
    public function processCheckout(\App\Http\Requests\CheckoutRequest $request)
    {
        $validated = $request->validated();

        $landing = Landing::find($validated['landing_id']);
        $product = Product::find($validated['product_id']);

        // Create Checkout Lead (Order)
        $lead = $this->createOrder(
            $validated,
            $request->except(['_token', 'password', 'payment_method', 'landing_id', 'product_id']),
            $landing,
            $product,
            $validated['payment_method'],
            'cod_'.uniqid(),
            'pending'
        );

        // CLEAR SESSION AFTER ORDER
        session()->forget('cart');

        // Find Thank You Page
        $thankYouPage = $landing->pages()->where('type', 'thankyou')->first();
        
        if ($thankYouPage) {
            if ($landing->is_main && $landing->status === 'published') {
                return redirect()->to(\Illuminate\Support\Facades\URL::signedRoute('public.page', [
                    'slug' => $thankYouPage->slug,
                    'lead' => $lead->id
                ]));
            } else {
                 return redirect()->route('landings.preview', [$landing, $thankYouPage, 'lead' => $lead->id]); 
            }
        }

        return redirect()->back()->with('success', 'Order placed successfully!');
    }

    // Shared method to create order from PaymentController
    public function createOrder($validatedData, $requestData, $landing, $product, $paymentMethod, $transactionId, $status = 'pending')
    {
        // 1. Calculate Total (Server-Side Recalculation from Session)
        $cart = session('cart', []);
        $grandTotal = $product->price; // Default fallback
        $orderItemsPayload = null;

        if (!empty($cart) && !empty($cart['items']) && is_array($cart['items'])) {
            $grandTotal = 0;
            $orderItemsPayload = [];

            foreach ($cart['items'] as $item) {
                $qty = intval($item['qty'] ?? 1);
                $price = floatval($item['price']);
                
                $lineTotal = $price * $qty;
                $grandTotal += $lineTotal;

                $orderItemsPayload[] = [
                    'product_id' => $item['product_id'] ?? $item['id'] ?? null,
                    'name' => $item['name'] ?? $item['title'] ?? 'Product',
                    'price' => $price,
                    'qty' => $qty,
                    'subtotal' => $lineTotal
                ];
            }
        }

         return Lead::create([
            'type' => 'checkout',
            'landing_id' => $landing->id,
            'email' => $requestData['email'] ?? null,
            'first_name' => $requestData['billing_first_name'] ?? $requestData['first_name'] ?? null,
            'last_name' => $requestData['billing_last_name'] ?? $requestData['last_name'] ?? null,
            'phone' => $requestData['billing_phone'] ?? $requestData['phone'] ?? null,
            'address' => $requestData['billing_address'] ?? $requestData['address'] ?? null,
            'city' => $requestData['billing_city'] ?? $requestData['city'] ?? null,
            'zip' => $requestData['billing_zip'] ?? $requestData['zip'] ?? null,
            'country' => $requestData['billing_country'] ?? $requestData['country'] ?? null,
            
            'product_id' => $product->id,
            'amount' => $grandTotal, // Use Calculated Total
            'currency' => $product->currency,
            'payment_provider' => $paymentMethod,
            'status' => $status,
            'data' => $requestData, 
            'ip_address' => request()->ip(), 
            'transaction_id' => $transactionId,
            'order_items' => $orderItemsPayload,
        ]);
    }
}
