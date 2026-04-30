<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkspaceSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'workspace_public_endpoint',
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
        // AI Settings
        'ai_provider',
        'ai_model',
        'ai_api_key',
        'ai_role_assignments',
        // Chatbot CTA
        'chatbot_custom_cta_enabled',
        'chatbot_custom_cta_text',
        'chatbot_custom_cta_type',
        'chatbot_custom_cta_target',
        'chatbot_custom_cta_landing_id',
    ];

    protected $casts = [
        'workspace_public_endpoint' => 'string',
        'sidebar_collapsed' => 'boolean',
        'thankyou_show_summary' => 'boolean',
        'thankyou_show_invoice_btn' => 'boolean',
        'whatsapp_enabled' => 'boolean',
        'whatsapp_redirect_enabled' => 'boolean',
        'whatsapp_open_new_tab' => 'boolean',
        'ai_role_assignments' => 'array',
        'chatbot_custom_cta_enabled' => 'boolean',
        'chatbot_custom_cta_landing_id' => 'integer',
    ];

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }
}
