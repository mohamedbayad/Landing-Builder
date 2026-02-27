<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecordingSession extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $guarded = [];

    protected $casts = [
        'utm_params' => 'array',
        'converted' => 'boolean',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function landingPage()
    {
        return $this->belongsTo(Landing::class, 'landing_page_id');
    }

    public function pages()
    {
        return $this->hasMany(RecordingPage::class, 'session_id', 'session_id');
    }

    public function getDurationAttribute()
    {
        if (!$this->total_duration_ms) return '0s';
        
        $seconds = floor($this->total_duration_ms / 1000);
        if ($seconds < 60) return $seconds . 's';
        
        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;
        return $minutes . 'm ' . $remainingSeconds . 's';
    }
}
