<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkspaceSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        // Theme
        'dashboard_direction',
        'dashboard_primary_color',
        'dashboard_link_color',
        'dashboard_text_color',
        'sidebar_bg',
        'sidebar_text',
        'sidebar_active',
        'sidebar_hover',
        'sidebar_border',
        'sidebar_collapsed',
        'checkout_style',
        'checkout_primary_color',
        'checkout_bg',
        'checkout_text',
        'thankyou_style',
        'thankyou_show_summary',
        'thankyou_show_invoice_btn',
        // WhatsApp
        'whatsapp_enabled',
        'whatsapp_phone',
        'whatsapp_redirect_enabled',
        'whatsapp_redirect_seconds',
        'whatsapp_open_new_tab',
        'whatsapp_template_landing',
        'whatsapp_template_thankyou',
    ];

    protected $casts = [
        'sidebar_collapsed' => 'boolean',
        'thankyou_show_summary' => 'boolean',
        'thankyou_show_invoice_btn' => 'boolean',
        'whatsapp_enabled' => 'boolean',
        'whatsapp_redirect_enabled' => 'boolean',
        'whatsapp_open_new_tab' => 'boolean',
    ];

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }
}
