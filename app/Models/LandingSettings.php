<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandingSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'landing_id',
        'meta_title',
        'meta_description',
        'fb_pixel_id',
        'ga_measurement_id',
        'custom_head_scripts',
        'custom_body_scripts',
        'stripe_publishable_key',
        'stripe_secret_key',
        'paypal_client_id',
        'paypal_secret',
        'currency',
        'product_price',
        'product_name',
        'enable_card',
        'enable_paypal',
        'enable_cod',
    ];

    public function landing()
    {
        return $this->belongsTo(Landing::class);
    }
}
