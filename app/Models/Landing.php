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
        'uuid',
        'storage_path',
        'imported_at',
        'enable_cart',
        'cart_bg_color',
        'cart_text_color',
        'cart_btn_color',
        'cart_btn_text_color',
        'cart_position',
        'cart_x_offset',
        'cart_y_offset',
        'countdown_enabled',
        'countdown_end_at',
        'countdown_duration_minutes',
        'countdown_started_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_main' => 'boolean',
        'enable_cart' => 'boolean',
        'cart_position' => 'string',
        'cart_x_offset' => 'integer',
        'cart_y_offset' => 'integer',
        'countdown_enabled' => 'boolean',
        'countdown_end_at' => 'datetime',
        'countdown_started_at' => 'datetime',
        'countdown_duration_minutes' => 'integer',
        'imported_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    public function getEffectiveCountdownEndAt()
    {
        if (!$this->countdown_enabled) {
            return null;
        }

        // Mode A: Fixed End Date
        if ($this->countdown_end_at) {
            return $this->countdown_end_at;
        }

        // Mode B: Duration-based
        // If we have a duration but no fixed end date, we need a start time.
        // We can use countdown_started_at which should be set when the landing is published 
        // or when the countdown is enabled.
        if ($this->countdown_duration_minutes && $this->countdown_started_at) {
            return $this->countdown_started_at->copy()->addMinutes($this->countdown_duration_minutes);
        }

        return null;
    }

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

    public function media()
    {
        return $this->hasMany(LandingMedia::class);
    }

    public function pageVisits()
    {
        return $this->hasMany(PageVisit::class);
    }
}
