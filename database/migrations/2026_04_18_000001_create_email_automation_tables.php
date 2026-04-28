<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_automations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('status')->default('draft')->index(); // draft|active|paused
            $table->string('trigger_type')->index(); // form_submitted|checkout_completed|lead_created
            $table->json('trigger_config')->nullable();
            $table->json('conditions')->nullable();
            $table->string('timezone')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->index(['status', 'trigger_type']);
        });

        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('subject');
            $table->string('preview_text')->nullable();
            $table->longText('body_html');
            $table->json('body_json')->nullable();
            $table->string('status')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('email_automation_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('automation_id')->constrained('email_automations')->cascadeOnDelete();
            $table->unsignedInteger('step_order');
            $table->string('step_type'); // send_email|wait
            $table->unsignedInteger('delay_value')->nullable();
            $table->string('delay_unit')->nullable(); // minutes|hours|days
            $table->foreignId('template_id')->nullable()->constrained('email_templates')->nullOnDelete();
            $table->json('rules')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->unique(['automation_id', 'step_order']);
        });

        Schema::create('email_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->string('email');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('status')->default('subscribed')->index(); // subscribed|unsubscribed|bounced|complained
            $table->string('source')->nullable();
            $table->json('meta')->nullable();
            $table->unsignedInteger('total_sent_emails')->default(0);
            $table->timestamp('last_opened_at')->nullable();
            $table->timestamp('last_clicked_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'email']);
            $table->index('email');
        });

        Schema::create('email_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('automation_id')->nullable()->constrained('email_automations')->nullOnDelete();
            $table->foreignId('automation_step_id')->nullable()->constrained('email_automation_steps')->nullOnDelete();
            $table->foreignId('template_id')->nullable()->constrained('email_templates')->nullOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained('email_contacts')->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            // Order table may be optional in this codebase, keep relation resilient.
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->foreignId('landing_page_id')->nullable()->constrained('landing_pages')->nullOnDelete();
            $table->string('recipient_email');
            $table->string('subject');
            $table->longText('body_html');
            $table->string('status')->default('queued')->index();
            $table->string('provider')->nullable();
            $table->string('provider_message_id')->nullable();
            $table->timestamp('sent_at')->nullable()->index();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('first_clicked_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['status', 'sent_at']);
        });

        Schema::create('email_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_message_id')->constrained('email_messages')->cascadeOnDelete();
            $table->string('event_type')->index();
            $table->json('event_data')->nullable();
            $table->timestamp('occurred_at')->index();
            $table->timestamps();
        });

        Schema::create('email_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_message_id')->constrained('email_messages')->cascadeOnDelete();
            $table->text('original_url');
            $table->string('tracking_code')->unique();
            $table->unsignedInteger('total_clicks')->default(0);
            $table->timestamp('first_clicked_at')->nullable();
            $table->timestamp('last_clicked_at')->nullable();
            $table->timestamps();

            $table->index('tracking_code');
        });

        Schema::create('email_unsubscribes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->nullable()->constrained('email_contacts')->nullOnDelete();
            $table->string('email')->index();
            $table->string('reason')->nullable();
            $table->string('source')->nullable();
            $table->timestamp('unsubscribed_at')->index();
            $table->timestamps();
        });

        Schema::create('email_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->unique();
            $table->string('mail_driver')->nullable();
            $table->string('smtp_host')->nullable();
            $table->unsignedInteger('smtp_port')->nullable();
            $table->string('smtp_username')->nullable();
            $table->text('smtp_password')->nullable();
            $table->string('smtp_encryption')->nullable();
            $table->string('from_email')->nullable();
            $table->string('from_name')->nullable();
            $table->string('reply_to_email')->nullable();
            $table->string('webhook_secret')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_settings');
        Schema::dropIfExists('email_unsubscribes');
        Schema::dropIfExists('email_links');
        Schema::dropIfExists('email_events');
        Schema::dropIfExists('email_messages');
        Schema::dropIfExists('email_contacts');
        Schema::dropIfExists('email_automation_steps');
        Schema::dropIfExists('email_templates');
        Schema::dropIfExists('email_automations');
    }
};
