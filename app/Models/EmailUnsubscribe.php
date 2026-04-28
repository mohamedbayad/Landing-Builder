<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailUnsubscribe extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_id',
        'email',
        'reason',
        'source',
        'unsubscribed_at',
    ];

    protected $casts = [
        'unsubscribed_at' => 'datetime',
    ];

    public function contact()
    {
        return $this->belongsTo(EmailContact::class, 'contact_id');
    }
}

