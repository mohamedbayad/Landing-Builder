<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AutomationExecution extends Model
{
    use HasFactory;

    protected $fillable = [
        'automation_id',
        'contact_id',
        'status',
        'current_node_id',
        'scheduled_for',
        'context',
        'last_error_code',
        'last_error_message',
        'completed_at',
    ];

    protected $casts = [
        'scheduled_for' => 'datetime',
        'completed_at' => 'datetime',
        'context' => 'array',
    ];

    public function automation()
    {
        return $this->belongsTo(EmailAutomation::class, 'automation_id');
    }

    public function contact()
    {
        return $this->belongsTo(EmailContact::class, 'contact_id');
    }

    public function history()
    {
        return $this->hasMany(AutomationExecutionHistory::class, 'execution_id')->orderBy('occurred_at');
    }
}

