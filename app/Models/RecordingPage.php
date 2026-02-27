<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecordingPage extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $guarded = [];

    protected $casts = [
        'entered_at' => 'datetime',
        'exited_at' => 'datetime',
    ];

    public function session()
    {
        return $this->belongsTo(RecordingSession::class, 'session_id', 'session_id');
    }

    public function events()
    {
        return $this->hasMany(RecordingEvent::class, 'page_id', 'id');
    }
}
