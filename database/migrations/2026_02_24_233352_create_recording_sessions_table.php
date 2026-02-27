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
        Schema::create('recording_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('landing_page_id')->index();
            $table->foreign('landing_page_id')->references('id')->on('landing_pages')->cascadeOnDelete();
            $table->string('session_id', 64)->unique();
            $table->string('visitor_id', 64)->index();
            $table->enum('device_type', ['desktop', 'tablet', 'mobile'])->nullable();
            $table->string('referrer', 500)->nullable();
            $table->json('utm_params')->nullable();
            $table->smallInteger('screen_width')->unsigned()->nullable();
            $table->smallInteger('screen_height')->unsigned()->nullable();
            $table->boolean('converted')->default(false);
            $table->unsignedInteger('total_duration_ms')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
            $table->index(['landing_page_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recording_sessions');
    }
};
