<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'lead_id',
        'email',
        'first_name',
        'last_name',
        'phone',
        'status',
        'source',
        'meta',
        'total_sent_emails',
        'last_opened_at',
        'last_clicked_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'last_opened_at' => 'datetime',
        'last_clicked_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function messages()
    {
        return $this->hasMany(EmailMessage::class, 'contact_id');
    }

    public function unsubscribes()
    {
        return $this->hasMany(EmailUnsubscribe::class, 'contact_id');
    }

    public function tags()
    {
        return $this->hasMany(EmailContactTag::class, 'contact_id');
    }

    public function getFullNameAttribute(): string
    {
        return trim(sprintf('%s %s', $this->first_name, $this->last_name));
    }
}
