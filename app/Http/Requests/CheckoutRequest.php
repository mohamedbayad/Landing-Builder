<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'landing_id' => ['required', 'exists:landings,id'],
            'product_id' => ['required', 'exists:products,id'],
            'payment_method' => ['required', 'in:card,paypal,cod'],
            // Customer Info validation
            'first_name' => ['nullable', 'string', 'max:120'],
            'last_name'  => ['nullable', 'string', 'max:120'],
            'email'      => ['nullable', 'email', 'max:190'],
            'address'    => ['nullable', 'string', 'max:255'],
            'phone'      => ['nullable', 'string', 'max:50'],
            'city'       => ['nullable', 'string', 'max:100'],
            'zip'        => ['nullable', 'string', 'max:20'],
            'country'    => ['nullable', 'string', 'max:100'],
        ];
    }

    protected function prepareForValidation()
    {
        // Sanitize inputs (XSS Protection)
        $this->merge([
            'first_name' => $this->first_name ? strip_tags($this->first_name) : null,
            'last_name' => $this->last_name ? strip_tags($this->last_name) : null,
            'address' => $this->address ? strip_tags($this->address) : null,
            'city' => $this->city ? strip_tags($this->city) : null,
            'phone' => $this->phone ? strip_tags($this->phone) : null,
        ]);
    }
}
