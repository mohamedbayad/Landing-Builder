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
        Schema::create('page_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('landing_id')->nullable()->constrained()->nullOnDelete();
            // Template ID might be useful for template analytics later
            $table->unsignedBigInteger('template_id')->nullable(); 
            
            $table->string('path');
            $table->text('full_url');
            $table->text('referrer')->nullable();
            
            // UTM Parameters
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('utm_content')->nullable();
            $table->string('utm_term')->nullable();
            
            // User/Device Info
            $table->string('ip_hash')->nullable(); // SHA256 of IP + Salt
            $table->text('user_agent')->nullable();
            $table->string('device_type')->default('unknown'); // mobile, desktop, tablet, bot, unknown
            $table->string('source_type')->default('direct'); // direct, social, search, referral, paid, email, other
            
            $table->string('country')->nullable();
            
            $table->timestamps();
            
            // Indexes for Analytics Performance
            $table->index('created_at');
            $table->index(['landing_id', 'created_at']);
            $table->index(['source_type', 'created_at']);
            $table->index(['device_type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_visits');
    }
};
