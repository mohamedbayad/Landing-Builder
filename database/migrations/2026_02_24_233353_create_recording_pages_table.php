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
        Schema::create('recording_pages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('session_id', 64)->index();
            $table->foreign('session_id')->references('session_id')->on('recording_sessions')->cascadeOnDelete();
            $table->enum('page_type', ['landing', 'checkout', 'thankyou']);
            $table->string('url', 1000);
            $table->unsignedInteger('duration_ms')->nullable();
            $table->timestamp('entered_at');
            $table->timestamp('exited_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recording_pages');
    }
};
