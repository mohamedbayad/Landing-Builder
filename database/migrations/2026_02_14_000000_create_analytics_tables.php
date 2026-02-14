<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Visitors (Unique People / Devices)
        Schema::create('analytics_visitors', function (Blueprint $table) {
            $table->id();
            $table->uuid('visitor_id')->unique(); // Cookie ID
            $table->string('ip_hash')->nullable(); // Privacy focused
            $table->text('user_agent')->nullable();
            
            $table->timestamp('first_seen_at')->useCurrent();
            $table->timestamp('last_seen_at')->useCurrent();
            $table->timestamps();

            $table->index('visitor_id');
            $table->index('ip_hash');
        });

        // 2. Sessions (Visits)
        Schema::create('analytics_sessions', function (Blueprint $table) {
            $table->id();
            $table->uuid('session_id')->unique(); // Cookie ID (expires ~30m)
            
            $table->foreignId('visitor_id')->constrained('analytics_visitors')->cascadeOnDelete();
            $table->foreignId('landing_id')->nullable()->constrained()->nullOnDelete();
            
            // Timing
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('last_activity_at')->useCurrent();
            $table->timestamp('ended_at')->nullable();
            $table->integer('duration_seconds')->default(0);
            
            // Engagement
            $table->boolean('is_bounce')->default(true); // Default true, set false if >1 interaction/pageview
            
            // Context (Source/Device - copied from first hit)
            $table->string('source_type')->default('direct'); // direct, social, search, referral, paid, email
            $table->text('referrer')->nullable();
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('utm_content')->nullable();
            $table->string('utm_term')->nullable();
            
            $table->string('device_type')->default('desktop'); // mobile, desktop, tablet
            $table->string('browser')->nullable();
            $table->string('os')->nullable();
            
            $table->timestamps();

            $table->index(['landing_id', 'started_at']);
            $table->index('session_id');
            $table->index('last_activity_at');
        });

        // 3. Events (Pageviews, Clicks, Form Submits)
        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();
            $table->uuid('session_id'); // No FK constraint for speed? Or yes? Let's use string to avoid join overhead on inserts if we want 
            // Actually FK is safer.
            $table->foreignId('session_id_fk')->nullable()->constrained('analytics_sessions')->nullOnDelete(); 
            // We'll store the UUID string too for easy querying without joins if needed, or just rely on FK.
            // Let's stick to standard FK for consistency.
            
            $table->foreignId('visitor_id')->constrained('analytics_visitors')->cascadeOnDelete();
            $table->foreignId('landing_id')->nullable()->constrained()->nullOnDelete();
            
            $table->string('event_name'); // pageview, cta_click, form_start, lead_submit, scroll
            $table->text('url_path')->nullable();
            $table->json('event_data')->nullable(); // { button_id: 'btn-1', form_id: 'form-2', scroll_depth: 50 }
            
            $table->timestamps();

            $table->index(['landing_id', 'created_at']);
            $table->index(['session_id_fk', 'created_at']);
            $table->index('event_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_events');
        Schema::dropIfExists('analytics_sessions');
        Schema::dropIfExists('analytics_visitors');
    }
};
