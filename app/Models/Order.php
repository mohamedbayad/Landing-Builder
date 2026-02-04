<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'landing_id',
        'product_id',
        'customer_name',
        'customer_email',
        'amount',
        'currency',
        'status',
        'payment_provider',
        'transaction_id',
        'metadata',
        'order_items',
    ];

    protected $casts = [
        'metadata' => 'array',
        'order_items' => 'array',
    ];

    public function landing()
    {
        return $this->belongsTo(Landing::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function lead()
    {
        return $this->morphOne(Lead::class, 'source');
    }
}
