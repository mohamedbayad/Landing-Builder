<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsEvent extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'event_data' => 'array',
    ];

    public function session()
    {
        return $this->belongsTo(AnalyticsSession::class, 'session_id_fk');
    }

    public function visitor()
    {
        return $this->belongsTo(AnalyticsVisitor::class, 'visitor_id');
    }

    public function landing()
    {
        return $this->belongsTo(Landing::class);
    }
}
