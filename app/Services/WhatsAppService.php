<?php

namespace App\Services;

use App\Models\Landing;
use App\Models\Lead;
use App\Models\WorkspaceSetting;

class WhatsAppService
{
    /**
     * Render the Landing Page (Button) Template
     */
    public function renderLandingMessage(string $template, Landing $landing, ?string $workspaceName = null): string
    {
        $vars = [
            '{{ landing-title }}' => $landing->title ?? 'Offers',
            '{{ landing-url }}'   => route('public.home'),
            '{{ workspace-name }}' => $workspaceName ?? 'Our Store',
            '{{ page-id }}'       => $landing->id,
        ];

        return str_replace(array_keys($vars), array_values($vars), $template);
    }

    /**
     * Render the Thank You Page (Redirect) Template
     */
    public function renderThankYouMessage(string $template, \App\Models\Lead $lead, Landing $landing): string
    {
        // Decode product metadata if available
        // Decode product metadata if available
        $productName = $lead->product->name ?? 'Product';
        $itemString = "1x $productName";

        // Robust data access (handling both direct columns and JSON data)
        $name = $lead->customer_name ?? $lead->name ?? $lead->data['billing_first_name'] ?? 'Guest';
        $email = $lead->email ?? $lead->data['email'] ?? $lead->data['billing_email'] ?? '';
        $phone = $lead->phone ?? $lead->data['phone'] ?? $lead->data['billing_phone'] ?? '';
        
        $vars = [
            // Customer
            '{{ customer-name }}'  => $name,
            '{{ customer-email }}' => $email,
            '{{ customer-phone }}' => $phone,
            
            // Order
            '{{ order-id }}'       => 'ORD-' . $lead->id,
            '{{ order-total }}'    => number_format($lead->amount ?? 0, 2),
            '{{ currency }}'       => $lead->currency ?? 'USD',
            '{{ order-items }}'    => $itemString,
            '{{ payment-method }}' => ucfirst($lead->payment_provider ?? 'N/A'),
            '{{ created-at }}'     => $lead->created_at->format('Y-m-d H:i'),

            // Landing
            '{{ landing-title }}'  => $landing->title ?? 'Offer',
            '{{ landing-url }}'    => route('public.home'),
        ];

        return str_replace(array_keys($vars), array_values($vars), $template);
    }

    /**
     * Generate the full WhatsApp URL
     */
    public function generateUrl(?string $phone, string $message): string
    {
        if (!$phone) return '#';
        
        $phone = preg_replace('/[^0-9]/', '', $phone); // Sanitize phone
        $encodedMessage = urlencode($message);
        
        return "https://wa.me/{$phone}?text={$encodedMessage}";
    }
}
