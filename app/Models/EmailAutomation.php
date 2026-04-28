<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailAutomation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'status',
        'trigger_type',
        'trigger_config',
        'conditions',
        'timezone',
        'settings',
    ];

    protected $casts = [
        'trigger_config' => 'array',
        'conditions' => 'array',
        'settings' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function steps()
    {
        return $this->hasMany(EmailAutomationStep::class, 'automation_id')->orderBy('step_order');
    }

    public function messages()
    {
        return $this->hasMany(EmailMessage::class, 'automation_id');
    }
}

