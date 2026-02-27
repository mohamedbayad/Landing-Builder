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
        Schema::create('recording_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('page_id')->index();
            $table->foreign('page_id')->references('id')->on('recording_pages')->cascadeOnDelete();
            $table->longText('events_compressed');
            $table->unsignedInteger('events_count')->default(0);
            $table->unsignedInteger('size_bytes')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recording_events');
    }
};
