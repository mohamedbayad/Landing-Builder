<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Landing extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id', 
        'template_id', 
        'name', 
        'slug', 
        'status', 
        'is_main',
        'published_at',
        'enable_cart',
        'cart_bg_color',
        'cart_text_color',
        'cart_btn_color',
        'cart_btn_text_color',
        'cart_position',
        'cart_x_offset',
        'cart_y_offset',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_main' => 'boolean',
        'enable_cart' => 'boolean',
        'cart_position' => 'string',
        'cart_x_offset' => 'integer',
        'cart_y_offset' => 'integer',
    ];

    public function settings()
    {
        return $this->hasOne(LandingSettings::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function checkoutFields()
    {
        return $this->hasMany(CheckoutField::class);
    }

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }

    public function template()
    {
        return $this->belongsTo(Template::class);
    }

    public function pages()
    {
        return $this->hasMany(LandingPage::class);
    }

    public function trackingEvents()
    {
        return $this->hasMany(TrackingEvent::class);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }
}
