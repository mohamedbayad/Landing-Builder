<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalyticsSession extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'started_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'ended_at' => 'datetime',
        'is_bounce' => 'boolean',
    ];

    public function visitor()
    {
        return $this->belongsTo(AnalyticsVisitor::class, 'visitor_id');
    }

    public function landing()
    {
        return $this->belongsTo(Landing::class);
    }

    public function events()
    {
        return $this->hasMany(AnalyticsEvent::class, 'session_id_fk');
    }
}
