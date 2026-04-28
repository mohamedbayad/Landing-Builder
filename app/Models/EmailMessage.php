<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'automation_id',
        'automation_step_id',
        'template_id',
        'contact_id',
        'lead_id',
        'order_id',
        'landing_page_id',
        'recipient_email',
        'subject',
        'body_html',
        'status',
        'provider',
        'provider_message_id',
        'sent_at',
        'delivered_at',
        'opened_at',
        'first_clicked_at',
        'failed_at',
        'error_message',
        'meta',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'opened_at' => 'datetime',
        'first_clicked_at' => 'datetime',
        'failed_at' => 'datetime',
        'meta' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function automation()
    {
        return $this->belongsTo(EmailAutomation::class, 'automation_id');
    }

    public function step()
    {
        return $this->belongsTo(EmailAutomationStep::class, 'automation_step_id');
    }

    public function template()
    {
        return $this->belongsTo(EmailTemplate::class, 'template_id');
    }

    public function contact()
    {
        return $this->belongsTo(EmailContact::class, 'contact_id');
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function page()
    {
        return $this->belongsTo(LandingPage::class, 'landing_page_id');
    }

    public function events()
    {
        return $this->hasMany(EmailEvent::class, 'email_message_id');
    }

    public function links()
    {
        return $this->hasMany(EmailLink::class, 'email_message_id');
    }
}

