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
        Schema::table('recording_sessions', function (Blueprint $table) {
            $table->dropForeign(['landing_page_id']);
            $table->foreign('landing_page_id')->references('id')->on('landings')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('recording_sessions', function (Blueprint $table) {
            $table->dropForeign(['landing_page_id']);
            $table->foreign('landing_page_id')->references('id')->on('landing_pages')->cascadeOnDelete();
        });
    }
};
