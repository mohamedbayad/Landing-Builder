<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandingPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'landing_id',
        'type',
        'funnel_step_type',
        'funnel_position',
        'next_landing_page_id',
        'step_metadata',
        'name',
        'slug',
        'status',
        'html',
        'css',
        'js',
        'grapesjs_json',
    ];

    protected $casts = [
        'funnel_position' => 'integer',
        'step_metadata' => 'array',
    ];

    public function landing()
    {
        return $this->belongsTo(Landing::class);
    }

    public function nextLandingPage()
    {
        return $this->belongsTo(self::class, 'next_landing_page_id');
    }

    public function previousLandingPages()
    {
        return $this->hasMany(self::class, 'next_landing_page_id');
    }
}
