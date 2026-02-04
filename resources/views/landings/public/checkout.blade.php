{{-- Checkout Layout Switcher --}}
{{-- This file dynamically includes the appropriate layout based on settings --}}

@php
    // Default to layout_1 if not set
    $selectedLayout = $layout ?? 'layout_1';
    
    // Validate layout exists, fallback to layout_1
    $validLayouts = ['layout_1', 'layout_2', 'layout_3', 'layout_4', 'layout_5'];
    if (!in_array($selectedLayout, $validLayouts)) {
        $selectedLayout = 'layout_1';
    }
@endphp

{{-- Include the selected layout --}}
@include('landings.public.checkout.' . $selectedLayout)

{{-- Payment Scripts (shared across all layouts) --}}
@php
    $s = $landing->settings;
    $enableCard = $s->enable_card ?? true;
    $enablePaypal = $s->enable_paypal ?? true;
    $paypalClientId = $s->paypal_client_id ?? $landing->workspace->paypal_client_id ?? '';
    $stripePubKey = $s->stripe_publishable_key ?? $landing->workspace->stripe_publishable_key ?? '';
@endphp

@if($enablePaypal && $paypalClientId && $product)
<script src="https://www.paypal.com/sdk/js?client-id={{ $paypalClientId }}&currency={{ strtoupper($product->currency) }}"></script>
<script>
    paypal.Buttons({
        style: {
            layout: 'vertical',
            color:  'gold',
            shape:  'rect',
            label:  'paypal'
        },
        createOrder: function(data, actions) {
            return fetch("{{ route('payment.paypal.create') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    landing_id: "{{ $landing->id }}",
                    product_id: "{{ $product->id }}"
                })
            })
            .then((response) => {
                if (!response.ok) {
                    return response.json().then(err => { throw err; });
                }
                return response.json();
            })
            .then((order) => {
                if (!order.id) {
                    throw new Error('No order ID returned from server');
                }
                return order.id;
            })
            .catch((err) => {
                console.error('PayPal Order Error:', err);
                throw err; 
            });
        },
        onApprove: function(data, actions) {
            const form = document.getElementById('checkout-form');
            const formData = new FormData(form);
            const plainFormData = Object.fromEntries(formData.entries());

            return fetch("{{ route('payment.paypal.capture') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    orderID: data.orderID,
                    landing_id: "{{ $landing->id }}",
                    product_id: "{{ $product->id }}",
                    ...plainFormData
                })
            })
            .then((response) => response.json())
            .then((details) => {
                if (details.status === 'COMPLETED') {
                     window.location.href = details.redirect_url || "/";
                } else {
                    if (details.redirect_url) window.location.href = details.redirect_url;
                }
            })
            .catch((err) => {
                console.error('PayPal Capture Error:', err);
            });
        },
        onError: function (err) {
            console.error('PayPal Widget Error:', err);
        }
    }).render('#paypal-button-container');
</script>
@endif

@if($enableCard && $stripePubKey && $product)
<script src="https://js.stripe.com/v3/"></script>
<script>
    document.addEventListener('DOMContentLoaded', async () => {
        const stripe = Stripe('{{ $stripePubKey }}');
        
        const { clientSecret } = await fetch("{{ route('payment.stripe.intent') }}", {
            method: "POST",
            headers: {
                 "Content-Type": "application/json",
                 'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            body: JSON.stringify({
                landing_id: "{{ $landing->id }}",
                product_id: "{{ $product->id }}"
            })
        }).then(r => r.json());

        const elements = stripe.elements({ clientSecret });
        const paymentElement = elements.create('payment');
        paymentElement.mount('#payment-element');

        const form = document.getElementById('checkout-form');
        const submitBtn = document.getElementById('main-submit-btn');

        form.addEventListener('submit', async (e) => {
            const paymentMethodInput = document.querySelector('input[name="payment_method"]:checked');
            const paymentMethod = paymentMethodInput ? paymentMethodInput.value : '';
            
            if (paymentMethod === 'card') {
                e.preventDefault();
                submitBtn.disabled = true;
                submitBtn.textContent = 'Processing...';

                const { error } = await stripe.confirmPayment({
                    elements,
                    confirmParams: {
                        return_url: window.location.origin + "/payment/stripe/return",
                    },
                });

                if (error) {
                    const messageContainer = document.querySelector('#stripe-error-message');
                    if (messageContainer) {
                        messageContainer.textContent = error.message;
                        messageContainer.classList.remove('hidden');
                    }
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Pay {{ $product->currency }} {{ $product->price }}';
                }
            }
        });
    });
</script>
@endif
