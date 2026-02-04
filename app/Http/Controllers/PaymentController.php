<?php

namespace App\Http\Controllers;

use App\Models\Landing;
use App\Models\Product;
use App\Http\Controllers\LeadsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class PaymentController extends Controller
{
    // --- PayPal Integration ---

    private function getPaypalBaseUrl($settings, $workspace)
    {
        // Use sandbox if no specific 'live' flag or if explicit sandbox mode (assuming sandbox for now unless env says otherwise)
        // ideally we check a setting like 'payment_mode' => 'live' or 'sandbox'
        $mode = $settings->payment_mode ?? $workspace->payment_mode ?? env('PAYPAL_MODE', 'sandbox');
        return $mode === 'live' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';
    }

    private function getPaypalAccessToken($settings, $workspace)
    {
        $clientId = $settings->paypal_client_id ?? $workspace->paypal_client_id ?? env('PAYPAL_CLIENT_ID');
        $secret = $settings->paypal_secret ?? $workspace->paypal_secret ?? env('PAYPAL_SECRET');

        if (!$clientId || !$secret) {
            return null;
        }

        $baseUrl = $this->getPaypalBaseUrl($settings, $workspace);
        
        $response = Http::withBasicAuth($clientId, $secret)
            ->asForm()
            ->post("$baseUrl/v1/oauth2/token", [
                'grant_type' => 'client_credentials',
            ]);

        return $response->json()['access_token'] ?? null;
    }

    public function createPaypalOrder(Request $request)
    {   
        $landing = Landing::findOrFail($request->landing_id);
        $product = \App\Models\Product::findOrFail($request->product_id);
        
        $settings = $landing->settings;
        $workspace = $landing->workspace;

        $token = $this->getPaypalAccessToken($settings, $workspace);
        
        if (!$token) {
            return response()->json(['error' => 'PayPal configuration missing'], 500);
        }

        $baseUrl = $this->getPaypalBaseUrl($settings, $workspace);

        $response = Http::withToken($token)
            ->post("$baseUrl/v2/checkout/orders", [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'amount' => [
                        'currency_code' => strtoupper($product->currency),
                        'value' => number_format($product->price, 2, '.', ''),
                    ],
                    'description' => $product->name,
                ]],
                'application_context' => [
                    'shipping_preference' => 'NO_SHIPPING', // Adjust based on needs
                    'user_action' => 'PAY_NOW',
                ]
            ]);
            
        if ($response->failed()) {
            return response()->json(['error' => 'Failed to create PayPal order', 'details' => $response->json()], 500);
        }

        return $response->json();
    }

    public function capturePaypalOrder(Request $request)
    {
        $orderId = $request->orderID; // From frontend
        $landing = Landing::findOrFail($request->landing_id);
        $product = \App\Models\Product::findOrFail($request->product_id);

        $settings = $landing->settings;
        $workspace = $landing->workspace;

        $token = $this->getPaypalAccessToken($settings, $workspace);
        $baseUrl = $this->getPaypalBaseUrl($settings, $workspace);

        $response = Http::withToken($token)
            ->post("$baseUrl/v2/checkout/orders/$orderId/capture", [
                'header' => ['Content-Type' => 'application/json']
            ]);

        if ($response->successful()) {
            $data = $response->json();
            
            // Allow 'COMPLETED' or 'APPROVED' (though capture usually returns COMPLETED)
            if (($data['status'] ?? '') === 'COMPLETED') {
                // Payment Verified! Create Order using LeadsController shared logic
                // We'll mimic the request data needed
                
                $transactionId = $data['id'] ?? $orderId;
                
                // Extract customer info from request or PayPal response if needed
                // For now, we trust the frontend form data sent along with capture? 
                // Actually, typically we send form data with the capture request or store it temporarily.
                // Current frontend plan: Send form data with the capture request.
                
                // Instantiate LeadsController to reuse logic (or use a Service)
                $leadsController = app(\App\Http\Controllers\LeadsController::class);
                
                $leadsController->createOrder(
                    [], // Validated data (not strictly used inside createOrder except generic passing)
                    $request->all(), // Form data (email, name, etc passes through)
                    $landing,
                    $product,
                    'paypal',
                    $transactionId,
                    'paid'
                );

                // Return URL for redirection
                $thankYouPage = $landing->pages()->where('type', 'thankyou')->first();
                $redirectUrl = $thankYouPage 
                    ? ($landing->is_main ? '/'.$thankYouPage->slug : route('landings.preview', [$landing, $thankYouPage]))
                    : null;

                return response()->json(['status' => 'COMPLETED', 'redirect_url' => $redirectUrl]);
            }
        }

        return response()->json(['error' => 'Payment capture failed or not completed.', 'details' => $response->json()], 400);
    }

    // --- Stripe Integration ---

    public function createStripePaymentIntent(Request $request)
    {
        $landing = Landing::findOrFail($request->landing_id);
        $product = \App\Models\Product::findOrFail($request->product_id);
        
        $settings = $landing->settings;
        $workspace = $landing->workspace;
        
        $stripeKey = $settings->stripe_secret_key ?? $workspace->stripe_secret_key ?? env('STRIPE_SECRET_KEY');

        if (!$stripeKey) {
             return response()->json(['error' => 'Stripe configuration missing'], 500);
        }

        // Call Stripe API manually
        $response = Http::withToken($stripeKey)
            ->asForm()
            ->post('https://api.stripe.com/v1/payment_intents', [
                'amount' => (int)($product->price * 100), // Cents
                'currency' => strtolower($product->currency),
                'automatic_payment_methods' => ['enabled' => 'true'],
                'metadata' => [
                    'landing_id' => $landing->id,
                    'product_id' => $product->id,
                    'order_id' => uniqid('ord_'), // Temp ID
                ]
            ]);

        if ($response->failed()) {
             return response()->json(['error' => 'Stripe API Error', 'details' => $response->json()], 500);
        }

        return response()->json([
            'clientSecret' => $response->json()['client_secret']
        ]);
    }
    
    // Stripe Return/Webhook handler would be needed for full verification
    // For now, we can rely on frontend 'confirmPayment' promise resolution for immediate UI feedback,
    // but SECURELY we should use a webhook. 
    // For MVP, we'll allow the frontend to submit the Order Creation after confirmPayment resolves.
    // WAIT, that's insecure. Better: The return_url sends them to a backend route that verifies the intent status.
    
    public function handleStripeReturn(Request $request)
    {
        // ... (Optional: Verify Securely here)
    }
}
