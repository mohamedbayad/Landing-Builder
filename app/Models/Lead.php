<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'landing_id',
        'landing_page_id',
        'type', // 'form' or 'checkout'
        'email',
        'first_name',
        'last_name',
        'phone',
        'address',
        'city',
        'zip',
        'country',
        'data', // JSON data
        'ip_address',
        'status', // new, paid, pending, failed...
        'payment_provider',
        'amount',
        'currency',
        'transaction_id',
        'invoice_id',
        'product_id',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
        'referrer',
        'order_items', 
    ];

    protected $casts = [
        'data' => 'array',
        'order_items' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function landing()
    {
        return $this->belongsTo(Landing::class);
    }

    public function page()
    {
        return $this->belongsTo(LandingPage::class, 'landing_page_id');
    }
    
    public function getCustomerNameAttribute()
    {
        if ($this->first_name || $this->last_name) {
            return trim($this->first_name . ' ' . $this->last_name);
        }
        return $this->email;
    }
}
