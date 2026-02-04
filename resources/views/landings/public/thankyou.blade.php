{{-- Thank You Page Layout Switcher --}}
{{-- This file dynamically includes the appropriate layout based on settings --}}

@php
    // Default to thankyou_1 if not set
    $selectedLayout = $layout ?? 'thankyou_1';
    
    // Validate layout exists, fallback to thankyou_1
    $validLayouts = ['thankyou_1', 'thankyou_2', 'thankyou_3', 'thankyou_4', 'thankyou_5'];
    if (!in_array($selectedLayout, $validLayouts)) {
        $selectedLayout = 'thankyou_1';
    }
    
    // Get workspace settings
    $wsSettings = $landing->workspace->settings ?? null;
    
    // Thank You Page Display Settings
    $showSummary = $wsSettings->thankyou_show_summary ?? true;
    $showInvoice = $wsSettings->thankyou_show_invoice_btn ?? true;
    
    // WhatsApp Settings
    $whatsappEnabled = $wsSettings->whatsapp_enabled ?? false;
    $autoWhatsappRedirect = $wsSettings->whatsapp_redirect_enabled ?? false;
    $adminPhone = $wsSettings->whatsapp_phone ?? '';
    $redirectDelay = ($wsSettings->whatsapp_redirect_seconds ?? 5) * 1000; // Convert to ms
    $whatsappTemplate = $wsSettings->whatsapp_template_thankyou ?? 'Hello, I just placed an order {{ order-id }}. Thank you!';
    
    // Prepare order data
    $orderData = [];
    $whatsappUrl = '';
    
    if (isset($lead)) {
        $orderData = [
            'orderId' => 'ORD-' . $lead->id,
            'customerName' => trim(($lead->first_name ?? '') . ' ' . ($lead->last_name ?? '')) ?: 'Guest',
            'email' => $lead->email ?? '',
            'phone' => $lead->phone ?? '',
            'address' => trim(($lead->address ?? '') . ', ' . ($lead->city ?? '') . ' ' . ($lead->zip ?? '') . ', ' . ($lead->country ?? ''), ', '),
            'date' => $lead->created_at->format('M d, Y'),
            'paymentMethod' => ucfirst($lead->payment_method ?? 'N/A'),
            'currency' => $lead->currency ?? 'USD',
            'amount' => number_format($lead->amount ?? 0, 2),
            'status' => ucfirst($lead->status ?? 'pending'),
            'invoiceUrl' => \Illuminate\Support\Facades\URL::signedRoute('invoices.download', $lead),
            'items' => [],
            'leadId' => $lead->id,
        ];
        
        // Parse order items
        if (!empty($lead->order_items)) {
            $items = is_string($lead->order_items) ? json_decode($lead->order_items, true) : $lead->order_items;
            if (is_array($items)) {
                $orderData['items'] = $items;
            }
        }
        
        // Fallback: If no order_items but lead has product, create single item
        if (empty($orderData['items']) && $lead->product) {
            $orderData['items'] = [
                [
                    'name' => $lead->product->name ?? 'Product',
                    'price' => $lead->amount ?? $lead->product->price ?? 0,
                    'qty' => 1,
                    'product_id' => $lead->product->id ?? null,
                ]
            ];
        }
    } else {
        // PREVIEW MODE: No lead data available - show sample/demo data
        $orderData = [
            'orderId' => 'ORD-DEMO',
            'customerName' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+1 (555) 123-4567',
            'address' => '123 Demo Street, New York, NY 10001',
            'date' => now()->format('M d, Y'),
            'paymentMethod' => 'Credit Card',
            'currency' => 'USD',
            'amount' => '99.00',
            'status' => 'Confirmed',
            'invoiceUrl' => '#',
            'items' => [
                ['name' => 'Sample Product', 'price' => 79.00, 'qty' => 1],
                ['name' => 'Bonus Item', 'price' => 20.00, 'qty' => 1],
            ],
            'isDemo' => true,
        ];
    }
        
        // Construct WhatsApp URL if enabled
        if ($whatsappEnabled && $adminPhone) {
            // Parse template placeholders
            $message = $whatsappTemplate;
            $message = str_replace('{{ order-id }}', $orderData['orderId'], $message);
            $message = str_replace('{{ customer-name }}', $orderData['customerName'], $message);
            $message = str_replace('{{ customer-email }}', $orderData['email'], $message);
            $message = str_replace('{{ customer-phone }}', $orderData['phone'], $message);
            $message = str_replace('{{ amount }}', $orderData['currency'] . ' ' . $orderData['amount'], $message);
            $message = str_replace('{{ payment-method }}', $orderData['paymentMethod'], $message);
            $message = str_replace('{{ date }}', $orderData['date'], $message);
            
            // Build product list for message
            $productList = '';
            if (!empty($orderData['items'])) {
                $productNames = array_map(function($item) {
                    $name = $item['name'] ?? 'Product';
                    $qty = $item['qty'] ?? 1;
                    return $qty > 1 ? "$name (x$qty)" : $name;
                }, $orderData['items']);
                $productList = implode(', ', $productNames);
            }
            $message = str_replace('{{ product-name }}', $productList, $message);
            
            // Clean phone number (remove spaces, dashes, etc.)
            $cleanPhone = preg_replace('/[^0-9+]/', '', $adminPhone);
            
            // Build WhatsApp URL
            $whatsappUrl = 'https://wa.me/' . ltrim($cleanPhone, '+') . '?text=' . urlencode($message);
        }
@endphp

{{-- Include the selected layout --}}
@include('landings.public.thankyou.' . $selectedLayout, [
    'orderData' => $orderData,
    'showSummary' => $showSummary,
    'showInvoice' => $showInvoice,
    'whatsappEnabled' => $whatsappEnabled,
    'whatsappUrl' => $whatsappUrl,
    'autoWhatsappRedirect' => $autoWhatsappRedirect,
    'redirectDelay' => $redirectDelay,
])

{{-- WhatsApp Auto-Redirect Script --}}
@if($whatsappEnabled && $autoWhatsappRedirect && $whatsappUrl && isset($lead))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Show countdown overlay
        const overlay = document.getElementById('whatsapp-redirect-overlay');
        const countdownEl = document.getElementById('whatsapp-countdown');
        const cancelBtn = document.getElementById('whatsapp-cancel-redirect');
        
        let seconds = {{ intval($redirectDelay / 1000) }};
        let redirectCancelled = false;
        
        if (overlay && countdownEl) {
            overlay.style.display = 'flex';
            
            const countdown = setInterval(function() {
                seconds--;
                if (countdownEl) countdownEl.textContent = seconds;
                
                if (seconds <= 0 || redirectCancelled) {
                    clearInterval(countdown);
                    if (!redirectCancelled) {
                        window.location.href = "{{ $whatsappUrl }}";
                    }
                }
            }, 1000);
            
            // Cancel button handler
            if (cancelBtn) {
                cancelBtn.addEventListener('click', function() {
                    redirectCancelled = true;
                    if (overlay) overlay.style.display = 'none';
                });
            }
        } else {
            // Fallback: just redirect after delay
            setTimeout(function() {
                window.location.href = "{{ $whatsappUrl }}";
            }, {{ $redirectDelay }});
        }
    });
</script>
@endif
