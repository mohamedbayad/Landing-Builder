<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrackingEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'landing_id',
        'landing_page_id',
        'type',
        'data',
        'session_id',
        'ip_address',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function landing()
    {
        return $this->belongsTo(Landing::class);
    }

    public function page()
    {
        return $this->belongsTo(LandingPage::class, 'landing_page_id');
    }
}
