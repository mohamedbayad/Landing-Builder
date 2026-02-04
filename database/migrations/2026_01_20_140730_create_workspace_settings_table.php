<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workspace_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->onDelete('cascade');
            
            // Theme: Dashboard
            $table->string('dashboard_direction')->default('ltr'); // ltr, rtl
            $table->string('dashboard_primary_color')->nullable();
            $table->string('dashboard_link_color')->nullable();
            $table->string('dashboard_text_color')->nullable();
            
            // Theme: Sidebar
            $table->string('sidebar_bg')->nullable();
            $table->string('sidebar_text')->nullable();
            $table->string('sidebar_active')->nullable();
            $table->string('sidebar_hover')->nullable();
            $table->string('sidebar_border')->nullable();
            $table->boolean('sidebar_collapsed')->default(false);

            // Theme: Checkout
            $table->string('checkout_style')->default('style_1');
            $table->string('checkout_primary_color')->nullable();
            $table->string('checkout_bg')->nullable();
            $table->string('checkout_text')->nullable();

            // Theme: Thank You Page
            $table->string('thankyou_style')->default('style_1');
            $table->boolean('thankyou_show_summary')->default(true);
            $table->boolean('thankyou_show_invoice_btn')->default(true);

            // WhatsApp Settings
            $table->boolean('whatsapp_enabled')->default(false);
            $table->string('whatsapp_phone')->nullable();
            $table->boolean('whatsapp_redirect_enabled')->default(false);
            $table->integer('whatsapp_redirect_seconds')->default(5);
            $table->boolean('whatsapp_open_new_tab')->default(true);
            $table->text('whatsapp_template_landing')->nullable();
            $table->text('whatsapp_template_thankyou')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workspace_settings');
    }
};
