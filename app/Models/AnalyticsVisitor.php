<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalyticsVisitor extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    
    protected $casts = [
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
    ];

    public function sessions()
    {
        return $this->hasMany(AnalyticsSession::class, 'visitor_id');
    }
}
