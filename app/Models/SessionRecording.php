<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SessionRecording extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'landing_page_id',
        'visitor_ip',
        'location',
        'duration',
        'events_data',
    ];

    protected $casts = [
        'events_data' => 'array',
        'duration' => 'integer',
    ];

    /**
     * Get the landing page this recording belongs to.
     */
    public function landingPage()
    {
        return $this->belongsTo(LandingPage::class);
    }

    /**
     * Get formatted duration (e.g., "2m 30s")
     */
    public function getFormattedDurationAttribute(): string
    {
        $minutes = floor($this->duration / 60);
        $seconds = $this->duration % 60;
        
        if ($minutes > 0) {
            return "{$minutes}m {$seconds}s";
        }
        return "{$seconds}s";
    }
}
