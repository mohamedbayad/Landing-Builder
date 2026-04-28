<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'subject',
        'preview_text',
        'body_html',
        'body_json',
        'status',
    ];

    protected $casts = [
        'body_json' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function steps()
    {
        return $this->hasMany(EmailAutomationStep::class, 'template_id');
    }

    public function messages()
    {
        return $this->hasMany(EmailMessage::class, 'template_id');
    }
}

