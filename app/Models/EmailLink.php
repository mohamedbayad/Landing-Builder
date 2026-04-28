<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'email_message_id',
        'original_url',
        'tracking_code',
        'total_clicks',
        'first_clicked_at',
        'last_clicked_at',
    ];

    protected $casts = [
        'first_clicked_at' => 'datetime',
        'last_clicked_at' => 'datetime',
    ];

    public function message()
    {
        return $this->belongsTo(EmailMessage::class, 'email_message_id');
    }
}

