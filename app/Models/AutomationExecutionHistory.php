<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AutomationExecutionHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'execution_id',
        'node_id',
        'event_type',
        'result',
        'occurred_at',
    ];

    protected $casts = [
        'result' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function execution()
    {
        return $this->belongsTo(AutomationExecution::class, 'execution_id');
    }
}

