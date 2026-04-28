<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailAutomationStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'automation_id',
        'step_order',
        'step_type',
        'delay_value',
        'delay_unit',
        'template_id',
        'rules',
        'settings',
    ];

    protected $casts = [
        'rules' => 'array',
        'settings' => 'array',
        'delay_value' => 'integer',
    ];

    public function automation()
    {
        return $this->belongsTo(EmailAutomation::class, 'automation_id');
    }

    public function template()
    {
        return $this->belongsTo(EmailTemplate::class, 'template_id');
    }

    public function messages()
    {
        return $this->hasMany(EmailMessage::class, 'automation_step_id');
    }
}

